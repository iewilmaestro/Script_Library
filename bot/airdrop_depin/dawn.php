<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://www.aeropres.in/",
siguplink = "https://dashboard.dawninternet.com/signup",
reffcode = "2cvunqs0",
extension = "https://chromewebstore.google.com/detail/dawn-validator-chrome-ext/fpdkjdnhkakefebpekbdhillbhonfjjp?pli=1",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		$this->ipApi = Display::ipApi();
		cookie:
		Display::Cetak("Register",siguplink);
		Display::Cetak("Reff code",reffcode);
		Display::Cetak("Extension",extension);
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
		$r = json_decode(Requests::get(host."api/atom/v1/userreferral/getpoint?appid=67cd9431f647519ec2fee9e6",$this->headers())[1],1);
		$data["username"] = $r["data"]["referralPoint"]["email"];
		$r = $r["data"]["rewardPoint"];
		$data["balance"] = $r["activeStreak"]+
			$r["points"] + 
			$r["registerpoints"] +
			$r["signinpoints"] +
			$r["twitter_x_id_points"] +
			$r["discordid_points"] +
			$r["telegramid_points"] +
			$r["bonus_points"];
		return $data;
	}
	private function claim(){
		while(true){
			$r = json_decode(Requests::get(host."api/atom/v1/userreferral/getpoint?appid=67cd9431f647519ec2fee9e6", $this->headers())[1],1);
			$username = $r["data"]["referralPoint"]["email"];
			if($r["status"] == 1){
				$r = $r["data"]["rewardPoint"];
				$reward = $r["activeStreak"]+
					$r["points"] + 
					$r["registerpoints"] +
					$r["signinpoints"] +
					$r["twitter_x_id_points"] +
					$r["discordid_points"] +
					$r["telegramid_points"] +
					$r["bonus_points"];
				
				date_default_timezone_set($this->ipApi->timezone);
				if(!$reward_awal){
					print h."---[".p.date("d/m/Y H:i:s").h."] ".k.$reward."\n";
					$reward_awal = $reward;
				}elseif($reward_awal == $reward){
					//print "[".date("d/m/Y H:i:s")."] ".$reward." (sama)\n";
				}else{
					print h."---[".p.date("d/m/Y H:i:s").h."] ".k.$reward."\n";
					$reward_awal = $reward;
				}
				
				Requests::post("https://www.aeropres.in/chromeapi/dawn/v1/userreward/keepalive?appid=67cd9431f647519ec2fee9e6", $this->headers(), '{"username":"'.$username.'","extensionid":"fpdkjdnhkakefebpekbdhillbhonfjjp","numberoftabs":0,"_v":"1.1.3"}')[1];
			}
			Functions::Tmr(300);
		}
	}
}

new Bot();