<?php
/**
 *
//
 * Author: 当燃      
*: 2015-09-09
 */
namespace Admin\Controller;


class IndexController extends BaseController {

    public function index(){
        $this->pushVersion();
        $act_list = session('act_list');
        $menu_list = getMenuList($act_list);
       //dump($menu_list);die;
        $this->assign('menu_list',$menu_list);
        $admin_info = getAdminInfo(session('admin_id'));
		$order_amount = M('order')->where("order_status=0 and (pay_status=1 or pay_code='cod')")->count();
		$this->assign('order_amount',$order_amount);
		$this->assign('admin_info',$admin_info);
        $this->display();
    }
    //广告列表
    public function ad() {
        $data = M('Ad')->field('ad_id,ad_name,ad_link,ad_code,start_time,ad_address')->select();
        foreach ($data as $k=>$v) {
            $data[$k]['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
        }
        $this->assign('ad',$data);
        //dump($data);exit;
        $this->display();
    }
    //test图片上传
    public function addAd1()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['start_time'] = strtotime($data['begin']);
            //上传图片
            if($_FILES['thumb']['size']>0){
                //定义上传路径
                $path="./Public/Upload/ad/";
                $uploadinfo=$this->fileUpload($path);
                //dump($uploadinfo);die;
                $data['ad_code0']='http://hyw.web66.cn:8090/Public/Upload/ad/'. $uploadinfo[0]['savepath'].$uploadinfo[0]['savename'];
                $data['ad_code1']='http://hyw.web66.cn:8090/Public/Upload/ad/'. $uploadinfo[1]['savepath'].$uploadinfo[1]['savename'];
                $data['ad_code2']='http://hyw.web66.cn:8090/Public/Upload/ad/'. $uploadinfo[2]['savepath'].$uploadinfo[2]['savename'];
                $data['ad_code3']='http://hyw.web66.cn:8090/Public/Upload/ad/'. $uploadinfo[3]['savepath'].$uploadinfo[3]['savename'];

                /*//缩略图
                $image = new \Think\Image();
                //拼接保存路径及名字
                $img=$uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];
                $sm_path=$path.$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];
                $image->open('./Public/Upload/knowledge/'.$img);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $image->thumb(150, 150)->save($sm_path);

                //拼变量
                $data['small']=$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];*/
            }

            $r = D('ad')->add($data);
            if ($r)
                $this->success('添加广告成功');
                //exit('tianjiachenggong');
            $this->error('添加广告失败');
        }
        $this->display();
    }
    //添加广告
    public function addAd()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['start_time'] = strtotime($data['begin']);
            //上传图片
            if($_FILES['thumb']['size']>0){
                //定义上传路径
                $path="./Public/Upload/ad/";
                $uploadinfo=$this->fileUpload($path);
                $data['ad_code']='http://hyw.web66.cn:8090/Public/Upload/ad/'. $uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];

                /*//缩略图
                $image = new \Think\Image();
                //拼接保存路径及名字
                $img=$uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];
                $sm_path=$path.$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];
                $image->open('./Public/Upload/knowledge/'.$img);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $image->thumb(150, 150)->save($sm_path);

                //拼变量
                $data['small']=$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];*/
            }
            $r = D('ad')->add($data);
            if ($r)
                $this->success('添加广告成功');
            //exit('tianjiachenggong');
            $this->error('添加广告失败');
        }
        $this->display();
    }
    //编辑广告
    public function editAd()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['start_time'] = strtotime($data['begin']);
            //上传图片
            if($_FILES['thumb']['size']>0){
                //定义上传路径
                $path="./Public/Upload/ad/";
                $uploadinfo=$this->fileUpload($path);
                $data['ad_code']='http://hyw.web66.cn:8090/Public/Upload/ad/'. $uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];

                /*//缩略图
                $image = new \Think\Image();
                //拼接保存路径及名字
                $img=$uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];
                $sm_path=$path.$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];
                $image->open('./Public/Upload/knowledge/'.$img);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $image->thumb(150, 150)->save($sm_path);

                //拼变量
                $data['small']=$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];*/
            }

            //$r = M('ad')->where('ad_id='.$data['ad_id'])->save($data);

            $r = M('ad')->where(array('ad_id'=>$data['ad_id']))->save($data);
            if($r)
                $this->success('修改广告成功');
            //exit('tianjiachenggong');
            $this->error('修改广告失败');
        }
        $data= I('get.');
        $data = M('Ad')->field('ad_id,ad_name,ad_link,ad_code,start_time,ad_address')->where(array('ad_id'=>$data['id']))->find();
        $data['start_time'] = date("Y-m-d",$data['start_time']);
        $this->assign('ad',$data);
        $this->display();
    }
    //删除广告
    public function delAd()
    {
        $data = I('post.');
        $data['start_time'] = strtotime($data['begin']);
        $r = D('ad')->where('ad_id='.$data['del_id'])->delete();
        if($r)
            if ($r)
                $this->success('删除广告成功');
        //exit('tianjiachenggong');
        $this->error('删除广告失败');
    }
   
    public function welcome(){
    	$this->assign('sys_info',$this->get_sys_info());
    	$today = strtotime("-1 day");
    	$count['handle_order'] = M('order')->where("order_status=0 and (pay_status=1 or pay_code='cod')")->count();//待处理订单
    	$count['new_order'] = M('order')->where("add_time>$today")->count();//今天新增订单
    	$count['goods'] =  M('goods')->where("1=1")->count();//商品总数
    	$count['article'] =  M('article')->where("1=1")->count();//文章总数
    	$count['users'] = M('users')->where("1=1")->count();//会员总数
    	$count['today_login'] = M('users')->where("last_login>$today")->count();//今日访问
    	$count['new_users'] = M('users')->where("reg_time>$today")->count();//新增会员
    	$count['comment'] = M('comment')->where("is_show=0")->count();//最新评论
    	$this->assign('count',$count);
        $this->display();
    }
    
    public function get_sys_info(){
		$sys_info['os']             = PHP_OS;
		$sys_info['zlib']           = function_exists('gzclose') ? 'YES' : 'NO';//zlib
		$sys_info['safe_mode']      = (boolean) ini_get('safe_mode') ? 'YES' : 'NO';//safe_mode = Off		
		$sys_info['timezone']       = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
		$sys_info['curl']			= function_exists('curl_init') ? 'YES' : 'NO';	
		$sys_info['web_server']     = $_SERVER['SERVER_SOFTWARE'];
		$sys_info['phpv']           = phpversion();
		$sys_info['ip'] 			= GetHostByName($_SERVER['SERVER_NAME']);
		$sys_info['fileupload']     = @ini_get('file_uploads') ? ini_get('upload_max_filesize') :'unknown';
		$sys_info['max_ex_time'] 	= @ini_get("max_execution_time").'s'; //脚本最大执行时间
		$sys_info['set_time_limit'] = function_exists("set_time_limit") ? true : false;
		$sys_info['domain'] 		= $_SERVER['HTTP_HOST'];
		$sys_info['memory_limit']   = ini_get('memory_limit');		
        $sys_info['version']   	    = file_get_contents('./Application/Admin/Conf/version.txt');
		$mysqlinfo = M()->query("SELECT VERSION() as version");
		$sys_info['mysql_version']  = $mysqlinfo['version'];
		if(function_exists("gd_info")){
			$gd = gd_info();
			$sys_info['gdinfo'] 	= $gd['GD Version'];
		}else {
			$sys_info['gdinfo'] 	= "未知";
		}
		return $sys_info;
    }
    
    
    public function pushVersion()
    {            
        if(!empty($_SESSION['isset_push']))
            return false;    
        $_SESSION['isset_push'] = 1;    
        error_reporting(0);//关闭所有错误报告
        $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
        $curent_version = file_get_contents($version_txt_path);

        $vaules = array(            
                'domain'=>$_SERVER['SERVER_NAME'], 
                'last_domain'=>$_SERVER['SERVER_NAME'], 
                'key_num'=>$curent_version, 
                'install_time'=>INSTALL_DATE,
                'serial_number'=>SERIALNUMBER,
         );     
         $url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
         stream_context_set_default(array('http' => array('timeout' => 3)));
         file_get_contents($url);         
    }
    
    /**
     * ajax 修改指定表数据字段  一般修改状态 比如 是否推荐 是否开启 等 图标切换的
     * table,id_name,id_value,field,value
     */
    public function changeTableVal(){  
            $table = I('table'); // 表名
            $id_name = I('id_name'); // 表主键id名
            $id_value = I('id_value'); // 表主键id值
            $field  = I('field'); // 修改哪个字段
            $value  = I('value'); // 修改字段值                        
            M($table)->where("$id_name = $id_value")->save(array($field=>$value)); // 根据条件保存修改的数据
    }	    

}