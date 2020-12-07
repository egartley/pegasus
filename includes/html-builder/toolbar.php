<?php

function get_profile_chooser_html()
{
    return "<div class=\"profile-chooser static-icon\"><span id=\"name\">" . $_SESSION["user"]["username"] . "</span><span id=\"icon\"></span></div>";
}

function get_editing_toolbar_html()
{
    return "
<div class=\"toolbar\">
    <div class=\"static-icon\"><span><img alt=\"X\" src=\"/resources/ico/favicon.ico\"></span></div>
    <div class=\"actionable action-back\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/png/back.png\"></span><span id=\"text\">Dashboard</span></div>
    <div class=\"delimiter\"></div>
    
    <div class=\"actionable action-livepage\"><span id=\"icon\"><img class=\"resize\" alt=\"X\" src=\"/resources/svg/redirect.svg\"></span><span id=\"text\">Live Page</span></div>
    <div class=\"delimiter\"></div>
    
    <div class=\"actionable action-save\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/png/save.png\"></span><span id=\"text\">Save</span></div>
    <div class=\"actionable action-options\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/png/gear.png\"></span><span id=\"text\">Options</span></div>
    <div class=\"delimiter\"></div>
    
    <div class=\"actionable action-newsection\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/svg/plus.svg\"></span><span id=\"text\">Section</span></div>
    <div class=\"actionable action-newlist\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/svg/list.svg\"></span><span id=\"text\">List</span></div>
    <div class=\"actionable action-addlink\"><span id=\"icon\"><img class=\"resize\" alt=\"X\" src=\"/resources/svg/link.svg\"></span><span id=\"text\">Link</span></div>
    <div class=\"delimiter\"></div>
    
    <div class=\"actionable action-addinfoboxsubheading\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/svg/plus.svg\"></span><span id=\"text\">Heading</span></div>
    <div class=\"actionable action-addinfoboxproperty\"><span id=\"icon\"><img alt=\"X\" src=\"/resources/svg/plus.svg\"></span><span id=\"text\">Property</span></div>
    <div class=\"delimiter\"></div>
    
    <div class=\"toolbar-status\"><span>Ready</span></div>
    <div class=\"toolbar-spinner hidden\"></div>" . get_profile_chooser_html() . "
</div>";
}

function get_generic_toolbar_html($current)
{
    $links = array("Dashboard", "Settings");
    $html = "
<div class=\"toolbar\">
    <div class=\"static-icon\"><span><img alt=\"X\" src=\"/resources/ico/favicon.ico\"></span></div>";
    foreach ($links as $link) {
        $html .= "<div class=\"general-link\"><span";
        if ($current === $link) {
            $html .= " id=\"current\"";
        }
        $html .= "><a href=\"/" . strtolower($link) . "/\">" . $link . "</a></span></div>
";
    }
    return $html . get_profile_chooser_html() . "
</div>";
}