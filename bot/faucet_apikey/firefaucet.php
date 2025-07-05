<?php
error_reporting(0);
const
versi = "0.0.1",
host = "https://firefaucet.win",
refflink = "https://firefaucet.win/ref/1258480",
youtube = "https://youtube.com/@iewil";

class Bot {
	public string $host;

    private string $cookie, $ua;
    private bool $cookieSet = false, $uaSet = false;
	
	public function __construct()
	{
		$this->scrap = new HtmlScrap();
		
	}
    public function setCookie(string $cookie): void {
        $this->cookie = $cookie;
        $this->cookieSet = true;
    }

    public function setUserAgent(string $ua): void {
        $this->ua = $ua;
        $this->uaSet = true;
    }

    private function ensureReady(): void {
        if (!$this->cookieSet || !$this->uaSet) {
            exit("[ERROR] Cookie dan User-Agent harus di-set terlebih dahulu.\nGunakan setCookie() dan setUserAgent().\n");
        }
    }

    private function buildGlobalHeaders(): array {
        return [
            "Host: " . parse_url($this->host, PHP_URL_HOST),
			"x-requested-with: XMLHttpRequest",
			"accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
			"content-type: application/x-www-form-urlencoded",
			"accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7",
			"referer: https://firefaucet.win/",
            "cookie: " . $this->cookie,
            "user-agent: " . $this->ua
        ];
    }

    private function mergeHeaders(array $headers): array {
        $global = $this->buildGlobalHeaders();
        $mergedAssoc = [];

        foreach (array_merge($global, $headers) as $header) {
            [$key, $value] = explode(":", $header, 2);
            $mergedAssoc[strtolower(trim($key))] = trim($value);
        }

        $final = [];
        foreach ($mergedAssoc as $key => $value) {
            $final[] = "$key: $value";
        }

        return $final;
    }

    private function requests(string $method, string $endpoint, array $headers, $data = null) {
        $this->ensureReady();

        $url = $this->host . $endpoint;
        $method = strtoupper($method);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RESOLVE, [
            "firefaucet.win:443:104.26.0.221",
			"firefaucet.win:443:172.67.75.144",
			"firefaucet.win:443:104.26.1.221"
        ]);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        $headers = $this->mergeHeaders($headers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            print_r(['error' => $err]);
            exit;
        }

        return ["httpCode" => $httpCode, "body" => $response];
    }

    public function getDashboard() {
        $r = $this->requests("GET", '/', [])["body"];
		$data['user'] = Functions::Mid($r, '<span class="username-text">','</span>');
		$data['acp'] = strip_tags(trim(Functions::Mid($r, '<div style="color:#00a8ff;font-size:3.56rem;text-shadow:1px 2px 2px #1d202b;margin-bottom:10px">','</div>')));
		$data['body'] = $r;
		return $data;
    }
	public function balance(){
		$r = $this->requests("GET", '/balance', [])["body"];
		$x = explode('-usd-balance">',$r);
		foreach($x as $a => $con){
			if($a == 0)continue;
			$bal = strip_tags(explode(' <i class="bi bi-info-circle tooltipped"',$con)[0]);
			print str_replace(" ~","/",$bal).n;
		}
	}
	public function getFaucet()
	{
		$r = $this->requests("GET", '/faucet', [])["body"];
		if(preg_match('/Please come back/',$r)){
			return;
		}
		return $this->scrap->Result($r);
	}
	public function verifFaucet($data)
	{
		$data = http_build_query($data);
		$this->requests("POST", '/faucet', [], $data);
		return $this->requests("GET", '/faucet', [])["body"];
	}
	public function getPtc()
	{
		return $this->requests("GET", '/ptc', [])["body"];
	}
	public function viewPtc($id)
	{
		return $this->requests("GET", '/viewptc?id='.$id, [])["body"];
	}
	public function verifPtc($data, $key, $id)
	{
		$data = http_build_query($data);
		$this->requests("POST", '/ptcverify?key='.$key.'&id='.$id, [], $data)["body"];
		return $this->requests("GET", '/ptc', [])["body"];
	}
	public function Autoclaim($data)
	{
		return $this->requests("POST", '/start', [], $data)["body"];
	}
	public function Payout()
	{
		return json_decode($this->requests("GET", '/internal-api/payout/', [])["body"],1);
	}
}

$api = new Bot();
$api->host = host;

Display::Ban(title, versi);
cookie:
Display::Cetak("Register",refflink);
$cookie = Functions::setConfig("cookie");
$uagent = Functions::setConfig("user_agent");
$captcha = new Captcha();
$tesseract = new Tesseract(title);

$api->setCookie($cookie);
$api->setUserAgent($uagent);

$r = $api->getDashboard();
if(!$r['user']){
	Functions::removeConfig("cookie");
	Functions::removeConfig("user_agent");
	Display::Error("Cookie Expired\n");
	Display::Line();
	goto cookie;
}

Display::Ban(title, versi);
Display::Cetak("username",$r['user']);
Display::Cetak("acp",$r['acp']);
Display::Line();
$api->balance();
Display::Line();
menu:
Display::Menu(1, "Faucet");
Display::Menu(2, "Visit Ptc");
Display::Menu(3, "Auto Faucet");
print Display::Isi("Number");
$pil = readline();
Display::Line();

if($pil == 1){
	while(true){
		$getfaucet = $api->getFaucet();
		if(!$getfaucet){
			$r = $api->getDashboard()['body'];
			$getTimer = explode('wait', $r)[0];
			//print_r($getTimer);exit;
			preg_match_all("/<script>.*?(\[.*?\]);.*?<\/script>/", $r, $match);
			foreach($match[1] as $array){
				if(preg_match('/floor/', $array)){
					$matches = $array;
					break;
				}
			}
			$buildJson = str_replace(['[',']'], ["',",",'",], $matches);
			$array = explode("','", $buildJson);		
			$timer = null;
			if(is_array($array)){
				foreach ($array as $val) {
					if (is_numeric($val) && strpos($val, '.') !== false) {
						$timer = ceil((float)$val);
						break;
					}
				}
				Functions::tmr($timer);
				continue;
			}else{
				Functions::tmr(1800);
				continue;
			}
		}
		$data = $getfaucet['input'];
		Display::Cetak("Captcha",strtoupper($data["selected-captcha"]));
		if($data["selected-captcha"] == "turnstile"){
			$cap = $captcha->Turnstile("0x4AAAAAAAEUvFih09RuyAna", host);
			if(!$cap)continue;
			$data["cf-turnstile-response"] = $cap;
		}elseif($data["selected-captcha"] == "hcaptcha"){
			$cap = $captcha->Hcaptcha("034eb992-02f4-4cd7-8f90-5dfb05fb21a2", host);
			if(!$cap)continue;
			$data["h-captcha-response"] = $cap;
		}elseif($data["selected-captcha"] == 'recaptcha'){
			$cap = $captcha->RecaptchaV2("6LcLRHMUAAAAAImKcp7V9dcmD3ILWPEBJjlFnnrB", host);
			if(!$cap)continue;
			$data["g-recaptcha-response"] = $cap;
		}elseif($data["selected-captcha"] == 'solvemedia'){
			sleep(5);
			continue;
		}else{
			print Display::Error("No Captcha Detect\n");
			sleep(5);
			continue;
		}
		$verifFaucet = $api->verifFaucet($data);
		$wr = explode('</div>',explode('<div class="error_msg hoverable">',$verifFaucet)[1])[0];
		$ss = strip_tags(explode('</div>',explode('<div class="success_msg hoverable"><b>',$verifFaucet)[1])[0]);
		if($ss){
			print Display::Sukses($ss);
			Display::Cetak("Acp",$api->getDashboard()["acp"]);
			Display::Cetak("Apikey",$captcha->getBalance());
			print Display::line();
		}else{
			Display::Error($wr.n);
			print Display::line();
		}
	}
}elseif($pil == 2){
	while(true){
		$r = $api->getPtc();
		$id = explode("']",explode("_0x727d=['",$r)[1])[0];
		if(!$id){
			$id = trim(explode('"', explode('new_ptc("',$r)[1])[0]);
			if(!$id){
				Display::Error("Ptc Habis\n");
				Display::line();
				goto menu;
			}
		}
		$r = $api->viewPtc($id);
		$csrf = explode('"',explode('name="csrf_token" value="',$r)[1])[0];
		$key = explode("')",explode("onclick=continueptc('",$r)[1])[0];
		$img = explode("'>",explode("<img src='data:image/png;base64, ",$r)[1])[0];
		$tmr = explode("')",explode("parseInt('",$r)[1])[0];
		if($tmr){Functions::tmr($tmr);}
		$cap = $tesseract->Firefaucet($img);
		if(!$cap)continue;
			
		$data = ["captcha"=>$cap,"csrf_token"=>$csrf];
		$r = $api->verifPtc($data, $key, $id);
		$ss = strip_tags(explode('</b>',explode('<div class="success_msg hoverable">',$r)[1])[0]);
		if($ss){
			Display::Sukses($ss);
			Display::Cetak("Acp",$api->getDashboard()["acp"]);
			//Display::Cetak("Apikey",$captcha->getBalance());
			Display::line();
		}
	}
}elseif($pil == 3){
	$curency = [];
	$r = $api->getDashboard()['body'];
	$csrf = explode('">', explode('<input type="hidden" name="csrf_token" value="', $r)[1])[0];
	$data = 'csrf_token='.$csrf;
	preg_match_all('/<input(.*?)value="([\w]+)"/is',$r,$coins);
	$a=0;
	foreach ($coins[2] as $a => $coin){
		Display::Menu($a, $coin);
		$curency[$a] = $coin;
	}
	print Display::Isi("Number");
	$pilih_coin = readline();
	print Display::line();
	$coin_pilih = explode(',',$pilih_coin);
	foreach($coin_pilih as $number){
		$data .= "&coins=".$curency[$number];
	}
	while(true)
	{
		$r = $api->Autoclaim($data);
		Functions::tmr(60);
		$r = $api->Payout();
		if($r["success"]==1){
			$coin = array_keys($r["logs"]);
			for($i=0;$i<count($coin);$i++){
				Display::Sukses("Success ".$r["logs"][$coin[$i]]." ".$coin[$i]);
			}
			Display::Cetak("Acp",$r["balance"]);
			Display::line();
		}else{
			Display::Error($r["message"].n);
			Display::line();
			goto menu;
		}
		if($r["time_left"] == "0 seconds"){
			Display::Error("Acp Mencapai batas terendah!\n");
			Display::line();
			goto menu;
		}
	}
}