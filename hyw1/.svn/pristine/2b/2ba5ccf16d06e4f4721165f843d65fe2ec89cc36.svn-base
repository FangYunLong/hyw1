<?php
namespace Admin\Controller;
use Think\AjaxPage;

class DistributController extends BaseController{
   
    public function DistributList()
    {
    	$this->display('index');
    }

    /*
     *Ajax首页
     */
    public function ajaxindex(){

        $create_time = I('create_time');
        if($create_time){
            $gap = explode('-', $create_time);
            // $begin = $gap[0];
            // $end = $gap[1];
            $begin = strtotime($gap[0]);
            $end = strtotime($gap[1]);
        }
        // 搜索条件
        $condition = array();
        if($begin && $end){
            $condition['create_time'] = array('between',"$begin,$end");
        }
        I('mobile1') ? $condition['u1.mobile'] = trim(I('mobile1')) : false;
        I('mobile2') ? $condition['u2.mobile'] = trim(I('mobile2')) : false;
        I('order_sn') ? $condition['order_sn'] = trim(I('order_sn')) : false;

        $count = M('RebateLog')->alias('rl')
                               ->join('tp_users as u1 on rl.user_id=u1.user_id')
                               ->join('tp_users as u2 on rl.buy_user_id=u2.user_id')
                               ->where($condition)
                               ->count();
        $Page  = new AjaxPage($count,20);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =  urlencode($val);
        }        
        $show = $Page->show();

        //获取订单列表
    	$List = M('RebateLog')->alias('rl')
                              ->field('u1.mobile as mobile1,u2.mobile as mobile2,rl.order_sn,rl.goods_price,rl.money,create_time')
                              ->join('tp_users as u1 on rl.user_id=u1.user_id')
                              ->join('tp_users as u2 on rl.buy_user_id=u2.user_id')
                              ->where($condition)
                              ->order('rl.create_time DESC')
                              ->limit($Page->firstRow,$Page->listRows)
                              ->select();
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('List',$List);
        $this->display();
    }   

    //提现列表
    public function extractMoney()
    {
        $money_status = C('money_status');                                 
        $this->assign('money_status',$money_status);      
        $this->display();
    } 

    //ajax列表
    public function ajaxExtractMoney()
    {
        $add_time = I('add_time');
        if($add_time){
            $gap = explode('-', $add_time);
            $begin = strtotime($gap[0]);
            $end = strtotime($gap[1]);
        }

        // 搜索条件
        $condition = array();
        if($begin && $end){
            $condition['em.add_time'] = array('between',"$begin,$end");
        }   
        I('mobile1') ? $condition['u1.mobile'] = trim(I('mobile1')) : false;
        I('status') ? $condition['em.status'] = trim(I('status')) : false;
        I('name') ? $condition['em.name'] = array('like','%'.trim(I('name')).'%') : false;
        I('bank_account') ? $condition['em.bank_account'] = trim(I('bank_account')) : false;

        $count = M('ExtractMoney')->alias('em')
                                  ->join('tp_users as u1 on em.user_id=u1.user_id')
                                  ->where($condition)
                                  ->count();
        $Page  = new AjaxPage($count,20);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =  urlencode($val);
        }        
        $show = $Page->show();

        //获取提现列表
        $List = M('ExtractMoney')->alias('em')
                                 ->field('em.id,em.name,em.bank_account,u1.mobile,em.amount,em.add_time,em.status')
                                 ->join('tp_users as u1 on em.user_id=u1.user_id')
                                 ->where($condition)
                                 ->limit($Page->firstRow,$Page->listRows)
                                 ->select();

        $money_status = C('money_status');                                 
        $this->assign('money_status',$money_status);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('List',$List);
        $this->display();
    }  

    //提现处理
    public function actionExtract()
    {
        $id   = I('post.id');
        $type = I('post.type');
        if($type==1){
            $data['status'] = 2;
        }else{
            $data['status'] = 3;
        }
        $data['id'] = $id;
        $ExtractMoney = M('ExtractMoney')->find($id);
        if(!$ExtractMoney){
            echo 0;exit;
        }
        if($ExtractMoney['status']>1){
            echo 1;exit;
        }        
    try{
        M('')->startTrans(); 
        $res = M('ExtractMoney')->save($data);
        if($type==2){
            if(!$res){
                echo 0;exit;
            }
            $Users = M('Users')->find($ExtractMoney['user_id']);
            $userData['actual_money'] = $Users['actual_money'] + $ExtractMoney['amount'];
            $userData['user_id'] = $ExtractMoney['user_id'];
            M('Users')->save($userData);
        }
        M('')->commit(); 
          echo 1;exit;
      }catch(\Exception $e){
        M('')->rollback();
          echo 0;exit;
      }
    }   
}