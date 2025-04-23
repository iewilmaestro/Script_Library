<?php

const
versi = "0.0.1",
host = "https://feyorra.top/",
refflink = "https://feyorra.top/?r=34383",
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
		$r = $this->Dashboard();
		if(!$r['balance']){
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Balance",$r['balance']);
		Display::Cetak("Apikey",$this->captcha->getBalance());
		Display::Line();
		if($this->Claim()){
			Functions::removeConfig("user_agent");
			Functions::removeConfig("cookie");
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
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$data['balance'] = explode('</p>',explode('<p>',explode('<h3>Main Balance</h3>', $r)[1])[1])[0];
		return $data;
	}
	private function Claim(){
		while(true){
			$r = Requests::get(host."faucet",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			if(preg_match('/Protecting faucet from Bots/', $r)){
				print Display::Error("Shortlink\n");
				exit;
			}
			if(preg_match('/Daily limit reached/', $r)){
				print Display::Error("Daily limit reached\n");
				exit;
			}
			
			if($scrap['firewall']){
				print Display::Error("Firewall Detect\n");
				//$this->Firewall();
				//continue;
				exit;
			}
			if($scrap['cloudflare']){
				print Display::Error(host."faucet".n);
				print Display::Error("Cloudflare Detect\n");
				Display::Line();
				return 1;
			}
			if(preg_match('/Locked/', $r)){
				$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr+5);
					continue;
				}
			}
			
			//if($scrap['faucet'][1][0] < 1)break;
			$timer = explode('-',explode('let wait =', $r)[1])[0];
			if($timer){
				Functions::Tmr($timer);
				continue;
			}
			$data = $scrap['input'];
			$token = explode("'",explode("<input type='hidden' name='_iconcaptcha-token' value='", $r)[1])[0];
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->captcha->Antibot($r);
				if(!$antibot)continue;
			}
			if($scrap['captcha']['g-recaptcha'] == "0x4AAAAAAA3a78IiE1GGrmei"){
				$turnstile = $this->captcha->Turnstile($scrap['captcha']['g-recaptcha'], host);
				$data['captcha'] = 'turnstile';
				$data['cf-turnstile-response'] = $turnstile;
				if(!$turnstile)continue;
			}else
			if($token){
				$icon = $this->iconBypass($token);
				if(!$icon)continue;
				$data = array_merge($data, $icon);
			}else{
				print Display::Error("update Captcha\n");
				exit;
			}
			
			if($antibot){
				$antibot['antibotlinks'] = $antibot;
				$data = array_merge($antibot, $data);
			}
			$data = http_build_query($data);
			$r = Requests::post(host."faucet/verify",$this->headers(), $data)[1];
			if(preg_match('/Locked/', $r)){
				$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr+5);
					continue;
				}
			}
			$wr = explode('</div>',explode('<i class="fas fa-exclamation-circle"></i> ', $r)[1])[0];
			$ss = explode("icon: 'success',", $r)[1];
			if(preg_match('/Shortlink must be completed/', $r)){
				$sl = explode("'",explode("Swal.fire('Error!', '", $r)[1])[0];
				print Display::Error($sl.n);
				exit;
			}
			if($ss){
				print Display::Sukses(explode("'",explode("title: '",$ss)[1])[0]);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}elseif($wr){
				print Display::Error($wr.n);
				Display::Line();
				exit;
			}else{
				print_r($r);
				exit;
			}
		}
		print Display::Error("faucet claim habis\n");
		Display::Line();
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
	private function iconBypass($token){
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
				"theme" 	=> "light",
				"token" 	=> $token,
				"timestamp"	=> $timestamp,
				"initTimestamp"	=> $initTimestamp
			]))
		];
		$r = json_decode(base64_decode(Requests::post(host."icaptcha/req",$icon_header, $data)[1]),1);
		$base64Image = $r["challenge"];
		$challengeId = $r["identifier"];
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
		$r = json_decode(base64_decode(Requests::post(host."icaptcha/req",$icon_header, $data)[1]),1);
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
}
new Bot();