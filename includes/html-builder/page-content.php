<?php

function contentHTML($content) {
	$r = "";
	foreach ($content["infobox"]["items"] as $item) {
		$r .= $item["value"] . ", ";
	}
	return $r;
} 

function getPageContentHTML($page) {
	$rawjsonstring = $page->getContentJSON();
	if ($rawjsonstring != null) {
		// O.K. in getting raw json from content.json
		return contentHTML(json_decode($rawjsonstring, true));
	} else {
		// could not get raw json
		return "";
	}
}

?>