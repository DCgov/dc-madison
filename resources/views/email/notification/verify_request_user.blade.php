<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8" />
	</head>
	<body>

		<p>A user - {{ $user['fname'] }} {{ $user['lname'] }} ({{ $user['email'] }}) has requested profile verification.</p>

        <p><a href="{{ URL::to('administrative-dashboard/verify-account') }}#request-{{ $request['id'] }}">View Request</a></p>

		<p>&ndash; The Drafts.dc.gov Team</p>
	</body>
</html>
