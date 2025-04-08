<?php

const
versi = "0.0.1",
host = "https://earncryptowrs.in/",
refflink = "https://earncryptowrs.in/?r=959",
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
		
		
		Display::Ban(title, versi);
			
		$r = $this->Dashboard();
		if(!$r){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("ReffId", $r);
		Display::Line();
		select_coin:
		$r = Requests::get(host."app/dashboard",$this->headers())[1];
		preg_match_all('#https?:\/\/'.str_replace('.','\.',parse_url(host)['host']).'\/app\/faucet\?currency=([a-zA-Z0-9]+)#', $r, $matches);
		$this->coins = $matches[1];
		foreach($this->coins as $num => $coins){
			Display::Menu(($num+1), strtoupper($coins));
			$all_coin[$num+1][0] = $coins;
		}
		Display::Menu(($num+=2), "All Coins");
		$all_coin[$num] = $this->coins;
		print Display::Isi("Nomor");
		$pil = readline();
		Display::Line();
			
		$pil = preg_replace('/\s+/','',$pil);
		preg_match_all('/(\d+)/', $pil, $match);
		if(count($match[1]) > 1){
			for($i = 0; $i < count($match[1]); $i++){
				$coin[$i] = $all_coin[$match[1][$i]][0];
			}
			$title = strtoupper(implode(",", $coin));
		}elseif(!is_numeric($pil) || $pil > $num || $pil < 1){
			print Display::Error("Wrong method!\n");
			Display::Line();
			goto select_coin;
		}else{
			$coin = $all_coin[$pil];
			if(count($coin) > 1){
				$title = "All Coins";
			}else{
				$title = $coin[0];
			}
		}
		print Display::Title($title);
		if($this->Claim($coin)){
			Functions::removeConfig("cookie");
			goto cookie;
		}
	}
	private function check($r){
		$scrap = $this->scrap->Result($r);
		if($scrap['cloudflare']){
			print Display::Error("https://earncryptowrs.in/app/links?currency=LTC\n");
			print Display::Error("Cloudflare Detect\n");
			Display::Line();
			return 1;
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
		$r = Requests::get(host."app/dashboard",$this->headers())[1];
		$this->check($r);
		$refId = explode('"', explode('value="https://earncryptowrs.in/?r=', $r)[1])[0];
		return $refId;
	}
	public function Firewall(){
		while(1){
			$r = Requests::get(host."firewall",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			if(!$scrap['input']){
				$scrap = $this->scrap->Result($r);
			}
			$data = $scrap['input'];
			
			if($scrap['captcha']['cf-turnstile']){
				$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host);
				$data['cf-turnstile-response']=$cap;
			}else{
				print Display::Error("Sitekey Error\n"); 
				continue;
			}
			if(!$cap)continue;
			
			$r = Requests::post(host."firewall/verify",$this->headers(), http_build_query($data))[1];
			if(preg_match('/Invalid Captcha/',$r))continue;
			Display::Cetak("Firewall","Bypassed");
			Display::Line();
			return;
		}
	}
	public function Claim($coins){
		while(true){
			$r = $this->Dashboard();
			if(!$r){
				print Display::Error("Cookie Expired\n");
				Display::Line();
				return 1;
			}
			foreach($coins as $a => $coin){
				$r = Requests::get(host."app/faucet?currency=".$coin,$this->headers())[1];
				$scrap = $this->scrap->Result($r);
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					//$this->Firewall();
					//continue;
					exit;
				}
				if($scrap['cloudflare']){
					print Display::Error(host."faucet/currency/".$coin.n);
					print Display::Error("Cloudflare Detect\n");
					Display::Line();
					return 1;
				}
				
				// Mesasge
				if(preg_match("/You don't have enough energy for Auto Faucet!/",$r)){exit(Error("You don't have enough energy for Auto Faucet!\n"));}
				if(preg_match('/Daily claim limit/',$r)){
					unset($coins[$a]);
					Display::Cetak($coin,"Daily claim limit");
					continue;
				}
				$status_bal = explode('</span>',explode('<span class="badge badge-danger">',$r)[1])[0];
				if($status_bal == "Empty"){
					unset($coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					continue;
				}
				preg_match('/<b id="minute">(\d+)<\/b>:(<b id="second">(\d+)<\/b>)/', $r, $matches);
				if (isset($matches[1]) && isset($matches[3])) {
					$minute = $matches[1];
					$second = $matches[3];
					$tmr = ($minute * 60) + $second;
					Functions::Tmr($tmr+5);
					continue;
				}
				// Delay
				$tmr = explode("-",explode('var wait = ',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr);
				}
				
				// Exsekusi
				$data = $scrap['input'];
				if(explode('rel=\"',$r)[1]){
					$antibot = $this->iewil->AntiBot($r);
					if(!$antibot)continue;
					$data['antibotlinks'] = str_replace("+"," ",$antibot);
				}
				if($scrap['captcha']){
					if($scrap['captcha']['cf-turnstile']){
						$data['captcha'] = "turnstile";
						$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host);
						$data['cf-turnstile-response']=$cap;
					}else{
						print Display::Error("Sitekey Error\n"); 
						continue;
					}
					if(!$cap)continue;
				}
				if($scrap['input']['_iconcaptcha-token']){
					$icon = $this->iconBypass($scrap['input']['_iconcaptcha-token']);
					if(!$icon)continue;
					$data = array_merge($data, $icon);
				}
				if(!$data){
					print Display::Error("Data not found");
					sleep(3);
					print "\r                              \r";
					continue;
				}
				$data = http_build_query($data);
				$r = Requests::post(host."app/faucet/verify?currency=".$coin,$this->headers(), $data)[1];
				//preg_match("/Toast\.fire\(\s*{\s*icon:\s*'([^']+)',\s*title:\s*'([^']+)',\s*text:\s*'([^']+)'/", $r, $matches);
				preg_match("/Toast\.fire\({\s*icon:\s*'([^']+)',\s*title:\s*'([^']+)',\s*text:\s*'([^']+)'/", $r, $matches);
				$scrap = $this->scrap->Result($r);
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					//$this->Firewall();
					//continue;
					exit;
				}
				if(preg_match('/Invalid API Key used/',$r)){
					unset($coins[$a]);
					Display::Cetak($coin,"Invalid apikey used");
					Display::Line();
					continue;
				}
				$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
				if($ban){
					print Display::Error("Your account".$ban.n);
					exit;
				}
				if(preg_match('/invalid amount/',$r)){
					unset($coins[$a]);
					print Display::Error("You are sending an invalid amount of payment to the user\n");
					Display::Line();
				}
				if(preg_match('/Shortlink in order to claim from the faucet!/',$r)){
					print Display::Error(explode("'",explode("html: '",$r)[1])[0]);
					Display::Line();
					exit;
				}
				if(preg_match('/sufficient funds/',$r)){
					unset($coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					Display::Line();
					continue;
				}
				
				if($matches[1] == "success"){
					Display::Cetak($coin," ");
					print Display::Sukses($matches[3]);
					Display::Line();
				}else{ 
					print Display::Error($matches[3]);
					if(preg_match('/Shortlink/',$matches[3])){
						print n;
						Display::Line();
						exit;
					}
					sleep(3);
					print "\r                              \r";
				}
			}
			if(!$coins){
				print Display::Error("All coins have been claimed\n");
				return;
			}
		}
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