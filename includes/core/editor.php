<?php

require_once '../includes/core/page-storage.php';
require_once '../includes/objects/page.php';
require_once '../includes/html-builder/page-content.php';
require_once '../includes/html-builder/page-editor.php';

// most things here are just for testing
$workingpage = null;
$invalidreason = "";

function get_action() {
	if (isset($_GET["action"])) {
		return $_GET["action"];
	} else if (isset($_POST["action"])) {
		return $_POST["action"];
	}
	// "edit" if not specified in URL
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

function editor() {
	if (get_action() == "edit") {
		// ex. "/editor/?action=edit&id=0"
		if (!valid_id($_GET["id"])) {
			echo "<p>" . $invalidreason . "</p>";
			return;
		}

		// get page
		$workingpage = get_page($_GET["id"]);

		if ($workingpage == null) {
			// there is not a page by that id
			echo "<p>ERROR: No page with ID of " . $_GET["id"] . " (make a <a rel=\"noopener\" href=\"/editor/?action=new\">new page</a>)</p>";
			return;
		}
	} else if (get_action() == "new") {
		// ex. "/editor/?action=new"	
		$workingpage = new Page(-1);
	} else if (get_action() == "save") {
		// ex. "/editor/?action=save&id=2&isnew=no"
		if (!valid_id($_POST["id"])) {
			echo "<p>" . $invalidreason . "</p>";
			return;
		}

		if (Page::action_save($_POST)) {
			header("Location: /editor/?action=edit&id=" . $_POST["id"]);
		} else {
			// the classic Microsoft "something went wrong" ;)
			echo "<p>Something went wrong while trying to save the page</p>";
		}

		return;
	} else {
		// unknown action specified
	}

	// output editor html
	echo get_editor_html($workingpage, get_action());
}

?>