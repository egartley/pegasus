<!DOCTYPE html>
<head>
	<?php require_once '../includes/management/min-header.php'; ?>
	<?php getStylesheet($STYLE_all_pages_list); ?>
	<title>Dashboard - Pegasus</title>
</head>
<body>
	<?php require_once '../includes/html-builder/all-pages-list.php'; echo getAllPagesListHTML(); ?>
	<h2 style="margin-top:48px;margin-left:8px"><a rel="noopener" href="/editor/?action=new">Create new page</a></h2>
</body>
</html>