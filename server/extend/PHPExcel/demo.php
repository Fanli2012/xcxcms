<?php
/**
 * Excel数据导入
 * @param string $file excel文件
 * @param string $sheet
 * @return string   返回解析数据
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 */
public function importExcel($file='', $sheet=0)
{
    $file = iconv("utf-8", "gb2312", $file); //转码
    if(empty($file) OR !file_exists($file))
    {
        die('file not exists!');
    }
    
    include(EXTEND_PATH.'PHPExcel/PHPExcel.php'); //引入PHP EXCEL类
    $objRead = new \PHPExcel_Reader_Excel2007(); //建立reader对象
    if(!$objRead->canRead($file))
    {
        $objRead = new \PHPExcel_Reader_Excel5();
        if(!$objRead->canRead($file))
        {
            die('No Excel!');
        }
    }
    
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
    
    $obj = $objRead->load($file); //建立excel对象
    $currSheet = $obj->getSheet($sheet); //获取指定的sheet表
    $columnH = $currSheet->getHighestColumn(); //取得最大的列号
    $columnCnt = array_search($columnH, $cellName);
    $rowCnt = $currSheet->getHighestRow(); //获取总行数
    
    $data = array();
    
    //读取内容
    for($_row=1; $_row<=$rowCnt; $_row++)
    {
        for($_column=0; $_column<=$columnCnt; $_column++)
        {
            $cellId = $cellName[$_column].$_row;
            $cellValue = $currSheet->getCell($cellId)->getValue();
            //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue(); #获取公式计算的值
            //富文本转换字符串
            if($cellValue instanceof PHPExcel_RichText)
            {
                $cellValue = $cellValue->__toString();
            }
            
            $data[$_row][$cellName[$_column]] = $cellValue;
        }
    }
    
    return $data;
}

/**
 * Excel数据导出
 * @param array   $title    标题行名称
 * @param array   $data     导出数据
 * @param string  $fileName 文件名
 * @param string  $savePath 保存路径
 * @param $type   是否下载  false--保存   true--下载
 * @return string 返回文件全路径
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 */
public function exportExcel($title=array(), $data=array(), $fileName='', $savePath='./', $isDown=false)
{
    include(EXTEND_PATH.'PHPExcel/PHPExcel.php');
    $obj = new \PHPExcel();
    
    //横向单元格标识
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
    
    $obj->getActiveSheet(0)->setTitle('sheet名称'); //设置sheet名称
    $_row = 1; //设置纵向单元格标识
    if($title)
    {
        $_cnt = count($title);
        $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row); //合并单元格
        $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出：'.date('Y-m-d H:i:s')); //设置合并后的单元格内容
        $_row++;
        $i = 0;
        
        //设置列标题
        foreach($title AS $v)
        {
            $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);
            $i++;
        }
        
        $_row++;
    }
    
    //填写数据  
    if($data)
    {
        $i = 0;
        foreach($data AS $_v)
        {
            $j = 0;
            foreach($_v AS $_cell)
            {
                $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i+$_row), $_cell);
                $j++;
            }
            
            $i++;
        }
    }
    
    //文件名处理
    if(!$fileName)
    {
        $fileName = uniqid(time(),true);
    }
    
    $objWrite = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
    
    //网页下载
    if($isDown)
    {
        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');exit;
    }
    
    $_fileName = iconv("utf-8", "gb2312", $fileName); //转码
    $_savePath = $savePath.$_fileName.'.xlsx';
    $objWrite->save($_savePath);
    
    return $savePath.$fileName.'.xlsx';
}

//导出
//exportExcel(array('姓名','年龄'), array(array('a',21),array('b',23)), '档案', './', true);

//导入
//$excel = array_splice(importExcel($_FILES["file"]["tmp_name"]),1);