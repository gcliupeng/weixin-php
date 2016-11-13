<?php  

define("TOKEN", "liupenggc");
function checkSignature()
{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
}
}
function token(){

	$redis_token=new Redis();
	$redis_token->connect("127.0.0.1",6379);
	$token=$redis_token->get("wx_token");
	$token=json_decode($token,true);
	if(empty($token)||intval($token["time"])+$token["expire"]<time()-600){
			if($redis_token->incr("check")>1){
				return $token["code"];
			}else{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxa15331b5f7da23aa&secret=365d1a7c6b6dd1996d9a81bd0a384063");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				curl_close($ch);
				$output=json_decode($output,true);
				$token["code"]=$output["access_token"];
				$token["expire"]=intval($output["expires_in"]);
				$token["time"]=time();
				$redis_token->set("wx_token",json_encode($token));
				$redis_token->set("check",0);
				return $token["code"];
			}
	}else{
		return $token["code"];
	}
	}

function parseData()
{
	$file_in = file_get_contents("php://input"); //接收post数据
	$xml = simplexml_load_string($file_in,'SimpleXMLElement', LIBXML_NOCDATA);
	$data = json_decode(json_encode($xml),true); 
	return $data;
}

 function replayText($content){
	$data = parseData();
	$xml = "<xml>";
	$xml.="<ToUserName>".$data["FromUserName"]."</ToUserName>";
	$xml.="<FromUserName>".$data["ToUserName"]."</FromUserName>";
	$xml.="<CreateTime>".time()."</CreateTime>";
	$xml.="<MsgType>text</MsgType>";
	$xml.="<Content>".$content."</Content>";
	$xml.= "</xml>";
	file_put_contents("/www/techan/php/wx/d.txt", $xml);
	return $xml;
}

function replayVoice($content){
	$data = parseData();
	$xml = "<xml>";
	$xml.="<ToUserName>".$data["FromUserName"]."</ToUserName>";
	$xml.="<FromUserName>".$data["ToUserName"]."</FromUserName>";
	$xml.="<CreateTime>".time()."</CreateTime>";
	$xml.="<MsgType>voice</MsgType>";
	$xml.="<Voice><MediaId>".$content."</MediaId></Voice>";
	$xml.= "</xml>";
	file_put_contents("/www/techan/php/wx/d.txt", $xml);
	return $xml;
}
function replayImage($content){
	$data = parseData();
	$xml = "<xml>";
	$xml.="<ToUserName>".$data["FromUserName"]."</ToUserName>";
	$xml.="<FromUserName>".$data["ToUserName"]."</FromUserName>";
	$xml.="<CreateTime>".time()."</CreateTime>";
	$xml.="<MsgType>image</MsgType>";
	$xml.="<Image><MediaId>".$content."</MediaId></Image>";
	$xml.= "</xml>";
	file_put_contents("/www/techan/php/wx/d.txt", $xml);
	return $xml;
}

function replayImageText(){
	$data = parseData();
	$xml = "<xml>";
	$xml.="<ToUserName>".$data["FromUserName"]."</ToUserName>";
	$xml.="<FromUserName>".$data["ToUserName"]."</FromUserName>";
	$xml.="<CreateTime>".time()."</CreateTime>";
	$xml.="<MsgType>news</MsgType>";
	$xml.="<ArticleCount>1</ArticleCount><Articles><item><Title>hhh</Title><Description>ddd</Description><PicUrl>http://i.meizitu.net/2013/08/131I0I04-6.jpg</PicUrl><Url>http://www.baidu.com</Url></item></Articles>";
	$xml.= "</xml>";
	file_put_contents("/www/techan/php/wx/d.txt", $xml);
	return $xml;
}

function uploadContent($file_name,$type){
	$file = array("media"=>'@'.$file_name);//文件路径，前面要加@，表明是文件上传.
	$curl = curl_init();
	$token = token();
	$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$token"."&type=$type";
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,CURLOPT_POSTFIELDS,$file);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $result = curl_exec($curl);  //$result 获取页面信息 
    curl_close($curl);
    return $result ; //输出 页面结果
}

?>