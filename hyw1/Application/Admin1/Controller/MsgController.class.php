<?php


namespace Admin\Controller;

class MsgController extends BaseController{
    public function msgList()
    {
        $data = M('Msg')->where('type=2')->order('msg_id desc')->select();
       // dump($data);exit;
        foreach ($data as $k =>$v) {
            $data[$k]['content']= htmlspecialchars_decode($v['content']);
        }
       // $data['content'] = htmlspecialchars($data['content']);

        $this->assign('data',$data);
        //dump($data);exit;
        $this->display('msgList');
    }
    
    //发布消息
    public function addMsg(){
        if (IS_POST) {
            $data = I('post.');
            $this->_addMsg($data);
            exit;
        }
        $this->display('article');
    }

    //发布消息处理
    public function _addMsg($data)
    {
        $data['public_time'] = time();
        $data['type'] = 2;
        $data['user_id'] = $_SESSION['admin_id'];
        //dump($data);die;
        $res = M('Msg')->add($data);

        if (!$res) {
            $this->error('发布消息失败');
        } else {
            $this->success('发布消息成功','msgList');
        }
    }
    //编辑消息
    public function editMsg()
    {
        $msg_id = I('get.msg_id');

        if (IS_POST) {
            $data = I('post.');
            $this->_editMsg($data,$msg_id);
            exit;
        }
        $msg = M('Msg')->where(array('msg_id'=>$msg_id))->find();
        //dump($msg);exit;
        $this->assign('content',$msg['content']);
        $this->assign('msg_id',$msg['msg_id']);
        $this->display();
    }
    //编辑处理
    public function _editMsg($data,$msg_id)
    {
        $data['public_time'] = date('Y-m-d H:i:s',time());
        //dump($data);exit;
        $res = M('Msg')->save($data);
        if (!$res) {
            $this->error('修改消息失败');
        } else {
            $this->success('修改消息成功','msgList');
        }
    }
    //删除
    public function delMsg()
    {
        $msg_id = I('post.msg_id');
        $res = M('Msg')->where(array('msg_id'=>$msg_id))->delete();
        if (!$res) {
            $this->error('删除消息失败');
        } else {
            $this->success('删除消息成功','msgList');
        }
    }

    //意见反馈
    public function opinion()
    {
        $data = M('Opinion')->field('op.*,u.mobile')->alias('op')->join('tp_users as u on op.user_id=u.user_id')->select();
        $this->assign('data',$data);
        $this->display();
    }

    //意见反馈详情留言
    public function opinionInfo()
    {
        $id = I('id');
        $data = M('opinion')->find($id);
        $this->assign('data',$data);
        $this->display();
    }

    //删除意见反馈
    public function delOpinion()
    {
        $id = I('id');
        $res = M('opinion')->delete($id);

        if (!$res) {
            $this->error('删除消息失败');
        } else {
            $this->success('删除消息成功','msgList');
        }
    }
}