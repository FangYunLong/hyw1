<?php
/**
 *
//
 * Author: 当燃      
*: 2015-12-21
 */

namespace Admin\Controller;
use Admin\Logic\GoodsLogic;
use Admin\Logic\OrderLogic;
use Think\AjaxPage;
class ReportController extends BaseController{
	public $begin;
	public $end;
	public  $order_status;
	public  $pay_status;
	public  $shipping_status;
	//public  $temp_status;
	/*
     * 初始化操作
     */
	public function _initialize(){
        parent::_initialize();
		$timegap = I('timegap');
		if($timegap){
			$gap = explode(' - ', $timegap);
			$begin = $gap[0];
			$end = $gap[1];
		}else{
			$lastweek = date('Y-m-d',strtotime("-1 month"));//30天前
			$begin = I('begin',$lastweek);
			$end =  I('end',date('Y-m-d'));
		}
		$this->begin = strtotime($begin);
		$this->end = strtotime($end)+86399;
		$this->assign('timegap',date('Y-m-d',$this->begin).' - '.date('Y-m-d',$this->end));

		C('TOKEN_ON',false); // 关闭表单令牌验证
		$this->order_status = C('ORDER_STATUS');
		$this->pay_status = C('PAY_STATUS');
		$this->shipping_status = C('SHIPPING_STATUS');
		$this->temp_status = C('temp_status');

		// 订单 支付 发货状态
		$this->assign('order_status',$this->order_status);
		$this->assign('pay_status',$this->pay_status);
		$this->assign('shipping_status',$this->shipping_status);
		$this->assign('temp_status',$this->temp_status);
	}



/***********************************************************************订单统计*************************************************************************/

	/*
	  * 年月租出租订单统计
	  */
	public function index()
	{
		$begin = date('Y/m/d',(time()-30*60*60*24));//30天前
		$end = date('Y/m/d',strtotime('+1 days'));
		//dump($begin.'-'.$end);die;
		//获取所有省份和地区
		$count1 = M('order')->where("order_status=2")->count(); //未完成订单数量
		$count2 = M('order')->where("order_status=5")->count(); //已完成订单数量
		$count_sum =$count1 + $count2; //订单总数量
		$this->assign('count_sum',$count_sum);
		$this->assign('count1',$count1);
		$this->assign('count2',$count2);
		$this->assign('count_sum',$count_sum);
		$this->assign('timegap',$begin.'-'.$end);
		$this->display();
	}
	/*
	 *  ajaxindex年月租出租订单统计
	 */
	public function ajaxindex(){
		$orderLogic = new OrderLogic();
		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = $gap[0];
			// $begin = strtotime($gap[0]);
			// $end = strtotime($gap[1]);
			$end = $gap[1];
		}
		// 搜索条件
		$condition = array();
		//条件1：订单状态
		$condition['order_status'] = I('order_status');
		//条件2：下单日期
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
		//条件3：订单所属城市
		I('city') ? $condition['city'] = trim(I('city')) : false;

		$sort_order = I('order_by','DESC').' '.I('sort');

		//获取总记录数
		if($condition['order_status'] == ''){
			$count = M('order')->where("order_status=2 or order_status=5")->count();
		}else{
			$count = M('order')->where($condition)->count();
		}

		$Page  = new AjaxPage($count,20);
		//  搜索条件下 分页赋值
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//dump($condition);die;
		//获取符合条件的订单列表
		if($condition['order_status'] == ''){
			$condition['order_status'] = array('in',array(2,5));
			$orderList = M('order')->where($condition)->order('add_time DESC')->limit($Page->firstRow,$Page->listRows)->select();
		}else{
			$orderList =  M('order')->where($condition)->order('add_time DESC')->limit($Page->firstRow,$Page->listRows)->select();
		}

		$this->assign('orderList',$orderList);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}

	/*
	 *临时租订单统计
	 */
	public function temporary_order(){
		$begin = date('Y/m/d',(time()-30*60*60*24));//30天前
		$end = date('Y/m/d',strtotime('+1 days'));

		$count1 = M('Temporary')->where("status=4")->count(); //未完成订单数量
		$count2 = M('Temporary')->where("status=3")->count(); //已完成订单数量
		$count_sum =$count1 + $count2; //订单总数量
		$this->assign('count_sum',$count_sum);
		$this->assign('count1',$count1);
		$this->assign('count2',$count2);
		$this->assign('timegap',$begin.'-'.$end);
		$this->display();
	}

	/*
     *Ajax临时租订单统计
     */
	public function ajax_temporary_order(){

		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = $gap[0];
			$end = $gap[1];
			// $begin = strtotime($gap[0]);
			// $end = strtotime($gap[1]);
		}
		// 搜索条件
		$condition = array();
		// I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
		I('temp_sn') ? $condition['temp_sn'] = trim(I('temp_sn')) : false;
		I('mobile') ? $condition['mobile'] = trim(I('mobile')) : false;
		I('username') ? $condition['username'] = trim(I('username')) : false;
		I('temp_status') != '' ? $condition['status'] = I('temp_status') : false;
		if($condition['status']==''){
			$count = M('Temporary')->where("status=3 or status=4")->count();
		}else{
			$count = M('Temporary')->where($condition)->count();
		}

		$Page  = new AjaxPage($count,20);
		//  搜索条件下 分页赋值
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//获取订单列表
		if($condition['status'] == ''){
			$condition['status'] = array('in',array(3,4));
			$orderList = M('Temporary')->where($condition)
								       ->order('add_time DESC')
								       ->limit($Page->firstRow,$Page->listRows)
								       ->select();
		}else{
			$orderList = M('Temporary')->where($condition)
								       ->order('add_time DESC')
								       ->limit($Page->firstRow,$Page->listRows)
								       ->select();
		}
		$this->assign('orderList',$orderList);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}


	/*
	 *特价车订单统计
	 */
	public function special_order(){
		$begin = date('Y/m/d',(time()-30*60*60*24));//30天前
		$end = date('Y/m/d',strtotime('+1 days'));
		$count1 = M('Special')->where("sp_status=0")->count(); //未完成订单数量
		$count2 = M('Special')->where("sp_status=1")->count(); //已完成订单数量
		$count_sum =$count1 + $count2; //订单总数量
		$this->assign('count_sum',$count_sum);
		$this->assign('count1',$count1);
		$this->assign('count2',$count2);
		$this->assign('timegap',$begin.'-'.$end);
		$this->display();
	}

	/*
	 *Ajax特价车订单统计
	 */
	public function ajax_special_order(){

		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
//			$begin = $gap[0];
//			$end = $gap[1];
			$begin = strtotime($gap[0]);
			$end = strtotime($gap[1]);
		}
		// 搜索条件
		$condition = array();

		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
		I('mobile2') ? $condition['u2.mobile'] = trim(I('mobile2')) : false;
		I('mobile') ? $condition['u.mobile'] = trim(I('mobile')) : false;
		I('pay_status')? intval(I('pay_status'))==8 ?$condition['sp.pay_status']=0 : $condition['sp.pay_status']=intval(I('pay_status')) : false;
		$count = M('special')->alias('sp')
							 ->join('tp_users as u on sp.user_id=u.user_id')
							 ->field('sp.*,u.mobile,u2.mobile as mobile2')
							 ->join('tp_users as u2 on sp.buy_user_id=u2.user_id')
							 ->order('sp.add_time DESC')
							 ->where($condition)
							 ->count();
		$Page  = new AjaxPage($count,20);
		//  搜索条件下 分页赋值
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//获取订单列表
		$orderList = M('special')->alias('sp')
								 ->join('tp_users as u on sp.user_id=u.user_id')
								 ->field('sp.*,u.mobile,u2.mobile as mobile2')
								 ->join('tp_users as u2 on sp.buy_user_id=u2.user_id')
								 ->order('sp.add_time DESC')
								 ->where($condition)
								 ->limit($Page->firstRow,$Page->listRows)
								 ->select();
		 //dump($orderList);exit;
		$this->assign('orderList',$orderList);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	/******************************************************************订单统计结束****************************************************************/



	/****************************************************************租车记录统计***************************************************************/

	//租车记录统计表

	/*
	 * 临时租出租记录表
	 */
	public function temporary_record(){
		$begin = date('Y/m/d',(time()-30*60*60*24));//30天前
		$end = date('Y/m/d',strtotime('+1 days'));

		$count1 = M('Temporary')->where("status=4")->count(); //未完成订单数量
		$count2 = M('Temporary')->where("status=3")->count(); //已完成订单数量
		$count_sum =$count1 + $count2; //订单总数量
		$this->assign('count_sum',$count_sum);
		$this->assign('count1',$count1);
		$this->assign('count2',$count2);
		$this->assign('timegap',$begin.'-'.$end);
		$this->display();
	}

	/*
   	 *	Ajax临时租出租记录表
   	 */
	public function ajax_temporary_record(){

		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = $gap[0];
			$end = $gap[1];
		}
		// 搜索条件
		$condition = array();
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
		// dump($condition);exit;
		I('mobile') ? $condition['mobile']= trim(I('mobile')) : false;
		$address = I('address') ? $address= trim(I('address')) : false;
		//$username = I('address') ? $address= trim(I('address')) : false;
		$condition['address'] =array('like',"%$address%");
		 $condition['status'] = 3;
		 // $condition['is_play'] =1;
		$count = M('Temporary')->where($condition)->count();
		$Page  = new AjaxPage($count,20);
		//  搜索条件下 分页赋值
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//获取订单列表
		// $orderList = $orderLogic->getOrderList($condition,$sort_order,$Page->firstRow,$Page->listRows);
		$orderList = M('Temporary')->where($condition)->order('add_time DESC')->limit($Page->firstRow,$Page->listRows)->select();
		// echo $orderList;
		$sum = M('Temporary')->where("is_play = 1 and status=3")->count();
		//dump($sum);die;
		$this->assign('orderList',$orderList);
		$this->assign('sum',$sum);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}

	/*
	 * 临时租出租订单详情
	 * @param int $id 订单id
	 */
	public function temporary_info($order_id){
		//$order_id =$_GET['id'];
		$order = M('Temporary')->where(array('temp_id'=>$order_id))->find();
		$Users = M('Users')->find($order['user_id']);   //用户信息
		$Users2 = M('Users')->find($order['driver_id']);   //车主信息
		$Goods = M('Goods')->find($order['goods_id']);  //车辆信息


		$this->assign('order',$order);
		$OrderInfo = M('temp_info')
				->alias('oi')
				->join('tp_users as u on oi.user_id = u.user_id')
				->where(['temp_id'=>$order_id])
				->find();
		//dump($order);dump($Users);dump($Goods);
		//dump($OrderInfo);
		$this->is_yt = C('is_yt');
		$this->assign('is_yt',$this->is_yt);
		$this->assign('OrderInfo',$OrderInfo);
		$this->assign('Goods',$Goods);
		$this->assign('Users',$Users);
		$this->assign('driver',$Users2);
		$this->display();
	}



	/*
	 *叉车出租记录表
	 */
	public function car_rental_record(){
		$begin = date('Y/m/d',(time()-30*60*60*24));//30天前
		$end = date('Y/m/d',strtotime('+1 days'));

		$count1 = M('Temporary')->where("status=4")->count(); //未完成订单数量
		$count2 = M('Temporary')->where("status=3")->count(); //已完成订单数量
		$count_sum =$count1 + $count2; //订单总数量
		$this->assign('count_sum',$count_sum);
		$this->assign('count1',$count1);
		$this->assign('count2',$count2);
		$this->assign('timegap',$begin.'-'.$end);
		$this->display();
	}

	/*
	 *  ajax_special_record叉车出租记录表
	 */
	public function ajax_order_record(){
		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = date('Y-m-d H:i:s',strtotime($gap[0]));
			$end = date('Y-m-d H:i:s',strtotime($gap[1] . ' 23:59:59'));
			$begin = strtotime($gap[0]);
			$end = strtotime($gap[1]);
		}
		// 搜索条件
		$condition = array();
		//条件1：下单日期
		if($begin && $end){
			$condition['a.start_time'] = array('between',"$begin,$end");
		}
		//条件2：订单所属地址
		$address = I('address') ? $address= trim(I('address')) : false;
		$address ? $condition['a.address'] = array('like', "%$address%") : false ;
		$condition['a.order_status'] = 5;
		$condition['end_time'] = ['GT',0];

		$car_id = I('car_old');
		$times = time();
		//即将到期统计
		if($car_id == 1){
			$timesd = $times + 60*60*24*7;
			$condition['end_time'] = ['BETWEEN',$times,$timesd];
		}

		//已经到期统计
		if($car_id == 2){
			$condition['end_time'] = ['LT',$times];
		}

		//获取总记录数
		$count = M('order')->alias("a")->where($condition)->count();
		$Page  = new AjaxPage($count,10);
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//获取符合条件的订单列表

		$orderList = M('Order')->alias('a')
							   ->join("tp_order_info as oi on oi.order_id = a.order_id")
							   ->join("tp_users as u on u.user_id=oi.user_id")
							   ->where($condition)
							   ->order('a.add_time DESC')
							   // ->fetchSql()
							   ->field("a.*,u.nickname,u.mobile as u_mobile,u.xueli")
							   ->limit($Page->firstRow,$Page->listRows)
							   ->select();
		// dump($orderList);exit;
		//select sum(成绩) as 总分,count(*) from Student
		$sum =  M('Order')->where("order_status = 5")->sum('number');
		$sum_money =  M('Order')->where("order_status = 5")->sum('mprice');
		$this->assign('orderList',$orderList);
		$this->assign('sum',$sum);
		$this->assign('sum_money',$sum_money);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}

	/**
	 * 叉车记录表详情
	 * @param int $id 订单id
	 */
	public function order_info($order_id){
		$orderLogic = new OrderLogic();
		$order = $orderLogic->getOrderInfo($order_id);      //订单信息
		$orderGoods = $orderLogic->getOrderGoods($order_id);
		$Users = M('Users')->find($order['user_id']);
		$Goods = M('Goods')->find($order['goods_id']);   //车辆信息
		$button = $orderLogic->getOrderButton($order);
		// 获取操作记录
		$action_log = M('order_action')->where(array('order_id'=>$order_id))->order('log_time desc')->select();
		//dump($order);die;
		$this->assign('order',$order);
		$this->assign('action_log',$action_log);
		$this->assign('orderGoods',$orderGoods);
		$split = count($orderGoods) >1 ? 1 : 0;
		foreach ($orderGoods as $val){
			if($val['goods_num']>1){
				$split = 1;
			}
		}
		$OrderInfo = M('OrderInfo')
				->alias('oi')
				->join('tp_users as u on oi.user_id = u.user_id')
				->where(['order_id'=>$order_id])
				->select();

		$this->is_yt = C('is_yt');
		$this->assign('is_yt',$this->is_yt);
		$this->assign('OrderInfo',$OrderInfo);
		$this->assign('Goods',$Goods);
		$this->assign('Users',$Users);
		$this->assign('split',$split);
		$this->assign('button',$button);
		$this->display();
	}

	/**
	 * 叉车车辆信息
	 * @param int $id 订单id
	 */
	public function car_info($order_id){
		$orderLogic = new OrderLogic();
		$order = $orderLogic->getOrderInfo($order_id);      //订单信息
		$Goods = M('Goods')->alias("g")
						   //->join("tp_users as u on u.user_id=g.user_id")
						   //->field("g.*,u.nickname")
						   ->find($order['goods_id']);      //车辆信息
		//dump($Goods);die;
		$this->assign('Goods',$Goods);
		$this->display();
	}

	/*************************************************************叉车记录统计结束*****************************************************************/




	/*************************************************************车辆统计******************************************************************/


	//车辆统计表
	public function car_stat(){
		$today = strtotime(date('Y-m-d'));
		//dump($today);die;
		$month = strtotime(date('Y-m-01'));
		$user['today'] = D('Goods')->where("add_time>$today")->count();//今日新增车辆
		$user['month'] = D('Goods')->where("add_time>$month")->count();//本月新增车辆
		$user['total'] = D('Goods')->count();//会员总数
		$user['user_money'] = D('Goods')->sum('shop_price');//会员余额总额
		$res = M('Goods')->cache(true)->distinct(true)->field('user_id')->select();
		$user['hasorder'] = count($res);
		//dump($res);die;
		$this->assign('user',$user);
		$sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(add_time,'%Y-%m-%d') as gap from __PREFIX__goods where add_time>$this->begin and add_time<$this->end group by gap";
		$new = M()->query($sql);//新增车辆趋势
		//dump($new);die;
		foreach ($new as $val){
			$arr[$val['gap']] = $val['num'];
		}

		for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
			$brr[] = empty($arr[date('Y-m-d',$i)]) ? 0 : $arr[date('Y-m-d',$i)];
			$day[] = date('Y-m-d',$i);
		}
		$result = array('data'=>$brr,'time'=>$day);
		$this->assign('result',json_encode($result));
		$this->display();
	}

	/*******************************************************************车辆统计结束*****************************************************************/




	/****************************************************************报表导出****************************************************************/

	//年月租订单导出表格
	public function export_order()
	{
		//搜索条件
		$where = 'where 1=1 ';
		$consignee = I('consignee');
		if($consignee){
			$where .= " AND consignee like '%$consignee%' ";
		}
		$order_sn =  I('order_sn');
		if($order_sn){
			$where .= " AND order_sn = '$order_sn' ";
		}
		if(I('order_status')!=''){
			$condition ['order_status']= I('order_status');
		}else{
			$condition['order_status'] = array('in',array(2,5));
		}

		if(I('mobile')){
			$where .= " AND mobile = ".I('mobile');
		}

		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
//			$begin = strtotime($gap[0]);
//			$end = strtotime($gap[1]);
			//$where .= " AND add_time>$begin AND add_time<$end ";
			$begin = $gap[0];
			$end =$gap[1];
		}
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
//		$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from __PREFIX__order $where order by order_id";
//		$orderList = D()->query($sql);
		$orderList = M('order')->where($condition)->order('add_time DESC')->select();
		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:60px;height: 30px;">订单id</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="200px">订单编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="130px">收货人</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">手机号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">租金</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">押金</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">订单状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">下单时间</td>';
		$strTable .= '</tr>';
		if(is_array($orderList)){
			$region	= M('region')->getField('id,name');
			foreach($orderList as $k=>$val){
				$strTable .= '<tr>';
				$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_id'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['order_sn'].' </td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['use_user'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mprice'].'元'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['yajin'].'元'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->order_status[$val['order_status']].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['add_time'].'</td>';
				$orderGoods = D('order_goods')->where('order_id='.$val['order_id'])->select();
				$strGoods="";
				foreach($orderGoods as $goods){
					$strGoods .= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name'];
					if ($goods['spec_key_name'] != '') $strGoods .= " 规格：".$goods['spec_key_name'];
					$strGoods .= "<br />";
				}
				unset($orderGoods);
				//$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods.' </td>';
				$strTable .= '</tr>';
			}
		}
		$strTable .='</table>';
		unset($orderList);
		downloadExcel($strTable,'年月租订单统计表');
		exit();
	}

	//临时租订单导出表格
	public function export_temporary()
	{
		//搜索条件
//		$where = 'where 1=1 ';
//		$consignee = I('consignee');
//		if($consignee){
//			$where .= " AND consignee like '%$consignee%' ";
//		}
//		$order_sn =  I('order_sn');
//		if($order_sn){
//			$where .= " AND order_sn = '$order_sn' ";
//		}
		I('temp_sn') ? $condition['temp_sn'] = trim(I('temp_sn')) : false;      //订单号
		I('mobile') ? $condition['mobile'] = trim(I('mobile')) : false;         //手机号
		I('username') ? $condition['username'] = trim(I('username')) : false;   //收货人
		if(I('temp_status')!=''){
			$condition ['status']= I('temp_status');
		}else{
			$condition['status'] = array('in',array(3,4));
		}


		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
//			$begin = strtotime($gap[0]);
//			$end = strtotime($gap[1]);
			//$where .= " AND add_time>$begin AND add_time<$end ";
			$begin = $gap[0];
			$end =$gap[1];
		}
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
//		$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from __PREFIX__order $where order by order_id";
//		$orderList = D()->query($sql);
		$orderList = M('Temporary')->where($condition)->order('add_time DESC')->select();

		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:60px;height: 30px;">订单id</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="200px">订单编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="130px">收货人</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">手机号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">吨位</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">位置</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">订单状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">下单时间</td>';
		$strTable .= '</tr>';
		if(is_array($orderList)){
			$region	= M('region')->getField('id,name');
			foreach($orderList as $k=>$val){
				$strTable .= '<tr>';
				$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['temp_id'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['temp_sn'].' </td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['username'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['dunwei'].'吨'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['address'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->temp_status[$val['status']-1].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['add_time'].'</td>';
				//$orderGoods = D('order_goods')->where('order_id='.$val['order_id'])->select();
				$strGoods="";
//				foreach($orderGoods as $goods){
//					$strGoods .= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name'];
//					if ($goods['spec_key_name'] != '') $strGoods .= " 规格：".$goods['spec_key_name'];
//					$strGoods .= "<br />";
//				}
				unset($orderGoods);
				//$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods.' </td>';
				$strTable .= '</tr>';
			}
		}
		$strTable .='</table>';
		unset($orderList);
		downloadExcel($strTable,'临时租订单统计表');
		exit();
	}

	//特价车订单导出表格
	public function export_special()
	{
		//搜索条件
		I('mobile2') ? $condition['u2.mobile'] = trim(I('mobile2')) : false;  //购买者手机
		I('mobile') ? $condition['u.mobile'] = trim(I('mobile')) : false;     //出售者手机

		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = strtotime($gap[0]);
			$end = strtotime($gap[1]);
			//$where .= " AND add_time>$begin AND add_time<$end ";
//			$begin = $gap[0];
//			$end =$gap[1];
		}
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
		$orderList = M('Special')->alias('sp')
				->join('tp_users as u on sp.user_id=u.user_id')
				->field('sp.*,u.mobile,u2.mobile as mobile2')
				->join('tp_users as u2 on sp.buy_user_id=u2.user_id')
				->order('sp.add_time DESC')
				->where($condition)
				->select();
		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:60px;height: 30px;">订单id</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="200px">出售者手机</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="130px">购买者手机</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">商品金额</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">实际付款金额</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">应付款金额</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">支付状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">下单时间</td>';
		$strTable .= '</tr>';
		if(is_array($orderList)){
			$region	= M('region')->getField('id,name');
			foreach($orderList as $k=>$val){
				$strTable .= '<tr>';
				$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['sp_id'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].' </td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile2'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['price'].'元'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_price'].'元'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.sprintf('%.2f',$val['pay_price'] * 0.95).'元'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->pay_status[$val['pay_status']].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['add_time'].'</td>';
				$strTable .= '</tr>';
			}
		}
		$strTable .='</table>';
		unset($orderList);
		downloadExcel($strTable,'特价车订单统计表');
		exit();
	}




	/*
   	 *	临时租出租记录统计表
   	 */
	public function temporary_tongji(){

		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = $gap[0];
			$end = $gap[1];
		}
		// 搜索条件
		$condition = array();
		if($begin && $end){
			$condition['add_time'] = array('between',"$begin,$end");
		}
		// dump($condition);exit;
		I('mobile') ? $condition['mobile']= trim(I('mobile')) : false;
		$address = I('address') ? $address= trim(I('address')) : false;
		//$username = I('address') ? $address= trim(I('address')) : false;
		$condition['address'] =array('like',"%$address%");
		 $condition['status'] = 3;
		 // $condition['is_play'] =1;
		$count = M('Temporary')->where($condition)->count();
		$Page  = new AjaxPage($count,20);
		//  搜索条件下 分页赋值
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//获取订单列表
		// $orderList = $orderLogic->getOrderList($condition,$sort_order,$Page->firstRow,$Page->listRows);
		$orderList = M('Temporary')->where($condition)->order('add_time DESC')->limit($Page->firstRow,$Page->listRows)->select();

		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:60px;height: 30px;">订单id</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="200px">订单编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="130px">收货人</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">手机号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">吨位</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">用车地点</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">订单状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">下单时间</td>';
		$strTable .= '</tr>';
		if(is_array($orderList)){
			$region	= M('region')->getField('id,name');
			foreach($orderList as $k=>$val){
				$strTable .= '<tr>';
				$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['temp_id'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['temp_sn'].' </td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['username'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['dunwei'].'吨'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['address'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.'已完成'.'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['add_time'].'</td>';
				$strTable .= '</tr>';
			}
		}
		$strTable .='</table>';
		unset($orderList);
		downloadExcel($strTable,'临时租订单统计表');
		exit();


				// echo $orderList;
		$sum = M('Temporary')->where("is_play = 1 and status=3")->count();
		//dump($sum);die;
		$this->assign('orderList',$orderList);
		$this->assign('sum',$sum);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}



	/*
	 *  ajax_special_record叉车出租记录统计报表
	 */
	public function order_record(){
		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = $gap[0];
			$begin = date(strtotime($gap[0]),"Y-m-d H:i:s");
			$end = $gap[1];
			$end = strtotime($gap[1]);
			$end = date(strtotime($gap[1]),"Y-m-d H:i:s");
		}
		// 搜索条件
		$condition = array();
		//条件1：下单日期
		// if($begin && $end){
		// 	$condition['add_time'] = array('between',"$begin,$end");
		// }
		//dump($begin);
		//dump($end);
		//dump($condition);die;
		//条件2：订单所属地址
		$address = I('address') ? $address= trim(I('address')) : false;
		$address ? $condition['address'] = array('like', "%$address%") : false ;
		$condition['order_status'] = 5;
		$condition['end_time'] = ['GT',0];
		// dump($condition);exit;
		//获取总记录数
		$count = M('order')->where($condition)->count();
		//dump($condition);die;
		$Page  = new AjaxPage($count,10);
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]   =  urlencode($val);
		}
		$show = $Page->show();
		//获取符合条件的订单列表

		$orderList = M('Order')->alias('a')
							   //->join("tp_goods as g on g.goods_id = a.goods_id")
							   ->join("tp_users as u on u.user_id=a.user_id")
							   ->where($condition)
							   ->order('a.add_time DESC')
							   ->field("u.nickname,a.*")
							   ->limit($Page->firstRow,$Page->listRows)
							   ->select();
		$strTable ='<table width="500" border="1">';
		//$strTable .= '<tr>';
		//$strTable .= '<td style="text-align:center;font-size:20px;height: 50px;">年月租订单统计表</td>';
		//$strTable .= '</tr>';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:60px;height: 30px;">订单id</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="130px">订单编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="160px">开始时间</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="160px">结束时间</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">用车地点</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">车主</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">用车数量(辆)</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="120px">用车时间(月)</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">租金(元)</td>';
		$strTable .= '</tr>';
		if(is_array($orderList)){
			$region	= M('region')->getField('id,name');
			foreach($orderList as $k=>$val){
				$strTable .= '<tr>';
				$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_id'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['order_sn'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d',$val['start_time']).' </td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d',$val['end_time']).' </td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['address'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['nickname'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['number'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['tenancy'].'</td>';
				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mprice'].'</td>';
				$strTable .= '</tr>';
			}
		}
		$strTable .='</table>';
		unset($orderList);
		downloadExcel($strTable,'年月租订单统计表');
		exit();

		// dump($orderList);exit;
		//select sum(成绩) as 总分,count(*) from Student
		$sum =  M('Order')->where("order_status = 5")->sum('number');
		$sum_money =  M('Order')->where("order_status = 5")->sum('mprice');
		$this->assign('orderList',$orderList);
		$this->assign('sum',$sum);
		$this->assign('sum_money',$sum_money);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
















	/*****************************************************************报表导出结束**********************************************************&***/

	
}