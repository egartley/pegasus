<?php

include '../includes/core/min-header.php';
require_once '../includes/core/page-storage.php';
require_once '../includes/html-builder/page-content.php';
require_once '../includes/objects/page.php';

function get_action() {
	if (isset($_GET["action"])) {
		return $_GET["action"];
	} else if (isset($_POST["action"])) {
		return $_POST["action"];
	}
	return "edit";
}

function valid_id($checkid) {
	if (!isset($checkid)){
		$invalidreason = "Page ID not specified";
		return false;
	} else if (!is_numeric($checkid)) {
		$invalidreason = "Page ID must be a number";
		return false;
	} else if ($checkid <= -1 || $checkid >= Page::$maxNumberOfPages) {
		$invalidreason = "Page ID must be between 0 and " . (Page::$maxNumberOfPages - 1) . ", inclusive";
		return false;
	}
	return true;
}

$action = get_action();
$workingpage = null;
$temp = false;
if ($action == "edit") {
	// ex. "/viewer/?action=edit&id=0"
	if (!valid_id($_GET["id"])) {
		echo "<p>" . $invalidreason . "</p>";
		return;
	}
	// get page
	$workingpage = get_page($_GET["id"]);
	if ($workingpage == null) {
		// there is not a page by that id
		echo "<p>ERROR: No page with ID of " . $_GET["id"] . " (make a <a rel=\"noopener\" href=\"/viewer/?action=new\">new page</a>)</p>";
		return;
	}
} else if ($action == "new") {
	// ex. "/viewer/?action=new"	
	$workingpage = new Page(-1);
	$temp = true;
} else if ($action == "save") {
	// ex. "/viewer/?action=save&id=2&isnew=no&contentjson=blahblah&title=My%20Page" (POST method)
	if (!valid_id($_POST["id"])) {
		echo "<p>" . $invalidreason . "</p>";
		return;
	}
	if (Page::action_save($_POST)) {
		header("Location: /viewer/?action=edit&id=" . $_POST["id"]);
	} else {
		// the typical Microsoft "something went wrong"
		echo "<p>Something went wrong while trying to save the page</p>";
	}
	return;
} else {
	// unknown action specified
}

// output html
if ($temp) {
	$workingpage = Page::get_temp_page();
}
echo get_page_content_html($workingpage, $action == "edit" || $action == "new", $temp);

?>