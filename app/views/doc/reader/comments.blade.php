@if(Auth::check())
<div id="participate-comment" class="row participate-comment">
	@include('doc.reader.comment')
</div>
@else
<div id="participate-comment" class="row participate-comment">
	<p>Please <a href="{{ url('/user/login', $parameters = array(), $secure = null) }}" target="_self">login</a> to comment.</p>
</div>
@endif
<div id="participate-activity" class="row participate-activity">
	<h3>Comments</h3>
	<div class="activity-thread col-md-12">
    	<div id="@{{ 'comment_' + comment.id }}" class="row activity-item" ng-repeat="comment in comments | orderBy:activityOrder:true track by $id(comment)" ng-class="comment.label">
        	<div comment-item activity-item-link="@{{ comment.link }}"></div>
    	</div>
	</div>
</div>

