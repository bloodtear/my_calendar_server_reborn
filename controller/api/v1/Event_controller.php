<?php
namespace my_calendar_server_reborn\controller\api\v1;

class Event_controller extends v1_base {
    private $mToken = null;
    private $mUser = null;

    public function preaction($action) {
        //$token = get_request_assert("token");
    }
    
    public function get_all_my_list(){
        $yuyue_session = get_request("owner");
        logging::d("ORGED_LIST yuyue_session:", $yuyue_session);
        
        $user = TempUser::oneBySession($yuyue_session);
        if (empty($user)) {
            return array('op' => 'fail', "code" => '000002', "reason" => '无此用户');
        }
        $userid = $user->id();

        $my_event_list = Event::get_event_list_new($userid);
        $event_code = Event::EVENT_CODES;
        $my_event_list_new = [];
        
        foreach ($my_event_list as $id => $event) {
            if ($event["operator"] == $userid){
                $event["operator_name"] = '你';
            }
            $event['time_content'] = time_content($event['time']);
            $event['event_content'] = $event_code[$event['event_code']];
            if ($event["calendar"] != 0 && $event["activity"] == 0 ) {
                $event['view_url'] = '../create/calendar?id=' . $event["calendar"];
            }else {
                $event['view_url'] = '../activity/detail?id=' . $event["activity"];
            }
            array_push($my_event_list_new, $event);
        }
        
        return $this->op("event_list", $my_event_list_new);
        
    }

}



function add_month($stamp){
        
    $y = date("Y",$stamp);
    $m = date("n",$stamp);
    $d = date("d",$stamp);
    $h = date("H",$stamp);
    $m = date("m",$stamp);
    $s = date("s",$stamp);
    
    $r = increate_month($y, $m);
    $y = $r['y'];
    $m = $r['m'];
    while(!check_valid($y, $m, $d)){
        $r = increate_month($y, $m);
        $y = $r['y'];
        $m = $r['m'];
    }
    
    $new_date = "$y-$m-$d $h:$m:$s";

    logging::d("add_month", $new_date);
    logging::d("add_month strtotime", strtotime($new_date));
    return strtotime($new_date);
}


function check_valid($y, $m, $d) {
    switch ($m) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            if ($d > 31 || $d < 0) {
                return false;
            }
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            if ($d > 30 || $d < 0) {
                return false;
            }
            break;
        case 2:
            if ($y % 4 == 0) {
                if ($d > 29 || $d < 0) {
                    return false;
                }
            }else {
                if ($d > 28 || $d < 0) {
                    return false;
                }
            }
            break;
        default:
            return false;
            break;
    }
    return true;
}

function increate_month($y, $m){
    if ($m != 12) {
        $m++;
    }else {
        $m = 1;
        $y++;
    }
    return array("y" => $y, "m" => $m);
}

function time_content($time) {
    $t = time() - $time;  
    $f = array(  
        '31536000'=>'年',  
        '2592000'=>'个月',  
        '604800'=>'星期',  
        '86400'=>'天',  
        '3600'=>'小时',  
        '60'=>'分钟',  
        '1'=>'秒'  
    );  
    foreach ($f as $k => $v)    {  
        $c = floor($t / (int)$k);
        if ($c != 0) {  
            return $c.$v.'前';  
        }  
    }  
}
