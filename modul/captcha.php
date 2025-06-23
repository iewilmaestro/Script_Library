<?php

class Captcha {
	
	protected $url;
	protected $provider;
	protected $key;
	
	public function __construct(){
		$type = Functions::cofigApikey();
		$this->url = $type["url"];
		$this->provider = $type["provider"];
		if($this->provider == "xevil"){
			$this->key = $type["apikey"]."|SOFTID1204538927";
		}else{
			$this->key = $type["apikey"];
		}
	}
	private function in_api($content, $method, $header = 0){
		$param = "key=".$this->key."&json=1&".$content;
		if($method == "GET")return json_decode(file_get_contents($this->url.'in.php?'.$param),1);
		$opts['http']['method'] = $method;
		if($header) $opts['http']['header'] = $header;
		$opts['http']['content'] = $param;
		return file_get_contents($this->url.'in.php', false, stream_context_create($opts));
	}
	private function res_api($api_id){
		$params = "?key=".$this->key."&action=get&id=".$api_id."&json=1";
		return json_decode(file_get_contents($this->url."res.php".$params),1);
	}
	private function solvingProgress($xr,$tmr, $cap){
		if($xr < 50){
			$wr=h;
		}elseif($xr >= 50 && $xr < 80){
			$wr=k;
		}else{
			$wr=m;
		}
		$xwr = [$wr,p,$wr,p];
		$sym = [' ─ ',' / ',' │ ',' \ ',];
		$a = 0;
		for($i=$tmr*4;$i>0;$i--){
			print $xwr[$a % 4]." Bypass $cap $xr%".$sym[$a % 4]." \r";usleep(100000);
			if($xr < 99)$xr+=1;$a++;
		}
		return $xr;
	}
	private function getResult($data ,$method, $header = 0){
		$cap = $this->filter(explode('&',explode("method=",$data)[1])[0]);
		$get_res = $this->in_api($data ,$method, $header);
		if(is_array($get_res)){
			$get_in = $get_res;
		}else{
			$get_in = json_decode($get_res,1);
		}
		if(!$get_in["status"]){
			$msg = $get_in["request"];
			if($msg){
				print Display::Error("in_api @".$this->provider." ".$msg.n);
			}elseif($get_res){
				print Display::Error($get_res.n);
			}else{
				print Display::Error("in_api @".$this->provider." something wrong\n");
			}
			return 0;
		}
		$a = 0;
		while(true){
			echo " Bypass $cap $a% |   \r";
			$get_res = $this->res_api($get_in["request"]);
			if($get_res["request"] == "CAPCHA_NOT_READY"){
				$ran = rand(5,10);
				$a+=$ran;
				if($a>99)$a=99;
				echo " Bypass $cap $a% ─ \r";
				$a = $this->solvingProgress($a,5, $cap);
				continue;
			}
			if($get_res["status"]){
				echo " Bypass $cap 100%";
				sleep(1);
				print "\r                              \r";
				print h."[".p."√".h."] Bypass $cap success";
				sleep(2);
				print "\r                              \r";
				return $get_res["request"];
			}
			print m."[".p."!".m."] Bypass $cap failed";
			sleep(2);
			print "\r                              \r";
			print Display::Error($cap." @".$this->provider." Error\n");
			return 0;
		}
	}
	private function filter($method){
		$map = [
			"userrecaptcha" => "RecaptchaV2",
			"hcaptcha" => "Hcaptcha",
			"turnstile" => "Turnstile",
			"universal" => "Ocr",
			"base64" => "Ocr",
			"antibot" => "Antibot",
			"authkong" => "Authkong",
			"teaserfast" => "Teaserfast"
		];

		return $map[$method] ?? null;
	}
	
	public function getBalance(){
		$res =  json_decode(file_get_contents($this->url."res.php?action=userinfo&key=".$this->key),1);
		return $res["balance"];
	}
	public function RecaptchaV2($sitekey, $pageurl){
		$data = http_build_query(["method" => "userrecaptcha","sitekey" => $sitekey,"pageurl" => $pageurl]);
		return $this->getResult($data, "GET");
	}
	public function Hcaptcha($sitekey, $pageurl ){
		$data = http_build_query(["method" => "hcaptcha","sitekey" => $sitekey,"pageurl" => $pageurl]);
		return $this->getResult($data, "GET");
	}
	public function Turnstile($sitekey, $pageurl){
		$data = http_build_query(["method" => "turnstile","sitekey" => $sitekey,"pageurl" => $pageurl]);
		return $this->getResult($data, "GET");
	}
	public function Authkong($sitekey, $pageurl){
		$data = http_build_query(["method" => "authkong","sitekey" => $sitekey,"pageurl" => $pageurl]);
		return $this->getResult($data, "GET");
	}
	public function Ocr($img){
		if($this->provider == "xevil"){
			$data = "method=base64&body=".$img;
		}else{
			$data = http_build_query(["method" => "universal","body" => $img]);
		}
		return $this->getResult($data, "POST");
	}
	public function AntiBot($source){
		$main = explode('"',explode('data:image/png;base64,',explode('Bot links',$source)[1])[1])[0];
		if(!$main){
			$main = explode('"',explode('data:image/png;base64,',explode('Click the buttons in the following order',$source)[1])[1])[0];
			if(!$main)return 0;
		}
		if($this->provider == "xevil"){
			$data = "method=antibot&main=$main";
		}else{
			$data["method"] = "antibot";$data["main"] = $main;
		}
		
		$src = explode('rel=\"',$source);
		foreach($src as $x => $sour){
			if($x == 0)continue;
			$no = explode('\"',$sour)[0];
			if($this->provider == "xevil"){
				$img = explode('\"',explode('data:image/png;base64,',$sour)[1])[0];
				$data .= "&$no=$img";
			}else{
				$img = explode('\"',explode('src=\"',$sour)[1])[0];
				$data[$no] = $img;
			}
		}
		if($this->provider == "xevil"){
			$res = $this->getResult($data, "POST");
		}else{
			$data = http_build_query($data);
			$ua = "Content-type: application/x-www-form-urlencoded";
			$res = $this->getResult($data, "POST", $ua);
		}
		if($res)return " ".str_replace(","," ",$res);
		return;
	}
	public function Teaserfast($main, $small){
		if($this->provider == "multibot"){
			return ["error"=> true, "msg" => "not support key!"];
		}
		$data = http_build_query(["method" => "teaserfast","main_photo" => $main,"task" => $small]);
		$ua = "Content-type: application/x-www-form-urlencoded";
		return $this->getResult($data, "POST",$ua);
	}
}

?>