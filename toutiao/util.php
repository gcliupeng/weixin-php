<?php
define("NAME","琅琊榜");
function push($name,$data){
	static $redis;
	if(is_null($redis)){
		$redis=new Redis();
		$redis->connect("localhost",6379);
	}
	return $redis->lpush($name,$data);
}
function pop($name){
	static $redis;
	if(is_null($redis)){
		$redis=new Redis();
		$redis->pconnect("localhost",6379,0);
	}
	$res=$redis->brpop("$name",0);
	return $res[1];
}

function putArticles($keyword){
	$curl = curl_init();
	$offset = 0;
	//$articles = array();
	while(true){
		$url = "http://www.toutiao.com/search_content/?format=json&keyword=$keyword"."&offset=$offset";
    	curl_setopt($curl, CURLOPT_URL,$url);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($curl, CURLOPT_HEADER, 0);
    	$result = curl_exec($curl);  //$result 获取页面信息
    	$result=json_decode($result,true);
    	if(count($result["data"])==0){
    		break;
    	}else{
    		$offset+=count($result["data"]);
    	}
    	foreach ($result["data"] as $value) {
            //只考虑24小时内的文章
            // if(strtotime($value["datetime"])<time()-24*60*60){
            //     continue;
            // }
    	 	$article=array();
    	 	$article["title"]=$value["title"];
    	 	$article["abstract"]=$value["abstract"];
    	 	$article["keywords"]=$keyword;
    	 	$article["url"]="http://www.toutiao.com".$value["source_url"];
    	 	$article["comment_count"]=$value["comment_count"];
    	 	$article["comments_url"]=$article["url"]."comments/?format=json&";
    	 	push("$keyword"."_articles",json_encode($article));
    	 	//$articles[]=$article;
    	 }

    	 // if($offset>10){
    		// 	break;
    		// } 
	}
	
    curl_close($curl);
    
    //var_dump($articles);
    return $articles;
}

function putComments($article){
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
	if(intval($article["comment_count"])==0){
		return;
	}
	$offset=0;
	while(1){	
		curl_setopt($curl, CURLOPT_URL,$article["comments_url"]."offset=$offset");
		$result = curl_exec($curl);  //$result 获取页面信息
    	$result=json_decode($result,true);
    	if(count($result["data"]["comments"])==0)
    		break;
    	$offset+=count($result["data"]["comments"]);
    	foreach ($result["data"]["comments"] as $comment) {
    		$comment=array("keyword"=>$article["keywords"],"text"=>$comment["text"],"num"=>$comment["digg_count"],"title"=>$article["title"],"create_time"=>intval($comment["create_time"]));
    		push($article["keywords"]."_comments",json_encode($comment));
    	}
	}
}

function sortByDiggcount($commentA,$commentB){
	return $commentA["num"]-$commentB["num"];
}
?>