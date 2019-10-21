<?php
/**
 * 定时自动执行任务控制器
 */

namespace app\task\controller;

class Month extends Base
{
    /**
     * 初始化方法,最前且始终执行
     */
    public function _initialize()
    {
        // 只可以以cli方式执行
//        if (!$this->request->isCli())
//            $this->error('Autotask script only work at client!');
        parent::_initialize();
        // 清除错误
        error_reporting(0);
        // 设置永不超时
        set_time_limit(0);
    }

    /**
     * 月账单
     */
    public function indext()
    {
        $orderModel = model('order'); //订单模型
        $orderBillModel = model('order_bill'); //结算表模型
        $orderStatisModel = model('order_statis'); //月销量统计表

        $year = date('Y'); //当前年份
        $month = date('m'); //当前月份
        $year_month = date('Ym'); //当前年月
        //
        //php获取本月起始时间戳和结束时间戳
        $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));

        //从会员表获取所有卖家，包括供应商和服务商
        Db::startTrans();
        /* 统计本月所有卖家销量 */
        for ($doc_type = 1; $doc_type <= 4; $doc_type++) {
            //$doc_type 文档类型 1剥离卷 2法律建议书 3尽调报告 4投资报告
            switch ($doc_type) {
                case 1:
                    //获取所有供应商
                    $mapMember['is_amc|is_bank|is_fm'] = 1;
                    break;
                case 2:
                    //获取所有法律顾问
                    $mapMember['is_lawyer'] = 1;
                    break;
                case 3:
                    //获取所有尽调公司
                    $mapMember['is_due'] = 1;
                    break;
                case 4:
                    //获取所有投资顾问
                    $mapMember['is_invest'] = 1;
                    break;
            }
            $seller_member_list = model('member')->getMemberAll($mapMember, 'member_id asc', 'member_id,member_name');
            foreach ($seller_member_list as $key => $value) {
                $mapOrder['add_time'][] = ['>=', $beginThismonth];
                $mapOrder['add_time'][] = ['<=', $endThismonth];
                $mapOrder['order_state'] = 20; //交易成功
                $mapOrder['seller_member_id'] = $value['member_id'];

                $mapOrder['doc_type'] = $doc_type;
                //根据每个商家统计当前月的销量，并写入结算单表
                $resultOrderBySeller = $orderModel->where($mapOrder)->field('sum(fee_amount) as sum_fee_amount,sum(order_amount) as sum_order_amount')->find();
                $orderBillList[$key]['ob_no'] = $year_month . $value['member_id'] . $doc_type; //结算单编号(年月+店铺ID+文档类型)
                $orderBillList[$key]['ob_start_date'] = $beginThismonth; //开始日期
                $orderBillList[$key]['ob_end_date'] = $endThismonth; //结束日期
                $orderBillList[$key]['ob_order_totals'] = $resultOrderBySeller['sum_order_amount'] ? $resultOrderBySeller['sum_order_amount'] : 0; //订单金额
                $orderBillList[$key]['ob_fee_totals'] = $resultOrderBySeller['sum_fee_amount'] ? $resultOrderBySeller['sum_fee_amount'] : 0; //总服务费
                $orderBillList[$key]['ob_result_totals'] = $orderBillList[$key]['ob_order_totals'] - $orderBillList[$key]['ob_fee_totals']; //应结金额
                $orderBillList[$key]['ob_create_date'] = time(); //生成结算单日期
                $orderBillList[$key]['os_month'] = $year_month; //结算单年月份
                $orderBillList[$key]['ob_state'] = 1; //1默认2卖家已确认3平台已审核4结算完成
                $orderBillList[$key]['ob_member_id'] = $value['member_id']; //会员ID
                $orderBillList[$key]['ob_member_name'] = $value['member_name']; //会员名
                $orderBillList[$key]['doc_type'] = $doc_type; //文档类型 1剥离卷 2法律建议书 3尽调报告 4投资报告

                //先查询结算单模型中是否有本月统计过的记录，若无记录，插入一条，若有记录，则更新本条记录
                $mapOrderBill['ob_no'] = $orderBillList[$key]['ob_no'];
                $orderBillRec = $orderBillModel->where($mapOrderBill)->field('ob_no')->find();
                if (!empty($orderBillRec)) {
                    $rs[] = $orderBillModel->save($orderBillList[$key], $mapOrderBill);
                } else {
                    $rs[] = $orderBillModel->insert($orderBillList[$key]);
                }
                $rs[] = $orderModel->save(['ob_no' => $orderBillList[$key]['ob_no']], $mapOrder);
            }
        }

        $map_statis['add_time'][] = ['>=', $beginThismonth];
        $map_statis['add_time'][] = ['<=', $endThismonth];
        $map_statis['order_state'] = 20; //交易成功
        //统计本月所有商家的销量，写入月销量统计表
        $resultOrderTotal = $orderModel->where($map_statis)->field('sum(fee_amount) as sum_fee_amount,sum(order_amount) as sum_order_amount')->find();
        if (!empty($resultOrderTotal)) {
            $orderStatisData['os_month'] = $year_month; //统计编号(年月)
            $orderStatisData['os_year'] = $year; //年
            $orderStatisData['os_start_date'] = $beginThismonth; //开始日期
            $orderStatisData['os_end_date'] = $endThismonth; //结束日期
            $orderStatisData['os_order_totals'] = $resultOrderTotal->sum_order_amount ? $resultOrderTotal->sum_order_amount : 0; //订单金额
            $orderStatisData['os_order_fee'] = $resultOrderTotal->sum_fee_amount ? $resultOrderTotal->sum_fee_amount : 0; //服务费
            $orderStatisData['os_result_totals'] = $orderStatisData['os_order_totals'] - $orderStatisData['os_order_fee']; //本期应结
            $orderStatisData['os_create_date'] = time(); //创建记录日期
            //先查询月销量统计表是否有本月的统计记录，若有，则更新，若无，则插入一条本月记录
            $mapOrderStatis['os_month'] = $year_month;
            $orderStatisRec = $orderStatisModel->where($mapOrderStatis)->field('os_month')->find();

            if (!empty($orderStatisRec)) {
                $rs[] = $orderStatisModel->where($mapOrderStatis)->update($orderStatisData);
            } else {
                $rs[] = $orderStatisModel->insert($orderStatisData);
            }
        }

        foreach ($rs as $value) {
            if (FALSE === $value) {
                Db::rollback();
                echo '程序执行过程出现错误，回滚已经执行的';
                exit;
            }
        }
        Db::commit();
        echo '程序执行成功';
    }
}