<?php

require_once '../includes/core/page-storage.php';
require_once '../includes/core/settings.php';

function get_page_list_item_html($page)
{
    check_settings_storage_file(true);
    return "
        <div class=\"listing\">
            <span id=\"title\">{$page->title}</span>
            <span id=\"slug\">{$page->slug}</span><br>
            <span id=\"edit\">
                <a rel=\"noopener\" href=\"" . ApplicationSettings::get_url_permalink_for_slug($page->slug) . "\">View</a>
                <a rel=\"noopener\" href=\"/editor/?action=edit&id={$page->id}\">Edit</a>
                <a rel=\"noopener\" href=\"/editor/?action=delete&id={$page->id}\">Delete</a>
            </span>
        </div>";
}

function get_all_pages_list_html()
{
    $html = "<div class=\"all-pages-list\">";
    $pagedirs = get_page_dirs(true);
    if (count($pagedirs) == 0) {
        $html .= "<div class=\"listing\"><span>No pages found</span></div>";;
    } else {
        foreach ($pagedirs as $id) {
            $page = get_page($id);
            if ($page != null) {
                $html .= get_page_list_item_html($page);
            }
        }
    }
    return $html . "</div>";
}