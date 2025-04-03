<?php

const
versi = "0.0.1",
host = "https://earn-crypto.co/",
refflink = "https://earn-crypto.co/?ref=9781",
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
		$this->captcha = new Captcha();
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
		if($this->ptc()){
			print Display::Error("Cookie expired\n");
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			Display::Line();
			goto cookie;
		}
		
		if($this->claim()){
			Functions::removeConfig("cookie");
			Display::Line();
			goto cookie;
		}
	}
	private function headers($xml=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($xml)$h[] = "x-requested-with: XMLHttpRequest";
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function Dashboard(){
		$r = Requests::get(host, $this->headers())[1];
		$user = explode('<',explode('</a>',explode('<a href="/notifications.html" class="notification', $r)[1])[1])[0];
		$balance = trim(explode('</b>', explode('Account Balance <div class="text-warning"><b>', $r)[1])[0]);
		$coins = trim(explode('</b>', explode('Current Coins Value <div class="text-warning"><b>', $r)[1])[0]);
		return ['username'=> $user,'balance' => $balance."/".$coins];
	}
	private function ptc(){
		while(true){
			$r = Requests::get(host."ptc.html", $this->headers())[1];
			$id = explode('">',explode('<div class="website_block" id="',$r)[1])[0];
			if(!$id)break;
			//.https://earn-crypto.co/surf.php?sid=35&key=394ff2d6f73ef731e3304ec446b307de
			$key = explode("',",explode("&key=",$r)[1])[0];
			$r = Requests::get(host.'surf.php?sid='.$id.'&key='.$key, $this->headers())[1];
			if(preg_match('/to disconnect and login again/', $r))return true;
			$token = explode("';",explode("var token = '",$r)[1])[0];
			$tmr = explode(";",explode('var secs = ',$r)[1])[0];
			
			Functions::Tmr($tmr);
			$cap = FreeCaptcha::Icon_hash($this->headers());
			if(!$cap)continue;
			
			$data = "a=proccessPTC&data=".$id."&token=".$token."&captcha-idhf=0&captcha-hf=".$cap;
			$r = json_decode(Requests::post(host.'system/ajax.php',$this->headers(),$data)[1],1);
			if($r["status"] == 200){
				print Display::Sukses(str_replace(" SUCCESS ","",strip_tags($r["message"])));
				$r = $this->dashboard();
				Display::Cetak("Balance",$r["balance"]);
				Display::Line();
			}else{
				print Display::Error(strip_tags($r['message']).n);
				Display::Line();
				exit;
			}
		}
		print Display::Error("ptc habis\n");
		Display::Line();
	}
	private function claim(){
		while(true){
			$r = Requests::get(host, $this->headers())[1];
			$scrap = $this->scrap->Result($r);
			$tmr = explode('<', explode('<span id="claimTime">', $r)[1])[0];
			if($tmr){
				preg_match('/(\d+)/', $tmr, $match);
				print_r($match);exit;
				if(is_numeric($match[1])){
					
					Functions::Tmr($match[1]*60+60);
					continue;
				}
			}
			if(preg_match('/Faucet Locked!/', $r)){
				$wr = explode('</b>',explode('<i class="fas fa-exclamation-circle fa-lrg text-primary"></i><br /><b>', $r)[1])[0];
				print Display::Error($wr.n);
				exit;
			}
			if($scrap['captcha']['g-recaptcha']){
				$cap = $this->captcha->RecaptchaV2($scrap['captcha']['g-recaptcha'], host);
				if(!$cap)continue;
			}
			$token = explode("';", explode("var token = '", $r)[1])[0];//9ae01cc224e796b42bdb3f594806f1b71c0dd5d8bf6d1a361a8efedc996f9cd8
			$data = "a=getFaucet&token=$token&captcha=1&challenge=false&response=$cap";
			$r = json_decode(Requests::post(host.'system/ajax.php', $this->headers(1), $data)[1],1);
			if($r['status'] == 200){
				print Display::Sukses(strip_tags($r['message']));
				$r = $this->dashboard();
				Display::Cetak("Balance",$r["balance"]);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
				Functions::Tmr(3605);
			}else{
				print Display::Error(strip_tags($r['message']).n);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
				exit;
			}
		}
	}
}

new Bot();