<!DOCTYPE html>
<head>
    <?php
        require_once '../includes/core/min-header.php';
        require_once '../includes/core/page-storage.php';
        require_once '../includes/html-builder/page-content.php';

        get_stylesheet_link("page-content.css");
    ?>
    <title>Page Viewer</title>
</head>
<body>
<?php
    echo get_page_content_html(get_page($_GET["id"]), false);
?>
</body>