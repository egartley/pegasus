<?php

class Page {
	public static $storageFilePath = "../data-storage/pages";
	public static $tempStorageFilePath = "../data-storage/temporary-page";
	public static $maxNumberOfPages = 5000;
	public static $emptyContentRawJSON = "{\"modules\":[],\"infobox\":{\"heading\":\"\",\"main-image\":{\"file\":\"\",\"caption\":\"\"},\"items\":[]}}";

	public $filePath = "";
	public $metaFilePath = "";
	public $contentFilePath = "";

	public $title = "Untitled";
	public $id = 0;
	public $isnew = "no";

	public $created = "0";
	public $updated = "0";

	function __construct() {
		$this->id = func_get_arg(0);
		if ($this->id == -1) {
			// make new page
			$this->isnew = "yes";

			// get next available id
			for ($i = 0; $i < Page::$maxNumberOfPages - 1; $i++) {
				if (file_exists(Page::$storageFilePath . "/" . $i)) {
					continue;
				}
				$this->id = $i;
				break;
			}

			// update paths and meta
			$this->update_paths();
			$this->set_meta();
		} else {
			// previously saved page (assuming)
			$this->update_paths();
			$this->load_meta_from_file();
		}
	}

	private static function get_meta_temp($key) {
		return json_decode(file_get_contents(Page::$tempStorageFilePath . "/meta.json"), true)[$key];
	}

	private static function get_meta_by_id($id) {
		return json_decode(file_get_contents(Page::$storageFilePath . "/" . $id . "/meta.json"), true);
	}
	
	private static function save_meta_to_temp($post) {
		$metafile = fopen(Page::$tempStorageFilePath . "/meta.json", "w");
		if ($metafile === false) {
			// not found or has wrong permissions
			return false;
		}

		fwrite($metafile, json_encode(array(
			"title" => $post["title"], // title from url
			"id" => $post["id"], // id from url
			"created" => strtotime("now"), // created just now
			"updated" => strtotime("now") // updated just now
		)));
		fclose($metafile);

		return true;
	}

	private static function save_content_to_temp() {
		$contentfile = fopen(Page::$tempStorageFilePath . "/content.json", "w");
		if ($contentfile === false) {
			// not found or has wrong permissions
			return false;
		}

		fwrite($contentfile, Page::$emptyContentRawJSON);
		fclose($contentfile);

		return true;
	}

	private static function save_meta_normal($meta) {
		$metafile = fopen(Page::$storageFilePath . "/" . $meta["id"] . "/meta.json", "w");
		if ($metafile === false) {
			// not found or has wrong permissions
			return false;
		}
		fwrite($metafile, json_encode($meta));
		fclose($metafile);
		return true;
	}

	// called from /core/editor.php when action is "save"
	// ex. "/editor/?action=save&id=2&isnew=no"
	public static function action_save($post) {
		if (isset($post["isnew"]) && $post["isnew"] == "yes") {
			// we're saving a new page

			// make sure temp directory exists
			Page::check_temporary_page_directory();
			
			// save files to temp
			if (Page::save_meta_to_temp($post) && Page::save_content_to_temp()) {
				// create its "normal" storage directory
				$normalPath = Page::$storageFilePath . "/" . $post["id"];
				if (!file_exists($normalPath)) {
					mkdir($normalPath);
				}
				// move meta from temp to normal
				rename(Page::$tempStorageFilePath . "/meta.json", $normalPath . "/meta.json");
				// move content from temp to normal
				rename(Page::$tempStorageFilePath . "/content.json", $normalPath . "/content.json");
			} else {
				// could not save to temp
				return false;
			}
			// everything went fine
			return true;
		} else {
			// not new, has been previously saved, get that meta
			$oldmeta = Page::get_meta_by_id($post["id"]);

			return Page::save_meta_normal(array(
				"title" => $post["title"], // updated title
				"id" => $oldmeta["id"], // id doesn't change
				"created" => $oldmeta["created"], // created doesn't change
				"updated" => strtotime("now") // update just now
			));
		}
	}
	
	private function check_temporary_page_directory() {
		if (!file_exists(Page::$tempStorageFilePath)) {
			mkdir(Page::$tempStorageFilePath);
		}
	}

	private function update_paths() {
		$this->filePath = Page::$storageFilePath . "/" . $this->id;
		$this->metaFilePath = $this->filePath . "/meta.json";
		$this->contentFilePath = $this->filePath . "/content.json";
	}

	private function load_meta_from_file() {
		// meta (title, dates/times, etc.)
		if ($rawmetajson = file_get_contents($this->metaFilePath)) {
			$meta = json_decode($rawmetajson, true);
			$this->title = $meta["title"];
			$this->id = $meta["id"];
			$this->created = $meta["created"];
			$this->updated = $meta["updated"];
		} else {
			// meta.json doesn't exist
			// all properties will stay at their defaults (except for id)
		}
	}

	private function set_meta() {
		$metafile = false;
		$meta = array(
			"title" => $this->title,
			"id" => $this->id,
			"created" => $this->created,
			"updated" => $this->updated
		);
		if ($this->isnew == "yes") {
			// this is a new page
			$meta["created"] = strtotime("now");
			$meta["updated"] = $meta["created"];
			$this->check_temporary_page_directory();
			$metafile = fopen(Page::$tempStorageFilePath . "/meta.json", "w");
		} else {
			// not a new page, save normally
			$metafile = fopen($this->metaFilePath, "w");
		}
		if ($metafile === false) {
			// not found or has wrong permissions
			return;
		}
		fwrite($metafile, json_encode($meta));
		fclose($metafile);
	}

	function get_content_rawjson() {
		if (!file_exists($this->contentFilePath)) {
			return null;
		} else {
			return file_get_contents($this->contentFilePath);
		}
	}

	function asPrettyString_created() {
		// TODO: convert from unix to pretty
		return $this->created;
	}

	function asPrettyString_updated() {
		// TODO: convert from unix to pretty
		return $this->updated;
	}

	public function __toString() {
		return $this->title . " (" . $this->id . ")";
	}
}

?>