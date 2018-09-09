<?php

include '../includes/core/min-header.php';
require_once '../includes/core/page-storage.php';
require_once '../includes/html-builder/page-content.php';

if (isset($_GET["id"]) && $_GET["id"] >= 0) {
	$temp = isset($_GET["temp"]) && $_GET["temp"] == "yes";
	$p = get_page($_GET["id"]);
	if ($temp) {
		$p = Page::get_temp_page();
	}
	echo get_page_content_html($p, (isset($_GET["edit"]) && $_GET["edit"] == "yes"), $temp);
} else {
	echo "No page ID specified!";
}

?>