<?php

function contentHTML($content, $page) {
	// inline styling for now
	$r = "<style type=\"text/css\">";
	if (file_exists("../resources/css/page-content.css")) {
		$r .= file_get_contents("../resources/css/page-content.css");
	} else {
		// css file no found!
		$r .= "div.page-content-container:before{content:\"Could not find the CSS file!\"}";
	}
	$r .= "</style><div class=\"page-content-container\"><div class=\"container\"><div class=\"page-content\">";
	// page title
	$r .= "<div class=\"module page-title\">" . $page->title . "</div>";
	// modules (paragraphs, inline images, data tables, etc.)
	foreach ($content["modules"] as $module) {
		$r .= "<div class=\"module ";
		// content of module div
		if ($module["type"] == "paragraph-container") {
			$r .= "paragraph-container\">";
			foreach ($module["value"] as $submodule) {
				$r .= "<div class=\"sub-module ";
				// content of sub-module paragraph div
				if ($submodule["type"] == "paragraph") {
					$r .= "paragraph\">";
					foreach ($submodule["value"] as $pmodule) {
						if ($pmodule["type"] == "text") {
							$r .= $pmodule["value"];
						}
					}
				}
				// end sub-module paragraph div
				$r .= "</div>";
			}
		} else if($module["type"] == "heading") {
			$r .= "heading\">" . $module["value"];
		} else {
			$r .= "\">";
		}
		// end module div
		$r .= "</div>";
	}

	return $r . "</div></div></div>";
} 

function getPageContentHTML($page) {
	$rawjsonstring = $page->getContentJSON();
	if ($rawjsonstring != null) {
		// O.K. in getting raw json from content.json
		return contentHTML(json_decode($rawjsonstring, true), $page);
	} else {
		// could not get raw json
		return "";
	}
}

?>