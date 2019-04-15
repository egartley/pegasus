<?php

require_once '../includes/html-builder/page-content.php';

function get_published_page_html(Page $page)
{
    return "<iframe src=\"/viewer/?id={$page->id}\"></iframe>";
}