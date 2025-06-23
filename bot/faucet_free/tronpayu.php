<?php

const
versi = "0.0.1",
host = "https://tronpayu.io/",
refflink = "https://tronpayu.io/?ref=iewilmaestro",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
			
		$this->cookie = Functions::setConfig("cookie");
		$this->csrf = $this->getCsrf();
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
	private function getCsrf(){
		$get_csrf = explode(";", $this->cookie);
		foreach($get_csrf as $csrf){
			if(explode("csrf_cookie_name=", $csrf)[1]){
				return trim(explode("csrf_cookie_name=", $csrf)[1]);
			}
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
		$r = Requests::get(host."faucet.php",$this->headers())[1];
		$data["username"] = explode('</h4>',explode('<h4>Welcome back, ', $r)[1])[0];
		$data["balance"] = explode('</p>',explode('<p class="drop_down_header_text user_balance">', $r)[1])[0]." TRX";
		return $data;
	}
	private function claim(){
		while(true){
			$r = Requests::get(host."faucet.php",$this->headers())[1];
			if(preg_match('/Just a moment.../',$r)){
				print Display::Error("Cloudflare detect \n");
				return true;
			}
			/*
			$js = explode('<script>', $r);
			foreach($js as $a => $code_js){
				$code = explode('</script>', $code_js)[0];
				while(true){
					if(preg_match('/eval/', $code)){
						$rep = str_replace('eval(', 'console.log(', $code);
						file_put_contents('a.js', $rep);
						$code = shell_exec('node a.js');
						$timer = explode(')', explode("show_countdown_clock(", $code)[2])[0];
						if($timer){
							unlink("a.js");
							Functions::Tmr($timer);
							continue;
						}
					}else{
						break;
					}
				}
			}
			*/
			$token = explode(" ",explode("<input type=hidden name=_iconcaptcha-token value=", $r)[1])[0];
			if(!$token){
				$token = explode("'",explode("<input type='hidden' name='_iconcaptcha-token' value='", $r)[1])[0];
			}
			if(!$token){
				print Display::Error("captcha error\n");
				exit;
			}
			if($token){
				$data = FreeCaptcha::iconBypass($token, $this->headers(), "light", "iconcaptcha.php");
				if(!$data)continue;
			}
			unset($data["captcha"]);
			$data["action"] = "claim_hourly_faucet";
			$data["g-recaptcha-response"] = 'null';
			$data["h-captcha-response"] = 'null';
			$data["csrf_test_name"] = $this->csrf;
			
			if(is_array($data)){$data = http_build_query($data);}else{continue;}
			$r = json_decode(Requests::post(host."process.php",array_merge($this->headers(),["x-requested-with: XMLHttpRequest"]), $data)[1],1);
			if($r["ret"] < 1){
				print Display::Error($r["mes"].n);
				if($r["mes"] == "You have to login to continue!"){
					return 1;
				}
				Functions::Tmr(3600);
				Display::Line();
			}
			if($r["num"]){
				Functions::Roll($r["num"]);
				print m." | ".p.$r["mes"].n;
				Display::Cetak("Balance",$r["balance"]/1000000);
				Display::Line();
				Functions::Tmr(3600);
			}
		}
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