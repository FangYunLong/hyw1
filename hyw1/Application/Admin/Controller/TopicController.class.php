<?php
/**
 *
//
 * Author: 当燃      
 * 专题管理
*: 2015-09-09
 */

namespace Admin\Controller;

class TopicController extends BaseController {

    public function index(){
        $this->display();
    }
    
    public function topic(){
    	$act = I('GET.act','add');
    	$this->assign('act',$act);
    	$topic_id = I('GET.topic_id');
    	$topic_info = array();
    	if($topic_id){
    		$topic_info = D('topic')->where('topic_id='.$topic_id)->find();
    		$this->assign('info',$topic_info);
    	}
    	
    	$this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'topic')));
    	$this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'topic')));
    	$this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'topic')));
    	$this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'topic')));
    	$this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'topic')));
    	$this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'topic')));
    	$this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'topic')));
    	$this->assign("URL_Home", "");
    	$this->display();
    }
    
    public function topicList(){
    	$Ad =  M('topic');
    	$res = $Ad->where('1=1')->order('ctime')->page($_GET['p'].',10')->select();
    	if($res){
    		foreach ($res as $val){
    			$val['topic_state'] = $val['topic_state']>1 ? '已发布' : '未发布';
    			$val['ctime'] = date('Y-m-d H:i',$val['ctime']);
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);// 赋值数据集
    	$count = $Ad->where('1=1')->count();// 查询满足要求的总记录数
    	$Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $Page->show();// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
    
    public function topicHandle(){
    	$data = I('post.');       
        $data['topic_content'] = $_POST['topic_content']; // 这个内容不做转义        
    	if($data['act'] == 'add'){
            $path = './Public/Upload/top/';
            $file = fileUploadNews($path,$_FILES);
            $data['topic_image'] = $path . $file['file'];
    		$data['ctime'] = time();
    		$r = D('topic')->add($data);
    	}
    	if($data['act'] == 'edit'){
    		$r = D('topic')->where('topic_id='.$data['topic_id'])->save($data);
    	}
    	 
    	if($data['act'] == 'del'){
    		$filename_path = M('Topic')->find($data['topic_id'])['topic_image'];
            $r = D('topic')->where('topic_id='.$data['topic_id'])->delete();
            if($r){
                unlink(iconv("utf-8","gb2312",$filename_path));
                exit(json_encode(1));
            } 
    	}
    	 
    	if($r){
    		$this->success("操作成功",U('Admin/Topic/topicList'));
    	}else{
    		$this->error("操作失败",U('Admin/Topic/topicList'));
    	}
    }

    //下载文件
    public function downloaded()
    {
        $Topic = M('Topic')->find(I('topic_id'));
        $filepath = $Topic['topic_image'];
        // echo $filepath;exit;
        $filename = iconv("utf-8","gb2312",$filepath);
        //打开资源
        $finfo = finfo_open(FILEINFO_MIME);

        // 获取mime类型
        $mime = finfo_file($finfo,$filename);
        //关闭资源
        finfo_close($finfo);
        
        //获取文件的文件名
        $basename = pathinfo($filename);

        $type = explode('.',$basename["basename"])[1];

        //指定下载文件类型的
        header('Content-Type:' . $mime ); 

        //设置head头信息，告知该文件时下载附件并且制定客户端临时存储名称
        header("Content-Disposition:attachment;filename=".  $Topic["topic_title"].'.'.$type);
        //指定下载文件的描述信息
        //指定文件大小的
        header("Content-Length:".filesize($filename));  

        //将内容输出，以便下载。
        readfile($filename);
    }    
}