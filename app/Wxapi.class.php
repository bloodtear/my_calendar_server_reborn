<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Wxapi {
    
    public static function check_token() {
        $token_set = Setting::get_by_name("token");

        if (empty($token_set) || $token_set->expired() < time()) {
            $access_token_ret = Wxapi::get_access_token();
            \framework\Logging::l("access_token_ret", json_encode($access_token_ret));
            if (isset($access_token_ret->errcode)) {
                return false;
            }
            
            $new_token = $access_token_ret->access_token;
            $expired = $access_token_ret->expires_in + time();
            
            if (empty($token_set)) {
                $token_set = new Setting();
            }
            
            $token_set->setName('token');
            $token_set->setValue($new_token);
            $token_set->setExpired($expired);
            $token_set->setStatus(0);
            $save = $token_set->save();
            
            if (empty($save)) {
                return false;
            }
            
        }
        
        $token = $token_set->value();
        
        return $token;
    }
    
    public static function wx_auth($code){
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $postString = array(
            "appid" => WX_APPID,
            "secret" => WX_SECRET,
            "js_code" => $code,
            "grant_type" => "authorization_code");
        $wx_auth_ret = comm_curl_request($url, $postString);
        return json_decode($wx_auth_ret);
    }

    
    public static function get_access_token() {
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $postString = array(
            "grant_type" => "client_credential",
            "appid" => WX_APPID,
            "secret" => WX_SECRET,);
        $wx_auth_ret = json_decode(comm_curl_request($url, $postString));
        return $wx_auth_ret;
    }
            
    public static function get_wx_acode($page, $scene){
        $wx_acess_token = Wxapi::check_token();

        \framework\Logging::d("wx_acess_token", $wx_acess_token);
        \framework\Logging::d("page", $page);
        \framework\Logging::d("scene", $scene);
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $wx_acess_token;
        $postString = array(
            "path" => $page,
            "scene" => $scene,
            "width" => 430
        );
        $ret = comm_curl_request($url, json_encode($postString));
        return $ret;
    }
    
    //get unionid 解码相关
    public static function unsign($sessionKey, $encryptedData, $iv) {
        
        \framework\Logging::d("sessionKey", $sessionKey);
        \framework\Logging::d("encryptedData", $encryptedData);
        \framework\Logging::d("iv", $iv);
        
        $pc = new sign\WXBizDataCrypt(WX_APPID, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode == 0) {
            return array("op" => "get_unionid", 'data' => $data);
        } else {
            return array('op' => 'fail', "code" => $errCode, "reason" => '解码失败');
        }
    }
     
    public static function sendMsg($json) {
        $token = self::check_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $token;
        $postString = $json;
        $wx_auth_ret = json_decode(comm_curl_request($url, json_encode($postString, JSON_UNESCAPED_UNICODE)));
        return $wx_auth_ret;
    }
}



function comm_curl_request($url,$postString='',$httpHeader='')  { 
    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$url);  
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postString);  
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //这个是重点。不加这curl报错
    curl_setopt($ch,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);  

    if(!empty($httpHeader) && is_array($httpHeader))  
    {  
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);  
    }  
    $data = curl_exec($ch);  
    $info = curl_getinfo($ch);  
    //var_dump(curl_error($ch)); 
    //var_dump($info);  
    curl_close($ch);  
    return $data;  
}  




?>
