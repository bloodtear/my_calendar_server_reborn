<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Preview_image {
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

    public function type() {
        return $this->mSummary["type"];
    }
    
    public function image() {
        return $this->mSummary["image"];
    }
    
    public function url() {
        return TOPIC_URL . "/" . $this->image();
    }
    
    public function thumbnail_url() {
        return TOPIC_THUMBNAIL_URL . "/thumbnail-" . $this->image();
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
            "url" => $this->url(), 
            "type" => $this->type(), 
            "thumbnail_url" => $this->thumbnail_url(), 
        );
    }

    public static function create($id) {
        $summary = db_preview_image::inst()->get($id);
        return new Preview_image($summary);
    }

    public static function all() {
        $summary = database\Db_preview_image::inst()->all();
        \framework\Logging::l('pr_all', json_encode($summary));
        $arr = [];
        foreach ($summary as $k => $v) {
            $img = new Preview_image($v);
            array_push($arr, $img->packInfo());
        }
        return $arr;
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
    
    public static function get_thumbnail_url_from_name($name) {
        return TOPIC_THUMBNAIL_URL . "/thumbnail-" . $name;
    }
};

