<?php

const
versi = "0.0.1",
host = "https://banfaucet.com/",
refflink = "https://banfaucet.com/?r=224743",
youtube = "https://youtube.com/@iewil";

class Bot {
	
	public function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		$this->iewil = new Iewil();
		$this->captcha = new Captcha();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
			
		$r = $this->Dashboard();
		if(!$r['username']){
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
			
		Display::Cetak("username",$r['username']);
		Display::Cetak("balance",$r['balance']);
		Display::Line();
		
		if($this->ptc()){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		if($this->faucet()){
			Functions::removeConfig("cookie");
			//Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
	}
	
	public function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	
	public function Dashboard(){
		das:
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$scrap = $this->scrap->Result($r);
		if($scrap['locked']){
			$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
			if($tmr){
				Functions::Tmr($tmr+5);
				goto das;
			}
		}
		$bal = explode(' ',explode('<i class="fas fa-dollar-sign"></i> ', $r)[1])[0];
		$username = explode('<i',explode('<i class="fa-solid fa-user-graduate me-2"></i>', $r)[1])[0];//iewilmaestro
		return ["username"=>$username, "balance"=>$bal];
	}
	
	public function Firewall(){
		while(1){
			$r = Requests::get(host."firewall",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			$data = $scrap['input'];
			
			if($scrap['captcha']){
				$cap = $scrap['captcha']['g-recaptcha'];
				if(substr($cap, 0 , 3) == "0x4"){
					$cap = $this->captcha->Turnstile($cap, host);
					$data['cf-turnstile-response']=$cap;
					$data['g-recaptcha-response']=$cap;
				}else{
					print Display::Error("Sitekey Error\n"); 
					continue;
				}
				if(!$cap)continue;
			}
			
			$r = Requests::post(host."firewall/verify",$this->headers(), http_build_query($data))[1];
			if(preg_match('/Invalid Captcha/',$r))continue;
			Display::Cetak("Firewall","Bypassed");
			Display::Line();
			return;
		}
	}
	private function ptc(){
		while(true){
			$r = Requests::get(host."ptc",$this->headers())[1];
			$id = explode("'", explode("ptc/view/", $r)[1])[0];//3210'
			if(preg_match('/Just a moment.../', $r)){
				print Display::Error(host."ptc/view".$coin.n);
				print Display::Error("Cloudflare Detect\n");
				Display::Line();
				return 1;
			}
			if(!$id)break;
			$r = Requests::get(host."ptc/view/".$id,$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			$timer = explode(';', explode("var timer = ", $r)[1])[0];//10;
			$url = explode("';", explode("var url = '", $r)[1])[0];//https://tap-coin.de/refer/user/15311
			Display::Cetak("ptc", $url);
			if($timer){
				Functions::Tmr($timer+5);
			}
			$data = $scrap['input'];
			if($scrap['captcha']){
				$cap = $scrap['captcha']['g-recaptcha'];
				if(substr($cap, 0 , 3) == "0x4"){
					$data['captcha'] = "recaptchav2";
					$cap = $this->captcha->Turnstile($cap, host);
					$data['cf-turnstile-response']=$cap;
					$data['g-recaptcha-response']=$cap;
				}else{
					print Display::Error("Sitekey Error\n"); 
					continue;
				}
				if(!$cap)continue;
			}
			$data = http_build_query($data);
			$r = Requests::post(host."ptc/verify/".$id,$this->headers(), $data)[1];
			
			$error = explode("icon: 'error',", $r)[1];
			$ss = explode("icon: 'success',", $r)[1];
			$res = explode("'",explode("title: '", $r)[1])[0];
			$scrap = $this->scrap->Result($r);
			if($scrap['locked']){
				$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr+5);
					continue;
				}
			}
			if($error){
				print Display::Error($res.n);
				Display::Line();
			}elseif($ss){
				print Display::Sukses($res);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}else{
				print Display::Error("no response".n);
				Display::Line();
			}
			
		}
		print Display::Error("Ptc has finished\n");
		Display::Line();
	}
	private function faucet(){
		while(true){
			$r = Requests::get(host."faucet",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			if($scrap['locked']){
				$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr+5);
					continue;
				}
			}
			if($scrap['firewall']){
				print Display::Error("Firewall Detect\n");
				$this->Firewall();
				continue;
			}
			if($scrap['cloudflare']){
				print Display::Error(host."faucet".n);
				print Display::Error("Cloudflare Detect\n");
				Display::Line();
				return 1;
			}
			$tmr = explode('-', explode('var wait = ', $r)[1])[0];
			if($tmr){
				Functions::Tmr($tmr);
				continue;
			}
			$limit = $scrap['faucet'][1][2];
			if($limit < 1)break;
			$data = $scrap['input'];
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->captcha->AntiBot($r);
				if(!$antibot)continue;
				$data['antibotlinks'] = str_replace("+"," ",$antibot);
			}
			
			if($scrap['captcha']){
				$cap = $scrap['captcha']['g-recaptcha'];
				if(substr($cap, 0 , 3) == "0x4"){
					$data['captcha'] = "recaptchav2";
					$cap = $this->captcha->Turnstile($cap, host);
					$data['cf-turnstile-response']=$cap;
					$data['g-recaptcha-response']=$cap;
				}else{
					print Display::Error("Sitekey Error\n"); 
					continue;
				}
				if(!$cap)continue;
			}
			$data = http_build_query($data);
			$r = Requests::post(host."faucet/verify",$this->headers(), $data)[1];
			$error = explode("icon: 'error',", $r)[1];
			$ss = explode("icon: 'success',", $r)[1];
			$res = explode("'",explode("title: '", $r)[1])[0];
			$scrap = $this->scrap->Result($r);
			if($scrap['locked']){
				$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr+5);
					continue;
				}
			}
			if($error){
				print Display::Error($res.n);
				Display::Line();
			}elseif($ss){
				print Display::Sukses($res);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}else{
				print Display::Error("no response".n);
				Display::Line();
			}
		}
		print Display::Error("Limit faucet\n");
	}
}
new Bot();