<?php

$redis=new Redis();
$redis->connect("localhost",6379);

$ua=array('User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36');
$ct=array('ContentType'=>'application/json; charset=UTF-8');

$userInfo=file_get_contents("userInfo");
$userInfo=json_decode($userInfo,true);

$loginInfo=file_get_contents("loginInfo");
$loginInfo=json_decode($loginInfo,true);

$contacts=file_get_contents("contacts");
$contacts=json_decode($contacts,true);

$uid=$userInfo["UserName"];
foreach ($contacts as $user) {
	if($user["NickName"]=="陈一竹"){
		$uid=$user["UserName"];
	}
}

$ua=array('User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36');
$ct=array('ContentType'=>'application/json; charset=UTF-8');

$curl= curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_COOKIEFILE, "cookie.txt");
curl_setopt($curl, CURLOPT_COOKIEJAR, "cookie.txt");

while(1){

$url=$loginInfo["url"]."/webwxsync?sid=".$loginInfo["wxsid"]."&skey=".$loginInfo["skey"]."&pass_ticket=".$loginInfo["pass_ticket"];
$data=array('BaseRequest'=>$loginInfo["BaseRequest"],'SyncKey'=>$loginInfo["SyncKey"],'rr'=>(~time()));
curl_setopt($curl, CURLOPT_URL,$url);
curl_setopt($curl,CURLOPT_POST,1);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl,  CURLOPT_HTTPHEADER,array_merge($ua,$ct));
$result = curl_exec($curl);
$result=json_decode($result,true);

var_dump($result);

$loginInfo["SyncKey"]=$result["SyncCheckKey"];
$aTemp=array();
foreach ($result["SyncCheckKey"]["List"] as $value) {
    $aTemp[]=$value["Key"]."_".$value["Val"];
}
$loginInfo["synckey"]=implode("|", $aTemp);

foreach ($result["AddMsgList"] as  $value) {
	$redis->lpush("wx_message",json_encode($value,JSON_UNESCAPED_UNICODE));
}

}
?>