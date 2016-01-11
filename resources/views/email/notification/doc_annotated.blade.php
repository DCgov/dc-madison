<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8" />
	</head>
	<body>
		<p>An annotation has been added to - {{ link_to('docs/' . $doc['slug'], $doc['title']) }}</p>

    <p>&quot;{{ $annotation['text'] }}&quot;</p>
    <blockquote>
      <p>{{ $annotation['quote'] }}</p>
    </blockquote>

		<p>&ndash; The Drafts.dc.gov Team</p>
	</body>
</html>
