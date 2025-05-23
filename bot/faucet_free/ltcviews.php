<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://www.ltcviews.com/",
refflink = "https://www.ltcviews.com/?ref=8851",
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
		Display::Cetak("User ID",$r["user"]);
		Display::Cetak("Balance",$r["balance"]);
		Display::Line();
		$this->surf_ads();
		$this->faucet();
	}
	private function headers($xml = 0){
		$h[] = "Host: ".parse_url(host)['host'];
		$h[] = "Upgrade-Insecure-Requests: 1";
		$h[] = "Connection: keep-alive";
		$h[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
		$h[] = "user-agent: ".$this->uagent;
		$h[] = "Referer: https://www.ltcviews.com/";
		$h[] = "Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7";
		$h[] = "cookie: ".$this->cookie;
		return $h;
	}
	private function Dashboard(){
		$r = Requests::get(host."dashboard.php",$this->headers())[1];
		$user = explode('</strong>',explode('Your id: <strong>',$r)[1])[0];
		$bal = explode('</h3>', explode('<h3 class="text-center">',explode('<h6>Acc Balance <strong>ŁTC</strong>',$r)[1])[1])[0];
		return ["user"=>$user,"balance"=>$bal];
	}
	private function surf_ads(){
		while(true){
			//$data = [];
			$r = Requests::get(host."surf.php",$this->headers())[1];
			$id = explode(';', explode('const adId = ', $r)[1])[0];//85;
			if(!$id){
				print Display::Error("Ads Finished\n");
				Display::Line();
				break;
			}
			/*
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
			*/
			$data = "ad_id=".$id;
			$tmr = explode(";",explode("const duration = ",$r)[1])[0];
			if($tmr){Functions::tmr($tmr);}
			
			$r = json_decode(Requests::post(host."surf.php",$this->headers(),$data)[1],1);
			if($r["success"]){
				Display::Cetak("Surf Ads","");
				Display::Cetak("Success",$r["reward"]);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r["balance"]);
				Display::Line();
			}
		}
	}
	private function faucet(){
		while(true){
			$r = Requests::get(host."faucet.php",$this->headers())[1];
			$tmr = explode(';', explode('let cooldown = ', $r)[1])[0];//267;
			if($tmr){Functions::tmr($tmr);continue;}
			
			$ad_timer = explode(',', explode('onclick="startTimer(', $r)[1])[0];//15, 4)">
			$ad_id = trim(explode(')', explode(',', explode('onclick="startTimer(', $r)[1])[1])[0]);//15, 4)">
			if($ad_timer){Functions::tmr($ad_timer);}
			$data = "ad_id=".$ad_id;
			$r = Requests::post(host."faucet_claim.php",$this->headers(),$data)[1];
			if(preg_match("/You've earned/",$r)){
				Display::Cetak("Faucet","");
				Display::Cetak("Success",trim(explode('!',$r)[1]));
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r["balance"]);
				Display::Line();
			}
		}
	}
}

new Bot();