<?php
require_once '../includes/core/min-header.php';
require_once '../includes/core/page-storage.php';
require_once '../includes/core/page.php';
require_once '../includes/core/settings.php';

if (isset($_POST["action"])) {
    if ($_POST["action"] == "updateslug" && isset($_POST["id"]) && isset($_POST["value"]) && isset($_POST["savemeta"])) {
        $page = get_page($_POST["id"]);
        if ($page === null) {
            echo "There was an issue getting the page with the given ID of \"{$_POST["id"]}\"";
            return;
        }

        if (valid_id($_POST["id"])) {
            // previously saved page, but change its slug
            if ($page->slug === $_POST["value"]) {
                // same slug, no point in "changing" it
                echo "The slug is already set to \"{$_POST["value"]}\"";
                return;
            }
            change_permalink_slug($page->slug, $_POST["value"]);
            remove_permalink($page->slug);
        } else {
            echo "Something is wrong with the page";
            return;
        }
        // actual/meta page slug needs to be updated still!
        echo "Successfully changed the slug to \"{$_POST["value"]}\"";

        if ($_POST["savemeta"] === "yes") {
            $page->slug = $_POST["value"];
            $page->public_write_meta();
        }
    } else if ($_POST["action"] == "updatepermalink" && isset($_POST["value"])) {
        // validate new permalink
        $input = $_POST["value"];
        if (strlen($input) === 0) {
            echo "Error: Must specify at least \"/@SLUG\"";
            return;
        }
        if (strpos($input, " ") !== false) {
            echo "Error: Cannot contain spaces (use \"_\" or \"-\" instead)";
            return;
        }
        if (substr($input, 0, 1) !== "/") {
            echo "Error: Must start with \"/\"";
            return;
        }
        if (substr($input, strlen($input) - 1) === "/") {
            echo "Error: Cannot end with \"/\"";
            return;
        }
        if (!strpos(strtolower($input), "@slug")) {
            echo "Error: Must use the page's slug (\"@SLUG\")";
            return;
        }
        foreach (array("{", "}", "|", "\\", "^", "~", "[", "]", "*", "?", ":", ";", "=") as $invaldchar) {
            if (strpos($input, $invaldchar) !== false) {
                echo "Error: Invalid character(s)";
                return;
            }
        }
        foreach (ApplicationSettings::$protectedDirectories as $protected) {
            // TODO: allow sub dirs of protected dirs (i.e. "/page/dashboard/@SLUG")
            // TODO: allow parital use (i.e. "/dashboardd/@SLUG")
            if (strpos($input, $protected) !== false) {
                echo "Error: Cannot use a protected directory";
                return;
            }
        }
        // looks to be valid, delete current structure and make new
        delete_permalink_structure();
        ApplicationSettings::$permalinkStructure = $input;
        ApplicationSettings::set_values(ApplicationSettings::get_json_string(), true);
        create_permalink_structure();
        echo "Successfully updated the permalink structure";
    } else {
        echo "Please make sure all parameters for the action are correctly supplied";
    }
} else {
    echo "Please specify an action";
}