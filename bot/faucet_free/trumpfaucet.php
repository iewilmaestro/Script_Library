<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://trumpfaucet.com/",
refflink = "https://trumpfaucet.com/dashboard/signup?ref=5560",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
			
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		$this->iewil = new Iewil();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		$r = $this->dashboard();
		if(!$r["username"]){
			print Display::Error("Cookie expired\n");
			Functions::removeConfig("cookie");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Username",$r["username"]);
		Display::Cetak("Balance",$r["balance"]);
		Display::Line();
		
		if($this->claim()){
			Functions::removeConfig("cookie");
			Display::Line();
			goto cookie;
		}
	}
	private function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function Dashboard(){
		$r = Requests::get(host, $this->headers())[1];
		$user = explode('</b>',explode('Welcome Back! <b>', $r)[1])[0];
		$balance = trim(explode('</h5>', explode('<h5 class="font-weight-bolder mb-0">', $r)[1])[0]);
		return ['username'=> $user,'balance' => $balance];
	}
	private function claim(){
		$r = $this->dashboard();
		$balance_awal = $r['balance'];
		while(true){
			$r = Requests::get(host."dashboard/claim",$this->headers())[1];
			$ready = trim(explode('</h6>',explode('<h6 class="font-weight-bolder mb-0 seconds">' , $r)[1])[0]);
			if($ready == "You Can Claim"){
				$cap = FreeCaptcha::Icon_hash($this->headers());
				if(!$cap)continue;
			}else{
				//$minutes = explode(':', $ready)[0];
				//$seconds = explode(':', $ready)[1];
				//if($minutes && $seconds){
				//	$timer = $minutes*60+$seconds;
					Functions::Tmr(600);
				//}
				continue;
			}
			$data = "captcha-hf=$cap&captcha-idhf=0&claim=";
			$r = Requests::post(host.'dashboard/claim', $this->headers(), $data)[1];
			$r = $this->dashboard();
			if($balance_awal == $r['balance']){
				
			}else{
				Display::Cetak('Balance', $r['balance']);
				Display::Line();
				$balance_awal = $r['balance'];
			}
		}
	}
}

new Bot();