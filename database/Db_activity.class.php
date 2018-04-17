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
        return $this->delete("id = $id");
    }
    
    public function remove_group($arr) {
        $where = '';
        foreach ($arr as $k => $v) {
            if ($k != 0) {
                $where .= ' or ';
            }
            $where .= "id = '$v'";
        }
        //\framework\Logging::l('where', $where);
        //return false;
        return $this->delete("$where");
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
                p1.image avatar_name,
                count(DISTINCT sub1.id) now_sub,
                count(DISTINCT sign1.id) now_join
            from 
                my_calendar_activity a 
            left JOIN 
                my_calendar_custom_activity_types b 
            on 
                a.type = b.id 
            left join 
                my_calendar_preview_images p1
            on 
                p1.id = a.avatar
            left join 
                my_calendar_sign sign1
            on 
                a.id = sign1.activity
            left join 
                my_calendar_subscribe sub1
            on 
                a.id = sub1.activity
            where 
                a.owner = $userid
            GROUP BY 
                a.id
            ";

        $my_joined = "
            select 
                d.*, 
                e.title type_title, 
                e.pub pub,
                p2.image avatar_name,
                count(DISTINCT sub2.id) now_sub,
                count(DISTINCT sign2.id) now_join
            from 
                my_calendar_sign f 
            left join 
                my_calendar_activity d 
            on 
                f.activity = d.id 
            left join 
                my_calendar_custom_activity_types e 
            on 
                d.type = e.id 
            left join 
                my_calendar_preview_images p2
            on 
                p2.id = d.avatar
            left join 
                my_calendar_sign sign2
            on 
                f.activity = sign2.activity
            left join 
                my_calendar_subscribe sub2
            on 
                f.activity = sub2.activity
            where 
                f.tempid = $userid
            GROUP BY 
                f.activity";
                
        $my_subscribed = "
            select 
                y.*, 
                z.title type_title, 
                z.pub,
                p3.image avatar_name,
                count(DISTINCT sub3.id) now_sub,
                count(DISTINCT sign3.id) now_join
            from 
                my_calendar_subscribe x 
            left join 
                my_calendar_activity y 
            on 
                x.activity = y.id 
            left join 
                my_calendar_custom_activity_types z 
            on 
                z.id = y.type  
            left join 
                my_calendar_preview_images p3
            on 
                p3.id = y.avatar
            left join 
                my_calendar_sign sign3
            on 
                x.activity = sign3.activity
            left join 
                my_calendar_subscribe sub3
            on 
                x.activity = sub3.activity
            where 
                x.tempid = $userid 
            GROUP BY 
                x.activity";
                
        $my_subscribed_type = "
            select 
                ay.*, 
                az.title type_title, 
                az.pub,
                p4.image avatar_name,
                count(DISTINCT sub4.id) now_sub,
                count(DISTINCT sign4.id) now_join
            from 
                my_calendar_subscribe_type ax 
            right join 
                my_calendar_activity ay 
            on 
                ay.type = ax.typeid
            left join 
                my_calendar_custom_activity_types az 
            on 
                az.id = ay.type
            left join 
                my_calendar_preview_images p4
            on 
                p4.id = ay.avatar
            left join 
                my_calendar_sign sign4
            on 
                ay.id = sign4.activity
            left join 
                my_calendar_subscribe sub4
            on 
                ay.id = sub4.activity
            where 
                ax.tempid = $userid
            group by 
                ay.id";
                
        $sql = "
            $my_create
                union
            $my_joined
                union
            $my_subscribed
                union
            $my_subscribed_type 
            order by 
                endtime desc;";
        return Db_base::inst()->do_query($sql);
    }
    
    // 已添加now_join, now_sub
    public static function my_list_by_type($userid, $choosed_type){
        $sql = "
        select 
            a.*, 
            b.title type_title, 
            b.pub pub,
            p.image avatar_name,
            count(distinct s.id) now_join,
            count(distinct sub.id) now_sub
        from 
            my_calendar_activity a 
        left JOIN 
            my_calendar_custom_activity_types b 
        on 
            a.type = b.id 
        left join 
            my_calendar_preview_images p
        on 
            p.id = a.avatar
        left join 
            my_calendar_sign s
        on 
            a.id = s.activity
        left join 
            my_calendar_subscribe sub
        on 
            sub.activity = a.id
        where 
            a.type = $choosed_type
        group by 
            a.id
        order by 
            a.endtime desc;
        ";
        return Db_base::inst()->do_query($sql);
    }
    
    // 已添加now_join, now_sub
    public static function my_joined_list($userid){
        $sql = "
        select 
            d.*, 
            e.title type_title, 
            e.pub pub,
            p.image avatar_name,
            count(distinct s.id) now_join,
            count(distinct sub.id) now_sub
        from 
            my_calendar_sign f 
        left join 
            my_calendar_activity d 
        on 
            f.activity = d.id 
        left join 
            my_calendar_custom_activity_types e 
        on 
            d.type = e.id 
        left join 
            my_calendar_preview_images p
        on 
            p.id = d.avatar
        left join 
            my_calendar_sign s
        on 
            d.id = s.activity
        left join 
            my_calendar_subscribe sub
        on 
            sub.activity = f.activity
        where 
            f.tempid = $userid
        group by 
            f.activity
        order by 
            d.endtime desc;
        ";
        return Db_base::inst()->do_query($sql);
    }    
    
    // 已添加now_join, now_sub
    public static function my_subscribed_list($userid){
        $sql = "
            select 
                b.*, 
                c.title type_title, 
                c.pub,
                p.image avatar_name,
                count(distinct sub.id) now_sub,
                count(distinct sign.id) now_join
            from 
                my_calendar_subscribe a 
            left join 
                my_calendar_activity b 
            on 
                a.activity = b.id 
            left join 
                my_calendar_custom_activity_types c 
            on 
                c.id = b.type
            left join 
                my_calendar_preview_images p
            on 
                p.id = b.avatar
            left join 
                my_calendar_sign sign
            on 
                sign.activity = a.activity
            left join 
                my_calendar_subscribe sub
            on 
                sub.activity = a.activity
            where 
                a.tempid = $userid
            group by 
                a.activity
            order by 
                b.endtime desc;
            ";
        return Db_base::inst()->do_query($sql);
    }
    
    // 已添加now_join, now_sub
    public static function share_list($choosed_type){
        $sql = "
        select 
            a.*, 
            b.title type_title, 
            b.pub pub,
            p.image avatar_name,
            count(distinct sub.id) now_sub,
            count(distinct s.id) now_join
        from 
            my_calendar_activity a 
        left JOIN 
            my_calendar_custom_activity_types b 
        on 
            a.type = b.id 
        left join 
            my_calendar_preview_images p
        on 
            p.id = a.avatar
        left join 
            my_calendar_sign s
        on 
            a.id = s.activity
        left join 
            my_calendar_subscribe sub
        on 
            a.id = sub.activity
        where 
            a.type = $choosed_type
        GROUP BY
            a.id
        order by 
            a.endtime desc";
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
                
                
        $sql = "
            select 
                a.*, 
                b.title type_title, 
                b.pub pub,
                p.image avatar_name,
                count(distinct sub.id) now_sub,
                count(distinct s.id) now_join
            from 
                my_calendar_activity a 
            left JOIN 
                my_calendar_custom_activity_types b 
            on 
                a.type = b.id 
            left join 
                my_calendar_preview_images p
            on 
                p.id = a.avatar
            left join 
                my_calendar_sign s
            on 
                a.id = s.activity
            left join 
                my_calendar_subscribe sub
            on 
                a.id = sub.activity
            where
                    match(a.scws_title) 
                    AGAINST('$input') 
                and 
                    b.pub != 0
            GROUP BY
                a.id
            order by 
                a.endtime desc
            limit 
                $start, 30";
        return Db_base::inst()->do_query($sql);
    }
    
    
};


