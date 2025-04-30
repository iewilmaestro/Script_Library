<?php

const
versi = "0.0.1",
host = "https://bitfaucet.net/",
refflink = "https://bitfaucet.net/?r=18047",
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
	private function dashboard(){
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$data["username"] = trim(explode('<',explode('<h3 class="card-title" style="margin-bottom: 0; color: var(--text-primary);">', $r)[1])[0]);
		$data["balance"] = explode('</h3>', explode('<h3 style="color: var(--text-primary);font-weight: bold">',explode('Available Balance</p>', $r)[1])[1])[0];
		return $data;
	}
	private function claim(){
		while(true){
			$r = Requests::get(host."faucet",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
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
			$available = explode('</b>', explode('<b class="mt-1 mb-2">Available faucet claim: ', $r)[1])[0];
			if(!$available){
				print Display::Error("After every 30 faucet claims,\n1 Shortlink must be completed to continue again!");
				exit;
			}
			if($scrap['faucet'][1][0] < 1)break;
			$tmr = explode('-', explode('var wait = ', $r)[1])[0];
			if($tmr){
				Functions::Tmr($tmr);
				continue;
			}
			$data = $scrap['input'];
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
			$r = Requests::post(host."faucet/verify", $this->headers(), $data)[1];
			$scrap = $this->scrap->Result($r);
			/*
			$wr = explode('</div>', explode('<div class="alert text-center alert-info"><i class="fas fa-exclamation-circle"></i> ', $r)[1])[0];//After every 30 faucet claims, 1 Shortlink must be completed to continue again! if you already claimed shortlinks so you receive 30 faucet claim every shortLinks claim 👍 
			if(preg_match('/Shortlink/', $wr)){
				print Display::Error(str_replace(",","\n",$wr));
				exit;
			}*/
			preg_match('/Toast\.fire\(\s*{\s*icon:\s*"([^"]*)"\s*,\s*title:\s*"([^"]*)"\s*}\s*\);/', $r, $matches);
			if($matches[1] == 'success'){
				Display::Cetak('Limit', $scrap['faucet'][0][0]);
				print Display::Sukses($matches[2]);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				Display::Line();
			}else{
				print Display::Error("no respon".n);
				Display::Line();
				exit;
			}
		}
		print Display::Error("Faucet Finished\n");
		Display::Line();
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