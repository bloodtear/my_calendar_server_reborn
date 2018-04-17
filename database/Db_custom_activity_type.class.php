<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_custom_activity_type extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_custom_activity_type();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "custom_activity_types");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }
	
	public function get_by_userid($userid) {
        $userid = (int)$userid;
        return $this->get_all("tempid = $userid");
    }

    public function all() {
        return $this->get_all();
    }

    public function add($userid, $title, $pub, $scws_title) {
        return $this->insert(array("tempid" => $userid, "title" => $title, "pub" => $pub, "scws_title" => $scws_title));
    }

    public function modify($id, $title, $pub, $scws_title) {
        return $this->update(array("title" => $title, "pub" => $pub, "scws_title" => $scws_title), "id = $id");
    }
    
    public function remove($id) {
        return $this->delete("id = $id");
    }

    public function default_init($userid) {
        $userid = (int)$userid;
        $db = Db_base::inst();
        $sql = "insert into " . MYSQL_PREFIX . "custom_activity_types" . "(tempid, title, pub, scws_title) values ". 
               "($userid, '个人', 0, '个人'),
                ($userid, '工作', 1, '工作'),
                ($userid, '娱乐', 1, '娱乐')";
        return $db->do_query($sql);
    }
    
     
    public static function my_types($userid){
        
        $my_subscribed_type = "
            select 
                b.*, 
                0 as editable ,
                1 as subscribed,
                count(act.id) num
            from 
                    my_calendar_subscribe_type a 
            inner join 
                    my_calendar_custom_activity_types b 
            on 
                    a.typeid = b.id 
            left JOIN
                my_calendar_activity act
            on 
                    act.type = a.typeid
            where 
                    a.tempid = $userid
            group by 
                    a.typeid
            ";
            
        $my_owned = "
            select 
                c.*, 
                1 as editable ,
                0 as subscribed,
                count(z.id) num
            from 
                my_calendar_custom_activity_types c 
            left join 
                my_calendar_activity z
            ON
                c.id = z.type
            where 
                c.tempid = $userid
            group by 
                c.id";
                
        $sql = "
            $my_owned
                UNION
            $my_subscribed_type";
            
        return Db_base::inst()->do_query($sql);
    }
    
    public static function view_by_user($typeid, $userid){
        $sql = "
            select 
                a.*, b.id subscribed , 
                $userid as view_userid,
                usr.avatar avatar_name
            from 
                my_calendar_custom_activity_types a 
            left join 
                my_calendar_subscribe_type b 
            on 
                a.id = b.typeid and b.tempid = $userid 
            left join
                my_calendar_tempuser usr
            on
                a.tempid = usr.id
            where 
                a.id = $typeid ";
        return Db_base::inst()->do_query($sql);
    }

    
    
    public static function search($input, $start) {
        $sql = "
            select 
                a.* ,
                b.avatar avatar_name
            from 
                my_calendar_custom_activity_types a
            join 
                my_calendar_tempuser b
            on 
                b.id = a.tempid
            where 
                match(a.scws_title) AGAINST('$input') 
                and 
                a.pub != 0
            limit 
                $start, 30;";
        return Db_base::inst()->do_query($sql);
    }
};


