<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_activity_type extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_activity_type();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "activity_types");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }

    public function all() {
        return $this->get_all();
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


