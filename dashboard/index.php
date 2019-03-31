<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    require_once '../includes/html-builder/toolbar.php';
    require_once '../includes/html-builder/all-pages-list.php';

    get_stylesheet_link("toolbar.css");
    get_stylesheet_link("all-pages-list.css");
    ?>
    <style>
        html, body {
            height: 100%
        }
        /* Credit: https://stackoverflow.com/a/27869108/11074765 */
        div.wrapper {
            display: flex;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            left: 0
        }
        div.block-container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            align-content: space-around;
            margin-left: auto;
            margin-right: auto
        }
        div.block-container div.block {
            background-color: #0a0a0a;
            padding: 6px;
            border: #666 solid 1px;
            border-radius: 3px;
            box-shadow: 0 0 3px 2px black
        }
    </style>
    <title>Dashboard</title>
</head>
<body>
<?php
    echo get_dashboard_toolbar_html();
    // echo get_all_pages_list_html();
?>
<!--<h2 style="margin-top:48px;margin-left:32px"><a rel="noopener" href="/editor/?action=new">Create new page</a></h2>-->
<div class="wrapper">
<div class="block-container">
    <div class="block">
        <div>
            <?php echo get_all_pages_list_html() ?>
        </div>
    </div>
</div></div>
</body>