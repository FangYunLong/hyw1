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
        $banner = M('Ad')->field('ad_code,ad_link,ad_name')->where(array('ad_address'=>'首页banner'))->select();
        foreach ($banner as $k=>$v) {
            $banner[$k]['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
        }

        $goods1 = $this->ajaxGoods(['pinpai'=>'三菱力至优']);
        $goods2 = $this->ajaxGoods(['pinpai'=>'丰田']);
        $goods3 = $this->ajaxGoods(['pinpai'=>'林德']);
        $goods4 = $this->ajaxGoods(['pinpai'=>'合力']);
        $goods5 = $this->ajaxGoods(['pinpai'=>'杭叉']);
        $goods6 = $this->ajaxGoods(['pinpai'=>'力至优']);
        $goods7 = $this->ajaxGoods(['pinpai'=>'其它']);
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

    //年月租商品查询筛选条件过滤
    public function carCatWhere($data='')
    {
        if($data['pinpai'] == 'other'){
            // $pinpai = implode(',',C('pinpai'));
            // $data['pinpai'] = ['not in',$pinpai];
            $whereData['cc1.name'] = '其它';
        }else{
            $whereData['cc1.name'] = $data['pinpai'];
        }

        if($data['cart_type'] == 'other'){
            // $cart_type = implode(',',C('cart_type'));
            // $data['cart_type'] = ['not in',$cart_type];
            $whereData['cc2.name'] = '其它';
        }else{
            $whereData['cc2.name'] = $data['cart_type'];
        }

        if(!empty($data['dunwei'])&&$data['dunwei']!='other'){
            $data['dunwei'] = (float)$data['dunwei'];
        }

        if($data['dunwei'] == 'other'){
            // $dunwei = implode(',',C('dunwei'));
            // $data['dunwei'] = ['not in',$dunwei];
            $whereData['cc3.name'] = '其它';
        }else{
            $whereData['cc3.name'] = $data['dunwei'];
        }

        if($data['menjia'] == 'other'){
            // $menjia = implode(',',C('menjia'));
            // $data['menjia'] = ['not in',$menjia];
            $whereData['cc4.name'] = '其它';
        }else{
            $whereData['cc4.name'] = $data['menjia'];
        }

        if($data['mj_height'] == 'other'){
            // $mj_height = implode(',',C('mj_height'));
            // $data['mj_height'] = ['not in',$mj_height];
            $whereData['cc5.name'] = '其它';
        }else{
            $whereData['cc5.name'] = $data['mj_height'];
        } 

        $whereData = array_filter($whereData);
        return $whereData;
    }

    //获取商品数据
    public function ajaxGoods($where='')
    {
        $array = ['pinpai','cart_type','dunwei','mj_height','menjia','bydc','shuju','is_yt'];

        if($where){
            $whereData = $where;
            // $whereData = $this->goodsWhere($where);
            $whereData['is_prefer'] = 2;
            $whereData['content'] = ['neq',''];
            // dump($data);
        }else{
            $data = array_filter(I('post.'));
            foreach($data as $k => $v){
                if(!in_array($k,$array)){
                    unset($data[$k]);
                }
            }
            session('shuju',$data['shuju'],600);
            session('bydc',$data['bydc'],600);
            session('is_yt',$data['is_yt'],600);            
            session('d_pinpai',$data['pinpai'],600);            
            session('d_cart_type',$data['cart_type'],600);            
            session('d_dunwei',$data['dunwei'],600);            
            session('d_menjia',$data['menjia'],600);            
            session('d_mj_height',$data['mj_height'],600);            
            $whereData = $this->carCatWhere($data);
            $whereData['cid'] = ['gt',0];
            $whereData['is_prefer']  = 0;
        }

        // if($data['bydc'] == 'other'){
        //     $bydc = implode(',',C('bydc'));
        //     $data['bydc'] = ['not in',$bydc];
        // }

        // if($data['shuju'] == 'other'){
        //     $shuju = implode(',',C('shuju'));
        //     $data['shuju'] = ['not in',$shuju];
        // }

        $whereData['is_on_sale'] = 1;
        $whereData['is_special'] = 0;

// dump($whereData);exit;
//         //搜索结果统计
//         $count = M('Goods')->field('goods_id,goods_name,original_img')->where($whereData)->count();

//         if($count < 1){
//             return false;exit;
//         }
        
//         $pages = ceil($count/$rows);//总页数
        
        if($where){
            $Goods = M('Goods')->field('goods_id,goods_name,pinpai,cart_type,zm_pic,original_img')
                               ->where($whereData)
                               ->limit(4)
                               ->order('rand()')
                               ->select();   
            return $Goods;exit;
        }else{
            // $Goods = M('Goods')->field('goods_id,goods_name,pinpai,cart_type,zm_pic,original_img')
            //                    ->where($whereData)
            //                    ->limit(9)
            //                    ->order('goods_id DESC')
            //                    ->select();   
            $Goods = M('Goods')->field('goods_id,goods_name,cc1.name as pinpai,cc2.name as cart_type,zm_pic,original_img')
                               ->alias('g')
                               ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                               ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                               ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                               ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                               ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                               ->where($whereData)
                               ->limit(9)
                               ->order('goods_id DESC')
                               ->select();                                    
        }
        $this->assign('goodsList',$Goods);
        $this->display();
    }

    public function testLevel()
    {
        $goods_id = 246;
        $goods = M('Goods')->find($goods_id);
        $rent = rent($goods['cid'],6,1200);
        dump($rent);
    }
}