<?php
/**
 *
//
 * 2015-11-21
 */
namespace Api\Controller;
use Home\Logic\UsersLogic;
use Think\Page;
use Think\Verify;

class UserController extends BaseController {

    /**
     * 个人中心--个人信息
     */
    public function info(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];
        if(IS_POST){
            I('post.nickname') ? $post['nickname'] = I('post.nickname') : false; //昵称
            I('post.qq') ? $post['qq'] = I('post.qq') : false;  //QQ号码
            I('post.head_pic') ? $post['head_pic'] = I('post.head_pic') : false; //头像地址
            I('post.sex') ? $post['sex'] = I('post.sex') : false;  // 性别
            I('post.birthday') ? $post['birthday'] = strtotime(I('post.birthday')) : false;  // 生日
            I('post.province') ? $post['province'] = I('post.province') : false;  //省份
            I('post.city') ? $post['city'] = I('post.city') : false;  // 城市
            I('post.district') ? $post['district'] = I('post.district') : false;  //地区
            if(!$userLogic->update_info($this->user_id,$post))
                $this->error("保存失败");
            $this->success("操作成功");
            exit;
        }
        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        //  获取订单城市
        $city =  M('region')->where(array('parent_id'=>$user_info['province'],'level'=>2))->select();
        //获取订单地区
        $area =  M('region')->where(array('parent_id'=>$user_info['city'],'level'=>3))->select();

        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('area',$area);
        $this->assign('user',$user_info);
        $this->assign('sex',C('SEX'));
        $this->assign('active','info');
        $this->display();
    }

    /**
     * 个人中心-个人信息
     *token、
    */
    public function userInfo()
    {
        $arr    = array();
        //token验证个人身份
        $token  = isset($_POST['token']) ? I('post.token') : '';
        $user_id = S($token);

        if (empty($user_id))
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        //从内存中读取用户信息
        //$user_id = S($token);
        //查库
        $data   = M('Users')->where(array('user_id'=>$user_id))->find();

        //判断是客户还是车主
        if ($data['level_id']==1) {
            //客户
            //收货地址
            // $is_default = $data['is_default']>0 ? 1 : 0;
            $arr['address'] = $data['address'];
        }else if ($data['level_id']==2) {
            //司机
            //营业执照
            $arr['cart_path']=$data['cart_path'];
            //公司名称
            $arr['gongsi'] = $data['gongsi'];
        }
        $arr['head_pic'] = $data['head_pic'];//用户头像
        $arr['nickname'] = $data['nickname'];
        $arr['zhanghu'] = $data['zhanghu'];//账户号
        $arr['sex'] = $data['sex'];//0保密1男2女
        $arr['mobile'] = $data['mobile'];
        $arr['cart_path'] = $data['cart_path'];
        $arr['invoice_title'] = $data['invoice_title'];
        $arr['token'] = $data['token'];//用户密钥
        S($data['token'].'reset_pwd','1',600);//把凭证存到缓存中

        exit(json_encode(array('status'=>1,'msg'=>'个人信息','result'=>$arr)));
    }

    /**
     * 个人中心-个人信息车主营业执照
     * token、file（车主营业执照）
    */
    public function editUser()
    {
        //接收post过来的数据
        $data = I('post.');
        $token = $data['token'];
        $user_id = S($token);
        if(!$user_id){
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $level_id = M('Users')->where(['user_id'=>$user_id])->getField('level_id');
        if($level_id!=2){
            exit(json_encode(array('status'=>-1,'msg'=>'您不是车主')));
        }

        //判断是否上传图片
        if ($_FILES['file']['size']>0){
           /* $uploadinfo  = fileUpload();
            $data['cart_path'] = $uploadinfo['file']['savepath'].$uploadinfo['file']['savename'];//图片保存路径*/
            $path="./Public/Upload/cartpart/";
            $list = $this->fileUploadNews($path,$_FILES);
            $data['cart_path']='http://hyw.web66.cn:8092/Public/Upload/cartpart/' . $list['file'];
        }
        $res = M('Users')->where(array('user_id'=>$user_id))->save($data);
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'修改失败')));
        else
            exit(json_encode(array('status'=>1,'msg'=>'修改成功','cart_path'=>$data['cart_path'])));
    }

    /**
     * 个人中心--头像设置信息回显
     * token
     *
    */
    public function headlist()
    {
        $token = I('post.token');
        //$data  = I('post.');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        $res = M('Users')->where(array('user_id'=>$user_id))->find();
        $arr=array();
        $arr['nickname'] = $res['nickname'];
        $arr['zhanghu'] = $res['zhanghu'];
        exit(json_encode(array('status'=>1,'msg'=>'头像设置信息回显','result'=>$arr)));
    }
    /**
     * 个人中心-修改头像
     *post
     * file（参数名）
    */
    public function editUserpic()
    {
        $token = I('post.token');
        $data  = I('post.');
        $user_id = S($token);
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        if ($_FILES['file']['size']>0) {
            /*$uploadinfo  = fileUpload();
            $data['head_pic'] = $uploadinfo['file']['savepath'].$uploadinfo['file']['savename'];//图片保存路径*/
            //定义上传路径
            $path = "./Public/Upload/userpic/";
            $list = $this->fileUploadNews($path,$_FILES);
            // $uploadinfo  = $this->fileUpload($path);
            // $filename = $_FILES['file']['tmp_name'];

            $data['head_pic']='http://hyw.web66.cn:8092/Public/Upload/userpic/' . $list['file'];
        }

        //保存
        $res = M('Users')->where(array('user_id'=>$user_id))->save($data);

        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'修改头像失败')));
        else
            exit(json_encode(array('status'=>1,'msg'=>'修改头像成功','head_pic'=>$data['head_pic'])));
    }

    //修改用户信息
    public function editUserInfo()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $data    = array_filter(I('post.'));

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }
        $data['user_id'] = $user_id;
        $res = M('Users')->save($data);

        if($res){
            json_App(['status'=>1,'msg'=>'修改成功！']);
        }else{
            json_App(['status'=>-1,'msg'=>'修改失败！']);
        }
    }    

    /**
     * 个人中心--昵称修改
     * nickname、token
    */
    public function editNickname()
    {
        $token = I('post.token');
        $user_id = S($token);
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问个人中心昵称修改')));
        }
       // $nickname = I('post.nickname');
        $data = I('post.');
        $res = M('Users')->where(array('user_id'=>$user_id))->save($data);
        if ($res) {
            exit(json_encode(array('status'=>1,'msg'=>'昵称修改成功')));
        }else {
            exit(json_encode(array('status'=>-1,'msg'=>'昵称修改失败')));
        }

    }


    /**
     * 个人中心-更换手机号
     * token、mobile、
    */
    public function changeMobile()
    {
        //
        $token = I('post.token');
        $data  = I('post.');
        $mobile = I('post.mobile');
        //验证手机号
        if (check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号格式不正确')));
        $res = M('Users')->where(array('token'=>$token))->save($data);
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'更换手机号失败')));
        exit(json_encode(array('status'=>1,'msg'=>'更换手机号成功')));
    }

    /**
     *
    */


    /*订单*/

    //*****  车主 ******//
    /**
     * 个人中心--订单中心---全部订单-车主年月租
     * token、
     * 返回值：图、品牌、吨位、车类型、时间
    */
    public function orderList()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;        
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租所有订单  field('order_id,name')->
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();
            $arr['nyz'] = $data;
            /*//统计年月租所有订单
            $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>0,'_logic'=>'and'))->count();
            $arr['nnum'] = $num;*/
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户全部订单信息','result'=>$arr)));
        }    
    }
    //车主年月租未完成
    /**
     * 个人中心--订单中心--全部未完成订单-年月租
     * token、
     * 返回值：图、品牌、吨位、车类型、时间
    */
    public function allunfinish()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;        
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问个人中心-订单中心-未完成订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        //dump($user);die;
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租车主未完成全部订单
            //$data = M('order')->alias('tb1')->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei')->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_offplay'=>0,'_logic'=>'and'))->select();//where条件userid and is_type or user_id and is_
            $map['tb1.user_id'] = $user_id;
            $map['tb1.is_type'] = 0;
            $map['_query'] = 'is_offplay=0&is_completed=0&_logic=or';
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where($map)
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();//where条件userid and is_type or user_id and is_
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部未完成订单信息成功','result'=>$arr)));
        }
    }
    /**
     * 个人中心--订单中心--年月租-车主租车未完成
     * token
     * 返回值：图、品牌、吨位、车类型、时间
    */
    public function notPlay()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;        
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问订单中心')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        //dump($user);
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租租车已完成
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_offplay'=>0,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();
            $arr['nyz'] = $data;

            /*//统计年月租租车已完成
            $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>0,'is_offplay'=>1,'_logic'=>'and'))->count();
            $arr['nnum'] = $num;*/
            /* //查询临时租租车已完成
             $data = M('order')->where(array('user_id'=>$user_id,'is_type'=>1,'is_offplay'=>1,'_logic'=>'and'))->select();
             $arr['lsz'] = $data;
             //统计年月租租车已完成
             $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>1,'is_offplay'=>1,'_logic'=>'and'))->count();
             $arr['lnum'] = $num;*/

            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部租车未完成订单信息成功','result'=>$arr)));

        }
    }
    /**
     * 个人中心--订单中心--年月租-车主抢单未完成
     * token
     * 返回值：图、品牌、吨位、车类型、时间
    */
    public function ontCompleted()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;        
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问订单中心')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        //dump($user);
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租租车已完成
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_completed'=>0,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部抢单未完成订单信息成功','result'=>$arr)));
        }
    }
    //end车主年月租未完成
    //车主年月租已完成
    /**
     * 个人中心-订单中心-全部已完成订单--年月租
     * token
     * 返回值：图、品牌、吨位、车类型、时间
    */
    public function allfinish()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;        
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问个人中心-订单中心-未完成订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        //dump($user);die;
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租车主未完成全部订单
            $map['tb1.user_id'] = $user_id;
            $map['tb1.is_type'] = 0;
            $map['_query'] = 'is_offplay=1&is_completed=1&_logic=or';
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where($map)
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();//where条件userid and is_type or user_id and is_
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部已完成订单信息成功','result'=>$arr)));
        }
    }
    /**
     * 个人中心--订单中心--年月租-车主租车已完成
     * token
     * 返回值：图、品牌、吨位、车类型、时间
     */
    public function play()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;         
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问订单中心')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        //dump($user);
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租租车已完成
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_offplay'=>1,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                               
                              ->select();
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部租车已完成订单信息成功','result'=>$arr)));

        }
    }
    /**
     * 个人中心--订单中心--年月租-车主抢单已完成
     * token
     * 返回值：图、品牌、吨位、车类型、时间
     */
    public function completed()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;        
        // $user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问订单中心')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        //dump($user);
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租租车已完成
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_completed'=>1,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                               
                              ->select();
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部抢单已完成订单信息成功','result'=>$arr)));
        }
    }
    //end车主年月租已完成
    //临时租
    /**
     * 个人中心--订单中心---全部订单-车主临时租-
     * token
     * 返回值：图片、客户、吨位、电话、地址
     */
    public function tempOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询临时租所有订单
            $data = M('Temporary')->where(array('driver_id'=>$user_id))
                                  ->limit($nums,$rows)
                                  ->order('add_time DESC')            
                                  ->select();
            $arr['lsz'] = $data;
            /*//统计租所有订单
            $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>1,'_logic'=>'and'))->count();
            $arr['lnum'] = $num;*/
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户（车主）临时租全部订单信息成功','result'=>$arr)));

        } /*else if ($level_id==1) {
            //客户
            $arr = array();//存储返回数据
            //查询临时租所有订单
            $data = M('Temporary')->where(array('user_id'=>$user_id))->select();
            $arr['lsz'] = $data;
            //统计年月租所有订单
            //$num = M('order')->where(array('user_id'=>$user_id,'is_type'=>1,'_logic'=>'and'))->count();
            //$arr['lnum'] = $num;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户临时租全部订单信息成功','result'=>$arr)));
        }*/
    }
    //***** end  车主 ******//


    //*****  客户 ******//
    //客户年月租
    /**
     * 个人中心--订单中心---全部订单-客户年月租
     * token、
     * 返回值：图、品牌、吨位、车类型、时间
    */
    public function clientOrderList()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;
       // $user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否用户
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
       // dump($user);die;
        if ($level_id == 1) {
            //客户
            $arr = array();//存储返回数据
            //查询年月租所有订单  field('order_id,name')->
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(['tb1.user_id'=>$user_id,'tb1.is_type'=>0,'_logic'=>'and'])
                              ->limit($nums,$rows)
                              ->order('add_time DESC')
                              ->select();
            $arr['nyz'] = $data;
            /*//统计年月租所有订单
            $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>0,'_logic'=>'and'))->count();
            $arr['nnum'] = $num;*/
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户(客户)全部订单信息成功','result'=>$arr)));

        }
    }
    /**
     * 个人中心--订单中心---未完成订单-客户年月租
     * token、
     * 返回值：图、品牌、吨位、车类型、时间
     */
    public function clientontPlaymoney()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        // dump($user);die;
        if ($level_id == 1) {
            //客户
            $arr = array();//存储返回数据
            //查询年月租所有订单  field('order_id,name')->
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_playmoney'=>0,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();
            $arr['nyz'] = $data;

            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户（客户）未完成订单成功','result'=>$arr)));

        }
    }
    /**
     * 个人中心--订单中心---已完成订单-客户年月租
     * token、
     * 返回值：图、品牌、吨位、车类型、时间
     */
    public function clientPlaymoney()
    {
        $token = I('post.token');
        $user_id = S($token);
        $page    = I('post.page')?I('post.page'):1;
        $rows    = I('post.rows')?I('post.rows'):6;
        $nums    = ($page-1) * $rows;
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        // dump($user);die;
        if ($level_id == 1) {
            //客户
            $arr = array();//存储返回数据
            //查询年月租所有订单  field('order_id,name')->
            $data = M('order')->alias('tb1')
                              ->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')
                              ->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei,tb2.zm_pic')
                              ->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_playmoney'=>1,'_logic'=>'and'))
                              ->limit($nums,$rows)
                              ->order('add_time DESC')                              
                              ->select();
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户（客户）已完成订单成功','result'=>$arr)));

        }
    }

    //客户临时租
    /**
     * 个人中心--订单中心--全部订单--客户临时租
     * token
     * 返回值：temp_id,username,dunwei,mobile,address,order_pic
    */
    public function  clientTempOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 1) {
            //车主
            $arr = array();//存储返回数据
            //查询临时租所有订单
            //$data = M('Temporary')->alias('tb1')->join('tb2 on tb1.user_id = tb2.user_id')->filed('tb1.temp_id,tb1.username,tb1.dunwei,tb1.mobile,tb1.address')->where(array('tb1.user_id'=>$user_id))->select();
            $data = M('Temporary')->field('temp_id,username,dunwei,mobile,address,order_pic')->where(array('user_id'=>$user_id))->select();
            //$arr  = M('Goods')->field('zm_pic')->where(array('driver'=>$data['driver']))->limit(1)->find();
            //dump($arr);
            $arr['lsz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户（客户）临时租全部订单信息成功','result'=>$arr)));
        }
    }
    /**
     * 个人中心--订单中心--未完成订单--客户临时租
     * token
     * 返回值：temp_id,username,dunwei,mobile,address,order_pic
     */
    public function  clientTempunfinshed()
    {
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 1) {
            //车主
            $arr = array();//存储返回数据
            //查询临时租所有订单
            //$data = M('Temporary')->alias('tb1')->join('tb2 on tb1.user_id = tb2.user_id')->filed('tb1.temp_id,tb1.username,tb1.dunwei,tb1.mobile,tb1.address')->where(array('tb1.user_id'=>$user_id))->select();
            $data = M('Temporary')->field('temp_id,username,dunwei,mobile,address,order_pic')->where(array('user_id'=>$user_id,'driver_id'=>0,'_logic'=>'AND'))->select();
            $arr['lsz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户（客户）临时租未完成订单信息成功','result'=>$arr)));
        }
    }
    /**
     * 个人中心--订单中心--已完成订单--客户临时租
     * token
     * 返回值：temp_id,username,dunwei,mobile,address,order_pic
     */
    public function  clientTempfinshed()
    {
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('Users')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 1) {
            //车主
            $arr = array();//存储返回数据
            //查询临时租所有订单
            //$data = M('Temporary')->alias('tb1')->join('tb2 on tb1.user_id = tb2.user_id')->filed('tb1.temp_id,tb1.username,tb1.dunwei,tb1.mobile,tb1.address')->where(array('tb1.user_id'=>$user_id))->select();
            $data = M('Temporary')->field('temp_id,username,dunwei,mobile,address,order_pic')->where(array('user_id'=>$user_id,'driver_id'=>array('neq',0),'_logic'=>'AND'))->select();
            $arr['lsz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有订单')));
            exit(json_encode(array('status'=>1,'msg'=>'该用户（客户）临时租已完成订单信息成功','result'=>$arr)));
        }
    }
    //***** end  客户 ******//


    /**
     * 个人中心--订单中心-年月租-车主租车已完成
     * token\
     */
   /* public function offPlay()
    {
        $token = I('post.token');
        $user_id = S($token);
        if (empty($token)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问订单中心')));
        }
        //判断是否车主
        $user = M('User')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租租车已完成
            $data = M('order')->alias('tb1')->join('tp_goods tb2 on tb1.goods_id=tb2.goods_id')->field('tb1.order_id,tb1.add_time,tb2.goods_id,tb2.pinpai,tb2.cart_type,tb2.dunwei')->where(array('tb1.user_id'=>$user_id,'tb1.is_type'=>0,'tb1.is_offplay'=>1,'_logic'=>'and'))->select();
            $arr['nyz'] = $data;
            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有租车完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的全部订单信息','result'=>$arr)));

        }else if ($level_id==1) {
            //客户
        }
    }*/
    /**
     * 订单中心-抢单已完成/（客户）已完成
    */
    /*public function isCompleted()
    {
        $token = I('post.token');
        $user_id = S($token);
        if (empty($token)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问全部订单')));
        }
        //判断是否车主
        $user = M('User')->where(array('user_id'=>$user_id))->find();
        $level_id = $user['level_id'];
        if ($level_id == 2) {
            //车主
            $arr = array();//存储返回数据
            //查询年月租抢单已完成
            $data = M('order')->where(array('user_id'=>$user_id,'is_type'=>0,'is_completed'=>1,'_logic'=>'and'))->select();
            $arr['nyz'] = $data;
            //统计年月租抢单已完成
            $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>0,'is_completed'=>1,'_logic'=>'and'))->count();
            $arr['nnum'] = $num;
            //查询临时租抢单已完成
            $data = M('order')->where(array('user_id'=>$user_id,'is_type'=>1,'is_completed'=>1,'_logic'=>'and'))->select();
            $arr['lsz'] = $data;
            //统计年月租抢单已完成
            $num = M('order')->where(array('user_id'=>$user_id,'is_type'=>1,'is_completed'=>1,'_logic'=>'and'))->count();
            $arr['lnum'] = $num;

            //返回数据
            if (empty($arr))
                exit(json_encode(array('status'=>-1,'msg'=>'您还没有抢单完成订单')));
            exit(json_encode(array('status'=>1,'msg'=>'您的完成抢单信息','result'=>$arr)));

        }else if ($level_id==1) {
            //客户
        }
    }*/

    /*订单  end*/


    /*我的消息*/

    /**
     * 我的消息--全部消息
     * token
     * post方式
     * 返回值：is_read（0未读1已读）
    */
    public function allMsg()
    {
        $token = I('post.token');
        $no_read = I('post.no_read');
        // $no_read = 1;
        $user_id = S($token);

        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }

        if($no_read){
            $data['is_read'] = 0;
        }

        $data['user_id'] = $user_id;
        $data['type'] = 1;
        $data['is_del'] = 1;
        $msg = M('Msg')->where($data)->select();//获取普通消息
        // dump($msg);
        $radioMsg = M('Msg')->where(['type'=>2])->select();//获取所有的广播消息

        foreach ($radioMsg as $k =>$v) {
            if (in_array($user_id,explode(',',$v['is_del']))) {
                unset($radioMsg[$k]);
            }
        }

        foreach ($radioMsg as $k => $v) {
            if (in_array($user_id,explode(',',$v['is_read']))) {
                $radioMsg[$k]['is_read']= 1;
            }else{
                $radioMsg[$k]['is_read']= 0;
            }
        }

        if($no_read){
            foreach ($radioMsg as $k => $v) {
                if($v['is_read']!=1){
                    $radioMsg[$k] = $v;
                }
            }    
            $result = array_merge($radioMsg,$msg);
            $count = count($result);
            json_App(['status'=>1,'msg'=>'未读消息','result'=>$count]);
        }

        $result = array_merge($radioMsg,$msg);
        $where1 = $where2 = $where3 = array();
        foreach($result as $k => $v){
            $where1[$k] = $v['is_read'];
            $where2[$k] = $v['type'];
            $where3[$k] = $v['public_time'];
        }
      
        //排序
        array_multisort($where1,SORT_ASC,$where2,SORT_DESC,$where3,SORT_DESC,$result); 
        exit(json_encode(array('status'=>1,'msg'=>'我的消息','result'=>$result)));
    }

    public function testAll()
    {
        addMsg(809,'恭喜，匹配成功');
    }

    /**
     * 我的消息--读取消息
     * token、msg_id（消息id）
     * post
     * 返回值：是否已读0未读1已读、消息页面
    */
    public function reMsg()
    {
        $token = I('post.token');
        $user_id= S($token);
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $msg_id = I('post.msg_id');
        if (empty($msg_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'缺少消息id')));
        }
        //查看是否已读
        $res = M('Msg')->where(array('msg_id'=>$msg_id))->find();
        $content = htmlspecialchars_decode($res['content']);

        if($res['type'] == 2){
            if (in_array($user_id,explode(',',$res['is_read']))) {
                $is_read = 1;
            }else {
                if(empty($res['is_read'])){
                    $res['is_read'] .= $user_id;
                }else{
                    $res['is_read'] .= ','.$user_id;
                }
            }
        }else{
            $res['is_read'] = 1;
        }
        //修改为已读
        $data = M('Msg')->where(array('msg_id'=>$msg_id))->save($res);
        //载入模板
        // $this->assign('content',$content);
        // $this->display();
        exit(json_encode(array('status'=>1,'msg'=>'消息修改为已读状态成功','result'=>$content)));

    }

    /**
     * 我的消息--删除消息
     * token、msg_id
     *返回状态码
    */
    public function delMsg()
    {
        $token = I('post.token');
        $user_id= S($token);
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $msg_id = I('post.msg_id');
        if (empty($msg_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'缺少消息id')));
        }        

        $res = M('Msg')->where(array('msg_id'=>$msg_id))->find();
        //$content = htmlspecialchars_decode($res['content']);
        if($res['type'] == 2){
            if (in_array($user_id,explode(',',$res['is_del']))) {
                exit(json_encode(array('status'=>-1,'msg'=>'消息已经被删除')));
            }else {
                if(empty($res['is_del'])){
                    $res['is_del'] .= $user_id;
                }else{
                    $res['is_del'] .= ','.$user_id;
                }
            }
        }else{
            //查看是否已读
            if($res2['user_id']!=$user_id){
                json_App(['status'=>1,'msg'=>'消息已经被删除']);
            }            
            $res['is_del'] = 2;
        }
        // dump($res);exit;
        //修改为已删除状态
        $data = M('Msg')->where(array('msg_id'=>$msg_id))->save($res);
        if ($data) {
            exit(json_encode(array('status'=>1,'msg'=>'删除成功')));
        }else {
            exit(json_encode(array('status'=>-1,'msg'=>'删除失败')));
        }


    }
    /*我的消息  end*/


    /* 我的收藏*/
    /**
     * 我的收藏--收藏列表
     * token
     * 测试通过
    */
    public function collect()
    {
        $token = I('post.token');
        $rows  = I('post.rows',6);
        $page  = I('post.page',1);
        $num   = ($page-1) * $rows;

        $user_id = S($token);
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $count = M('GoodsCollect')->where(array('tp_goods_collect.user_id'=>$user_id))->join("tp_goods on tp_goods_collect.goods_id=tp_goods.goods_id")->count();

        if($count<1){
            exit(json_encode(['status'=>2,'msg'=>'没有数据']));
        }
        $pages = ceil($count / $rows);

        //将商品原图更改成正面图 by lonelytears on 20170103
        $res = M('GoodsCollect')->field('tp_goods.goods_id,tp_goods.goods_name,tp_goods.zm_pic,tp_goods_collect.collect_id,tp_goods.is_special')
                                ->where(array('tp_goods_collect.user_id'=>$user_id,'tp_goods.is_on_sale'=>1))
                                ->join("tp_goods on tp_goods_collect.goods_id=tp_goods.goods_id")
                                ->limit($num,$rows)
                                ->select();
        //返回  收藏id、商品id、商品名称、商品原图
        exit(json_encode(array('status'=>1,'msg'=>'我的收藏','page'=>$page,'pages'=>$pages,'rows'=>$rows,'result'=>$res)));
    }
    /**
     * 我的收藏--收藏详情
     * collect_id收藏id
     * token
     * 测试通过
     * （返回值和特价车详情一样）
    */
    public function collectInfo()
    {
        $token = I('post.token');
        $collect_id = I('post.collect_id');
        $user_id = S($token);
        // $user_id = 1;
        // $collect_id = 128;
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'缺少token')));
        }
        if (empty($collect_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'缺少collect_id')));
        }        
        $collect = M('GoodsCollect')->where(array('collect_id'=>$collect_id))->find();
        $goods_id = $collect['goods_id'];
        $goods = M('Goods')->field('pinpai,dunwei,cart_type,menjia,mj_height,shuju,is_yt,cart_age,use_hours,dcsj,description,address,factorytime,username,mobile,zm_pic,cm_pic,czt_pic,nb_pic,is_special,buy_year,special_price,is_status')
                           ->where(array('goods_id'=>$goods_id,'is_on_sale'=>1))
                           ->find();
        
        if(!$goods){
            $res = M('GoodsCollect')->delete($collect_id);//车下架则删除该条收藏数据
            if($res){
                exit(json_encode(array('status'=>-1,'msg'=>'该车已下架')));
            }else{
                exit(json_encode(array('status'=>-1,'msg'=>'数据异常')));
            }
        }
        //返回数据
        exit(json_encode(array('status'=>1,'msg'=>'我的收藏成功','result'=>$goods)));
    }
    /**
     * 我的收藏--删除收藏
     * token
     * collect_id
     * 测试通过
    */
    public function delCollect()
    {
        $token = I('post.token');
        $user_id = S($token);
        $collect_id = I('post.collect_id');
        $goods_id = I('post.goods_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }

        if($collect_id){
            $coll = M('GoodsCollect')->where(['collect_id'=>$collect_id])->find();
            if(!$coll){
                exit(json_encode(array('status'=>1,'msg'=>'删除成功')));
            }
            $res = M('GoodsCollect')->where(array('collect_id'=>$collect_id))->delete();
            $goods_id = null;
        }        
        if($goods_id){
            $coll = M('GoodsCollect')->where(['goods_id'=>$goods_id,'user_id'=>$user_id])->find();
            if(!$coll){
                exit(json_encode(array('status'=>1,'msg'=>'删除成功')));
            }
            $res = M('GoodsCollect')->where(['goods_id'=>$goods_id,'user_id'=>$user_id])->delete();
        }
        
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'删除失败')));
        exit(json_encode(array('status'=>1,'msg'=>'删除成功')));
    }
    /**
     * 我的收藏--加入收藏
     * goods_id
     * token
     * 测试通过
    */
    public function addCollect()
    {
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        $goods_id = I('post.goods_id');
        $data = I('post.');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        if(!$goods_id){
            exit(json_encode(array('status'=>-1,'msg'=>'参数错误')));
        }
        //拼接时间]
        $coll = M('GoodsCollect')->where(['user_id'=>$user_id,'goods_id'=>$goods_id])->find();
        if($coll){
            exit(json_encode(array('status'=>1,'msg'=>'加入收藏成功')));
        }
        $data['user_id'] = $user_id;
        $data['add_time'] = time();
        $res = M('GoodsCollect')->add($data);
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'加入收藏失败')));
        exit(json_encode(array('status'=>1,'msg'=>'加入收藏成功')));

    }
    /* 我的收藏  end*/

    /*我的求职*/
    /**
     * 我的求职--简历回显
     * token
     * 测试通过
     *
    */
    public function resume()
    {
        //简历回显
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $res = M('Resume')->where(array('user_id'=>$user_id))->find();
        //dump($res);
        if (!$res)
            exit(json_encode(array('status'=>2,'msg'=>'还没有添加简历')));
        exit(json_encode(array('status'=>1,'msg'=>'简历信息成功','result'=>$res)));
    }
    /**
     * 我的求职--简历填写
     * token
     * user_name,sex,age,jingyan,xueli,mobile,address
     *测试成功
    */
    public function addResume()
    {
        $token = I('post.token');
        $user_id = S($token);
       //$user_id = I('post.user_id');
        $data = I('post.');
        $data['user_id'] = $user_id;
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        //查看是否存在简历信息
        $info = M('Resume')->where(array('user_id'=>$user_id))->find();
        if (!empty($info))
            $res = M('Resume')->where(array('user_id'=>$user_id))->save($data);
        else
            $res = M('Resume')->add($data);
        /*dump($info);
        dump($res);*/
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'重发简历失败')));
        exit(json_encode(array('status'=>1,'msg'=>'重发简历成功')));
    }
    /**
     * 我的求职--隐藏/显示简历
     * token
     * is_hidden
     *
    */
    public function hidResume()
    {
        $token = I('post.token');
        $user_id = S($token);
        $data = I('post.');
        $data['user_id'] = $user_id;
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        //查看是否存在简历信息
        $info = M('Resume')->where(array('user_id'=>$user_id))->find();
        if ($info) {
            $res = M('Resume')->where(['user_id'=>$user_id])->save(['is_hidden'=>$data['is_hidden']]);
            if ($res) {
                if ($data['is_hidden']==1){
                    exit(json_encode(array('status'=>1,'msg'=>'简历隐藏成功')));
                }else{
                    exit(json_encode(array('status'=>1,'msg'=>'简历公开成功')));
                }
            } else {
                exit(json_encode(array('status'=>-1,'msg'=>'操作失败')));
            }
        } else {
            exit(json_encode(array('status'=>-1,'msg'=>'您还没填写简历')));
        }
    }
    /*我的求职  end */

    /**************************订单**************************/

    //订单中心-年月租-租车订单
    public function userOrder()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page',1); 
        $rows    = I('post.rows',6); 
        $num     = ($page-1)*$rows;

        $order_status         = I('post.order_status');
        $data['order_status'] = $order_status;
        $data['user_id']      = $user_id;
        $where  = ' 1=1 ';
        $where .= " AND o.user_id = {$user_id} ";

        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        if(!empty($order_status)){
            // $data['order_status'] = array('neq',$order_status);
            if($order_status!=5){
                $where .= ' AND order_status != 5 ';
            }else{
                $where .= ' AND order_status = 5 ';
            }
        }

        $data  = array_filter($data);

        $count = M('Order')->alias('o')->where($where)
                           ->count();
        if($count<1){
            exit(json_encode([
                'status'=> 2,
                'msg'   => '没有数据',
            ]));            
        }

        $pages = ceil($count / $rows);

        $sql = "SELECT o.order_id,o.order_sn,o.order_status,o.goods_id,o.add_time,g.pinpai,g.cart_type,g.dunwei,g.zm_pic
                FROM tp_order AS o LEFT JOIN tp_goods AS g ON o.goods_id=g.goods_id
                WHERE {$where} ORDER BY o.add_time DESC LIMIT {$num},{$rows} ";
        $res = M('')->query($sql);        

        if($res){
            exit(json_encode([
                'status'=> 1,
                'msg'   => '成功',
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $res
            ]));
        }else{
            exit(json_encode([
                'status'=> -1,
                'msg'   => '失败',
            ]));            
        }
    }

    //订单中心-年月租-租车详情
    public function userOrderInfo()
    {
        $token    = I('post.token');
        $user_id  = S($token);
        $order_id = I('post.order_id');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        if(!$order_id){
            json_App(['status'=>-1,'msg'=>'参数错误']);
        }

        $Order = M('Order')->alias('o')
                           ->field('o.order_id,o.order_status,o.order_sn,o.add_time,g.pinpai,g.cart_type,g.dunwei,g.menjia,g.mj_height,g.factorytime,g.use_hours,g.is_status,g.bydc,g.shuju,g.is_yt,o.tenancy,o.yhours,o.mprice,o.number,o.use_user,o.mobile,o.address,o.invoice_title,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,start_time,end_time')
                           ->join('tp_goods as g on o.goods_id=g.goods_id')
                           ->where(['o.user_id'=>$user_id,'o.order_id'=>$order_id])
                           ->find();
        if($Order){
            json_App(['status'=>1,'msg'=>'成功','data'=>$Order]);
        }else{
            json_App(['status'=>-1,'msg'=>'失败','data'=>[]]);
        }

    }

    //订单中心-年月租-抢单详情
    public function carOrderInfo()
    {
        $token    = I('post.token');
        $user_id  = S($token);
        $order_id = I('post.order_id');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }
        if(!$order_id){
            json_App(['status'=>-1,'msg'=>'参数错误']);
        }        

        $OrderInfo = M('OrderInfo')->alias('oi')
                                   ->field('o.order_id,oi.status,o.order_sn,oi.add_time,g.pinpai,g.cart_type,g.dunwei,g.menjia,g.mj_height,g.bydc,g.shuju,g.is_yt,o.tenancy,o.yhours,o.mprice,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,oi.cart_age,oi.use_hours,oi.dcsj,oi.paly_num,o.start_time,o.end_time')
                                   ->join('tp_order as o on oi.order_id=o.order_id')
                                   ->join('tp_goods as g on o.goods_id=g.goods_id')
                                   ->where(['oi.user_id'=>$user_id,'oi.order_id'=>$order_id])
                                   ->find();
        if($OrderInfo){
            json_App(['status'=>1,'msg'=>'成功','data'=>$OrderInfo]);
        }else{
            json_App(['status'=>-1,'msg'=>'失败','data'=>[]]);
        }                                   
    }

    //订单中心-年月租-抢单订单
    public function carOrder()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page',1); 
        $rows    = I('post.rows',6); 
        $num     = ($page-1)*$rows;
        $status  = I('post.status');

        $data['status']  = $status;   
        $data['user_id'] = $user_id;

        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        $level_id = M('Users')->field('level_id')->find($user_id)['level_id'];

        if($level_id!=2){
            exit(json_encode(['status'=>-1,'msg'=>'您不是车主！']));
        }

        if(!empty($status)&&$status!=5){
            $data['status'] = array('neq',5);
        }

        $data  = array_filter($data);

        $count = M('OrderInfo')->where($data)
                               ->count();
        if($count<1){
            exit(json_encode(['status'=>2,'msg'=>'没有数据！']));
        }

        $pages = ceil($count / $rows);

        $order_id = M('OrderInfo')->field('order_id,status,add_time')
                                  ->where($data)
                                  ->limit($num,$rows)
                                  ->order('add_time DESC')
                                  ->select();      
        
        foreach($order_id as $k => $v){
            $order_ids[$k] = $v['order_id'];
        }

        $order_id_str = implode(',',$order_ids);
        $orderData['order_id'] = ['in',$order_id_str];

        $sql    = "SELECT o.order_id,o.order_sn,o.order_status,o.goods_id,g.pinpai,g.cart_type,g.dunwei,g.zm_pic
                   FROM tp_order AS o 
                   LEFT JOIN tp_goods AS g ON o.goods_id=g.goods_id
                   WHERE o.order_id IN({$order_id_str})";
        $Orders = M('')->query($sql);

        foreach ($Orders as $k => $v) {
            $Order[$k] = array_merge($Orders[$k],$order_id[$k]);
        }

        if($Order){
            exit(json_encode([
                'status'=> 1,
                'msg'   => '成功',
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $Order
            ]));
        }else{
            exit(json_encode([
                'status'=> -1,
                'msg'   => '失败',
            ]));            
        }                         

    }

    //订单中心-临时租-租车订单
    public function userTemp()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page',1); 
        $rows    = I('post.rows',6); 
        $num     = ($page-1)*$rows;
        $status  = I('post.status');
        $data['status']  = $status;
        $data['user_id'] = $user_id;

        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        if(!empty($status)&&$status!=3){
            $data['status'] = array('neq',3);
        }

        $data  = array_filter($data);

        $count = M('Temporary')->where($data)
                               ->count();
        if($count<1){
            exit(json_encode([
                'status'=> 2,
                'msg'   => '没有数据',
            ]));            
        }

        $pages     = ceil($count / $rows);

        $Temporary = M('Temporary')->field('temp_id,user_id,temp_sn,status,mobile,username,dunwei,driver_id,address,add_time,push_time')
                                   ->where($data)
                                   ->limit($num,$rows)
                                   ->order('add_time DESC')
                                   ->select();
        
        foreach($Temporary as $k => $v){
            if($v['driver_id']){
                $Users = M('Users')->where(['user_id'=>$v['driver_id']])->find();
                $Temporary[$k]['nickname']  = $Users['nickname'];
                $Temporary[$k]['carmobile'] = $Users['mobile'];
            }else{
                $Temporary[$k]['nickname']  = '';
                $Temporary[$k]['carmobile'] = '';
            }
        }

        if($Temporary){
            exit(json_encode([
                'status'=> 1,
                'msg'   => '成功',
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $Temporary
            ]));
        }else{
            exit(json_encode([
                'status'=> -1,
                'msg'   => '失败',
            ]));            
        }
    }

    //订单中心-临时租-租车详情
    public function userTempInfo()
    { 
        $token = I('post.token');
        $user_id = S($token);
        $temp_id = I('post.temp_id');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        if(!$temp_id){
            json_App(['status'=>-1,'msg'=>'参数错误！']);
        }

        $Temp   = M('Temporary')->field('add_time,push_time,username,mobile,address,driver_id,status,dunwei')->find($temp_id);
        if($Temp['driver_id']){
            $Users  = M('Users')->find($Temp['driver_id']);
            $Temp['nickname']   = $Users['nickname'];
            $Temp['carmobile']  = $Users['mobile'];
        }else{
            $Temp['nickname']   = '';
            $Temp['carmobile']  = '';            
        }

        json_App(['status'=>1,'msg'=>'成功','data'=>$Temp]);
    }

    //订单中心-临时租-抢单详情
    public function carTempInfo()
    {


    }

    //订单中心-临时租-抢单订单
    public function carTemp()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $page    = I('post.page',1); 
        $rows    = I('post.rows',6); 
        $num     = ($page-1)*$rows;
        $status  = I('post.status');

        $data['status']  = $status;   
        $data['user_id'] = $user_id;

        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        $level_id = M('Users')->field('level_id')->find($user_id)['level_id'];

        if($level_id!=2){
            exit(json_encode(['status'=>-1,'msg'=>'您不是车主！']));
        }

        if(!empty($status)&&$status!=3){
            $data['status'] = array('neq',3);
        }

        $data  = array_filter($data);

        $count = M('TempInfo')->where($data)
                              ->count();
        if($count<1){
            exit(json_encode(['status'=>2,'msg'=>'没有数据！']));
        }

        $pages   = ceil($count/$rows);
        
        $temp_id = M('TempInfo')->field('temp_id,status')
                                ->where($data)
                                ->limit($num,$rows)
                                ->order('add_time DESC')
                                ->select();
 
        foreach($temp_id as $k => $v){
            $temp_ids[$k] = $v['temp_id'];
        }
        $temp_id_str = implode(',',$temp_ids);
        $tempData['temp_id'] = ['in',$temp_id_str];

        $Temporarys = M('Temporary')->field('temp_id,mobile,username,dunwei,address,add_time,push_time')
                                    ->where($tempData)
                                    ->select();
        foreach($Temporarys as $k => $v){
            $Temporary[$k] = array_merge($temp_id[$k],$Temporarys[$k]);
        }            

        if($Temporary){
            exit(json_encode([
                'status'=> 1,
                'msg'   => '成功',
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $Temporary
            ]));
        }else{
            exit(json_encode([
                'status'=> -1,
                'msg'   => '失败',
            ]));            
        }                         

    }

}