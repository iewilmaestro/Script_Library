<?php

const
versi = "0.0.1",
host = "https://ourcoincash.xyz/",
refflink = "https://ourcoincash.xyz/?r=3408",
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
		
		$this->captcha = new Captcha();
		$this->iewil = new Iewil();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		
		$r = $this->Dashboard();
		if(!$r['bal']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		
		Display::Cetak("Balance",$r['bal']);
		Display::Cetak("Apikey",$this->captcha->getBalance());
		Display::Line();
		if($this->Ptc()){
			Functions::removeConfig("cookie");
			goto cookie;
		}
		if($this->Claim()){
			Functions::removeConfig("cookie");
			goto cookie;
		}
		if($this->ClaimWithEnergy()){
			Functions::removeConfig("cookie");
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
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$data['bal'] = explode('</p>', explode('<i class="fas fa-coins"></i> ', $r)[1])[0];
		$data['energy'] = preg_replace("/[^0-9]/", "", explode('</p>', explode('<i class="fas fa-bolt"></i>', $r)[1])[0]);
		return $data;
	}
	private function Ptc(){
		while(true){
			$r = Requests::get(host."ptc",$this->headers())[1];
			if(preg_match('/Just a moment/',$r)){
				print Display::Error("Cloudflare\n");
				return 1;
			}
			$id = explode("'",explode('/view/',$r)[1])[0];
			if(!$id){
				print Display::Error("Ads are finished\n");
				Display::Line();
				return;
			}
			$r = Requests::get(host."ptc/view/".$id,$this->headers())[1];
			$tmr = explode(';',explode("let timer = ", $r)[1])[0];
			
			$scrap = $this->scrap->Result($r);
			$data = $scrap['input'];
			if($scrap['captcha']['cf-turnstile']){
				$data['captcha'] = "turnstile";
				$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host."ptc/view/".$id);
				if(!$cap)continue;
				$data['cf-turnstile-response']=$cap;
			}elseif($scrap['captcha']['g-recaptcha']){
				$data['captcha'] = "recaptchav2";
				$cap = $this->captcha->RecaptchaV2($scrap['captcha']['g-recaptcha'], host."ptc/view/".$id);
				if(!$cap)continue;
				$data['g-recaptcha-response'] = $cap;
			}
			
			if($tmr){
				Functions::Tmr($tmr);
			}
			
			$data = http_build_query($data);
			$r = Requests::post(host."ptc/verify/".$id,$this->headers(), $data)[1];
			$ss = explode("type: 'success'", $r)[1];
			$textss = explode('has',explode("text: '",$r)[1])[0];
			$r = $this->Dashboard();
			if($ss){
				print Display::Sukses($textss);
				Display::Cetak("Blanace",$r['bal']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
			}else{
				print Display::Error("Not found\n");
				Display::Cetak("Blanace",$r['bal']);
			}
			Display::Line();
		}
	}
	private function Claim(){
		while(true){
			$r = Requests::get(host."faucet",$this->headers())[1];
			if(preg_match('/Just a moment/',$r)){print Display::Error("Cloudflare\n");return 1;}
			preg_match('/(\d{1,})\/(\d{1,})/',$r,$limit);
			if($limit[2]){
				$sisa = $limit[1];
				$limit = $limit[2];
			}
			if($sisa < 1){
				print Display::Error("Limit Claim Faucet\n");
				Display::Line();
				return;
			}
			$data = [];
			$data['csrf_token_name'] = explode('"',explode('id="token" value="',$r)[1])[0];
			$data['token'] = explode('"',explode('name="token" value="',$r)[1])[0];
			$recaptcha = explode('"', explode('<div class="g-recaptcha" data-sitekey="', $r)[1])[0];//6LfqFAcdAAAAAKWiUVv3EkT0le7pDL6lnsGfe5l6">
			
			if($recaptcha){
				$cap = $this->captcha->RecaptchaV2($recaptcha, host);
				if(!$cap)continue;
				$data["captcha"] = "recaptchav2";
				$data["g-recaptcha-response"] = $cap; 
			}
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->captcha->AntiBot($r);
				if(!$antibot)continue;
				$data["antibotlinks"] = $antibot;
			}
			if(!$data){
				continue;
			}
			$data = http_build_query($data);
			
			$r = Requests::post(host."faucet/verify", $this->headers(),$data)[1];
			if(preg_match('/Just a moment/',$r)){
				print Display::Error(host."faucet/verify\n");
				print Display::Error("Cloudflare\n");
				return 1;
			}
			$tmr = explode('-',explode('let wait = ',$r)[1])[0];
			$ss = explode('has',explode("text: '",$r)[1])[0];
			$r = $this->Dashboard();
			if($ss){
				print Display::Sukses($ss);
				Display::Cetak("Limit",$sisa."/".$limit);
				Display::Cetak("Blanace",$r['bal']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}else{
				print Display::Error("Not found\n");
				Display::Cetak("Limit",$sisa."/".$limit);
				Display::Cetak("Blanace",$r['bal']);
				Display::Line();
			}
			if($tmr){Functions::tmr($tmr);}
		}
	}
	private function ClaimWithEnergy(){
		$r = $this->Dashboard();
		$energy = $r['energy'];
		while(true){
			if($energy < 10)break;
			$r = Requests::get(host."faucet",$this->headers())[1];
			if(preg_match('/Just a moment/',$r)){print Display::Error("Cloudflare\n");return 1;}
			$data = [];
			$data['csrf_token_name'] = explode('"',explode('id="token" value="',$r)[1])[0];
			$data['token'] = explode('"',explode('name="token" value="',$r)[1])[0];
			
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->captcha->AntiBot($r);
				if(!$antibot)continue;
				$data["antibotlinks"] = $antibot;
			}
			if(!$data){
				continue;
			}
			$data = http_build_query($data);
			$r = Requests::post(host."faucet/verify", $this->headers(),$data)[1];
			if(preg_match('/Just a moment/',$r)){
				print Display::Error(host."faucet/verify\n");
				print Display::Error("Cloudflare\n");
				return 1;
			}
			$tmr = explode('-',explode('let wait = ',$r)[1])[0];
			$ss = explode('has',explode("text: '",$r)[1])[0];
			$r = $this->Dashboard();
			$energy = $r['energy'];
			if($ss){
				print Display::Sukses($ss);
				Display::Cetak("Energy",$energy);
				Display::Cetak("Blanace",$r['bal']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}else{
				print Display::Error("Not found\n");
				//Display::Cetak("Limit",$sisa."/".$limit);
				Display::Cetak("Blanace",$r['bal']);
				Display::Line();
			}
			if($tmr){Functions::tmr($tmr);}
		}
	}
}

new Bot();