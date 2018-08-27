<?php

require_once '../includes/util.php';

function numberOfPages() {
	$num = 0;
	// http://php.net/manual/en/function.readdir.php
	if ($handle = opendir("../data-storage/pages")) {
    	while (false !== ($entry = readdir($handle))) {
    		if ($entry != "." && $entry != ".." && is_dir("../data-storage/pages/" . $entry)) {
    			$num++;
    		}
    	}
    	closedir($handle);
	}
	return $num;
}

function getPageByID($id) {
	if (directoryExists("../data-storage/pages/" . $id)) {
		// page with that id exists
		return new Page($id, true);
	} else {
		// id was fine, but there is no page with it
		return null;
	}
}

?>