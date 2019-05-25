<?php
require_once '../includes/core/min-header.php';
require_once '../includes/core/page-storage.php';
require_once '../includes/core/page.php';
require_once '../includes/core/settings.php';

if (isset($_POST["action"])) {
    if ($_POST["action"] == "updateslug" && isset($_POST["id"]) && isset($_POST["value"]) && isset($_POST["savemeta"])) {
        $workingpage = get_page($_POST["id"]);
        if ($workingpage === null) {
            echo "There was an issue getting the page with the given ID of \"{$_POST["id"]}\"";
            return;
        }

        if (valid_id($_POST["id"])) {
            // previously saved page, but change its slug
            if ($workingpage->slug === $_POST["value"]) {
                // same slug, no point in "changing" it
                echo "The slug is already set to \"{$_POST["value"]}\"";
                return;
            }
            change_permalink_slug($workingpage->slug, $_POST["value"]);
            remove_permalink($workingpage->slug);
        } else {
            echo "Something is wrong with the page";
            return;
        }
        // actual/meta page slug needs to be updated still!
        echo "Successfully changed the slug to \"{$_POST["value"]}\"";

        if ($_POST["savemeta"] === "yes") {
            $workingpage->slug = $_POST["value"];
            $workingpage->public_write_meta();
        }
    } else if ($_POST["action"] == "updatepermalink" && isset($_POST["value"])) {
        delete_permalink_structure();
        // validate new permalink
        $input = $_POST["value"];

        if (strlen($input) === 0) {
            echo "Error: You must specify at least the slug";
            return;
        }
        if (strpos($input, " ") !== false) {
            echo "Error: Do not include any spaces (use \"_\" or \"-\" instead)";
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
                echo "Error: Unavailable directory";
                return;
            }
        }
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