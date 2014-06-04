<div class="logo-madison col-md-4">
	<a class="link-home" href="{{ URL::to('/') }}">
		<h1 class="white">Madison <span class="level">Demo</span></h1>
	</a>
</div>
<div class="nav nav-main col-md-4 col-md-offset-4">

	<ul>
		<li class="link-about"><a href="{{ URL::to('about') }}">About</a></li>
		<li class="link-faq"><a href="{{ URL::to('faq') }}">FAQ</a></li>	
		<li class="link-support"><a href="https://rally.org/opengovfoundation" target="_blank">Donate</a></li>	
<<<<<<< HEAD
		<li class="link-subscribe"><a href="http://opengovfoundation.us6.list-manage.com/subscribe?u=9d450bf68b3df1185fc9f62b2&id=697ad42ab6" target="_blank">Subscribe</a></li>
=======
		<li class="link-subscribe"><a href="http://opengovfoundation.us6.list-manage.com/subscribe?u=9d450bf68b3df1185fc9f62b2&id=40a5a16e19" target="_blank">Subscribe</a></li>
>>>>>>> master
		@if(Auth::check())
			<li class="dropdown">
				<a class="dropdown-trigger" href="#" data-toggle="dropdown">Welcome {{ Auth::user()->fname }} <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li class="link-settings"><a href="{{ URL::to('user/edit/' . Auth::user()->id) }}">Account Settings</a></li>
					<li class="link-settings"><a href="{{ URL::to('groups') }}">Group Management</a></li>
					@if(Auth::user()->hasRole('Admin'))
					<li><a href="{{ URL::to('dashboard') }}">Administrative Dashboard</a></li>
					@endif
					<li class="link-logout"><a href="{{ URL::to('logout') }}">Logout</a></li>
				</ul>
			</li>
		@else
			<li class="link-login"><a href="{{ URL::to('user/login') }}">Login</a></li>
			<li class="link-signup"><a href="{{ URL::to('user/signup') }}">Sign Up</a></li>
		@endif
	</ul>

</div>


