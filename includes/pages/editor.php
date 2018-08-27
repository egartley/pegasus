<?php

require_once '../includes/objects/page.php';
require_once '../includes/pages/storage.php';
require_once '../includes/html-builder/page-content.php';

// most things here are just for testing
$page = null;

function getAction() {
	if (isset($_GET["action"])) {
		// something like "new" or "edit"
		return $_GET["action"];
	}
	// "edit" by default
	return "edit";
}

function editor_html() {
	// ex. "/editor/?action=edit&id=0"
	if (getAction() == "edit") {
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
		$page = getPageByID($_GET["id"]);

		if ($page == null) {
			// there is not a page by that id
			echo "<p>ERROR: No page with ID of " . $_GET["id"] . "</p>";
			return;
		}
	} else if (getAction() == "new") {
		// ex. "/editor/?action=new"	
		// extra stuff for when making a new page (that doesn't exist yet)
		// $page = new Page();
	}
	// output editor html
	echo "<h2>" . $page->title . "</h2><p>Page ID: " . $page->id . "</p><p>Created: " . $page->created . "</p><p>Updated: " . $page->updated . "</p>";
}

?>