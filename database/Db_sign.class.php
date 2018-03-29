<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_sign extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_sign();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "sign");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }
    public function one($activity_id, $userid) {
        $activity_id = (int)$activity_id;
        $userid = (int)$userid;
        return $this->get_one("activity = $activity_id and tempid = $userid");
    }

    public function all() {
        return $this->get_all();
    }

    public function add($activity_id, $userid, $sheet, $notice, $modify_time) {
        return $this->insert(array("activity" => $activity_id, "notice" => $notice, "tempid" => $userid, "sheet" => $sheet, "modify_time" => $modify_time));
    }

    public function modify($id, $activity, $user, $sheet, $notice, $modify_time) {
        $id = (int)$id;
        return $this->update(array("sheet" => $sheet, "activity" => $activity, "notice" => $notice, "tempid" => $user, "modify_time" => $modify_time), "id = $id");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }

    public function cancel($id) {
        $id = (int)$id;
        return $this->delete("id = $id");
    }
    
    public function load_by_aid($id) {
        $sql = "
            select 
                a.*,
                b.avatar,
                b.nickname
            from 
                my_calendar_sign a
            join
                my_calendar_tempuser b
            on 
                a.tempid = b.id
            where 
                a.activity = $id";
        return Db_base::inst()->do_query($sql);
    }


};


