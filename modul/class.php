<?php

const class_version = "1.1.7";

// Warna teks
const n = "\n";          // Baris baru
const d = "\033[0m";     // Reset
const m = "\033[1;31m";  // Merah
const h = "\033[1;32m";  // Hijau
const k = "\033[1;33m";  // Kuning
const b = "\033[1;34m";  // Biru
const u = "\033[1;35m";  // Ungu
const c = "\033[1;36m";  // Cyan
const p = "\033[1;37m";  // Putih
const o = "\033[38;5;214m"; // Warna mendekati orange
const o2 = "\033[01;38;5;208m"; // Warna mendekati orange

// Warna teks tambahan
const r = "\033[38;5;196m";   // Merah terang
const g = "\033[38;5;46m";    // Hijau terang
const y = "\033[38;5;226m";   // Kuning terang
const b1 = "\033[38;5;21m";   // Biru terang
const p1 = "\033[38;5;13m";   // Ungu terang
const c1 = "\033[38;5;51m";   // Cyan terang
const gr = "\033[38;5;240m";  // Abu-abu gelap

// Warna latar belakang
const mp = "\033[101m\033[1;37m";  // Latar belakang merah
const hp = "\033[102m\033[1;30m";  // Latar belakang hijau
const kp = "\033[103m\033[1;37m";  // Latar belakang kuning
const bp = "\033[104m\033[1;37m";  // Latar belakang biru
const up = "\033[105m\033[1;37m";  // Latar belakang ungu
const cp = "\033[106m\033[1;37m";  // Latar belakang cyan
const pm = "\033[107m\033[1;31m";  // Latar belakang putih (merah teks)
const ph = "\033[107m\033[1;32m";  // Latar belakang putih (hijau teks)
const pk = "\033[107m\033[1;33m";  // Latar belakang putih (kuning teks)
const pb = "\033[107m\033[1;34m";  // Latar belakang putih (biru teks)
const pu = "\033[107m\033[1;35m";  // Latar belakang putih (ungu teks)
const pc = "\033[107m\033[1;36m";  // Latar belakang putih (cyan teks)
const yh = d."\033[43;30m"; // Latar belakang kuning (black teks)

// Warna latar belakang tambahan
const bg_r = "\033[48;5;196m";   // Latar belakang merah terang
const bg_g = "\033[48;5;46m";    // Latar belakang hijau terang
const bg_y = "\033[48;5;226m";   // Latar belakang kuning terang
const bg_b1 = "\033[48;5;21m";   // Latar belakang biru terang
const bg_p1 = "\033[48;5;13m";   // Latar belakang ungu terang
const bg_c1 = "\033[48;5;51m";   // Latar belakang cyan terang
const bg_gr = "\033[48;5;240m";  // Latar belakang abu-abu gelap

const LIST_YOUTUBE = [
	"https://youtu.be/lf1IpmCBGKU",
	"https://youtu.be/ZWBJ7unGjm8",
	"https://youtu.be/NlFhmw3DVvc",
	"https://youtu.be/a8PLbkNoj0E",
	"https://youtu.be/uCFB9J14GrI",
	"https://youtu.be/YnvE9JSoi-k",
	"https://youtu.be/XX4kVx-80Vw",
	"https://youtu.be/wfczg8pS9AA",
	"https://youtu.be/5S5jwy8Ulnw",
	"https://youtu.be/_mRSxm6a1OQ",
	"https://youtu.be/sgJecMF6ThI",
	"https://youtu.be/k1Lep8-9jig",
	"https://youtu.be/0gAY6vUdcRg",
	"https://youtu.be/uoP0GSveytM",
	"https://youtu.be/IF292mEvpvA",
	"https://youtu.be/x8FjgcCt3kc",
	"https://youtu.be/vOPgqGLx2gA"
];

class Iewil {
	protected $url;
	function __construct(){
		$this->url = "https://iewilbot.my.id/res.php";
	}
	private function requests($postParameter){
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParameter);
		$response = curl_exec($ch);
		if(!curl_errno($ch)) {
			switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
				case 200:  # OK
					break;
				default:
					return '{"status":0, "message":"iewilbot HTTP code "'.$http_code.'}';
			}
		}
		curl_close($ch);
		return $response;
	}
	private function getResult($postParameter){
		$r = json_decode($this->requests($postParameter),1);
		//print_r($r);
		if($r && $r['status']){
			return $r['result'];
		}
		if($r["msg"]){
			print Display::Error(substr($r["msg"],0,30));
			sleep(2);
			print "\r                                      \r";
		}
		
		print Display::Error("iewilbot say captcha can't be solve");
		sleep(2);
		print "\r                                          \r";
		
	}
	public function IconCoordiant($base64Img){
		$postParameter = http_build_query([
			"img"		=> $base64Img,
			"method"	=> "icon_coordinat"
		]);
		return $this->getResult($postParameter);
	}
	public function AntibotGif($base64ImgGIF){
		$postParameter = http_build_query([
			"imgGif"	=> $base64ImgGIF,
			"method"	=> "antibot_gif"
		]);
		return $this->getResult($postParameter);
	}
	public function Turnstile( $sitekey, $pageurl){
		$postParameter = http_build_query([
			"pageurl"	=> $pageurl,
			"sitekey"	=> $sitekey,
			"method"	=> "turnstile"
		]);
		return $this->getResult($postParameter);
	}
	public function gp($src){
		$postParameter = http_build_query([
			"main"		=> base64_encode($src),
			"method"	=> "gp"
		]);
		return $this->getResult($postParameter);
	}
	
	public function altcha($signature, $salt, $challenge){
		$postParameter = http_build_query([
			"signature"	=> $signature,
			"salt"		=> $salt,
			"challenge"	=> $challenge,
			"method"	=> "altcha"
		]);
		return $this->getResult($postParameter);
	}
	
	public function Antibot($source){
		$data["method"] = "antibot";
		
		$main = explode('"',explode('src="',explode('Bot links',$source)[1])[1])[0];
		$data["main"] 	= $main;
		$src = explode('rel=\"',$source);
		foreach($src as $x => $sour){
			if($x == 0)continue;
			$no = explode('\"',$sour)[0];
			$img = explode('\"',explode('src=\"',$sour)[1])[0];
			$data[$no] = $img;
		}
		$postParameter = http_build_query($data);
		$res = $this->getResult($postParameter);
		unset($data["apikey"]);
		unset($data["method"]);
		unset($data["main"]);
		if(isset($res["solution"])){
			$cap = $res["solution"];
			$cek = explode(",", $cap);
			for($i=0;$i<count($data);$i++){
				if(!$cek[$i]){
					return;
				}
			}
			return " ".str_replace(","," ",$cap);
		}
	}
}
class FreeCaptcha {
	static function Antibot($source){
		$src = explode('rel=\"',$source);
		print_r($src);exit;
	}
	static function widgetId() {
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
	static function iconBypass($token, $icon_header, $theme = "light", $sub = "icaptcha/req"){
		
		$retry = 0;
		
		$icon_header[] = "origin: ".host;
		$icon_header[] = "referer: ".host;
		$icon_header[] = "x-iconcaptcha-token: ".$token;
		$icon_header[] = "x-requested-with: XMLHttpRequest";
		
		bypass_icon:
		if($retry == 10)return;
		$timestamp = round(microtime(true) * 1000);
		$initTimestamp = $timestamp - 2000;
		$widgetID = self::widgetId();
		
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
		sleep(2);
		print "\r                        \r";
		print "---[1] Bypass....";
		$r = json_decode(base64_decode(Requests::post(host.$sub,$icon_header, $data)[1]),1);
		$retry++;
		if(isset($r["challenge"])){
			$base64Image = $r["challenge"];
			$challengeId = $r["identifier"];
		}else{
			sleep(2);
			print "\r                        \r";
			print "---[x] load img failed";
			sleep(2);
			print "\r                        \r";
			goto bypass_icon;
		}
		$timestamp = round(microtime(true) * 1000);
		$initTimestamp = $timestamp - 2000;
		$data = ["payload" => 
			base64_encode(json_encode([
				"widgetId"		=> $widgetID,
				"challengeId"	=> $challengeId,
				"action"		=> "SELECTION",
				"x"				=> 160,
				"y"				=> 24,
				"width"			=> 320,
				"token" 		=> $token,
				"timestamp"		=> $timestamp,
				"initTimestamp"	=> $initTimestamp
			]))
		];
		sleep(2);
		print "\r                        \r";
		print "---[2] Bypass..";
		$r = json_decode(base64_decode(Requests::post(host.$sub,$icon_header, $data)[1]),1);
		
		if (($r['completed'] ?? false) === false) {
			sleep(2);
			print "\r                        \r";
			print "---[3] Bypass failed";
			sleep(2);
			print "\r                        \r";
			goto bypass_icon;
		}
		sleep(2);
		print "\r                        \r";
		print "---[3] Bypass success";
		sleep(2);
		print "\r                        \r";
		$data = [];
		$data['captcha'] = "icaptcha";
		$data['_iconcaptcha-token']=$token;
		$data['ic-rq']=1;
		$data['ic-wid'] = $widgetID;
		$data['ic-cid'] = $challengeId;
		$data['ic-hp'] = '';
		return $data;
	}
	
	static function Icon_hash($header){
		$url = host.'system/libs/captcha/request.php';
		$data["method"] = "icon_hash";
		$head = array_merge($header, ["X-Requested-With: XMLHttpRequest"]);
		$getCap = json_decode(Requests::post($url,$head,"cID=0&rT=1&tM=light")[1],1);
		if(!$getCap){
			$url = host.'src/captcha-request.php';
			$getCap = json_decode(Requests::post($url,$head,"cID=0&rT=1&tM=light")[1],1);
		}
		$head2 = array_merge($header, ["accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8"]);
		foreach($getCap as $c){
			$data[$c] = base64_encode(Requests::get($url.'?cid=0&hash='.$c, $head2)[1]);
		}
		$data = http_build_query($data);
		$cap = json_decode(Requests::post("https://iewilbot.my.id/res.php","",$data)[1],1);
		if(!$cap['status'])return 0;
		Requests::postXskip($url,$head,"cID=0&pC=".$cap['result']."&rT=2");
		return $cap['result'];
	}
}

?>
