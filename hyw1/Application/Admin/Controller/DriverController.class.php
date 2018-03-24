<?php
namespace Admin\Controller;
use Think\AjaxPage;

class DriverController extends BaseController{

    public  $xueli;
    public  $jingyan;
    public  $sex;
    /*
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
        C('TOKEN_ON',false); // 关闭表单令牌验证
        $this->xueli = C('xueli');
        $this->jingyan = C('jingyan');
        $this->sex = C('sex');
        $this->temp_status = C('temp_status');

        // 学历 经验 性别
        $this->assign('xueli',$this->xueli);
        $this->assign('jingyan',$this->jingyan);
        $this->assign('sex',$this->sex);
        $this->assign('temp_status',$this->temp_status);
    }
   
    public function driverList()
    {
    	$this->display('index');
    }

    /*
     *Ajax首页
     */
    public function ajaxindex(){

        // 搜索条件
        $condition = array();
        // I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
        // if($begin && $end){
        //     $condition['add_time'] = array('between',"$begin,$end");
        // }
        I('user_name') ? $condition['user_name'] = trim(I('user_name')) : false;
        I('mobile') ? $condition['mobile'] = trim(I('mobile')) : false;
        I('sex') ? $condition['sex'] = intval(I('sex')) : false;
        I('jingyan') != '' ? $condition['jingyan'] = intval(I('jingyan')) : false;
        I('xueli') != '' ? $condition['xueli'] = trim(I('xueli')) : false;
        I('city') != '' ? $city=trim(I('city')) : false;
        I('city') != '' ? $condition['city'] = array('like',"%$city%") : false;
        $sort = 'add_time DESC';
        // $condition['is_hidden'] = 0;
        $count = M('Resume')->where($condition)->count();
        $Page  = new AjaxPage($count,20);
        $show = $Page->show();
        //获取求职列表
    	$diverList = M('Resume')->where($condition)->limit($Page->firstRow,$Page->listRows)->order($sort)->select();
        $this->assign('page',$show);// 赋值分页输出
        // dump($diverList);exit;
        $this->assign('jingyan',C('jingyan'));
        $this->assign('diverList',$diverList);
        $this->display();
    }

     /*
      * 司机求职详情
      */
    public function driver_info($user_id)
    {
        $Users = M('Resume')->where(array('user_id'=>$user_id))->find();
        $this->assign('users', $Users);
        $this->display();
    }

    /*
     * 司机求职删除
     */
    public function driver_del()
    {
        $user_id =$_GET['id'];
        $res = M('Resume')->where(array('user_id'=>$user_id))->delete();
        if($res){
            $return_arr = array('status' => 1,'msg' => '操作成功');
        }else{
            $return_arr = array('status' => -1,'msg' => '操作失败');
        }
        $this->ajaxReturn(json_encode($return_arr));
    }








}