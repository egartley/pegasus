<?php

require_once '../includes/objects/page.php';
require_once '../includes/util.php';

function get_page_dirs($relative) {
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

function num_pages() {
	return count(get_page_dirs(true));
}

function get_page($id) {
	if (file_exists(Page::$storageFilePath . "/" . $id) && is_dir(Page::$storageFilePath . "/" . $id)) {
		// page with that id exists
		return new Page($id);
	} else {
		// id was fine, but there is no page with it
		return null;
	}
}

?>