<?php

namespace app\common\lib;
/**
 * 图片处理类
 */
class Image
{
    /**
     * [yuan_img 编辑图片为圆形]  剪切头像为圆形
     * @param  [string] $imgpath [头像保存之后的图片名]
     */
    public static function yuan_img($imgpath)
    {
        $ext = pathinfo($imgpath);
        $src_img = null;
        switch ($ext['extension']) {
            case 'jpg':
                $src_img = imagecreatefromjpeg($imgpath);
                break;
            case 'jpeg':
                $src_img = imagecreatefromjpeg($imgpath);
                break;
            case 'png':
                $src_img = imagecreatefrompng($imgpath);
                break;
        }
        $wh = getimagesize($imgpath);
        $w = $wh[0];
        $h = $wh[1];
        $w = min($w, $h);
        $h = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }

        return $img;
    }

    /**
     * [create_pic_watermark 添加图片水印] 头像贴在二维码中间
     * @param  [string] $dest_image [需要添加图片水印的图片名]
     * @param  [string] $watermark  [水印图片名]
     * @param  [string] $locate     [水印位置，center,left_buttom,right_buttom三选一]
     * @return [type]             [description]
     */
    public static function create_pic_watermark($dest_image, $watermark, $locate)
    {
        list($dwidth, $dheight, $dtype) = getimagesize($dest_image);
        list($wwidth, $wheight, $wtype) = getimagesize($watermark);
        $types = array(1 => "GIF", 2 => "JPEG", 3 => "PNG",
            4 => "SWF", 5 => "PSD", 6 => "BMP",
            7 => "TIFF", 8 => "TIFF", 9 => "JPC",
            10 => "JP2", 11 => "JPX", 12 => "JB2",
            13 => "SWC", 14 => "IFF", 15 => "WBMP", 16 => "XBM");
        $dtype = strtolower($types[$dtype]);//原图类型
        $wtype = strtolower($types[$wtype]);//水印图片类型
        $created = "imagecreatefrom" . $dtype;
        $createw = "imagecreatefrom" . $wtype;
        $imgd = $created($dest_image);
        $imgw = $createw($watermark);
        switch ($locate) {
            case 'center':
                $x = ($dwidth - $wwidth) / 2;
                $y = ($dheight - $wheight) / 2;
                break;
            case 'left_buttom':
                $x = 1;
                $y = ($dheight - $wheight - 2);
                break;
            case 'right_buttom':
                $x = ($dwidth - $wwidth - 1);
                $y = ($dheight - $wheight - 2);
                break;
            default:
                die("未指定水印位置!");
                break;
        }
        imagecopy($imgd, $imgw, $x, $y, 0, 0, $wwidth, $wheight);
        $save = "image" . $dtype;
        //保存到服务器
        $f_file_name = $dest_image;
        imagejpeg($imgd, $f_file_name); //保存
        imagedestroy($imgw);
        imagedestroy($imgd);
        //传回处理好的图片
        //$url = 'https://www.qubaobei.com/'.str_replace('/opt/ci123/www/html/markets/app2/baby/','',PATH.$f_file_name);
        return $dest_image;
    }

    /**
     * 重置图片文件大小
     * @param $filename：生成的图片路径
     * @param $tmpname：原图路径
     * @param $xmax：修改后最大宽度。
     * @param $ymax：修改后最大高度。
     * @return
     */
    public static function resize_image($filename, $tmpname, $xmax, $ymax)
    {
        $ext = explode(".", $filename);
        $ext = $ext[count($ext) - 1];

        if ($ext == "jpg" || $ext == "jpeg")
            $im = imagecreatefromjpeg($tmpname);
        elseif ($ext == "png")
            $im = imagecreatefrompng($tmpname);
        elseif ($ext == "gif")
            $im = imagecreatefromgif($tmpname);

        $x = imagesx($im);
        $y = imagesy($im);

        if ($x <= $xmax && $y <= $ymax)
            return $im;

        if ($x >= $y) {
            $newx = $xmax;
            $newy = $newx * $y / $x;
        } else {
            $newy = $ymax;
            $newx = $x / $y * $newy;
        }

        $im2 = imagecreatetruecolor($newx, $newy);
        // 拷贝图像或图像的一部分并调整大小
        imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newx), floor($newy), $x, $y);
        //输出缩小后的图像

        if ($ext == "jpg" || $ext == "jpeg")
            imagejpeg($im2, $filename, 100);
        elseif ($ext == "png")
            imagepng($im2, $filename, 100);
        elseif ($ext == "gif")
            imagegif($im2, $filename, 100);

        imagedestroy($im);
        imagedestroy($im2);
        return $im2;
    }

}