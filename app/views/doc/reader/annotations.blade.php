<div id="participate-activity" class="row participate-activity">
	<h3>Annotations</h3>
	<div class="activity-thread col-md-12">
		<div ng-hide="annotations.length">
			No annotations yet. Add your own!
		</div>
    	<div class="row activity-item" ng-repeat="annotation in annotations | orderBy:activityOrder:true track by $id(annotation)" ng-class="annotation.label">
        	<div annotation-item activity-item-link="@{{ annotation.link }}"></div>
    	</div>
	</div>
</div>

