<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    require_once '../includes/html-builder/toolbar.php';
    require_once '../includes/core/settings.php';

    check_settings_storage_file(true);

    get_stylesheet_link("toolbar.css");
    get_stylesheet_link("application.css");
    ?>
    <title>Settings</title>
    <script src="/resources/js/jquery.js" type="application/javascript"></script>
    <script src="/resources/js/settings.js" type="application/javascript"></script>
    <script src="/resources/js/general.js" type="application/javascript"></script>
</head>
<body>
<?php
echo get_generic_toolbar_html("Settings")
?>
<span class="hidden" id="onloadpermalink"><?php echo ApplicationSettings::$permalinkStructure ?></span>
<div class="outter">
    <div class="block-container">
        <div class="base-dialog-modal">
            <div class="dialog-subtitle">
                <span>Permalink Structure</span>
            </div>
            <div class="dialog-content">
                <div class="textbox-container">
                    <input type="text" id="permalinktextbox" autocomplete="off" max="2000" placeholder="/example/@SLUG">
                </div>
                <div>
                    <span>Pages are publicly accessible by their permalink, which must include their slug with "@SLUG". Additional variables can be used.</span>
                </div>
                <div>
                    @TITLE, @ID
                </div>
                <div>
                    <button id="permalinkapply">Apply</button>
                </div>
                <div>
                    <span id="permalinkapplystatustext"></span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>