<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_event extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_event();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "event");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }

    public function load($activity, $calendar, $event_code, $operator){
        $id = (int)$id;
        return $this->get_one("activity = $activity and calendar = $calendar and event_code = $event_code and operator = $operator");
    }

    public function all() {
        return $this->get_all("", " order by time desc");
    }
    
    public function add($activity, $calendar, $event_code, $operator) {
        return $this->insert(array("activity" => $activity, "calendar" => $calendar, "event_code" => $event_code, "operator" => $operator, "status" => 0, "time" => time()));
    }

    public function modify($id, $activity, $calendar, $event_code, $operator, $status) {
        return $this->update(array("activity" => $activity, "calendar" => $calendar, "event_code" => $event_code, "operator" => $operator, "status" => $status, "time" => time()), "id = $id");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }

    public function cancel($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }


};


