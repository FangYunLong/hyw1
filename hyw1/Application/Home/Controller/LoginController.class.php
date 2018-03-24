<?php
/**
 *
//
 * 微信交互类
 */
namespace Home\Controller;
use Think\Controller;
use Home\Logic\UsersLogic;
header("Content-Type:text/html; charset=UTF-8");

class LoginController extends BaseController{

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
        // $level_id = isset($_POST['level_id']) ? $data['level_id'] : '';
        //从用户表获取用户信息
        //$user = M('users')->where("mobile='{$username}' OR email='{$username}'")->find();
        $user = M('users')->where(array('mobile'=>$username))->find();
        if(!$user){
            exit("<script>history.go(-1);alert('账号不存在');</script>");
        }elseif(encrypt(md5($password)) != $user['password']){
            exit("<script>history.go(-1);alert('密码错误!');</script>");
        }elseif($user['is_lock'] == 1) {
            exit("<script>history.go(-1);alert('账号异常已被冻结0！！！');</script>");
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
            //将用户信息存到session里
            session('user',$user,7200);

            $html_arr = glob("./Application/Runtime/Html/*.html");
            foreach ($html_arr as $key => $val)
            {
                strstr($val,'Home_Index_index.html') && unlink($val); // 首页                    
                strstr($val,'Home_Goods_goodsList') && unlink($val); // 列表页
                strstr($val,'Home_Channel_index') && unlink($val);  // 频道页
                strstr($val,'Index_Article_articleList') && unlink($val);  // 文章列表页
                strstr($val,'Index_Article_detail') && unlink($val);  // 文章详情
                strstr($val,'Doc_Index_index_') && unlink($val);  // 文章列表页                    
                strstr($val,'Doc_Index_article_') && unlink($val);  // 文章详情                                        
            }    
            $url = session('prev_url');
            if($url){
                header("location:".$url);
            }else{
                header("location:".U('Home/Index/index'));
            }    
            // exit;
            // exit("<script>history.go(-2);location.reload()</script>");
        }

        
    }

    public function login()
    {
        $this->display();
    }

    public function regist()
    {
        $this->display();
    }

    /**
     * 注册
     * username、password、password2、$level_id、$code
     */
    public function user_register()
    {
        $username  = I('post.username','');
        $mobile    = $username;
        $password  = I('post.password','');
        $password2 = I('post.password2','');
        $code      = I('post.code','');
        $level_id  = I('post.level_id','');
        $mobile2   = I('post.mobile2');

        if(!$code){
            exit("<script>history.go(-1);alert('验证码不能为空！');</script>");
        }
        // //验证验证码
        // $res = check_verify($mobile,$code);
        
        // if (!$res)
        //     exit("<script>history.go(-1);alert('验证码错误！');</script>");
        if(!$username || !$password)
            exit("<script>history.go(-1);alert('请输入手机号或密码！');</script>");
        //验证两次密码是否匹配
        if($password != $password2)
            exit("<script>history.go(-1);alert('两次输入密码不一致！');</script>");
        //验证是否存在用户名
        $check = M('Users')->where(array('mobile'=>$username))->find();
        if($check)
            exit("<script>history.go(-1);alert('该手机号已注册！');</script>");
        if(!empty($mobile2)){
            $UsersReferees   = M('Users')->where(['mobile'=>$mobile2])->find();
            if(!$UsersReferees){
                exit("<script>history.go(-1);alert('没有该推荐人！');</script>");
            }
        }
        $map['password'] = encrypt(md5($password));
        $map['reg_time'] = time();
        //判断是否车主注册
        if ($level_id==2) {
            //调用上传类，上传执照
            if ($_FILES['file']['size']>0) {
                //定义上传路径
                $path="./Public/Upload/reg/";
                $uploadinfo  = fileUploadNews($path,$_FILES);
                if(!$uploadinfo){
                    exit("<script>history.go(-1);alert('图片格式错误！');</script>");
                }                
                $map['cart_path']  = 'http://hyw.web66.cn:8092/Public/Upload/reg/'.$uploadinfo['file'];
            }else {
                //没有上传文件
                exit("<script>history.go(-1);alert('您还没有上传营业执照');</script>");
            }
        }
        
        $map['token']      = md5(time().mt_rand(1,99999)); //生成token
        $map['zhanghu']    = uniqid();                     //生成账户号----13位,利用uniqid()方法生成
        $map['level_id']   = $level_id;                    //添加用户角色
        $map['mobile']     = $username;                    //手机号
        $map['mobile2']    = I('post.mobile2');            //推荐人手机号
        // $map['sex']        = I('post.sex');                //性别
        $user_id = M('Users')->add($map);                  //将用户信息写入数据库
        
        if(!$user_id)
            exit("<script>history.go(-1);alert('注册失败');</script>");
        $user = M('users')->where("user_id = {$user_id}")->find();
            exit("<script>history.go(-1);alert('注册成功');</script>");
            $this->success('注册成功！',U('Login/login'));
    }

    /**
     * 重置密码
     * password\password2\token\level_id
     *
    */
    public function rePassword()
    {
        $username   =  I('post.username');
        $code       =  I('post.code');
        $password   =  I('post.password');
        $password2  =  I('post.password2');
        
        if(!$username){
            exit("<script>history.go(-1);alert('手机号不能为空！');</script>");
        }

        if(!$code){
            exit("<script>history.go(-1);alert('验证码不能为空！');</script>");
        }
        //验证验证码
        $res = check_verify($username,$code);
        
        if (!$res)
            exit("<script>history.go(-1);alert('验证码错误！');</script>");
        
        if($password !== $password2) {
            exit("<script>history.go(-1);alert('两次输入密码不一样');</script>");
        }
        $Users = M('Users')->where(['mobile'=>$username])->find();

        if(!$Users){
            exit("<script>history.go(-1);alert('没有该用户！');</script>");
        }

        //将密码加密，然后保存
        $data['password'] = encrypt(md5($password));

        if($data['password'] == $Users['password']){
            exit("<script>history.go(-1);alert('重置密码成功');</script>");
        }

        $res = M('Users')->where(array('user_id'=>$Users['user_id']))->save($data);
        //dump($res);
        if (!$res)
            exit("<script>history.go(-1);alert('重置密码失败');</script>");
        else
            exit("<script>history.go(-1);alert('重置密码成功');</script>");
    
    }

    public function logout(){
        session('user',null);
        $html_arr = glob("./Application/Runtime/Html/*.html");
        foreach ($html_arr as $key => $val)
        {
            strstr($val,'Home_Index_index.html') && unlink($val); // 首页                    
            strstr($val,'Home_Goods_goodsList') && unlink($val); // 列表页
            strstr($val,'Home_Channel_index') && unlink($val);  // 频道页
            strstr($val,'Index_Article_articleList') && unlink($val);  // 文章列表页
            strstr($val,'Index_Article_detail') && unlink($val);  // 文章详情
            strstr($val,'Doc_Index_index_') && unlink($val);  // 文章列表页                    
            strstr($val,'Doc_Index_article_') && unlink($val);  // 文章详情                                        
        }        
        // $this->success("退出成功",U('Home/Index/index'));
        header("location:".U('Home/Index/index'));
        exit;
    }

    /**
     * 短信验证码
     * mobile
    */
    public function sms_code()
    {
        // exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收','code'=>$code)));
        $mobile = I('post.mobile');
        // $mobile = '13539919834';

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

    public function pageTest()
    {
        $res = pageRows(1,4,6);
        dump($res);
    }    

}