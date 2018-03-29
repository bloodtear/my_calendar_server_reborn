<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Event {
    private $mSummary = null;

    const EVENT_CODES = array(
        10001 => "创建了单体活动",
        10002 => "修改了单体活动",
        10003 => "暂停了单体活动",
        10004 => "启动了单体活动",
        10005 => "报名了单体活动",
        10006 => "取消报名单体活动",
        10010 => "订阅了单体活动",
        20001 => "创建了日历活动",
        20002 => "修改了日历活动",
        20010 => "订阅了日历活动",
        //10011 => "取消订阅单体活动",
    );
    
    public function __construct($summary = array()) {
        if (empty($summary)) {
            $summary = array(
                "id" => 0,
            );
        }
        $this->mSummary = $summary;
    }

    public function id() {
        return $this->mSummary["id"];
    }
    public function activity() {
        return $this->mSummary["activity"];
    }
    public function activity_detail() {
        $activity = Activity::oneById($this->mSummary["activity"]);
        return $activity ? $activity->packInfo() : $activity;
    }
    public function calendar() {
        return $this->mSummary["calendar"];
    }
    public function calendar_detail() {
        $calendar = Calendar::oneById($this->mSummary["calendar"]);
        return $calendar ? $calendar->packInfo() : $calendar;
    }
    public function event_code() {
        return $this->mSummary["event_code"];
    }
    public function event_content() {
        return self::EVENT_CODES[$this->mSummary["event_code"]];
    }
    public function operator() {
        return $this->mSummary["operator"];
    }
    public function time_stamp() {
        return $this->mSummary["time"];
    }
    public function modify_time() {
        return date("Y-m-d H:i:s",$this->time_stamp());
    }
    public function status() {
        return $this->mSummary["status"];
    }
    public function operator_detail() {
        $operator_id = $this->operator();
        $user = TempUser::oneById($operator_id);
        return $user ? $user->packInfo() : null;
    }


    public function set_activity($n) {
        $this->mSummary["activity"] = $n;
    }
    public function set_calendar($n) {
        $this->mSummary["calendar"] = $n;
    }
    public function set_event_code($n) {
        $this->mSummary["event_code"] = $n;
    }
    public function set_operator($n) {
        $this->mSummary["operator"] = $n;
    }
    public function set_time($n) {
        $this->mSummary["time"] = $n;
    }
    public function set_status($n) {
        $this->mSummary["status"] = $n;
    }

    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = db_event::inst()->add($this->activity(), $this->calendar(), $this->event_code(), $this->operator());
            if ($id !== false) {
                $this->mSummary["id"] = $id;
            }
        } else {
            $id = db_event::inst()->modify($this->id(), $this->activity(), $this->calendar(), $this->event_code(), $this->operator(), $this->status());
        }
        return $id;
    }

    public function packInfo($detail = false) {
       return array(
            "id" => $this->id(),
            "activity" => $this->activity(),
            "activity_detail" => $this->activity_detail(),
            "calendar" => $this->calendar(),
            "calendar_detail" => $this->calendar_detail(),
            "event_code" => $this->event_code(),
            "event_content" => $this->event_content(),
            "operator" => $this->operator(),
            "operator_detail" => $this->operator_detail(),
            "time" => $this->time_stamp(),
            "modify_time" => $this->modify_time(),
            "status" => $this->status()
        );
    }

    public static function create($id) {
        $summary = db_event::inst()->get($id);
        return new Event($summary);
    }
    
    public static function record($activity, $calendar, $event_code, $operator) {
        $event = Event::load($activity, $calendar, $event_code, $operator);
        if (empty($event)) {
            $event = new Event();
        }
        $event->set_activity($activity);
        $event->set_calendar($calendar);
        $event->set_event_code($event_code);
        $event->set_operator($operator);

        $ret = $event->save();
        
        return array("ret" => $ret, "event" => $event->packInfo());
    }
    
    public static function edit_one($event_id, $activity,  $calendar,  $event_code,  $operator) {
        
        $event = Event::oneById($event_id);
            
        $event->set_activity($activity);
        $event->set_calendar($calendar);
        $event->set_event_code($event_code);
        $event->set_operator($operator);

        $ret = $event->save();
        
        return array("ret" => $ret, "event" => $event);
    }
    
    public static function oneById($id) {
        $events = self::cachedAll();
        foreach ($events as $event) {
            if ($event->id() == $id) {
                return $event;
            }
        }
        return null;
    }
    
    public static function load($activity, $calendar, $event_code, $operator) {
        $ret = db_event::inst()->load($activity, $calendar, $event_code, $operator);
        if ($ret) {
            return new Event($ret);
        }else {
            return null;
        }
    }

    public static function all() {
        $items = db_event::inst()->all();
        $arr = array();
        foreach ($items as $id => $summary) {
            $arr[$id] = new Event($summary);
        }
        return $arr;
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.event.all", null);
        if ($all === null) {
            $all = Event::all();
            $cache->save("class.event.all", $all);
        }
        return $all;
    }

    public static function remove($id) {
        return db_event::inst()->remove($id);
    }
    
    



// 我个人 && 我相关组织创建的活动 && 我关注的活动 && 我报名的活动
/*
    select c.id from yyba_activity c join yyba_organization_member b on b.organization = c.owner  where c.type = 2 and b.user = 5
    union 
    select a.id from yyba_activity a where a.type = 1 and a.owner = 5
    union
    select sub.activity from yyba_subscribe sub where sub.user = 5
    union
    select yyba_sign.activity from yyba_sign where yyba_sign.user = 5 
*/

    
    public static function get_activity_event_list($userid){
        $mysql = "
            SELECT x.*, act.title activity_title, tempu.nickname operator_name, tempu.avatar operator_avatar FROM yyba_event x 
            join (
                SELECT c.id FROM yyba_activity c join yyba_organization_member b ON b.organization = c.owner WHERE c.type = 2 and b.user = $userid   
                union 
                SELECT a.id FROM yyba_activity a WHERE a.type = 1 and a.owner = $userid
                union
                SELECT sub.activity FROM yyba_subscribe sub WHERE sub.user = $userid
                union
                SELECT yyba_sign.activity FROM yyba_sign WHERE yyba_sign.user = $userid 
            ) y 
            ON y.id = x.activity
            join 
                yyba_activity act ON act.id = x.activity
            join 
                yyba_tempuser tempu ON x.operator = tempu.id order by x.time desc";
                
        return db_base::inst()->do_query($mysql);
    }

    
// 我个人创建的活动， 应该收到所有信息，包括修改，暂停，启动，关注，报名
// 我组织创建的活动， 应该收到所有信息，包括修改，暂停，启动，关注，报名
// 我关注的活动，应收到修改，暂停，启动信息
// 我报名的活动，应收到修改，暂停，启动信息。

// 修改，暂停，启动 ： XXX 活动 被修改/暂停/启动了
// 关注，报名 ： xxx 活动 被关注/报名了。

// 所有我自身相关的都应当被记录，并表现为 “你” xxxx 了  xxxx活动
    
    
    
/*
    const EVENT_CODES = array(
        10001 => "创建单体活动",
        10002 => "修改单体活动",
        10003 => "暂停单体活动",
        10004 => "启动单体活动",
        10005 => "报名单体活动",
        10006 => "取消报名单体活动",
        10010 => "订阅单体活动",
        20001 => "创建日历活动",
        20002 => "修改日历活动",
        20010 => "订阅日历活动",
        //10011 => "取消订阅单体活动",
    ); 
*/
    
    public static function get_activity_event_list_new($userid){
        $mysql = "
        SELECT z.*,act.title, tempu.nickname operator_name, tempu.avatar operator_avatar  FROM yyba_event z JOIN (
            SELECT x1.* FROM yyba_event x1 
            JOIN (
                SELECT c.id FROM yyba_activity c JOIN yyba_organization_member b ON b.organization = c.owner WHERE c.type = 2 and b.user = $userid   
                UNION 
                SELECT a.id FROM yyba_activity a WHERE a.type = 1 and a.owner = $userid
            ) y1 ON y1.id = x1.activity
            UNION (
            SELECT x2.* FROM yyba_event x2 
            JOIN (
                SELECT sub.activity FROM yyba_subscribe sub WHERE sub.user = $userid
                UNION
                SELECT yyba_sign.activity FROM yyba_sign WHERE yyba_sign.user = $userid 
            ) y2 ON y2.activity = x2.activity WHERE x2.event_code = 10002 OR x2.event_code = 10003 OR x2.event_code = 10004
            )
            UNION (
            SELECT x3.* FROM yyba_event x3 WHERE x3.operator = $userid
            )
        ) evt ON evt.id = z.id 
        JOIN 
            yyba_activity act ON act.id = z.activity
        JOIN 
            yyba_tempuser tempu ON z.operator = tempu.id
        order by z.time desc    
        ";
            
        return db_base::inst()->do_query($mysql);
    }

    
    public static function get_calendar_event_list_new($userid){
        $mysql = "
            SELECT z.*,cal.title , tempu.nickname operator_name, tempu.avatar operator_avatar  FROM yyba_event z JOIN (
            select a.* from yyba_calendar a  where a.owner = $userid and a.type =1
            UNION
            select c.* from yyba_calendar c JOIN 
            (select * from yyba_organization_member where user = $userid )b on c.owner = b.organization and c.type =2 
            union 
            select e.* from yyba_calendar e JOIN
            (select * from yyba_subscribe where user = $userid) d on d.calendar = e.id
            ) cals ON cals.id = z.calendar 
            JOIN 
            yyba_calendar cal ON cal.id = z.calendar
            JOIN 
            yyba_tempuser tempu ON z.operator = tempu.id where z.activity = 0
            order by z.time desc  
            ";
            
        return db_base::inst()->do_query($mysql);
    }

    
    public static function get_event_list_new($userid){
        $mysql = "
        
    SELECT z.*,act.title, tempu.nickname operator_name, tempu.avatar operator_avatar  FROM yyba_event z JOIN (
        SELECT x1.* FROM yyba_event x1 
        JOIN (
            SELECT c.id FROM yyba_activity c JOIN yyba_organization_member b ON b.organization = c.owner WHERE c.type = 2 and b.user = $userid   
            UNION 
            SELECT a.id FROM yyba_activity a WHERE a.type = 1 and a.owner = $userid
        ) y1 ON y1.id = x1.activity
        UNION (
        SELECT x2.* FROM yyba_event x2 
        JOIN (
            SELECT sub.activity FROM yyba_subscribe sub WHERE sub.user = $userid
            UNION
            SELECT yyba_sign.activity FROM yyba_sign WHERE yyba_sign.user = $userid 
        ) y2 ON y2.activity = x2.activity WHERE x2.event_code = 10002 OR x2.event_code = 10003 OR x2.event_code = 10004
        )
        UNION (
        SELECT x3.* FROM yyba_event x3 WHERE x3.operator = $userid
        )
    ) evt ON evt.id = z.id 
    JOIN 
        yyba_activity act ON act.id = z.activity
    JOIN 
        yyba_tempuser tempu ON z.operator = tempu.id

        
    union 


    SELECT z.*,cal.title , tempu.nickname operator_name, tempu.avatar operator_avatar  FROM yyba_event z JOIN (
    select a.* from yyba_calendar a  where a.owner = $userid and a.type =1
    UNION
    select c.* from yyba_calendar c JOIN 
    (select * from yyba_organization_member where user = $userid )b on c.owner = b.organization and c.type =2 
    union 
    select e.* from yyba_calendar e JOIN
    (select * from yyba_subscribe where user = $userid) d on d.calendar = e.id
    ) cals ON cals.id = z.calendar 
    JOIN 
    yyba_calendar cal ON cal.id = z.calendar
    JOIN 
    yyba_tempuser tempu ON z.operator = tempu.id where z.activity = 0

    order by time DESC
        ";
            
        return db_base::inst()->do_query($mysql);
    }

};


