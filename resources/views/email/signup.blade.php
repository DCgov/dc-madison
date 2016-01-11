<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8" />
	</head>
	<body>
		<h1>Welcome to Drafts.dc.gov</h1>

		<p>Congratulations! After clicking <a href="{{ url('user/verify/' . $token, $parameters = array(), $secure = null) }}">here</a> to verify your account, you can:</p>

		<ol>
			<li>Find a document by browsing through the full list or filtering by sponsor, status, or category</li>
			<li>Add your voice by supporting or opposing a document, making general comments, or annotating specific sections of the work in progress.</li>
		</ol>

		<p>Drafts.dc.gov is run in partnership between the DC Council, the Executive Office of the Mayor, the OCTO Technology Innovation Program, and the OpenGov Foundation.</p>

        <p>Madison is free, open source software created by <a href="http://opengovfoundation.org">The OpenGov Foundation</a>, a nonpartisan, nonprofit 501(c)3 based in Washington, DC.</p>
	</body>
</html>
