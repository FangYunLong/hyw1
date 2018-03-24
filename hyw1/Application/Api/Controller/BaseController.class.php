<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Api\Controller;
use Think\Controller;
class BaseController extends Controller {
    public $session_id;
    public $cateTrre = array();
    const JSON = "json";
    /*
     * 初始化操作
     */
    public function _initialize() {


        //查询当前用户是否信息是否在内存中，是则为已登陆，否则相反
        /*$token=isset($_POST['token'])?$_POST['token']:'';
        if (empty($token)) {
            //若果等于空，那么就是非正常访问
            api_show('false','00000');exit();

        } else {
            //查询用户信息

        }*/
        $token  = isset($_POST['token']) ? $_POST['token'] : '';
        //if(session('?user'))
        //判断是否存在token
        /*if (!empty($token)) {
            $user   = S($token);//从缓存中获取用户信息。
            //$user = session('user');
            //$user = M('users')->where("user_id = {$user['user_id']}")->find();
			$user = M('users')->where("user_id = $user")->find();
            session('user',$user);  //覆盖session 中的 user
            //将用户信息存到缓存里面
            S($token,$user,7200);//缓存7200秒
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user',$user); //存储用户信息
            $this->assign('user_id',$this->user_id);
        }else{
            $nologin = array(
                'login','pop_login','do_login','logout','verify','set_pwd','finished',
                'verifyHandle','reg','send_sms_reg_code','identity','check_validate_code',
                'forget_pwd','check_captcha','check_username','send_validate_code','userlogin'
            );
            if(!in_array(ACTION_NAME,$nologin)){
                //返回登录页面登录
                //exit(json_encode(array('status'=>-1,'msg'=>'跳转登录页面登录')));
                /*header("location:".U('Home/User/login'));
                exit;*/
           // }
       // }

        $today['ip'] = $this->getIPaddress();
        $res = M('UserIp')->where(['ip'=>$today['ip'],'status'=>1])->find();
        if(!$res){
            $today['add_time'] = time();
            $today['status'] = 1;
            M('UserIp')->add($today);
        }


    	$this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        // 判断当前用户是否手机                
        if(isMobile())
            cookie('is_mobile','1',3600); 
        else 
            cookie('is_mobile','0',3600);

        // $this->overCheck();
        $this->public_assign();
    }

    public function getIPaddress()
    {

        $IPaddress='';

        if (isset($_SERVER)){

            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){

                $IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];

            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {

                $IPaddress = $_SERVER["HTTP_CLIENT_IP"];

            } else {

                $IPaddress = $_SERVER["REMOTE_ADDR"];

            }

        } else {

            if (getenv("HTTP_X_FORWARDED_FOR")){

                $IPaddress = getenv("HTTP_X_FORWARDED_FOR");

            } else if (getenv("HTTP_CLIENT_IP")) {

                $IPaddress = getenv("HTTP_CLIENT_IP");

            } else {

                $IPaddress = getenv("REMOTE_ADDR");

            }

        }
        return $IPaddress;
    }

    //检查验证码
    public function check_verify($mobile,$verify){
        //验证码在缓存中将手机号作为key，验证码为vlaue
        $code=S($mobile);
        
        if($code==$verify){
            return true;
        }else{
            return false;
        }
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
       $tp_config = M('config')->cache(true,TPSHOP_CACHE_TIME)->select();       
       foreach($tp_config as $k => $v)
       {
       	  if($v['name'] == 'hot_keywords'){
       	  	 $tpshop_config['hot_keywords'] = explode('|', $v['value']);
       	  }       	  
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }

       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,TPSHOP_CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();              
       $this->assign('brand_list', $brand_list);
       $this->assign('tpshop_config', $tpshop_config);          
    }

    /**
     * 按综合方式输出通信数据
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * @param string $type 数据类型
     * return string
     */
    public static function showApi($code, $message = '', $data = array(), $type = self::JSON) {
        if(!is_numeric($code)) {
            return '';
        }

        $type = isset($_GET['format']) ? $_GET['format'] : self::JSON;

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );

        if($type == 'json') {
            self::json($code, $message, $data);
            exit;
        } elseif($type == 'array') {
            var_dump($result);
        } elseif($type == 'xml') {
            self::xmlEncode($code, $message, $data);
            exit;
        } else {
            // TODO
        }
    }
    /**
     * 按json方式输出通信数据
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * return string
     */
    public static function json($code, $message = '', $data = array()) {

        if(!is_numeric($code)) {
            return '';
        }

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        echo json_encode($result);
        exit;
    }

    /**
     * 按xml方式输出通信数据
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * return string
     */
    public static function xmlEncode($code, $message, $data = array()) {
        if(!is_numeric($code)) {
            return '';
        }

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );

        header("Content-Type:text/xml");
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<root>\n";

        $xml .= self::xmlToEncode($result);

        $xml .= "</root>";
        echo $xml;
    }

    public static function xmlToEncode($data) {

        $xml = $attr = "";
        foreach($data as $key => $value) {
            if(is_numeric($key)) {
                $attr = " id='{$key}'";
                $key = "item";
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= is_array($value) ? self::xmlToEncode($value) : $value;
            $xml .= "</{$key}>\n";
        }
        return $xml;
    }

    public function fileUploadNews($path='',$files)
    {
        foreach($files as $k => $v){
            $str = date('Y-m-d',time()).'/'.md5(time().rand(1,999)).'.'.explode('.',$v['name'])[1];
            $filename = $v['tmp_name'];
            $destination  = $path.$str;
            if(!file_exists($destination)){
                $dirPath = $path.date('Y-m-d',time());
                $resb = mkdir($dirPath,0755,true);
            }
            move_uploaded_file($filename,$destination);
            $res[$k] = $str;
        }
        return $res;
    }    

    //计划任务-检查订单过期时间
    public function overCheck()
    {
        $fp = fopen('./Public/test.txt','a+');
        fwrite($fp,"\r\n".date('Y-m-d H:i:s',time()));
        fclose($fp);
        $basic = tpCache('basic');
        $order_over_time = $basic['hot_keywords']*60*60; 
        $temp_over_time  = $basic['hot_keywords1']*60*60;

        //年月租订单过期检查
        $sql   = "SELECT order_id,user_id,order_sn,grab_number 
                  FROM tp_order 
                  WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(add_time) >= {$order_over_time} AND is_over_time = 0 AND order_status = 0";
        $Order = M('')->query($sql);

        if($Order){
            foreach($Order as $k => $v){
                $order_id_str[] = $v['order_id'];
            }

            $order_id_str = implode(',',$order_id_str);
            $sql  = "UPDATE tp_order SET is_over_time = 1,is_completed = 1,order_status = (CASE WHEN grab_number = 0 THEN 2 WHEN grab_number > 0 THEN 3 END) WHERE order_id IN({$order_id_str})";
            $res = M('')->execute($sql);

            if($res){
                foreach($Order as $k => $v){
                    if($v['grab_number'] == 0){
                        $type = ['type'=>3,'order_id'=>$v['order_id'],'order_status'=>2];
                        $content = '您有订单无人接单而失效，点击查看详情！';
                        $content1 = $v['order_sn'] . '订单无人接单，订单失效！';
                        Jpush($v['user_id'],$type,$content,$content1,true);//年月租过期无人接单，消息推送
                    }
                }
            }
        }


        //临时租订单过期检查
        $sql   = "SELECT temp_id,user_id,temp_sn 
                  FROM tp_temporary 
                  WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(add_time) >= {$temp_over_time} AND status = 1";
        $Temp = M('')->query($sql);

        if($Temp){
            foreach($Temp as $k => $v){
                $temp_id_str[] = $v['temp_id'];
            }

            $temp_id_str = implode(',',$temp_id_str);
            $sql  = "UPDATE tp_temporary SET status = 4 WHERE temp_id IN({$temp_id_str})";
            $res = M('')->execute($sql);

            if($res){
                foreach($Temp as $k => $v){
                    $type = ['type'=>11,'temp_id'=>$v['temp_id']];
                    $content = '您有订单无人接单而失效，点击查看详情！';
                    $content1 = $v['temp_sn'] . '订单无人接单，订单失效！';
                    Jpush($v['user_id'],$type,$content,$content1,true);//临时租过期无人接单，消息推送
                }
            }
        }

    }

}