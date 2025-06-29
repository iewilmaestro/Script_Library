<?php
error_reporting(0);
const
versi = "0.0.1",
host = "https://top1faucet.site/",
refflink = "https://top1faucet.site/?r=705",
youtube = "https://youtube.com/@iewil";


class Bot {
	private $cookie, $uagent, $coins; 
	function __construct(){
		$this->coins = "";
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		$this->scrap = new HtmlScrap();
		$this->captcha = new Captcha();
		Display::Ban(title, versi);
		$r = $this->Dashboard();
		if(!$r['user']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Username", $r['user']);
		$this->kiriKanan($r['level'], $r['need']);
		$this->displayProgressBar($r['progres']);
		$this->kiriKanan($r['bonus'], $r['bonus_level']);
		Display::Line();
		
		$this->Claim();
	}
	private function displayProgressBar($percentage, $length = 45) {
		$progressLength = round($percentage / 100 * $length);
		$progressNow = "\033[48;5;32m".str_repeat(" ", $progressLength);
		$progressBar = "\033[48;5;15m".str_repeat(" ", $length-$progressLength);
		
		echo $progressNow.$progressBar."\033[0m".$percentage. "%\n";
	}
	private function kiriKanan($kiri, $kanan){
		$length = 45;
		$lenkiri = strlen($kiri);
		$lenkanan = strlen($kanan);
		$spasi = $length - $lenkiri -$lenkanan -2;
		print c." ".$kiri.str_repeat(" ", $spasi).u.$kanan."\n";
	}
	private function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function Dashboard(){
		ulang:
		$r = Requests::get(host."app/dashboard",$this->headers())[1];
		if(preg_match('/Locked/',$r)){
			print Display::Error("temporary locked\n");
			Display::Line();
			preg_match('/<b id=minute>(\d+)<\/b>:(<b id=second>(\d+)<\/b>)/', $r, $matches);
			if (isset($matches[1]) && isset($matches[3])) {
				$minute = $matches[1];
				$second = $matches[3];
				$tmr = ($minute * 60) + $second;
				Functions::Tmr($tmr+5);
				goto ulang;
			}
		}
		$data['user'] = explode('</h6>',explode('<h6 class="mb-0">', $r)[1])[0];
		if(!$data['user']){
			$data['user'] = explode('</h6>',explode('<h6 class=mb-0>', $r)[1])[0];
		}
		$data['level'] = explode('</span>',explode('<span class="pc-badge badge bg-light-primary mt-text">', $r)[1])[0];//0 lvl
        $data['need'] = explode('</span>',explode('<span class="pc-badge badge bg-light-warning mt-text">', $r)[1])[0];//98 need exp
		preg_match('/style="width: ([0-9]+)%"/', $r, $matchesWidth);
		$width = isset($matchesWidth[1]) ? $matchesWidth[1] : 'Unknown';
		$data['progres'] = $width;
		$data['bonus'] = explode('</span>',explode('<span class="pc-badge badge bg-light-success mt-text">', $r)[1])[0];//0% faucet bonus
        $data['bonus_level'] = explode('</span>',explode('<span class="pc-badge badge bg-light-secondary mt-text">', $r)[1])[0];//0.001% lvl bonus
		return $data;
	}
	private function Claim(){
		if(!$this->coins){
			$r = Requests::get(host."app/dashboard",$this->headers())[1];
			preg_match_all('#https?:\/\/top1faucet.site\/app\/faucet\?currency=([a-zA-Z0-9]+)#', $r, $matches);
			$this->coins = $matches[1];
		}
		$success = 0;
		while(true){
			foreach($this->coins as $a => $coin){
				$r = Requests::get(host."app/faucet?currency=".strtoupper($coin),$this->headers())[1];
				$scrap = $this->scrap->Result($r);
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					//$this->Firewall();
					//continue;
					exit;
				}
				if($scrap['cloudflare']){
					print Display::Error(host."app/faucet?currency=".$coin.n);
					print Display::Error("Cloudflare Detect\n");
					Display::Line();
					return 1;
				}
				if(preg_match('/Locked/',$r)){
					print Display::Error("temporary locked\n");
					Display::Line();
					preg_match('/<b id=["\']minute["\']>(\d+)<\/b>:(<b id=["\']second["\']>(\d+)<\/b>)/', $r, $matches);
					if (isset($matches[1]) && isset($matches[3])) {
						$minute = $matches[1];
						$second = $matches[3];
						$tmr = ($minute * 60) + $second;
						Functions::Tmr($tmr+5);
						continue;
					}
				}
				
				preg_match('/<b id=["\']minute["\']>(\d+)<\/b>:(<b id=["\']second["\']>(\d+)<\/b>)/', $r, $matches);
				if (isset($matches[1]) && isset($matches[3])) {
					$minute = $matches[1];
					$second = $matches[3];
					$tmr = ($minute * 60) + $second;
					Functions::Tmr($tmr+5);
					continue;
				}
				$timer = explode('-',explode('var wait = ', $r)[1])[0];
				if($timer){
					Functions::Tmr($timer+5);
					continue;
				}
				if(!preg_match('/Ready/',$r)){
					print_r($r);exit;
					continue;
				}
				if(explode('rel=\"',$r)[1]){
					$antibot = $this->captcha->Antibot($r);
					if(!$antibot)continue;
				}
				if($timer){Functions::Tmr($timer);continue;}
				if(preg_match("/You don't have enough energy for Auto Faucet!/",$r)){exit(Error("You don't have enough energy for Auto Faucet!\n"));}
				if(preg_match('/Daily claim limit/',$r) || $scrap['faucet'][1][0] < 1){
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
				if($scrap['input']['_iconcaptcha-token']){
					$icon = FreeCaptcha::iconBypass($scrap['input']['_iconcaptcha-token'], $this->headers());
					if(!$data)continue;
					$data['ci_csrf_token'] = '';
				}
				if($antibot){
					$data['antibotlinks'] = $antibot;
				}
				if(!$data){
					print Display::Error("Data not found");
					sleep(3);
					print "\r                              \r";
					continue;
				}
				$data = http_build_query($data);
				$r = Requests::post(host."app/faucet/verify?currency=".strtoupper($coin),$this->headers(), $data)[1];
				$wr = explode('</div>',explode('<i class="fas fa-exclamation-circle"></i> ', $r)[1])[0];//Invalid Anti-Bot Links
				$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
				$ss = explode("title: 'Great!',",$r)[1];
				if(preg_match('/Locked/',$r)){
					print Display::Error("temporary locked\n");
					Display::Line();
					preg_match('/<b id=["\']minute["\']>(\d+)<\/b>:(<b id=["\']second["\']>(\d+)<\/b>)/', $r, $matches);
					if (isset($matches[1]) && isset($matches[3])) {
						$minute = $matches[1];
						$second = $matches[3];
						$tmr = ($minute * 60) + $second;
						Functions::Tmr($tmr+5);
						continue;
					}
				}
				if($ban){
					print Display::Error("Your account".$ban.n);
					exit;
				}
				if(preg_match('/invalid amount/',$r)){
					unset($this->coins[$a]);
					print Display::Error("You are sending an invalid amount of payment to the user\n");
					Display::Line();
					continue;
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
					print Display::Sukses(str_replace('has been','',explode("account",explode("text: '",$ss)[1])[0]));
					Display::Line();
					$success++;
				}elseif($wr){
					print Display::Error(substr($wr,0,30));
					sleep(3);
					print "\r                                   \r";
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
		}
	}
	private function widgetId() {
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
	private function iconBypass($token){
		$icon_header = $this->headers();
		$icon_header[] = "origin: ".host;
		$icon_header[] = "x-iconcaptcha-token: ".$token;
		$icon_header[] = "x-requested-with: XMLHttpRequest";
		
		$timestamp = round(microtime(true) * 1000);
		$initTimestamp = $timestamp - 2000;
		$widgetID = $this->widgetId();
		
		$data = ["payload" => 
			base64_encode(json_encode([
				"widgetId"	=> $widgetID,
				"action" 	=> "LOAD",
				"theme" 	=> "light",
				"token" 	=> $token,
				"timestamp"	=> $timestamp,
				"initTimestamp"	=> $initTimestamp
			]))
		];
		$r = json_decode(base64_decode(Requests::post(host."icaptcha/req",$icon_header, $data)[1]),1);
		$base64Image = $r["challenge"];
		$challengeId = $r["identifier"];
		$cap = $this->iewil->IconCoordiant($base64Image);
		if(!$cap['x'])return;
		
		$timestamp = round(microtime(true) * 1000);
		$initTimestamp = $timestamp - 2000;
		$data = ["payload" => 
			base64_encode(json_encode([
				"widgetId"		=> $widgetID,
				"challengeId"	=> $challengeId,
				"action"		=> "SELECTION",
				"x"				=> $cap['x'],
				"y"				=> 24,
				"width"			=> 320,
				"token" 		=> $token,
				"timestamp"		=> $timestamp,
				"initTimestamp"	=> $initTimestamp
			]))
		];
		$r = json_decode(base64_decode(Requests::post(host."icaptcha/req",$icon_header, $data)[1]),1);
		if(!$r['completed']){
			return;
		}
		$data = [];
		$data['captcha'] = "icaptcha";
		$data['_iconcaptcha-token']=$token;
		$data['ic-rq']=1;
		$data['ic-wid'] = $widgetID;
		$data['ic-cid'] = $challengeId;
		$data['ic-hp'] = '';
		return $data;
	}
}

new Bot();