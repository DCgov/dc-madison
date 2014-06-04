/*global Annotator*/
/*global getAnnotationService*/
/*global user*/
/*global doc*/
/*global diff_match_patch*/
Annotator.Plugin.Madison = function () {
  Annotator.Plugin.apply(this, arguments);
};

$.extend(Annotator.Plugin.Madison.prototype, new Annotator.Plugin(), {
  events: {},
  options: {},
  pluginInit: function () {

    /**
     *  Subscribe to Store's `annotationsLoaded` event
     *    Stores all annotation objects provided by Store in the window
     *    Adds all annotations to the sidebar
     **/
    this.annotator.subscribe('annotationsLoaded', function (annotations) {
      annotations.forEach(function (annotation) {
        annotation.highlights.forEach(function (highlight) {
          $(highlight).attr('id', 'annotation_' + annotation.id);
        });
      });

      //Set the annotations in the annotationService
      var annotationService = getAnnotationService();
      annotationService.setAnnotations(annotations);
    });

    /**
     *  Subscribe to Annotator's `annotationCreated` event
     *    Adds new annotation to the sidebar
     */
    this.annotator.subscribe('annotationCreated', function (annotation) {
      var annotationService = getAnnotationService();
      annotationService.addAnnotation(annotation);

      if ($.showAnnotationThanks) {
        $('#annotationThanks').modal({
          remote: '/modals/annotation_thanks',
          keyboard: true
        });
      }
    });

    this.annotator.subscribe('commentCreated', function (comment) {
      comment = $('<div class="existing-comment"><blockquote>' + comment.text + '<div class="comment-author">' + comment.user.name + '</div></blockquote></div>');
      var currentComments = $('#current-comments');
      currentComments.append(comment);
      currentComments.removeClass('hidden');

      $('#current-comments').collapse(true);
    });

    this.annotator.subscribe('annotationViewerTextField', function (field, annotation) {
      if(annotation.tags.length === 0){
        return;
      }

      var showDiff = false;

      annotation.tags.forEach(function (tag){
        if(tag === 'edit'){
          var jField = $(field);
          var differ = new diff_match_patch();
          var diffs = differ.diff_main(annotation.quote, annotation.text);
          var html = differ.diff_prettyHtml(diffs);
          jField.find('p').html(html);
        }
      });
    });

    //Add Madison-specific fields to the viewer when Annotator loads it
    this.annotator.viewer.addField({
      load: function (field, annotation) {
        this.addNoteLink(field, annotation);
        this.addNoteActions(field, annotation);
        this.addComments(field, annotation);
      }.bind(this)
    });
  },
  addComments: function (field, annotation) {
    //Add comment wrapper and collapse the comment thread
    var commentsHeader = $('<div class="comment-toggle" data-toggle-"collapse" data-target="#current-comments">Comments <span id="comment-caret" class="caret caret-right"></span></button>').click(function () {
      $('#current-comments').collapse('toggle');
      $('#comment-caret').toggleClass('caret-right');
    });

    //If there are no comments, hide the comment wrapper
    if ($(annotation.comments).length === 0) {
      commentsHeader.addClass('hidden');
    }

    //Add all current comments to the annotation viewer
    var currentComments = $('<div id="current-comments" class="current-comments collapse"></div>');

    /*jslint unparam: true*/
    $.each(annotation.comments, function (index, comment) {
      comment = $('<div class="existing-comment"><blockquote>' + comment.text + '<div class="comment-author">' + comment.user.name + '</div></blockquote></div>');
      currentComments.append(comment);
    });
    /*jslint unparam: false*/

    //Collapse the comment thread on load
    currentComments.ready(function () {
      $('#existing-comments').collapse({
        toggle: false
      });
    });

    //If the user is logged in, allow them to comment
    if (user.id !== '') {
      var annotationComments = $('<div class="annotation-comments"></div>');
      var commentText = $('<input type="text" class="form-control" />');
      var commentSubmit = $('<button type="button" class="btn btn-primary" >Submit</button>');
      commentSubmit.click(function () {
        this.createComment(commentText, annotation);
      }.bind(this));
      annotationComments.append(commentText);

      annotationComments.append(commentSubmit);

      $(field).append(annotationComments);
    }

    $(field).append(commentsHeader, currentComments);
  },
  addNoteActions: function (field, annotation) {
    //Add actions ( like / dislike / error) to annotation viewer
    var annotationAction = $('<div></div>').addClass('annotation-action');
    var generalAction = $('<span></span>').addClass('glyphicon').data('annotation-id', annotation.id);

    var annotationLike = generalAction.clone().addClass('glyphicon-thumbs-up').append('<span class="action-count">' + annotation.likes + '</span>');
    var annotationDislike = generalAction.clone().addClass('glyphicon-thumbs-down').append('<span class="action-count">' + annotation.dislikes + '</span>');
    var annotationFlag = generalAction.clone().addClass('glyphicon-flag').append('<span class="action-count">' + annotation.flags + '</span>');

    annotationAction.append(annotationLike, annotationDislike, annotationFlag);

    //If user is logged in add his current action and enable the action buttons
    if (user.id !== '') {
      if (annotation.user_action) {
        if (annotation.user_action === 'like') {
          annotationLike.addClass('selected');
        } else if (annotation.user_action === 'dislike') {
          annotationDislike.addClass('selected');
        } else if (annotation.user_action === 'flag') {
          annotationFlag.addClass('selected');
        } // else this user doesn't have any actions on this annotation
      }

      var that = this;

      annotationLike.addClass('logged-in').click(function () {
        that.addLike(annotation, this);
      });

      annotationDislike.addClass('logged-in').click(function () {
        that.addDislike(annotation, this);
      });

      annotationFlag.addClass('logged-in').click(function () {
        that.addFlag(annotation, this);
      });
    }

    $(field).append(annotationAction);
  },
  addNoteLink: function (field, annotation) {
    //Add link to annotation
    var noteLink = $('<div class="annotation-link"></div>');
    var annotationLink = $('<a></a>').attr('href', window.location.origin + '/annotation/' + annotation.id).text('View Annotation');
    noteLink.append(annotationLink);
    $(field).append(noteLink);
  },
  createComment: function (textElement, annotation) {
    var text = textElement.val();
    textElement.val('');

    var comment = {
      text: text,
      user: user
    };

    //POST request to add user's comment
    $.post('/api/docs/' + doc.id + '/annotations/' + annotation.id + '/comments', {
      comment: comment
    }, function () {
      annotation.comments.push(comment);

      return this.annotator.publish('commentCreated', comment);
    }.bind(this));
  },
  addLike: function (annotation, element) {
    $.post('/api/docs/' + doc.id + '/annotations/' + annotation.id + '/likes', function (data) {
      element = $(element);
      element.children('.action-count').text(data.likes);
      element.siblings('.glyphicon').removeClass('selected');

      if (data.action) {
        element.addClass('selected');
      } else {
        element.removeClass('selected');
      }

      element.siblings('.glyphicon-thumbs-up').children('.action-count').text(data.likes);
      element.siblings('.glyphicon-thumbs-down').children('.action-count').text(data.dislikes);
      element.siblings('.glyphicon-flag').children('.action-count').text(data.flags);

      annotation.likes = data.likes;
      annotation.dislikes = data.dislikes;
      annotation.flags = data.flags;
      annotation.user_action = 'like';
    });
  },
  addDislike: function (annotation, element) {
    $.post('/api/docs/' + doc.id + '/annotations/' + annotation.id + '/dislikes', function (data) {
      element = $(element);
      element.children('.action-count').text(data.dislikes);
      element.siblings('.glyphicon').removeClass('selected');

      if (data.action) {
        element.addClass('selected');
      } else {
        element.removeClass('selected');
      }

      element.siblings('.glyphicon-thumbs-up').children('.action-count').text(data.likes);
      element.siblings('.glyphicon-thumbs-down').children('.action-count').text(data.dislikes);
      element.siblings('.glyphicon-flag').children('.action-count').text(data.flags);

      annotation.likes = data.likes;
      annotation.dislikes = data.dislikes;
      annotation.flags = data.flags;
      annotation.user_action = 'dislike';
    });
  },
  addFlag: function (annotation, element) {
    $.post('/api/docs/' + doc.id + '/annotations/' + annotation.id + '/flags', function (data) {
      element = $(element);
      element.children('.action-count').text(data.flags);
      element.siblings('.glyphicon').removeClass('selected');

      if (data.action) {
        element.addClass('selected');
      } else {
        element.removeClass('selected');
      }

      element.siblings('.glyphicon-thumbs-up').children('.action-count').text(data.likes);
      element.siblings('.glyphicon-thumbs-down ').children('.action-count').text(data.dislikes);

      annotation.likes = data.likes;
      annotation.dislikes = data.dislikes;
      annotation.flags = data.flags;
      annotation.user_action = 'flag';
    });
  }
});