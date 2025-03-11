<?php

/*
if (!defined('title') || title == "") {
    define("title", "tronpayu");
    require "../../modul/class.php";
}
*/

const
versi = "0.0.1",
host = "https://tonrocket.com/",
refflink = "https://t.me/toncoin_rocket_bot/tonapp?startapp=tr_pixnS9oKdabW0HVKwEQ2",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		$this->config = "data/".title."/data.json";
		if(!file_exists("data/".title)){
			system("mkdir ".title);
			if(PHP_OS_FAMILY == "Windows"){system("move ".title." data");}else{system("mv ".title." data");}
			print Display::Sukses("Berhasil membuat folder ".title);
		}
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
		
		Functions::view();
		
		menu:
		Display::Menu(1, "Add Account");
		Display::Menu(2, "Run bot");
		print Display::Isi("Nomor");
		$pil = readline();
		Display::Line();
		
		if($pil == 1)$this->input_data_user();
		if($pil == 2)$this->run_bot();
	}
	private function input_data_user()
	{
		while(true){
			$array = [];
			$config = [];
			if(file_exists($this->config)){
				$config = json_decode(file_get_contents($this->config),1);
			}
			print k."Total account: ". count($config)."\n";
			Display::Line();
			print Display::Title("Add account");
			print Display::Isi("Cookie")."\t";
			$cookie = readline();
			print Display::Isi("User-Agent")."\t";
			$user_agent = readline();
			
			if(isset($cookie) && isset($user_agent)){
				//Cek
				$this->cookie = $cookie;
				$this->uagent = $user_agent;
				$dashboard = $this->Dashboard();
				if($dashboard['Username']){
					$username = $dashboard['Username'];
					foreach($config as $con){
						if($con['username'] == $username || 
						$con['cookie'] == $cookie
						){
							print Display::Error("The account has been saved previously\n");
							goto ngent;
						}
					}
					
					$array = [['username'=> $username, 'cookie' => $cookie, 'user-agent'=>$user_agent]];
					Display::Cetak("Username", $dashboard['Username']);
					$dataPost = array_merge($config, $array);
					file_put_contents($this->config, json_encode($dataPost,JSON_PRETTY_PRINT));
					print Display::Sukses("Account have been saved");
				}
			}
			Display::Line();
			ngent:
			print Display::Sukses("Press enter to add another account");
			print Display::Error("Press CTRL + C to stop adding accounts\n");
			readline();
		}
	}
	private function headers($data=0){
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	private function Dashboard(){
		$r = Requests::get(host."/home",$this->headers())[1];
		$user = explode('&',explode('username&quot;:&quot;',$r)[1])[0];
		$balance = explode('&',explode('balance_alias&quot;:&quot;', $r)[1])[0];
		return ["Username" => $user, "Balance" => $balance];
	}
	private function run_bot(){
		if(!file_exists($this->config) || count(json_decode(file_get_contents($this->config),1)) < 1){
			print Display::Error("account not detected, add account first\n");
			Display::Line();
			return;
		}
		while(true){
			$list_akun = json_decode(file_get_contents($this->config),1);
			foreach($list_akun as $akun){
				$this->cookie = $akun['cookie'];
				$this->uagent = $akun['user-agent'];
				$r = json_decode(Requests::get(host."/home/claim",$this->headers())[1],1);
				if($r['status']){
					$dashboard = $this->Dashboard();
					Display::Cetak("Username", $dashboard['Username']);
					Display::Cetak("Payout", $r['payout']);
					Display::Cetak("Balance", $dashboard['Balance']);
					Display::Line();
				}
			}
			Functions::Tmr(3600);
		}
	}
}

new Bot();