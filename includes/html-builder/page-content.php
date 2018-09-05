<?php

function page_content_html($content, $page, $edit) {
	// inline styling for now
	$r = "<style type=\"text/css\">";
	if (file_exists("../resources/css/page-content.css")) {
		$r .= file_get_contents("../resources/css/page-content.css");
	} else {
		// css file no found!
		$r .= "div.page-content:before{content:\"Could not find \\\"/resources/page-content.css\\\"\"}";
	}
	$r .= "</style><script src=\"../resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"../resources/js/viewer-edit.js\" type=\"text/javascript\"></script><button class=\"save-changes\">Save Changes</button><div class=\"page-content\"><div class=\"content\">";
	// page title
	$r .= "<div class=\"module page-title\" contenteditable=\"true\">" . $page->title . "</div>";
	// modules (paragraphs, inline images, data tables, etc.)
	$moduleindex = 0;
	foreach ($content["modules"] as $module) {
		$r .= "<div class=\"module mod-" . $moduleindex . " ";
		// content of module div
		if ($module["type"] == "paragraph-container") {
			$r .= "paragraph-container\">";
			foreach ($module["value"] as $submodule) {
				$r .= "<div contenteditable=\"true\" class=\"sub-module ";
				// content of sub-module div
				if ($submodule["type"] == "paragraph") {
					$r .= "paragraph\">";
					foreach ($submodule["value"] as $pmodule) {
						if ($pmodule["type"] == "text") {
							$r .= $pmodule["value"];
						} else {
							$r .= "Unknown type!";
						}
					}
				} else {
					$r .= "\">Unknown type!";
				}
				// end sub-module div
				$r .= "</div>";
			}
		} else if($module["type"] == "heading") {
			$r .= "heading\" contenteditable=\"true\">" . $module["value"];
			/*if ($edit) {
				$r .= "<sup class=\"edit\">Edit</sup>";
			}*/
		} else {
			$r .= "\">Unknown type!";
		}
		// end module div
		$r .= "</div>";
		$moduleindex++;
	}
	// footer (hardcoded for now)
	$r .= "<div class=\"module footer\">Copyright 2018</div>";
	// end modules
	$r .= "</div>";

	// infobox
	$infobox = $content["infobox"];
	$r .= "<table class=\"infobox\"><tbody><tr class=\"heading\"><td colspan=\"2\"><div class=\"bold-text\">" . $infobox["heading"] . "</div></td></tr><tr class=\"main-image\"><td colspan=\"2\"><span class=\"flex-centered\"><table><tbody><tr><td><img src=\"" . $infobox["main-image"]["file"] . "\"></td></tr><tr><td class=\"small-text\" id=\"caption\">" . $infobox["main-image"]["caption"] . "</td></tr></tbody></table></span></td></tr>";
	// properties
	foreach ($infobox["items"] as $item) {
		$r .= "<tr class=\"";
		if ($item["type"] == "property") {
			$r .= "property\"><th>" . $item["label"] . "</th><td id=\"value\">" . $item["value"] . "</td>";
		} else if ($item["type"] == "sub-heading") {
			$r .= "sub-heading\"><td colspan=\"2\"><div class=\"bold-text\">" . $item["value"] . "</div></td>";
		} else {
			$r .= "\"><td>Unknown type!</td>";
		}
		$r .= "</tr>";
	}

	return $r . "</tbody></table></div>";
} 

function get_page_content_html($page, $edit) {
	$rawjsonstring = $page->get_content_rawjson();
	if ($rawjsonstring != null) {
		// O.K. in getting raw json from content.json
		return page_content_html(json_decode($rawjsonstring, true), $page, $edit);
	} else {
		// could not get raw json
		return "Could not retrieve content HTML (make sure the page exists and has content)";
	}
}

?>