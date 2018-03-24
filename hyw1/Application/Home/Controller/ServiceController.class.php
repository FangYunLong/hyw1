<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Home\Controller;
use Home\Logic\CartLogic;
use Home\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;
class ServiceController extends BaseController {

    public function index(){      
        $this->display();
    }

    /**
     *  服务网络
     */
    public function service(){
        $Join = M('Join')->where(['join_status'=>1])->select();
        $Ad = M('Ad')->where(['ad_id'=>79])->find();
        $this->assign('ad',$Ad);
        $east_china  = C('east_china');
        $south_china = C('south_china');
        $north_china = C('north_china');
        $southwest   = C('southwest');
        $northeast   = C('northeast');
// dump($east_china);
// dump($south_china);
// dump($north_china);
// dump($southwest);
// dump($northeast);
        foreach($Join as $key => $val){
            if($east_china[$val['province']]){
                $east_china_list[$val['province']] = $east_china[$val['province']];
            }
            if($south_china[$val['province']]){
                $south_china_list[$val['province']] = $south_china[$val['province']];
            }
            if($north_china[$val['province']]){
                $north_china_list[$val['province']] = $north_china[$val['province']];
            }
            if($southwest[$val['province']]){
                $southwest_list[$val['province']] = $southwest[$val['province']];
            }
            if($northeast[$val['province']]){
                $northeast_list[$val['province']] = $northeast[$val['province']];
            }                                                
        }
// dump($east_china_list);
// dump($south_china_list);
// dump($north_china_list);
// dump($southwest_list);
// dump($northeast_list);
        $this->assign('east_china_list',$east_china_list);
        $this->assign('south_china_list',$south_china_list);
        $this->assign('north_china_list',$north_china_list);
        $this->assign('southwest_list',$southwest_list);
        $this->assign('northeast_list',$northeast_list);
        $this->display();
    }

    /**
     *  加入我们
     */
    public function join(){
        $Join = M('Join')->where(['join_status'=>1])->select();
        $Ad = M('Ad')->where(['ad_id'=>80])->find();
        $this->assign('ad',$Ad);
        $aboutInfo = M("Article")->where("cat_id = 3")->order('add_time desc')->limit('1')->select();
        //上一篇文章
        $Article =M("Article");
        $article_id = $aboutInfo[0]['article_id'];
        $front=$Article->where("article_id>".$article_id)->order('article_id asc')->limit('1')->find();
        $fid= !$front ? '没有了': $front['article_id'];
        $this->assign('front',$front);
        $this->assign('fid',$fid);
        //下一篇文章
        $after=$Article->where("article_id<".$article_id)->order('article_id desc')->limit('1')->find();
        $aid= !$after ? '没有了': $after['article_id'];
        $this->assign('after',$after);
        $this->assign('aid',$aid);
        //dump($after);
        // dump($aboutInfo);
        $this->assign('about',$aboutInfo);
        $this->display();
    }

    /**
     *  求职发布
     */
    public function jobRelease(){
        if(IS_POST){
            dump($_POST);DIE;
            $this->doJob();die;
        }
        $this->display();
    }





   /**
    *  服务网络详情页
    */ 
    public function service_details(){
        $province = I('province');
        $area = ['','华东大区','华南大区','华北大区','西南大区','东北大区'];
        $type = I('type');
        $Join = M('Join')->field('j.*,r1.name as name1,r2.name as name2,r3.name as name3')->alias('j')
                         ->join('tp_region as r1 on j.province = r1.id')
                         ->join('tp_region as r2 on j.city = r2.id')
                         ->join('tp_region as r3 on j.district = r3.id')
                         ->where(['province'=>$province,'join_status'=>1])
                         ->select();
        $this->assign('Join',$Join);
        $this->assign('type',$area[$type]);
        $this->display();
    }



    /**
     * 处理提交的求职信息
     * username、password、password2、$level_id、$code
     */
    public function doJob()
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
        // if($check)
        //     exit("<script>history.go(-1);alert('该手机号已注册！');</script>");
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
        $map['sex']        = I('post.sex');                //性别
        $user_id = M('Users')->add($map);                  //将用户信息写入数据库

        if(!$user_id)
            exit("<script>history.go(-1);alert('注册失败');</script>");
        $user = M('users')->where("user_id = {$user_id}")->find();
        exit("<script>history.go(-1);alert('注册成功');</script>");
        $this->success('注册成功！',U('Login/login'));
    }

    
}