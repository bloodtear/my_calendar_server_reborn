<?php

namespace my_calendar_server_reborn\controller\api\v1;
use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Test_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    public function preaction($action) {
        return false;
    }


    public function test1() {
        return false;
        $text = '我是共产主义的接班人';
        $scws = new app\Scws();
        $split_result = $scws->split_text($text);
        return $split_result;
        
    }

    public function editsetting() {
        return false;
        $id = get_request_assert("id");
        $val = get_request_assert("value");
        $ret = setting::instance()->update($id, $val);
        return $this->checkRet($ret);
    }
    
    public function add_scws_titie() {
        return false
        $start = 3100;
        $scws = app\Scws::instance();
        while ($start < 160068) {

            $end = $start + 1000;

            $data = '';
            $sql = "
            select 
                a.id ,a.title 
            from 
                my_calendar_activity a 
            limit 
                $start, $end"; 
            $data = database\db_base::inst()->do_query($sql);
            
            //\framework\Logging::l('data',json_encode($data));
            $data_new = '';
            foreach ($data as $k => $v) {
                
                $id = $v['id'];
                $title = $v['title'];
                $scws_title = $scws->split_text($title);
                
                $string = '';
                if ($k != 0) {
                    $string .= ',';
                }
                
                $string .= "($id, '$scws_title')";
                
                $data_new .= $string;
                
            }
            //\framework\Logging::l('scws_title',$data_new);
            //return false;
            $sql = "
            insert into 
            my_calendar_activity 
            (`Id`,`scws_title`) 
            values 
            $data_new
            on duplicate key update 
            scws_title=values(scws_title);"; 
            $data = database\db_base::inst()->do_query($sql);
            
            $start = $end;
        }
        
    }
    
    
    // xy 20180316
    // 30k 测试用户 
    public function add_testuser() {
        return false;
        $data = "";
        for ($i = 0; $i < 30000; $i++) {
            if ($i != 0) {
                $data .= ',';
            }
            $openid = md5(uniqid($i));
            $calendar_session = md5(uniqid($i));
            $nickname = "testuser_$i";
            $avatar = 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJnlolKibNOGq3tdTwnSWdSia79ZcaiaoJUcEoJuq7XqI6PJxUgQibGrmLwsGWRcmU2OO9ODBysGibY7BQ/0';
            $token = md5(uniqid($i));
            $lastlogin = 0;
            $createtime = time();
            $status = 0;
            $uid = 0;
            
            $data .= "
            ('$openid', 
            '$calendar_session', 
            '$nickname', 
            '$avatar', 
            '$token', 
            '$lastlogin',
            '$createtime',
            '$status',
            '$uid')";
            }
        //logging::d('data', $data);
        //return false;
        $sql = "
            insert into 
            my_calendar_tempuser 
            (openid, 
            calendar_session, 
            nickname, 
            avatar, 
            token, 
            last_login,
            create_time,
            status,
            uid) 
            values $data";
        return db_base::inst()->do_query($sql);
    }

    
    // xy 20180316
    // 30k 测试用户， 每个用户大概有5-10分类, 180k分类 
    public function add_custom_type() {
        return false;
        $sql = "select id from my_calendar_tempuser";
        $tempid_data = array_keys(db_base::inst()->do_query($sql));
        
        array_push($tempid_data, 6,7,8,9);
       
        $user_count = count($tempid_data);
        
        $data = "";
        for ($i = 0; $i < 30000; $i++) {
            if ($i != 0) {
                $data .= ',';
            }
            
            $tempid = $this->get_random($tempid_data, $user_count);
            $title = md5(uniqid($i));
            $pub = rand(0, 1);

            $data .= "
            ('$tempid', 
            '$title', 
            '$pub')";
        }
        //logging::d('data', $data);
        //return false;
        $sql = "
            insert into 
            my_calendar_custom_activity_types
            (tempid, 
            title, 
            pub) 
            values $data";
        return db_base::inst()->do_query($sql);
        

    }
    
    
    // 30k 测试用户， 每个用户大概有5活动, 160k活动
    public function add_activity() {
        // xy 20180316
        // 活动需要有关联数组，必须先查询一套对应的userid, typeid来才可以使用，否则会出现无效数据 
        return false;
        $sql = "select tempid, id typeid from my_calendar_custom_activity_types";
        $tempid_typeid_array = db_base::inst()->do_query($sql);
        
        $count = count($tempid_typeid_array);

        $start = 0;
        $data = "";
        for ($i = $start + 0; $i < $start + 20000; $i++) {
            if ($i != $start + 0) {
                $data .= ',';
            }
            
            $couple = $this->get_random($tempid_typeid_array, $count);
            //logging::d('couple', json_encode($couple));
            
            $owner = $couple['tempid'];
            $type = $couple['typeid'];
            
            $title = "标题_$i" . "_" . time();
            $avatar = rand(1, 2);
            $content = '内容_' . md5(uniqid($i));
            $images = '';
            $createtime = time();
            $modifytime = time();
            $begintime = time();
            $endtime = time();
            $repeattype = 0;
            $repeatend = 0;
            $address =  '地址_' . md5(uniqid($i));
            $participants = 0;
            $joinsheet = '';
            $clickcount = 0;
            $status = 0;
            $pid = 0;
            $notice = rand(0, 1);

            $data .= "
            ('$owner', 
            '$title', 
            '$avatar', 
            '$content', 
            '$images', 
            '$createtime', 
            '$modifytime', 
            '$begintime', 
            '$endtime', 
            '$repeattype', 
            '$repeatend', 
            '$address', 
            '$participants', 
            '$joinsheet',  
            '$type', 
            '$clickcount', 
            '$status', 
            '$pid', 
            '$notice')";
            }
        //logging::d('data', $data);
        //return false;
        $sql = "
            insert into 
            my_calendar_activity
            (owner, 
            title, 
            avatar, 
            content, 
            images, 
            createtime, 
            modifytime, 
            begintime, 
            endtime, 
            repeattype, 
            repeatend,
            address,
            participants,
            joinsheet,
            type,
            clickcount,
            status,
            pid,
            notice) 
            values $data";
        return db_base::inst()->do_query($sql);
    }
    
    
    public function add_sign() {
        return false;
        $data = "";
        for ($i = 0; $i < 10500; $i++) {
            if ($i != 0) {
                $data .= ',';
            }
            $tempid = rand(6, 10000);
            $activity = rand(1020, 101063);
            $sheet = '{"name":"","phone":"","comment":""}';
            $modify_time = time();
            $notice = rand(0, 1);

            $data .= "
            ('$activity', 
            '$tempid', 
            '$sheet', 
            '$modify_time', 
            '$notice')";
            }
        //logging::d('data', $data);
        //return false;
        $sql = "
            insert into 
            my_calendar_sign
            (activity, 
            tempid, 
            sheet, 
            modify_time, 
            notice) 
            values $data";
        return db_base::inst()->do_query($sql);
    }
    
    
    public function add_subscribe() {
        return false;
        $data = "";
        for ($i = 0; $i < 10500; $i++) {
            if ($i != 0) {
                $data .= ',';
            }
            $tempid = rand(6, 10000);
            $activity = rand(1020, 101063);
            $time = time();

            $data .= "
            ('$activity', 
            '$tempid', 
            '$time')";
            }
        //logging::d('data', $data);
        //return false;
        $sql = "
            insert into 
            my_calendar_subscribe
            (activity, 
            tempid, 
            time) 
            values $data";
        return db_base::inst()->do_query($sql);
    } 
    
    public function add_subscribe_type() {
        return false;
        $data = "";
        for ($i = 0; $i < 80500; $i++) {
            if ($i != 0) {
                $data .= ',';
            }
            $tempid = rand(6, 10000);
            $typeid = rand(1020, 101063);
            $time = time();
            $alias = md5(uniqid());

            $data .= "
            ('$typeid', 
            '$tempid', 
            '$time', 
            '$alias')";
            }
        //logging::d('data', $data);
        //return false;
        $sql = "
            insert into 
            my_calendar_subscribe_type
            (typeid, 
            tempid, 
            time,
            alias) 
            values $data";
        return db_base::inst()->do_query($sql);
    }
    
    public function get_random($arr, $count) {
        $key = rand(0, $count);
        $ret = isset($arr[$key]) ? $arr[$key] : 0;
        if (empty($ret)) {
            $this->get_random($arr, $count);
        }
        return $ret;
    }
    
    
}







