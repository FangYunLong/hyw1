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

    //年月租订单提交
    public function Order()
    {
        $goods_id = I('post.goods_id');
        $data     = I('post.');
        $user     = session('user');
        $data['user_id'] = $user['user_id'];
        
        if(!$data['use_user']){
            exit(json_encode(['status'=>-1,'msg'=>'收车人不能为空！']));
        }        

        if(!$data['address']){
            exit(json_encode(['status'=>-1,'msg'=>'收车地址不能为空！']));
        } 
        
        if(!$data['mobile']){
            exit(json_encode(['status'=>-1,'msg'=>'电话不能为空！']));
        } 

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

        $Goods = M('Goods')->find($goods_id);

        if($Goods['is_on_sale'] != 1){
            exit(json_encode(['status'=>-1,'msg'=>'该叉车已下架！']));
        }

        if($Goods['is_special'] == 1){
            exit(json_encode(['status'=>-1,'msg'=>'该叉车已下架！']));
        }

        $rent = rentCount($user['level_id'],$goods_id,$data['tenancy'],$data['yhours']);

        if(!$rent){
            exit(json_encode(['status'=>-1,'msg'=>'租金计算出错！']));
        }

        $data['mprice'] = $rent * $data['number'];

        $data['add_time'] = date('Y-m-d H:i:s',time());

        do{
            $data['order_sn'] = 'hyw'.date('YmdHis').rand(1000,9999);
        }while(M('Order')->where(['order_sn'=>$data['order_sn']])->find());
        
        $Order = M('Order')->add($data);

        if($Order){
            $this->display();
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'订单提交失败']));
        }
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
}