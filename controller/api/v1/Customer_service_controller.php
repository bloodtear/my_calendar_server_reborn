<?php

namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Customer_service_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    
    public function pretreat() {
  

        
    }

    public function receive_msg() {
        
        $check_sign = app\Customer_service::check_sign();
        \framework\Logging::l("check", json_encode($check_sign));
        if (empty($check_sign)) {
            return array('op' => 'fail', "code" => '100002', "reason" => 'not from wx_server');
        }
        
        $input = file_get_contents('php://input');
        
        $customer_msg = new app\Customer_service(json_decode($input,true));
        
        switch ($customer_msg->MsgType()) {
            case 'event': 
                $ret = $customer_msg->send_welcome_msg();
            break;
        }
        
        \framework\Logging::l("send_msg", json_encode($ret));
        
        return $ret;
        
        
        

    }

    public function posttreat() {

        unset_session('userid');
        unset_session('username');
        unset_session('calendar_session');
        
    }
    
    
}
