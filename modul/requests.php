<?php

Class Requests {
	
	static function Curl($url, $header=0, $post=0, $data_post=0, $cookie=0, $proxy=0, $skip=0){
		while(true){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_COOKIE,TRUE);
			if($cookie){curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie);curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie);}
			if($post) {curl_setopt($ch, CURLOPT_POST, true);}
			if($data_post) {curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);}
			if($header) {curl_setopt($ch, CURLOPT_HTTPHEADER, $header);}
			if($proxy){curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);curl_setopt($ch, CURLOPT_PROXY, $proxy);}
			curl_setopt($ch, CURLOPT_HEADER, true);
			$r = curl_exec($ch);
			if($skip){return;}
			$c = curl_getinfo($ch);
			if(!$c) return "Curl Error : ".curl_error($ch); else{
				$head = substr($r, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
				$body = substr($r, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
				$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				if(!$body){
					print "Check your Connection!";
					sleep(2);
					print "\r                         \r";
					continue;
				}
				return array($head,$body,"status_code"=>$status_code);
			}
		}
	}
	static function get($url, $head =0){return self::curl($url,$head);}
	static function post($url, $head=0, $data_post=0){return self::curl($url,$head, 1, $data_post);}
	static function getXskip($url, $head =0){return self::curl($url,$head,'','','','',1);}
	static function postXskip($url, $head=0, $data_post=0){return self::curl($url,$head, 1, $data_post,'','',1);}
	static function getXcookie($url, $head=0, $cookie=0){if(!$cookie){$cookie = "data/".title."/cookie.txt";}return self::curl($url,$head,'','',$cookie);}
	static function postXcookie($url, $head=0, $data_post=0, $cookie=0){if(!$cookie){$cookie = "data/".title."/cookie.txt";}return self::curl($url,$head,1,$data_post,$cookie);}
	static function getXproxy($url, $head=0, $proxy){return self::curl($url,$head,'','',1,$proxy);}
	static function postXproxy($url, $head=0, $data_post, $proxy){return self::curl($url,$head,1,$data_post,1,$proxy);}
}

?>