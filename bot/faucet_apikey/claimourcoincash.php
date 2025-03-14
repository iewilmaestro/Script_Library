<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://claim.ourcoincash.xyz/",
refflink = "https://claim.ourcoincash.xyz/?r=1485",
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
		$r = $this->dashboard();
		if($r['Logout']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		if($this->Claim()){
			Functions::removeConfig("user_agent");
			Functions::removeConfig("cookie");
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
	private function Dashboard(){
		$r = Requests::get(host,$this->headers())[1];
		if(!preg_match('/Logout/',$r)){
			return ["Logout" => true];
		}else{
			return ["Logout" => false];
		}
	}
	private function Firewall(){
		while(1){
			$r = Requests::get(host."firewall",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			$data = $scrap['input'];
			if($scrap['captcha']['g-recaptcha']){
				$cap = $this->captcha->RecaptchaV2($scrap['captcha']['g-recaptcha'], host);
				$data['g-recaptcha-response'] = $cap;
			}else
			if($scrap['captcha']['h-captcha']){
				$cap = $this->captcha->Hcaptcha($scrap['captcha']['h-captcha'], host);
				$data['g-recaptcha-response'] = $cap;
				$data['h-captcha-response'] = $cap;
			}else
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
	private function Claim(){
		if(!$this->coins){
			$r = Requests::get(host,$this->headers())[1];
			preg_match_all('#https?:\/\/'.str_replace('.','\.',parse_url(host)['host']).'\/faucet\/currency\/([a-zA-Z0-9]+)#', $r, $matches);
			$this->coins = array_unique($matches[1]);
		}
		while(true){
			$r = $this->Dashboard();
			if($r['Logout']){
				print Display::Error("Cookie Expired\n");
				Display::Line();
				return 1;
			}
			foreach($this->coins as $a => $coin){
				$r = Requests::get(host."faucet/currency/".$coin,$this->headers())[1];
				$scrap = $this->scrap->Result($r);
				
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					$this->Firewall();
					continue;
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
					unset($this->coins[$a]);
					Display::Cetak($coin,"Daily claim limit");
					continue;
				}
				$status_bal = explode('</span>',explode('<span class="badge badge-danger">',$r)[1])[0];
				if($status_bal == "Empty"){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					continue;
				}
				
				// Delay
				$tmr = explode(";",explode('let timer = ',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr);
				}
				
				// Exsekusi
				$data = $scrap['input'];

				if(!$data)continue;
				if(explode('rel=\"',$r)[1]){
					$antibotlinks = $this->iewil->AntiBot($r);
					if(!$antibotlinks)continue;
					$data["antibotlinks"] = $antibotlinks;
				}
				// CAPTCHA
				if($scrap['captcha']){
					print Display::Error("Captcha detect\n");
					Display::Line();
					exit;
				}
				
				$data = http_build_query($data);
				$r = Requests::post(host."faucet/verify/".$coin,$this->headers(), $data)[1];
				$scrap2 = $this->scrap->Result($r);
				if($scrap2['firewall']){
					print Display::Error("Firewall Detect\n");
					$this->Firewall();
					continue;
				}
				
				$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
				$ss = explode("title: 'Success!',",$r)[1];
				$wr = explode("'",explode("html: '",$r)[1])[0];
				if($ban){
					print Display::Error("Your account".$ban.n);
					exit;
				}
				if(preg_match('/invalid amount/',$r)){
					unset($this->coins[$a]);
					print Display::Error("You are sending an invalid amount of payment to the user\n");
					Display::Line();
				}
				if(preg_match('/Shortlink in order to claim from the faucet!/',$r)){
					print Display::Error(explode("'",explode("html: '",$r)[1])[0]);
					Display::Line();
					exit;
				}
				if(preg_match('/sufficient funds/',$r)){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					Display::Line();
					continue;
				}
				if($ss){
					Display::Cetak($coin,($scrap['faucet'][1][0]-1)."/".$scrap['faucet'][2][0]);
					print Display::Sukses(strip_tags(explode("',",explode("html: '",$ss)[1])[0]));
					Display::Line();
				}elseif($wr){
					print Display::Error(substr($wr,0,30));
					sleep(3);
					print "\r                            \r";
				}else{
					print Display::Error("Server Down\n");
					sleep(3);
					print "\r                  \r";
				}
			}
			if(!$this->coins){
				print Display::Error("All coins have been claimed\n");
				exit;
			}
			sleep(2);
		}
	}
}

new Bot();