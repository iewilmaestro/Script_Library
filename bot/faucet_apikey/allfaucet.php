<?php

const
versi = "0.0.1",
host = "https://allfaucet.xyz/",
refflink = "https://allfaucet.xyz/?r=290",
youtube = "https://youtube.com/@iewil";

class Bot {
	protected $cookie;
	protected $uagent;
	
	function __construct(){
		$this->coins = "";
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
			
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		$this->captcha = new Captcha();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		$r = $this->dashboard();
		if(!$r['Username']){
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Username",$r['Username']);
		Display::Cetak("Apikey",$this->captcha->getBalance());
		Display::Line();
		if($this->Claim()){
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
		$user = explode('</b>',explode('<b>',explode('<span id="greeting"></span>', $r)[1])[1])[0];
		return ["Username" => $user];
	}
	private function Firewall(){
		while(1){
			$r = Requests::get(host."firewall",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			if(!$scrap['input']){
				$scrap = $this->scrap->Result($r);
			}
			$data = $scrap['input'];
			if($scrap['captcha']['g-recaptcha']){
				$cap = $this->captcha->RecaptchaV2($scrap['captcha']['g-recaptcha'], host);
				$data['g-recaptcha-response']=$cap;
			}else
			if($scrap['captcha']['cf-turnstile']){
				$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host);
				$data['cf-turnstile-response']=$cap;
			}else{
				//print Display::Error("Sitekey Error\n"); 
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
			$this->coins = $matches[1];
		}
		while(true){
			$r = $this->Dashboard();
			if(!$r['Username']){
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
					$antibot = $this->captcha->AntiBot($r);
					if(!$antibot)continue;
					$data['antibotlinks'] = $antibot;
				}
				
				if($scrap['captcha']){
					$captcha = $scrap['captcha'];
					$recaptcha = $captcha['g-recaptcha'];
					$turnstile = $captcha['cf-turnstile'];
					if($recaptcha){
						$data['captcha'] = "recaptchav2";
						$cap = $this->captcha->RecaptchaV2($recaptcha, host);
						if(!$cap)continue;
						$data['g-recaptcha-response'] = $cap;
					}elseif($turnstile){
						$data['captcha'] = "turnstile";
						$cap = $this->captcha->Turnstile($turnstile, host);
						if(!$cap)continue;
						$data['cf-turnstile-response']=$cap;
					}else{
						print Display::Error("Captcha Detect, please update Sc");
						exit;
					}
				}
				if(is_array($data)){$data = http_build_query($data);}else{continue;}
				
				$r = Requests::post(host."faucet/verify/".$coin,$this->headers(), $data)[1];
				$scrap = $this->scrap->Result($r);
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					$this->Firewall();
					continue;
				}
				if($scrap["response"]["success"]){
					Display::cetak($coin," ");
					Display::sukses($scrap["response"]["success"]);
					Display::cetak("Apikey",$this->captcha->getBalance());
					Display::line();
				}elseif($scrap["response"]["warning"]){ 
					Display::Error($scrap["response"]["warning"]);
					if($scrap["response"]["unset"]){
						unset($coins[$a]);
						PHP_EOL;
						Display::line();
					}elseif($scrap["response"]["exit"]){
						PHP_EOL;
						exit;
					}
					sleep(3);
					print "\r                              \r";
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