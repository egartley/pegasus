<?php

function get_editing_toolbar_html() {
	return "
<div class=\"toolbar\">
    <div class=\"static-icon\"><span><img src=\"/resources/ico/favicon.ico\"></span></div>
    <div class=\"actionable action-back\"><span id=\"icon\"><img src=\"/resources/png/back.png\"></span><span id=\"text\">Dashboard</span></div>
    <div class=\"delimiter\"></div>
    <div class=\"actionable action-save\"><span id=\"icon\"><img src=\"/resources/png/check.png\"></span><span id=\"text\">Save</span></div>
    <div class=\"actionable action-options\"><span id=\"icon\"><img src=\"/resources/png/gear.png\"></span><span id=\"text\">Options</span></div>
    <div class=\"delimiter\"></div>
    <div class=\"actionable action-newsection\"><span id=\"icon\"><img src=\"/resources/png/plus.png\"></span><span id=\"text\">Section</span></div>
    <div class=\"actionable action-newparagraph\"><span id=\"icon\"><img src=\"/resources/png/paragraph.png\"></span><span id=\"text\">Paragraph</span></div>
    <div class=\"actionable action-newlist\"><span id=\"icon\"><img src=\"/resources/png/list.png\"></span><span id=\"text\">List</span></div>
    <div class=\"toolbar-status\"><span>Ready</span></div>
    <div class=\"toolbar-spinner hidden\"></div>
</div>";
}

function get_dashboard_toolbar_html() {
	return "
<div class=\"toolbar\">
    <div class=\"static-icon\"><span><img src=\"/resources/ico/favicon.ico\"></span></div>
    <!-- <div class=\"static-text\"><span>Dashboard</span></div> -->
</div>";
}

?>