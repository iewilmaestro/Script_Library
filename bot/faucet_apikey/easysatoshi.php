<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://easysatoshi.com/",
refflink = "https://easysatoshi.com/ref/iewilmaestro",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		$this->captcha = new Captcha();
		$this->scrap = new HtmlScrap();
		Functions::view();
		
		Display::Ban(title, versi);
		$r = $this->dashboard();
		if(!$r['username']){
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Username", $r['username']);
		Display::Cetak("Balance", $r['balance']);
		Display::Cetak("Apikey", $this->captcha->getBalance());
		Display::Line();
		
		$this->viewAds();
		$this->surf();
		$this->faucet();
	}
	private function headers($data = 0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		$h[] = "x-requested-with: XMLHttpRequest";
		return $h;
	}
	private function dashboard(){
		$r = Requests::get(host."account", $this->headers())[1];
		$data['username'] = explode('"',explode('value="https://easysatoshi.com/ref/', $r)[1])[0];
		$data['balance'] = explode('</span>',explode('<span id="balance">' ,$r)[1])[0]; //279.3 Tokens</span>
		return $data;
	}
	private function viewAds(){
		Display::Title("View Ads");
		$r = Requests::get(host."ads", $this->headers())[1];
		$list_id = explode('<a href="/view/', $r);
		
		// Menentukan status Ads
		foreach($list_id as $num => $_id){
			if($num == 0)continue;
			$id = explode('"', $_id)[0];
			$status = (explode('"', explode('class="card shadow text-decoration-none ', $_id)[1])[0])?true:false;
			if($status)break;
			$r = Requests::get(host."view/".$id, $this->headers())[1];
			$turnstile = explode('"', explode('class="cf-turnstile" data-sitekey="' ,$r)[1])[0];//0x4AAAAAAARFASGjXilqHrmu
			$url = explode("'", explode("let url = '", $r)[1])[0];//https://crypto-motorsports.com/?ref=ProInvest';
            $token = explode('"', explode('name="token" value="', $r)[1])[0];
			$timer = explode(';', explode("let duration = ", $r)[1])[0];
			if($timer){
				Functions::Tmr($timer);
			}
			$ua_post = [
				'accept: */*',
				'content-type: application/x-www-form-urlencoded; charset=UTF-8',
				'origin: '.host,
				'sec-fetch-site: same-origin',
				'referer: '.host.'view/'.$id,
				'accept-language: id,id-ID;q=0.9,en-US;q=0.8,en;q=0.7'
			];
			Requests::post(host."ajax/setTimestamp", array_merge($this->headers(), $ua_post))[1];
			if($turnstile){
				$cap = $this->captcha->Turnstile($turnstile, host);
				if(!$cap)continue;
				$data = "adUId=$id&token=$token&cf-turnstile-response=".$cap;
			}else{
				print Display::Error("captcha not found\n");
				Display::Line();
				break;
			}
			if(!$data){
				print Display::Error("data not found\n");
				Display::Line();
				break;
			}
			
			$r = json_decode(Requests::post(host."ajax/verify", array_merge($this->headers(), $ua_post), $data)[1],1);
			if($r['status'] == 200){
				print Display::Sukses($r['message']);
				$r = $this->Dashboard();
				Display::Cetak("Balance", $r['balance']);
				Display::Cetak("Apikey", $this->captcha->getBalance());
				Display::Line();
			}
		}
		print Display::Error("Ads Habis\n");
		Display::Line();
	}
	private function surf(){
		Display::Title("Surf Ads");
		$r = Requests::get(host."surf", $this->headers())[1];
		$list_id = explode('<a href="/surf/' , $r);
		foreach($list_id as $num => $_id){
			if($num == 0)continue;
			$id = explode('"', $_id)[0];
			$status = (explode('"', explode('class="card shadow text-decoration-none ', $_id)[1])[0])?true:false;
			if($status)break;
			
			$r = Requests::get(host."surf/".$id, $this->headers())[1];
			$turnstile = explode('"', explode('class="cf-turnstile" data-sitekey="' ,$r)[1])[0];
			$letId = explode("'", explode("let id = '", $r)[1])[0].rand(1000,9999);
			$timer = explode(";", explode("let count = ", $r)[1])[0];
			$ua_post = [
				'accept: */*',
				'content-type: application/x-www-form-urlencoded;',
				'origin: '.host,
				'sec-fetch-site: same-origin',
				'referer: '.host.'surf/'.$id,
				'accept-language: id,id-ID;q=0.9,en-US;q=0.8,en;q=0.7'
			];
			
			Requests::get(host."surf?uid=$id&c=$letId", array_merge($this->headers(), $ua_post));
			if($timer){
				Functions::Tmr($timer);
			}
			if($turnstile){
				$cap = $this->captcha->Turnstile($turnstile, host);
				if(!$cap)continue;
				$data = "cf-turnstile-response=$cap&uid=$id&c=".$letId;
			}else{
				print Display::Error("captcha not found\n");
				Display::Line();
				break;
			}
			if(!$data){
				print Display::Error("data not found\n");
				Display::Line();
				break;
			}
			
			$r = json_decode(Requests::post(host."ajax/surf", array_merge($this->headers(), $ua_post), $data)[1],1);
			if($r['success']){
				print Display::Sukses($r['message']);
				$r = $this->Dashboard();
				Display::Cetak("Balance", $r['balance']);
				Display::Cetak("Apikey", $this->captcha->getBalance());
				Display::Line();
			}
		}
		print Display::Error("Surf Habis\n");
		Display::Line();
	}
	private function faucet(){
		Display::Title("Faucet");
		while(true){
			$r = Requests::get(host."faucet", $this->headers())[1];
			$csrfToken = explode('"', explode('<input type="hidden" name="csrfToken" value="', $r)[1])[0];
			$turnstile = explode('"', explode('class="cf-turnstile" data-sitekey="' ,$r)[1])[0];
			$timer = explode(';', explode('let timeLeft = ', $r)[1])[0];
			if($timer){
				Functions::Tmr($timer);continue;
			}
			if($turnstile){
				$cap = $this->captcha->Turnstile($turnstile, host);
				if(!$cap)continue;
				$data = "csrfToken=$csrfToken&cf-turnstile-response=$cap";
			}else{
				print Display::Error("captcha not found\n");
				Display::Line();
				break;
			}
			$r = Requests::post(host."faucet", $this->headers(), $data)[1];
			$pattern = "/type:\s*'(\w+)',\s*message:\s*'([^']+)'/";
			preg_match_all($pattern, $r, $out);
			if($out[1][0] == "success"){
				print Display::Sukses($out[2][0]);
				print Display::Sukses($out[2][1]);
				$r = $this->Dashboard();
				Display::Cetak("Balance", $r['balance']);
				Display::Cetak("Apikey", $this->captcha->getBalance());
				Display::Line();
			}else{
				print Display::Error($out[2][0].n);
				break;
			}
		}
		print Display::Error("Faucet Habis\n");
		Display::Line();
	}
}

new Bot();