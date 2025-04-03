<?php

const
versi = "0.0.1",
host = "https://hotfaucet.in/",
refflink = "https://hotfaucet.in/?r=372",
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
		if($this->faucet("madfaucet")){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		if($this->faucet("faucet")){
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
			print Display::Error("Account Locked\n");
			$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
			if($tmr){
				Functions::Tmr($tmr+5);
				goto das;
			}
		}
		$bal = explode('</p>',explode('<p class="text-muted mb-0">', $r)[1])[0];
		$username = explode('</span>',explode('<span class="d-none d-xl-inline-block ml-1" key="t-henry">', $r)[1])[0];
		return ["username"=>$username, "balance"=>$bal];
	}
	public function Firewall(){
		while(1){
			$r = Requests::get(host."firewall",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			$data = $scrap['input'];
			
			if($scrap['captcha']['mt-3 mb-3 cf-turnstile']){
				$cap = $this->captcha->Turnstile($scrap['captcha']['mt-3 mb-3 cf-turnstile'], host);
				$data['cf-turnstile-response']=$cap;
				if(!$cap)continue;
			}else{
				print Display::Error("Sitekey Error\n"); 
				continue;
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
			if($scrap['input']['_iconcaptcha-token']){
				$icon = $this->iconBypass($scrap['input']['_iconcaptcha-token']);
				if(!$icon)continue;
				$data = array_merge($data, $icon);
			}elseif($scrap['captcha']['mt-3 mb-3 cf-turnstile']){
				$data['captcha'] = "turnstile";
				$cap = $this->captcha->Turnstile($scrap['captcha']['mt-3 mb-3 cf-turnstile'], host);
				$data['cf-turnstile-response']=$cap;
				if(!$cap)continue;
			}else{
				print Display::Error("Sitekey Error\n"); 
				continue;
			}
			if(!$data){
				print Display::Error("Data not found");
				sleep(3);
				print "\r                              \r";
				continue;
			}
			
			$data = http_build_query($data);
			$r = Requests::post(host."ptc/verify/".$id,$this->headers(), $data)[1];
			$wr = explode('</div>', explode('<i class="fas fa-exclamation-circle"></i> ',$r)[1])[0];//Invalid Anti-Bot Links
			preg_match("/Swal\.fire\('([^']*)', '([^']*)', '([^']*)'\)/", $r, $matches);
			
			if($matches[1] == 'Good job!'){
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
		print Display::Error("Ptc has finished\n");
		Display::Line();
	}
	private function faucet($xxx){
		while(true){
			$r = Requests::get(host.$xxx,$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			if($scrap['locked']){
				print Display::Error("Account Locked\n");
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
			$limit = $scrap['faucet'][1][0];
			if($limit < 1)break;
			$data = $scrap['input'];
			if(explode('rel=\"',$r)[1]){
				if($sitekey_error){
					print Display::Error("sepertinya captcha update\n");
					exit;
				}
				$antibot = $this->captcha->AntiBot($r);
				if(!$antibot)continue;
				$data['antibotlinks'] = str_replace("+"," ",$antibot);
			}
			
			if($scrap['input']['_iconcaptcha-token']){
				$icon = $this->iconBypass($scrap['input']['_iconcaptcha-token']);
				if(!$icon)continue;
				$data = array_merge($data, $icon);
			}elseif($scrap['captcha']['mt-3 mb-3 cf-turnstile']){
				$data['captcha'] = "turnstile";
				$cap = $this->captcha->Turnstile($scrap['captcha']['mt-3 mb-3 cf-turnstile'], host);
				$data['cf-turnstile-response']=$cap;
				if(!$cap)continue;
			}else{
				print Display::Error("Sitekey Error\n"); 
				continue;
			}
			if(!$data){
				print Display::Error("Data not found");
				sleep(3);
				print "\r                              \r";
				continue;
			}
			$data = http_build_query($data);
			$r = Requests::post(host.$xxx."/verify",$this->headers(), $data)[1];
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
				//print_r($r);exit;
				print Display::Error("no respon".n);
				Display::Line();
			}
		}
		print Display::Error("Limit faucet\n");
	}
	private function iconBypass($token, $url = host."icaptcha/req", $theme = "light"){
		
		$icon_header = $this->headers();
		$icon_header[] = "origin: ".host;
		$icon_header[] = "x-iconcaptcha-token: ".$token;
		$icon_header[] = "x-requested-with: XMLHttpRequest";
		
		$timestamp = round(microtime(true) * 1000);
		$initTimestamp = $timestamp - 2000;
		$widgetID = $this->widgetId();
		
		$data = ["payload" => 
			base64_encode(json_encode([
				"widgetId"	=> $widgetID,
				"action" 	=> "LOAD",
				"theme" 	=> $theme,
				"token" 	=> $token,
				"timestamp"	=> $timestamp,
				"initTimestamp"	=> $initTimestamp
			]))
		];
		$r = json_decode(base64_decode(Requests::post($url, $icon_header, $data)[1]),1);
		$base64Image = $r["challenge"];
		$challengeId = $r["identifier"];
		if(!$base64Image || !$challengeId){
			return;
		}
		$cap = $this->iewil->IconCoordiant($base64Image);
		if(!$cap['x'])return;
		
		$timestamp = round(microtime(true) * 1000);
		$initTimestamp = $timestamp - 2000;
		$data = ["payload" => 
			base64_encode(json_encode([
				"widgetId"		=> $widgetID,
				"challengeId"	=> $challengeId,
				"action"		=> "SELECTION",
				"x"				=> $cap['x'],
				"y"				=> 24,
				"width"			=> 320,
				"token" 		=> $token,
				"timestamp"		=> $timestamp,
				"initTimestamp"	=> $initTimestamp
			]))
		];
		$r = json_decode(base64_decode(Requests::post($url,$icon_header, $data)[1]),1);
		if(!$r['completed']){
			return;
		}
		$data = [];
		$data['captcha'] = "icaptcha";
		$data['_iconcaptcha-token']=$token;
		$data['ic-rq']=1;
		$data['ic-wid'] = $widgetID;
		$data['ic-cid'] = $challengeId;
		$data['ic-hp'] = '';
		return $data;
	}
	private function widgetId() {
		$uuid = '';
		for ($n = 0; $n < 32; $n++) {
			if ($n == 8 || $n == 12 || $n == 16 || $n == 20) {
				$uuid .= '-';
			}
			$e = mt_rand(0, 15);

			if ($n == 12) {
				$e = 4;
			} elseif ($n == 16) {
				$e = ($e & 0x3) | 0x8;
			}
			$uuid .= dechex($e);
		}
		return $uuid;
	}
	
}
new Bot();