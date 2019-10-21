<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\OrderLogic;
use app\common\model\Order as OrderModel;

class Order extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new OrderLogic();
    }

    //订单列表
    public function index()
    {
        $where = array();
        $where['delete_time'] = OrderModel::ORDER_UNDELETE;
        if (!empty($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') {
            $where['name|mobile|order_sn'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        if (isset($_REQUEST['mobile']) && $_REQUEST['mobile'] != '') {
            $where['mobile'] = array('like', '%' . $_REQUEST['mobile'] . '%');
        }

        if (isset($_REQUEST['order_sn']) && $_REQUEST['order_sn'] != '') {
            $where['order_sn'] = array('like', '%' . $_REQUEST['order_sn'] . '%');
        }

        if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
            $where['name'] = array('like', '%' . $_REQUEST['name'] . '%');
        }

        //0或者不传表示全部，1待付款，2待发货,3待收货,4待评价(确认收货，交易成功),5退款/售后
        if (isset($_REQUEST['status']) && $_REQUEST['status'] > 0) {
            if ($_REQUEST['status'] == 1) {
                $where['order_status'] = 0;
                $where['pay_status'] = 0;
            } elseif ($_REQUEST['status'] == 2) {
                $where['order_status'] = 0;
                $where['shipping_status'] = 0;
                $where['pay_status'] = 1;
                $where['pay_status'] = 0;
            } elseif ($_REQUEST['status'] == 3) {
                $where['order_status'] = 0;
                $where['refund_status'] = 0;
                $where['shipping_status'] = 1;
                $where['pay_status'] = 1;
            } elseif ($_REQUEST['status'] == 4) {
                $where['order_status'] = 3;
                $where['refund_status'] = 0;
                $where['shipping_status'] = 2;
                $where['is_comment'] = 0;
            } elseif ($_REQUEST['status'] == 5) {
                $where['order_status'] = 3;
                $where['refund_status'] = 1;
            }
        }
        $list = $this->getLogic()->getPaginate($where, 'id desc');

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //订单详情
    public function detail()
    {
        if (!empty($_GET['id'])) {
            $id = $_GET['id'];
        } else {
            $id = '';
        }
        if (preg_match('/[0-9]*/', $id)) {
        } else {
            exit;
        }

        $assign_data['post'] = model('Order')->getOne(array('id' => $id));
        $assign_data['kuaidi'] = model('Kuaidi')->getAll(array('status' => 0), 'listorder asc');

        $this->assign($assign_data);
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $this->success($res['msg'], url('index'), '', 1);
        }

        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $this->success($res['msg'], url('index'), '', 1);
        }

        if (!checkIsNumber(input('id', null))) {
            $this->error('参数错误');
        }
        $where['id'] = input('id');
        $this->assign('id', $where['id']);

        $post = $this->getLogic()->getOne($where);
        $this->assign('post', $post);

        return $this->fetch();
    }

    //删除
    public function del()
    {
        if (!checkIsNumber(input('id', null))) {
            $this->error('删除失败！请重新提交');
        }
        $where['id'] = input('id');

        $res = $this->getLogic()->del($where);
        if ($res['code'] != ReturnData::SUCCESS) {
            $this->error($res['msg']);
        }

        $this->success('删除成功');
    }

    //发货修改物流信息
    public function change_shipping()
    {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $id = $_POST['id'];
        } else {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));
        }

        $data['shipping_id'] = input('shipping_id', '');
        $data['shipping_sn'] = input('shipping_sn', '');

        if ($data['shipping_id'] == '') {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));
        }

        if ($data['shipping_id'] == 0) {
            $data['shipping_name'] = '无须物流';
            unset($data['shipping_sn']);
        } else {
            if ($data['shipping_sn'] == '') {
                exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));
            }

            $data['shipping_name'] = model('Kuaidi')->getValue(array('id' => $data['shipping_id']), 'name');
        }

        if (model('Order')->edit($data, array('id' => $id, 'shipping_status' => 0)) === false) {
            exit(json_encode(ReturnData::create(ReturnData::FAIL)));
        }

        exit(json_encode(ReturnData::create(ReturnData::SUCCESS)));
    }

    //修改订单状态
    public function change_status()
    {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $id = $_POST['id'];
        } else {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));
        }
        $status = input('status', '');
        if ($status == '') {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));
        }

        //2设为已付款，3发货，4设为已收货，7设为无效，8同意退款
        if ($status == 2) {
            $data['pay_status'] = 1;

            //...
        } elseif ($status == 3) {
            $data['shipping_status'] = 1;
        } elseif ($status == 4) {
            $data['order_status'] = 3;
            $data['shipping_status'] = 2;

            //...
        } elseif ($status == 7) {
            $data['order_status'] = 2;

            //返库存
            if (!model('Order')->returnStock($id)) {
                exit(json_encode(ReturnData::create(ReturnData::FAIL)));
            }
        } elseif ($status == 8) {
            $data['refund_status'] = 2;

            $order = model('Order')->getOne(array('id' => $id));
            if (!$order) {
                exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));
            }
            if ($order['pay_money'] > 0) {
                //增加用户余额及余额记录
                $user_money_data['user_id'] = $order['user_id'];
                $user_money_data['type'] = 0;
                $user_money_data['money'] = $order['pay_money'];
                $user_money_data['desc'] = '退货-返余额';
                $user_money = logic('UserMoney')->add($user_money_data);
                if ($user_money['code'] != ReturnData::SUCCESS) {
                    exit(json_encode(ReturnData::create(ReturnData::FAIL)));
                }
            }

            //返库存
            if (!Order::returnStock($id)) {
                exit(json_encode(ReturnData::create(ReturnData::FAIL)));
            }
        }

        if (model('Order')->edit($data, array('id' => $id)) === false) {
            exit(json_encode(ReturnData::create(ReturnData::FAIL)));
        }

        exit(json_encode(ReturnData::create(ReturnData::SUCCESS)));
    }

    //导出订单Excel
    public function output_excel()
    {
        $res = '';
        $where = array();
        $where['delete_time'] = OrderModel::ORDER_UNDELETE;
        if (!empty($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') {
            $where['name|mobile|order_sn'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        if (isset($_REQUEST['mobile']) && $_REQUEST['mobile'] != '') {
            $where['mobile'] = array('like', '%' . $_REQUEST['mobile'] . '%');
        }

        if (isset($_REQUEST['order_sn']) && $_REQUEST['order_sn'] != '') {
            $where['order_sn'] = array('like', '%' . $_REQUEST['order_sn'] . '%');
        }

        if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
            $where['name'] = array('like', '%' . $_REQUEST['name'] . '%');
        }
        if (isset($_REQUEST['min_addtime']) && isset($_REQUEST['max_addtime']) && !empty($_REQUEST['min_addtime']) && !empty($_REQUEST['max_addtime'])) {
            $where['add_time'] = array('>=', strtotime($_REQUEST['min_addtime']));
            $where['add_time'] = array('<=', strtotime($_REQUEST['max_addtime']));
        }
        //0或者不传表示全部，1待付款，2待发货,3待收货,4待评价(确认收货，交易成功),5退款/售后
        if (isset($_REQUEST['status']) && $_REQUEST['status'] > 0) {
            if ($_REQUEST['status'] == 1) {
                $where['order_status'] = 0;
                $where['pay_status'] = 0;
            } elseif ($_REQUEST['status'] == 2) {
                $where['order_status'] = 0;
                $where['shipping_status'] = 0;
                $where['pay_status'] = 1;
                $where['pay_status'] = 0;
            } elseif ($_REQUEST['status'] == 3) {
                $where['order_status'] = 0;
                $where['refund_status'] = 0;
                $where['shipping_status'] = 1;
                $where['pay_status'] = 1;
            } elseif ($_REQUEST['status'] == 4) {
                $where['order_status'] = 3;
                $where['refund_status'] = 0;
                $where['shipping_status'] = 2;
                $where['is_comment'] = 0;
            } elseif ($_REQUEST['status'] == 5) {
                $where['order_status'] = 3;
                $where['refund_status'] = 1;
            }
        }

        //导出Excel
        $excel_title = array('ID', '订单号', '时间', '状态', '商品总价', '应付金额', '支付金额', '收货人', '地址', '电话', '订单来源');
        $cellData = array();
        array_push($cellData, $excel_title);
        $order_list = model('Order')->getAll($where, 'id desc');
        if ($order_list) {
            foreach ($order_list as $k => $v) {
                array_push($cellData, array($v['id'], $v['order_sn'], date('Y-m-d H:i:s', $v['add_time']), $v['status_text'], $v['goods_amount'], $v['order_amount'], $v['pay_money'], $v['name'], $v['province_name'] . $v['city_name'] . $v['district_name'] . ' ' . $v['address'], $v['mobile'], $v['place_type_text']));
            }
        }
        $excel_data = $cellData;
        $this->export_excel($excel_title, $excel_data, '订单列表', './', true);
    }

    /**
     * Excel数据导出
     * @param array $title 标题行名称
     * @param array $data 导出数据
     * @param string $fileName 文件名
     * @param string $savePath 保存路径
     * @param $type   是否下载  false保存,true下载
     * @return string 返回文件全路径
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function export_excel($title = array(), $data = array(), $fileName = '', $savePath = './', $isDown = false)
    {
        include(EXTEND_PATH . 'PHPExcel/PHPExcel.php');
        $obj = new \PHPExcel();

        //横向单元格标识
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $obj->getActiveSheet(0)->setTitle('sheet名称'); //设置sheet名称
        $_row = 1; //设置纵向单元格标识
        //填写数据  
        if ($data) {
            $i = 0;
            foreach ($data AS $_v) {
                $j = 0;
                foreach ($_v AS $_cell) {
                    $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + $_row), $_cell);
                    $j++;
                }

                $i++;
            }
        }

        //文件名处理
        if (!$fileName) {
            $fileName = uniqid(time(), true);
        }

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');

        //网页下载
        if ($isDown) {
            header('pragma:public');
            header("Content-Disposition:attachment;filename=$fileName.xlsx");
            $objWrite->save('php://output');
            exit;
        }

        $_fileName = iconv('utf-8', 'gb2312', $fileName); //转码
        $_savePath = $savePath . $_fileName . '.xlsx';
        $objWrite->save($_savePath);

        return $savePath . $fileName . '.xlsx';
    }
}