<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    require_once '../includes/html-builder/toolbar.php';
    require_once '../includes/core/settings.php';

    settings_check(true);

    get_stylesheet_link("toolbar.css");
    get_stylesheet_link("application.css");
    ?>
    <title>Settings</title>
    <script src="/resources/js/jquery.js" type="application/javascript"></script>
    <script src="/resources/js/settings.js" type="application/javascript"></script>
    <script src="/resources/js/general.js" type="application/javascript"></script>
    <style type="text/css">
        div.description > span {
            color: #555555;
            font-size: 14px
        }

        div.section-title:not(:first-child) {
            margin-top: 32px
        }

        div.section-title > span {
            font-size: 16px
        }
    </style>
</head>
<body>
<?php
echo get_generic_toolbar_html("Settings")
?>
<span class="hidden" id="onloadpermalink"><?php echo ApplicationSettings::$permalinkStructure ?></span>
<div class="outter">
    <div class="block-container">
        <div class="base-dialog-modal">
            <div class="dialog-title">
                <span>General</span>
            </div>
            <div class="dialog-content">
                <div class="section-title">
                    <span>Permalink Structure</span>
                </div>
                <div class="textbox-container">
                    <input type="text" id="permalinktextbox" spellcheck="false" autocomplete="off" max="2000">
                </div>
                <div class="description">
                    <span>Pages are publicly accessible by their permalink, which must include their slug with "@SLUG". Additional variables cannot be used.</span>
                </div>
                <div>
                    <button id="permalinkapply">Apply</button>
                    <span class="statustext" id="permalinkapplystatustext"></span>
                    <span class="statustext error" id="permalinkapplyerrortext"></span>
                </div>
                <div class="section-title">
                    <span>Theme (Disabled)</span>
                </div>
                <div>
                    <div class="switcher">
                        <span id="light">Light</span>
                        <span class="selected" id="dark">Dark</span>
                    </div>
                </div>
                <div class="description">
                    <span>Choose a theme to be used. This affects all pages.</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>