<?php

class ApplicationSettings
{
    public static $didLoad = false;
    public static $default_permalinkStructure = "/page/@SLUG";
    public static $storageFilePath = "../data-storage/app/";
    public static $storageFileName = "settings.json";
    public static $permalinkStructure = "";

    static function set_defaults()
    {
        ApplicationSettings::$permalinkStructure = ApplicationSettings::$default_permalinkStructure;
    }

    static function set_values($jsonstring, $write = false)
    {
        $json = json_decode($jsonstring, true);
        ApplicationSettings::$permalinkStructure = $json["permalink-structure"];
        if ($write) {
            ApplicationSettings::write_values_to_file();
        }
    }

    static function write_values_to_file() {
        $file = fopen(ApplicationSettings::$storageFilePath . ApplicationSettings::$storageFileName, "w");
        fwrite($file, ApplicationSettings::get_json_string());
        fclose($file);
    }

    static function get_json_string()
    {
        return "{\"permalink-structure\":\"" . ApplicationSettings::$permalinkStructure . "\"}";
    }

    static function get_php_permalink_for_slug($slug)
    {
        return ".." . ApplicationSettings::get_url_permalink_for_slug($slug);
    }
    static function get_url_permalink_for_slug($slug)
    {
        return str_replace("@SLUG", $slug, ApplicationSettings::$permalinkStructure);
    }

}

function settings_check($set = false)
{
    if (ApplicationSettings::$didLoad) {
        return;
    }
    ApplicationSettings::$didLoad = true;
    $settingsjson = ApplicationSettings::$storageFilePath . ApplicationSettings::$storageFileName;
    if (file_exists($settingsjson)) {
        // all good
        if ($set) {
            ApplicationSettings::set_values(file_get_contents($settingsjson));
        }
        return;
    }
    // file doesn't exist

    ApplicationSettings::set_defaults();

    $file = fopen($settingsjson, "w");
    if ($file === false) {
        return;
    }
    // write file with default values
    fwrite($file, ApplicationSettings::get_json_string());
    fclose($file);
}