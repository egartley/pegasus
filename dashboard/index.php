<!DOCTYPE html>
<head>
    <?php
        require_once '../includes/core/min-header.php';
        require_once '../includes/html-builder/toolbar.php';
        require_once '../includes/html-builder/all-pages-list.php';

        require_once "../includes/core/check-auth.php";

        get_stylesheet("toolbar.css");
    ?>
    <title>Dashboard</title>
    <script src="/resources/js/jquery.js" type="application/javascript"></script>
    <script src="/resources/js/toolbar.js" type="application/javascript"></script>
    <script src="/resources/js/dashboard.js" type="application/javascript"></script>
</head>
<body>
<?php
    echo get_generic_toolbar_html("Dashboard");
?>
<div class="outter">
    <div class="block-container">
        <div class="base-dialog-modal">
            <div class="dialog-title">
                <span>All Pages</span>
            </div>
            <div class="dialog-content">
                <?php echo get_all_pages_list_html() ?>
                <button id="makenewpage">Make New</button>
            </div>
        </div>
    </div>
</div>
</body>