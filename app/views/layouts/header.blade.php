<div class="navbar-header ">
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  <a class="brand logo-madison navbar-brand link-home" href="{{ URL::to('/') }}" target="_self">
  	Madison <span class="level">Demo</span>
  </a>
</div>
<div class="nav nav-main navbar-collapse collapse">
	<ul class="nav navbar-nav navbar-right">
		<li class="link-about"><a href="{{ URL::to('about') }}" target="_self">About</a></li>
		<li class="link-faq"><a href="{{ URL::to('faq') }}" target="_self">FAQ</a></li>
		<li class="link-support"><a href="https://rally.org/opengovfoundation" target="_blank">Donate</a></li>
    <li class="link-subscribe"><a href="http://opengovfoundation.us6.list-manage.com/subscribe?u=9d450bf68b3df1185fc9f62b2&id=697ad42ab6" target="_blank">Subscribe</a></li>
		@if(Auth::check())
			<li class="dropdown">
				<a class="dropdown-trigger" href="#" data-toggle="dropdown">Welcome {{ Auth::user()->fname }} <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li class="link-settings"><a href="{{ URL::to('user/edit/' . Auth::user()->id) }}" target="_self">Account Settings</a></li>
					<li class="link-settings"><a href="{{ URL::to('groups') }}" target="_self">Group Management</a></li>
					@if(Auth::user()->hasRole('Admin'))
					<li><a href="{{ URL::to('dashboard') }}" target="_self">Administrative Dashboard</a></li>
					@endif
					<li class="link-logout"><a href="{{ URL::to('logout') }}" target="_self">Logout</a></li>
				</ul>
			</li>
		@else
			<li class="link-login"><a href="{{ URL::to('user/login') }}" target="_self">Login</a></li>
			<li class="link-signup"><a href="{{ URL::to('user/signup') }}" target="_self">Sign Up</a></li>
		@endif
	</ul>
</div><!--/.navbar-collapse -->
