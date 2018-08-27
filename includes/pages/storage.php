<?php

require_once '../includes/objects/page.php';
require_once '../includes/util.php';

function getPageDirectories($relative) {
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

function numberOfPages() {
	return count(getPageDirectories(true));
}

function getPageByID($id) {
	if (in_array($id, getPageDirectories(true))) {
		// page with that id exists
		return new Page($id, true);
	} else {
		// id was fine, but there is no page with it
		return null;
	}
}

?>