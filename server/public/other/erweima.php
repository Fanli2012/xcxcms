<?php
require_once(dirname(dirname(__FILE__))."/../vendor/phpqrcode/phpqrcode.php");
header('Content-Type: image/png');

$url = "http://www.baidu.com/";

if(isset($_REQUEST['url']) && !empty($_REQUEST['url']))
{
	$url = $_REQUEST['url'];

	$url = str_replace("%26","&",$url);
	$url = str_replace("%3F","?",$url);
	$url = str_replace("%3D","=",$url);
}

QRcode::png($url,false,"H",6,2);
?>