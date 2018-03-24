<?php
/**
 * Author:Lonelytears
*: 2016-12-15
 */
namespace Api\Controller;


class SpecialCartController extends BaseController {


    /*我的特价车*/
    /**
     * 我的特价车--发布回显
     * 测试通过
     */
    public function specialCart()
    {
        //回显
        $cat_list = M('CartArt')->field('id,name,child')->select(array('index'=>'id'));
        $data = array();
        //$child_id = array();
        foreach ($cat_list as $k=>$v) {
            if (isset($v['child'])) {
                $child_id = explode('|',$v['child']);
                //$child_id[]=$v['id'];
                array_unshift($child_id,$v['id']);
                $data[] = $child_id;
            }
        }
        $c = array();
        foreach ($data as $v) {
            $c = array_merge($c,$v);
        }
        $arr = array();
        foreach ($c as $v) {
            $arr[] = $cat_list[$v];
        }
        exit(json_encode(array('status'=>1,'msg'=>'发布页数据回显','result'=>$arr)));
    }
    /**
     * 我的特价车--发布特价车
     * token
     *pinpai、dunwei、cart_type（车类型）、menjia、mj_height、shuju、is_ty、cart_age、use_hours、dcsj（电池使用时长）、description、address、factorytime（出厂时间）、username、mobile、zm_pic、cm_pic、czt_pic、nb_pic、is_special=1（特价车必须带1）
     * 测试通过
    */
    public function publishCart()
    {
        $token = I('post.token');
        $user_id = S($token);
        // dump($user_id);exit;
        // $_POST['pinpai'] = '三菱';
        // $_POST['dunwei'] = '2';
        // $_POST['special_price'] = '32000';
        $data = I('post.');
        $data['is_special'] = 1;
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        //处理上传照片
        //上传图片

        //定义上传路径
        $path="./Public/Upload/ad/";
        $uploadinfo = $this->fileUploadNews($path,$_FILES);
        // $uploadinfo=$this->fileUpload($path);
        $uploadinfo['zm_pic']?$data['zm_pic']   = APP_URL . '/Public/Upload/ad/' . $uploadinfo['zm_pic']:'';
        $uploadinfo['cm_pic']?$data['cm_pic']   = APP_URL . '/Public/Upload/ad/' . $uploadinfo['cm_pic']:'';
        $uploadinfo['nb_pic']?$data['nb_pic']   = APP_URL . '/Public/Upload/ad/' . $uploadinfo['nb_pic']:'';
        $uploadinfo['czt_pic']?$data['czt_pic'] = APP_URL . '/Public/Upload/ad/' . $uploadinfo['czt_pic']:'';

        /*//缩略图
        $image = new \Think\Image();
        //拼接保存路径及名字
        $img=$uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];
        $sm_path=$path.$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];
        $image->open('./Public/Upload/knowledge/'.$img);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
        $image->thumb(150, 150)->save($sm_path);

        //拼变量
        $data['small']=$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];*/
        
        //拼接数据
        $data['user_id'] =$user_id;//车主id
        // dump($data);exit;
        //存数据
        $res = M('Goods')->add($data);
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'我的特价车发布失败')));
        exit(json_encode(array('status'=>1,'msg'=>'我的特价车发布成功')));
    }

    /**
     * 我的特价车--列表
     * token
     * is_special=1
     * 测试通过
     */
    public function specialCartList()
    {
        $token = I('post.token');
        $user_id = S($token);
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $res = M('Goods')->field('goods_id,pinpai,dunwei,zm_pic,special_price')
                         ->where(array('user_id'=>$user_id,'is_on_sale'=>1,'is_special'=>1,'_logic'=>'AND'))
                         ->select();
        if (!$res)
            exit(json_encode(array('status'=>2,'msg'=>'没有数据！')));
        exit(json_encode(array('status'=>1,'msg'=>'我的特价车列表访问成功','result'=>$res)));
    }
    /**
     * 特价车--详情
     * 
     * goods_id
     * 测试通过
     */
    public function specialCartInfo()
    {
        $goods_id = I('post.goods_id');

        if (empty($goods_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'缺少叉车id')));
        }        
        $res = M('Goods')->field('pinpai,dunwei,cart_type,menjia,mj_height,shuju,is_yt,cart_age,use_hours,dcsj,description,special_price,address,factorytime,buy_year,username,mobile,zm_pic,cm_pic,czt_pic,nb_pic')
                         ->where(array('goods_id'=>$goods_id,'is_special'=>1,'is_on_sale'=>1,'_logic'=>'AND'))
                         ->select();
        
        $token = I('post.token');
        $user_id = S($token);
        if($user_id){
            $collect_id = M('GoodsCollect')->where(['user_id'=>$user_id,'goods_id'=>$goods_id])->getField('collect_id');                          
        }        

        //dump($res);
        if (!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'特价车详情访问失败')));
        exit(json_encode(array('status'=>1,'msg'=>'特价车详情访问成功','result'=>$res,'collect_id'=>$collect_id)));

    }
    /**
     * 我的特价车--特价车支付
     *token
     *
    */

    /*我的特价车  end*/

    //特价车列表
    public function specialCartListPub()
    {
        // $_POST['pinpai'] = 'other';
        // $_POST['pinpai'] = '三菱';
        // $_POST['dunwei'] = '2.0';
        // $_POST['dunwei'] = 'other';
        // $_POST['dunwei'] = 'other';
        $_POST['cart_type'] = 'other';

        $rows = I('post.rows','9');
        $page = I('post.page','1');
        $num  = ($page-1)*$rows;

        $data = array_filter(I('post.'));//去除空值字段

        if($data['pinpai'] == 'other'){
            $pinpai = implode(',',C('pinpai'));
            $data['pinpai'] = ['not in',$pinpai];
        }

        if($data['cart_type'] == 'other'){
            $cart_type = implode(',',C('cart_type'));
            $data['cart_type'] = ['not in',$cart_type];
        }

        if(!empty($data['dunwei'])&&$data['dunwei']!='other'){
            $data['dunwei'] = (float)$data['dunwei'];
        }

        if($data['dunwei'] == 'other'){
            $dunwei = implode(',',C('dunwei'));
            $data['dunwei'] = ['not in',$dunwei];
        }

        $data['is_special'] = 1;
        $data['is_on_sale'] = 1;

        $count = M('Goods')->where($data)->count();

        if($count<1){
            exit(json_encode(['status'=>2,'msg'=>'没有数据']));
        }

        $pages = ceil($count / $rows);
        $Goods = M('Goods')->field('goods_id,zm_pic,pinpai,dunwei,cart_type,special_price')
                           ->where($data)
                           ->page($num,$rows)
                           ->select();
        exit(json_encode([
            'status' => 1,
            'msg'    => '成功',
            'pages'  => $pages,
            'page'   => $page,
            'data'   => $Goods
        ]));
    }

    //特价车详情
    public function specialInfo()
    {
        $goods_id = I('post.goods_id');

        if(!$goods_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少goods_id']));
        }
        $res = M('Goods')->field('pinpai,cart_type,dunwei,menjia,mj_height,shuju,is_yt,use_hours,dcsj,description,address,factorytime,username,mobile,zm_pic,cm_pic,czt_pic,nb_pic,buy_year,special_price,is_status')
                          ->where(array('goods_id'=>$goods_id,'is_special'=>1,'is_on_sale'=>1))
                          ->find();

        $token = I('post.token');
        $user_id = S($token);
        if($user_id){
            $collect_id = M('GoodsCollect')->where(['user_id'=>$user_id,'goods_id'=>$goods_id])->getField('collect_id');                          
        }

        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'特价车详情','collect_id'=>$collect_id,'result'=>$res)));//有结果则返回
        exit(json_encode(array('status'=>-1,'msg'=>'不存在该特价车详情','result'=>array())));//没有结果返回空
    }    

}