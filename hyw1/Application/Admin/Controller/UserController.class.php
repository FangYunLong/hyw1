<?php
/**
 *
//
 * Author: 当燃      
*: 2015-09-09
 */

namespace Admin\Controller;

use Think\AjaxPage;
use Think\Page;
use Admin\Logic\UsersLogic;

class UserController extends BaseController {

    public function index(){
        //获取所有会员组
        $model= M('user_level');
        $user_level = $model->field("level_id , level_name")->select();
        //dump($user_level);die;
        $this->assign("users",$user_level);
        $this->display();
    }

    /**
     * 会员列表
     */
    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('level_id') ? $condition['level_id'] = I('level_id') : false;
        $sort_order = I('order_by','user_id').' '.I('sort','desc');
               
        $model = M('users');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();
                
        $user_id_arr = get_arr_column($userList, 'user_id');
        if(!empty($user_id_arr))
        {
            $first_leader = M('users')->query("select first_leader,count(1) as count  from __PREFIX__users where first_leader in(".  implode(',', $user_id_arr).")  group by first_leader");
            $first_leader = convert_arr_key($first_leader,'first_leader');
            
            $second_leader = M('users')->query("select second_leader,count(1) as count  from __PREFIX__users where second_leader in(".  implode(',', $user_id_arr).")  group by second_leader");
            $second_leader = convert_arr_key($second_leader,'second_leader');            
            
            $third_leader = M('users')->query("select third_leader,count(1) as count  from __PREFIX__users where third_leader in(".  implode(',', $user_id_arr).")  group by third_leader");
            $third_leader = convert_arr_key($third_leader,'third_leader');            
        }
        /*n级下线数*/
        $this->assign('first_leader',$first_leader);
        $this->assign('second_leader',$second_leader);
        $this->assign('third_leader',$third_leader);
        /*n级下线数 end*/
        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('level',M('user_level')->getField('level_id,level_name'));
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /**
     * 会员详细信息查看
     */
    public function detail(){
        $uid = I('get.id');
        $user = D('users')->where(array('user_id'=>$uid))->find();
        if(!$user)
            exit($this->error('会员不存在'));
        if(IS_POST){
            //  会员信息编辑
            $password = I('post.password');
            $password2 = I('post.password2');
            if($password != '' && $password != $password2){
                exit($this->error('两次输入密码不同'));
            }
            if($password == '' && $password2 == ''){
                unset($_POST['password']);
            }else{
                $_POST['password'] = encrypt(md5($_POST['password']));
            }
            $data = I('post.');

            if($user['level_id'] > 2){
                if(!$data['province']){
                    $this->error('请选择省份！');exit;
                }

                if(!$data['city']){
                    if($user['level_id'] != 4){
                        $this->error('只有股东才能代理省！');exit;
                    }
                    $agent_area = $data['province'];
                }else{
                    $agent_area = $data['city']; 
                }   

                $agent_area_count = M('Users')->where(['agent_area'=>$agent_area,'level_id'=>['gt',2]])->count();

                if($agent_area_count > 0 && $agent_area != $user['agent_area']){
                    $this->error('该区域已有代理商代理！');exit;
                }
                $data['agent_area'] = $agent_area;
            }            

            $row = M('users')->where(array('user_id'=>$uid))->save($data);

            if($row)
                exit($this->success('修改成功',U("User/index")));
            exit($this->error('未作内容修改或修改失败'));
        }
        $Resume = M('Resume')->where(['user_id'=>$user['user_id']])->find();
        $Resume['thumb'] ? $user['card_path'] = $Resume['thumb'] : null;
                
        $user['first_lower'] = M('users')->where("first_leader = {$user['user_id']}")->count();
        $user['second_lower'] = M('users')->where("second_leader = {$user['user_id']}")->count();
        $user['third_lower'] = M('users')->where("third_leader = {$user['user_id']}")->count();
        if($user['level_id'] > 2){
            $Region = M('Region')->find($user['agent_area']);
            $province = M('Region')->where(['level'=>1])->select();
            if($Region['level'] == 2){
                $city = M('Region')->where(['parent_id'=>$Region['parent_id']])->select();
                $where = ['province'=>$Region['parent_id'],'city'=>$Region['id']];
            }else{
                $city = M('Region')->where(['parent_id'=>$Region['id']])->select();
                $where = ['province'=>$Region['id'],'city'=>0];
            }
            $this->assign('province',$province);
            $this->assign('city',$city);
            $this->assign('where',$where);
        }

        $this->assign('level_id',C('level_id'));
        $this->assign('user',$user);
        $this->display();
    }

    public function lookImg()
    {
        $user_id = I('get.user_id');
        $img   = I('get.img');
        $Users = M('Users')->find($user_id);
        $this->assign('Users',$Users);
        $this->assign('img',$img);
        $this->display();
    }

    /**
     * 会员添加
     */
    public function add_user(){
    	if(IS_POST){
            $username  = I('post.mobile','');
            $mobile    = $username;
            $password  = I('post.password','');
            $password2 = I('post.password2','');
            $level_id  = I('post.level_id','');
            $mobile2   = I('post.mobile2');
            $data      = I('post.');
            // dump($data);exit;
            if(!$username || !$password){
                $this->error('请输入账号与密码！');exit;
            }
            //验证两次密码是否匹配
            if($password != $password2){
                $this->error('两次输入密码不一致！');exit;
            }
            //验证是否存在用户名
            $check = M('Users')->where(array('mobile'=>$username))->find();
            if($check){
                $this->error('该账号已被注册！');exit;
            }
            if(!empty($mobile2)){
                $UsersReferees = M('Users')->where(['mobile'=>$mobile2])->find();
                if(!$UsersReferees){
                    $this->error('没有该推荐人！');exit;
                }
            }
            $map['password'] = encrypt(md5($password));
            $map['reg_time'] = time();
            // dump($_FILES);exit;
            //判断是否车主注册
            if ($level_id==2) {
                //调用上传类，上传执照
                if ($_FILES['cart_path']['size']>0) {
                    //定义上传路径
                    $path="./Public/Upload/reg/";
                    $uploadinfo  = fileUploadNews($path,$_FILES);
                    if(!$uploadinfo){
                        $this->error('图片错误！');exit;
                    }                
                    $map['cart_path']  = 'http://hyw.web66.cn:8092/Public/Upload/reg/'.$uploadinfo['cart_path'];
                }else {
                    //没有上传文件
                    $this->error('您还没有上传营业执照');exit;
                }
            }
            
            if($level_id > 2){

                if(!$data['province']){
                    $this->error('请选择省份！');exit;
                }

                $array = [1,338,10543,31929,47494,47495];

                if(!$data['city']){
                    if($data['level_id'] != 4 && !in_array($data['province'],$array)){
                        $this->error('只有股东才能代理省！');exit;
                    }
                    $agent_area = $data['province'];
                }else{
                    $agent_area = $data['city']; 
                }   

                $agent_area_count = M('Users')->where(['agent_area'=>$agent_area,'level_id'=>['gt',2]])->find();

                if($agent_area_count){
                    $this->error('该区域已有代理商代理！');exit;
                }
            }


            $map['token']      = md5(time().mt_rand(1,99999)); //生成token
            $map['zhanghu']    = uniqid();                     //生成账户号----13位,利用uniqid()方法生成
            $map['level_id']   = $level_id;                    //添加用户角色
            $map['mobile']     = $username;                    //手机号
            $map['mobile2']    = I('post.mobile2');            //推荐人手机号
            $map['nickname']   = I('post.nickname');           //昵称
            $map['sex']        = I('post.sex');                //性别
            $map['agent_area'] = $agent_area;                  //代理区域
            $user_id = M('Users')->add($map);                  //将用户信息写入数据库
            
			if($user_id){
				$this->success('添加成功',U('User/index'));exit;
			}else{
				$this->error('添加失败,'.$res['msg'],U('User/index'));exit;
			}
    	}
        //获取所有会员组
        $model= M('user_level');
        $user_level = $model->field("level_id , level_name")->select();
        $this->assign("users",$user_level);
    	$this->display();
    }

    /**
     * 用户收货地址查看
     */
    public function address(){
        $uid = I('get.id');
        $lists = D('user_address')->where(array('user_id'=>$uid))->select();
        $regionList = M('Region')->getField('id,name');
        $this->assign('regionList',$regionList);
        $this->assign('lists',$lists);
        $this->display();
    }

    /**
     * 删除会员
     */
    public function delete(){
        $uid = I('get.id');
        $row = M('users')->where(array('user_id'=>$uid))->delete();
        if($row){
            $this->success('成功删除会员');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 账户资金记录
     */
    public function account_log(){
        $user_id = I('get.id');
        //获取类型
        $type = I('get.type');
        //获取记录总数
        $count = M('account_log')->where(array('user_id'=>$user_id))->count();
        $page = new Page($count);
        $lists  = M('account_log')->where(array('user_id'=>$user_id))->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        $this->display();
    }

    /**
     * 账户资金调节
     */
    public function account_edit(){
        $user_id = I('get.id');
        if(!$user_id > 0)
            $this->error("参数有误");
        if(IS_POST){
            //获取操作类型
            $m_op_type = I('post.money_act_type');
            $user_money = I('post.user_money');
            $user_money =  $m_op_type ? $user_money : 0-$user_money;

            $p_op_type = I('post.point_act_type');
            $pay_points = I('post.pay_points');
            $pay_points =  $p_op_type ? $pay_points : 0-$pay_points;

            $f_op_type = I('post.frozen_act_type');
            $frozen_money = I('post.frozen_money');
            $frozen_money =  $f_op_type ? $frozen_money : 0-$frozen_money;

            $desc = I('post.desc');
            if(!$desc)
                $this->error("请填写操作说明");
            if(accountLog($user_id,$user_money,$pay_points,$desc)){
                $this->success("操作成功",U("Admin/User/account_log",array('id'=>$user_id)));
            }else{
                $this->error("操作失败");
            }
            exit;
        }
        $this->assign('user_id',$user_id);
        $this->display();
    }
    
    public function recharge(){
    	$timegap = I('timegap');
    	$nickname = I('nickname');
    	$map = array();
    	if($timegap){
    		$gap = explode(' - ', $timegap);
    		$begin = $gap[0];
    		$end = $gap[1];
    		$map['ctime'] = array('between',array(strtotime($begin),strtotime($end)));
    	}
    	if($nickname){
    		$map['nickname'] = array('like',"%$nickname%");
    	}  	
    	$count = M('recharge')->where($map)->count();
    	$page = new Page($count);
    	$lists  = M('recharge')->where($map)->order('ctime desc')->limit($page->firstRow.','.$page->listRows)->select();
    	$this->assign('page',$page->show());
    	$this->assign('lists',$lists);
    	$this->display();
    }

    //会员类型
    public function level(){
    	$act = I('GET.act','add');
    	$this->assign('act',$act);
    	$level_id = I('GET.level_id');
    	$level_info = array();
    	if($level_id){
    		$level_info = D('user_level')->where('level_id='.$level_id)->find();
    		$this->assign('info',$level_info);
    	}
    	$this->display();
    }

    //会员分组列表
    public function levelList(){
    	$Ad =  M('user_level');
    	$res = $Ad->where('1=1')->order('level_id')->page($_GET['p'].',10')->select();
        //dump($res);die;
    	if($res){
    		foreach ($res as $val){
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
    	$count = $Ad->where('1=1')->count();
    	$Page = new \Think\Page($count,10);
    	$show = $Page->show();
    	$this->assign('page',$show);
    	$this->display();
    }
    
    public function levelHandle(){
    	$data = I('post.');
    	if($data['act'] == 'add'){
    		$r = D('user_level')->add($data);
    	}
    	if($data['act'] == 'edit'){
    		$r = D('user_level')->where('level_id='.$data['level_id'])->save($data);
    	}
    	 
    	if($data['act'] == 'del'){
    		$r = D('user_level')->where('level_id='.$data['level_id'])->delete();
    		if($r) exit(json_encode(1));
    	}
    	 
    	if($r){
    		$this->success("操作成功",U('Admin/User/levelList'));
    	}else{
    		$this->error("操作失败",U('Admin/User/levelList'));
    	}
    }

    /**
     * 搜索用户名
     */
    public function search_user()
    {
        $search_key = trim(I('search_key'));        
        if(strstr($search_key,'@'))    
        {
            $list = M('users')->where(" email like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['email']}</option>";
            }                        
        }
        else
        {
            $list = M('users')->where(" mobile like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['mobile']}</option>";
            }            
        } 
        exit;
    }
    
    /**
     * 分销树状关系
     */
    public function ajax_distribut_tree()
    {
          $list = M('users')->where("first_leader = 1")->select();
          $this->display();
    }

    /**
     *
     * @time 2016/08/31
     * @author dyr
     * 发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('users')->field('user_id,nickname')->where(array('user_id' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users',$users);
        $this->display();
    }

    /**
     * 发送系统消息
     * @author dyr
     * @time  2016/09/01
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $type = I('post.type', 0);//个体or全体
        $admin_id = session('admin_id');
        $users = I('post.user');//个体id
        $message = array(
            'admin_id' => $admin_id,
            'message' => $message,
            'category' => 0,
            'send_time' => time()
        );
        if ($type == 1) {
            //全体用户系统消息
            $message['type'] = 1;
            M('Message')->data($message)->add();
        } else {
            //个体消息
            $message['type'] = 0;
            if (!empty($users)) {
                $create_message_id = M('Message')->data($message)->add();
                foreach ($users as $key) {
                    M('user_message')->data(array('user_id' => $key, 'message_id' => $create_message_id, 'status' => 0, 'category' => 0))->add();
                }
            }
        }
        echo "<script>parent.{$call_back}(1);</script>";
        exit();
    }

    /**
     *
     * @time 2016/09/03
     * @author dyr
     * 发送邮件
     */
    public function sendMail()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $user_where = array(
                'user_id' => array('IN', $user_id_array),
                'email' => array('neq', '')
            );
            $users = M('users')->field('user_id,nickname,email')->where($user_where)->select();
        }
        $this->assign('smtp', tpCache('smtp'));
        $this->assign('users', $users);
        $this->display();
    }

    /**
     * 发送邮箱
     * @author dyr
     * @time  2016/09/03
     */
    public function doSendMail()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $title = I('post.title');//标题
        $users = I('post.user');
        if (!empty($users)) {
            $user_id_array = implode(',', $users);
            $users = M('users')->field('email')->where(array('user_id' => array('IN', $user_id_array)))->select();
            $to = array();
            foreach ($users as $user) {
                if (check_email($user['email'])) {
                    $to[] = $user['email'];
                }
            }
            $res = send_email($to, $title, $message);
            echo "<script>parent.{$call_back}({$res});</script>";
            exit();
        }
    }

    /**
     * 提现申请记录
     */
    public function withdrawals()
    {
        $model = M("withdrawals");
        $_GET = array_merge($_GET,$_POST);
        unset($_GET['create_time']);

        $status = I('status');
        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        $create_time = I('create_time');
        $create_time = $create_time  ? $create_time  : date('Y/m/d',strtotime('-1 year')).'-'.date('Y/m/d',strtotime('+1 day'));
        $create_time2 = explode('-',$create_time);
        $where = " create_time >= '".strtotime($create_time2[0])."' and create_time <= '".strtotime($create_time2[1])."' ";

        if($status === '0' || $status > 0)
            $where .= " and status = $status ";
        $user_id && $where .= " and user_id = $user_id ";
        $account_bank && $where .= " and account_bank like '%$account_bank%' ";
        $account_name && $where .= " and account_name like '%$account_name%' ";

        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('create_time',$create_time);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();
    }
    /**
     * 删除申请记录
     */
    public function delWithdrawals()
    {
        $model = M("withdrawals");
        $model->where('id ='.$_GET['id'])->delete();
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn(json_encode($return_arr));
    }

    /**
     * 修改编辑 申请提现
     */
    public  function editWithdrawals(){
        $id = I('id');
        $model = M("withdrawals");
        $withdrawals = $model->find($id);
        $user = M('users')->where("user_id = {$withdrawals[user_id]}")->find();

        if(IS_POST)
        {
            $model->create();

            // 如果是已经给用户转账 则生成转账流水记录
            if($model->status == 1 && $withdrawals['status'] != 1)
            {
                if($user['user_money'] < $withdrawals['money'])
                {
                    $this->error("用户余额不足{$withdrawals['money']}，不够提现");
                    exit;
                }


                accountLog($withdrawals['user_id'], ($withdrawals['money'] * -1), 0,"平台提现");
                $remittance = array(
                    'user_id' => $withdrawals['user_id'],
                    'bank_name' => $withdrawals['bank_name'],
                    'account_bank' => $withdrawals['account_bank'],
                    'account_name' => $withdrawals['account_name'],
                    'money' => $withdrawals['money'],
                    'status' => 1,
                    'create_time' => time(),
                    'admin_id' => session('admin_id'),
                    'withdrawals_id' => $withdrawals['id'],
                    'remark'=>$model->remark,
                );
                M('remittance')->add($remittance);
            }
            $model->save();
            $this->success("操作成功!",U('Admin/User/remittance'),3);
            exit;
        }



        if($user['nickname'])
            $withdrawals['user_name'] = $user['nickname'];
        elseif($user['email'])
            $withdrawals['user_name'] = $user['email'];
        elseif($user['mobile'])
            $withdrawals['user_name'] = $user['mobile'];

        $this->assign('user',$user);
        $this->assign('data',$withdrawals);
        $this->display();
    }
    /**
     *  转账汇款记录
     */
    public function remittance(){
        $model = M("remittance");
        $_GET = array_merge($_GET,$_POST);
        unset($_GET['create_time']);

        $status = I('status');
        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');

        $create_time = I('create_time');
        $create_time = $create_time  ? $create_time  : date('Y/m/d',strtotime('-1 year')).'-'.date('Y/m/d',strtotime('+1 day'));
        $create_time2 = explode('-',$create_time);
        $where = " create_time >= '".strtotime($create_time2[0])."' and create_time <= '".strtotime($create_time2[1])."' ";
        $user_id && $where .= " and user_id = $user_id ";
        $account_bank && $where .= " and account_bank like '%$account_bank%' ";
        $account_name && $where .= " and account_name like '%$account_name%' ";

        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('create_time',$create_time);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();
    }
}