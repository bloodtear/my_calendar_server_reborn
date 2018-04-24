<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Wxapi {
    
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
        if (empty($wx_auth_ret->error)) {
            $_SESSION["WX_ACCESS_TOKEN"] = $wx_auth_ret->access_token;
            $_SESSION["WX_ACCESS_TOKEN_EXPIRES_IN"] = $wx_auth_ret->expires_in + time();
            return true;
        }else {
            return false;
        }
    }
    
    public static function check_access_token() {
        $wx_acess_token = isset($_SESSION['WX_ACCESS_TOKEN']) ? $_SESSION['WX_ACCESS_TOKEN'] : null;
        $wx_acess_token_expires_in = isset($_SESSION['WX_ACCESS_TOKEN_EXPIRES_IN']) ? $_SESSION['WX_ACCESS_TOKEN_EXPIRES_IN'] : null;
        if (empty($wx_acess_token) || empty($wx_acess_token_expires_in) || time() > $wx_acess_token_expires_in) {
            Wxapi::get_access_token();
        }
    }
    
            
    public static function get_wx_acode($page, $scene){
        Wxapi::check_access_token();
        $wx_acess_token = $_SESSION['WX_ACCESS_TOKEN'];
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
    
    public static function send_welcome_msg($openid) {
        Wxapi::check_access_token();
        $wx_acess_token = $_SESSION['WX_ACCESS_TOKEN'];
        \framework\Logging::d("wx_acess_token", $wx_acess_token);

        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $wx_acess_token;
        $postString = array(
            "touser" => $openid,
            "msgtype" => "link",
            "link" => array(
                "title" => "Happy Day",
                "description" => "Is Really A Happy Day",
                "url" => "URL",
                "thumb_url" => "THUMB_URL"
            );
        );
        $ret = comm_curl_request($url, json_encode($postString));
        return $ret;
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