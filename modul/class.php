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

Class Requests {
	static function Curl($url, $header=0, $post=0, $data_post=0, $cookie=0, $proxy=0, $skip=0){
		$max_retries = 5;
		$attempt = 0;
		while ($attempt < $max_retries) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_COOKIE,TRUE);
			if($cookie){
				curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie);
				curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie);
			}
			if($post) {
				curl_setopt($ch, CURLOPT_POST, true);
			}
			if($data_post) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);
			}
			if($header) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			}
			if($proxy){
				curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
				curl_setopt($ch, CURLOPT_PROXY, $proxy);
			}
			curl_setopt($ch, CURLOPT_HEADER, true);
			$r = curl_exec($ch);
			if($skip){
				return;
			}
			$c = curl_getinfo($ch);
			if(!$c) return "Curl Error : ".curl_error($ch);
			else{
				$head = substr($r, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
				$body = substr($r, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
				$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				if ($status_code == 200) {
					return array($head,$body);
				}else{
					$body = strtolower($body);
					$title = explode('</title>', explode('<title>', $body)[1])[0];
					if($title){
						print $title;
						sleep(10);
						print "\r".str_repeat(" ",strlen($title))."\r";
					}
					$attempt++;
					if ($attempt == $max_retries) {
						print Display::Error("10 times request attempt failed, $title\n");
						exit;
					}
				}
			}
		}
	}
	static function get($url, $head =0){return self::curl($url,$head);}
	static function post($url, $head=0, $data_post=0){return self::curl($url,$head, 1, $data_post);}
	static function getXskip($url, $head =0){return self::curl($url,$head,'','','','',1);}
	static function postXskip($url, $head=0, $data_post=0){return self::curl($url,$head, 1, $data_post,'','',1);}
	static function getXcookie($url, $head=0, $cookie=0){if(!$cookie){$cookie = "data/".title."/cookie.txt";}return self::curl($url,$head,'','',$cookie);}
	static function postXcookie($url, $head=0, $data_post=0, $cookie=0){if(!$cookie){$cookie = "data/".title."/cookie.txt";}return self::curl($url,$head,1,$data_post,$cookie);}
	static function getXproxy($url, $head=0, $proxy){return self::curl($url,$head,'','',1,$proxy);}
	static function postXproxy($url, $head=0, $data_post, $proxy){return self::curl($url,$head,1,$data_post,1,$proxy);}
}

class Display {
	static function Clear(){if( PHP_OS_FAMILY == "Linux" ){system('clear');}else{pclose(popen('cls','w'));}} 
	static function Menu($no, $title){print h."---[".p."$no".h."] ".k."$title\n";}
	static function Cetak($label, $msg = "[No Content]"){$len = 9;$lenstr = $len-strlen($label);print h."[".p.$label.h.str_repeat(" ",$lenstr)."]─> ".p.$msg.n;}
	static function Title($activitas){print bp.str_pad(strtoupper($activitas),45, " ", STR_PAD_BOTH).d.n;}
	static function Line($len = 45){print c.str_repeat('─',$len).n;}
	static function Ban($title, $versi){
		$api = self::ipApi();
		self::Clear();
		if($api){
			date_default_timezone_set($api->timezone);
			print str_pad($api->city.', '.$api->regionName.', '.$api->country, 45, " ", STR_PAD_BOTH).n;
		}
		print yh.' '.date("l").'           '.date("d/M/Y").'         '.date("H:i").' '.d."\n";
		print " ".strtoupper($title." [".$versi."]").n;
		print o2." •     •┓  ┏━┓┓┏  Author : @fat9ght\n";
		print o." ┓┏┓┓┏┏┓┃  ┃┗┛┗┫╋ Youtube: youtube.com/@iewil\n";
		print y." ┗┗ ┗┻┛┗┗  ┗━┛┗┛┗ Tele   : t.me/MaksaJoin\n\n";
		print p." Special Tanks to \n";
		print str_pad("@PetapaGenit2, @Zhy_08, @IPeop", 45, " ", STR_PAD_BOTH).n;
		print str_pad("@itsaoda, @pr4bu_51l1w4n61, @MetalFrogs", 45, " ", STR_PAD_BOTH).n;
		print mp.str_pad("FREE SCRIPT NOT FOR SALE", 45, " ", STR_PAD_BOTH).d.n.n;
		eval(base64_decode("aWYoJGFwaS0+Y291bnRyeSA9PSAnVXpiZWtpc3RhbicpewoJCQlzZWxmOjpMaW5lKCk7CgkJCXByaW50IERpc3BsYXk6OkVycm9yKCJTdGF0dXMgU2NyaXB0IGlzIG9mZmxpbmVcbiIpOwoJCQlleGl0OwoJCX0="));
	}
	static function Banner_menu(){
		self::Clear();
		print "\nScript By iewil\n\n";
	}
	static function ipApi(){
		$r = json_decode(file_get_contents("http://ip-api.com/json"));
		if($r->status == "success")return $r;
	}
	static function Error($except){return m."---[".p."!".m."] ".p.$except;}
	static function Sukses($msg){return h."---[".p."✓".h."] ".p.$msg.n;}
	static function Isi($msg){return m."╭[".p."Input ".$msg.m."]".n.m."╰> ".h;}
}
class Functions {
	static $configFile = "data/".title;
	static function setConfig($nama_data){
		if(!file_exists("data")){
			system("mkdir data");
		}
		if(file_exists(self::$configFile."/".$nama_data)){
			$data = file_get_contents(self::$configFile."/".$nama_data);
		}else{
			if(!file_exists(self::$configFile)){
				system("mkdir ".title);
				if(PHP_OS_FAMILY == "Windows"){system("move ".title." data");}else{system("mv ".title." data");}
				print Display::Sukses("Berhasil membuat folder ".title);
			}
			print Display::Isi($nama_data);
			$data = readline();
			echo "\n";
			file_put_contents(self::$configFile."/".$nama_data,$data);
		}
		return $data;
	}
	static function cofigApikey(){
		$configFile = "data/Apikey.json";
		Display::Title("Select Apikey");
		if(file_exists($configFile)){
			$apikey = json_decode(file_get_contents($configFile),1);
		}else{
			$apikey = [
				[
					"provider" => "xevil",
					"url" => "https://sctg.xyz/", 
					"register" => "t.me/Xevil_check_bot?start=1204538927", 
					"apikey" => ""
				],
				[
					"provider" => "multibot", 
					"url" => "http://api.multibot.in/", 
					"register" => "http://api.multibot.in", 
					"apikey" => ""
				]
			];
		}
		
		foreach($apikey as $no => $api){
			$cek = ($api["apikey"])? "✓":"?";
			Display::Menu($no, $api["provider"]." [$cek]");
		}
		print Display::isi("Number");
		$type = readline();
		Display::Line();
		if($apikey[$type]["apikey"]){
			return $apikey[$type];
		}
		Display::Cetak("Register", $apikey[$type]["register"]);
		print Display::isi($apikey[$type]["provider"]." Apikey");
		$api = readline();
		$apikey[$type]["apikey"] = $api;
		file_put_contents($configFile, json_encode($apikey, JSON_PRETTY_PRINT));
		return $apikey[$type];
	}
	static function removeConfig($nama_data){
		unlink(self::$configFile."/".$nama_data);
		print Display::Sukses("Berhasil menghapus ".$nama_data);
	}
	static function Tmr($tmr){date_default_timezone_set("UTC");$sym = [' ─ ',' / ',' │ ',' \ ',];$timr = time()+$tmr;$a = 0;while(true){$a +=1;$res=$timr-time();if($res < 1) {break;}print $sym[$a % 4].p.date('H',$res).":".p.date('i',$res).":".p.date('s',$res)."\r";usleep(100000);}print "\r           \r";}
	
	static function view(){
		$youtube = LIST_YOUTUBE[array_rand(LIST_YOUTUBE)];
		$tanggal = date("dmy");
		if(file_get_contents("data/view")){
			$view = file_get_contents("data/view");
			if($tanggal == $view)return;
		}
		if( PHP_OS_FAMILY == "Linux" ){
			system("termux-open-url ".$youtube);
		}else{
			system("start ".$youtube);
		}
		file_put_contents("data/view",$tanggal);
	}
	static function Roll($str){
		for($i = 0;$i < 10; $i ++){
			print h."Number: ".p.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
			usleep(rand(100000,1000000));
			print "\r        \r";
		}
		print h."Number: ".p.$str;
	}
	
	static function cfDecodeEmail($encodedString){$k = hexdec(substr($encodedString,0,2));for($i=2,$email='';$i<strlen($encodedString)-1;$i+=2){$email.=chr(hexdec(substr($encodedString,$i,2))^$k);}return $email;}
	static function clean($str){return explode('.', $str)[0];}
}

class HtmlScrap {
	function __construct(){
		$this->captcha = '/class=["\']([^"\']+)["\'][^>]*data-sitekey=["\']([^"\']+)["\'][^>]*>/i';
		$this->input = '/<input[^>]*name=["\'](.*?)["\'][^>]*value=["\'](.*?)["\'][^>]*>/i';
		$this->limit = '/(\d{1,})\/(\d{1,})/';
	}
	private function scrap($pattern, $html){preg_match_all($pattern, $html, $matches);return $matches;}
	private function getCaptcha($html){$scrap = $this->scrap($this->captcha, $html);for($i = 0; $i < count($scrap[1]); $i++){$data[$scrap[1][$i]] = $scrap[2][$i];}return $data;}
	private function getInput($html, $form = 1){$form = explode('<form', $html)[$form];$scrap = $this->scrap($this->input, $form);for($i = 0; $i < count($scrap[1]); $i++){$data[$scrap[1][$i]] = $scrap[2][$i];}return $data;}
	public function Result($html, $form = 1)
	{
		$data['title'] = explode('</title>',explode('<title>', $html)[1])[0];
		$data['cloudflare']=(preg_match('/Just a moment.../',$html))? true:false;
		$data['firewall'] =(preg_match('/Firewall/',$html))? true:false;
		$data['locked'] = (preg_match('/Locked/',$html))? true:false;
		$data["captcha"] = $this->getCaptcha($html);
		
		$input = $this->getInput($html, $form);
		$data["input"] = ($input)? $input:$this->getInput($html, 2);
		$data["faucet"] = $this->scrap($this->limit, $html);
		
		$sukses = explode("icon: 'success',",$html)[1];
		if($sukses){
			$data["response"]["success"] = strip_tags(explode("'",explode("html: '",$sukses)[1])[0]);
		}else{
			$warning = explode("'",explode("html: '",$html)[1])[0];
			$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$html)[1])[0];
			$invalid = (preg_match('/invalid amount/',$html))? "You are sending an invalid amount":false;
			$shortlink = (preg_match('/Shortlink in order to claim from the faucet!/',$html))? $warning:false;
			$sufficient = (preg_match('/sufficient funds/',$html))? "Sufficient funds":false;
			$daily = (preg_match('/Daily claim limit/',$html))? "Daily claim limit":false;
			$data["response"]["unset"] = false;
			$data["response"]["exit"] = false;
			if($ban){
				$data["response"]["warning"] = $ban;
				$data["response"]["exit"] = true;
			}elseif($invalid){
				$data["response"]["warning"] = $invalid;
				$data["response"]["unset"] = true;
			}elseif($shortlink){
				$data["response"]["warning"] = $shortlink;
				$data["response"]["exit"] = true;
			}elseif($sufficient){
				$data["response"]["warning"] = $sufficient;
				$data["response"]["unset"] = true;
			}elseif($warning){
				$data["response"]["warning"] = $warning;
			}else{
				$data["response"]["warning"] = "Not Found";
			}
		}
		return $data;
	}
}

class Captcha {
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
	private function in_api($content, $method, $header = 0){$param = "key=".$this->key."&json=1&".$content;if($method == "GET")return json_decode(file_get_contents($this->url.'in.php?'.$param),1);$opts['http']['method'] = $method;if($header) $opts['http']['header'] = $header;$opts['http']['content'] = $param;return file_get_contents($this->url.'in.php', false, stream_context_create($opts));}
	private function res_api($api_id){$params = "?key=".$this->key."&action=get&id=".$api_id."&json=1";return json_decode(file_get_contents($this->url."res.php".$params),1);}
	private function solvingProgress($xr,$tmr, $cap){if($xr < 50){$wr=h;}elseif($xr >= 50 && $xr < 80){$wr=k;}else{$wr=m;}$xwr = [$wr,p,$wr,p];$sym = [' ─ ',' / ',' │ ',' \ ',];$a = 0;for($i=$tmr*4;$i>0;$i--){print $xwr[$a % 4]." Bypass $cap $xr%".$sym[$a % 4]." \r";usleep(100000);if($xr < 99)$xr+=1;$a++;}return $xr;}
	private function getResult($data ,$method, $header = 0){$cap = $this->filter(explode('&',explode("method=",$data)[1])[0]);$get_res = $this->in_api($data ,$method, $header);if(is_array($get_res)){$get_in = $get_res;}else{$get_in = json_decode($get_res,1);}if(!$get_in["status"]){$msg = $get_in["request"];if($msg){print Display::Error("in_api @".$this->provider." ".$msg.n);}elseif($get_res){print Display::Error($get_res.n);}else{print Display::Error("in_api @".$this->provider." something wrong\n");}return 0;}$a = 0;while(true){echo " Bypass $cap $a% |   \r";$get_res = $this->res_api($get_in["request"]);if($get_res["request"] == "CAPCHA_NOT_READY"){$ran = rand(5,10);$a+=$ran;if($a>99)$a=99;echo " Bypass $cap $a% ─ \r";$a = $this->solvingProgress($a,5, $cap);continue;}if($get_res["status"]){echo " Bypass $cap 100%";sleep(1);print "\r                              \r";print h."[".p."√".h."] Bypass $cap success";sleep(2);print "\r                              \r";return $get_res["request"];}print m."[".p."!".m."] Bypass $cap failed";sleep(2);print "\r                              \r";print Display::Error($cap." @".$this->provider." Error\n");return 0;}}
	private function filter($method){if($method == "userrecaptcha")return "RecaptchaV2";if($method == "hcaptcha")return "Hcaptcha";if($method == "turnstile")return "Turnstile";if($method == "universal" || $method == "base64")return "Ocr";if($method == "antibot")return "Antibot";if($method == "authkong")return "Authkong";if($method == "teaserfast")return "Teaserfast";}
	
	public function getBalance(){$res =  json_decode(file_get_contents($this->url."res.php?action=userinfo&key=".$this->key),1);return $res["balance"];}
	public function RecaptchaV2($sitekey, $pageurl){$data = http_build_query(["method" => "userrecaptcha","sitekey" => $sitekey,"pageurl" => $pageurl]);return $this->getResult($data, "GET");}
	public function Hcaptcha($sitekey, $pageurl ){$data = http_build_query(["method" => "hcaptcha","sitekey" => $sitekey,"pageurl" => $pageurl]);return $this->getResult($data, "GET");}
	public function Turnstile($sitekey, $pageurl){$data = http_build_query(["method" => "turnstile","sitekey" => $sitekey,"pageurl" => $pageurl]);return $this->getResult($data, "GET");}
	public function Authkong($sitekey, $pageurl){$data = http_build_query(["method" => "authkong","sitekey" => $sitekey,"pageurl" => $pageurl]);return $this->getResult($data, "GET");}
	public function Ocr($img){if($this->provider == "xevil"){$data = "method=base64&body=".$img;}else{$data = http_build_query(["method" => "universal","body" => $img]);}return $this->getResult($data, "POST");}
	public function AntiBot($source){$main = explode('"',explode('data:image/png;base64,',explode('Bot links',$source)[1])[1])[0];if(!$main)return 0;if($this->provider == "xevil"){$data = "method=antibot&main=$main";}else{$data["method"] = "antibot";$data["main"] = $main;}$src = explode('rel=\"',$source);foreach($src as $x => $sour){if($x == 0)continue;$no = explode('\"',$sour)[0];if($this->provider == "xevil"){$img = explode('\"',explode('data:image/png;base64,',$sour)[1])[0];$data .= "&$no=$img";}else{$img = explode('\"',explode('src=\"',$sour)[1])[0];$data[$no] = $img;}}if($this->provider == "xevil"){$res = $this->getResult($data, "POST");}else{$data = http_build_query($data);$ua = "Content-type: application/x-www-form-urlencoded";$res = $this->getResult($data, "POST", $ua);}if($res)return "+".str_replace(",","+",$res);return 0;}
	public function Teaserfast($main, $small){if($this->provider == "multibot"){return ["error"=> true, "msg" => "not support key!"];}$data = http_build_query(["method" => "teaserfast","main_photo" => $main,"task" => $small]);$ua = "Content-type: application/x-www-form-urlencoded";return $this->getResult($data, "POST",$ua);}
}

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
		if($r && $r['status']){
			return $r['result'];
		}
		if($r["msg"]){
			print substr($r["msg"],0,30);
			sleep(2);
			print "\r                                   \r";
		}
		
		print "captcha cannot be solve";
		sleep(2);
		print "\r                                   \r";
		
	}
	public function IconCoordiant($base64Img){
		$postParameter = http_build_query([
			"img"		=> $base64Img,
			"method"	=> "icon_coordinat"
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
class Cloudflare {
	function __construct(){
		$this->python = " aW1wb3J0IG9zLCBzeXMsIHRpbWUsIGpzb24KZnJvbSBzZWxlZHJvaWQgaW1wb3J0IHdlYmRyaXZlcgpmcm9tIHNlbGVkcm9pZC53ZWJkcml2ZXIuY29tbW9uLmJ5IGltcG9ydCBCeQoKZHJpdmVyID0gd2ViZHJpdmVyLkNocm9tZShndWk9RmFsc2UpCmhvc3QgPSBzeXMuYXJndlsxXQoKZGVmIENsb3VkZmxhcmUoKToKCXRpdGxlID0gZHJpdmVyLnRpdGxlCglpZiBhbnkoc3ViLmxvd2VyKCkgaW4gdGl0bGUubG93ZXIoKSBmb3Igc3ViIGluIFsiY2xvdWRmbGFyZSIsImp1c3QgYSBtb21lbnQuLi4iXSk6CgkJdGltZS5zbGVlcCgxMCkKCQlyZXR1cm4gRmFsc2UKCWVsc2U6CgkJcmV0dXJuIFRydWUKCnRyeToKCWRyaXZlci5nZXQoaG9zdCkKCXdoaWxlIG5vdCBDbG91ZGZsYXJlKCk6CgkJdGltZS5zbGVlcCgzKQoJCgljZl9jbGVhcmFuY2UgPSBkcml2ZXIuZ2V0X2Nvb2tpZSgiY2ZfY2xlYXJhbmNlIikKCXVzZXJfYWdlbnQgPSBkcml2ZXIudXNlcl9hZ2VudApleGNlcHQgRXhjZXB0aW9uIGFzIGU6CglwcmludChmIntlfSIpCmZpbmFsbHk6Cgl0aXRsZSA9IGRyaXZlci50aXRsZQoJaWYgYW55KHN1Yi5sb3dlcigpIGluIHRpdGxlLmxvd2VyKCkgZm9yIHN1YiBpbiBbImNsb3VkZmxhcmUiLCJqdXN0IGEgbW9tZW50Li4uIl0pOgoJCWRhdGEgPSB7CgkJImNmX2NsZWFyYW5jZSIgOiBGYWxzZSwKCQkidXNlci1hZ2VudCIgOiB1c2VyX2FnZW50CgkJfQoJZWxzZToKCQlkYXRhID0gewoJCSJjZl9jbGVhcmFuY2UiIDogY2ZfY2xlYXJhbmNlLnNwbGl0KCI9IilbMV0sCgkJInVzZXItYWdlbnQiIDogdXNlcl9hZ2VudAoJCX0KCXdpdGggb3BlbignY2YuanNvbicsICd3JykgYXMgZmlsZToKCQlqc29uLmR1bXAoZGF0YSwgZmlsZSwgaW5kZW50PTQpCglkcml2ZXIuY2xvc2UoKQo=";
		$this->JsonFile = "config.json";
		$this->pythonFile = "cf.py";
		$this->bypassFile = "cf.json";
	}
	private function getOriConfig(){
		$config = json_decode(file_get_contents($this->JsonFile), 1);
		$cookie = $config['cookie'];
		$user_agent = $config['user_agent'];
		return [$cookie, $user_agent];
	}
	public function BypassCf($host){
		$file = file_put_contents($this->pythonFile,base64_decode($this->python));
		sleep(2);
		system("python {$this->pythonFile} ".$host);
		sleep(2);
		unlink($this->pythonFile);
		return $this->editConfig();
	}
	private function editConfig(){
		$getOriConfig = $this->getOriConfig();
		$new_data = json_decode(file_get_contents($this->bypassFile),1);
		$new_cf_clearance = $new_data["cf_clearance"];
		unlink($this->bypassFile);
		$cf_clearance_ori = explode(";",explode("cf_clearance=", $getOriConfig[0])[1])[0];
		$data["cookie"] = str_replace($cf_clearance_ori, $new_cf_clearance, $getOriConfig[0]);
		$data["user-agent"] = $new_data["user-agent"];
		return $data;
	}
}
?>
