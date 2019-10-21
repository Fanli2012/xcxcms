<?php
namespace app\weixin\controller;
use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Server extends Base
{
    //文章列表页
    public function listarc()
	{
        $res["code"] = 0;
        $res["msg"] = "success";
		$res["data"] = "";
		
        $where = array();
        $result = "";
		
        $PageIndex = input('PageIndex',1);
    	$PageSize = input('PageSize',CMS_PAGESIZE);
        $limit = ($PageIndex-1)*$PageSize.','.$PageSize;
        
        $typeid = input('typeid');if(!empty($typeid)){ $where['typeid']=$typeid; }
        $tuijian = input('tuijian');if(!empty($tuijian)){ $where['tuijian']=$tuijian; }
		$field = input('field','id,typeid,click,title,writer,litpic,pubdate');
        $orderby = input('orderby','pubdate desc');
        $mname = input('mname','article');
        
		$count = db($mname)->where($where)->count();
        $list = db($mname)->where($where)->field($field)->order($orderby)->limit($limit)->select();
        
		if(!empty($list) && $PageIndex<=10)
		{
			/* foreach($list as $key=>$row)
			{
				//$list[$key]["pubdate"] = date("Y-m-d", $list[$key]["pubdate"]);
				$result .= '<div class="list">';
				
				if(!empty($row['litpic']) && file_exists($_SERVER['DOCUMENT_ROOT'].$row['litpic']))
				{
					$result .= '<a class="';
					//判断图片长宽
					if(getimagesize($row['litpic'])[0]>getimagesize($row['litpic'])[1])
					{
						$result .= 'limg';
					}
					else
					{
						$result .= 'simg';
					}
					
					$result .= '" href="'.WEBHOST.'/p/'.$row['id'].'"><img alt="'.$row['title'].'" src="'.$row['litpic'].'"></a>';
				}
				
				$result .= '<strong class="tit"><a href="'.WEBHOST.'/p/'.$row['id'].'">'.$row['title'].'</a></strong><p>'.mb_strcut(strip_tags($row['description']),0,126,'UTF-8').'..<a href="'.WEBHOST.'/p/'.$row['id'].'" class="more">[详情]</a></p>';
				$result .= '<div class="info"><span class="fl"><i class="pub-v"></i><em>'.date("Y-m-d H:i",$row['pubdate']).'</em></span><span class="fr"><em>'.$row['click'].'</em>人阅读</span></div><div class="cl"></div></div>';
			} */
			
            foreach($list as $key=>$row)
			{
                $list[$key]["url"] = get_front_url(array("id"=>$row['id'],"catid"=>$row['typeid'],"type"=>'content'));
            }
            
			$res["data"] = $list;
		}
		/* $result['List']=$list;
		$result['Count']=$count>0?$count:0; */
		
        //return $res;
        exit(json_encode($res));
	}
}