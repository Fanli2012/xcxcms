<?php
//header('Content-Type: image/jpeg');

imageResize($_REQUEST['url'], $_REQUEST['w'], $_REQUEST['h']);
//imageResize("http://www.thinkphp5.com/images/banner.jpg", 500, 500);

// 按比例调整图片大小
function imageResize($url, $width, $height)
{
	header("Content-type: image/jpeg");
	
	//获取图片后缀
	$image_suffix = pathinfo($url)['extension'];
	
	list($width_orig, $height_orig) = getimagesize($url);
	$ratio_orig = $width_orig/$height_orig;
	
	if($width/$height > $ratio_orig)
	{
		$width = $height*$ratio_orig;
	}
	else
	{
		$height = $width/$ratio_orig;
	}
	
	// This resamples the image
	$image_p = imagecreatetruecolor($width, $height);
	
	$image = "";
	switch ($image_suffix)
	{
		case "png":
		$image = imagecreatefrompng($url);
		break;
		case "gif":
		$image = imagecreatefromgif($url);
		break;
		case "jpg":
		$image = imagecreatefromjpeg($url);
		break;
		case "jpeg":
		$image = imagecreatefromjpeg($url);
		break;
		case "bmp":
		$image = imagecreatefromwbmp($url);
		break;
		default:
		$image = imagecreatefromstring($url);
	}
	
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	// Output the image
	imagejpeg($image_p, null, 100);
}
?>