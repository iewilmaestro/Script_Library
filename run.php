<?php

if($argv[1] !== "error"){
	error_reporting(0);
}

require "modul/class.php";

Display::Banner_menu();

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
$r = scandir("bot/".$menu[$pil]);
$a = 0;
foreach($r as $act){
	if($act == '.' || $act == '..') continue;
	$menu2[$a] =  $act;
	Display::Menu($a, Functions::clean($act));
	$a++;
}
Display::Menu($a, m.'Back');
print Display::Isi("Number");
$pil2 = readline();
Display::Line();
if($pil2 == '' || $pil2 > Count($menu2))exit(Display::Error("Tolol"));
if($pil2 == Count($menu2))goto menu_pertama;

define("title",Functions::clean($menu2[$pil2]));

require "bot/".$menu[$pil]."/".$menu2[$pil2];

?>