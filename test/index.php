<!DOCTYPE html>
<head>
	<?php
		require_once '../includes/core/min-header.php';
		require_once '../includes/core/page-storage.php';
		require_once '../includes/html-builder/page-content.php';
	?>
	<title>Test - Pegasus</title>
</head>
<body>
	<?php echo get_page_content_html(get_page(0)); ?>
</body>
</html>