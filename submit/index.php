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
        // TODO: validate new permalink value
        check_settings_storage_file(true);
        ApplicationSettings::$permalinkStructure = $_POST["value"];
        ApplicationSettings::set_values(ApplicationSettings::get_json_string(), true);

        update_permalink_structure($_POST["value"]);
        // TODO: remove old structure?

        echo "Successfully updated the permalink structure";
    } else {
        echo "Please make sure all parameters for the action are correctly supplied";
    }
} else {
    echo "Please specify an action";
}