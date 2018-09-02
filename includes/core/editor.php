<?php

require_once '../includes/core/page-storage.php';
require_once '../includes/objects/page.php';
require_once '../includes/html-builder/page-content.php';
require_once '../includes/html-builder/page-editor.php';

// most things here are just for testing
$workingpage = null;

function get_action() {
	if (isset($_GET["action"])) {
		return $_GET["action"];
	}
	// "edit" if not specified in URL
	return "edit";
}

function editor() {
	// ex. "/editor/?action=edit&id=0"
	if (get_action() == "edit") {
		// make sure there's an id to work with
		if (!isset($_GET["id"])) {
			echo "<p>ERROR: Please provide a page ID</p>";
			return;
		}
		// sanitize input
		if (!is_numeric($_GET["id"])) {
			echo "<p>ERROR: Invalid page ID (must be a number)</p>";
			return;
		}

		// get page with id
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
		// ex. "/editor/?action=save&id=2&title=Untitled&isnew=no"
		Page::action_save($_GET);
		header("Location: /editor/?action=edit&id=" . $_GET["id"]);
		return;
	} else {
		// unknown action
	}

	// output editor html
	echo get_editor_html($workingpage, get_action());
}

?>