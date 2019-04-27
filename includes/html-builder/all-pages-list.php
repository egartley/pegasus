<?php

require_once '../includes/core/page-storage.php';

function get_page_list_item_html($page)
{
    return "
        <div class=\"listing\">
            <span id=\"title\">{$page->title}</span>
            <span id=\"slug\">{$page->slug}</span><br>
            <span id=\"edit\">
                <a rel=\"noopener\" href=\"" . Page::$publishedFilePath . "{$page->slug}\">View</a>
                <a rel=\"noopener\" href=\"/editor/?action=edit&id={$page->id}\">Edit</a>
                <a rel=\"noopener\" href=\"/editor/?action=delete&id={$page->id}\">Delete</a>
            </span>
        </div>";
}

function get_all_pages_list_html()
{
    $r = "<div class=\"all-pages-list\">";
    $pagedirs = get_page_dirs(true);
    if (count($pagedirs) == 0) {
        $r .= "<div class=\"listing\"><span>No pages found</span></div>";;
    } else {
        foreach ($pagedirs as $currentpageid) {
            $currentpage = get_page($currentpageid);
            if ($currentpage != null) {
                $r .= get_page_list_item_html($currentpage);
            }
        }
    }
    return $r . "</div>";
}