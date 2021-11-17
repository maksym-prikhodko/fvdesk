<!DOCTYPE html PUBLIC "-
	"http:
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
		<title>PHP LibDiff - Examples</title>
		<link rel="stylesheet" href="styles.css" type="text/css" charset="utf-8"/>
	</head>
	<body>
		<h1>PHP LibDiff - Examples</h1>
		<hr />
		<?php
		require_once dirname(__FILE__).'/../lib/Diff.php';
		$a = explode("\n", file_get_contents(dirname(__FILE__).'/a.txt'));
		$b = explode("\n", file_get_contents(dirname(__FILE__).'/b.txt'));
		$options = array(
		);
		$diff = new Diff($a, $b, $options);
		?>
		<h2>Side by Side Diff</h2>
		<?php
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Html/SideBySide.php';
		$renderer = new Diff_Renderer_Html_SideBySide;
		echo $diff->Render($renderer);
		?>
		<h2>Inline Diff</h2>
		<?php
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Html/Inline.php';
		$renderer = new Diff_Renderer_Html_Inline;
		echo $diff->render($renderer);
		?>
		<h2>Unified Diff</h2>
		<pre><?php
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Text/Unified.php';
		$renderer = new Diff_Renderer_Text_Unified;
		echo htmlspecialchars($diff->render($renderer));
		?>
		</pre>
		<h2>Context Diff</h2>
		<pre><?php
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Text/Context.php';
		$renderer = new Diff_Renderer_Text_Context;
		echo htmlspecialchars($diff->render($renderer));
		?>
		</pre>
	</body>
</html>
