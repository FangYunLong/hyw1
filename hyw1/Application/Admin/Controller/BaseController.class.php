<?php

/**
 *
//
 * Author: 当燃
*: 2015-09-09
 */

namespace Admin\Controller;
use Think\Controller;
use Admin\Logic\UpgradeLogic;
class BaseController extends Controller {

    /**
     * 析构函数
     */
    function __construct() 
    {
        parent::__construct();
        $upgradeLogic = new UpgradeLogic();
        $upgradeMsg = $upgradeLogic->checkVersion(); //升级包消息        
        $this->assign('upgradeMsg',$upgradeMsg);    
        //用户中心面包屑导航
        $navigate_admin = navigate_admin();
        $this->assign('navigate_admin',$navigate_admin);
        tpversion();        
   }    
    
    /*
     * 初始化操作
     */
    public function _initialize() 
    {
      // $Ip = substr(getIpAddress(),0,6);
      // $ip_array = ['116.22'];
      // if(!in_array($Ip,$ip_array)){
      // 	echo $Ip;
      //   echo '<meta charset="utf-8">';
      //   echo '系统正在维护，请稍后访问！！！！！！';exit;
      // }
        $this->assign('action',ACTION_NAME);
        //过滤不需要登陆的行为
        if(in_array(ACTION_NAME,array('login','logout','vertify')) || in_array(CONTROLLER_NAME,array('Ueditor','Uploadify'))){
        	//return;
        }else{
        	if(session('admin_id') > 0 ){
        		$this->check_priv();//检查管理员菜单操作权限
        	}else{
        		$this->error('请先登陆',U('Admin/Admin/login'),1);
        	}
        }
        $this->public_assign();
    }

    //定义一个上传方法
    public function fileUpload ($path)
    {
        //实例化对象
        $upload=new \Think\Upload();
        //设置上传参数
        $upload->maxSize=3145728; //默认1024*1024*3
        $upload->exts=array('jpg','png','jpeg','gif','doc','docx');
        //保存上传路径
        //$upload->rootPath="./Public/Upload/Doc/";
        $upload->rootPath=$path;
        //上传文件
        $info=$upload->upload();
        //判断是否成功
        //如果$info为空，则上传失败
        if(empty($info)){
           // $this->error($upload->getError());
            return $info;
        }
        else {
            return $info;
        }
    }
    
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign()
    {
       $tpshop_config = array();
       $tp_config = M('config')->select();       
       foreach($tp_config as $k => $v)
       {
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }
       $this->assign('tpshop_config', $tpshop_config);       
    }
    
    public function check_priv()
    {
        $ctl = CONTROLLER_NAME;
        $act = ACTION_NAME;
        $act_list = session('act_list');
        //无需验证的操作
        $uneed_check = ['login','SpecialCarInfo','level','levelHandle','editCategory','delGoodsCategory','roleSave','delete','adminHandle','role_info','handle','linkHandle','add_user','detail','link','admin_info','logout','vertifyHandle','vertify','imageUp','upload','login_task'];

        $check = ['adminHandle'];

        if($ctl == 'Index' || $act_list == 'all'){
            //后台首页控制器无需验证,超级管理员无需验证
            return true;
        }elseif(IS_AJAX || in_array($act,$uneed_check)){
            //所有ajax请求不需要验证权限
            return true;
        }else{
            $right = M('system_menu')->where("id in ($act_list)")->cache(true)->getField('right',true);
            foreach ($right as $val){
                $role_right .= $val.',';
            }
            $role_right = explode(',', $role_right);
    		//检查是否拥有此操作权限
    		if(!in_array($ctl.'Controller@'.$act, $role_right)){
                $this->error('您没有操作权限,请联系超级管理员分配权限');
    			// $this->error('您没有操作权限,请联系超级管理员分配权限',U('Admin/Index/welcome'));
    		}
    	}
    }
}