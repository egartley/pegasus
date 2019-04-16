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

    function get_action()
    {
        if (isset($_GET["action"])) {
            return $_GET["action"];
        } else if (isset($_POST["action"])) {
            return $_POST["action"];
        }
        return "unknown";
    }

    function on_editor_load()
    {
        $action = get_action();
        $workingpage = null;
        $valid = null;
        if (isset($_GET["id"])) {
            $valid = valid_id($_GET["id"]);
        } else if (isset($_POST["id"])) {
            $valid = valid_id($_POST["id"]);
        }
        // check for url params
        if ($action !== "new" && ($action === "unknown" || $valid === null)) {
            echo "<p>You must specify a page ID and/or action</p>";
            return;
        }

        if ($action == "edit") {
            // ex. "/editor/?action=edit&id=0" (GET)
            if ($valid !== true) {
                echo "<p>" . $valid . "</p>";
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
            // ex. "/editor/?action=save&id=2&isnew=no&contentjson=..." (POST or GET)
            if ($_GET["isnew"] == "yes") {
                // not previously saved, need to move from temp to normal
                $newpost = array(
                    "isnew" => "yes",
                    "id" => $_GET["id"],
                    "slug" => $_GET["slug"],
                    "contentjson" => Page::$emptyContentRawJSON,
                    "title" => Page::$defaultTitle
                );
                if (Page::action_save($newpost)) {
                    header("Location: /editor/?action=edit&id=" . $newpost["id"]);
                } else {
                    echo "<p>Something went wrong while trying to save the page</p>";
                }
            }
            if ($valid !== true) {
                echo "<p>" . $valid . "</p>";
                return;
            }
            // was previously saved
            if (Page::action_save($_POST)) {
                $myid = $_GET["id"];
                if ($myid == "") {
                    $myid = $_POST["id"];
                }
                header("Location: /editor/?action=edit&id=" . $myid);
            } else {
                echo "<p>Something went wrong while trying to save the page</p>";
            }
            return;
        } else if ($action == "delete") {
            // ex. "/editor/?action=delete&id=2" (GET)
            if ($valid !== true) {
                echo "<p>" . $valid . "</p>";
                return;
            }
            if (Page::action_delete($_GET["id"])) {
                header("Location: /dashboard/");
            } else {
                echo "<p>Something went wrong while trying to delete the page</p>";
            }
            // end here so that no html is written
            return;
        } else {
            echo "<p>Unknown action specified (\"{$action}\")</p>";
        }

        // write html
        echo get_page_content_html($workingpage, $action == "edit");
    }


    ?>
    <title>
        <?php
            if (get_action() == "edit") {
                echo "Editing \"" . get_page($_GET["id"])->title . "\"";
            } else if (get_action() == "new") {
                echo "New Page";
            } else {
                echo "Working...";
            }
        echo " - Pegasus"; ?>
    </title>
</head>
<body>
    <?php on_editor_load(); ?>
</body>