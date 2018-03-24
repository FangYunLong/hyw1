<?php
/**
 * Created by PhpStorm.
 * User: Lonelytears
*: 2016/12/12 0012
 * Time: 下午 4:54
 */
namespace Api\Controller;

use Home\Logic\UsersLogic;
use Think\Page;
use Think\Verify;

class LoginController extends BaseController {
    public $user_id = 0;
    public $user = array();
/*
 * 初始化设置
 */
    /*public function _initialize() {
        parent::_initialize();
        $token  = isset($_POST['token']) ? $_POST['token'] : '';
        //if(session('?user'))
        //判断是否存在token
        if (!empty($token)) {
            $user   = S($token);//从缓存中获取用户信息。
            //$user = session('user');
            $user = M('users')->where("user_id = {$user['user_id']}")->find();
            session('user',$user);  //覆盖session 中的 user
            //将用户id信息存到缓存里面
            S($token,$user['user_id'],7200);//缓存7200秒
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user',$user); //存储用户信息
            $this->assign('user_id',$this->user_id);
        }else{
            $nologin = array(
                'login','pop_login','do_login','logout','verify','set_pwd','finished',
                'verifyHandle','reg','send_sms_reg_code','identity','check_validate_code',
                'forget_pwd','check_captcha','check_username','send_validate_code','userlogin','demo','sms_code'
            );
            if(!in_array(ACTION_NAME,$nologin)){
                header("location:".U('Home/User/login'));
                exit;
            }
        }
    }*/

    /**
     * 用户经纬度
     * token\lat(纬度)\lon(经度)
    */
    public function  latlon()
    {
        $token = I('post.token');
        $user_id = S($token);
        //将经纬度地址信息存入到地址表里
        $ad = M('Address')->where(array('user_id'=>$user_id))->find();
        $address['lat'] = I('post.lat');//纬度
        $address['lon'] = I('post.lon');//经度
        $address['lasttime'] = date('Y-m-d H:i:s',time());//时间
        $address['user_id'] = $user_id;//用户id
        if ($ad) {
            //若果存在地址信息，则执行更新操作
           $res = M('Address')->where(array('user_id'=>$user_id))->save($address);
            if ($res) {
                exit(json_encode(array('status'=>1,'msg'=>'地址更新成功')));
            }else{
                exit(json_encode(array('status'=>-1,'msg'=>'地址更新失败')));
            }
        }else {
            $res = M('Address')->where(array('user_id'=>$user_id))->add($address);
            if ($res) {
                exit(json_encode(array('status'=>1,'msg'=>'地址添加成功')));
            }else{
                exit(json_encode(array('status'=>-1,'msg'=>'地址添加失败')));
            }
        }
    }

    /**
     * 登录
     *\username\password\level_id
     * post
     */
    public function userlogin ()
    {
        $data   = I('POST.');

        $username = isset($data['username']) ? trim($data['username']) : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $level_id = isset($_POST['level_id']) ? $data['level_id'] : '';
        //从用户表获取用户信息
        //$user = M('users')->where("mobile='{$username}' OR email='{$username}'")->find();
        $user = M('users')->where(array('mobile'=>$username))->find();
        if(!$user){
            $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif(encrypt($password) != $user['password']){
            $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['is_lock'] == 1) {
            $result = array('status' => -3, 'msg' => '账号异常已被锁定！！！');
        }else{
            //查询用户信息之后, 查询用户的登记昵称
            $levelId = $user['level_id'];
            $levelName = M("user_level")->where("level_id = {$levelId}")->getField("level_name");
            
            $user['level_name'] = $levelName;
            $res    = array(
                'user_id'  => $user['user_id'],
                'nickname' => $user['nickname'],
                'mobile'   => $user['mobile'],
                'token'    => $user['token'],
                'level_id' => $user['level_id'],
                'head_pic' => $user['head_pic']
                );
            $result = array('status'=>1,'msg'=>'登陆成功','result'=>$res);//将用户数组返回

           /* //将经纬度地址信息存入到地址表里
            $ad = D('Address')->where(array('user_id'=>$user['user_id']))->find();
            $address['lat'] = I('post.lat');//纬度
            $address['lon'] = I('post.lon');//经度
            $address['lasttime'] = time();//时间
            $address['user_id'] = $user['user_id'];//用户id
            if ($ad) {
                //若果存在地址信息，则执行更新操作
                D('Address')->where(array('user_id'=>$user['user_id']))->save($address);
            }else {
                D('Address')->where(array('user_id'=>$user['user_id']))->add($address);
            }*/
        }

        $UsersData['user_id']    = $user['user_id'];
        $UsersData['identifier'] = $data['identifier'];
        $UsersData['login_status'] = 1;

        //绑定设备标识,用于消息推送
        if($user['identifier']!=$data['identifier']||empty($user['identifier'])){
            //新设备登录，推送消息
            if($user['identifier']!=$data['identifier']){
                // $receiver = array('registration_id'=>array($user['identifier']));
                $content  = '您的帐号于'.date('Y-m-d H:i:s',time()).'在新设备登录，如非本人操作，请尽快更改密码！';
                $type   = array('type'=>1);
                Jpush($user['user_id'],$type,$content);
                // push($receiver,$content,$extras);
            }
        }   
        $UsersData = array_filter($UsersData);
        M('Users')->save($UsersData);
        
        //将用户信息存到session里
        session('user',$user);
        //生成一个token，并返回给app端
        $token   = $user['token'];
        //将用户信息存到缓存里面
        S($token,$user['user_id'],2592000);//缓存一天

        exit(json_encode($result));
    }

    /**
     * 退出登录
     *
     */
    public function logout ()
    {
        $token   = I('post.token');
        $user_id = S($token);
        if(!$user_id){
            exit(['status'=>-2,'msg'=>'缺少token']);
        }
        $UsersData['user_id'] = $user_id;
        $UsersData['login_status'] = 0;
        $res = M('Users')->save($UsersData);
        if($res){
            $result = array('status'=>1,'msg'=>'退出成功');
        }else{
            $result = array('status'=>-1,'msg'=>'退出失败');
        }
        exit(json_encode($result));
    }


    /**
     * 注册
     * username、password、password2、$level_id、$code
     */
    public function user_register()
    {
        $username  = I('post.username','');
        $password  = I('post.password','');
        $password2 = I('post.password2','');
        $code      = I('post.code','');
        $level_id  = I('post.level_id','');
        $mobile2   = I('post.mobile2');

        // if(!$code){
        //     exit(json_encode(array('status'=>-1,'msg'=>'验证码不能为空')));
        // }
        //验证验证码
        // $res = $this->check_verify($mobile,$code);
        
        // if (!$res)
        //     exit(json_encode(array('status'=>-1,'msg'=>'验证码错误')));
        if(!$username || !$password)
            exit(json_encode(array('status'=>-1,'msg'=>'请输入用户名或密码'))) ;
        //验证两次密码是否匹配
        if($password != $password2)
            exit(json_encode(array('status'=>-1,'msg'=>'两次输入密码不一致')) );
        //验证是否存在用户名
        $check = M('Users')->where(array('mobile'=>$username))->find();
        if($check)
            exit(json_encode(array('status'=>-1,'msg'=>'账号已存在'))) ;
        if(!empty($mobile2)){
            $UsersReferees   = M('Users')->where(['mobile'=>$mobile2])->find();
            if(!$UsersReferees){
                exit(json_encode(array('status'=>-1,'msg'=>'没有该推荐人'))) ;
            }
        }
        $map['password'] = encrypt($password);
        $map['reg_time'] = time();
        //判断是否车主注册
        if ($level_id==2) {
            //调用上传类，上传执照
            if ($_FILES['file']['size']>0) {
                //定义上传路径
                $path="./Public/Upload/reg/";
                $uploadinfo  = $this->fileUploadNews($path,$_FILES);
                $map['cart_path']  = 'http://hyw.web66.cn:8092/Public/Upload/reg/'.$uploadinfo['file'];
            }else {
                //没有上传文件
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有上传营业执照')));
            }
        }
        
        $map['token']      = md5(time().mt_rand(1,99999)); //生成token
        $map['zhanghu']    = uniqid();                     //生成账户号----13位,利用uniqid()方法生成
        $map['level_id']   = $level_id;                    //添加用户角色
        $map['mobile']     = $username;                    //手机号
        $map['mobile2']    = I('post.mobile2');            //推荐人手机号
        $map['sex']        = I('post.sex');            //推荐人手机号
        $user_id = M('Users')->add($map);                  //将用户信息写入数据库
        
        if(!$user_id)
           exit(json_encode(array('status'=>-1,'msg'=>'注册失败')));
        $user = M('users')->where("user_id = {$user_id}")->find();
        exit(json_encode(array('status'=>1,'msg'=>'注册成功')));
    }

    /**
     * 短信验证码
     * mobile
    */
    public function sms_code()
    {
        $mobile = I('post.mobile');
        $mobile = '13539919834';

        $code = mt_rand(100000,999999);
        $content ='感谢您使用好运旺平台，您的验证码为：'.$code.'【好运旺】';
        $sms = send_sms_code($mobile,$content);
        if ($sms) {
            S($mobile,$code,900);
            exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收','code'=>$code)));
        } else {
            exit(json_encode(array('status'=>-1,'msg'=>'验证码发送失败')));
        }
    }


    /**
     * 发送手机注册验证码
     */
   /* public function send_sms_reg_code(){
        $mobile = I('mobile');
        $userLogic = new UsersLogic();
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        //查看短时间内是否已经发送过验证码
        //$send = $this->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }*/

    /**
     * app发送短信验证码记录
     * @param $mobile
     * @param $code
     * @param $session_id
     */
   /* public function sms_log($mobile,$code,$session_id){
        //判断是否存在验证码
        $data = M('sms_log')->where(array('mobile'=>$mobile,'session_id'=>$session_id))->order('id DESC')->find();
        //获取时间配置
        $sms_time_out = tpCache('sms.sms_time_out');
        $sms_time_out = $sms_time_out ? $sms_time_out : 120;
        //120秒以内不可重复发送
        if($data && (time() - $data['add_time']) < $sms_time_out)
            return array('status'=>-1,'msg'=>$sms_time_out.'秒内不允许重复发送');
        $row = M('sms_log')->add(array('mobile'=>$mobile,'code'=>$code,'add_time'=>time(),'session_id'=>$session_id));
        if(!$row)
            return array('status'=>-1,'msg'=>'发送失败');
        //$send = sendSMS($mobile,'您好，你的验证码是：'.$code);
        $send = sendSMS($mobile,$code);
        if(!$send)
            return array('status'=>-1,'msg'=>'发送失败');
        return array('status'=>1,'msg'=>'发送成功');
    }*/

    /**
     * 找回密码
     * username、code
    */
    public function findPassword()
    {
        $code = I('post.code','');
        $mobile = I('post.username');
        $level_id = I('post.level_id');
        //$code='6279';
        //$mobile = '18312706605';
        if(empty($mobile)){
            exit(json_encode(array('status'=>-1,'msg'=>'手机号不能为空')));
        }
        if(!$code){
            exit(json_encode(array('status'=>-1,'msg'=>'验证码不能为空')));
        }
        //验证验证码
        $res = $this->check_verify($mobile,$code);
        if (!$res){
            exit(json_encode(array('status'=>-1,'msg'=>'验证码错误')));
        }

        /*//是否开启注册短信验证码机制
        if(check_mobile($username) && tpCache('sms.regis_sms_enable')){
            if(!$code)
                $this->error('请输入短信验证码');
            $check_code = $logic->sms_code_verify($username,$code,$this->session_id);
            if($check_code['status'] != 1)
                exit(json_encode(array('status'=>$check_code['status'],'msg'=>$check_code['msg'])));
            //$this->error($check_code['msg']);
            //else
                //exit(json_encode(array('status'=>$check_code['status'],'msg'=>$check_code['msg'])));
        }*/
        $info=M('Users')->where(array('mobile'=>$mobile))->find();
        //$sql="select userkey from phome_enewsmember where username='$username'";
        //$info=$empire->fetch1($sql);
        $data=$info['token'];
        S($info['token'].'reset_pwd','1',600);//把凭证存到缓存中
       // api_show('true','20010',array('data'=>$data));//返回userkey
        exit(json_encode(array('status'=>1,'msg'=>'验证码正确','token'=>$data)));

    }

    /**
     * 重置密码
     * password\password2\token\level_id
     *
    */
    public function rePassword()
    {
        $userkey=isset($_POST['token'])?$_POST['token']:'';
        $user_id = S($userkey);
        $check=S($userkey.'reset_pwd');
        $level_id = I('post.level_id');

        $user_id = M('Users')->field('user_id,level_id')->where(array('token'=>$userkey))->find();

        $password   =  I('post.password');
        $password2   = I('post.password2');
        
        if ($password !== $password2) {
            exit(json_encode(array('status'=>-1,'mag'=>'两次输入密码不一样')));
        } else {
            //将密码加密，然后保存
            $data['password'] = encrypt($password);
            $res = M('Users')->where(array('user_id'=>$user_id['user_id']))->save($data);
            //dump($res);
            if (!$res)
                exit(json_encode(array('status'=>-1,'msg'=>'重置密码失败')));
            else
                exit(json_encode(array('status'=>1,'msg'=>'重置密码成功')));
        }
    }
}