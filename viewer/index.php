<!DOCTYPE html>
<head>
    <?php
        require_once '../includes/core/min-header.php';
        require_once '../includes/core/page-storage.php';
        require_once '../includes/html-builder/page-content.php';

        get_stylesheet("page-content.css");
    ?>
    <!-- Shouldn't be able to see the title because of the iframe -->
    <title>Page Viewer</title>
</head>
<body>
<?php
    echo get_page_content_html(get_page($_GET["id"]), false);
?>
</body>