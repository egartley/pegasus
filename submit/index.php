<?php
require_once '../includes/core/min-header.php';
require_once '../includes/core/page-storage.php';
require_once '../includes/objects/page.php';

function on_load()
{
    if (isset($_POST["action"])) {
        if ($_POST["action"] == "updateslug" && isset($_POST["id"]) && isset($_POST["value"]) && isset($_POST["isnew"])) {
            if ($_POST["isnew"] == "yes") {
                add_slug(get_page($_POST["id"]), $_POST["value"]);
            } else if (valid_id($_POST["id"])) {
                copy_slug(get_page($_POST["id"])->slug, $_POST["value"]);
                remove_slug(get_page($_POST["id"])->slug);
            }
        } else {
            echo "There was a problem...";
        }
    } else {
        echo "Please specify an action";
    }
}

?>

<body>
<?php
on_load();
?>
</body>
