<?php

$redis=new Redis();
$redis->connect("localhost",6379);

$contacts=file_get_contents("contacts");
$contacts=json_decode($contacts,true);
$userMap=array();
foreach ($contacts as $user) {
	$userMap[$user["UserName"]]=$user["NickName"];
}

while(true){
	$data=$redis->blpop("wx_message",0);
	$data=json_decode($data[1],true);
	echo "message:".$data["Content"]."\r\n";
	//var_dump($data);
	echo "from:".$userMap[$data["FromUserName"]]."\r\n";
}