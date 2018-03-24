<?php
/**
 * Author:Lonelytears
*: 2016-12-15
 */
namespace Api\Controller;


class LongController extends BaseController {

    /**
     * 年月租--搜索条件回显
    */
    public function condition()
    {
        $cat_list = M('CartArt')->select(array('index'=>'id'));
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
        exit(json_encode(array('status'=>1,'msg'=>'年月租搜索条件回显成功','result'=>$arr)));
    }

    /**
     * 年月租--搜索结果
     */
    public function search()
    {
        $whereData = carCatWhere(I('post.'));

        $whereData['is_special'] = 0;
        $whereData['is_on_sale'] = 1;
        $whereData['is_prefer']  = 0;
        $whereData['cid'] = ['gt',0];
        $page = I('page',1);//当前页
        $rows = I('rows',6);//每一页显示记录数

       	$count = M('Goods')->alias('g')
                           ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                           ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                           ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                           ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                           ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                           ->where($whereData)
                           ->count();//搜索结果统计
        if($count < 1){
            json_App(['status'=>2,'msg'=>'没有数据','result'=>[]]);
        }
        $pages = ceil($count/$rows);//总页数
        
        $list = M('Goods')->field('goods_id,goods_name,zm_pic')
                          ->alias('g')
                          ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                          ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                          ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                          ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                          ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')        
                          ->where($whereData)
                          ->page($page,$rows)
                          ->order('add_time DESC')
                          ->select();

        //同类推荐---随机取6款搜索中的产品出来
        $tl = M('Goods')->field('goods_id,goods_name,zm_pic')
                        ->alias('g')
                        ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                        ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                        ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                        ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                        ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                        ->where($whereData)
                        ->limit(6)
                        ->order('rand()')
                        ->select();

        $arr['pages'] = $pages;
        $arr['rows'] = $rows;
        $arr['list'] = $list;
        $arr['tl'] = $tl;
        if ($list){
            //有搜索结果则返回
            exit(json_encode(array('status'=>1,'msg'=>'年月租-搜索结果成功','result'=>$arr)));
        }else{
            json_App(['status'=>2,'msg'=>'没有数据','result'=>[]]);
        }
        
    }

    public function Similar()
    {
        $data = I('post.');
        $page = isset($_POST['page']) ? I('post.page') : 1;//当前页
        $rows = isset($_POST['rows']) ? I('post.rows') : 6;//每一页显示记录数
    }

    /**
     * 年月租--租车详情
     *goods_id
     * 测试通过
    */
    public function carInfo()
    {
        $data = I('post.');
        $res =  M('Goods')->field('goods_name,pinpai,dunwei,cart_type,menjia,mj_height,shuju,is_yt,use_hours,factorytime,zm_pic,cm_pic,czt_pic,nb_pic,bydc,is_status')
                          ->where(array('goods_id'=>$data['goods_id'],'is_on_sale'=>1))
                          ->find();
                          
        $token = I('post.token');
        $user_id = S($token);
        if($user_id){
            $collect_id = M('GoodsCollect')->where(['user_id'=>$user_id,'goods_id'=>$data['goods_id']])->getField('collect_id');                          
        }

        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'租车详情','result'=>$res,'collect_id'=>$collect_id)));//有结果则返回
        exit(json_encode(array('status'=>-1,'msg'=>'不存在该租车详情','result'=>array())));//没有结果返回空
    }

    //热租品牌--品牌列表
    public function brandHot()
    {
        // if(S('brandHotList')){
        //     $CartArt = S('brandHotList');
        // }else{
            $CartArt = M('CartArt')->field('child')->find(1)['child'];

            $catstr = str_replace('|',',',$CartArt);

            $sql = "SELECT name,concat('http://hyw.web66.cn:8092',pinpai_img) as pinpai_img FROM tp_cart_art WHERE id IN($catstr)";

            $CartArt = M('')->query($sql);

            // $CartArt = M('CartArt')->where(['parent_id'=>846])->select();

            S('brandHotList',$CartArt,7200);
        // }

        $row   = I('post.row',6);
        $page  = I('post.page',1);
        $pages = ceil(count($CartArt) / $row);
        
        if(empty($CartArt)||(!is_array($CartArt))){
            exit(json_encode([
                'status'=>-1,
                'msg'   =>'失败，没有数据'
            ]));            
        }

        $num = ($page-1)*$row;
        $CartList = array_slice($CartArt,$num,$row);
        if($CartList){
            exit(json_encode([
                'status'=>1,
                'msg'   =>'成功',
                'pages' =>$pages,
                'res'   =>$CartList
            ]));
        }else{
            exit(json_encode([
                'status'=>-1,
                'msg'   =>'失败，没有数据'
            ]));
        }
    }

    /**
     * 热租品牌--叉车列表
     */    
    public function brandCarList()
    {
        $pinpai  = I('post.pinpai');
        $rows    = I('post.rows')?I('post.rows'):8;
        $page    = I('post.page')?I('post.page'):1;
        $num     = ($page-1)*$rows;

        if(!$pinpai){
            exit(json_encode([
                'status'   => -1,
                'msg'      => '缺少品牌'
            ]));            
        }        

        $count = M('Goods')->where(['pinpai'=>['eq',$pinpai],'is_special'=>0,'is_prefer'=>0])->count();

        if($count < 1){
            json_App(['status'=>2,'没有数据！']);
        }

        $pages = ceil($count / $rows);        
        $Goods = M('Goods')->field('goods_id,pinpai,goods_name,chezhong,zm_pic')
                           ->where(['pinpai'=>['eq',$pinpai],'is_special'=>0,'is_prefer'=>0])
                           ->limit($num,$rows)
                           ->select();
        if($Goods){
            exit(json_encode([
                'status'   => 1,
                'msg'      => '成功',
                'rows'     => $rows,
                'page'     => $page,
                'pages'    => $pages,
                'res'      => $Goods
            ]));
        }else{
            exit(json_encode([
                'status'   => -1,
                'msg'      => '失败'
            ]));
        }                                 

    }

    /**
     * 年月租-查看其他品牌
     * pinpai
    */
    public function otherBrand()
    {
        $pinpai = I('post.pinpai','',false);
        $res = M('CartArt')->field('name as pinpai')
                           ->where(['parent_id'=>1,'name'=>['neq',$pinpai]])
                           ->select();
        if ($res) {
            exit(json_encode(array('status'=>1,'msg'=>'年月租-查看其他品牌','result'=>$res)));
        } else {
            exit(json_encode(array('status'=>-1,'msg'=>'年月租-查看其他品牌出错','result'=>'')));
        }

    }

    /**
     * 年月租--租车详情
     * goods_id
     * 
     */
    public function carInfos($goods_id='')
    {
        if(!$goods_id){
            return false;
        }

        $res =  M('Goods')->alias('g')
                          ->field('goods_id,cid,goods_name,zm_pic,cm_pic,czt_pic,nb_pic,cc1.name as pinpai,cc1.type as origin,cc2.name as cart_type,cc2.chexing,cc3.name as dunwei,cc4.name as menjia,cc5.name as mj_height,use_hours,factorytime,is_status')
                          ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                          ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                          ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                          ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                          ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                          ->where(['goods_id'=>$goods_id])
                          ->select()[0];
        return $res;
    }    

    //年月租-查看租金
    public function lookMprice()
    {
        $token   = I('token');
        $user_id = S($token);

        $data    = I('post.');

        $tenancy = I('tenancy');
        $yhours  = I('yhours');
        
        $is_yt   = I('is_yt',2);
        $bydc    = I('bydc',0);

        if(!$data['level_id']){
            $data['level_id'] = 2;
        }

        if(!$data['goods_id']){
            json_App(['status'=>-1,'msg'=>'叉车数据错误！']);
        }
        $Goods = $this->carInfos($data['goods_id']);

        if($Goods['origin'] == 1){
            if($data['yhours'] > 3000){
                json_App(['status'=>-1,'msg'=>'国产叉车年使用小时不能超过3000']);
            }
        }else{
            if($data['yhours'] > 5400){
                json_App(['status'=>-1,'msg'=>'年使用小时不能超过5400']);
            }
        }

        $is_yt === null ? $is_yt = 2 : false;
        $rent = rent($Goods['cid'],(int)$tenancy,(int)$yhours,(int)$bydc,(int)$is_yt);

        if(!$rent){
            json_App(['status'=>-1,'msg'=>'计算租金出错！']);
        }else{
            if($data['level_id'] == 1){
                $zujin = $rent['user_rent'];
            }else{
                $zujin = $rent['car_rent'];
            }
        }

        $Goods['yhours']  = $yhours;
        $Goods['tenancy'] = $tenancy;
        $Goods['zujin']   = (string)$zujin;
        $Goods['is_yt']   = I('is_yt',2);
        $Goods['bydc']    = I('bydc',0);
// dump($Goods);
        exit(json_encode([
            'status'      => 1,
            'msg'         => '成功',
            'data'        => $Goods
        ]));
    }

    /**
     * 年月租--车主计算租金
     *goods_id\chexing\dunwei\pinpai\chezhong\$catr_type\is_yt
     */
    //根据车型、吨位、品牌组合
    public function mprice($data='')
    {
        $goods_id = $data['goods_id'];

        if(!$goods_id){
            return false;
        }
        if(!$data['yhours']){
            return false;
        }
        if(!$data['tenancy']){
            return false;
        }

        $Goods = M('Goods')->field('dunwei,pinpai,chezhong,cart_type,is_yt,chexing')
                           ->find($goods_id);

        // dump($Goods);exit;
        // $dunwei     =   I('post.dunwei');
        // $pinpai     =   I('post.pinpai');
        // $chezhong   =   I('post.chezhong');
        // $goods_id   =   I('post.goods_id');
        // $cart_type  =   I('post.cart_type');
        // $is_yt      =   I('post.is_yt');
        // $chexing = I('post.chexing');

        $dunwei     =   $Goods['dunwei'];
        $pinpai     =   $data['pinpai']?$data['pinpai']:$Goods['pinpai'];
        $chezhong   =   $Goods['chezhong'];
        $cart_type  =   $Goods['cart_type'];
        $is_yt      =   $Goods['is_yt'];
        $chexing    =   $Goods['chexing'];        

        $yhours     =   $data['yhours']>600 ? $data['yhours'] : 600;//年使用时间
        $tenancy    =   $data['tenancy'];//租期

        //获取各种价格参数
        $prices     =   M('Goods')->field('chejia,shoujia,yajin,khdzj,dcj,cb,bzzj,hdccj,chexing') 
                                  ->where(array('goods_id'=>$goods_id))
                                  ->find();
        // dump($prices);exit;
        $chexing    =   $prices['chexing'];
        //进口叉车折旧年限，X1=12-年使用时间∕600，年使用时间小于600小时按600小时计算
        $x1         =   12-$yhours/600;
        //X2为国产叉车折旧年限，X2=8-年使用时间*∕600，年使用时间小于600小时按600小时计算；
        $x2         =   8-$yhours/600;
        /*if ($x2<3) {
            exit(json_encode(array('status'=>-1,'msg'=>'国产叉车年使用时间不能超过3000小时')));
        }*/
        //M=600∕季数（季数为按照季度计算的租赁期间，数值按大计，不满一季度按照一季度计算；M>4时，M=0）
        $m          =   (600 / ($tenancy/3 >1 ? $tenancy/3 : 1)) >4 ? (600 / ($tenancy/3 >1 ? $tenancy/3 : 1)) : 0;
        //利息=（车价-押金）×0.21/36
        $lixi       =   ($prices['chejia']-$prices['yajin'])*0.21/36;
        //折旧：①柴油车：[车价×（1-0.7x）]÷（12·X）  （进口叉车使用X1 ，国产叉车使用X2）②电动车：[1.8*电池价+(含电池车价-电池价)*(1-0.75 x)] ÷(12·X)
        $zj1        =   ($prices['chejia']*(1-pow(0.7,$x1)))/(12*$x1);//进口柴油
        $zj2        =   ($prices['chejia']*(1-pow(0.7,$x2)))/(12*$x2);//国产柴油
        $zj3        =   (1.8*$prices['dcj']+($prices['hdccj']-$prices['dcj'])*(1-pow(0.75,$x1)))/(12*$x1);//进口电车
        $zj4        =   (1.8*$prices['dcj']+($prices['hdccj']-$prices['dcj'])*(1-pow(0.75,$x2)))/(12*$x2);//国产电车
        /*//裸租价：客户端租价-服务费
        $luoz = $prices['khdzj'] - $fwf;*/
        //计算公式---[折旧+0.3×车价/（X1×12）-年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10

        $jk_pinpai  =   array('三菱','力至优','丰田','林德');//进口车
        $gc__pinpai =   array('杭叉','合力');//国产车

        //计算租金
        if ($pinpai=='丰田' && $dunwei>=4) {
            $zujin  =   $prices['shoujia']/0.95;
            return $zujin;
        }
        if ($pinpai=='林德' && $cart_type !='高位前移式') {
            $zujin  =   $prices['shoujia']/0.93;
            return $zujin;
        }
        if ($pinpai=='三菱' && $dunwei>=4) {
            $zujin  =   $prices['shoujia']*0.92;
            return $zujin;
        } else if ($pinpai=='三菱' && $dunwei<=3.5) {
            $zujin  =   $prices['shoujia']*0.95;
            return $zujin;
        }
        if ($pinpai=='合力' && $dunwei>=3.5) {
            $zujin  =   $prices['shoujia']/0.9;
            return $zujin;
        }else if($pinpai=='合力' && $dunwei<3.5){
            $zujin  =   $prices['shoujia']/0.8;
            return $zujin;
        }
        if ($is_yt=='0') {
           // echo '1122';
            $zujin  =   $prices['bzzj']+20000*1.21/36/0.95;
            return $zujin;
        }
        if ($is_yt=='1') {
            $prices['chejia']=$prices['chejia']+300000;
            //exit(json_encode(array('status'=>1,'msg'=>'防爆车租金','zujin'=>$zujin)));
        }
        //车型条件
        if ($chexing == 'FD/G') {//FD/G车型
            //吨位条件
            if ($dunwei>=2 && $dunwei<=3.5) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车
                    //[折旧+0.5×车价/（X1×12）-年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×100+300（+150）
                    $fuwufei = $yhours/600*100+300;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.5*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.5*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }

                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车[折旧+0.4×车价/（X2×12）-年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×150+300（+150）
                    $fuwufei = $yhours/600*150+300;
                    if ($chezhong=='柴油') {
                        $zujin=($zj2+0.4*$prices['chejia']/($x2*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj4+0.4*$prices['chejia']/($x2*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }
                }
            }else if ($dunwei>=4 && $dunwei<=5.5) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车  [折旧+0.5×车价/（X1×12）-2*年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                   // 服务费：时间∕600×150+500（+200）
                    $fuwufei = $yhours/600*150+500;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.5*$prices['chejia']/($x1*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.5*$prices['chejia']/($x1*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }

                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车 [折旧+0.4×车价/（X2×12）-1.5*年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×200+500（+200）
                    $fuwufei = $yhours/600*200+500;
                    if ($chezhong=='柴油') {
                        $zujin=($zj2+0.4*$prices['chejia']/($x2*12)-1.5*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj4+0.4*$prices['chejia']/($x2*12)-1.5*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }
                }
            }else if ($dunwei>=6 && $dunwei<=10) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车
                    /*$fuwufei = $yhours/600*150+500;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.5*$prices['chejia']/($x1*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.5*$prices['chejia']/($x1*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }*/
                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车 [折旧+0.4×车价/（X2×12）-2*年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×300+500（+300）
                    $fuwufei = $yhours/600*300+500;
                    if ($chezhong=='柴油') {
                        $zujin=($zj2+0.4*$prices['chejia']/($x2*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj4+0.4*$prices['chejia']/($x2*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }
                }
            }

        } else if($chexing=='FB'){//FB车型
            //吨位条件
            if ($dunwei>=1 && $dunwei<=3) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车[折旧+0.25×车价/（X1×12）-年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×100+150（+150）
                    $fuwufei = $yhours/600*100+150;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.25*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.25*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }

                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车
                }

            }else if ($dunwei>=3.5 && $dunwei<=5) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车[折旧+0.25×车价/（X1×12）-2*年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×120+200（+200）
                    $fuwufei = $yhours/600*120+200;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.25*$prices['chejia']/($x1*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.25*$prices['chejia']/($x1*12)-2*$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }

                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车
                }

            }

        }else if($chexing=='FBR'){//FBR车型
            //吨位条件
            if ($dunwei>=1 && $dunwei<=2) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车[折旧+0.3×车价/（X1×12）-年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                    //服务费：时间∕600×80+150（+100）
                    $fuwufei = $yhours/600*80+150;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.3*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.3*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }

                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车
                }

            }
        }else if($chexing=='FBRE'){//FBRE车型
            //吨位条件
            if ($dunwei>=1.4 && $dunwei<=2.0) {
                if (in_array($pinpai,$jk_pinpai)) {
                    //进口车 [折旧+0.3×车价/（X1×12）-年使用时间/12+利息+M-(年使用时间-1800)/10]÷0.95+服务费+(年使用时间-1800)/10
                        //服务费：时间∕600×100+250（+200）
                    $fuwufei = $yhours/600*100+250;
                    if ($chezhong=='柴油') {
                        $zujin=($zj1+0.3*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    } else if ($chezhong=='电车'){
                        $zujin=($zj3+0.3*$prices['chejia']/($x1*12)-$yhours/12+$lixi+$m-($yhours-1800)/10)/0.95+$fuwufei+($yhours-1800)/10;
                    }
                }else if (in_array($pinpai,$gc__pinpai)) {
                    //国产车
                }
            }
        }

//vendor(Kint,kint,kint);
       /* require './kint/Kint.class.php';
        Kint::dump( $_SERVER);
        d( $_SERVER );*/

        //输出租金
        //echo $zujin;
        return $zujin;
    }



    /**
     * 支付方式-线下支付
     * post
     */
    public function offlinePay()
    {
        $token    = I('post.token');
        $user_id  = S($token);
        $order_id = I('post.order_id');
        $rows     = I('post.row')?I('post.row'):5;
        // $order_id = 809;
        // $user_id  = 2;

        if(empty($user_id)){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        // $Order_user_id = M('Order')
        //                   ->where(['order_id'=>$order_id])
        //                   ->getField('user_id');
        // $BankAccount   = M('BankAccount')
        //                   ->field('cardholder,reserved,idcard,bankname,bank_type,bank_account')
        //                   ->where(['user_id'=>$Order_user_id,'is_del'=>1])
        //                   ->limit($rows)
        //                   ->select();
        $BankAccount = M('BankAccount')
                          ->field('cardholder,bankname,bank_type,bank_account')
                          ->where(['type'=>2])
                          ->limit($rows)
                          ->select();

        if($BankAccount){
            json_App(['status'=>1,'msg'=>'获取数据成功','res'=>$BankAccount]);
        }else{
            json_App(['status'=>-1,'msg'=>'获取数据失败','res'=>[]]);
        }
    }

    /**
     * 支付方式-添加银行卡
     * post
     */
    public function addBank()
    {
        $token     = I('post.token');
        $user_id   = S($token);
        $data      = I('post.');
        // $user_id = 1;

        if(empty($user_id)){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }  
        if(!$data['code']){
            $this->error('请输入短信验证码');
        }
        $res = $this->check_verify($data['reserved'],$data['code']);
        if (!$res){
            exit(json_encode(array('status'=>-1,'msg'=>'验证码错误')));  
        }        

        $BankInfo          = explode('-',bankInfo($data['bank_account']));//根据卡号获取银行卡的信息
        $data['add_time']  = time();        
        $data['user_id']   = $user_id;
        $data['bank_type'] = $BankInfo[2];
        $data['bankname']  = $BankInfo[0];
        $bank = C('bank');
        if(!in_array($data['bankname'],$bank)){
            exit(json_encode(['status'=>-1,'暂不支持该银行！']));
        }

        $result = M('BankAccount')->add($data); 

        if($result){
            json_App(['status'=>1,'msg'=>'添加数据成功','id'=>$result]);
        }else{
            json_App(['status'=>-1,'msg'=>'添加数据失败']);
        }              
    }

    public function Pushs()
    {
        $res = push();

        dump($res);exit;
        // pushMess();
    }

    //订单提交
    public function Order()
    {
        $token    = I('token');
        $user_id  = S($token);
        $goods_id = I('goods_id');
        $data     = I('post.');
        $data['user_id'] = $user_id;
        
        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        $Users = M('Users')->where(['user_id'=>$user_id])->find();

        if(!$Users){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        if($data['number'] < 1){
            exit(json_encode(['status'=>-1,'msg'=>'请选择数量！']));
        }

        if(!$goods_id){
            exit(json_encode(['status'=>-1,'msg'=>'没有该叉车！']));
        }

        if(!$data['province']){
            exit(json_encode(['status'=>-1,'msg'=>'请选择省份！']));
        }

        if(!$data['city']){
            exit(json_encode(['status'=>-1,'msg'=>'请选择城市！']));
        }        

        if($data['city'] == $data['province']){
            unset($data['province']);
        }

        $data['address'] = $data['province'].$data['city'].$data['address'];
        
        $Goods = M('Goods')->alias('g')
                          ->field('g.is_on_sale,g.is_special,g.cid,cc1.name as pinpai,cc2.name as cart_type,cc2.chexing,cc3.name as dunwei,cc4.name as menjia,cc5.name as mj_height')
                          ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                          ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                          ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                          ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                          ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                          ->where(['g.goods_id'=>$goods_id])
                          ->find();

        if($Goods['is_on_sale'] != 1){
            exit(json_encode(['status'=>-1,'msg'=>'该叉车已下架！']));
        }

        if($Goods['is_special'] == 1){
            exit(json_encode(['status'=>-1,'msg'=>'该叉车已下架！']));
        }        

        $data['is_yt'] === null ? $data['is_yt'] = 2 : false;
        $rent = rent($Goods['cid'],$data['tenancy'],$data['yhours'],(int)$data['bydc'],(int)$data['is_yt']);

        if(!$rent){
            exit(json_encode(['status'=>-1,'msg'=>'租金计算异常！']));
        }

        if($Users['level_id'] == 2){
            $data['mprice'] = $rent['car_rent'];
        }else{
            $data['mprice'] = $rent['user_rent'];
        }

        $data['pinpai']    = $Goods['pinpai'];
        $data['cart_type'] = $Goods['cart_type'];
        $data['dunwei']    = $Goods['dunwei'];
        $data['menjia']    = $Goods['menjia'];
        $data['chexing']   = $Goods['chexing'];
        $data['mj_height'] = $Goods['mj_height'];
        $data['add_time'] = date('Y-m-d H:i:s',time());

        do{
            $data['order_sn'] = 'hyw'.date('YmdHis').rand(1000,9999);
        }while(M('Order')->where(['order_sn'=>$data['order_sn']])->find());
        
        $Order = M('Order')->add($data);

        if($Order){
            echo json_encode(['status'=>1,'msg'=>'订单提交成功','order_id'=>$Order,'order_sn'=>$data['order_sn']]);
            exit;
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'订单提交失败']));
        }
    }

    public function pushUser()
    {
            $user_id = 2617;
            $user_list = $this->getUserJpush($user_id);
            $user_list = ['191e35f7e075772e58b','170976fa8ab667be747'];
            $push['registration_id'] = $user_list;
            // dump($user_list);exit;
            // if($user_list){
                $content = '您有新的年月租订单可抢！';
                $type = ['type'=>12,'order_id'=>$Order];
                $res = push(array('registration_id'=>$user_list),$content,$type);
                dump($res);
            // }        
    }

    //推送新订单给车主
    public function pushOrder()
    {
        $order_id = I('order_id');
        $token    = I('token');
        $user_id  = S($token);

        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'请先登录！']));
        }

        if(!$order_id){
            exit(json_encode(['status'=>-1,'msg'=>'订单不存在！']));
        }

        $user_list = $this->getUserJpush($user_id,$order_id);

        if($user_list){
            $push['registration_id'] = $user_list;
            $content = '您有新的年月租订单可抢！';
            $type = ['type'=>12,'order_id'=>$order_id];
            $res = push($push,$content,$type);
        }
        exit(json_encode(['status'=>1,'msg'=>'请求成功！']));
    }

    //获取开启推送功能的用户
    public function getUserJpush($user_id='',$order_id='')
    {
        $Order = M('Order')->where(['order_id'=>$order_id,'user_id'=>$user_id])->find();
        if($Order['order_status'] > 0){
            return false;
        }
        $where['jpush_status'] = 1;//推送状态开启
        $where['level_id']     = 2;//车主身份
        $where['login_status'] = 1;//登录状态
        $where['order_type']   = $Order['order_type'];//1完整版   2精简版
        $where['user_id'] = ['neq',$user_id];

        $identifier = M('Users')->field('identifier')->where($where)->select();

        if(!$identifier){
            return false;
        }

        foreach ($identifier as $key => $val) {
            $user_list[] = $val['identifier'];
        }

        $user_list = array_filter($user_list);//去空值
        $user_list = array_unique($user_list);//去重复
        sort($user_list);//重新排序

        return $user_list;
    }

    /**
     * 年月租-订单详情接口
     * post
     */
    public function orderInfo()
    {
        $token   = I('token');
        $user_id = S($token);

        if(empty($user_id)){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        if(empty(I('order_id'))){
            json_App(['status'=>-1,'msg'=>'缺少订单id']);
        }

        $OrderInfo = M('Order')->alias('o')
                               ->join('tp_goods g on o.goods_id=g.goods_id')
                               ->where(['o.order_id'=>I('order_id')])
                               ->find();
        
        if($OrderInfo){
            json_App(['status'=>1,'msg'=>'获取订单数据成功','res'=>$OrderInfo]);
        }else{
            json_App(['status'=>-1,'msg'=>'获取订单数据失败']);
        }
    }

    //年月租支付接口
    public function yongyiPay()
    {
        $token    = I('post.token');
        $user_id  = S($token);
        $order_id = I('post.order_id');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        if(!$order_id){
            json_App(['status'=>-1,'msg'=>'参数错误！']);
        }

        $Order = M('Order')->find($order_id);

        if(!$Order){
            json_App(['status'=>-1,'msg'=>'参数错误！']);
        }

        $res = yongyipays($Order['order_sn'],$Order['yajin'],$Order['user_id'],'叉车租赁押金',true);
        echo $res;exit;
        if($res){
            exit(json_encode(['status'=>1,'msg'=>'成功','url'=>$res['url'],'data'=>$res['data']]));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'失败']));
        }
    }

    //年月租取消订单
    public function cancelOrder()
    {
        $token   = I('post.token');
        $user_id = S($token);
        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        $order_id = I('post.order_id');
        if(!$order_id){
            exit(json_encode(['status'=>-1,'msg'=>'没有该订单！']));
        }

        $Order = M('Order')->find($order_id);

        if($Order['order_status'] == 1){
            exit(json_encode(['status'=>1,'msg'=>'取消成功！']));
        }

        $orderData['order_status'] = 1;
        $orderData['is_completed'] = 1;//抢单状态改为完成
        $orderData['order_id']     = $order_id;

        $res = M('Order')->save($orderData);

        if($res){
            $OrderInfo = M('OrderInfo')->field('user_id')->where(['order_id'=>$order_id])->select();
            if($OrderInfo){

                $user_id_str = implode(',',$OrderInfo);
                //客户取消订单，抢单成功的车主订单失效
                $sql = "UPDATE tp_order_info 
                        SET status = 2 
                        WHERE order_id IN($user_id_str)";
                M('')->query($sql);

                //查找用户对应的设备标识
                $UsersData['user_id'] = ['in',$user_id_str];
                $identifier = M('Users')->field('identifier')->where($UsersData)->select();
                
                //转为一维数组
                foreach($identifier as $k => $v){
                    $registration_id[$k] = $v['identifier'];
                }          

                //消息推送通知车主
                $receiver = array('registration_id'=>$registration_id);
                $content  = $Order['order_sn'].'该年月租订单已被用户取消，抢单失败！';
                $extras   = array('type'=>2,'order_id'=>$Order['order_id'],'order_status'=>$Order['order_status']);
                push($receiver,$content,$extras);                

            }
            exit(json_encode(['status'=>1,'msg'=>'取消成功！']));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'取消失败！']));
        }
    }

    public function test1()
    {
        $data['_logic']='AND';
        $data['is_type'] = 0;     //年月租订单
        $data['order_status'] = 0;//抢单状态
        $data['is_completed'] = 0;//抢单未完成

        $basic  = tpCache('basic');

        $order_s = $basic['hot_keywords']*60*60; 

        $res   = M('Order')->alias('o')->join('tp_goods as g on o.goods_id=g.goods_id')
                           ->field('o.order_id,o.goods_id,o.address,pinpai,dunwei,chezhong')
                           ->where($data)->where("UNIX_TIMESTAMP(NOW())  - UNIX_TIMESTAMP(o.add_time) < {$order_s}")
                           ->order('o.add_time ASC')
                           ->select();
        dump($res);
    }

    //计划任务-检查订单过期时间
    public function overCheck()
    {
        $fp = fopen('./Public/test.txt','a+');
        fwrite($fp,'\r\n'.date('Y-m-d H:i:s',time()));
        fclose($fp);
        $basic = tpCache('basic');
        $order_over_time = $basic['hot_keywords']*60*60; 
        $temp_over_time  = $basic['hot_keywords1']*60*60;

        //年月租订单过期检查
        $sql   = "SELECT order_id,user_id,order_sn,grab_number 
                  FROM tp_order 
                  WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(add_time) >= {$order_over_time} AND is_over_time = 0 AND order_status = 0";
        $Order = M('')->query($sql);


        foreach($Order as $k => $v){
            $order_id_str[] = $v['order_id'];
        }

        $order_id_str = implode(',',$order_id_str);
        $sql  = "UPDATE tp_order SET is_over_time = 1,is_completed = 1,order_status = (CASE WHEN grab_number = 0 THEN 2 WHEN grab_number > 0 THEN 3 END) WHERE order_id IN({$order_id_str})";
        $res = M('')->execute($sql);

        if($res){
            foreach($Order as $k => $v){
                if($v['grab_number'] == 0){
                    $type = ['type'=>3,'order_id'=>$v['order_id']];
                    $content = $v['order_sn'] . '年月租订单无人接单，订单失效！';
                    Jpush($v['user_id'],$type,$content);//年月租过期无人接单，消息推送
                }
            }
        }

        //临时租订单过期检查
        $sql   = "SELECT temp_id,user_id,temp_sn 
                  FROM tp_temporary 
                  WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(add_time) >= {$temp_over_time} AND status = 1";
        $Temp = M('')->query($sql);

        foreach($Temp as $k => $v){
            $temp_id_str[] = $v['temp_id'];
        }

        $temp_id_str = implode(',',$temp_id_str);
        $sql  = "UPDATE tp_temporary SET status = 4 WHERE temp_id IN({$temp_id_str})";
        $res = M('')->execute($sql);

        if($res){
            foreach($Temp as $k => $v){
                $type = ['type'=>11,'temp_id'=>$v['temp_id']];
                $content = $v['temp_sn'] . '临时租订单无人接单，订单失效！';
                Jpush($v['user_id'],$type,$content);//临时租过期无人接单，消息推送
            }
        }

    }

    //重发订单接口
    public function againOrder()
    {
        $token = I('token');
        $user_id = S($token);
        $order_id = I('post.order_id');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token！']);
        }

        if(!$order_id){
            json_App(['status'=>-1,'msg'=>'数据异常！']);
        }

        $Order = M('Order')->find($order_id);
        // unset($Order['order_id']);
        $Order['order_status'] = 0;
        $Order['is_completed'] = 0;
        $Order['is_offplay']   = 0;
        $Order['grab_number']  = 0;
        $Order['grab_userid']  = '';
        $Order['is_over_time'] = 0;
        $Order['add_time'] = date('Y-m-d H:i:s',time());

        // do{
        //     $Order['order_sn'] = 'hyw'.date('YmdHis').rand(1000,9999);
        // }while(M('Order')->where(['order_sn'=>$Order['order_sn']])->find());
        // json_App(['status'=>1,'msg'=>'重发成功！','data'=>$Order]);exit;

        $res = M('Order')->save($Order);

        if($res){
            $data['order_id'] = $order_id;
            $data['order_sn'] = $Order['order_sn'];
            json_App(['status'=>1,'msg'=>'重发成功！','data'=>$data]);
        }else{
            json_App(['status'=>-1,'msg'=>'重发失败！']);
        }
    }

}