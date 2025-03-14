<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://dogefree.in/",
refflink = "https://dogefree.in/?r=319828",
youtube = "https://youtube.com/@iewil";

class Bot{
	public $cookie,$uagent;
	public function __construct(){
		Display::Ban(title, versi);
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		
		$this->cookie = Functions::setConfig("cookie");
		$this->csrf = $this->getCsrf();
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		Display::Ban(title, versi);
		$r = $this->Dashboard();
		if(!$r['bal']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		
		Display::Cetak("Balance",$r['bal']);
		Display::Cetak("Reward",$r['rp']);
		Display::Line();
		if($this->Claim()){
			Functions::removeConfig("cookie");
			goto cookie;
		}
	}
	private function getCsrf(){
		$get_csrf = explode(";", $this->cookie);
		foreach($get_csrf as $csrf){
			if(explode("csrf_cookie_name=", $csrf)[1]){
				return trim(explode("csrf_cookie_name=", $csrf)[1]);
			}
		}
	}
	private function headers(){
		$h = [
			"x-csrf-token: ","sec-ch-ua-mobile: ?1",
			"user-agent: ".$this->uagent,
			"x-requested-with: XMLHttpRequest",
			"save-data: on",
			"origin: ".host,
			"sec-fetch-site: same-origin",
			"sec-fetch-mode: cors",
			"sec-fetch-dest: empty",
			"accept-language: en-US,en;q=0.9,id;q=0.8",
			"cookie: ".$this->cookie
		];
		return $h;
	}
	public function num_rand($int){
		$rand_num = "1234567890";
		$split = str_split($rand_num);
		$res = "";
		while(true){
			$rand = array_rand($split);
			$res .= $split[$rand];
			if( strlen($res) == $int ){ 
				return $res; 
			}
		}
	}
	public function str_rand($int){
		$rand_str = "abcdefghijklmnopqrstuvwqyz";
		$rand_num = "1234567890";
		$rand_str_up= "ABCDEFGHIJKLMNOPQRSTUVWQYZ";
		$split = str_split($rand_str.$rand_num.$rand_str_up);
		$res = "";
		while(true){
			$rand = array_rand($split);
			$res .= $split[$rand];
			if( strlen($res) == $int ){
				return $res;
			}
		}
	}
	private function finger(){
		$rand = $this->num_rand(6);
		$mdrand = md5($rand);
		$h = ["user-agent: ".$this->uagent];
		$data = http_build_query(["op"=> "record_fingerprint","fingerprint"=> $mdrand,"csrf_token" => $this->csrf]);
		$r = Requests::get(host."cgi-bin/api.pl?$data",$h)[1];
		if($r){
			return ["finger"=>$mdrand,"fingernum"=>$rand,"sheed"=>$this->str_rand(16)];
		}
	}
	private function Dashboard(){
		$r = Requests::get(host."/?op=home",$this->headers())[1];
		$data['rp'] = trim(explode('</div>',explode('<div class="reward_table_box br_0_0_5_5 user_reward_points font_bold" style="border-top:none;">',$r)[1])[0]);
		$data['bal'] = trim(explode('</span>',explode('<span id="balance_small">',$r)[1])[0]);
		return $data;
	}
	private function Claim(){
		while(true){
			$r = Requests::get(host."/?op=home",$this->headers())[1];
			$timer = explode(');',explode("title_countdown(",$r)[1])[0];
			if($timer){Functions::tmr($timer);}
			$finger = $this->finger();
			$data = ["csrf_token"=>$this->csrf,"op"=>"free_play","fingerprint"=>$finger["finger"],"client_seed"=>$finger["sheed"],"fingerprint2"=>$finger["fingernum"],"pwc"=>1,"h_recaptcha_response" =>""];
			$r = Requests::post(host, $this->headers(),http_build_query($data))[1];
			$x = explode(':',$r);
			if($x[2]){
				Display::Cetak("Number",$x[1]);
				Display::Cetak("You Win",$x[3]." DOGE");
				Display::Cetak("Balance",$x[2]." DOGE");
				$r = $this->Dashboard();
				Display::Cetak("Reward",$r['rp']);
				Display::line();
			}else
			if(explode("incorrect",$r)[1]){
				$wr = explode(".",$r)[0];
				print Display::Error($wr);
				sleep(3);
				print "\r                           \r";
			}else{
				exit(m.str_replace(". ","\n",explode(':',$r)[1].n));
			}
		}
	}
}

new Bot();