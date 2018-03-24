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
        $supplier_model = M('');
        $db_prefix = C('DB_PREFIX');
        $supplier_count = $supplier_model->table($db_prefix.'agent')->where('')->count();
        $page = new Page($supplier_count, 10);
        $show = $page->show();
        $supplier_list = $supplier_model
            ->field('s.*,a.admin_id,a.user_name')
            ->table($db_prefix.'agent s')
            ->join('LEFT JOIN '.$db_prefix.'admin AS a ON a.agent_id = s.agent_id')
            ->where('')
            ->limit($page->firstRow, $page->listRows)
            ->select();
       // dump($supplier_list);die;
        $this->assign('list', $supplier_list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 代理资料  asdsad
     */
    public function agent_info()
    {
        $suppliers_id = I('get.agent_id', 0);
        if ($suppliers_id) {
            $db_prefix = C('DB_PREFIX');
            $suppliers_model = M('agent');
            $info = $suppliers_model
                ->field('s.*,a.admin_id,a.user_name')
                ->table($db_prefix.'agent s')
                ->join('LEFT JOIN '.$db_prefix.'admin AS a ON a.agent_id = s.agent_id')
                ->where(array('s.agent_id' => $suppliers_id))
                ->find();
            $this->assign('info', $info);
        }
        $act = empty($suppliers_id) ? 'add' : 'edit';
        $this->assign('act', $act);
        $admin_id = $_SESSION['admin_id'];
        //dump($admin_id);die;
        if($admin_id == 1){
            $admin = M('admin')->field('admin_id,user_name')->where('1=1')->select();
            $this->assign('admin', $admin);
        }else{
            $admin = M('admin')->field('admin_id,user_name')->where("admin_id=$admin_id")->select();
            $this->assign('admin', $admin);
        }

        $this->display();
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

        $Join = M('Join')->limit($Page->firstRow.','.$Page->listRows)->order('add_time DESC')->select();

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