<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Preview_image_type {
    private $mSummary = null;

    public function __construct($summary = array()) {
        if (empty($summary)) {
            $summary = array(
                "id" => 0,
            );
        }
        $this->mSummary = $summary;
    }

    public function id() {
        return $this->mSummary["id"];
    }

    public function title() {
        return $this->mSummary["title"];
    }
    
    public function url() {
        $image = $this->image();
        return TOPIC_URL . "/$image";
    }

    public function setName($n) {
        $this->mSummary["name"] = $n;
    }


    public function save() {
        // $id = $this->id();
        // if ($id == 0) {
        //     $id = db_template::inst()->add();
        //     if ($id !== false) {
        //         $this->mSummary["id"] = $id;
        //     }
        // } else {
        //     $id = db_template::inst()->modify($id);
        // }
        // return $id;
    }

    public function packInfo() {
       return array(
            "id" => $this->id(),
            "title" => $this->title(), 
        );
    }

    public static function create($id) {
        $summary = db_preview_image::inst()->get($id);
        return new Preview_image($summary);
    }

    public static function all() {
        return database\Db_preview_image_type::inst()->all();
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.Preview_image.all", null);
        if ($all === null) {
            $all = Preview_image::all();
            $cache->save("class.Preview_image.all", $all);
        }
        return $all;
    }

    public static function remove($id) {
        return db_template::inst()->remove($id);
    }
    
    public static function get_url_from_name($name) {
        return TOPIC_URL . "/$name";
    }
};

