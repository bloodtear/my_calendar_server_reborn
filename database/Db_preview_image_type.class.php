<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_preview_image_type extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_preview_image_type();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "preview_images_type");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }

    public function all() {
        return $this->get_all();
    }
    
    public function all_with_title() {
        $sql = "
            select 
                a.*, b.title 
            from 
                my_calendar_preview_images a 
            join 
                my_calendar_preview_images_type b 
            on 
                a.type = b.id";
        return Db_base::inst()->do_query($sql);
    }

    public function add($name) {
        return $this->insert(array("name" => $name));
    }

    public function modify($id, $name) {
        $id = (int)$id;
        return $this->update(array("name" => $name), "id = $id");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }


};


