<?php
/**
 *Author:Lonelytears
*: 2016-12-27
 */
namespace Home\Controller;


class PayController extends BaseController {    

    public function index()
    {
        $this->display();
    }

    /*************************甬易支付**************************/

    //年月租押金支付接口
    public function yongyiPay()
    {
        $order_id = I('order_id');
        $user_id = $this->user['user_id'];

        if(!$user_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'请先登录！']);
        }

        if(!$order_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'参数错误！']);
        }

        $Order = M('Order')->find($order_id);

        if(!$Order){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'参数错误！']);
        }

        if($Order['order_status']!=4){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'订单状态有误或已支付！']);
        }

        // $res = yongyipays($user_id,$Order['order_id'],$Order['yajin'],1,'deposit',true);echo $res;exit;
        $res = yongyipays($user_id,$Order['order_id'],$Order['yajin'],1,'deposit');
        // $res = yongyipays();
        // dump($res);
        
        if($res){
            exit(json_encode(['status'=>1,'msg'=>'成功','url'=>$res['url'],'data'=>$res['data']]));
            // exit(json_encode(['status'=>1,'msg'=>'成功','url'=>$res]));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'失败']));
        }
    }

    //特价车支付
    public function specialPay()
    {
        $goods_id = I('goods_id'); 
        $user_id = $this->user['user_id'];

        if(!$user_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'请先登录！']);
        }

        if(!$goods_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'参数错误，付款失败！']);
        }

        $Goods = M('Goods')->find($goods_id);

        if(!$Goods){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'参数错误，付款失败！']);
        }

        if($Goods['is_special'] != 1){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'不是特价车，付款失败！']);
        }

        if($Goods['is_on_sale'] != 1){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'抱歉，该车已下架！']);
        }

        if($Goods['user_id'] == $user_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'无法购买自己的特价车！']);
        }

        $spData['buy_user_id']  = $user_id;
        $spData['user_id']      = $Goods['user_id'];
        $spData['goods_id']     = $goods_id;
        $spData['price']        = $Goods['special_price'];
        $spData['pay_price']    = $Goods['special_price']*0.98;
        $spData['add_time']     = time();
        $spData['sp_sn']        = 'hywt'.date('YmdHis').mt_rand(1000,9999);
        $spRes = M('Special')->add($spData);

        if(!$spRes){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'抱歉，支付失败！']);
        }
        $res = yongyipays($user_id,$spRes,$Goods['special_price']*0.98,3,'',true);echo $res;exit;
        $res = yongyipays($user_id,$spRes,$Goods['special_price']*0.98,3,'');

        if($res){
            exit(json_encode(['status'=>1,'msg'=>'成功','url'=>$res['url'],'data'=>$res['data']]));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'失败']));
        }
    }

    //租金支付
    public function rentPay()
    {
        $order_id = I('order_id');
        $user_id = $this->user['user_id'];

        if(!$user_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'请先登录！']);
        }

        if(!$order_id){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'参数错误！']);
        }

        $Order = M('Order')->find($order_id);

        if(!$Order){
            $this->titleErrorHYW(['status'=>-1,'msg'=>'参数错误！']);
        }

        // $res = yongyipays($user_id,$Order['order_id'],$Order['yajin'],1,'deposit',true);echo $res;exit;
        $res = yongyipays($user_id,$Order['order_id'],$Order['mprice']*0.95,2,'deposit');
        
        if($res){
            exit(json_encode(['status'=>1,'msg'=>'成功','url'=>$res['url'],'data'=>$res['data']]));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'失败']));
        }
    }

    public function returnURL()
    {
        $this->titleErrorHYW(['status'=>1,'msg'=>'支付成功！']);
    }    

    public function returnURL_bat()
    {
// echo  date('Y-m-d H:i:s','1500282074');
        // exit;
        // require_once APP_PATH . 'Common/Conf/payconfig.php';
        // header("Content-type:text/html; Charset=utf-8");    
        // $data = I('post.');
        // $xml = base64_decode($data['tranData']);
        // $payInfo = simplexml_load_string($xml);    
        // $merSignMsg = HmacMd5($xml,$key);  


        // // $dataTest['content'] = $xml;
        // // $dataTest['admin_id'] = 1;
        // // $dataTest['titile'] = date('Y-m-d H:i:s',time());
        // // M('WxMsg')->add($dataTest);
        // exit;

        // if($merSignMsg!=$data['signData']){
        //     exit(json_encode(['status'=>-1,'msg'=>'非法访问！']));
        // }

        // if($payInfo->tranStat!=1){
        //     exit(json_encode(['status'=>-1,'msg'=>'支付失败！']));
        // }
        // $path = './Public/file/'.$payInfo->orderNo.'.lock';

        // $fp = fopen($path,'a+');        

        // //启用文件锁，防止高并发
        // if(flock($fp,LOCK_EX))
        // {

        // $Pay = M('Pay')->where(['pay_sn'=>"$payInfo->orderNo"])->find();


        // $Users = M('Users')->find($Pay['user_id']);
        // // $OrderInfo = M('OrderInfo')->where(['order_id'=>$Order['order_id'],'status'=>4])->select();
        // dump($Pay);exit;
        // if($Pay['pay_status'] != 1){
        //     echo 'no order';
        //     exit;
        // }

        // if(!$Users){
        //     echo 'no user';
        //     exit;
        // }

        // $payData['pay_status'] = 2;
        // $payData['add_time']  = strtotime($payInfo->tranTime);
        // $payData['yongyi_sn'] = (string)$payInfo->tranSerialNo; 
        // // dump($payData);
        // // dump($data);
        // // dump($Pay);
        // // dump($Users);
        // // exit;        
        // M('Pay')->where(['pay_sn'=>"{$payInfo->orderNo}"])->save($payData);

        // // $type = substr($payInfo->orderNo,0,3);
        // // $type = substr($payInfo->orderNo,0,4);
        // switch($Pay['pay_type']){
        //     //年月租押金支付，修改订单状态
        //     // case 'hywy':
        //     case 1:
        //         $orderData['order_status'] = 5; 
        //         $orderData['pay_status']   = 1; 
        //         $orderData['is_playmoney'] = 1; 
        //         $orderData['order_id']     = $Pay['order_id']; 
        //         $orderData['playmoney_time'] = strtotime($payInfo->tranTime); 
        //         $res = M('Order')->save($orderData);
                
        //         if($res){
        //             //修改匹配成功的车主的抢单状态
        //             $orderInfoData['status'] = 5;
        //             $res2 = M('OrderInfo')->where(['order_id'=>$Pay['order_id'],'status'=>4])->save($orderInfoData);
        //             if(!$res2){
        //                 echo 'no car zhu'; 
        //                 exit; 
        //             }
        //         }
        //     break;
        //     //年月租租金支付
        //     case 2:
        //     $Order = M('Order')->find($Pay['order_id']);
        //     $OrderData['order_id']   = $Order['order_id'];
        //     $OrderData['stact_time'] = time();
        //     $OrderData['end_time']   = time()+60*60*24*30;  
        //     M('Order')->save($OrderData);
        //     $goods_price = $Order['mprice'];
        //     $order_sn = $Order['order_sn'];
        //     break;            
        //     //特价车支付，修改订单状态
        //     case 3:
        //     $Special = M('Special')->find($Pay['order_id']);
        //     // dump($Special);exit;
        //     $goods_price = $Special['price'];
        //     $goodsData['goods_id'] = $Special['goods_id'];
        //     $goodsData['is_on_sale'] = 0;
        //     $spData['sp_id'] = $Pay['order_id'];
        //     $spData['pay_status'] = 1;
        //     $spData['sp_status'] = 1;
        //     $order_sn = $Special['sp_sn'];
        //     $spData['pay_add_time'] = strtotime($payInfo->tranTime);
        //     M('Goods')->save($goodsData);
        //     M('Special')->save($spData);
        //     break;
        // }

        // if(!$Users['mobile2']){
        //     echo 'no user.mobile2';
        //     exit;
        // } 

        // $Users2 = M('Users')->where(['mobile'=>$Users['mobile2']])->find();
        // if(!$Users2){
        //     echo 'no user2';
        //     exit;
        // }

        // $RebateLog = M('RebateLog')->where(['order_sn'=>$order_sn])->find();

        // if($Pay['pay_type']!=1){
        //     echo 'yes';
        //     //计入分销列表
        //     $rebateLogData['user_id']        = $Users2['user_id'];
        //     $rebateLogData['buy_user_id']    = $Users['user_id'];
        //     $rebateLogData['namenick']       = $Users['namenick'];
        //     $rebateLogData['order_sn']       = $order_sn;
        //     $rebateLogData['goods_price']    = $goods_price;
        //     $rebateLogData['money']          = sprintf('%.2f',$payInfo->orderAmt*0.02);
        //     $rebateLogData['create_time']    = time();
        //     M('RebateLog')->add($rebateLogData);
        // }else{
        //     echo 'no';
        // } 

        // flock($fp,LOCK_UN);

        // }

        // fclose($fp);        
        // echo 'yes';        
    }

    //支付结算页面 线上与线下选择
    public function settlement()
    {
        $order_id = I('order_id');
        if($order_id){
            $Order = M('Order')->find($order_id);
            if($Order['order_status']==5){
                $data = ['order_id'=>$order_id,'msg'=>'年月租租金','money'=>$Order['mprice'],'type'=>3];
            }else{
                $data = ['order_id'=>$order_id,'msg'=>'年月租押金','money'=>$Order['yajin'],'type'=>1];
            }
        }else{
            $goods_id = I('goods_id');
            $Goods = M('Goods')->find($goods_id);
            $data = ['goods_id'=>$goods_id,'msg'=>'特价车购买','money'=>$Goods['special_price'],'type'=>2];
        }
        $this->assign('data',$data);
        $this->display();
    }

    //3411 2619 7709 2183 66
    //6216 2610 0000 0000 018
    public function offline()
    {
        $BankAccount = M('BankAccount')
                          ->field('cardholder,bankname,bank_type,bank_account')
                          ->where(['type'=>2])
                          ->limit(5)
                          ->select();        
        $this->assign('BankAccount',$BankAccount);
        $this->display();
    }

    public function titleErrorHYW($error='')
    {
        $this->assign('error',$error);
        $this->display('error');
        exit;
    }
}