<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_session extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_session();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "session");
    }

    public function get_by_session($calendar_session) {
        return $this->get_one("calendar_session = '$calendar_session'");
    }

    public function get_exist_by_session($tempid) {
        return $this->get_one("tempid = '$tempid' and type = 1");
    }

    public function add($calendar_session, $tempid, $expired, $last_login, $type) {
        return $this->insert(array("calendar_session" => $calendar_session,"tempid" => $tempid, "expired" => $expired, "last_login" => $last_login, "type" => $type));
    }

    public function modify($id, $calendar_session, $tempid, $expired, $last_login, $type) {
        return $this->update(array("calendar_session" => $calendar_session,"tempid" => $tempid, "expired" => $expired, "last_login" => $last_login, "type" => $type), "id = $id");
    }

    public function remove($calendar_session) {
        return $this->delete("calendar_session = '$calendar_session'");
    }


};


