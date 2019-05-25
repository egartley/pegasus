<?php

require_once '../includes/core/page.php';
require_once '../includes/core/settings.php';

function remove_permalink(string $slug)
{
    // move to trash/recyle bin instead of permanent?
    $dir = opendir(ApplicationSettings::get_php_permalink_for_slug($slug));
    while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            unlink(ApplicationSettings::get_php_permalink_for_slug($slug) . "/" . $file);
        }
    }
    // directory should now be empty, so it should be deleted successfully
    closedir($dir);
    return rmdir(ApplicationSettings::get_php_permalink_for_slug($slug));
}

function add_permalink(Page $page, string $slug = "")
{
    $custom = true;
    if ($slug === "") {
        // use page's slug if not specified otherwise
        $slug = $page->slug;
        $custom = false;
    }
    if (file_exists(ApplicationSettings::get_php_permalink_for_slug($slug))) {
        // already exists, don't want to overwrite
        return false;
    }

    // actually make the permalink directory
    mkdir(ApplicationSettings::get_php_permalink_for_slug($slug), 0755, true);

    // check what slug to use
    if ($custom) {
        // using a different slug than the page's slug
        return $page->write_permalink_index_html($slug);
    }

    // using the page's own slug
    return $page->write_permalink_index_html();
}

function change_permalink_slug(string $oldslug, string $newslug)
{
    // copy page from oldslug to newslug
    // ex. "My_Page_1" to "My_Page_2"
    if (file_exists(ApplicationSettings::get_php_permalink_for_slug($newslug))
        || !file_exists(ApplicationSettings::get_php_permalink_for_slug($oldslug))) {
        // new permalink already exists or the old permalink does not
        return;
    }

    // make the new directory
    mkdir(ApplicationSettings::get_php_permalink_for_slug($newslug));
    // copy old to new
    recurse_copy(ApplicationSettings::get_php_permalink_for_slug($oldslug), ApplicationSettings::get_php_permalink_for_slug($newslug));
}

function update_permalink_structure($newpermalink)
{
    // assuming that the new permalink structure is valid and not the same as the current one
    foreach (get_page_dirs(true) as $id) {
        add_permalink(get_page($id));
    }
}

function valid_id($checkid)
{
    if (!isset($checkid)) {
        return "Page ID not specified";
    } else if (!is_numeric($checkid)) {
        return "Page ID must be a number";
    } else if ($checkid <= -1 || $checkid >= Page::$maxNumberOfPages) {
        return "Page ID must be between 0 and " . (Page::$maxNumberOfPages - 1) . ", inclusive";
    }
    return true;
}

// Credit: https://stackoverflow.com/a/2050909
function recurse_copy(string $from, string $to)
{
    $dir = opendir($from);
    if ($dir === false) {
        // not a directory or does not exist
        return;
    }
    while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            if (is_dir($from . '/' . $file)) {
                recurse_copy($from . '/' . $file, $to . '/' . $file);
            } else {
                copy($from . '/' . $file, $to . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function get_page_dirs(bool $relative)
{
    $r = [];
    if ($handle = opendir(Page::$storageFilePath)) {
        while (false !== ($entry = readdir($handle))) {
            $path = Page::$storageFilePath . "/" . $entry;
            if ($entry != "." && $entry != ".." && is_dir($path)) {
                if ($relative) {
                    $r[] = $entry;
                } else {
                    $r[] = $path;
                }
            }
        }
        closedir($handle);
    } else {
        // something went wrong
    }
    return $r;
}

function num_pages()
{
    return count(get_page_dirs(true));
}

function get_page($id)
{
    if (file_exists(Page::$storageFilePath . "/" . $id) && is_dir(Page::$storageFilePath . "/" . $id)) {
        // page with that id exists
        return new Page($id);
    } else {
        // id is valid, but there is no page with it
        return null;
    }
}