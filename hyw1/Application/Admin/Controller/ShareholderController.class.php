<?php
namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;
class ShareholderController extends BaseController{


    /**
     * 股东后台密码修改
     */
    public function forgivepw(){
        $session = $_SESSION['admin_id'];
        //dump($session);die;
        $admin_id = $session;
        if($admin_id == 1){}
        if($admin_id != 1 ){
            $info = D('admin')->where("admin_id=$admin_id")->find();
            $info['password'] =  "";
            $this->assign('info',$info);
        }
        $act = empty($admin_id) ? 'add' : 'edit';
        $this->assign('act',$act);
        //$role = D('admin_role')->where('1=1')->select();
        //$this->assign('role',$role);
        $this->display();
    }

    /**    
     * 代理商列表     
     */
    public function agent()
    {
        $this->display();
    }

    //代理商列表
    public function ajaxAgent()
    {
        // 搜索条件
        $condition = array();
        I('company') != ''  ? $company  = trim(I('company'))  : false;
        I('username') != '' ? $username = trim(I('username')) : false;
        I('mobile') != ''   ? $mobile   = trim(I('mobile'))   : false;

        I('company') ? $condition['company'] = ['like',"%{$company}%"]  : false;
        I('username') ? $condition['username'] = ['like',"%{$username}%"]  : false;
        I('mobile') ? $condition['mobile'] = ['like',"%{$mobile}%"]  : false;
        
        $condition['type'] = 2;

        $count = M('Join')->where($condition)->count();

        $Page  = new AjaxPage($count,20);

        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }

        $show = $Page->show();

        $Join = M('Join')->where($condition)->limit($Page->firstRow.','.$Page->listRows)->order('add_time DESC')->select();

        $this->assign('Join',$Join);
        $this->assign('page',$show);
        $this->display();
    }

    /**
     * 代理资料  asdsad
     */
    public function agent_info()
    {
        $join_id = I('join_id');
        // $userData['agent_area'] = 0;
        // $userData['level_id'] = ['gt',2];
        // //查找代理商、股东身份，并且还没有分配代理区域
        // $agent = M('Users')->field('user_id,mobile,level_id')->where($userData)->select();
        // $UserLevel = M('UserLevel')->select();

        // $level_id = array();
        // foreach($UserLevel as $key => $val){
        //     $level_id[$val['level_id']] = $val['level_name'];
        // }

        if($join_id){
            $Join = M('Join')->find($join_id);
            $User = M('Users')->field('user_id,mobile,level_id')->find($Join['user_id']);
            $agent[] = $User;
            $this->assign('info',$Join);
            $city = M('Region')->where('parent_id = ' . $Join['province'])->select();
            $this->assign('city',$city);
            $district = M('Region')->where('parent_id = ' . $Join['city'])->select();
            $this->assign('district',$district);            
        }

        $province = M('Region')->where('parent_id = 0')->select();
        $this->assign('province',$province);
        // $this->assign('agent',$agent);
        // $this->assign('level_id',$level_id);
        $this->display();
    }

    //新增/编辑代理商信息
    public function setJoin()
    {
        // $join_id = I('join_id');
        $data = I('post.');
        unset($data['__hash__']);
        $data = array_filter($data);
        // $Users = M('Users')->find($data['user_id']);

        // if(!$data['user_id']){
        //     $this->error('请选择代理人！');exit;
        // }

        if(!$data['company']){
            $this->error('请选择公司名！');exit;
        }

        if(!$data['username']){
            $this->error('请选择联系人！');exit;
        }

        if(!$data['mobile']){
            $this->error('请选择联系方式！');exit;
        }

        if(!$data['province']){
            $this->error('请选择省份！');exit;
        }

        if(!$data['address']){
            $this->error('请选择详细地址！');exit;
        }


        // if(!$data['city']){
        //     if($Users['level_id'] != 4){
        //         $this->error('只有股东才能代理省！');exit;
        //     }
        //     $agent_area = $data['province'];
        // }else{
        //     $agent_area = $data['city'];
        // }

        // if($Users['level_id'] <= 2){
        //     $this->error('代理人必须是股东或代理商身份！');exit;
        // }

        // if($Users['agent_area'] > 0){
        //     $this->error('该代理人已代理其他区域！');exit;
        // }

        // $agent_area_count = M('Users')->where(['agent_area'=>$agent_area,'level_id'=>['gt',2]])->find();
// dump($agent_area_count);exit;
        // if($agent_area_count > 0){
        //     $this->error('该地区已有代理商代理！');exit;
        // }

        // M('Join')->startTrans();

        if($data['join_id']){
            $res = M('Join')->save($data);
        }else{
            $data['type'] = 2;
            $data['add_time'] = time();
            $res = M('Join')->add($data);
        }

        if($res){
            // $res2 = M('Users')->save(['user_id'=>$data['user_id'],'agent_area'=>$agent_area]);
            // if($res2){
            //     M('Join')->commit();
            //     $this->success('操作成功！',U('Shareholder/agent'));exit;
            // }else{
            //     M('Join')->rollback();
            //     $this->error('操作失败！');
            // }
            $this->success('操作成功！',U('Shareholder/agent'));exit;
        }else{
            // M('Join')->rollback();
            $this->error('操作失败！');
        }
        exit;
    }

    /**
     * 代理商增删改
     */
    public function supplierHandle()
    {
        $data = I('post.');
        //dump($data);die;
        $suppliers_model = M('Agent');
        //增
        if ($data['act'] == 'add') {
            unset($data['agent_id']);
            $count = $suppliers_model->where("agent_name='" . $data['agent_name'] . "'")->count();

            if ($count) {
                $this->error("此代理商名称已被注册，请更换", U('Admin/Shareholder/agent'));
            } else {
                //dump($data);die;
                $r = $suppliers_model->add($data);
                if (!empty($data['admin_id'])) {
                    $admin_data['agent_id'] = $suppliers_model->getLastInsID();
                    M('admin')->where(array('agent_id' => $admin_data['agent_id']))->save(array('agent_id' => 0));
                    M('admin')->where(array('admin_id' => $data['admin_id']))->save($admin_data);
                }
            }
        }
        //改
        if ($data['act'] == 'edit' && $data['agent_id'] > 0) {
            $r = $suppliers_model->where('agent_id=' . $data['agent_id'])->save($data);
            if (!empty($data['admin_id'])) {
                $admin_data['agent_id'] = $data['agent_id'];
                M('admin')->where(array('agent_id' => $admin_data['agent_id']))->save(array('agent_id' => 0));
                M('admin')->where(array('admin_id' => $data['admin_id']))->save($admin_data);
            }
        }
        //删
        if ($data['act'] == 'del' && $data['agent_id'] > 0) {
            $r = $suppliers_model->where('agent_id=' . $data['agent_id'])->delete();
            M('admin')->where(array('agent_id' => $data['agent_id']))->save(array('agent_id' => 0));
        }
        //dump($r);die;
        if ($r !== false) {
            $this->success("操作成功", U('Admin/Shareholder/agent'));
        } else {
            $this->error("操作失败", U('Admin/Shareholder/agent'));
        }
    }

    //加盟商页面
    public function joinList()
    {
        $this->display();
    }

    //加载加盟商详情
    public function joinInfo()
    {
        $join_id = I('join_id');
        $JoinInfo = M('Join')->field('j.*,concat(r1.name,r2.name,r3.name,j.address) as address')
                             ->alias('j')
                             ->join('tp_region as r1 on j.province=r1.id','LEFT')
                             ->join('tp_region as r2 on j.city=r2.id','LEFT')
                             ->join('tp_region as r3 on j.district=r3.id','LEFT')
                             ->find($join_id);

        $this->assign('joinInfo',$JoinInfo);
        $this->display();
    }

    //加盟商列表
    public function ajaxJoinList()
    {
        $page = I('page');

        $count = M('Join')->count();

        $Page  = new AjaxPage($count,20);

        $show = $Page->show();

        $Join = M('Join')->where(['type'=>1])->limit($Page->firstRow.','.$Page->listRows)->order('add_time DESC')->select();

        $this->assign('Join',$Join);
        $this->assign('page',$show);
        $this->display();
    }

    //审核加盟商
    public function setJoinStatus()
    {
         $join_id = I('post.join_id');
         $data['join_id'] = $join_id;
         $Join = M('Join')->find($join_id); 

         if($Join['join_status'] == 1){
            $data['join_status'] = 0;
         }else{
            $data['join_status'] = 1;
         }

         $res = M('Join')->save($data);

         if($res){
            exit(json_encode(['status'=>1,'msg'=>'操作成功！','join_status'=>$data['join_status']]));
         }else{
            exit(json_encode(['status'=>-1,'msg'=>'操作失败！']));      
         }  
    }

    //删除加盟商
    public function delJoin()
    {
         $join_id = I('post.join_id');
         $res = M('Join')->delete($join_id);

         if($res){
            exit(json_encode(['status'=>1,'msg'=>'操作成功！']));
         }else{
            exit(json_encode(['status'=>-1,'msg'=>'操作失败！']));      
         }          
    }
}