<?php
namespace my_calendar_server_reborn\database;
use my_calendar_server_reborn\Database as fdb;

class Db_setting extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_setting();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "setting");
    }

    public function get_by_name($name) {
        return $this->get_one("name = '$name'");
    }

    public function all() {
        return $this->get_all();
    }

    public function add($name, $value, $expired, $status) {
        return $this->insert(array("name" => $name, "expired" => $expired, "value" => $value, "status" => $status));
    }

    public function modify($id, $name, $value, $expired, $status) {
        $id = (int)$id;
        return $this->update(array("name" => $name, "expired" => $expired, "value" => $value, "status" => $status), "id = $id");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }


};


