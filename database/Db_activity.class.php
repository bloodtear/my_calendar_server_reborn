<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_activity extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_activity();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "activity");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }

    public function all() {
        return $this->get_all();
    }

    public function add($owner, $title, $avatar, $images, $begintime, $endtime, $repeattype, $repeatend, $address, $content, $participants, $joinsheet, $type, $pid, $notice, $scws_title) {
		return $this->insert(array("owner" => $owner, "title" => $title, "avatar" => $avatar, "images" => $images, "begintime" => $begintime, "endtime" => $endtime, "repeattype" => $repeattype, "repeatend" => $repeatend, "address" => $address, "content" => $content, "participants" => $participants, "joinsheet" => $joinsheet, "type" => $type, "status" => 0, "createtime" => time(),  "modifytime" => time(), "clickcount" => 0, "pid" => $pid, "notice" => $notice, "scws_title" => $scws_title));
    }

    public function modify($id, $title, $avatar, $images, $begintime, $endtime, $repeattype, $repeatend, $address, $content, $participants, $joinsheet, $pid, $status, $notice, $scws_title) {
        return $this->update(array("title" => $title, "avatar" => $avatar, "images" => $images, "begintime" => $begintime, "endtime" => $endtime, "repeattype" => $repeattype, "repeatend" => $repeatend, "address" => $address, "content" => $content, "participants" => $participants, "joinsheet" => json_encode($joinsheet), "modifytime" => (int)time(), "pid" => $pid, "status" => $status, "notice" => $notice, "scws_title" => $scws_title), "id = $id");
    }

    public function modify_batch($title, $content, $address, $images, $avatar, $type, $pid) {
        return $this->update(array("title" => $title, "avatar" => $avatar, "images" => $images, "address" => $address, "content" => $content, "sheet" => json_encode($joinsheet), "modifytime" => (int)time()), "pid = $pid");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }

    public function cancel($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }

    public function modify_pid($pid, $aid_array) {
      $where  = "";
      foreach ($aid_array as $k => $aid) {
        $where .= "id = $aid or ";
      }
      $where = substr($where, 0, -3);
      return $this->update(array("pid" => $pid), "$where");
    }

    
    /*******************
    / 长查询
    /*******************/
      
    public static function my_all_list($userid){
        // 1: 我创建的 单体
        // 2: 我加入的 单体
        // 3: 我关注的 单体
        // 4: 我关注的 分类
        $my_create = "
            select 
                a.*, 
                b.title type_title, 
                b.pub pub,
                p1.image avatar_name
            from 
                my_calendar_activity a 
            JOIN 
                my_calendar_custom_activity_types b 
            on 
                a.type = b.id 
            join 
                my_calendar_preview_images p1
            on 
                p1.id = a.avatar
            where 
                a.owner = $userid";

        $my_joined = "
            select 
                d.*, 
                e.title type_title, 
                e.pub pub,
                p2.image avatar_name
            from 
                my_calendar_sign f 
            join 
                my_calendar_activity d 
            on 
                f.activity = d.id 
            join 
                my_calendar_custom_activity_types e 
            on 
                d.type = e.id 
            join 
                my_calendar_preview_images p2
            on 
                p2.id = d.avatar
            where 
                f.tempid = $userid";
                
        $my_subscribed = "
            select 
                y.*, 
                z.title type_title, 
                z.pub,
                p3.image avatar_name
            from 
                my_calendar_subscribe x 
            join 
                my_calendar_activity y 
            on 
                x.activity = y.id 
            join 
                my_calendar_custom_activity_types z 
            on 
                z.id = y.type  
            join 
                my_calendar_preview_images p3
            on 
                p3.id = y.avatar
            where 
                x.tempid = $userid";
                
        $my_subscribed_type = "
            select 
                ay.*, 
                az.title type_title, 
                az.pub,
                p4.image avatar_name
            from 
                my_calendar_subscribe_type ax 
            join 
                my_calendar_activity ay 
            on 
                ay.type = ax.typeid
            join 
                my_calendar_custom_activity_types az 
            on 
                az.id = ay.type
            join 
                my_calendar_preview_images p4
            on 
                p4.id = ay.avatar
            where 
                ax.tempid = $userid";
                
        $sql = "
            $my_create
                union
            $my_joined
                union
            $my_subscribed
                union
            $my_subscribed_type;";
        return Db_base::inst()->do_query($sql);
    }
    
    public static function my_list_by_type($userid, $choosed_type){
        $sql = "
        select 
            a.*, 
            b.title type_title, 
            b.pub pub,
            p.image avatar_name
        from 
            my_calendar_activity a 
        JOIN 
            my_calendar_custom_activity_types b 
        on 
            a.type = b.id 
        join 
            my_calendar_preview_images p
        on 
            p.id = a.avatar
        where 
            a.type = $choosed_type
        ";
        return Db_base::inst()->do_query($sql);
    }
    
    public static function my_joined_list($userid){
        $sql = "
        select 
            d.*, 
            e.title type_title, 
            e.pub pub,
            p.image avatar_name
        from 
            my_calendar_sign f 
        join 
            my_calendar_activity d 
        on 
            f.activity = d.id 
        join 
            my_calendar_custom_activity_types e 
        on 
            d.type = e.id 
        join 
            my_calendar_preview_images p
        on 
            p.id = d.avatar
        where 
            f.tempid = $userid;
        ";
        return Db_base::inst()->do_query($sql);
    }    
    
    public static function my_subscribed_list($userid){
        $sql = "
            select 
                b.*, 
                c.title type_title, 
                c.pub,
                p.image avatar_name
            from 
                my_calendar_subscribe a 
            join 
                my_calendar_activity b 
            on 
                a.activity = b.id 
            join 
                my_calendar_custom_activity_types c 
            on 
                c.id = b.type
            join 
                my_calendar_preview_images p
            on 
                p.id = b.avatar
            where 
                a.tempid = $userid";
        return Db_base::inst()->do_query($sql);
    }
    
    public static function share_list($choosed_type){
        $sql = "
        select 
            a.*, 
            b.title type_title, 
            b.pub pub,
            p.image avatar_name
        from 
            my_calendar_activity a 
        JOIN 
            my_calendar_custom_activity_types b 
        on 
            a.type = b.id 
        join 
            my_calendar_preview_images p
        on 
            p.id = a.avatar
        where 
            a.type = $choosed_type";
        return Db_base::inst()->do_query($sql);
    }

    public static function view_by_user($id, $userid) {
        $sql = "
            select 
                a.*, 
                b.id sign_id,
                b.sheet sign_sheet, 
                b.notice sign_notice,
                c.id subscribe,
                u.nickname owner_nickname,
                u.avatar owner_avatar,
                p.image avatar_name,
                t.title type_title,
                t.pub
            from 
                my_calendar_activity a 
            left join 
                my_calendar_sign b 
            on 
                b.activity = $id and b.tempid = $userid 
            left join 
                my_calendar_subscribe c
            on 
                c.activity = $id and c.tempid = $userid
            join 
                my_calendar_preview_images p
            on 
                a.avatar = p.id
            join
                my_calendar_tempuser u 
            on
                u.id = a.owner
            join
                my_calendar_custom_activity_types t
            on
                t.id = a.type
            where 
                a.id = $id 
            limit 1";
        return Db_base::inst()->do_query($sql);
    }
    
    
    public static function search($input, $start) {
        $sql = "
            select 
                a.* ,
                b.title type_title,
                b.pub pub,
                c.image avatar_name
            from 
                my_calendar_activity a
            join 
                my_calendar_custom_activity_types b
            on 
                a.type = b.id
            join 
                my_calendar_preview_images c
            on 
                a.avatar = c.id
            where 
                match(a.scws_title) AGAINST('$input') 
                and 
                b.pub != 0
            limit 
                $start, 30;";
        return Db_base::inst()->do_query($sql);
    }
    
    
};


