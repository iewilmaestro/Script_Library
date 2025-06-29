<?php

const
versi = "0.0.1",
host = "https://adbch.top/",
refflink = "https://adbch.top/r/110267",
youtube = "https://youtube.com/@iewil";

class Bot{
	private $cookie,$uagent;
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
	private function headers($xml = 0){
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
	private function Dashboard(){
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$user = Functions::Mid($r, 'User id: <b>', '</b>');
		$bal = Functions::Mid($r, 'Balance<br><b>', '</b>');
		return ["user"=>$user,"balance"=>$bal];
	}
	private function Claim(){
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
			$tmr = Functions::Mid($r, "let duration = '", "'");
			if($tmr){Functions::tmr($tmr);}
			
			$r = Requests::post(host."surf/browse/",$this->headers(),$data)[1];
			$ss = Functions::Mid($r, 'You earned ', 'BCH');
			if($ss){
				Display::Cetak("Success",$ss);
				$r = Requests::get(host."dashboard",$this->headers())[1];
				$bal = Functions::Mid($r, 'Balance<br><b>', '</b>');
				Display::Cetak("Balance",$bal);
				Display::Line();
			}
		}
	}
}

new Bot();