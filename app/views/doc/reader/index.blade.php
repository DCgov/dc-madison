@extends('layouts/main')
@section('content')
	@if(Auth::check())
		<script>
			var user = {
				id: {{ Auth::user()->id }},
				email: '{{ Auth::user()->email }}',
				name: '{{ Auth::user()->fname . ' ' . substr(Auth::user()->lname, 0, 1) }}'
			};
		</script>
	@else
		<script>
			var user = {
				id: '',
				email: '',
				name: ''
			}
		</script>
	@endif
	<script>
		var doc = {{ $doc->toJSON() }};
		@if($showAnnotationThanks)
			$.showAnnotationThanks = true;
		@else
			$.showAnnotationThanks = false;
		@endif
	</script>
	{{ HTML::style('vendor/annotator/annotator.min.css') }}
	{{ HTML::script('vendor/annotator/annotator-full.min.js') }}
	{{ HTML::script('vendor/showdown/showdown.js') }}
	{{ HTML::script('bower_components/bootstrap/js/collapse.js') }}
	{{ HTML::script('bower_components/bootstrap/js/modal.js') }}
	{{ HTML::script('js/annotator-madison.js') }}
	{{ HTML::script('js/doc.js') }}

<div class="modal fade" id="annotationThanks" tabindex="-1" role="dialog" aria-labelledby="annotationThanks" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    </div>
  </div>
</div>
<div ng-controller="DocumentPageController">
	<div class="row">
		<div class="col-md-8" ng-controller="ReaderController" ng-init="init({{ $doc->id }})">
			<div class="doc-info">
				<div class="">
					<h1>{{ $doc->title }}</h1>
				</div>
				<div class="doc-sponsor" ng-repeat="sponsor in doc.sponsor">
					<strong>Sponsored by </strong><span>@{{ sponsor.fname }} @{{ sponsor.lname }}</span>
				</div>
				<div class="doc-status" ng-repeat="status in doc.statuses">
					<strong>Status: </strong><span>@{{ status.label }}</span>
				</div>
				<div class="doc-date" ng-repeat="date in doc.dates">
					<strong>@{{ date.label }}: </strong><span>@{{ date.date | parseDate | date:'shortDate' }}</span>
				</div>
				<div class="" ng-show="user.id > 0">
						<a href="#" class="btn btn-default doc-support" ng-click="support(true, $event)" ng-class="{'btn-success': supported}">Support This Document</a>
						<a href="#" class="btn btn-default doc-oppose" ng-click="support(false, $event)" ng-class="{'btn-danger': opposed}">Oppose This Document</a>
						@if($doc->id == 3)
							<a href="http://dc.granicus.com/MediaPlayer.php?publish_id=3" target="_blank" class="btn btn-danger" style="position:relative; bottom:0;">Watch Live</a>
						@endif
				</div>
				@if($doc->id == 3)
				<style>
					a{
						color:#2e74bc;
					}
				</style>
				<div class="sponsor-intro" style="margin-top:20px;">
					<p>Submit your questions on this bill for the <a style="color:#2e74bc" href="http://dccouncil.us/committees/committee-on-finance-and-revenue" target="_blank">DC Council Committee on Finance and Revenue</a> to consider live during their June 12, 2014 legislative hearing on the {{ $doc->title }}.  Your questions go directly to DC Council Member David Grosso, who will get them answered before, during, and after tomorrow's event.</p>
					<p><strong>How to Participate</strong></p>
					<ol>
						<li><a href="{{ URL::to('user/signup') }}">Sign up</a> and <a href="{{ URL::to('user/login') }}">log in</a></li>
						<li>Read the legislation then add your feedback to the bill</li>
						<li><a href="http://dc.granicus.com/MediaPlayer.php?publish_id=3" target="_blank">Launch the DC Council webcast</a> as 11am on June 12 to join the real-time hearing.</li>
					</ol>

				</div>
				@endif
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3">
			<div class="document-toc" id="toc">
				<h2>Table of Contents</h2>
				<div ng-controller="DocumentTocController" id="toc-container">
					<ul>
						<li ng-repeat="heading in headings">
							<a class="toc-heading toc-@{{ heading.tag | lowercase }}" href="#@{{ heading.link }}">@{{ heading.title }}</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div id="content" class="content doc_content @if(Auth::check())logged_in@endif">
				<div id="doc_content">{{ $doc->get_content('html') }}</div>
			</div>
		</div>
		<div class="col-md-3">
			<!-- Start Introduction GIF -->
			<div class="how-to-annotate" ng-if="!hideIntro">
				<span class="how-to-annotate-close glyphicon glyphicon-remove" ng-click="hideHowToAnnotate()"></span>
				<h2>How to Participate</h2>
				<div class="">
					<img src="/img/how-to-annotate.gif" class="how-to-annotate-img img-responsive" />
				</div>
				<div class="">
					<ol>
						<li>Read the policy document.</li>
						<li>Sign up to add your voice.</li>
						<li>Annotate, Comment, Support or Oppose!</li>
					</ol>
				</div>
			</div>
			<!-- End Introduction GIF -->
			<div ng-controller="ParticipateController" ng-init="init({{ $doc->id }})" class="rightbar participate">
				@include('doc.reader.participate')
			</div>
		</div>
	</div>
</div>
@endsection
