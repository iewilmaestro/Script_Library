<?php

const
title = "feyorratop",
versi = "1.0.0",
class_require = "1.0.9",
host = "https://feyorra.top/",
refflink = "https://feyorra.top/?r=34383",
youtube = "https://youtube.com/@iewil";

class Bot {
	public $cookie,$uagent;
	public function __construct(){
		
		Display::Ban(title, versi);
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		$this->captcha = new captcha();
		$this->iewil = new iewil();
		$this->scrap = new HtmlScrap();
		$this->tesseract = new Tesseract(title);
		
		Display::Ban(title, versi);
		
		$r = $this->Dashboard();
		if(!$r['balance']){
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Balance",$r['balance']);
		Display::Line();
		if($this->Claim()){
			Functions::removeConfig("user_agent");
			Functions::removeConfig("cookie");
			goto cookie;
		}
	}
	private function Dashboard(){
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$data['balance'] = explode('<',explode('<p>', explode('<div class="left_tsc">', $r)[1])[1])[0];
		return $data;
	}
	private function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function Claim(){
		while(true){
			$r = Requests::get(host."faucet",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			if(preg_match('/required to continue claiming!/', $r)){
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
			$timer = explode('-',explode('let wait = ', $r)[1])[0];
			if($timer){
				Functions::Tmr($timer);
				continue;
			}
			$data = $scrap['input'];
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->iewil->Antibot($r);
				if(!$antibot)continue;
			}
			$captcha_img = explode('"', explode('<img id="Imageid" src="', $r)[1])[0];
			
			$data['human_move']=true;
			if($scrap['input']['_iconcaptcha-token']){
				$icon = FreeCaptcha::iconBypass($scrap['input']['_iconcaptcha-token'], $this->headers());
				if(!$icon)continue;
				$data = array_merge($data, $icon);
			}elseif($scrap['captcha']['g-recaptcha']){
				$data['captcha'] = "turnstile";
				if($scrap['captcha']['g-recaptcha'] == "0x4AAAAAAA3a78IiE1GGrmei"){
					$cap = $this->captcha->Turnstile($scrap['captcha']['g-recaptcha'], host);
					$data['cf-turnstile-response']=$cap;
					$data['g-recaptcha-response']=$cap;
				}else{
					$data['captcha'] = "recaptchav2";
					$cap = $this->captcha->RecaptchaV2($scrap['captcha']['g-recaptcha'], host);
					$data['cf-turnstile-response']=$cap;
					$data['g-recaptcha-response']=$cap;
				}
				if(!$cap)continue;
			}elseif($captcha_img){
				$img = base64_encode(Requests::get($captcha_img,$this->headers())[1]);
				$cap = $this->tesseract->Feytop($img);
				foreach ($data as $key => $value) {
					if (empty($value)) {
						$data[$key] = $cap;
					}
				}
				if(!$cap)continue;
			}elseif($scrap['captcha']){
				print Display::Error("Sitekey Error\n"); 
				continue;
			}
			if($antibot){
				$antibot['antibotlinks'] = $antibot;
				$data = array_merge($antibot, $data);
			}
			if(!$data){
				print Display::Error("Data not found");
				sleep(3);
				print "\r                              \r";
				continue;
			}
			
			$data = http_build_query($data);
			sleep(5);
			$r = Requests::post(host."faucet/verify",$this->headers(), $data)[1];
			if(preg_match('/Locked/', $r)){
				$tmr = explode('">',explode('<span class="counter" wait="',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr+5);
					continue;
				}
			}
			$wr = explode('</div>',explode('<i class="fas fa-exclamation-circle"></i> ', $r)[1])[0];
			$ss = explode("',",explode("title: '",$r)[1])[0];
			if(preg_match('/Shortlink must be completed/', $r)){
				$sl = explode("'",explode("Swal.fire('Error!', '", $r)[1])[0];
				print Display::Error($sl.n);
				exit;
			}
			if($ss){
				print Display::Sukses($ss);
				$r = $this->Dashboard();
				Display::Cetak("Balance",$r['balance']);
				//Display::Cetak("Apikey",$this->captcha->getBalance());
				Display::Line();
			}elseif($wr){
				print Display::Error($wr.n);
				Display::Line();
			}else{
				print_r($r);
				exit;
			}
		}
		print Display::Error("faucet clim habis\n");
		Display::Line();
	}
}
new Bot();