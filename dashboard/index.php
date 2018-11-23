<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    require_once '../includes/html-builder/toolbar.php';
    require_once '../includes/html-builder/all-pages-list.php';

    get_stylesheet_link("toolbar.css");
    get_stylesheet_link("all-pages-list.css");
    ?>
    <title>Dashboard - Pegasus</title>
</head>
<body>
<?php
echo get_dashboard_toolbar_html();
echo get_all_pages_list_html();
?>
<h2 style="margin-top:48px;margin-left:32px"><a rel="noopener" href="/editor/?action=new">Create new page</a></h2>
</body>