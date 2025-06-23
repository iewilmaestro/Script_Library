<?php

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