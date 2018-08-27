<!DOCTYPE html>
<head>
	<?php
		require_once '../includes/management/min-header.php';
		require_once '../includes/pages/storage.php';
		require_once '../includes/html-builder/page-content.php';
	?>
	<title>Test - Pegasus</title>
</head>
<body>
	<p><?php echo getPageContentHTML(getPageByID(0)); ?></p>
</body>
</html>