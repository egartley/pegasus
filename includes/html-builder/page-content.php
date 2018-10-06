<?php

function page_content_html($content, $page, $edit) {
	// styling
	$html = "<style type=\"text/css\">";
	if (file_exists("../resources/css/page-content.css")) {
		$html .= file_get_contents("../resources/css/page-content.css");
	} else {
		// css file no found!
		$html .= "div.page-content:before{content:\"Could not find \\\"/resources/page-content.css\\\"\"}";
	}
	$html .= "</style><script src=\"../resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"../resources/js/viewer.js\" type=\"text/javascript\"></script><button class=\"save-changes\">Save Changes</button><span style=\"display:none\" id=\"hiddenpageid\">" . $page->id . "</span><span style=\"display:none\" id=\"hiddenpageisnew\">" . $page->isnew . "</span><div class=\"page-content\"><div class=\"content\">";
	// page title
	$html .= "<div class=\"module page-title\"";
	if ($edit) {
		$html .= " contenteditable=\"true\"";
	}
	$html .= ">" . $page->title . "</div>";

	// modules (paragraphs, inline images, data tables, etc.)
	foreach ($content["modules"] as $module) {
		$html .= "<div class=\"module ";
		// content of module div
		if ($module["type"] == "paragraph-container") {
			$html .= "paragraph-container\">";
			$submoduleindex = 0;
			foreach ($module["value"] as $submodule) {
				$html .= "<div ";
				if ($edit) {
					$html .= "contenteditable=\"true\" ";
				}
				$html .= "class=\"sub-module ";
				if ($submodule["type"] == "paragraph") {
					$html .= "paragraph\">";
					foreach ($submodule["value"] as $pmodule) {
						if ($pmodule["type"] == "text") {
							$html .= $pmodule["value"];
						} else {
							$html .= "Unknown type!";
						}
					}
				} else {
					$html .= "\">Unknown type!";
				}
				// end sub-module div
				$html .= "</div>";
				if ($submoduleindex == count($module["value"]) - 1) {
					// last module in paragraph container
					$html .= "<div class=\"add-content-container\"><button class=\"add-paragraph\">Add Paragraph</button><br><button class=\"new-section\">New Section</button></div>";
				}
				$submoduleindex++;
			}
		} else if($module["type"] == "heading") {
			$html .= "heading\"";
			if ($edit) {
				$html .= " contenteditable=\"true\"";
			}
			$html .= ">" . $module["value"];
		} else {
			$html .= "\">Unknown type!";
		}
		// end module div
		$html .= "</div>";
	}
	// footer (hardcoded for now)
	$html .= "<div class=\"module footer\">Copyright 2018</div>";
	// end modules
	$html .= "</div>";

	// infobox
	$infobox = $content["infobox"];
	$html .= "<table class=\"infobox\"><tbody><tr class=\"heading\"><td colspan=\"2\"><div class=\"bold-text\">" . $infobox["heading"] . "</div></td></tr><tr class=\"main-image\"><td colspan=\"2\"><span class=\"flex-centered\"><table><tbody><tr><td><img src=\"" . $infobox["image"]["file"] . "\"></td></tr><tr><td class=\"small-text\" id=\"caption\">" . $infobox["image"]["caption"] . "</td></tr></tbody></table></span></td></tr>";
	// properties
	foreach ($infobox["items"] as $item) {
		$html .= "<tr class=\"";
		if ($item["type"] == "property") {
			$html .= "property\"><th>" . $item["label"] . "</th><td id=\"value\">" . $item["value"] . "</td>";
		} else if ($item["type"] == "sub-heading") {
			$html .= "sub-heading\"><td colspan=\"2\"><div class=\"bold-text\">" . $item["value"] . "</div></td>";
		} else {
			$html .= "\"><td>Unknown type!</td>";
		}
		$html .= "</tr>";
	}

	return $html . "</tbody></table></div>";
} 

function get_page_content_html($page, $edit, $temp) {
	$rawjsonstring = null;
	if (isset($temp) && $temp == "yes") {
		$rawjsonstring = Page::$emptyContentRawJSON;
	} else {
		$rawjsonstring = $page->get_content_rawjson();
	}
	if ($rawjsonstring != null) {
		// O.K. in getting raw json from content.json
		return page_content_html(json_decode($rawjsonstring, true), $page, $edit);
	} else {
		// could not get raw json
		return "Could not retrieve content HTML (make sure the page exists and has content)";
	}
}

?>