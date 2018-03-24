<?php
require_once ('HttpClient.class.php');
class Sms{
public function sendSms($data){
	//UID ,mobile,content,sendto 
$pageContents = HttpClient::quickPost('http://211.147.242.161:8888/sms.aspx',
    array(
    'action' => 'send',
    'userid' => '6037',
    'account'=>'粤力租贷',
    'password'=>'ylzd789',
    'mobile'=>$data["mobile"],
    'content'=>$data["content"],
    'sendtime'=>'',
    'extno'=>'',
));
    /* 'action' => 'send',
    'userid' => '用户id',
    'account'=>'账号',
    'password'=>'密码',
    'mobile'=>$data["mobile"],
    'content'=>$data["content"],
    'sendtime'=>'',
    'extno'=>''*/

}


}

?>