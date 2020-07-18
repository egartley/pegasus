<?php

/**
 * Returns HTML for page content, with editing capabilities if specified
 *
 * @param $content array Content in a JSON array
 * @param $page Page The page to get meta from
 * @param $edit bool Whether or not the content is editable
 * @return string Semi-formatted HTML for displaying the page and its contents
 */
function page_content_html(array $content, Page $page, bool $edit)
{
    // script
    $html = "
<script src=\"/resources/js/jquery.js\" type=\"application/javascript\"></script>
<script src=\"/resources/js/page.js\" type=\"application/javascript\"></script>";
    if ($edit) {
        $html .= "
<script src=\"/resources/js/editor.js\" type=\"application/javascript\"></script>";
    }

    // meta/other
    $html .= "
<span class=\"hidden\" id=\"hiddenpageid\">{$page->id}</span>
<span class=\"hidden\" id=\"hiddenpageslug\">{$page->slug}</span>
<span class=\"hidden\" id=\"hiddenedit\">{$edit}</span>
<span class=\"hidden\" id=\"hiddenpagelivepath\">" . ApplicationSettings::get_url_permalink_for_slug($page->slug) . "</span>";

    // toolbar when editing
    if ($edit) {
        $html .= get_editing_toolbar_html();
    }

    // modals/hoverers/etc.
    if ($edit) {
        $html .= "
<div class=\"base-modal link-modal hidden\">
    <div class=\"link-dialog base-dialog-modal base-dialog\">
        <div class=\"dialog-title\">
            <span>New Link</span>
        </div>
        <div class=\"dialog-content\">
            <div class=\"textbox-container\">
                <span id=\"text\">Link to:</span>
                <input type=\"text\" autocomplete=\"off\" max=\"2000\" placeholder=\"http://example.com\">
            </div>
            <button class=\"insert-link\">Insert</button>
        </div>
    </div>
</div>
<div class=\"base-modal options-modal hidden\">
    <div class=\"options-dialog base-dialog-modal base-dialog\">
        <div class=\"dialog-title\">
            <span>Options</span>
        </div>
        <div class=\"dialog-content\">
            <div class=\"textbox-container\">
                <span id=\"text\">URL slug:</span>
                <input type=\"text\" spellcheck=\"false\" id=\"sluginput\" autocomplete=\"off\" max=\"512\" placeholder=\"Untitled_Page\">
            </div>
            <button id=\"slugapply\">Apply</button>
            <span class=\"statustext\" id=\"slugstatustext\"></span>
        </div>
    </div>
</div>";
    }
    $html .= "
<div class=\"link-hoverer base-dialog-modal hidden\" tabindex=\"-1\">
    <span>
        <input type=\"text\" id=\"linkURL\" autocomplete=\"off\" max=\"2000\" placeholder=\"http://example.com\">
        <button id=\"apply\" class=\"small-button bold-text\">Apply</button>
    </span>
    <span style=\"margin-top:4px\">
        <input type=\"checkbox\" id=\"newtab\"><label for=\"newtab\">New tab</label>
        <button class=\"content-only small-button\" id=\"remove\">Remove</button>
    </span>
</div>";

    // start of actual content
    $html .= "
<div class=\"page-content\">
    <div class=\"content\">";
    $html .= "<div class=\"module page-title\"";
    if ($edit) {
        $html .= " contenteditable=\"true\"";
    }
    $html .= ">{$page->title}</div>";

    // modules (paragraphs, inline images, data tables, etc.)
    foreach ($content["modules"] as $module) {
        $html .= "<div class=\"module ";
        // content of module div
        if ($module["type"] == "section-content") {
            // paragraph container
            $html .= "section-content\">";
            foreach ($module["value"] as $submodule) {
                $html .= "<div class=\"sub-module ";
                if ($submodule["type"] == "paragraph") {
                    // paragraph
                    $html .= "paragraph\">";
                    foreach ($submodule["value"] as $pmodule) {
                        // start element div
                        $ptype = $pmodule["type"];
                        $html .= "<span class=\"e {$ptype}\"";
                        if ($edit) {
                            $html .= " contenteditable=\"true\"";
                        }
                        if ($ptype == "text" || $ptype == "plain") {
                            $html .= ">" . $pmodule["value"];
                        } else {
                            $html .= "Unknown type!";
                        }
                        // end element div
                        $html .= "</span>";
                    }
                } else if ($submodule["type"] == "list") {
                    // list
                    $html .= "list\"><ul>";
                    foreach ($submodule["value"] as $listitem) {
                        $html .= "<li id=\"list-item\"";
                        if ($edit) {
                            $html .= " contenteditable=\"true\"";
                        }
                        $html .= ">{$listitem}</li>";
                    }
                    $html .= "</ul>";
                } else {
                    $html .= "\">Unknown type!";
                }
                // end sub-module of paragraph container module
                $html .= "</div>";
            }
        } else if ($module["type"] == "heading") {
            // section heading
            $html .= "heading\"><span";
            if ($edit) {
                $html .= " contenteditable=\"true\"";
            }
            $html .= ">{$module["value"]}</span>";
            if ($edit) {
                $html .= "<span id=\"removesection\"><img src=\"../resources/png/trash.png\" alt=\"[X]\" title=\"Remove this section\"></span>";
            }
        } else {
            $html .= "\">Unknown module type!";
        }
        // end module
        $html .= "</div>";
    }
    // footer (hardcoded for now)
    $html .= "
    <div class=\"module footer small-text\"><b>Last updated:</b>  {$page->asPrettyString_updated()}<br><b>Published:</b>  {$page->asPrettyString_created()}</div>";
    // end all modules
    $html .= "
    </div>";

    // infobox
    $infobox = $content["infobox"];
    $html .= "
    <table class=\"infobox base-dialog-modal\"><tbody><tr class=\"heading\"><td colspan=\"2\"><div class=\"bold-text\"";
    if ($edit) {
        $html .= " contenteditable=\"true\"";
    }
    // heading/title and main image
    $html .= ">{$infobox["heading"]}</div></td></tr><tr class=\"main-image\"><td colspan=\"2\"><span class=\"flex-centered\"><table><tbody><tr><td><img alt=\"image\" src=\"{$infobox["image"]["file"]}\"></td></tr><tr><td class=\"small-text\" id=\"caption\"";
    if ($edit) {
        $html .= " contenteditable=\"true\"";
    }
    // main image caption
    $html .= ">{$infobox["image"]["caption"]}</td></tr></tbody></table></span></td></tr>";
    // properties & sub-headings
    foreach ($infobox["items"] as $item) {
        $html .= "<tr class=\"";
        if ($item["type"] == "property") {
            $html .= "property\"><th";
            if ($edit) {
                $html .= " contenteditable=\"true\"";
            }
            $html .= ">{$item["label"]}</th><td id=\"value\"";
            if ($edit) {
                $html .= " contenteditable=\"true\"";
            }
            $html .= ">{$item["value"]}</td>";
        } else if ($item["type"] == "sub-heading") {
            $html .= "sub-heading\"><td colspan=\"2\"><span class=\"bold-text\"";
            if ($edit) {
                $html .= " contenteditable=\"true\"";
            }
            $html .= ">{$item["value"]}</span></td>";
        } else {
            $html .= "\"><td>Unknown type!</td>";
        }
        $html .= "</tr>";
    }

    return $html . "</tbody>
    </table>
</div>";
}

/**
 * Returns HTML for just the page content, and nothing else
 *
 * @param $page
 * @param $edit
 * @return string
 */
function get_page_content_html(Page $page, bool $edit)
{
    if ($page == null) {
        return "The page supplied to get_page_content_html was null";
    }
    $rawjsonstring = $page->get_content_rawjson();
    if ($rawjsonstring != null) {
        return page_content_html(json_decode($rawjsonstring, true), $page, $edit);
    } else {
        return "Could not get content HTML (make sure the page exists and has content)";
    }
}