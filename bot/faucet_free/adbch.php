<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://adbch.top/",
refflink = "https://adbch.top/r/110267",
youtube = "https://youtube.com/@iewil";

class Bot{
	public $cookie,$uagent;
	public function __construct(){
		Display::Ban(title, versi);
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		Display::Ban(title, versi);
		$r = $this->Dashboard();
		
		if(!$r["user"]){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired!\n");
			goto cookie;
		}
		Display::Cetak("Username",$r["user"]);
		Display::Cetak("Balance",$r["balance"]);
		Display::Line();
		
		$this->Claim();
	}
	public function headers($xml = 0){
		$h[] = "Host: ".parse_url(host)['host'];
		$h[] = "Upgrade-Insecure-Requests: 1";
		$h[] = "Connection: keep-alive";
		$h[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
		$h[] = "user-agent: ".$this->uagent;
		$h[] = "Referer: https://adbch.top/";
		$h[] = "Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7";
		$h[] = "cookie: ".$this->cookie;
		return $h;
	}
	public function Dashboard(){
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$user = explode('</b>',explode('User id: <b>',$r)[1])[0];
		$bal = explode('</b>',explode('Balance<br><b>',$r)[1])[0];
		return ["user"=>$user,"balance"=>$bal];
	}
	public function Claim(){
		while(true){
			$data = [];
			$r = Requests::get(host."surf/browse/",$this->headers())[1];
			if(!preg_match("/Skip/",$r)){
				print Display::Error("Ads Finished\n");
				Display::Line();
				break;
			}
			preg_match_all('#<input type="hidden" name="(.*?)" value="(.*?)">#',$r,$x);
			foreach($x[1] as $a => $label){
				$data[$label] = $x[2][$a];
			}
			$data = http_build_query($data);
			$tmr = explode("'",explode("let duration = '",$r)[1])[0];
			if($tmr){Functions::tmr($tmr);}
			
			$r = Requests::post(host."surf/browse/",$this->headers(),$data)[1];
			$ss = explode('BCH',explode('You earned ',$r)[1])[0];
			$bal = explode('</b>',explode('class="white-text bal">Баланс: <b>',$r)[1])[0];
			Display::Cetak("Success",$ss);
			$r = Requests::get(host."dashboard",$this->headers())[1];
			$bal = explode('</b>',explode('Balance<br><b>',$r)[1])[0];
			Display::Cetak("Balance",$bal);
			Display::Line();
		}
	}
}

new Bot();