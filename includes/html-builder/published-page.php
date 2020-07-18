<?php

function get_published_page_html(Page $page)
{
    return "<!-- " . date("H:i:s") . " -->
<!DOCTYPE html>
<head>
    <?php
        require_once '../../includes/core/min-header.php';

        get_stylesheet(\"published-page.css\");
    ?>
    <title>{$page->title}</title>
</head>
<body>
<div class=\"flex-container\">
    <div class=\"other\">
        <p>todo</p>
    </div>
    <iframe class=\"content\" src=\"/viewer/?id={$page->id}\"></iframe>
</div>
</body>";
}
