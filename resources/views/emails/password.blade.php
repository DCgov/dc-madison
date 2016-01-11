<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Drafts.dc.gov Password Reset</h2>

		<div>
			To reset your password, complete this form: {{ url('password/reset/' . $token) }}.
		</div>
	</body>
</html>
