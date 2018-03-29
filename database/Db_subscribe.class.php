<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_subscribe extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_subscribe();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "subscribe");
    }

    public function get($aid, $userid) {
        $aid = (int)$aid;
        return $this->get_one("activity = $aid and tempid = $userid");
    }
    
    public function get_activity_by_user($userid) {
        $userid = (int)$userid;
        return $this->get_all("calendar = 0 and tempid = $userid");
    }
    
    public function get_calendar_by_user($userid) {
        $userid = (int)$userid;
        return $this->get_all("activity = 0 and tempid = $userid");
    }

    public function all() {
        return $this->get_all();
    }

    public function add($aid, $userid) {
        return $this->insert(array("activity" => $aid, "tempid" => $userid, "time" => time()));
    }

    public function modify($id, $name) {
        $id = (int)$id;
        return $this->update(array("name" => $name), "id = $id");
    }

    public function remove($aid, $userid) {
        $aid = (int)$aid;
        return $this->delete("activity = $aid and tempid = $userid");
    }


};


