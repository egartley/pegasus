<?php

// this entire thing is a complete shitshow, but at least it works... kind of...

class Page
{
    public static $storageFilePath = "../data-storage/pages";
    public static $tempStorageFilePath = "../data-storage/temporary-page";
    public static $maxNumberOfPages = 5000;
    public static $emptyContentRawJSON = "{\"modules\": [{\"type\": \"section-content\", \"value\": [{\"type\": \"paragraph\", \"value\": [{\"type\": \"plain\", \"value\": \"Type anything\"}] }] }], \"infobox\": {\"heading\": \"Infobox\", \"image\": {\"file\": \"/resources/png/infobox-default.png\", \"caption\": \"Add your own image here\"}, \"items\": [{\"type\": \"property\", \"label\": \"Property\", \"value\": \"value\"}] } }";
    public static $defaultTitle = "Untitled";
    public static $defaultDateFormat = "F jS, Y, \a\\t g:i A";
    public static $defaultSlug = "Untitled";

    public $filePath = "";
    public $metaFilePath = "";
    public $contentFilePath = "";

    public $title = "Untitled";
    public $id = 0;
    public $isnew = "no";
    public $slug = "Untitled";

    public $created = "0";
    public $updated = "0";

    function __construct()
    {
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
            $this->set_empty_content();
        } else {
            // previously saved page (assuming)
            $this->update_paths();
            $this->load_meta_from_file();
        }
    }

    public static function get_temp_page()
    {
        return new Page(-1);
    }

    public function get_url_slug_from_title()
    {
        // See also: http://www.faqs.org/rfcs/rfc1738.html
        // only alphanumeric and $-_.+!*'(), characters are allowed in URls
        return str_replace([" ", "`", "{", "}", "|", "\\", "^", "~", "[", "]", ";", "/", "?", ":", "@", "=", "&", "#", "<", ">"], "_", $this->title);
    }

    private static function get_meta_by_id($id)
    {
        return json_decode(file_get_contents(Page::$storageFilePath . "/" . $id . "/meta.json"), true);
    }

    private static function save_meta_to_temp($post)
    {
        $metafile = fopen(Page::$tempStorageFilePath . "/meta.json", "w");
        if ($metafile === false) {
            // not found or has wrong permissions
            return false;
        }
        fwrite($metafile, json_encode(array(
            "title" => $post["title"], // title from url
            "id" => $post["id"], // id from url
            "slug" => $post["slug"],
            "created" => strtotime("now"), // created just now
            "updated" => strtotime("now") // updated just now
        )));
        fclose($metafile);
        return true;
    }

    private static function save_content_to_temp($post)
    {
        $contentfile = fopen(Page::$tempStorageFilePath . "/content.json", "w");
        if ($contentfile === false) {
            // not found or has wrong permissions
            return false;
        }
        fwrite($contentfile, urldecode($post["contentjson"]));
        fclose($contentfile);
        return true;
    }

    private static function save_meta_normal($meta)
    {
        $metafile = fopen(Page::$storageFilePath . "/" . $meta["id"] . "/meta.json", "w");
        if ($metafile === false) {
            // not found or has wrong permissions
            return false;
        }
        fwrite($metafile, json_encode($meta));
        fclose($metafile);
        return true;
    }

    private static function save_content_normal($post)
    {
        $contentfile = fopen(Page::$storageFilePath . "/" . $post["id"] . "/content.json", "w");
        if ($contentfile === false) {
            // not found or has wrong permissions
            return false;
        }
        fwrite($contentfile, urldecode($post["contentjson"]));
        fclose($contentfile);
        return true;
    }

    // Credit: http://php.net/manual/en/function.rmdir.php#117354
    private static function delete_page_directory($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    Page::delete_page_directory($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

    public static function action_delete($deleteID)
    {
        Page::delete_page_directory(Page::$storageFilePath . "/" . $deleteID);
        return true;
    }

    public static function action_save($post)
    {
        // ex. "/editor/?action=save&id=2&isnew=no" POST
        if (isset($post["isnew"]) && $post["isnew"] == "yes") {
            // we're saving a new page
            // make sure temp directory exists
            Page::check_temporary_page_directory();
            // save files to temp
            if (Page::save_meta_to_temp($post) && Page::save_content_to_temp($post)) {
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
            // save meta and content
            return Page::save_meta_normal(array(
                    "title" => $post["title"], // updated title
                    "id" => $oldmeta["id"], // id doesn't change
                    "slug" => $post["slug"], // updated slug
                    "created" => $oldmeta["created"], // created doesn't change
                    "updated" => strtotime("now") // update just now
                )) && Page::save_content_normal($post);
        }
    }

    /**
     * Checks for the temporary directory's existence, and creates it if needed
     */
    private static function check_temporary_page_directory()
    {
        if (!file_exists(Page::$tempStorageFilePath)) {
            mkdir(Page::$tempStorageFilePath);
        }
    }

    private function update_paths()
    {
        $this->filePath = Page::$storageFilePath . "/" . $this->id;
        $this->metaFilePath = $this->filePath . "/meta.json";
        $this->contentFilePath = $this->filePath . "/content.json";
    }

    private function load_meta_from_file()
    {
        if ($rawmetajson = file_get_contents($this->metaFilePath)) {
            $meta = json_decode($rawmetajson, true);
            $this->title = $meta["title"];
            $this->id = $meta["id"];
            $this->slug = $meta["slug"];
            $this->created = $meta["created"];
            $this->updated = $meta["updated"];
        } else {
            // meta.json doesn't exist
            // all properties will stay at their defaults (except for id)
        }
    }

    private function set_meta()
    {
        $meta = array(
            "title" => $this->title,
            "id" => $this->id,
            "slug" => $this->slug,
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

    private function set_empty_content()
    {
        // make sure temp directory exists
        $this->check_temporary_page_directory();
        // get temp content json file
        $file = fopen(Page::$tempStorageFilePath . "/content.json", "w");
        if ($file === false) {
            // not found or has wrong permissions
            return;
        }
        // write the empty content json to it
        fwrite($file, Page::$emptyContentRawJSON);
        fclose($file);
    }

    /**
     * Returns the page's raw content JSON (content.json) as a string if it exists, otherwise returns <code>null</code>
     *
     * @return false|string|null
     */
    function get_content_rawjson()
    {
        if (!file_exists($this->contentFilePath)) {
            return null;
        } else {
            return file_get_contents($this->contentFilePath);
        }
    }

    function asPrettyString_created()
    {
        return date(Page::$defaultDateFormat, $this->created);
    }

    function asPrettyString_updated()
    {
        return date(Page::$defaultDateFormat, $this->updated);
    }

    public function __toString()
    {
        return $this->title . " (" . $this->id . ")";
    }
}