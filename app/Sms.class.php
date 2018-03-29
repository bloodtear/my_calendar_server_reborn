<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Sms {
    
    const CHINA_NATION_CODE = 86;

    private static $instance;
    
    private $sms_single_sender;

    public static function instance() {
        if (empty(self::$instance)) {
            self::$instance = new Sms();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->sms_single_sender = new sms\SmsSingleSender(WX_SMS_SDKID, WX_SMS_SECRET);
    }
    
    public function send_verify_code($phone_number, $random_code) {
                
        $nationCode = self::CHINA_NATION_CODE;
        $templId    = 50285;
        $params     = [$random_code];

        return $this->sms_single_sender->sendWithParam($nationCode, $phone_number, $templId, $params);
        
    }
    
    
    
    
    
    
};

