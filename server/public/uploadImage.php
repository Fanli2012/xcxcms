<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

/**
 * 建立文件夹
 *
 * @param string $aimUrl
 * @return viod
 */
function createDir($aimUrl) {
    $aimUrl = str_replace('', '/', $aimUrl);
    $aimDir = '';
    $arr = explode('/', $aimUrl);
    $result = true;
    foreach ($arr as $str) {
        $aimDir .= $str . '/';
        if (!file_exists($aimDir)) {
            $result = mkdir($aimDir);
        }
    }
    return $result;
}

// Define a destination
$wjj = 'uploads/'.date('Y/m',$_POST['timestamp']); // Relative to the root
$targetFolder = '/uploads/'.date('Y/m',$_POST['timestamp']); // Relative to the root
//$targetFolder = '/uploads'; // Relative to the root
$str=$targetFolder.'/';


$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if(!file_exists($wjj)){
    createDir($wjj); //echo '创建文件夹test成功';
}

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
    $tempFile = $_FILES['Filedata']['tmp_name'];
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
    $fileParts = pathinfo($_FILES['Filedata']['name']);
    $filename=date('Ymdhis',$_POST['timestamp']).rand(1000,9999).".".$fileParts['extension'];

    $targetFile = rtrim($targetPath,'/') . '/' . $filename;

    // Validate the file type
    $fileTypes = array('jpg','jpeg','gif','png'); // File extensions

    if (in_array($fileParts['extension'],$fileTypes)) {
        move_uploaded_file($tempFile,$targetFile);
        echo $str.$filename;
    } else {
        echo 'Invalid file type.';
    }
}
?>

<?php
/* header("Content-type: text/html; charset=utf-8");

$file = $_FILES['Filedata'];//得到传输的数据
//得到文件名称
$name = $file['name'];
$type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
$allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
//判断文件类型是否被允许上传
if(!in_array($type, $allow_type)){
  //如果不被允许，则直接停止程序运行
  return ;
}
//判断是否是通过HTTP POST上传的
if(!is_uploaded_file($file['tmp_name'])){
  //如果不是通过HTTP POST上传的
  return ;
}
$upload_path = "/uploads/"; //上传文件的存放路径
//开始移动文件到相应的文件夹
if(move_uploaded_file($file['tmp_name'],$upload_path.$file['name'])){
  echo "Successfully!";
}else{
  echo "Failed!";
} */
?>