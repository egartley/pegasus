<?php

function get_style_sheet_inline($name) {
	$r = "<style type=\"text/css\">";
	try {
    	$content = file_get_contents("../resources/css/" . $name . ".css");
    	if ($content === false) {
        	$r .= ".page-content:before{content:\"Could not find \\\"/resources/css/" . $name . ".css\\\"\"}";
    	} else {
    		$r .= $content;
    	}
    	$r .= "</style>";
	} catch (Exception $e) {
		// todo
	}
	return $r;
}

function page_content_html($content, $page, $edit) {
	// styling
	$html = get_style_sheet_inline("min");
	$html .= get_style_sheet_inline("page-content");

	// script
	$html .= "</style><script src=\"../resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"../resources/js/viewer.js\" type=\"text/javascript\"></script>";
	
	// meta/other
	$html .= "<span class=\"hidden\" id=\"hiddenpageid\">" . $page->id . "</span>";
	$html .= "<span class=\"hidden\" id=\"hiddenpageisnew\">" . $page->isnew . "</span>";
	$html .= "<span class=\"hidden\" id=\"hiddenedit\">" . $edit . "</span>";

	// if editing, add toolbar
	if ($edit) {
		$html .= "<div class=\"toolbar\"><div class=\"actionable action99\"><span id=\"icon\"><img src=\"../resources/png/check.png\"></span><span id=\"text\">Save</span></div><div class=\"actionable action01\"><span id=\"icon\"><img src=\"../resources/gif/plus.gif\"></span><span id=\"text\">Paragraph</span></div><div class=\"actionable action02\"><span id=\"icon\"><img src=\"../resources/gif/plus.gif\"></span><span id=\"text\">Section</span></div><div class=\"actionable action03\"><span id=\"icon\"><img src=\"../resources/png/gear.png\"></span><span id=\"text\">Options</span></div><div class=\"toolbar-status\"><span>Ready</span></div><div class=\"toolbar-spinner hidden\"></div></div>";
	}

	// start of actual content
	$html .= "<div class=\"page-content\"><div class=\"content\">";
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
			// paragraph container
			$html .= "paragraph-container\">";
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
				// end sub-module of paragraph container module
				$html .= "</div>";
			}
		} else if($module["type"] == "heading") {
			// section heading
			$html .= "heading\"";
			if ($edit) {
				$html .= " contenteditable=\"true\"";
			}
			$html .= ">" . $module["value"];
		} else {
			$html .= "\">Unknown type!";
		}
		// end module
		$html .= "</div>";
	}
	// footer (hardcoded for now)
	$html .= "<div class=\"module footer\">Copyright 2018</div>";
	// end all modules
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
		return "Could not get content HTML (make sure the page exists and has content)";
	}
}

?>