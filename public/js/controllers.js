/*global user*/
/*global doc*/
angular.module('madisonApp.controllers', [])
  .controller('HomePageController', ['$scope', '$http', '$filter',
    function ($scope, $http, $filter) {
      $scope.docs = [];
      $scope.categories = [];
      $scope.sponsors = [];
      $scope.statuses = [];
      $scope.dates = [];
      $scope.dateSort = '';
      $scope.select2 = '';
      $scope.docSort = "created_at";
      $scope.reverse = true;

      $scope.select2Config = {
        multiple: true,
        allowClear: true,
        placeholder: "Filter documents by category, sponsor, or status"
      };

      $scope.dateSortConfig = {
        allowClear: true,
        placeholder: "Sort By Date"
      };

      //Retrieve all docs
      $http.get('/api/docs')
        .success(function (data) {
          $scope.parseDocs(data);
        })
        .error(function (data) {
          console.error("Unable to get documents: %o", data);
        });

      $scope.parseDocs = function (docs) {
        angular.forEach(docs, function (doc) {
          $scope.docs.push(doc);

          $scope.parseDocMeta(doc.categories, 'categories');
          $scope.parseDocMeta(doc.sponsor, 'sponsors');
          $scope.parseDocMeta(doc.statuses, 'statuses');

          angular.forEach(doc.dates, function (date) {
            date.date = Date.parse(date.date);
          });
        });
      };

      $scope.parseDocMeta = function (collection, name) {
        if (collection.length === 0) {
          return;
        }

        angular.forEach(collection, function (item) {
          var found = $filter('getById')($scope[name], item.id);

          if (found === null) {
            switch (name) {
            case 'categories':
              $scope.categories.push(item);
              break;
            case 'sponsors':
              $scope.sponsors.push(item);
              break;
            case 'statuses':
              $scope.statuses.push(item);
              break;
            default:
              console.error('Unknown meta name: ' + name);
            }
          }
        });
      };

      $scope.docFilter = function (doc) {

        var show = false;

        if ($scope.select2 !== undefined && $scope.select2 !== '') {
          var cont = true;

          var select2 = $scope.select2.split('_');
          var type = select2[0];
          var value = parseInt(select2[1], 10);

          switch (type) {
          case 'category':
            angular.forEach(doc.categories, function (category) {
              if (+category.id === value && cont) {
                show = true;
                cont = false;
              }
            });
            break;
          case 'sponsor':
            angular.forEach(doc.sponsor, function (sponsor) {
              if (+sponsor.id === value && cont) {
                show = true;
                cont = false;
              }
            });
            break;
          case 'status':
            angular.forEach(doc.statuses, function (status) {
              if (+status.id === value && cont) {
                show = true;
                cont = false;
              }
            });
            break;
          }
        } else {
          show = true;
        }

        return show;
      };
    }
    ])
  .controller('DocumentPageController', ['$scope', '$cookies', '$location',
    function ($scope, $cookies, $location) {
      $scope.hideIntro = $cookies.hideIntro;

      $scope.hideHowToAnnotate = function () {
        $cookies.hideIntro = true;
        $scope.hideIntro = true;
      };
    }
    ])
  .controller('ReaderController', ['$scope', '$http', 'annotationService', 'createLoginPopup', '$timeout', '$anchorScroll',
    function ($scope, $http, annotationService, createLoginPopup, $timeout, $anchorScroll) {
      $scope.annotations = [];

      $scope.$on('annotationsUpdated', function () {
        $scope.annotations = annotationService.annotations;
        $scope.$apply();

        $timeout(function () {
          $anchorScroll();
        }, 0);
      });

      $scope.init = function () {
        $scope.user = user;
        $scope.doc = doc;

        $scope.setSponsor();
        $scope.getSupported();
      };

      $scope.setSponsor = function () {
        if($scope.doc.group_sponsor.length !== 0){
          $scope.doc.sponsor = $scope.doc.group_sponsor;
        }else{
          $scope.doc.sponsor = $scope.doc.user_sponsor;
          $scope.doc.sponsor[0].display_name = $scope.doc.sponsor[0].fname + ' ' + $scope.doc.sponsor[0].lname;
        }
      };

      $scope.getSupported = function () {
        if ($scope.user.id !== '') {
          $http.get('/api/users/' + $scope.user.id + '/support/' + $scope.doc.id)
            .success(function (data) {
              switch (data.support) {
              case "1":
                $scope.supported = true;
                break;
              case "":
                $scope.opposed = true;
                break;
              default:
                $scope.supported = null;
                $scope.opposed = null;
              }

              if($scope.supported !== null && $scope.opposed !== null){
                $('#doc-support').text(data.supports + ' Support');
                $('#doc-oppose').text(data.opposes + ' Oppose');
              }
            }).error(function () {
              console.error("Unable to get support info for user %o and doc %o", $scope.user, $scope.doc);
            });
        }
      };

      $scope.support = function (supported, $event) {

        if ($scope.user.id === '') {
          createLoginPopup($event);
        } else {
        $http.post('/api/docs/' + $scope.doc.id + '/support', {
          'support': supported
        })
          .success(function (data) {
            //Parse data to see what user's action is currently
            if (data.support === null) {
              $scope.supported = false;
              $scope.opposed = false;
            } else {
              $scope.supported = data.support;
              $scope.opposed = !data.support;
            }

            var button = $($event.target);
            var otherButton = $($event.target).siblings('a.btn');

            if(button.hasClass('doc-support')){
              button.text(data.supports + ' Support');
              otherButton.text(data.opposes + ' Oppose');
            }else{
              button.text(data.opposes + ' Oppose');
              otherButton.text(data.supports + ' Support');
            }

          })
          .error(function (data) {
            console.error("Error posting support: %o", data);
          });
        }
      };
    }
    ])
  .controller('ParticipateController', ['$scope', '$sce', '$http', 'annotationService', 'createLoginPopup', 'growl', '$location', '$filter', '$timeout',
    function ($scope, $sce, $http, annotationService, createLoginPopup, growl, $location, $filter, $timeout) {
      $scope.annotations = [];
      $scope.comments = [];
      $scope.activities = [];
      $scope.supported = null;
      $scope.opposed = false;

      //Parse sub-comment hash if there is one
      var hash = $location.hash();
      var subCommentId = hash.match(/^subcomment_([0-9]+)$/);
      if(subCommentId){
        $scope.subCommentId = subCommentId[1];  
      }
      

      $scope.init = function (docId) {
        $scope.getDocComments(docId);
        $scope.user = user;
        $scope.doc = doc;
      };

      //Watch for annotationsUpdated broadcast
      $scope.$on('annotationsUpdated', function () {
        angular.forEach(annotationService.annotations, function (annotation) {
          if ($.inArray(annotation, $scope.activities) < 0) {
            var collapsed = true;

            if($scope.subCommentId){
              angular.forEach(annotation.comments, function (subcomment) {
                if(subcomment.id == $scope.subCommentId){
                  collapsed = false;
                }
              });
            }

            annotation.label = 'annotation';
            annotation.commentsCollapsed = collapsed;
            $scope.activities.push(annotation);
          }
        });

        $scope.$apply();
      });

      $scope.isSponsor = function(){
        var currentId = $scope.user.id;
        var sponsored = false;
 
        angular.forEach($scope.doc.sponsor, function(sponsor){
          if(currentId === sponsor.id){
            sponsored = true;
          }
        });

        return sponsored;
      };

      $scope.notifyAuthor = function(activity){
 
    // If the current user is a sponsor and the activity hasn't been seen yet, 
     // post to API route depending on comment/annotation label
        $http.post('/api/docs/' + doc.id + '/' + activity.label + 's/' + activity.id + '/' + 'seen')
        .success(function(data){
          activity.seen = data.seen;
        }).error(function(data){
          console.error("Unable to mark activity as seen: %o", data);
        });
      };


      $scope.getDocComments = function (docId) {
        $http({
          method: 'GET',
          url: '/api/docs/' + docId + '/comments'
        })
          .success(function (data) {
            angular.forEach(data, function (comment) {
              var collapsed = true;
              if($scope.subCommentId){
                angular.forEach(comment.comments, function (subcomment) {
                  if(subcomment.id == $scope.subCommentId){
                    collapsed = false;
                  }
                });
              }
              comment.commentsCollapsed = collapsed;
              comment.label = 'comment';
              comment.link = 'comment_' + comment.id;
              $scope.activities.push(comment);
            });
          })
          .error(function (data) {
            console.error("Error loading comments: %o", data);
          });
      };

      $scope.commentSubmit = function () {

        var comment = angular.copy($scope.comment);
        comment.user = $scope.user;
        comment.doc = $scope.doc;

        $http.post('/api/docs/' + comment.doc.id + '/comments', {
          'comment': comment
        })
          .success(function () {
            comment.label = 'comment';
            comment.user.fname = comment.user.name;
            $scope.activities.push(comment);
            $scope.comment.text = '';
          })
          .error(function (data) {
            console.error("Error posting comment: %o", data);
          });
      };

      $scope.activityOrder = function (activity) {
        var popularity = activity.likes - activity.dislikes;

        return popularity;
      };

      $scope.addAction = function (activity, action, $event) {
        if ($scope.user.id !== '') {
          $http.post('/api/docs/' + $scope.doc.id + '/' + activity.label + 's/' + activity.id + '/' + action)
            .success(function (data) {
              activity.likes = data.likes;
              activity.dislikes = data.dislikes;
              activity.flags = data.flags;
            }).error(function (data) {
              console.error(data);
            });
        } else {
          createLoginPopup($event);
        }

      };

      $scope.collapseComments = function (activity) {
        activity.commentsCollapsed = !activity.commentsCollapsed;
      };

      $scope.subcommentSubmit = function (activity, subcomment) {
        subcomment.user = $scope.user;

        $.post('/api/docs/' + $scope.doc.id + '/' + activity.label + 's/' + activity.id + '/comments', {
          'comment': subcomment
        })
          .success(function (data) {
            activity.comments.push(data);
            subcomment.text = '';
            subcomment.user = '';
            $scope.$apply();
          }).error(function (data) {
            console.error(data);
          });
      };
    }
    ])
  .controller('UserPageController', ['$scope', '$http', '$location',
    function ($scope, $http, $location) {
      $scope.user = {};
      $scope.meta = '';
      $scope.docs = [];
      $scope.activities = [];
      $scope.verified = false;

      $scope.init = function () {
        $scope.getUser();
      };

      $scope.getUser = function () {
        var abs = $location.absUrl();
        var id = abs.match(/.*\/(\d+)$/);
        id = id[1];

        $http.get('/api/user/' + id)
          .success(function (data) {
            $scope.user = angular.copy(data);
            $scope.meta = angular.copy(data.user_meta);

            angular.forEach(data.docs, function (doc) {
              $scope.docs.push(doc);
            });

            angular.forEach(data.comments, function (comment) {
              comment.label = 'comment';
              $scope.activities.push(comment);
            });

            angular.forEach(data.annotations, function (annotation) {
              annotation.label = 'annotation';
              $scope.activities.push(annotation);
            });

            angular.forEach($scope.user.user_meta, function (meta) {
              var cont = true;

              if (meta.meta_key === 'verify' && meta.meta_value === 'verified' && cont) {
                $scope.verified = true;
                cont = false;
              }
            });

          }).error(function (data) {
            console.error("Unable to retrieve user: %o", data);
          });
      };

      $scope.showVerified = function () {
        if ($scope.user.docs && $scope.user.docs.length > 0) {
          return true;
        }

        return false;
      };

      $scope.activityOrder = function (activity) {
        return Date.parse(activity.created_at);
      };

    }
    ])
    .controller('DocumentTocController', ['$scope',
      function ($scope) {
        $scope.headings = [];
        // For now, we use the simplest possible method to render the TOC -
        // just scraping the content.  We could use a real API callback here
        // later if need be.  A huge stack of jQuery follows.
        var headings = $('#doc_content').find('h1,h2,h3,h4,h5,h6');

        if(headings.length > 0) {

          headings.each(function(i, elm) {
            elm = $(elm);
            // Set an arbitrary id.
            // TODO: use a better identifier here - preferably a title-based slug
            if(!elm.attr('id'))
            {
              elm.attr('id', 'heading-' + i);
            }
            elm.addClass('anchor');
            $scope.headings.push({'title': elm.text(), 'tag': elm.prop('tagName'), 'link': elm.attr('id')});
          });
        }
        else {
          $('#toc-column').remove();
          var container = $('#content').parent();
          container.removeClass('col-md-6');
          container.addClass('col-md-9');
        }

      }
    ]);
