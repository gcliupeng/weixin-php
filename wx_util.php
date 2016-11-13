<?php
$uuid="";
$login_Info=array();
$userInfo=array();
$contactArray=array();
$ua=array('User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36');
$ct=array('ContentType'=>'application/json; charset=UTF-8');

$curl= curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_COOKIEFILE, "cookie.txt");
curl_setopt($curl, CURLOPT_COOKIEJAR, "cookie.txt");

function get_QRuuid(){
	global $ua,$curl,$uuid;
	$url = "https://login.weixin.qq.com/jslogin?appid=wx782c26e4c19acffb&fun=new";
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,0);
    curl_setopt($curl,  CURLOPT_HTTPHEADER, $ua);
    $result = curl_exec($curl);  //$result 获取页面信息 
    preg_match('/window.QRLogin.code = (\d+); window.QRLogin.uuid = "(\S+?)"/', $result,$matches);
    $uuid=$matches[2];
    echo "uuid is $uuid\r\nbegin create QR picture\r\n";
  //  return $matches[2];
}

function get_QR(){
	global $uuid ,$ua,$curl;
	$url = "https://login.weixin.qq.com/qrcode/$uuid";
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,0);
    curl_setopt($curl,  CURLOPT_HTTPHEADER, $ua);
    $result = curl_exec($curl);  //$result 获取页面信息
    file_put_contents("pic.png",$result);
    echo "QR picture created\r\n";
}

function check_Login(){
	global $uuid,$login_Info, $ua ,$curl;
	$url = "https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?tip=1&uuid=$uuid"."&_=".time();
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,0);
    curl_setopt($curl,  CURLOPT_HTTPHEADER, $ua);
    $result = curl_exec($curl);  //$result 获取页面信息
    preg_match('/window.code=(\d+)/', $result,$matches);
    $code=$matches[1];
    preg_match('/window.redirect_uri="(\S+)/', $result,$matches);
    $login_Info["url"]=$matches[1];
    return intval($code);
}

function get_LoginInfo(){
	global $login_Info, $ua, $curl;
		curl_setopt($curl, CURLOPT_URL,$login_Info["url"]);
		curl_setopt($curl,CURLOPT_POST,0);
    	curl_setopt($curl,  CURLOPT_HTTPHEADER, $ua);
    	$result = curl_exec($curl);  //$result 获取页面信息
    	$xml = simplexml_load_string($result,'SimpleXMLElement', LIBXML_NOCDATA);
    	$data = json_decode(json_encode($xml),true);
    	// var_dump($data);
    	$login_Info["url"]=substr($login_Info["url"], 0,strrpos($login_Info["url"], "/"));
    	$login_Info["fileUrl"]=$login_Info["syncUrl"]=$login_Info["url"];
    	$login_Info["deviceid"]="e954892788548023";
    	$login_Info["msgid"]=time()*1000;
    	$login_Info["BaseRequest"]=array();
    	
    	$login_Info["skey"]=$login_Info["BaseRequest"]["Skey"]=$data["skey"];
    	$login_Info["wxsid"]=$login_Info["BaseRequest"]["Sid"]=$data["wxsid"];
    	$login_Info["wxuin"]=$login_Info["BaseRequest"]["Uin"]=$data["wxuin"];
    	$login_Info["pass_ticket"]=$login_Info["BaseRequest"]["DeviceID"]=$data["pass_ticket"];
    	$login_Info["BaseRequest"]["DeviceID"]="e954892788548033";
    	
}

function web_init(){ 
	global $login_Info, $ua,$userInfo, $curl,$ct;
	$url = $login_Info["url"]."/webwxinit?r=".time()."&pass_ticket=".$login_Info["pass_ticket"];
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    $content=json_encode(array("BaseRequest"=>$login_Info["BaseRequest"])); 
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl,  CURLOPT_HTTPHEADER,array_merge($ua,$ct));
    $result = curl_exec($curl);  //$result 获取页面信息
    file_put_contents('back',$result);
    $result=json_decode($result,true);
    $userInfo["UserName"]=$result["User"]["UserName"];
    $userInfo["NickName"]=$result["User"]["NickName"];
    $login_Info["SyncKey"]=$result["SyncKey"];
    $aTemp=array();
    foreach ($result["SyncKey"]["List"] as $value) {
    	$aTemp[]=$value["Key"]."_".$value["Val"];
    }
    $login_Info["synckey"]=implode("|", $aTemp);
    file_put_contents("loginInfo", json_encode($login_Info));
    file_put_contents("userInfo", json_encode($userInfo));
}

function getContact(){
	global $login_Info,$ua,$contactArray,$curl,$ct;
	$url = $login_Info["url"]."/webwxgetcontact?r=".time()."&seq=0&lang=zh_CN&skey=".$login_Info["skey"]."&pass_ticket=".$login_Info["pass_ticket"];
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,0); 
    curl_setopt($curl,  CURLOPT_HTTPHEADER, array_merge($ua,$ct));
    $result = curl_exec($curl);  //$result 获取页面信息
    file_put_contents('backc',$result);
    $result=json_decode($result,true);
    foreach ($result["MemberList"] as  $value) {
    	$contactArray[]=array("UserName"=>$value["UserName"],"NickName"=>$value["NickName"],"RemarkName"=>$value["RemarkName"]);
    }
    file_put_contents("contacts", json_encode($contactArray));
}

function setAlias($userName,$alias){
	global $login_Info,$ua,$curl;
	$url = $login_Info["url"]."/webwxoplog?lang=zh_CN&pass_ticket=".$login_Info['pass_ticket'];
    $data = array(
            'UserName'    => $userName,
            'CmdId'       => 2,
            'RemarkName'  => $alias,
            'BaseRequest' => $login_Info['BaseRequest']
        );
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl,  CURLOPT_HTTPHEADER,$ua);
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    $result = curl_exec($curl);
    var_dump($result);
}

function sendMsg($msg,$uid){
	global $login_Info,$ua,$contactArray,$userInfo,$curl,$ct;
	$url = $login_Info["url"]."/webwxsendmsg?lang=zh_CN&pass_ticket=".$login_Info["pass_ticket"];
	$content=json_encode(array("BaseRequest"=>$login_Info["BaseRequest"],"Msg"=>array(
		'Type'=>1,
		'Content'=>$msg,
		'FromUserName'=>$userInfo["UserName"],
//		'ToUserName'=>'@00f7a4d0649ed60704fbc7a3fe14d3d70605201d81b0c4686e68ab5f9e519dff',
		'ToUserName'=>$uid,
		'LocalID'=>$login_Info["msgid"],
		'ClientMsgId'=>$login_Info["msgid"]
		),"Scene"=>0),JSON_UNESCAPED_UNICODE);  
	$login_Info["msgid"]+=1;

    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,  CURLOPT_HTTPHEADER, array_merge($ua,$ct));

    $result = curl_exec($curl);  //$result 获取页面信息
    var_dump($result);
}

get_QRuuid();
get_QR();
while(1){
	$code=check_Login();
	if($code==200)
		break;
	if($code==408){
		echo "请扫描二维码\r\n";
	}
	if($code==201){
		echo "请确认\r\n";
	}
	sleep(3);
}
get_LoginInfo();
web_init();
var_dump($userInfo);
getContact(); 
//var_dump($contactArray);

$uid=$userInfo["UserName"];
foreach ($contactArray as $user) {
	if($user["NickName"]=="陈一竹"){
		$uid=$user["UserName"];
	}
}

//setAlias($uid,"wife");
while(1){
	$stdin=fopen('php://stdin','r');
	$some=trim(fgets($stdin,100));
	fclose($stdin);
	sendMsg($some,$uid);
}
?>