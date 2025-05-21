<?php

const
versi = "0.0.1",
host = "https://aruble.net/",
refflink = "https://aruble.net/?r=zvGZlK9TWL",
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
		Display::Line();
		
		$getFaucet = Requests::get(host."earn/manualfaucet", $this->headers())[1];
		print Display::Error("Payment method faucetpay\n");
		
		$currency = explode('</select>', explode('<select class="form-control" name="" id="curr-select" required>', $getFaucet)[1])[0];
		preg_match_all('/<option value="(\d+)"(.*?)>\s*([^<]+)\s*<\/option>/', $currency, $matches);

		for($i=0; $i<count($matches[1]); $i++){
			print Display::Menu($i,$matches[3][$i]);
		}
		print Display::Isi("input payment method");
		$currlock = readline();
		Display::Line();

		$currselect = $matches[1][$currlock];
		$payment_method = 2;

		if($this->claim($currselect, $payment_method)){
			Functions::removeConfig("cookie");
			Display::Line();
			goto cookie;
		}
	}
	private function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		$h[] = "x-requested-with: XMLHttpRequest";
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function dashboard(){
		$r = Requests::get(host."page/dashboard",$this->headers())[1];
		$data["username"] = explode('</a>',explode('<a href="page/dashboard" class="text-success">',$r)[1])[0];
		return $data;
	}
	private function claim($currselect, $payment_method){
		while(true){
			$r = Requests::get(host."earn/manualfaucet", $this->headers())[1];
			if(preg_match('/You have to wait/', $r)){
				$minutes = explode(',',explode('claim_countdown("claim_again", ', $r)[1])[0];//4, 54);
				$seconds = explode(')', explode(',',explode('claim_countdown("claim_again", ', $r)[1])[1])[0];
				Functions::Tmr($minutes*60 + $seconds);
				continue;
			}
			
			$postCsrf = json_decode(Requests::post(host."request/csrf", $this->headers(),1)[1],1);
			$csrfCLaim = $postCsrf["csrf_token"];
			$data = "paymethod=$payment_method&currselect=$currselect&csrftoken=".$csrfCLaim;
			$postAjaxClaim = json_decode(Requests::post(host."requestHandler/ajax/claim", $this->headers(), $data)[1],1);
			
			if($postAjaxClaim["status"] == "success"){
				// jika sukses
			}else{
				print Display::Error(strip_tags($postAjaxClaim["message"]).n);
				return 1;
			}
			
			$postCsrf = json_decode(Requests::post(host."request/csrf", $this->headers(),1)[1],1);
			$csrfCLaim = $postCsrf["csrf_token"];
			
			$antibotlinks = $this->captcha->Antibot($r);
			if(!$antibotlinks)continue;
			
			$data = [];
			$data["antibotlinks"] = $antibotlinks;
			$data["paymethod"] = $payment_method;
			$data["currselect"] = $currselect;
			$data["csrftoken"] = $csrfCLaim;
			$data["current_page"] = "https://aruble.net/earn/manualfaucet";

			$turnstile_sitekey = explode('"', explode('<div class="cf-turnstile mb-2" data-sitekey="', $r)[1])[0];
			$icon_token = explode("'", explode("<input type='hidden' name='_iconcaptcha-token' value='", $r)[1])[0];
			if($icon_token){
				$iconBypass = FreeCaptcha::iconBypass($icon_token, $this->headers(),"light", "IconCaptcha/examples/captcha-request.php");
				$data = array_merge($data, $iconBypass);
			}
			
			if($turnstile_sitekey){
				$turnstile = $this->captcha->Turnstile($turnstile_sitekey, "https://aruble.net");
				if(!$turnstile)continue;
				
				$data = "antibotlinks=".$antibotlinks."&turnstile_response=".$turnstile."&paymethod=$payment_method&currselect=$currselect&csrftoken=".$csrfCLaim;
			}
			
			if(!$data)continue;
			
			//$recaptcha = $captcha->RecaptchaV2("6LeYAZ4aAAAAAITBD3YyMA6KTTLRSonhXHLLMIXW", "https://aruble.net");
			//$data = "antibotlinks=".$antibotlinks."&recaptcha_response=".$recaptcha."&paymethod=$payment_method&currselect=5&csrftoken=".$csrfCLaim;
			
			$postCsrf = json_decode(Requests::post("https://aruble.net/requestHandler/ajax/verify", $this->headers(), $data)[1],1);
			if($postCsrf["status"] == "success"){
				print Display::Sukses(strip_tags($postCsrf["message"]));
				print Display::Line();
			}elseif(preg_match('/Please try again/', $postCsrf["message"])){
				continue;
			}elseif(preg_match('/security step!/', $postCsrf["message"])){
				continue;
			}else{
				print_r($postCsrf);
				exit;
			}
		}
	}
}

new Bot();