<?php

require_once '../includes/core/page.php';
require_once '../includes/core/settings.php';

function remove_permalink(string $slug)
{
    // move to trash/recyle bin instead of permanent?
    remove_directory(ApplicationSettings::get_php_permalink_for_slug($slug));
}

function add_permalink(Page $page)
{
    $slug = $page->slug;
    if (file_exists(ApplicationSettings::get_php_permalink_for_slug($slug))) {
        // already exists, don't want to overwrite
        return;
    }

    // actually make the permalink directory
    mkdir(ApplicationSettings::get_php_permalink_for_slug($slug), 0755, true);

    $page->write_permalink_index_html($slug);
}

function change_permalink_slug(string $oldslug, string $newslug)
{
    // make the new directory
    mkdir(ApplicationSettings::get_php_permalink_for_slug($newslug));
    // copy old to new
    recurse_copy(ApplicationSettings::get_php_permalink_for_slug($oldslug), ApplicationSettings::get_php_permalink_for_slug($newslug));
}

function delete_permalink_structure()
{
    $currentpermalink = ApplicationSettings::get_url_permalink_for_slug("foo");
    if ($currentpermalink !== "/foo") {
        // not "/@SLUG", so find the permalink's "first" directory
        // for example, it would be "page" for "/page/@SLUG"
        $currentpermalink = substr($currentpermalink, 1, strpos(substr($currentpermalink, 1), "/"));
    }
    // else, permalink structure is "/@SLUG", so it would
    remove_directory("../" . $currentpermalink);
}

function create_permalink_structure()
{
    foreach (get_page_dirs(true) as $id) {
        add_permalink(get_page($id));
    }
}

// Credit: https://stackoverflow.com/a/3338133
function remove_directory($directory)
{
    if (is_dir($directory)) {
        $contents = scandir($directory);
        foreach ($contents as $file) {
            if ($file != "." && $file != "..") {
                if (is_dir($directory . "/" . $file))
                    remove_directory($directory . "/" . $file);
                else
                    unlink($directory . "/" . $file);
            }
        }
        rmdir($directory);
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