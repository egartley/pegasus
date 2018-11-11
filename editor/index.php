<!DOCTYPE html>
<head>
	<?php
		require_once '../includes/core/min-header.php';
		require_once '../includes/core/page-storage.php';
		require_once '../includes/html-builder/toolbar.php';
		require_once '../includes/html-builder/page-content.php';
		require_once '../includes/objects/page.php';

		get_stylesheet_link("toolbar.css");
		get_stylesheet_link("page-content.css");

		echo "<title>";
		if (get_action() == "edit") {
			echo "Editing \"" . get_page($_GET["id"])->title . "\"";
		} else if (get_action() == "new") {
			echo "New Page";
		} else {
			// action is "save" or "delete"
			echo "Working...";
		}
		echo " - Pegasus</title>";

		// FUNCTIONS
		function on_editor_load() {
			$action = get_action();
			$workingpage = null;
			if ($action == "edit") {
				// ex. "/editor/?action=edit&id=0" (GET)
				if (!valid_id($_GET["id"])) {
					echo "<p>" . $invalidreason . "</p>";
					return;
				}
				// get page with specified id
				$workingpage = get_page($_GET["id"]);
				if ($workingpage == null) {
					// there is not a page by that id
					echo "<p>ERROR: No page with ID of " . $_GET["id"] . " (make a <a rel=\"noopener\" href=\"/editor/?action=new\">new page</a>)</p>";
					return;
				}
			} else if ($action == "new") {
				// ex. "/editor/?action=new" (GET)
				$workingpage = Page::get_temp_page();
				header("Location: /editor/?action=save&id=" . $workingpage->id . "&isnew=yes");
				return;
			} else if ($action == "save") {
				// ex. "/editor/?action=save&id=2&isnew=no&contentjson=blahblah&title=My%20Awesome%20Page" (POST)
				if ($_GET["isnew"] == "yes") {
					// not previously saved, need to move from temp to normal
					$newpost = array(
						"isnew" => "yes",
						"id" => $_GET["id"],
						"contentjson" => Page::$emptyContentRawJSON,
						"title" => Page::$defaultTitle
					);
					if (Page::action_save($newpost)) {
						header("Location: /editor/?action=edit&id=" . $_GET["id"]);
					} else {
						echo "<p>Something went wrong while trying to save the page</p>";
					}
				}
				if (!valid_id($_POST["id"])) {
					echo "<p>" . $invalidreason . "</p>";
					return;
				}
				// was previously saved
				if (Page::action_save($_POST)) {
					header("Location: /editor/?action=edit&id=" . $_POST["id"]);
				} else {
					echo "<p>Something went wrong while trying to save the page</p>";
				}
				return;
			} else if ($action == "delete") {
				// ex. "/editor/?action=delete&id=2" (GET)
				if (!valid_id($_GET["id"])) {
					echo "<p>" . $invalidreason . "</p>";
					return;
				}
				if (Page::action_delete($_GET["id"])) {
					header("Location: /dashboard/");
				} else {
					echo "<p>Something went wrong while trying to delete the page</p>";
				}
				return;
			} else {
				// unknown action specified
			}

			// output html
			echo get_page_content_html($workingpage, $action == "edit");
		}

		function get_action() {
			if (isset($_GET["action"])) {
				return $_GET["action"];
			} else if (isset($_POST["action"])) {
				return $_POST["action"];
			}
			return "unknown";
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
	?>
</head>
<body>
	<?php
		// EXECUTE
		on_editor_load();
	?>
</body>
</html>