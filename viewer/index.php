<?php

include '../includes/core/min-header.php';
require_once '../includes/core/page-storage.php';
require_once '../includes/html-builder/page-content.php';

if (isset($_GET["id"]) && $_GET["id"] >= 0) {
	echo get_page_content_html(get_page($_GET["id"]));
} else {
	echo "No page ID specified!";
}

?>