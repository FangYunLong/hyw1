<?php
/**
 *
//
 * Author: 当燃      
*: 2015-09-09
 */
namespace Home\Controller;

class DriverController extends BaseController {



    /**
     * 招司机--发布简历
     *token
     * user_name,sex,age,jingyan,xueli,mobile,address(详细地址),thumb(叉车证)，province(省)，city(市区)
    */
    public function publishResume()
    {
        //上传证件
        if ($_FILES['thumb']['size'] > 0) {
            //定义上传路径
            $path="./Public/Upload/cartpart/";
            $uploadinfo=$this->fileUploadNews($path,$_FILES);
            // $uploadinfo=$this->fileUpload($path);
            $data['thumb']='http://hyw.web66.cn:8092/Public/Upload/cartpart/'. $uploadinfo['thumb'];
        }
        //查看是否存在简历信息
        $info = M('Resume')->where(array('user_id'=>$user_id))->find();
        if (!empty($info)){
            $res = M('Resume')->where(array('user_id'=>$user_id))->save($data);
        }else{
            $res = M('Resume')->add($data);
        }
        if (!$res){
            exit(json_encode(array('status'=>-1,'msg'=>'发布简历失败')));
        }
        exit(json_encode(array('status'=>1,'msg'=>'发布简历成功')));

    }

    /**
     * 招司机--简历回显
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
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问我的求职')));
        }
        $res = M('Resume')->where(array('user_id'=>$user_id))->find();
        //dump($res);
        if (!$res)
            exit(json_encode(array('status'=>2,'msg'=>'还没有添加简历')));
        exit(json_encode(array('status'=>1,'msg'=>'简历信息','result'=>$res)));
    }

    /**
     * 招司机--所在省
     * 返回值--省-市
    */
    public function address()
    {
        //$data = M('SwfArea')->field('id,name')->where(array('parent_id'=>0))->select();
        $data = M('SwfArea')->field('id,name,parent_id')->select(array('index'=>id));
        $arr = array();
        foreach ($data as $k=> $v) {
            if ($v['parent_id']==0) {
               $arr = $v;
                foreach ($data as $vv) {

                    if ($vv['parent_id']==$v['id']) {

                        $arr[] = $vv;
                        //$v[] = $vv;
                    }
                }
                array_unshift($arr,$v);
                $res[$k] = $arr;
            }
        }
       //dump($res);
        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'所在省/市成功','result'=>$res)));
        exit(json_encode(array('status'=>-1,'msg'=>'所在省-错误','result'=>'')));
        //dump($data);
    }

    /**
     * 招司机--所在市
     * id(城市id)
    */
    public function city()
    {
        $parent_id = I('post.id');
        $data = M('SwfArea')->field('id,name')->where(array('parent_id'=>$parent_id))->select();
        if ($data)
            exit(json_encode(array('status'=>1,'msg'=>'所在市','result'=>$data)));
        exit(json_encode(array('status'=>-1,'msg'=>'所在市-错误','result'=>'')));
    }

    /**
     * 招司机--求职司机信息列表
     *post
     *
    */
    public function driverList($where)
    {
        $_POST = $where;
        $age     = I('post.age');
        $xueli   = I('post.xueli');
        $jingyan = I('post.jingyan');
        $city    = I('post.city');
        $data = $where;
        unset($data['__hash__']);

        if($age&&$age!='other'){
            $ages = explode('-',$age);
            $data['age'] = array('BETWEEN',"{$ages[0]},{$ages[1]}");
        }
        if($age=='other'){
            $data['age'] = array('GT',45);
        }
        if($jingyan){
            $data['jingyan'] = $jingyan;
        }
        if($xueli){
            $data['xueli'] = $xueli;
        }        
        if($city){
            $data['city'] = $city;
        }        
        if($jingyan=='5+'){
            $data['jingyan'] = array('GT',5);
        }
        if($jingyan=='10+'){
            $data['jingyan'] = array('GT',10);
        } 

        $data['_logic'] = 'AND';
        $data['is_hidden'] = 0;
        $count = M('Resume')->where($data)->count();

        if($count<1){
            return false;
        }
        $page    = I('post.page',1);
        $rows    = I('post.rows',9);
        $pages = ceil($count / $rows);
        if($page<1){
            $page = 1;
        }  
        if($page>$pages){
            $page = $pages;
        }              
        $res   = M('Resume')->field('user_id,sex,user_name,age,jingyan,province,city,xueli,is_hidden,mobile,address')->where($data)->page($page,$rows)->select();

        if ($res){
            $date = array('page'=>$page,'pages'=>$pages,'result'=>$res);
            return $date;
        }
    }

    //去除省市名称与APP上的差异
    public function delDiffer($id='')
    {
        $string = M('region')->find($id)['name'];

        if($string == '市辖区' || $string == '县' || $string == '市辖县'){
            return false;
        }

        $string = str_replace('省','',$string);
        $string = str_replace('市','',$string);
        $string = str_replace('自治','',$string);
        $string = str_replace('维吾尔','',$string);
        $string = str_replace('壮族','',$string);
        $string = str_replace('回族','',$string);
        $string = str_replace('特别行政区','',$string);

        return $string;
    }

    //招司机列表
    public function index()
    {   
        $where = I('post.');
        $province = M('Region')->where(['parent_id'=>0])->select();
        $city = M('Region')->where(['parent_id'=>I('province')])->select();
        $data = I('post.');
        $data['province'] = $data['province'] ? $this->delDiffer($data['province']) : null;
        $data['city']     = $data['city'] ? $this->delDiffer($data['city']) : null;
        $data = array_filter($data);

        $list = $this->driverList($data);

        if($list){
            $this->assign('pageRows',pageRows($list['page'],$list['pages']));
            $this->assign('page',['page'=>$list['page'],'pages'=>$list['pages']]);
            $this->assign('list',$list);
        }else{
            $this->assign('page',['page'=>0,'pages'=>0]);
        }

        $this->assign('jingyan',[1=>'1年',2=>'2年',3=>'3年',4=>'4年','5+'=>'5年以上','10+'=>'10年以上']);
        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('where',$where);
        $this->display();
    }

    /**
     * 招司机--求职司机详细信息
     * post
     *
     */
    public function driverDetailedInfo()
    {
        $data = I('post.');
        $data['_logic'] = 'AND';
        $res = M('Resume')->field('user_name,sex,age,jingyan,xueli,mobile,address,province,city,thumb')->where($data)->select()[0];
        $res['sex'] = $res['sex'] == 0 ? '女':'男';
        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'求职司机详细信息','result'=>$res)));
        exit(json_encode(array('status'=>-1,'msg'=>'数据错误','result'=>'')));
    }
}