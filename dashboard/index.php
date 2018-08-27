<!DOCTYPE html>
<head>
	<?php require '../includes/management/min-header.php'; ?>
	<title>Dashboard - Pegasus</title>
	<?php getStylesheet($STYLE_all_pages_list); ?>
</head>
<body>
	<?php require '../includes/pages/get.php'; html_allPagesList(); ?>
	<p style="margin-top:48px;margin-left:8px;font-size:22px"><a rel="noopener" href="/page-editor/?action=new">Create new page</a></p>
</body>
</html>