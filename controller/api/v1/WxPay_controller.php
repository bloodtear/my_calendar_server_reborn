<?php
namespace my_calendar_server_reborn\controller\api\v1;

class WxPay_controller  {
	
    public function pay() {
		 $code = get_request("code");
		 
		 $nickName= get_request("nickName");
		 $avatarUrl= get_request("avatarUrl");
		 $display = get_request("display");
        //重新通过code获取openid和session_key;
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $postString = array(
            "appid" => WX_APPID,
            "secret" => WX_SECRET,
            "js_code" => "$code",
            "grant_type" => "authorization_code");
        $wx_auth_ret = comm_curl_request($url, $postString);
        $wx_auth_ret = json_decode($wx_auth_ret);
        if (!empty($wx_auth_ret->errcode)) {
            logging::e("WXLOGIN", "errcode: " . $wx_auth_ret->errcode);
            logging::e("WXLOGIN", "errmsg: " . $wx_auth_ret->errmsg);
            return array("status" => "fail", "errcode" => $wx_auth_ret->errcode, "errmsg" => $wx_auth_ret->errmsg);
        }
        
        //登录校验
        $openid = $wx_auth_ret->openid;
		
		$appid=WX_APPID;  
 
			$mch_id=MCH_ID;  
		$key=PAY_KEY;  
			$time =time();
		$out_trade_no = $mch_id. $time; 
		
		$total_fee =get_request("total_fee"); 
		
		if(empty($total_fee)) //押金  
		{  
			$body = "充值押金";  
			$total_fee = floatval(99*100);  
		}  
		else {  
			$body = "充值余额";  
			$total_fee = floatval($total_fee*100);  
		}  
		$weixinpay = new WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee);  
		payment::add($nickName, $avatarUrl, $total_fee, '' . $time . '', $display,"false",$out_trade_no);
		$return=$weixinpay->pay();  
  
		echo json_encode($return);  
	}
 public function pay_success() {
	 $out_trade_no =get_request("out_trade_no");
	 $updata['status'] = 'true';
	 logging::d("payment:" , $out_trade_no);
return payment::update_one_payment($updata,$out_trade_no);

}}

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
