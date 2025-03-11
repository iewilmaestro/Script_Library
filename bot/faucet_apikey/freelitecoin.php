<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://free-litecoin.com/",
refflink = "https://free-litecoin.com/login?referer=1931549",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
			
		$this->email = Functions::setConfig("Email");
		$this->password = Functions::setConfig("Password");
		Functions::view();
		
		$this->captcha = new Captcha();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		
		$r = $this->Dashboard();
		if(!$r){
			print Display::Error("Error user\n");
			exit;
		}
		Display::Cetak("Wallet", $r);
		Display::Cetak("Apikey",$this->captcha->getBalance());
		Display::Line();
		$this->Claim();
	}
	private function headers($xml=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($xml)$h[] = "x-requested-with: XMLHttpRequest";
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function Login(){
		
		$data["email"] = $this->email;
		$data["heslo"] = $this->password;
		
		$r = Requests::getXcookie(host."login",$this->headers())[1];
		$scrap = $this->scrap->Result($r);
		$img = explode('"', explode('<img class="captchaownhomepage"  src="data:image/png;base64,',$r)[1])[0];
		if($img){
			$cap = $this->captcha->Ocr($img);
			if(!$cap)return;
			$data["captcha_login"] = strtoupper($cap);
		}else
		if($scrap["captcha"]["g-recaptcha"]){
			$cap = $this->captcha->RecaptchaV2($scrap["captcha"]["g-recaptcha"], host);
			if(!$cap)return;
			$data["g-recaptcha-response"] = $cap;
		}
		Requests::postXcookie(host."login",$this->headers(), http_build_query($data))[1];
	}
	private function Dashboard(){
		login_ulang:
		$r = Requests::getXcookie(host."profile",$this->headers())[1];
		$wallet = explode('"',explode('<input type="text"  class="form-control" value="',$r)[1])[0];
		if(!$wallet){
			if(!$try){
				print k."Trying Login\n";
				$try = 1;
			}
			$this->Login();
			goto login_ulang;
		}
		Display::Ban(title, versi);
		return $wallet;
	}
	private function Claim(){
		while(true){
			$r = Requests::getXcookie(host,$this->headers())[1];
			$tmr = explode(';',explode("var time =",$r)[1])[0];
			if($tmr){
				Functions::Tmr(floor($tmr/1000)+1);
				continue;
			}
			$img = explode('"',explode('<img src="data:image/png;base64,', $r)[1])[0];
			if(!$img){
				print Display::Error("Can't find Captcha img\n");
				sleep(5);
				continue;
			}
			$cap = $this->captcha->Ocr($img);
			if(!$cap)continue;
			$data = "recaptcha=".strtoupper($cap);
			$r = json_decode(Requests::postXcookie(host.'php/rollnumber.php',$this->headers(1),$data)[1],1);
			if($r["state"]){
				Functions::Roll($r["roll"]);
				print m." | ".h."You Won: ".p.$r["value"].n;
				Display::Cetak("Balance",$r["valuefinal"]);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
				Functions::Tmr($r["secondtime"]/1000);
			}
		}
	}
}

new Bot();