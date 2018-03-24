<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Home\Controller;
use Home\Logic\CartLogic;
use Home\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;
class GoodsController extends BaseController {
    
    //叉车列表
    public function goodsList()
    {      
        $this->display();
    }

    //获取年月租叉车数据
    public function ajaxGoods1($where='')
    {
        $where = ' 1 = 1 '; // 搜索条件
        I('intro')    && $where = "$where and ".I('intro')." = 1" ;
        $brand_name =I('brand_id');
        $cat_type =I('cat_type');
        $tonnage =I('tonnage');
        $cat_kind =I('cat_kind');
        $is_special =I('is_special');
        I('brand_id') && $where = "$where and (pinpai like '%$brand_name%') ";
        I('cat_type') && $where = "$where and (cart_type like '%$cat_type%') ";
        I('cat_kind') && $where = "$where and (chezhong like '%$cat_kind%') ";
        (I('tonnage') !== '') && $where = "$where and dunwei = ".I('tonnage') ;
        (I('is_special') !== '') && $where = "$where and is_special = $is_special";
        (I('is_on_sale') !== '') && $where = "$where and is_on_sale = ".I('is_on_sale') ;
        $cat_id = I('cat_id');
        // 关键词搜索
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if($key_word)
        {
            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%' or pinpai like '%$key_word%')" ;
        }

        if($cat_id > 0)
        {
            $grandson_ids = getCatGrandson($cat_id);
            $where .= " and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
        }


        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        /**  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
        $Page->parameter[$key]   =   urlencode($val);
        }
         */
        $show = $Page->show();
        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";
        $goodsList = $model->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();

        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    public function ajaxGoods()
    {
        $data = I('post.');

        if($data['content_type']){
        	$whereData['pinpai'] = $data['pinpai'];
            $whereData['content'] = ['neq',''];
            $whereData['is_prefer'] = 2;
        }else{
        	$whereData = carCatWhere($data);
            $whereData['is_prefer']  = 0;
        	$whereData['cid'] = ['gt',0];
            session('bydc',$data['bydc'],600);
            session('is_yt',$data['is_yt'],600);
            session('shuju',$data['shuju'],600);
            session('d_pinpai',$data['pinpai'],600);            
            session('d_cart_type',$data['cart_type'],600);            
            session('d_dunwei',$data['dunwei'],600);            
            session('d_menjia',$data['menjia'],600);            
            session('d_mj_height',$data['mj_height'],600);
        }


        $whereData['is_on_sale'] = 1;
        $whereData['is_special'] = 0;
        $page = I('page',1);//当前页
        $rows = I('rows',12);//每一页显示记录数
        
        if($data['content_type']){
        	$count = M('Goods')->where($data)->count();//搜索结果统计
        }else{
        	$count = M('Goods')->alias('g')
	                          ->join('tp_car_cate as cc5 on g.cid=cc5.id')
	                          ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
	                          ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
	                          ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
	                          ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')        
					          ->where($whereData)
					          ->count();
        }

        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->display();
            exit;
        }

        $pages = ceil($count/$rows);//总页数
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;

        if($data['content_type']){
	        $list = M('Goods')->field('goods_id,goods_name,zm_pic,pinpai,cart_type') 
					          ->where($whereData)
					          ->page($page,$rows)
					          ->select();
        }else{
	        $list = M('Goods')->field('goods_id,goods_name,cid,zm_pic,cc1.name as pinpai,cc2.name as cart_type') 
	 						  ->alias('g')
	                          ->join('tp_car_cate as cc5 on g.cid=cc5.id')
	                          ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
	                          ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
	                          ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
	                          ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')        
					          ->where($whereData)
					          ->page($page,$rows)
					          ->select();
			foreach ($list as $key => $val) {
				$rent = rent($val['cid'],12,1800,(int)$data['bydc'],(int)$data['is_yt']);
				$list[$key]['user_bzzj'] = $rent['user_rent'];
				$list[$key]['car_bzzj']  = $rent['car_rent'];
			}
        }

        if ($list){
            $this->assign('goodsList',$list);
            $this->assign('pageRows',pageRows($page,$pages,6));
            $this->assign('page',['page'=>$page,'pages'=>$pages]);
            if($data['content_type']){
                $this->display('ajaxCarData');
            }else{
                $this->display();
            }
        }
    }

    //设置属具  冷库、防爆  备用电池
    public function setShuJu()
    {
        $shuju = I('shuju');
        $is_yt = I('is_yt');
        $bydc = I('bydc');
        session('shuju',$shuju,600);
        session('is_yt',$is_yt,600);
        session('bydc',$bydc,600);
    }

    //年月租-租车详情
    public function goodsInfo()
    {   
        $goods_id  = I('goods_id');

        $goodsInfo = M('Goods')->alias('g')
                               ->field('g.goods_id,g.goods_name,g.description,g.goods_describe,g.factorytime,g.use_hours,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,cc1.name as pinpai,cc2.name as cart_type,cc2.chexing,cc3.name as dunwei,cc4.name as menjia,cc5.name as mj_height')
                               ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                               ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                               ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                               ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                               ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                               ->where(['goods_id'=>$goods_id])
                               ->find();
        if(!$goodsInfo){
            echo '<script>alert("该叉车已下架！")</script>';
            header("location:".U('Home/Index/index'));
            exit;
        }

        $Ad = M('Ad')->where(['ad_id'=>86])->find();
        $this->assign('ad',$Ad);
        $this->assign('goodsInfo',$goodsInfo);
        $this->display();
    }

    //年月租-计算租金
    public function goodsRent1()
    {   
        $goods_id  = I('goods_id');
        $tenancy   = I('tenancy');
        $yhours    = I('yhours');
        $orderInfo = I('post.');
        $user      = session('user');

        if(!$goods_id){
            header("location:".U('Goods/goodsList'));
        }

        $goodsInfo = M('Goods')->find($goods_id);

        if(!$user['level_id']){
            $user['level_id'] = 1;
        }

        // if($goodsInfo['is_on_sale']!=1){
        //     exit('<script>alert("该车已出租")</script>');
        // }

        // $rent = rentCount($user['level_id'],$goods_id,$data['tenancy'],$data['yhours']);

        $rent = rentCount($user['level_id'],$goods_id,$tenancy,$yhours);

        $orderInfo['rent'] = $rent;
        $orderInfo['level_id'] = $user['level_id'];

        $is_yt = C('is_yt');
        $this->assign('is_yt',$is_yt);
        $this->assign('orderInfo',$orderInfo);
        $this->assign('goodsInfo',$goodsInfo);
        $this->display();
    }

    //临时租
    public function goodsTemp()
    {
        $dunwei = C('dunwei');
        $Ad = M('Ad')->where(['ad_id'=>86])->find();
        $this->assign('ad',$Ad);
        $this->assign('dunwei',$dunwei);
        $this->display();
    }   

    /**
     * 临时租--订单提交处理
     * token
     * dunwei
     * username、mobile、address
     */
    public function addOrder()
    {
        $user = session('user');
        $user_id = $user['user_id'];
        $data = I('post.');

        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'请先登录！')));
        }

        if(!$data['address']){
            exit(json_encode(array('status'=>-1,'msg'=>'请填写用车地点！')));
        }

        if(!$data['username']){
            exit(json_encode(array('status'=>-1,'msg'=>'请填写联系人！')));
        }

        if(!$data['mobile']){
            exit(json_encode(array('status'=>-1,'msg'=>'请填写联系方式！')));
        }

        //调用百度地图api，根据地址获得经纬度
        $content = file_get_contents('http://api.map.baidu.com/geocoder/v2/?address='.$data['address'].'&output=json&ak=4pB7ZEGWIgyhP26ykh6BrOynGv1YpZSl');//调用百度api
        $json = json_decode($content); 

        if($json->{'result'}){
            $data['lat'] = $json->{'result'}->{'location'}->{'lat'};
            $data['lng'] = $json->{'result'}->{'location'}->{'lng'};
        }else{
            exit(json_encode(array('status'=>-1,'msg'=>'用车地点不存在！')));
        }   
        $data['user_id']  = $user_id;
        $data['add_time'] = date('Y-m-d H:i:s',time());
        $data['temp_sn']  = 'hywd'.date('YmdHis',time()).mt_rand(1000,9999);
        $res = M('Temporary')->add($data);
        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'临时租提交订成功','temp_id'=>$res)));
        exit(json_encode(array('status'=>-1,'msg'=>'临时租提交订失败')));
    }   

    //叉车参数列表页面
    public function carData()
    {
        $pinpai = I('pinpai',1);
        $pinpai_data = ['','三菱力至优','丰田','林德','合力','杭叉','other'=>'其它'];
        $this->assign('pinpai',$pinpai_data[$pinpai]);
        $this->assign('pinpai_where',$pinpai);
        $this->display();
    }

    //叉车参数详情
    public function carDataInfo()
    {
        $goods_id = I('goods_id');
        $goodsInfo = M('Goods')->find($goods_id);
        $this->assign('goodsInfo',$goodsInfo);
        $this->display();
    }

    //叉车搜索
    public function goodsSearch()
    {
        $keyword = I('keyword');
        // $keyword = '柴油';
        $page    = I('page',1);
        $rows    = I('rows',12);
        $SQL = '1=1';

        if($keyword){
        $SQL .= " AND (`goods_name` LIKE '%{$keyword}%' OR cc1.`name` LIKE '%{$keyword}%' OR cc2.`name` LIKE '%{$keyword}%' OR cc3.`name` LIKE '%{$keyword}%' OR cc4.`name` LIKE '%{$keyword}%')";
        }

        $SQL .= ' AND is_on_sale = 1 AND is_prefer = 0 AND is_special = 0 AND cid > 0';

        $count = M('Goods')->alias('g')
                           ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                           ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                           ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                           ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                           ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                           ->where($SQL)
                           ->count();
           
        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);
        $page < 1      ? $page = 1      : null;
        $page > $pages ? $page = $pages : null;
        $num = ($page-1)*$rows;

        $Goods = M('Goods')->alias('g')
                           ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                           ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                           ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                           ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                           ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                           ->where($SQL)
                           ->order('add_time DESC')
                           ->limit($num,$rows)
                           ->select();
        $this->assign('keyword',$keyword);
        $this->assign('goodsList',$Goods);
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();
    }

    //其他品牌
    public function other()
    {
        $pinpai = I('pinpai',1);
        $pinpai_array = ['','三菱力至优','丰田','林德','合力','杭叉','其它'];        
        $this->assign('pinpai',$pinpai_array[$pinpai]);
        $this->display();
    }

    //对比其他品牌列表
    public function ajaxOtherList()
    {
        $page = I('page',1);
        $rows = I('rows',8);
        $data['cc1.name'] = I('pinpai');
        $data['cc2.name'] = session('d_cart_type');
        $data['cc3.name'] = session('d_dunwei');
        $data['cc4.name'] = session('d_menjia');
        $data['cc5.name'] = session('d_mj_height');
        $data = array_filter($data);
        $data['is_on_sale'] = 1;
        $data['is_special'] = 0;
        $data['is_prefer']  = 0;
        $data['cid']  = ['gt',0];

        $count = M('Goods')->alias('g')
                           ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                           ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                           ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                           ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                           ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                           ->where($data)
                           ->count();
        $pages = ceil($count / $rows);
        $page < 1      ? $page = 1      : null;
        $page > $pages ? $page = $pages : null;
        
        $goodsList = M('Goods')->alias('g')
                               ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                               ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                               ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                               ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                               ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                               ->where($data)
                               ->page($page,$rows)
                               ->select();
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->assign('goodsList',$goodsList);
        $this->display();
    }
}