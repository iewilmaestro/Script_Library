<?php

const
versi = "0.0.1",
host = "https://whoopyrewards.com/",
refflink = "https://whoopyrewards.com/?r=76779",
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
		$this->scrap = new HtmlScrap();
		$this->captcha = new Captcha();
		
		Display::Ban(title, versi);
			
		$r = $this->Dashboard();
		if(!$r['username']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		
		Display::Cetak("Username",$r['username']);
		Display::Cetak("Balance",$r['balance']);
		Display::Cetak("Apikey",$this->captcha->getBalance());
		Display::Line();
		if($this->ptc()){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		if($this->faucet()){
			Functions::removeConfig("cookie");
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
		$bal = explode('</h5>',explode('<h5 class="mb-0">', $r)[1])[0];
		$username = explode('</span>',explode('<span class="d-none d-xl-inline-block ml-1" key="t-henry">', $r)[1])[0];//iewilmaestro
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
				print Display::Error(host."faucet/currency/".$coin.n);
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
			$wr = explode('</div>', explode('<i class="fas fa-exclamation-circle"></i> ',$r)[1])[0];//Invalid Anti-Bot Links
			preg_match("/Swal\.fire\('([^']*)', '([^']*)', '([^']*)'\)/", $r, $matches);
			
			if($matches[1] == 'Good job!'){
				Display::Cetak('Limit', $scrap['faucet'][0][0]);
				print Display::Sukses($matches[2]);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}elseif($wr){
				print Display::Error($wr.n);
				Display::Line();
			}else{
				print Display::Error("no respon".n);
				Display::Line();
			}
			
		}
		print Display::Error("Ptc has finisghed\n");
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
				print Display::Error(host."faucet".$coin.n);
				print Display::Error("Cloudflare Detect\n");
				Display::Line();
				return 1;
			}
			$tmr = explode('-', explode('var wait = ', $r)[1])[0];
			if($tmr){
				Functions::Tmr($tmr);
				continue;
			}
			if( !$scrap['title'] || preg_match('/Home/',$scrap['title'])){
				sleep(3);
				continue;
			}
			$limit = $scrap['faucet'][1][0];
			if($limit < 1)break;
			$data = $scrap['input'];
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->captcha->AntiBot($r);
				//$antibot = $this->iewil->AntiBot($r);
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
			$wr = explode('</div>', explode('<i class="fas fa-exclamation-circle"></i> ',$r)[1])[0];//Invalid Anti-Bot Links
			preg_match("/Swal\.fire\('([^']*)', '([^']*)', '([^']*)'\)/", $r, $matches);
			
			if($matches[1] == 'Good job!'){
				Display::Cetak('Limit', $scrap['faucet'][0][0]);
				print Display::Sukses($matches[2]);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}elseif($wr){
				print Display::Error($wr.n);
				Display::Line();
			}else{
				print Display::Error("no respon".n);
				Display::Line();
			}
		}
		print_r($scrap);
		print Display::Error("Limit faucet\n");
	}
}
new Bot();