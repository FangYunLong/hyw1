<?php
/**
 *Author:Lonelytears
*: 2016-12-27
 */
namespace Api\Controller;
// require_once(APP_PATH."/Api/Common/PHPpay/shanpayconfig.php");
// require_once(APP_PATH."/Api/Common/PHPpay/lib/shanpayfunction.php");



class PayController extends BaseController {    

    public function index()
    {
        $this->display();
    }

    /*************************甬易支付**************************/

    //年月租押金支付接口
    public function yongyiPay()
    {
        $token    = I('post.token');
        $user_id  = S($token);
        $order_id = I('post.order_id');

        // $user_id = 2617;
        // $order_id = 885;

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

        if($Order['order_status']!=4){
            json_App(['status'=>-1,'msg'=>'订单状态有误或已支付！']);
        }

        // $res = yongyipays($user_id,$Order['order_id'],$Order['yajin'],1,'deposit',true);echo $res;exit;
        $res = yongyipays($user_id,$Order['order_id'],$Order['yajin'],1,'deposit');
        // $res = yongyipays();
        
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
        $token    = I('post.token');
        $user_id  = S($token);
        $goods_id = I('post.goods_id');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }   

        if(!$goods_id){
            json_App(['status'=>-1,'msg'=>'参数错误！']);
        }

        $Goods = M('Goods')->find($goods_id);

        if(!$Goods){
            json_App(['status'=>-1,'msg'=>'参数错误！']);
        }

        if($Goods['is_special'] != 1){
            json_App(['status'=>-1,'msg'=>'不是特价车！']);
        }

        if($Goods['is_on_sale'] != 1){
            json_App(['status'=>-1,'msg'=>'抱歉，该车已下架！']);
        }

        if($Goods['user_id'] == $user_id){
            json_App(['status'=>-1,'msg'=>'无法购买自己的特价车！']);
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
            json_App(['status'=>-1,'msg'=>'抱歉，支付失败！']);
        }
        // $res = yongyipays($user_id,$spRes,$Goods['special_price']*0.98,3,'',true);echo $res;exit;
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

        if($Order['end_time'] - time() > 60*60*24*3){
            exit(json_encode(['status'=>-1,'msg'=>'本月已支付，请在到期前3天内支付！']));
        }

        // $res = yongyipays($user_id,$Order['order_id'],$Order['mprice']*0.95,2,'',true);echo $res;exit;
        $res = yongyipays($user_id,$Order['order_id'],$Order['mprice']*0.95,2,'');
        
        if($res){
            exit(json_encode(['status'=>1,'msg'=>'成功','url'=>$res['url'],'data'=>$res['data']]));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'失败']));
        }
    }

    public function notifyURL()
    {
        require_once APP_PATH . 'Common/Conf/payconfig.php';
        header("Content-type:text/html; Charset=utf-8");    
        $data = I('post.');
        $xml = base64_decode($data['tranData']);
        $payInfo = simplexml_load_string($xml);    
        $merSignMsg = HmacMd5($xml,$key);
        //sql断点测试
        $dataTest['content'] = $xml;
        $dataTest['admin_id'] = 1;
        $dataTest['titile'] = date('Y-m-d H:i:s',time());
        M('WxMsg')->add($dataTest);

        $path = './Public/file/'.$payInfo->orderNo.'.lock';
        $fp = fopen($path,'a+');        

        //启用文件锁，防止高并发
        if(flock($fp,LOCK_EX))
        {

        if($merSignMsg!=$data['signData']){
            exit(json_encode(['status'=>-1,'msg'=>'非法访问！']));
        }

        if($payInfo->tranStat!=1){
            exit(json_encode(['status'=>-1,'msg'=>'支付失败！']));
        }

        $Pay = M('Pay')->where(['pay_sn'=>"$payInfo->orderNo"])->find();

        $Users = M('Users')->find($Pay['user_id']);
        // $OrderInfo = M('OrderInfo')->where(['order_id'=>$Order['order_id'],'status'=>4])->select();

        if($Pay['pay_status'] != 1 ){
            echo '请勿重复支付';
            exit;
        }

        if(!$Users){
            echo 'no user';
            exit;
        }

        $payData['pay_status'] = 2;
        $payData['add_time']  = strtotime($payInfo->tranTime);
        $payData['yongyi_sn'] = (string)$payInfo->tranSerialNo; 

        M('Pay')->where(['pay_sn'=>"{$payInfo->orderNo}"])->save($payData);

        switch($Pay['pay_type']){
            //年月租押金支付，修改订单状态
            // case 'hywy':
            case 1:
                $orderData['order_status'] = 5; 
                $orderData['pay_status']   = 1; 
                $orderData['is_playmoney'] = 1; 
                $orderData['order_id']     = $Pay['order_id']; 
                $orderData['playmoney_time'] = strtotime($payInfo->tranTime); 
                $res = M('Order')->save($orderData);
                
                if($res){
                    //修改匹配成功的车主的抢单状态
                    $orderInfoData['status'] = 5;
                    $res2 = M('OrderInfo')->where(['order_id'=>$Pay['order_id'],'status'=>4])->save($orderInfoData);
                    if(!$res2){
                        echo 'no car zhu'; 
                        exit; 
                    }
                }
            break;
            //年月租租金支付
            case 2:
            // $Pay['order_id'] = 20;
            $Order = M('Order')->find($Pay['order_id']);

            if($Order['end_time'] < 1){
                $OrderData['start_time'] = strtotime($payInfo->tranTime);
                $OrderData['end_time']   = strtotime($payInfo->tranTime)+60*60*24*30;  
            }else{
                $OrderData['end_time']   = $Order['end_time']+60*60*24*30;  
            }
            
            $OrderData['playmoney_time'] = strtotime($payInfo->tranTime);
            
            $OrderData['order_id']   = $Pay['order_id'];
            M('Order')->save($OrderData);
            $goods_price = $Order['mprice'];
            $order_sn = $Order['order_sn'];

            // $Order['province'] = 28240;
            // $Order['city'] = 28241;
            if($Order['province'] == ''){
                $city     = M('Region')->where(['parent_id'=>0,'name'=>['like',"%{$Order['city']}%"]])->getField('id');
                $province = $city;
            }else{
                $province = M('Region')->where(['parent_id'=>0,'name'=>['like',"%{$Order['province']}%"]])->getField('id');
                $city     = M('Region')->where(['parent_id'=>$province,'name'=>['like',"%{$Order['city']}%"]])->getField('id');
                $shareholders = M('Users')->where(['level_id'=>['gt',2],'agent_area' => $province])->select()[0];
            }
            
            $agent        = M('Users')->where(['level_id'=>['gt',2],'agent_area' => $city])->select()[0];
            
            //代理商
            if($agent){
                $agent_fee['user_id'] = $agent['user_id'];
                $agent_fee['consumers_id'] = $Users['user_id'];
                $agent_fee['order_id'] = $Order['order_id'];
                $agent_fee['order_sn'] = $Order['order_sn'];
                $agent_fee['add_time'] = strtotime($payInfo->tranTime);
                $agent_fee['province'] = $province;
                $agent_fee['city']     = $city;
                $agent_fee['payments_fee'] = (float)$payInfo->orderAmt;
                $agent_fee['reward'] = $agent_fee['payments_fee']*0.03;
                //增加代理商 市场管理费
                $af_res = M('AgentFee')->add($agent_fee);

                if($af_res){
                    $sql = "UPDATE tp_users SET actual_money = actual_money + {$agent_fee['reward']} WHERE user_id = {$agent_fee['user_id']}";
                    M('')->query($sql);
                }
            }

            //股东
            if($shareholders){
                $agent_fee['user_id'] = $shareholders['user_id'];
                $agent_fee['consumers_id'] = $Users['user_id'];
                $agent_fee['order_id'] = $Order['order_id'];
                $agent_fee['order_sn'] = $Order['order_sn'];
                $agent_fee['add_time'] = strtotime($payInfo->tranTime);
                $agent_fee['province'] = $province;
                $agent_fee['city']     = $city;
                $agent_fee['payments_fee'] = (float)$payInfo->orderAmt;
                $agent_fee['reward'] = 0;
                //增加代理商 市场管理费
                $af_res = M('AgentFee')->add($agent_fee);
            }

            break;            
            //特价车支付，修改订单状态
            case 3:
            $Special = M('Special')->find($Pay['order_id']);
            // dump($Special);exit;
            $goods_price = $Special['price'];
            $goodsData['goods_id'] = $Special['goods_id'];
            $goodsData['is_on_sale'] = 0;
            $spData['sp_id'] = $Pay['order_id'];
            $spData['pay_status'] = 1;
            $spData['sp_status'] = 1;
            $order_sn = $Special['sp_sn'];
            $spData['pay_add_time'] = strtotime($payInfo->tranTime);
            M('Goods')->save($goodsData);
            M('Special')->save($spData);
            break;
        }

        if(!$Users['mobile2']){
            echo 'no user.mobile2';
            exit;
        } 

        $Users2 = M('Users')->where(['mobile'=>$Users['mobile2']])->find();
        if(!$Users2){
            echo 'no user2';
            exit;
        }

        if($Pay['pay_type']!=1){
            echo 'yes';
            //计入分销列表
            $rebateLogData['user_id']        = $Users2['user_id'];
            $rebateLogData['buy_user_id']    = $Users['user_id'];
            $rebateLogData['namenick']       = $Users['namenick'];
            $rebateLogData['order_sn']       = $order_sn;
            $rebateLogData['goods_price']    = (float)$payInfo->orderAmt;
            $rebateLogData['money']          = sprintf('%.2f',$payInfo->orderAmt*0.02);
            $rebateLogData['create_time']    = time();
            M('RebateLog')->add($rebateLogData);
            $User2_actual_money['actual_money'] = $Users2['actual_money'] + $rebateLogData['money'];
            M('Users')->where(['user_id'=>$Users2['user_id']])->save($User2_actual_money);
        }else{
            echo 'no';
        }        
            flock($fp,LOCK_UN);
        }
        fclose($fp); 
    } 


    //线下支付银行卡web页面
    public function offlinePay()
    {
        $bank = tpCache('smtp');
        $this->assign('bank',$bank);        
        $this->display();
    }

    public function githubTest()
    {
        dump(I('post.'));
    }
}