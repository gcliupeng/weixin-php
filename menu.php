<?php
require_once("util.php");
function menu()
{
	$token = token();
	$curl = curl_init();
	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$token";
    $data = array();
    $data["button"] = array();
    $data["button"][]=array("type"=>"click","name"=>"提交","key"=>"001");
    $data["button"][]=array("type"=>"view","name"=>"搜索","url"=>"http://www.soso.com");
    $data["button"][]=array("type"=>"click","name"=>"阿阿","key"=>"003");
    //var_dump(json_encode($data));
    //exit;
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($data,JSON_UNESCAPED_UNICODE));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $result = curl_exec($curl);  //$result 获取页面信息 
    curl_close($curl);
    return $result ; //输出 页面结果
}
?>
