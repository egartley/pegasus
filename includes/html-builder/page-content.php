<?php

function page_content_html($content, $page, $edit) {
	// script
	$html = "<script src=\"/resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"/resources/js/editor.js\" type=\"text/javascript\"></script>";
	
	// meta/other
	$html .= "<span class=\"hidden\" id=\"hiddenpageid\">" . $page->id . "</span>";
	$html .= "<span class=\"hidden\" id=\"hiddenpageisnew\">" . $page->isnew . "</span>";
	$html .= "<span class=\"hidden\" id=\"hiddenedit\">" . $edit . "</span>";

	// if editing, add toolbar
	if ($edit) {
		$html .= get_editing_toolbar_html();
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
		if ($module["type"] == "section-content") {
			// paragraph container
			$html .= "section-content\">";
			foreach ($module["value"] as $submodule) {
				$html .= "<div class=\"sub-module ";
				if ($submodule["type"] == "paragraph") {
					// paragraph
					$html .= "paragraph\" contenteditable=\"true\">";
					foreach ($submodule["value"] as $pmodule) {
						if ($pmodule["type"] == "text") {
							$html .= $pmodule["value"];
						} else {
							$html .= "Unknown type!";
						}
					}
				} else if ($submodule["type"] == "list") {
					// list
					$html .= "list\"><ul>";
					foreach ($submodule["value"] as $listitem) {
						$html .= "<li id=\"list-item\" contenteditable=\"true\">" . $listitem . "</li>";
					}
					$html .= "</ul>";
				} else {
					$html .= "\">Unknown type!";
				}
				// end sub-module of paragraph container module
				$html .= "</div>";
			}
		} else if($module["type"] == "heading") {
			// section heading
			$html .= "heading\"><span";
			if ($edit) {
				$html .= " contenteditable=\"true\"";
			}
			$html .= ">" . $module["value"] . "</span><span id=\"removesection\"><img src=\"../resources/png/trash.png\" alt=\"[X]\" title=\"Remove this section\"></span>";
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
	$html .= "<table class=\"infobox\"><tbody><tr class=\"heading\"><td colspan=\"2\"><div class=\"bold-text\"";
	if ($edit) {
		$html .= "contenteditable=\"true\"";
	}
	$html .= ">" . $infobox["heading"] . "</div></td></tr><tr class=\"main-image\"><td colspan=\"2\"><span class=\"flex-centered\"><table><tbody><tr><td><img src=\"" . $infobox["image"]["file"] . "\"></td></tr><tr><td class=\"small-text\" id=\"caption\"";
	if ($edit) {
		$html .= "contenteditable=\"true\"";
	}
	$html .= ">" . $infobox["image"]["caption"] . "</td></tr></tbody></table></span></td></tr>";
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

function get_page_content_html($page, $edit) {
	$rawjsonstring = $page->get_content_rawjson();
	if ($rawjsonstring != null) {
		// O.K. in getting raw json from content.json
		return page_content_html(json_decode($rawjsonstring, true), $page, $edit);
	} else {
		// could not get raw json
		return "Could not get content HTML (make sure the page exists and has content)";
	}
}

?>