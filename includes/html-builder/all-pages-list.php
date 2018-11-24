<?php

require_once '../includes/core/page-storage.php';

function get_page_list_item_html($page)
{
    return "<div class=\"listing\"><span><a rel=\"noopener\" href=\"/editor/?action=edit&id=" . $page->id . "\">" . $page->title . "</a></span></div>";
}

function get_all_pages_list_html()
{
    $r = "<div class=\"all-pages-list-scroller\"><div class=\"all-pages-list\">";
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
    return $r . "</div></div>";
}