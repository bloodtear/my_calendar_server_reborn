<?php

namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Customer_service_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    
    public function pretreat() {
  

        
    }

    public function receive_msg() {
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = "2ghlmcl1hblsqt";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function posttreat() {

        unset_session('userid');
        unset_session('username');
        unset_session('calendar_session');
        
    }
    
    
}