<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://nodego.ai/",
siguplink = "https://app.nodego.ai/r/NODE2726ACC86960",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		$this->ipApi = Display::ipApi();
		cookie:
		Display::Cetak("Register",siguplink);
		Display::Line();
		
		$this->autoriz = Functions::setConfig("autorization");
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		Display::Ban(title, versi);
		
		$r = $this->dashboard();
		if(!$r["username"]){
			print Display::Error("Autorization expired\n");
			Functions::removeConfig("autorization");
			Display::Line();
			goto cookie;
		}
		Display::Cetak("Username",$r["username"]);
		Display::Cetak("Balance",$r["balance"]);
		Display::Cetak("Node",count($r["aktif"]));
		Display::Line();
		
		$this->claim();
	}
	private function headers($data=0){
		$h[] = "content-type: application/json";
		$h[] = "user-agent: ".$this->uagent;
		$h[] = "authorization: ".$this->autoriz;
		return $h;
	}
	private function dashboard(){
		$r = json_decode(Requests::get(host."api/user/me",$this->headers())[1],1);
		$data["username"] = $r["metadata"]["username"];
		$data["balance"] = $r["metadata"]["rewardPoint"];
		foreach($r["metadata"]["nodes"] as $a => $bal){
			$data["balance"] = $data["balance"] +$bal["totalPoint"];
			if($bal["isActive"]){
				$data["aktif"][$a]["id"] = $bal["type"];
			}
		}
		return $data;
	}
	private function claim(){
		$a = 0;
		while(true){
			date_default_timezone_set($this->ipApi->timezone);
			$r = Requests::get("https://api.bigdatacloud.net/data/client-ip", ["user-agent: ".$this->uagent])[1];
			if($a % 4 == 0){
				$r = json_decode(Requests::post(host."api/user/nodes/ping", $this->headers(), '{"type":"extension"}')[1],1);
				$statuscode = $r["statusCode"];
				$code = ($statuscode == 200)?h:m;
				if($statuscode == 200){
					$das = $this->dashboard();
					print h."---[".p.date("d/m/Y H:i:s").h."]".$code."[$statuscode]".k.$das["balance"]." ".o."(".count($das["aktif"]).")\n";
				}
				$a++;
				Functions::Tmr(30);
				continue;
			}
			if($a % 2 == 0 && $statuscode == 429){
				$r = json_decode(Requests::post(host."api/user/nodes/ping", $this->headers(), '{"type":"extension"}')[1],1);
				$statuscode = $r["statusCode"];
				$code = ($statuscode == 200)?h:m;
				if($statuscode == 200){
					$das = $this->dashboard();
					print h."---[".p.date("d/m/Y H:i:s").h."]".$code."[$statuscode]".k.$das["balance"]." ".o."(".count($das["aktif"]).")\n";
				}
			}
			$a++;
			Functions::Tmr(30);
		}
	}
}

new Bot();