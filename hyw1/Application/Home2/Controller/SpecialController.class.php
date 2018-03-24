<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Home2\Controller;
use Think\Page;
use Think\Verify;
class SpecialController extends BaseController {
    
    public function index()
    {
        $dunwei = M('CartArt')->where(['parent_id'=>2])->select();
        $Ad = M('Ad')->where(['ad_id'=>77])->find();
        $pinpai = M('CartArt')->where(['parent_id'=>1])->select();
        $cart_type = M('CartArt')->where(['parent_id'=>3])->select();
        $this->assign('ad',$Ad);
        $this->assign('pinpai',$pinpai);
        $this->assign('dunwei',$dunwei);
        $this->assign('cart_type',$cart_type);
        $this->display();
    }

    public function ajaxSpecialList()
    {
        $rows = I('post.rows','12');
        $page = I('post.page',1);
        $array = ['pinpai','cart_type','dunwei','mj_height','menjia','bydc','shuju','is_yt'];

        $data = array_filter(I('post.'));//去除空值字段

        foreach($data as $k => $v){
            if(!in_array($k,$array)){
                unset($data[$k]);
            }
        }

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
        $data['is_special_car'] = 1;
        $count = M('Goods')->where($data)->count();
// dump($count);
        if($count<1){
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;        
        $Goods = M('Goods')->field('add_time,goods_id,zm_pic,pinpai,dunwei,cart_type,special_price')
                           ->where($data)
                           ->page($page,$rows)
                           ->order('add_time DESC')
                           ->select();
        $this->assign('goodsList',$Goods);
        $this->assign('pageRows',pageRows($page,$pages));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();

    }

    //特价车详情
    public function SpecialInfo()
    {
        $goods_id = I('goods_id');
        $Goods = M('Goods')->find($goods_id);

        $is_yt = C('is_yt');
        $this->assign('goodsInfo',$Goods);
        $this->assign('is_yt',$is_yt);
        $this->display();
    }
}
