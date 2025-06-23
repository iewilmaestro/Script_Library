<?php

const
versi = "0.0.1",
host = "https://altcryp.com/",
refflink = "https://altcryp.com/?r=13472",
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
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		$r = $this->dashboard();
		if($r['Logout']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		
		Display::Cetak("Apikey",$this->captcha->getBalance());
		Display::Line();
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
		$logout = 0;
		if(!preg_match('/logout/',$r)){
			$logout = 1;
		}
		if(!preg_match('/Logout/',$r)){
			$logout = 1;
		}
		return ["Logout" => $logout];
	}
	private function Firewall(){
		while(1){
			$r = Requests::get(host."firewall",$this->headers())[1];
			$scrap = $this->scrap->Result($r);
			$data = $scrap['input'];
			
			if($scrap['captcha']['cf-turnstile']){
				$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host);
				$data['cf-turnstile-response']=$cap;
			}else{
				print Display::Error("Sitekey Error"); 
				sleep(2);
				print "\r                \r";
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
			if($r['Logout']){
				print Display::Error("Cookie Expired\n");
				Display::Line();
				return 1;
			}
			$retry = 0;
			foreach($this->coins as $a => $coin){
				$r = Requests::get(host."faucet/currency/".$coin,$this->headers())[1];
				$scrap = $this->scrap->Result($r);
				if($scrap['title'] == "404 Error Page"){
					unset($this->coins[$a]);
					print Display::Error($coin." 404 Error Page\n");
					Display::Line();
					continue;
				}
				
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					$this->Firewall();
					continue;
				}
				
				if($scrap['cloudflare']){
					print Display::Error(host."faucet/currency/".$coin.n);
					print Display::Error("Cloudflare Detect\n");
					Display::Line();
					if($retry > 3){
						return 1;
					}
					$retry++;
					continue;
				}
				
				// Mesasge
				if(preg_match("/You don't have enough energy for Auto Faucet!/",$r)){exit(Error("You don't have enough energy for Auto Faucet!\n"));}
				if(preg_match('/Daily claim limit/',$r)){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Daily claim limit");
					Display::Line();
					continue;
				}
				$status_bal = explode('</span>',explode('<span class="badge badge-danger">',$r)[1])[0];
				if($status_bal == "Empty"){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					Display::Line();
					continue;
				}
				
				// Delay
				$tmr = explode("-",explode('var wait = ',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr);
				}
				
				// Exsekusi
				$data = $scrap['input'];
				if($scrap['captcha']['cf-turnstile']){
					$data['captcha'] = "turnstile";
					$cap = $this->captcha->Turnstile($scrap['captcha']['cf-turnstile'], host);
					$data['cf-turnstile-response']=$cap;
				}else{
					print Display::Error("Sitekey Error"); 
					sleep(2);
					print "\r                \r";
					continue;
				}
				if(!$cap)continue;
				
				if(is_array($data)){$data = http_build_query($data);}else{continue;}
				
				$r = Requests::post(host."faucet/verify/".$coin,$this->headers(), $data)[1];
				$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
				$ss = explode("'",explode("Swal.fire('Good job!', '",$r)[1])[0];
				$wr = explode("'",explode("Swal.fire('",$r)[1])[0];
				if($ban){
					print Display::Error("Your account".$ban.n);
					exit;
				}
				if(preg_match('/Shortlink in order to claim from the faucet!/',$r)){
					print Display::Error(explode("'",explode("html: '",$r)[1])[0]);
					exit;
				}
				if(preg_match('/sufficient funds/',$r)){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					continue;
				}
				if($ss){
					Display::Cetak($coin,($scrap['faucet'][1][0]-1)."/".$scrap['faucet'][2][0]);
					print Display::Sukses("0.".str_replace("has been sent ","",strip_tags($ss)));
					Display::Cetak("Apikey",$this->captcha->getBalance());
					Display::Line();
				}elseif($wr){
					print Display::Error(substr($wr,0,30));
					sleep(3);
					print "\r                  \r";
				}else{
					print Display::Error("$coin: Server Down");
					sleep(3);
					print "\r                  \r";
				}
			}
			if(!$this->coins){
				print Display::Error("All coins have been claimed\n");
				exit;
			}
		}
	}
}
new Bot();