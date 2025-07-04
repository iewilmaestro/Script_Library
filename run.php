<?php


error_reporting(0);
date_default_timezone_set("UTC");

require "modul/class.php";
require "modul/display.php";
require "modul/showerror.php";
require "modul/requests.php";
require "modul/functions.php";
require "modul/htmlscrap.php";
require "modul/captcha.php";
require "modul/cloudflare.php";
require "modul/tesseract.php";

if(file_exists("data/error.log")){
	unlink("data/error.log");
}

$display = new Display();

// Buat folder untuk menyimpan cuki & data
if(!file_exists("data")){
	mkdir("data");
	Display::sukses("successfully created `data` folder");
	Display::line();
}

if ($argc > 1 && $argv[1] === '?') {
    echo "Gunakan perintah berikut:\n";
    echo "  php run.php            → normal usage\n";
    echo "  php run.php debug      → show debug\n";
    echo "  php run.php ?          → help\n";
    exit;
}elseif($argc > 1 && $argv[1] === 'debug') {
	$cok = new ShowError();
	$cok->_start();
}elseif($argc > 1 && $argv[1]){
	echo "❌ Error: The command '{$argv[1]}' is not recognized.\n";
    echo "Use 'php run.php ?' to see help.\n";
    exit;
}

Display::Ban();

menu_pertama:
Display::Title("Menu");
Display::Line();

$menu = [];
$r = scandir("bot");
$a = 0;
foreach($r as $act){
	if($act == '.' || $act == '..') continue;
	$menu[$a] =  $act;
	Display::Menu($a, $act);
	$a++;
}
print Display::Isi("Number");
$pil = readline();
Display::Line();
if($pil == '' || $pil >= Count($menu))exit(Display::Error("Tolol"));

menu_kedua:
Display::Title("menu -> ".$menu[$pil]);
Display::Line();

$menu2 = [];
$folderPath = "bot/" . $menu[$pil];
$r = scandir($folderPath);
$a = 0;
foreach($r as $act){
	if($act == '.' || $act == '..') continue;
	$filePath = $folderPath . '/' . $act;
	/*
    if (is_file($filePath)) {
        $lastModified = date("d/m/Y", filemtime($filePath))." (D/M/Y)";
    }else{
		$lastModified = 0;
	}
	*/
	$menu2[$a] =  $act;
	Display::Menu($a, Functions::clean($act));
	$a++;
}
Display::Menu($a, 'Back');
print Display::Isi("Number");
$pil2 = readline();
Display::Line();
if($pil2 == '' || $pil2 > Count($menu2))exit(Display::Error("Tolol"));
if($pil2 == Count($menu2))goto menu_pertama;

define("title",Functions::clean($menu2[$pil2]));

require "bot/".$menu[$pil]."/".$menu2[$pil2];

?>