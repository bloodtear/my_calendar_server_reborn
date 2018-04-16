<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_session extends fdb\database_table {
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

    public function add($calendar_session, $tempid, $expired, $last_login) {
        return $this->insert(array("calendar_session" => $calendar_session,"tempid" => $tempid, "expired" => $expired, "last_login" => $last_login));
    }

    public function modify($id, $calendar_session, $tempid, $expired, $last_login) {
        return $this->update(array("calendar_session" => $calendar_session,"tempid" => $tempid, "expired" => $expired, "last_login" => $last_login), "id = $id");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }


};


