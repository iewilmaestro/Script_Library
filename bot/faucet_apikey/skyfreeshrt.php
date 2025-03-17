<?php

const
versi = "0.0.1",
host = "https://skyfreeshrt.top/faucet/",
refflink = "https://skyfreeshrt.top/faucet/?r=15",
youtube = "https://youtube.com/@iewil";

class Bot {
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
		$this->iewil = new Iewil();
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
	private function check_code($response){
		return ($response["status_code"] == 200)?true:false;
	}
	private function get($url, $head){
		$attempt = 0;
		while(true){
			$response = Requests::get($url, $head);
			print "\r                              \r";
			if($this->check_code($response))return $response;
			$body = strtolower($response[1]);
			$title = explode('</title>', explode('<title>', $body)[1])[0];
			if($title){
				print Display::Error($title);
				sleep(10);
				print "\r   ".str_repeat(" ",strlen($title))."   \r";
				continue;
			}
			$attempt++;
			print Display::Error("try reconnecting..($attempt)");
			sleep(10);
		}
	}
	private function post($url, $head, $data){
		$attempt = 0;
		while(true){
			$response = Requests::post($url, $head, $data);
			print "\r                              \r";
			if($this->check_code($response))return $response;
			$body = strtolower($response[1]);
			$title = explode('</title>', explode('<title>', $body)[1])[0];
			if($title){
				print Display::Error($title);
				sleep(10);
				print "\r   ".str_repeat(" ",strlen($title))."   \r";
				continue;
			}
			$attempt++;
			print Display::Error("try reconnecting..($attempt)");
			sleep(10);
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
		$r = $this->get(host,$this->headers())[1];
		$user = explode('</b>',explode('<b>',explode('<span id="greeting"></span>', $r)[1])[1])[0];
		return ["Username" => $user];
	}
	private function Firewall(){
		while(1){
			$r = $this->get(host."firewall",$this->headers())[1];
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
			
			$r = $this->post(host."firewall/verify",$this->headers(), http_build_query($data))[1];
			if(preg_match('/Invalid Captcha/',$r))continue;
			Display::Cetak("Firewall","Bypassed");
			Display::Line();
			return;
		}
	}
	private function Claim(){
		if(!$this->coins){
			$r = $this->get(host,$this->headers())[1];
			preg_match_all('#https?:\/\/'.str_replace('.','\.',parse_url(host)['host']).'\/faucet\/faucet\/currency\/([a-zA-Z0-9]+)#', $r, $matches);
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
				$r = $this->get(host."faucet/currency/".$coin,$this->headers())[1];
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
					$antibot = $this->iewil->AntiBot($r);
					if(!$antibot)continue;
					$data['antibotlinks'] = $antibot;
				}
				
				if($scrap['captcha']){
					if($scrap['captcha']["g-recaptcha"]){
						$data['captcha'] = "recaptchav2";
						$cap = $this->captcha->RecaptchaV2($scrap['captcha']['g-recaptcha'], host);
						$data['g-recaptcha-response']=$cap;
					}elseif($scrap['captcha']['cf-turnstile']){
						$data['captcha'] = "turnstile";
						$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host);
						$data['cf-turnstile-response']=$cap;
					}else{
						print Display::Error("Captcha Detect, please update Sc");
						exit;
					}
				}
				$data = http_build_query($data);
				$r = $this->post(host."faucet/verify/".$coin,$this->headers(), $data)[1];
				
				$scrap = $this->scrap->Result($r);
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					$this->Firewall();
					continue;
				}
				if(preg_match('/Invalid API Key used/',$r)){
					unset($this->coins[$a]);
					Display::Cetak($coin,"invalid apikey used\n");
					Display::Line();
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
					Display::Cetak($coin," ");
					print Display::Sukses(strip_tags(explode("'",explode("html: '",$ss)[1])[0]));
					Display::Cetak("Apikey",$this->captcha->getBalance());
					Display::Line();
				}elseif($wr){
					print Display::Error(substr($wr,0,30));
					sleep(3);
					print "\r                              \r";
				}else{
					print Display::Error("Server Down");
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