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
class GoodsPrivateController extends BaseController {



    /**
     * 计算租金
     * @param level_id 用户组 1为客户 2为车主
     * @param goods_id 叉车id
     * @param tenancy  租期（月）
     * @param yhours   年使用小时数（小时）
     */
    public function rentCount($level_id='',$goods_id='',$tenancy='',$yhours='')
    {   
        if(!$level_id||!$goods_id||!$tenancy||!$yhours){
            return false;
        }

        $Goods = M('Goods')->find($goods_id);

        if(!$Goods){
            return false;
        }

        //排除特价车
        if($Goods['is_special'] == 1){
            return false;
        }

        $chezhong = $Goods['chezhong'];//车种   柴油   电车
        $chejia   = $Goods['chejia'];  //车价
        $cb       = $Goods['cb'];      //成本
        $dcj      = $Goods['dcj'];     //电池价
        $hdccj    = $Goods['hdccj'];   //含电池车价
        $shoujia  = $Goods['shoujia']; //售价
        $yajin    = $Goods['yajin'];   //押金
        $origin   = $Goods['origin'];  //产地   1 为进口  2为国产
        $chexing  = $Goods['chexing']; //车型
        $dunwei   = $Goods['dunwei'];  //吨位
        $is_yt    = $Goods['is_yt'];   //冷库 0 /防爆 1

        //年使用小时数小于600按600计算
        $yhours = $yhours < 600 ? 600 : $yhours;
        
        //进口叉车折旧年限 = 12-年使用时间∕600，年使用时间小于600小时按600小时计算；
        $x1     = 12 - $yhours / 600;
        
        //国产叉车折旧年限 = 8 -年使用时间∕600，年使用时间小于600小时按600小时计算；
        $x2     = 8  - $yhours / 600;
        
        //判断是国产还是进口车
        $x      = $origin == 1 ? $x1 : $x2;

        //M值 = 600 / 季度 ,季度 = 租期 / 3
        $M      = 600 / ceil($tenancy / 3);
        // $M      = $M > 4 ? 0 : $M;
        
        //利息 = （车价-押金）× 0.21 / 36
        $interest = ($chejia - $yajin) * 0.21 / 36;
        
        //柴油车折旧 = [ 车价 * ( 1- 0.7的x次方 )] / ( 12 * x )
        $dieselDeprec   = ($chejia * ( 1 - pow(0.7,$x) )) / (12 * $x); 
        
        //电动车折旧 = [ 1.8 * 电池价 + ( 含电池车价 - 电池价 ) * (1-0.75的x次方 )] / (12 * X)
        $electricDeprec = (1.8 * $dcj + ($hdccj - $dcj) * (1 - pow(0.75,$x))) / (12 * $x);
        
        //判断是柴油还是电车
        $Deprec = $chezhong == '柴油' ? $dieselDeprec : $electricDeprec;

        switch($chexing){
            case 'FD/G':
                if($dunwei >= 2 && $dunwei <= 3.5){
                    if($origin == 1){
                        $service_fee = $tenancy / 600 * 100 + 300;//服务费   
                        $u1 = 0.5;//公式不定系数1
                        $u2 = 1;  //公式不定系数2
                    }else{
                        $service_fee = $tenancy / 600 * 150 + 300;                      
                        $u1 = 0.4;
                        $u2 = 1;
                    }
                }elseif($dunwei >= 4 && $dunwei <= 5.5){
                    if($origin == 1){
                        $service_fee = $tenancy / 600 * 150 + 500;                      
                        $u1 = 0.5;
                        $u2 = 2;
                    }else{
                        $service_fee = $tenancy / 600 * 200 + 500;                      
                        $u1 = 0.4;
                        $u2 = 1.5;
                    }
                }elseif($dunwei >= 6 && $dunwei <= 10){
                    if($origin == 1){
                        $service_fee = 0;                      
                        
                    }else{
                        $service_fee = $tenancy / 600 * 300 + 500;                      
                        $u1 = 0.4;
                        $u2 = 2;
                    }                                          
                }
            break;
            case 'FB':
                if($dunwei >= 1 && $dunwei <= 3){
                    if($origin == 1){
                        $service_fee = $tenancy / 600 * 100 + 150;                      
                        $u1 = 0.25;
                        $u2 = 1;
                    }else{
                        $u1 = 0;
                        $u2 = 0;
                    }
                }elseif($dunwei >= 3.5 && $dunwei <= 5){
                    if($origin == 1){
                        $service_fee = $tenancy / 600 * 120 + 200;                      
                        $u1 = 0.25;
                        $u2 = 2;
                    }else{
                        $u1 = 0;
                        $u2 = 0;
                    }
                }
            break;
            case 'FBR':
                if($dunwei >= 1 && $dunwei <= 2){
                    if($origin == 1){
                        $service_fee = $tenancy / 600 * 80 + 150;                      
                        $u1 = 0.3;
                        $u2 = 1;
                    }else{
                        $u1 = 0;
                        $u2 = 0;
                    }
                }
            break;
            case 'FBRE':
                if($dunwei >= 1.4 && $dunwei <= 2){
                    if($origin == 1){
                        $service_fee = $tenancy / 600 * 100 + 250;                      
                        $u1 = 0.3;
                        $u2 = 1;
                    }else{
                        $u1 = 0;
                        $u2 = 0;
                    }
                }
            break;
        }

        if($is_yt == 1){
            $service_fee = 833;             //防爆车，服务费变更
            $chejia     += 300000;          //防爆车，车价增加30万
        }else{
            $service_fee = $cb * 0.1 / 36;  //冷库车，服务费变更
        }

        if($level_id == 2){
            $service_fee = 0;//车主不需要任何服务费
        }

        //标准租金 = （折旧 + 不定系数1 × 车价 ÷ （折旧年限 × 12） - 不定系数2 × 年使用时间 ÷ 利息 + M - （年使用时间 - 1800） ÷ 10） ÷ 0.95 + 服务费 + （年使用时间 - 1800） ÷ 10;
        $standard_rent = ($Deprec + $u1 * $chejia / ($x * 12) - $u2 * $yhours / 12 + $interest + $M - ($yhours - 1800) / 10) / 0.95 + $service_fee + ($yhours - 1800) / 10;

        if($is_yt == '0'){
            //冷库车在标准租金上增加费用
            $standard_rent = $standard_rent + 20000 * 1.21 / 36 / 0.95;
        }

        $standard_rent = $u1 == 0 && $u2 == 0 ? 0 : $standard_rent;

        return ceil($standard_rent);
    }  

    //年月租-计算租金
    public function goodsRent()
    {   

        $goods_id  = I('goods_id');
        $tenancy   = I('tenancy');
        $yhours    = I('yhours');
        $orderInfo = I('post.');
        $user      = session('user');

        if(!$goods_id){
            header("location:".U('Goods/goodsList'));
            exit;
        }

        $goodsInfo = M('Goods')->alias('g')
                               ->field('g.goods_id,g.cid,g.goods_name,g.description,g.goods_describe,g.factorytime,g.use_hours,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,cc1.name as pinpai,cc2.name as cart_type,cc2.chexing,cc3.name as dunwei,cc4.name as menjia,cc5.name as mj_height')
                               ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                               ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                               ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                               ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                               ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                               ->where(['goods_id'=>$goods_id])
                               ->find();

        if(!$user['level_id']){
            $user['level_id'] = 1;
        }

        $rent = rent($goodsInfo['cid'],(int)$tenancy,(int)$yhours,(int)session('bydc'),(int)session('is_yt'));

        if($user['level_id'] == 2){
            $orderInfo['rent'] = $rent['car_rent'];
        }else{
            $orderInfo['rent'] = $rent['user_rent'];
        }

        $orderInfo['level_id'] = $user['level_id'];

        $this->assign('orderInfo',$orderInfo);
        $this->assign('goodsInfo',$goodsInfo);
        $this->display();
    }

    //年月租订单提交
    public function Order()
    {
        $goods_id = I('post.goods_id');
        $data     = I('post.');
        $user     = $this->user;
        $data['user_id'] = $user['user_id'];
// dump($user);
        if($this->user['level_id'] > 2 ){
            exit(json_encode(['status'=>-1,'msg'=>'代理商、股东无法下单！']));
        }
        
        if(!$data['use_user']){
            exit(json_encode(['status'=>-1,'msg'=>'收车人不能为空！']));
        }        

        if(!$data['address']){
            exit(json_encode(['status'=>-1,'msg'=>'收车地址不能为空！']));
        } 
        
        if(!$data['mobile']){
            exit(json_encode(['status'=>-1,'msg'=>'电话不能为空！']));
        } 

        if(!$data['province']){
            exit(json_encode(['status'=>-1,'msg'=>'请选择省份！']));
        }
        $data['province'] = delDiffer($data['province']);    

        if(!$data['city']){
            exit(json_encode(['status'=>-1,'msg'=>'请选择城市！']));
        }        
        $data['city'] = delDiffer($data['city']);  

        if(!$data['city']){
            $data['city'] = $data['province'];
            unset($data['province']);
        }  

        $data['address'] = $data['province'].$data['city'].$data['address'];

        if(!$goods_id){
            exit(json_encode(['status'=>-1,'msg'=>'没有该叉车！']));
        }
        
        if($data['number'] < 1){
            exit(json_encode(['status'=>-1,'msg'=>'租车数量至少1']));
        }

        if($data['tenancy'] < 1){
            exit(json_encode(['status'=>-1,'msg'=>'租期至少为1']));
        }        

        if($data['yhours'] < 1){
            exit(json_encode(['status'=>-1,'msg'=>'年使用小时至少1']));
        }

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

        $rent = rentCount($user['level_id'],$goods_id,$data['tenancy'],$data['yhours']);

        $rent = rent($Goods['cid'],(int)$data['tenancy'],(int)$data['yhours'],(int)$data['bydc'],(int)$data['is_yt']);

        if($this->user['level_id'] == 2){
            $data['mprice'] = $rent['car_rent'] * $data['number'];
        }else{
            $data['mprice'] = $rent['user_rent'] * $data['number'];
        }

        if(!$rent){
            exit(json_encode(['status'=>-1,'msg'=>'租金计算出错！']));
        }

        $data['pinpai']    = $Goods['pinpai'];
        $data['cart_type'] = $Goods['cart_type'];
        $data['dunwei']    = $Goods['dunwei'];
        $data['menjia']    = $Goods['menjia'];
        $data['chexing']   = $Goods['chexing'];
        $data['mj_height'] = $Goods['mj_height'];
        $data['add_time']  = date('Y-m-d H:i:s',time());

        do{
            $data['order_sn'] = 'hyw'.date('YmdHis').rand(1000,9999);
        }while(M('Order')->where(['order_sn'=>$data['order_sn']])->find());
        
        $Order = M('Order')->add($data);

        if($Order){
            echo json_encode(['status'=>1,'msg'=>'订单提交成功','order_id'=>$Order,'order_sn'=>$data['order_sn']]);
            $user_list = $this->getUserJpush($user['user_id']);
            $push['registration_id'] = $user_list;
            if($user_list){
                $content = '您有新的年月租订单可抢！';
                $type = ['type'=>12,'order_id'=>$Order];
                push($push,$content,$type);
            }
            exit;
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'订单提交失败']));
        }
    }

    //获取开启推送功能的用户
    public function getUserJpush($user_id='')
    {
        $where['jpush_status'] = 1;//推送状态开启
        $where['level_id']     = 2;//车主身份
        $where['login_status'] = 1;//登录状态
        $where['user_id']      = ['neq',$user_id];

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
        
        if($this->user['level_id'] > 2 ){
            exit(json_encode(['status'=>-1,'msg'=>'代理商、股东无法下单！']));
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

        if ($res){
            $user_list = $this->getPushUser($res,$user_id);

            if($user_list){
                $push['registration_id'] = $user_list;
                $content = '您有新的临时租订单可抢！';
                $type = ['type'=>13,'temp_id'=>$res];
                push($push,$content,$type);
            }            
            exit(json_encode(array('status'=>1,'msg'=>'临时租提交订成功','temp_id'=>$res)));
        }
        exit(json_encode(array('status'=>-1,'msg'=>'临时租提交订失败')));
    }     

    //获取可以推送的车主
    public function getPushUser($temp_id='',$user_id='')
    {
        $Temp = M('Temporary')->where(['temp_id'=>$temp_id,'user_id'=>$user_id])->find();

        if($Temp['status'] > 1){
            return false;
        }

        $TempInfo = M('TempInfo')->where(['temp_id'=>$temp_id])->select();

        $userId_array = array();

        if($TempInfo){
            foreach ($TempInfo as $key => $val) {
                $userId_array[] = $val['user_id'];
            }
        }

        $userId_array[] = $user_id;

        $userId_str = implode(',',$userId_array);

        $x = 20000;
        $lng_l = $Temp['lng'] - $x / 11 * 0.0001;
        $lng_r = $Temp['lng'] + $x / 11 * 0.0001;
        $lat_b = $Temp['lat'] - $x / 10 * 0.0001;
        $lat_t = $Temp['lat'] + $x / 10 * 0.0001;

        $where['jpush_status'] = 1;//推送状态开启
        $where['level_id']     = 2;//车主身份
        $where['login_status'] = 1;//登录状态
        $where['user_id'] = ['not in',$userId_str];
        $where['lat'] = ['BETWEEN',"{$lat_b},{$lat_t}"];//纬度范围
        $where['lng'] = ['BETWEEN',"{$lng_l},{$lng_r}"];//经度范围
        $identifier   = M('Users')->field('identifier')->where($where)->select();

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

    //临时租订单提交成功的跳转页面
    public function TempYes()
    {
        $temp_id = I('temp_id');
        if(!$temp_id){
            header("location:".U('Home/Index/index'));exit;
        }
        $Temp = M('Temporary')->find($temp_id);
        $over = $this->orderOverTemp($temp_id);
        // dump($over);
        $overs['h'] = floor($over['time_s'] / 60);
        $overs['time'] = $over['time_s'] % 60;
        // dump($overs);
        $Ad = M('Ad')->where(['ad_id'=>86])->find();
        $this->assign('ad',$Ad);
        $this->assign('Temp',$Temp);
        $this->assign('overs',$overs);
        if($Temp['status']==3){
            $driver = M('Users')->field('mobile,nickname')->find($Temp['driver_id']);
            $this->assign('driver',$driver);
            $this->display('dTemp');
            exit;
        }      
        if($overs['h'] >= 10){
            $this->display('oldTemp');
            exit;
        }
        $this->display();
    }

    //临时租订单-重发订单
    public function againTemp()
    {
        $temp_id = I('post.temp_id');

        $data['status']    = 1;
        $data['temp_id']   = $temp_id;
        $data['driver_id'] = NULL;
        $data['push_time'] = NULL;
        $data['add_time']  = date('Y-m-d H:i:s',time());

        $Temp = M('Temporary')->find($temp_id);

        if(!$Temp){
            exit(json_encode(['status'=>-1,'msg'=>'该订单不存在！']));
        }

        if($Temp['user_id']!=$this->user['user_id']){
            exit(json_encode(['status'=>-1,'msg'=>'该订单不存在！']));
        }

        $res = M('Temporary')->save($data);

        if($Temp['status'] == 1){
            exit(json_encode(['status'=>1,'msg'=>'重发成功！']));
        }

        if($res){
            if($Temp['driver_id']){
                // $TfData['status'] = 4;
                // $TfWhere['user_id'] = $Temp['driver_id'];
                // $TfWhere['temp_id'] = $temp_id;
                $content1 = $Temp['temp_sn'].'订单已被客户取消，接单失败！';
                $type    = ['type'=>10,'temp_id'=>$Temp['temp_id']];
                Jpush($Temp['driver_id'],$type,$content,$content1);
                // M('TempInfo')->where($TfWhere)->save($TfData);
            }
            exit(json_encode(['status'=>1,'msg'=>'重发成功！']));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'重发失败！']));
        }
    }

    /**
     * 临时租--取消订单
     *token、temp_id
     * 测试通过
    */
    public function cancelOrder()
    {
        $user_id = $this->user['user_id'];
        $data = I('post.');
        $data['is_cancel'] = 1;
        $data['status']    = 2;

        $Temp = M('Temporary')->find($data['temp_id']);

        if($Temp['user_id'] != $user_id){
            exit(json_encode(array('status'=>-1,'msg'=>'没有该订单！')));
        }

        if($Temp['status']==2){
            exit(json_encode(array('status'=>1,'msg'=>'临时租取消订单成功')));
        }

        $res = M('Temporary')->where(array('temp_id'=>$data['temp_id']))->save($data);
        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'临时租取消订单成功')));
        exit(json_encode(array('status'=>-1,'msg'=>'取消订单失败')));
    }

    //查看临时租订单是否已有人抢单
    public function tempStatus()
    {
        $temp_id = I('temp_id');
        $Temp = M('Temporary')->find($temp_id);
        if($Temp['status']==3){
            $Users = M('Users')->field('mobile,nickname')->find($Temp['driver_id']);
            exit(json_encode(['status'=>1,'driver'=>$Users]));
        }else{
            exit(json_encode(['status'=>-1,'Temp'=>$Temp]));
        }
    }

    //临时租过期时间
    private function orderOverTemp($temp_id='')
    {
        if(!$temp_id){
            return false;
        }

        $Temporary = M('Temporary')->field('temp_sn,status,add_time')->find($temp_id);

        $basic  = tpCache('basic');

        $time_s = time() - strtotime($Temporary['add_time']);

        $Temp_s = $basic['hot_keywords1']*60*60;

        return ['time_s'=>$time_s,'temp_s'=>$Temp_s,'data'=>$Temporary];
        exit;
        if($time_s > $Temp_s){
            return ['status'=>2,'msg'=>'订单已失效','time_s'=>''];
        }else{
        }
    }

    //年月租订单提交成功的跳转页面
    public function OrderYes()
    {
        $order_id = I('order_id');
        if(!$order_id){
            header("location:".U('Home/Index/index'));exit;
        }
        $Order = M('Order')->find($order_id);
        // $Temp = M('Temporary')->find($temp_id);
        $over = $this->orderOver($order_id);
        $overs['h'] = floor($over['time_s'] / 60);
        $overs['time'] = $over['time_s'] % 60;
// dump($overs);
        $Ad1 = M('Ad')->where(['ad_id'=>84])->find();
        $Ad2 = M('Ad')->where(['ad_id'=>85])->find();
        $Ad = M('Ad')->where(['ad_id'=>86])->find();
        $this->assign('ad',$Ad);
        $this->assign('ad1',$Ad1);
        $this->assign('ad2',$Ad2);
        $this->assign('Order',$Order);
        $this->assign('overs',$overs);
        $this->display();
    }

    //年月租过期时间
    private function orderOver($order_id = '')
    {
        if(!$order_id){
            return false;
        }

        $Order = M('Order')->field('order_sn,order_status,add_time')->where(['order_id'=>$order_id])->find();

        $basic  = tpCache('basic');

        $time_s = time() - strtotime($Order['add_time']);

        $order_s = $basic['hot_keywords']*60*60;

        return ['time_s'=>$time_s,'order_s'=>$order_s,'data'=>$Order];exit;
        // if($time_s > $order_s){
        //     exit(json_encode(['status'=>1,'msg'=>'订单已失效','time_s'=>$time_s,'order_s'=>$order_s,'data'=>$Order]));
        // }else{
        //     exit(json_encode(['status'=>1,'msg'=>'成功','time_s'=>$time_s,'order_s'=>$order_s,'data'=>$Order]));
        // }
    }    

    //查看年月租订单是否已有人抢单
    public function orderStatus()
    {
        $order_id = I('order_id');
        $Order = M('Order')->find($order_id);
        if($Order['order_status']==4){
            exit(json_encode(['status'=>1]));
        }else{
            exit(json_encode(['status'=>-1]));
        }
    }    

    //年月租-订单匹配成功
    public function matchCar()
    {
        $user_id  = $this->user['user_id'];
        $order_id = I('get.order_id');

        $Order = M('Order')->alias('o')
                           ->field('o.yajin,o.order_id,o.order_status,o.order_sn,o.add_time,g.pinpai,g.cart_type,g.dunwei,g.menjia,g.mj_height,g.factorytime,g.use_hours,g.is_status,g.bydc,g.shuju,g.is_yt,o.tenancy,o.yhours,o.mprice,o.number,o.use_user,o.mobile,o.address,o.invoice_title,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,start_time,end_time,pairing_status,o.shuju')
                           ->join('tp_goods as g on o.goods_id=g.goods_id')
                           ->where(['o.user_id'=>$user_id,'o.order_id'=>$order_id])
                           ->find();
        if(!$Order||$Order['order_status'] !=4 ){
            echo '<script>history.go(-1)</script>';
            exit;
        }

        $this->assign('is_yt',C('is_yt'));
        $this->assign('user_order_tips',C('user_order_tips'));
        $this->assign('is_status',['一般','良好','优秀']);
        $this->assign('OrderInfo',$Order);
        $this->assign('orderStatus',$Order['order_status']);
        $this->display();
    }
}