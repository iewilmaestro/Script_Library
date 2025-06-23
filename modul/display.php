<?php

class Display {
	private ?string $title = null;
	
	static function Clear(){
		(PHP_OS == "Linux") ? system('clear') : pclose(popen('cls','w'));
	}
	static function rata ( string $var, string $value): string
	{
		$list_var = [
			"success"	=> h."✓",
			"warning"	=> m."!",
			"debug"		=> k."?",
			"info"		=> b."i"
		];
		$len = (in_array($var, array_keys($list_var)))? 8:9;
		$lenstr = ($len == 8)? $len-strlen($var)+1:$len-strlen($var);
		$open = ($len == 8)? $list_var[$var]." ":"› ";
		return $open.$var.str_repeat(" ",$lenstr).p.":: ".$value;
	}
	static function Menu($no, $title, $last = 0): void
	{
		if($last && strlen($title) < 11){
			print "-[$no] $title\t\t$last\n";
		}elseif($last){
			print "-[$no] $title\t$last\n";
		}else{
			print "-[$no] $title\n";
		}
	}
	static function cetak( string $var, string $value): void
	{
		print self::rata($var, $value) . "\n";
	}
	static function Title($string){
		print str_pad(strtoupper($string),45, " ", STR_PAD_BOTH)."\n";
	}
	static function line( int $len = 45): void
	{
		print d.str_repeat('─',$len)."\n";
	}
	static function table( array $data): void
	{
		foreach ($data as $key => $value) {
			print self::rata($key, $value) . "\n";
		}
	}
	static function Ban($title = null, $versi = null): void
	{
		self::clear();
		self::line();
		if ($title !== null) {
			self::title($title." [$versi]");
		}
		self::table([
			"author"	=> "iewil",
			"youtube"	=> "youtube.com/@iewil",
			"telegram"	=> "t.me/MaksaJoin",
			"blogger"	=> "iewilofficial.blogspot.com"
		]);
		
		self::line();
		if ($title !== null) {
			self::Error("illegal program\n");
			self::info("Free script, not for sale");
		}else{
			self::info("Update Manual script by command `git pull`");
			self::info("before run script!");
		}
		self::line();
		print PHP_EOL;
	}
	static function ipApi(){
		$r = json_decode(file_get_contents("http://ip-api.com/json"));
		if($r->status == "success")return $r;
	}
	static function debug( string $message): void
	{
		print self::rata("debug", $message) . "\n";
	}
	static function info( string $message): void
	{
		print self::rata("info", $message) . "\n";
	}
	static function Error(string $message): void
	{
		print self::rata("warning", $message);
	}
	static function sukses ( string $message ): void
	{
		print self::rata("success", $message) . "\n";
	}
	static function Isi($msg){
		return m."╭[".p."Input $msg".m."]\n╰> ".h;
	}
}