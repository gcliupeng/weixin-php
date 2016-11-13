<?php

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

file_put_contents("loginInfo", json_encode($loginInfo));

foreach ($result["AddMsgList"] as  $value) {
	if($value["FromUserName"]==$uid){
		sendMsg($value["Content"],$uid);
	}
}


// break;


// 	sendMsg("dddd",$uid);
// 	sleep(3);
}

function sendMsg($msg,$uid){
	global $loginInfo,$ua,$contacts,$userInfo,$curl,$ct;
	$url = $loginInfo["url"]."/webwxsendmsg?lang=zh_CN&pass_ticket=".$loginInfo["pass_ticket"];
	$content=json_encode(array("BaseRequest"=>$loginInfo["BaseRequest"],"Msg"=>array(
		'Type'=>1,
		'Content'=>$msg,
		'FromUserName'=>$userInfo["UserName"],
//		'ToUserName'=>'@00f7a4d0649ed60704fbc7a3fe14d3d70605201d81b0c4686e68ab5f9e519dff',
		'ToUserName'=>$uid,
		'LocalID'=>$loginInfo["msgid"],
		'ClientMsgId'=>$loginInfo["msgid"]
		),"Scene"=>0),JSON_UNESCAPED_UNICODE);  
	$loginInfo["msgid"]+=1;
	file_put_contents("loginInfo", json_encode($loginInfo));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,  CURLOPT_HTTPHEADER, array_merge($ua,$ct));

    $result = curl_exec($curl);  //$result 获取页面信息
    var_dump($result);
}

//var_dump($result);
?>