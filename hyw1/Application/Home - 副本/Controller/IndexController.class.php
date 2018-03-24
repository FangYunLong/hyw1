<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Home\Controller;
use Think\Page;
use Think\Verify;
class IndexController extends BaseController {
    
    //渲染首页
    public function index()
    {

        $today['ip'] = $this->getIPaddress();
        $today['add_time'] = time();
        $today['status'] = 2;
        M('UserIp')->add($today);
        
        //首页轮播图
        $banner = M('Ad')->field('ad_code')->where(array('ad_address'=>'首页banner'))->select();
        foreach ($banner as $k=>$v) {
            $banner[$k]['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
        }

        $goods1 = $this->ajaxGoods(['pinpai'=>'三菱&力至优']);
        // dump($goods1);exit;
        $goods2 = $this->ajaxGoods(['pinpai'=>'丰田']);
        $goods3 = $this->ajaxGoods(['pinpai'=>'林德']);
        $goods4 = $this->ajaxGoods(['pinpai'=>'合力']);
        $goods5 = $this->ajaxGoods(['pinpai'=>'杭叉']);
        $goods6 = $this->ajaxGoods(['pinpai'=>'力至优']);
        $goods7 = $this->ajaxGoods(['pinpai'=>'other']);
// dump($goods7);
        $New['dynamic']  = M('Article')->where(['cat_id'=>5,'is_open'=>1])->order('add_time DESC')->limit(3)->select();
        $New['company']  = M('Article')->where(['cat_id'=>89,'is_open'=>1])->order('add_time DESC')->limit(3)->select();
        $New['industry'] = M('Article')->where(['cat_id'=>90,'is_open'=>1])->order('add_time DESC')->limit(3)->select();

        $Link = M('FriendLink')->where(['is_show'=>1])->order('orderby ASC')->select();

        $this->assign('link',$Link);        
        $this->assign('New',$New);        
        $this->assign('banner',$banner);        
        $this->assign('goods1',$goods1);        
        $this->assign('goods2',$goods2);        
        $this->assign('goods3',$goods3);        
        $this->assign('goods4',$goods4);        
        $this->assign('goods5',$goods5);        
        $this->assign('goods6',$goods6);        
        $this->assign('goods7',$goods7);        
        $this->assign('cart_type',C('cart_type'));        
        $this->assign('dunwei',C('dunwei'));        
        $this->assign('menjia',C('menjia'));        
        $this->assign('pinpai',C('pinpai'));        
        $this->assign('mj_height',C('mj_height'));        
        $this->assign('shuju',C('shuju'));        
        $this->assign('is_yt',C('is_yt'));        
        $this->assign('bydc',C('bydc'));        
        $this->display();
    }


    //获取商品数据
    public function ajaxGoods($where='')
    {
        $array = ['pinpai','cart_type','dunwei','mj_height','menjia','bydc','shuju','is_yt'];

        if($where){
            $data = $where;
            $data['content'] = ['neq',''];
        }else{
            $data = array_filter(I('post.'));
            foreach($data as $k => $v){
                if(!in_array($k,$array)){
                    unset($data[$k]);
                }
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

        if($data['mj_height'] == 'other'){
            $mj_height = implode(',',C('mj_height'));
            $data['mj_height'] = ['not in',$mj_height];
        } 

        if($data['menjia'] == 'other'){
            $menjia = implode(',',C('menjia'));
            $data['menjia'] = ['not in',$menjia];
        } 

        if($data['bydc'] == 'other'){
            $bydc = implode(',',C('bydc'));
            $data['bydc'] = ['not in',$bydc];
        }

        if($data['shuju'] == 'other'){
            $shuju = implode(',',C('shuju'));
            $data['shuju'] = ['not in',$shuju];
        }

        $data['is_on_sale'] = 1;
        $data['is_special'] = 0;
        $data['is_prefer']  = 0;
        // $data['is_on_sale'] = 1;

        $count = M('Goods')->field('goods_id,goods_name,original_img')->where($data)->count();//搜索结果统计

        if($count < 1){
            return false;exit;
        }
        
        $pages = ceil($count/$rows);//总页数
        
        if($where){
            $Goods = M('Goods')->field('goods_id,goods_name,pinpai,cart_type,zm_pic,original_img')
                               ->where($data)
                               ->limit(4)
                               ->order('rand()')
                               ->select();        
            return $Goods;exit;
        }else{
            $Goods = M('Goods')->field('goods_id,goods_name,pinpai,cart_type,zm_pic,original_img')->where($data)->limit(12)->order('goods_id DESC')->select();        
        }
        // dump($_REQUEST);exit;
        // $Goods = M('Goods')->limit(12)->select();
        $this->assign('goodsList',$Goods);
        $this->display();
    }
}