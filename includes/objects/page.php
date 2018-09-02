<?php

class Page {
	public static $storageFilePath = "../data-storage/pages";
	public static $tempStorageFilePath = "../data-storage/temporary-page";
	public static $maxNumberOfPages = 5000;

	public $filePath = "";
	public $metaFilePath = "";
	public $contentFilePath = "";

	public $title = "Untitled";
	public $id = 0;
	public $isnew = false;

	public $created = "0";
	public $updated = "0";

	private static function get_temp_meta($key) {
		return json_decode(file_get_contents(Page::$tempStorageFilePath . "/meta.json"), true)[$key];
	}

	private static function get_meta_normal($id) {
		return json_decode(file_get_contents(Page::$storageFilePath . "/" . $id . "/meta.json"), true);
	}
	
	public static function save_temp_meta($title) {
		$metafile = false;
		$meta = array(
			"title" => $title,
			"id" => Page::get_temp_meta("id"),
			"created" => Page::get_temp_meta("created"),
			"updated" => Page::get_temp_meta("updated")
		);
		$metafile = fopen(Page::$tempStorageFilePath . "/meta.json", "w");
		if ($metafile === false) {
			// not found or has wrong permissions
			return;
		}
		fwrite($metafile, json_encode($meta));
		fclose($metafile);
	}

	public static function action_save($get) {
		if (isset($get["isnew"]) == "no") {
			$m = Page::get_meta_normal($get["id"]);
			Page::save_meta_normal(array(
				"title" => $get["title"],
				"id" => $m["id"],
				"created" => $m["created"],
				"updated" => strtotime("now")
			));
			return;
		} else {
			// is newly created page
		}

		Page::save_temp_meta($get["title"]);

		if (!file_exists(Page::$storageFilePath . "/" . $get["id"])) {
			// make its directory
			mkdir(Page::$storageFilePath . "/" . $get["id"]);
		}
		// move meta.json
		rename(Page::$tempStorageFilePath . "/meta.json", Page::$storageFilePath . "/" . $get["id"] . "/meta.json");
	}

	public function __toString() {
		return $this->title . " (" . $this->id . ")";
	}

	function __construct() {
		$this->id = func_get_arg(0);
		if ($this->id == -1) {
			// id was passed as -1, make new page
			$this->isnew = true;
			// get next available id
			for ($i = 0; $i < Page::$maxNumberOfPages - 1; $i++) {
				if (file_exists(Page::$storageFilePath . "/" . $i)) {
					continue;
				}
				$this->id = $i;
				break;
			}
			$this->update_paths();
			$this->save_meta();
		} else {
			$this->update_paths();
			$this->load_meta();
		}
	}

	private function check_temporary() {
		if (!file_exists(Page::$tempStorageFilePath)) {
			mkdir(Page::$tempStorageFilePath);
		}
	}

	private function update_paths() {
		$this->filePath = Page::$storageFilePath . "/" . $this->id;
		$this->metaFilePath = $this->filePath . "/meta.json";
		$this->contentFilePath = $this->filePath . "/content.json";
	}

	private function load_meta() {
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

	private function save_meta() {
		$metafile = false;
		$meta = array(
			"title" => $this->title,
			"id" => $this->id,
			"created" => $this->created,
			"updated" => $this->updated
		);
		if ($this->isnew) {
			$meta["created"] = strtotime("now");
			$meta["updated"] = $meta["created"];
			$this->check_temporary();
			$metafile = fopen(Page::$tempStorageFilePath . "/meta.json", "w");
		} else {
			$metafile = fopen($this->metaFilePath, "w");
		}
		if ($metafile === false) {
			// not found or has wrong permissions
			return;
		}
		fwrite($metafile, json_encode($meta));
		fclose($metafile);
	}

	private static function save_meta_normal($meta) {
		$metafile = false;
		$metafile = fopen(Page::$storageFilePath . "/" . $meta["id"] . "/meta.json", "w");
		if ($metafile === false) {
			// not found or has wrong permissions
			return;
		}
		fwrite($metafile, json_encode($meta));
		fclose($metafile);
	}

	function get_content() {
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
}

?>