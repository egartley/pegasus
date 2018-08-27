<?php

require_once '../includes/objects/page.php';
require_once '../includes/pages/storage.php';

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

function outputEditorHTML() {
	if (isset($_GET["id"])) {
		// sanitize input
		if (!is_numeric($_GET["id"])) {
			echo "<p>ERROR: Invalid page ID (must be a number)</p>";
			return;
		}
		// proceed with normal edit html, since we have a page id
		$page = getPageByID($_GET["id"]);
		if ($page == null) {
			// there is not a page by that id
			echo "<p>ERROR: No page with ID of " . $_GET["id"] . "</p>";
			return;
		}
	} else if (getAction() == "new") {
		// extra stuff for when makinga new page (that doesn't exist yet)
		// $page = new Page();
	}
	echo "<h2>" . $page->title . "</h2><p>Page ID: " . $page->id . "</p><p>Created: " . $page->created . "</p><p>Updated: " . $page->updated . "</p>";
}

?>