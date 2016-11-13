<?php
require_once("util.php");
function uploadImage($file_name)
{
	$file = array("media"=>'@'.$file_name);//文件路径，前面要加@，表明是文件上传.
	$curl = curl_init();
	$token = token();
	$url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=$token";
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,CURLOPT_POSTFIELDS,$file);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $result = curl_exec($curl);  //$result 获取页面信息 
    curl_close($curl);
    return $result ; //输出 页面结果
}
function broadCastText($content){
	$curl = curl_init();
	$token = token();
	//$url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$token";
	$url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=$token";
	$data=array();
	//$data["filter"]=array("is_to_all"=>true);
	$data["towxname"]="gh_80ede193b1f3";
	$data["text"]=array('content' => $content );
	$data["msgtype"]="text";

	curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $result = curl_exec($curl);  //$result 获取页面信息 
    curl_close($curl);
    return $result ; //输出 页面结果
}
?>