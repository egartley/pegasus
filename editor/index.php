<!DOCTYPE html>
<head>
    <?php
        require_once '../includes/core/min-header.php';
        require_once '../includes/core/page-storage.php';
        require_once '../includes/html-builder/toolbar.php';
        require_once '../includes/html-builder/page-content.php';
        require_once '../includes/core/page.php';

        require_once "../includes/core/check-auth.php";

        get_stylesheet("toolbar.css");
        get_stylesheet("page-content.css");

        function get_action()
        {
            if (isset($_GET["action"])) {
                return $_GET["action"];
            } else if (isset($_POST["action"])) {
                return $_POST["action"];
            }
            return "unknown";
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
        ?>
    </title>
</head>
<body>
<?php
    $action = get_action();
    $page = null;
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
        $page = get_page($_GET["id"]);
        if ($page == null) {
            // there is not a page by that id
            echo "<p>ERROR: No page with ID of " . $_GET["id"] . " (make a <a rel=\"noopener\" href=\"/editor/?action=new\">new page</a>)</p>";
            return;
        }
    } else if ($action == "new") {
        // ex. "/editor/?action=new" (GET)
        header("Location: /editor/?action=edit&id=" . Page::new_page()->id);
        return;
    } else if ($action == "save") {
        // ex. "/editor/?action=save&id=2&contentjson=..." (POST)
        if ($valid !== true) {
            echo "<p>" . $valid . "</p>";
            return;
        }
        Page::action_save($_POST);
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
        return;
    }

    // write html
    echo get_page_content_html($page, $action == "edit");
?>
</body>