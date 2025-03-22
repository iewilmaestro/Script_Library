<?php

const
versi = "0.0.1",
host = "https://api.farazfaucets.com/",
refflink = "https://farazfaucets.com?r=67166902af66d3cc2ec25059",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		$this->ipApi = Display::ipApi();
		$this->autorization = Functions::setConfig("Autorization");
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		$this->iewil = new Iewil();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		$r = $this->dashboard();
		if(!$r["username"]){
			print Display::Error("Autorization expired\n");
			Functions::removeConfig("Autorization");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Username",$r["username"]);
		Display::Line();
		$list_faucet = $this->getFaucet();
		foreach($list_faucet as $a => $coins){
			Display::Menu($a, $coins['name'] ." (".$coins['currentBalance'].")");
		}
		print Display::Isi("Nomor");
		$pil = readline();
		Display::Line();
		$_id = $list_faucet[$pil]['_id'];
		if($this->claim($_id, $pil)){
			Functions::removeConfig("Autorization");
			Display::Line();
			goto cookie;
		}
	}
	private function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		$h[] = "content-type: application/json";
		$h[] = "authorization: ".$this->autorization;
		$h[] = "referer: https://farazfaucets.com/app/faucet";
		return $h;
	}
	private function Dashboard(){
		return json_decode(Requests::post(host.'Dashboard/Get-Profile', $this->headers())[1],1);
	}
	private function getFaucet(){
		ulang:
		$r = json_decode(Requests::post(host.'Faucet/Get-Faucet', $this->headers())[1],1);
		if($r['waitTime']){
			Functions::Tmr($r["waitTime"]);
			goto ulang;
		}
		return $r['rewards'];
	}
	private function Antibot(){
		while(true){
			$r = json_decode(Requests::post(host.'Antibot/Check-Antibot', $this->headers())[1],1);
			if($r["msg"] == "You can start antibot challenge"){
				
			}else{
				print_r($r);exit;
			}
			$r = json_decode(Requests::post(host.'Antibot/Start-Challenge', $this->headers())[1],1);
			$gif = $r["antibot"]["challenge"];
			
			$cap = $this->iewil->AntibotGif($gif);
			if(!$cap)continue;
			sleep(3);
			$data = '{"antibotResponse":['.str_replace(",", ", ",$cap).']}';
			$r = json_decode(Requests::post(host.'Antibot/Verify-Challenge', $this->headers($data), $data)[1],1);
			if($r["verifiedResponse"])return $r["verifiedResponse"];
		}
	}
	private function claim($id, $num){
		$bal_awal = $this->getFaucet()[$num]['currentBalance'];
		$reward = $this->getFaucet()[$num]['reward'];
		$ticker = $this->getFaucet()[$num]['ticker'];
		while(true){
			$r = json_decode(Requests::post(host.'Faucet/Check-Wait-Time', $this->headers())[1],1);
			if($r["waitTime"]){
				Functions::Tmr($r["waitTime"]);
				continue;
			}
			
			$antibot = $this->Antibot();
			if(!$antibot)continue;
			$data = '{"currency":"'.$id.'","antibotVerifiedResponse":"'.$antibot.'"}';
			$r = json_decode(Requests::post(host.'Faucet/Claim-Faucet', $this->headers(), $data)[1],1);
			if($r["type"] == "success"){
				date_default_timezone_set($this->ipApi->timezone);
				Display::Cetak("Time", date("d/M/Y H:i:s"));
				print Display::Sukses($r['msg']);
				$bal_awal = $bal_awal+$reward;
				Display::Cetak("Balance", sprintf('%.8f',floatval($bal_awal." ". $ticker))." ". $ticker);
				Display::Line();
			}
		}
	}
}

new Bot();