<?php
/**
 *
//
 * 2015-11-21
 */
namespace Home2\Controller;
use Home2\Logic\UsersLogic;
use Think\Page;
use Think\Verify;

class UserController extends BaseController {

    //个人中心
    public function userInfo()
    {   
        $Users_abc = M('Users')->find($this->user['user_id']);
        // dump($Users_abc);
        $this->assign('User',$Users_abc);
        $this->assign('sex',C('sex'));
        $this->display();
    }

    //修改个人信息页面
    public function editInfo()
    {
        $Users = M('Users')->find($this->user['user_id']);
        $this->assign('User',$Users);
        $this->assign('sex',C('sex'));        
        $this->display();
    }  

    //提交修改个人信息
    public function editAction()
    {
        $data = I('post.');
        unset($data['__hash__']);
        // dump($data);
        if($_FILES){
            foreach ($_FILES as $key => $val) {
                if($val['tmp_name']){
                    $upload_file[$key] = $val;
                }
            }
            $path1 = './Public/Upload/userpic/';
            $file = fileUploadNews1($path1,$upload_file);
            $file['touxiang'] ? $data['head_pic']  = 'http://hyw.web66.cn:8092/Public/Upload/userpic/'.$file['touxiang'] : null;
            $file['file'] ? $data['cart_path']  = 'http://hyw.web66.cn:8092/Public/Upload/userpic/'.$file['file'] : null;
        }
        $user = session('user');
        $data['user_id'] = $user['user_id'];
        $res = M('Users')->save($data);
        $Users = M('Users')->find($user['user_id']);
        // dump($data);exit;
        session('user',$Users,7200);

        if($res){
            json_App(['status'=>1,'msg'=>'修改成功！']);
        }else{
            json_App(['status'=>-1,'msg'=>'修改失败！']);
        }
    }  

    //订单中心-租车订单
    public function userOrder()
    {
        $Order = $this->userOrderList('',1,2);
        $Temp  = $this->userTempList('',1,2);
        // $Order = false;
        // $Temp = false;
        // dump($Order);
        $this->assign('order',$Order['data']);
        $this->assign('temp',$Temp['data']);
        $this->display();
    }

    //订单中心-租车订单-已完成与未完成订单
    public function userOrderStatus()
    {
        $OrderYes = $this->userOrderList(5,1,2);
        $OrderNo  = $this->userOrderList(3,1,2);
        // dump($OrderYes);
        // dump($OrderNo);
        $this->assign('OrderYes',$OrderYes['data']);
        $this->assign('OrderNo',$OrderNo['data']);        
        $this->display();
    }

    //订单中心-租车订单-已完成与未完成订单-列表
    public function userOrderStatusList()
    {
        $orderStatus = I('get.order_status',3); 
        $page        = I('get.page',1);
        $OrderList   = $this->userOrderList($orderStatus,$page,6);
        // dump($OrderList);
        $this->assign('OrderList',$OrderList['data']);
        $this->assign('page',$OrderList);
        $this->assign('orderStatus',$orderStatus);
        $this->assign('pageRows',pageRows($OrderList['page'],$OrderList['pages'],6));
        $this->display();
    }



    //订单中心-租车订单-订单详情
    public function userOrderInfo()
    {
        $user_id  = $this->user['user_id'];
        $order_id = I('get.order_id');

        $Order = M('Order')->alias('o')
                           ->field('o.order_id,o.order_status,o.order_sn,o.add_time,g.pinpai,g.cart_type,g.dunwei,g.menjia,g.mj_height,g.factorytime,g.use_hours,g.is_status,g.bydc,g.shuju,g.is_yt,o.tenancy,o.yhours,o.mprice,o.number,o.use_user,o.mobile,o.address,o.invoice_title,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,start_time,end_time')
                           ->join('tp_goods as g on o.goods_id=g.goods_id')
                           ->where(['o.user_id'=>$user_id,'o.order_id'=>$order_id])
                           ->find();
        if(!$Order){
            echo '<script>history.go(-1)</script>';
            exit;
        }
// dump($Order);
        $this->assign('is_yt',C('is_yt'));
        $this->assign('user_order_tips',C('user_order_tips'));
        $this->assign('is_status',['一般','良好','优秀']);
        $this->assign('OrderInfo',$Order);
        $this->assign('orderStatus',$Order['order_status']);
        $this->display();
    }

    //订单中心-年月租-租车订单
    public function userOrderList($order_status='',$page=1,$rows=6)
    {
        $user_id = $this->user['user_id'];
        
        $data['order_status'] = $order_status;
        $data['user_id']      = $user_id;
        $where  = ' 1=1 ';
        $where .= " AND o.user_id = {$user_id} ";

        if(!empty($order_status)){
            if($order_status!=5){
                $where .= ' AND order_status != 5 ';
            }else{
                $where .= ' AND order_status = 5 ';
            }
        }

        $data  = array_filter($data);

        $count = M('Order')->alias('o')->where($where)
                           ->count();
        if($count < 1){
            return false;            
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null ;
        $page > $pages ? $page = $pages : null ;
        $num     = ($page-1)*$rows;

        $sql = "SELECT o.order_id,o.order_sn,o.order_status,o.goods_id,o.add_time,g.pinpai,g.cart_type,g.dunwei,g.zm_pic
                FROM tp_order AS o LEFT JOIN tp_goods AS g ON o.goods_id=g.goods_id
                WHERE {$where} ORDER BY o.add_time DESC LIMIT {$num},{$rows} ";
        $res = M('')->query($sql);        

        if($res){
            return [
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $res
            ];
        }else{
            return false;          
        }
    }    

    //订单中心-临时租-租车订单
    public function userTempStatus()
    {
        $TempYes = $this->userTempList(3,1,2);
        $TempNo  = $this->userTempList(2,1,2);
        $this->assign('TempYes',$TempYes['data']);
        $this->assign('TempNo',$TempNo['data']);
        $this->display();
    }

    //订单中心-临时租-已完成与未完成订单-列表
    public function userTempStatusList()
    {
        $status = I('get.status',2); 
        $page        = I('get.page',1);
        $TempList   = $this->userTempList($status,$page,6);
        // dump($TempList);
        $this->assign('TempList',$TempList['data']);
        $this->assign('page',$TempList);
        $this->assign('status',$status);
        $this->assign('pageRows',pageRows($TempList['page'],$TempList['pages'],6));
        $this->display();
    }    

    //订单中心-临时租-订单详情
    public function userTempInfo()
    {
        $temp_id = I('get.temp_id');
        $Temp = M('Temporary')->find($temp_id);

        if(!$Temp){
            echo '<script>history.go(-1)</script>';
            exit;
        }        

        $this->assign('temp',$Temp);
        $this->assign('user_temp_tips',C('user_temp_tips'));
        $this->display();
    }

    //订单中心-临时租-租车订单
    public function userTempList($status='',$page=1,$rows=6)
    {
        $user_id = $this->user['user_id'];
        $data['status']  = $status;
        $data['user_id'] = $user_id;

        if(!empty($status)&&$status!=3){
            $data['status'] = array('neq',3);
        }

        $data  = array_filter($data);

        $count = M('Temporary')->where($data)
                               ->count();
        if($count < 1){
            return false;            
        }

        $page < 1 ? $page = 1 : null ;
        $pages     = ceil($count / $rows);
        $page > $pages ? $page = $pages : null ;
        $num     = ($page-1)*$rows;

        $Temporary = M('Temporary')->field('temp_id,user_id,temp_sn,status,mobile,username,dunwei,driver_id,address,add_time,push_time')
                                   ->where($data)
                                   ->limit($num,$rows)
                                   ->order('add_time DESC')
                                   ->select();
        
        foreach($Temporary as $k => $v){
            if($v['driver_id']){
                $Users = M('Users')->where(['user_id'=>$v['driver_id']])->find();
                $Temporary[$k]['nickname']  = $Users['nickname'];
                $Temporary[$k]['carmobile'] = $Users['mobile'];
            }else{
                $Temporary[$k]['nickname']  = '';
                $Temporary[$k]['carmobile'] = '';
            }
        }

        if($Temporary){
            return [
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $Temporary
            ];
        }else{
            return false;            
        }
    }    

    //订单中心-年月租-抢单订单
    public function carOrderList($status='',$page=1,$rows = 6)
    {
        $user_id = $this->user['user_id'];

        $level_id = M('Users')->field('level_id')->find($user_id)['level_id'];

        if($level_id!=2){
            return false;
        }

        $data['status']  = $status;
        $data['user_id'] = $user_id;

        if(!empty($status)&&$status!=5){
            $data['status'] = array('neq',5);
        }

        $data  = array_filter($data);

        $count = M('OrderInfo')->where($data)
                               ->count();
        if($count < 1){
            return false;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null ;
        $page > $pages ? $page = $pages : null ;
        $num     = ($page-1)*$rows;

        $order_id = M('OrderInfo')->field('order_id,status,add_time')
                                  ->where($data)
                                  ->limit($num,$rows)
                                  ->order('add_time DESC')
                                  ->select();      
        
        foreach($order_id as $k => $v){
            $order_ids[$k] = $v['order_id'];
        }

        $order_id_str = implode(',',$order_ids);
        $orderData['order_id'] = ['in',$order_id_str];

        $sql    = "SELECT o.order_id,o.order_sn,o.order_status,o.goods_id,g.pinpai,g.cart_type,g.dunwei,g.zm_pic
                   FROM tp_order AS o 
                   LEFT JOIN tp_goods AS g ON o.goods_id=g.goods_id
                   WHERE o.order_id IN({$order_id_str})";
        $Orders = M('')->query($sql);

        foreach ($Orders as $k => $v) {
            $Order[$k] = array_merge($Orders[$k],$order_id[$k]);
        }

        if($Order){
            return [
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $Order
            ];
        }else{
            return false;            
        }                         

    }

    //订单中心-抢单订单
    public function carOrder()
    {
        $level_id = $this->user['level_id'];

        if($level_id!=2){
            echo '<script>history.go(-1)</script>';
            exit;            
        }

        $Order = $this->carOrderList('',1,2);
        $Temp  = $this->carTempList('',1,2);
        $this->assign('order',$Order['data']);
        $this->assign('temp',$Temp['data']);
        $this->display();
    }    

    //订单中心-抢单订单-年月租订单
    public function carOrderStatus()
    {
        $OrderYes = $this->carOrderList(5,1,2);
        $OrderNo  = $this->carOrderList(3,1,2);
        // dump($OrderYes);
        // dump($OrderNo);
        $this->assign('OrderYes',$OrderYes['data']);
        $this->assign('OrderNo',$OrderNo['data']);        
        $this->display();
    }

    //订单中心-抢单订单-已完成与未完成订单-列表
    public function carOrderStatusList()
    {
        $orderStatus = I('get.order_status',3); 
        $page        = I('get.page',1);
        $OrderList   = $this->carOrderList($orderStatus,$page,6);
        $this->assign('OrderList',$OrderList['data']);
        $this->assign('page',$OrderList);
        $this->assign('orderStatus',$orderStatus);
        $this->assign('pageRows',pageRows($OrderList['page'],$OrderList['pages'],6));
        $this->display();
    }

    //订单中心-抢单订单-订单详情
    public function carOrderInfo()
    {
        $user_id  = $this->user['user_id'];
        $order_id = I('get.order_id');

        $OrderInfo = M('OrderInfo')->alias('oi')
                                   ->field('o.order_id,oi.status,o.order_sn,oi.add_time,g.pinpai,g.cart_type,g.dunwei,g.menjia,g.mj_height,g.bydc,g.shuju,g.is_yt,o.tenancy,o.yhours,o.mprice,g.zm_pic,g.cm_pic,g.czt_pic,g.nb_pic,oi.cart_age,oi.use_hours,oi.dcsj,oi.paly_num,o.start_time,o.end_time')
                                   ->join('tp_order as o on oi.order_id=o.order_id')
                                   ->join('tp_goods as g on o.goods_id=g.goods_id')
                                   ->where(['oi.user_id'=>$user_id,'oi.order_id'=>$order_id])
                                   ->find();

        if(!$OrderInfo){
            echo '<script>history.go(-1)</script>';
            exit;
        }

        $this->assign('is_yt',C('is_yt'));
        $this->assign('car_order_tips',C('car_order_tips'));
        $this->assign('orderStatus',$OrderInfo['status']);
        $this->assign('OrderInfo',$OrderInfo);
        $this->display();
    }    

    //订单中心-临时租-抢单订单
    public function carTempList($status='',$page=1,$rows = 6)
    {
        $user_id = $this->user['user_id'];

        $level_id = M('Users')->field('level_id')->find($user_id)['level_id'];

        if($level_id!=2){
            return false;
        }

        $data['status']  = $status;
        $data['user_id'] = $user_id;

        if(!empty($status)&&$status!=3){
            $data['status'] = array('neq',3);
        }

        $data  = array_filter($data);

        $count = M('TempInfo')->where($data)
                              ->count();
        if($count < 1){
            return false;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null ;
        $page > $pages ? $page = $pages : null ;
        $num     = ($page-1)*$rows;
        
        $temp_id = M('TempInfo')->field('temp_id,status')
                                ->where($data)
                                ->limit($num,$rows)
                                ->order('add_time DESC')
                                ->select();
        foreach($temp_id as $k => $v){
            $temp_ids[$k] = $v['temp_id'];
        }
        $temp_id_str = implode(',',$temp_ids);
        $tempData['temp_id'] = ['in',$temp_id_str];

        $Temporarys = M('Temporary')->field('temp_id,mobile,username,dunwei,address,push_time')
                                    ->where($tempData)
                                    ->order('push_time DESC')
                                    ->select();
        foreach($Temporarys as $k => $v){
            $Temporary[$k] = array_merge($temp_id[$k],$Temporarys[$k]);
        }            

        if($Temporary){
            return [
                'page'  => $page,
                'pages' => $pages,
                'rows'  => $rows,
                'data'  => $Temporary
            ];
        }else{
            return false;            
        }                         

    }

    //订单中心-临时租-租车订单
    public function carTempStatus()
    {
        $TempYes = $this->carTempList(3,1,2);
        $TempNo  = $this->carTempList(2,1,2);
        $this->assign('TempYes',$TempYes['data']);
        $this->assign('TempNo',$TempNo['data']);
        $this->display();
    }

    //订单中心-临时租-已完成与未完成订单-列表
    public function carTempStatusList()
    {
        $status = I('get.status',2); 
        $page   = I('get.page',1);
        $TempList = $this->carTempList($status,$page,6);
        // dump($TempList);
        $this->assign('TempList',$TempList['data']);
        $this->assign('page',$TempList);
        $this->assign('status',$status);
        $this->assign('pageRows',pageRows($TempList['page'],$TempList['pages'],6));
        $this->display();
    }    

    //订单中心-临时租-订单详情
    public function carTempInfo()
    {
        $temp_id = I('get.temp_id');
        $Temp = M('TempInfo')->alias('ti')
                             ->field('ti.temp_id,ti.status,tp.add_time,tp.push_time,tp.dunwei,tp.username,tp.mobile,tp.address')
                             ->join('tp_temporary as tp on ti.temp_id = tp.temp_id')
                             ->where(['ti.temp_id'=>$temp_id,'ti.user_id'=>$this->user['user_id']])
                             ->find();

        if(!$Temp){
            echo '<script>history.go(-1)</script>';
            exit;
        }        
// dump($Temp);
        $this->assign('temp',$Temp);
        $this->assign('status',$Temp['status']);
        $this->assign('car_temp_tips',C('car_temp_tips'));
        $this->display();
    }

    /**
     * 我的分销-分销列表
     * post
     */
    public function distributionList($page=1,$rows=8,$stact_time='',$end_time='')
    {
        $stact_time = $stact_time ? strtotime($stact_time) : strtotime('2017/01/01');
        $end_time   = $end_time ? strtotime($end_time) : time();
        $user_id    = $this->user['user_id'];

        $Users = M('Users')->find($user_id);

        $sql = "SELECT count(r.user_id) AS count,sum(r.money) AS money_count 
                FROM tp_rebate_log AS r 
                LEFT JOIN tp_users AS u 
                ON u.user_id = r.buy_user_id 
                WHERE r.user_id = $user_id AND 
                create_time BETWEEN $stact_time AND $end_time"; 

        $res_count = M('')->query($sql)[0];
        if($res_count['count']<1){
            return [
              'pages'       =>    0,
              'page'        =>    0,
              'money_count' =>    $Users['actual_money']
            ];
        }

        $pages = ceil($res_count['count'] / $rows);
        $page < 1 ? $page = 1 : null ;
        $page > $pages ? $page = $pages : null ;
        $num   = ($page-1)*$rows;        


        $sql = "SELECT u.mobile,r.buy_user_id,r.order_sn,r.create_time,r.goods_price,r.money 
                FROM tp_rebate_log AS r 
                LEFT JOIN tp_users AS u 
                ON u.user_id = r.buy_user_id 
                WHERE r.user_id = $user_id AND
                create_time BETWEEN $stact_time AND $end_time
                LIMIT $num,$rows";

        $res = M('')->query($sql);

        foreach($res as $k => $v){
            $res[$k]['mobile'] = substr($v['mobile'],0,3).'***'.substr($v['mobile'],-4,4);
        }

        if(!empty($res)){
            return [
              'pages'       =>    $pages,
              'page'        =>    $page,
              'rows'        =>    $rows,
              'money_count' =>    $Users['actual_money'],
              'list'        =>    $res
            ];
        }else{
            return false;
        }
    }

    //订单中心-我的分销
    public function distribution()
    {
        $page = I('page');
        $stact_time = I('stact_time','2017-1-1');
        $end_time   = I('end_time',date('Y-n-j',time())).' 23:59:59';
        $data = $this->distributionList($page,8,$stact_time,$end_time);
        $where = ['stact_time'=>date('Y-n-j',strtotime($stact_time)),'end_time'=>date('Y-n-j',strtotime($end_time))];
        $this->assign('list',$data['list']);
        $this->assign('page',$data);
        $this->assign('time',['stact_time'=>$stact_time,'end_time'=>$end_time]);
        $this->assign('pageRows',pageRows($data['page'],$data['pages'],6));
        $this->assign('where',$where);
        $this->display();
    }

    /**
     * 我的分销-提现账户-获取用户银行卡
     * post
     */
    public function getBank()
    {
        $user_id = $this->user['user_id'];
        $BankAccount = M('BankAccount')->field('id,bankname,bank_type,bank_account,cardholder')
                                       ->where(['user_id'=>$user_id])
                                       ->order('is_del ASC')
                                       ->select();
        if(!$BankAccount){
          return false;
        }

        $bank = array_flip(C('bank'));

        foreach($BankAccount as $k => $v){
            $BankAccount[$k]['bank_account'] = substr($v['bank_account'],-4,4);
            $BankAccount[$k]['banklogo']     = APP_URL . '/Public/bank/'.$bank[$v['bankname']].'.png';
        }

        return $BankAccount;
    }

    //调取添加银行卡的页面视图
    public function addBankView()
    {
        $this->display();
    }

    /**
     * 我的分销-添加银行卡
     * post
     */
    public function addBankAction()
    {
      $user_id           = $this->user['user_id'];
      $data              = I('post.');
      $data['user_id']   = $user_id;
      $data['add_time']  = time();

      $BankInfo          = explode('-',bankInfo($data['bank_account']));
      $data['bank_type'] = $BankInfo[2];
      $data['bankname']  = $BankInfo[0];
      $bank = C('bank');
      if(!in_array($data['bankname'],$bank)){
        json_App(['status'=>-1,'msg'=>'暂不支持该银行！']);
      }
      // dump($data);exit;
      $res = M('BankAccount')->add($data);

      if($res){
        exit(json_encode(array('status'=>1,'msg'=>'添加成功','id'=>$res)));
      }else{
        exit(json_encode(array('status'=>-1,'msg'=>'添加失败！')));
      }
    }    

    //订单中心-提取奖励
    public function reward()
    {
        $user_id = $this->user['user_id'];
        $Users = M('Users')->find($user_id);
        $BankAccount = $this->getBank();
        $this->assign('bank',$BankAccount);
        $this->assign('bank1',$BankAccount[0]);
        $this->assign('actual_money',$Users['actual_money']);
        $this->display();
    }

    //订单中心-提取奖励-确认信息
    public function rewardConfirm()
    {
        $id = I('id');
        $amount = I('amount');
        $BankAccount = M('BankAccount')->find($id);
        dump($BankAccount);
        $this->assign('bank',$BankAccount);
        $this->assign('amount',$amount);
        $this->display();
    }

    //提现
    public function extractMoney()
    {
        $user_id = $this->user['user_id'];
        $data    = I('post.');

        if(!$data['id']){
            json_App(['status'=>-1,'msg'=>'请选择银行卡！']);
        }

        if(!is_numeric($data['amount'])){
            json_App(['status'=>-1,'msg'=>'提现金额异常！']);
        }

        $Users = M('Users')->find($user_id);

        if(!$Users){
            json_App(['status'=>-1,'msg'=>'用户信息异常！']);
        }

        if($data['amount'] <= 2){
            json_App(['status'=>-1,'msg'=>'提现余额必须大于2元！']);
        }
        
        if($data['amount'] > $Users['actual_money']){
            json_App(['status'=>-1,'msg'=>'提现余额不足！']);
        }
        
        $BankAccount = M('BankAccount')->where(['id'=>$data['id'],'user_id'=>$user_id])->find();
        if(!$BankAccount){
            json_App(['status'=>-1,'msg'=>'数据异常！']);
        }        

        $data['user_id'] = $user_id;
        $data['bank_account'] = $BankAccount['bank_account'];
        $data['name'] = $BankAccount['cardholder'];
        $data['add_time'] = time();
        unset($data['id']);

    try{
        M('')->startTrans(); 
        $res = M('ExtractMoney')->add($data);
        if($res){
            $userData['actual_money'] = $Users['actual_money'] - $data['amount'];
            $userData['user_id'] = $user_id; 
            $res2 = M('Users')->save($userData);
            if(!$res2){
                M('')->rollback();
                json_App(['status'=>-1,'msg'=>'提交失败']);
            }
        }else{
            json_App(['status'=>-1,'msg'=>'提交失败']);
        }        
        M('')->commit(); 
        json_App(['status'=>1,'msg'=>'提交成功']);
      }catch(\Exception $e){
        M('')->rollback();
        json_App(['status'=>-1,'msg'=>'提交失败']);
      }
    }

    /**
     * 我的消息--全部消息
     * token 
     * post方式 
     * 返回值：is_read（0未读1已读） 
     */
    public function allMsgList($no_read='') 
    {
        if($no_read){ 
            $data['is_read'] = 0; 
        }

        $user_id = $this->user['user_id']; 
        $UsersMsg = M('Users')->find($user_id);
        $data['user_id'] = $user_id;
        $data['type'] = 1; 
        $data['is_del'] = 1;
        $msg = M('Msg')->where($data)->select();//获取普通消息
        $Users = M('Users')->find($user_id);
        $radioMsg = M('Msg')->where(['type'=>2,'public_time'=>['GT',$UsersMsg['reg_time']]])->select();//获取所有的广播消息

        foreach ($radioMsg as $k =>$v) {
            
            if(!(($v['level_id'] == 3) || ($v['level_id'] == $UsersMsg['level_id']))){
                unset($radioMsg[$k]);
            }

            if (in_array($user_id,explode(',',$v['is_del']))) {
                unset($radioMsg[$k]);
            }
        }

        foreach ($radioMsg as $k => $v) {
            if (in_array($user_id,explode(',',$v['is_read']))) {
                $radioMsg[$k]['is_read']= 1;
            }else{
                $radioMsg[$k]['is_read']= 0;
            }
        }
        
        $no_read_list = array();

        if($no_read){
            foreach ($radioMsg as $k => $v) {
                if($v['is_read'] == 0){
                    $no_read_list[$k] = $v;
                }
            }    

            $result = array_merge($no_read_list,$msg);//未读普通消息与未读广播消息总数
            $count = count($result);
            return ['status'=>1,'msg'=>'未读消息','result'=>$count];
        }

        $result = array_merge($radioMsg,$msg);

        if(!$result){
            return false;
        }

        $where1 = $where2 = $where3 = array();
        foreach($result as $k => $v){
            $where1[$k] = $v['is_read'];
            $where2[$k] = $v['type'];
            $where3[$k] = $v['public_time'];
        }
        
        //排序
        array_multisort($where1,SORT_ASC,$where2,SORT_DESC,$where3,SORT_DESC,$result); 
        return ['status'=>1,'msg'=>'我的消息','result'=>$result];
    }

    /**
     * 我的消息--删除消息
     * token、msg_id
     *返回状态码
    */
    public function delMsg()
    {
        $user_id = $this->user['user_id'];
        $msg_id = I('post.msg_id');
        if (empty($msg_id)) {
            exit(json_encode(array('status'=>1,'msg'=>'消息已经被删除')));
        }        

        $Msg = M('Msg')->find($msg_id);

        if($Msg['type'] == 2){
            if (in_array($user_id,explode(',',$Msg['is_del']))) {
                exit(json_encode(array('status'=>1,'msg'=>'消息已经被删除')));
            }else {
                if(empty($Msg['is_del'])){
                    $MsgData['is_del'] .= $user_id;
                }else{
                    $MsgData['is_del'] .= ','.$user_id;
                }
            }
            $res = M('Msg')->where(array('msg_id'=>$msg_id))->save($MsgData);
        }else{
            //查看是否已读
            if(!$Msg){
                json_App(['status'=>1,'msg'=>'消息已经被删除']);
            }            
            $res = M('Msg')->delete($msg_id);
        }
        // dump($res);exit;
        //修改为已删除状态
        if ($res) {
            exit(json_encode(array('status'=>1,'msg'=>'删除成功')));
        }else {
            exit(json_encode(array('status'=>-1,'msg'=>'删除失败')));
        }


    }


    //全部消息
    public function allMsg()
    {
        $data = $this->allMsgList();
        $page = I('get.page',1);
        $rows = 8;

        $count = count($data['result']);

        if($count < 1){

        }else{
            $pages = ceil($count / $rows);
            $page < 1 ? $page = 1 : null;
            $page > $pages ? $page = $pages : null;
            $num = ($page-1)*$rows;
        }

        $list = array_slice($data['result'],$num,$rows);
        $this->assign('list',$list);
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();
    }

    /**
     * 我的消息--读取消息
     * token、msg_id（消息id）
     * post
     * 返回值：是否已读0未读1已读、消息页面
    */
    public function reMsg()
    {
        $user_id = $this->user['user_id'];
        $msg_id = I('get.msg_id');

        //查看是否已读
        $data = M('Msg')->where(array('msg_id'=>$msg_id))->find();
        // $content = htmlspecialchars_decode($data['content']);

        if($data['type'] == 2){
            if (in_array($user_id,explode(',',$data['is_read']))) {
                $is_read = 1;
            }else {
                if(empty($data['is_read'])){
                    $Msg['is_read'] .= $user_id;
                }else{
                    $Msg['is_read'] .= ','.$user_id;
                }
            }
        }else{
            $Msg['is_read'] = 1;
        }
        //修改为已读
        $res = M('Msg')->where(array('msg_id'=>$msg_id))->save($Msg);
        //载入模板
        $this->assign('data',$data);
        $this->display();

    }  

    //我的簡歷
    public function resume()
    {
        $user_id = $this->user['user_id'];
        $status = I('get.fabu');
        $Resume = M('Resume')->where(['user_id'=>$user_id])->find();

        $this->assign('Resume',$Resume);
        if(!$Resume||$status==1){
            $this->display('fabu');
        }else{
            $this->display();
        }
    }  

    //去除省市名称与APP上的差异
    public function delDiffer($id='')
    {
        $string = M('region')->find($id)['name'];

        if($string == '市辖区' || $string == '县' || $string == '市辖县'){
            return false;
        }

        $string = str_replace('省','',$string);
        $string = str_replace('市','',$string);
        $string = str_replace('自治','',$string);
        $string = str_replace('维吾尔','',$string);
        $string = str_replace('壮族','',$string);
        $string = str_replace('回族','',$string);
        $string = str_replace('特别行政区','',$string);

        return $string;
    }

    /**
     * 招司机--发布简历
     *token
     * user_name,sex,age,jingyan,xueli,mobile,address(详细地址),thumb(叉车证)，province(省)，city(市区)
    */
    public function publishResume()
    {
        $user_id = $this->user['user_id'];
        $data = I('post.');
        $data['user_id'] = $user_id;

        if(!$data['user_name']){
            json_App(['status'=>-1,'msg'=>'请填写姓名！']);
        }

        if(!$data['age']){
            json_App(['status'=>-1,'msg'=>'请填写年龄！']);
        }

        if(!$data['jingyan']){
            json_App(['status'=>-1,'msg'=>'请填写经验！']);
        }

        if(!$data['xueli']){
            json_App(['status'=>-1,'msg'=>'请填写学历！']);
        }

        if(!$data['mobile']){
            json_App(['status'=>-1,'msg'=>'请填写手机号！']);
        }

        if(!$data['province']){
            json_App(['status'=>-1,'msg'=>'请填写地址！']);
        }

        if(!$data['city']){
            json_App(['status'=>-1,'msg'=>'请填写地址！']);
        }

        $data['province'] = $this->delDiffer($data['province']);

        $data['city'] = $this->delDiffer($data['city']);

        if(!$data['city']){
            $data['city'] = $data['province'];
        }

        $data['address'] = $data['province'] . $data['city'];

        //上传证件
        if ($_FILES['thumb']['size'] > 0) {
            //定义上传路径
            $path="./Public/Upload/cartpart/";
            $uploadinfo=fileUploadNews($path,$_FILES);
            // $uploadinfo=$this->fileUpload($path);
            $data['thumb']='http://hyw.web66.cn:8092/Public/Upload/cartpart/'. $uploadinfo['thumb'];
        }else{
            json_App(['status'=>-1,'msg'=>'请上传叉车证！']);
        }

        //查看是否存在简历信息
        $info = M('Resume')->where(array('user_id'=>$user_id))->find();
        
        $data['add_time'] = time();
        if (!empty($info)){
            $res = M('Resume')->where(array('user_id'=>$user_id))->save($data);
        }else{
            $res = M('Resume')->add($data);
        }

        if (!$res){
            exit(json_encode(array('status'=>-1,'msg'=>'发布简历失败')));
        }
        exit(json_encode(array('status'=>1,'msg'=>'发布简历成功')));
    }

    /**
     * 我的求职--隐藏/显示简历
     * token
     * is_hidden
     *
    */
    public function hidResume()
    {   
        $user_id = $this->user['user_id'];

        //查看是否存在简历信息
        $info = M('Resume')->where(array('user_id'=>$user_id))->find();
        

        if ($info) {
            $is_hidden = $info['is_hidden'] == 1 ? 0 : 1;
            $res = M('Resume')->where(['user_id'=>$user_id])->save(['is_hidden'=>$is_hidden]);
            if ($res) {
                if ($is_hidden==1){
                    exit(json_encode(array('status'=>1,'msg'=>'公开简历')));
                }else{
                    exit(json_encode(array('status'=>1,'msg'=>'隐藏简历')));
                }
            } else {
                exit(json_encode(array('status'=>-1,'msg'=>'操作失败')));
            }
        } else {
            exit(json_encode(array('status'=>-1,'msg'=>'操作失败')));
        }
    }

    //我的特价车列表
    public function specialList()
    {
        $user_id = $this->user['user_id'];

        $goods_count = M('Goods')->where(array('user_id'=>$user_id,'is_special'=>1,'_logic'=>'AND'))->count();

        if($goods_count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->display();            
            exit;       
        }

        $page = I('page',1);
        $rows = I('rows',9);
        $pages = ceil($goods_count / $rows);
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;
        $num = ($page - 1) * $rows;

        $Goods = M('Goods')->field('goods_id,pinpai,dunwei,zm_pic,special_price')
                           ->where(array('user_id'=>$user_id,'is_special'=>1,'_logic'=>'AND'))
                           ->order($num,$rows)
                           ->order('goods_id DESC')
                           ->select();      
        
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->assign('goodsList',$Goods);
        $this->display();                   
    }

    //特价车详情
    public function specialInfo()
    {
        $goods_id = I('goods_id');
        $Goods = M('Goods')->field('goods_id,is_on_sale,is_status,pinpai,dunwei,cart_type,menjia,mj_height,shuju,is_yt,cart_age,use_hours,dcsj,description,special_price,address,factorytime,buy_year,username,mobile,zm_pic,cm_pic,czt_pic,nb_pic')
                           ->find($goods_id);   
        $this->assign('goodsInfo',$Goods);     
        $this->assign('is_yt',C('is_yt'));
        $this->assign('is_status',C('is_status'));
        $this->display();
    }

    //显示/隐藏特价车
    public function hiddenOrDisplayCar()
    {
        $goods_id = I('goods_id');
        $user_id = $this->user['user_id'];

        if(!$goods_id){
            json_App(['status'=>-1,'msg'=>'参数错误']);
        }

        $Goods = M('Goods')->find($goods_id);

        if($Goods['is_special']!=1){
            json_App(['status'=>-1,'msg'=>'不是特价车']);
        }

        $data['goods_id'] = $goods_id;
        if($Goods['is_on_sale']==1){
            $data['is_on_sale'] = 0;
            $msg = '隐藏';
            $msg1 = '显示';
            $msg2 = '车已隐藏：您可以';
        }else{
            $data['is_on_sale'] = 1;
            $msg = '显示';
            $msg1 = '隐藏';
            $msg2 = '若车已卖：您可以';
        }

        $res = M('Goods')->where(['user_id'=>$user_id])->save($data);

        if($res){
            json_App(['status'=>1,'msg1'=>$msg1.'特价车','msg2'=>$msg2,'is_on_sale'=>$data['is_on_sale']]);
        }else{
            json_App(['status'=>-1,'msg'=>$msg.'特价车失败']);
        }        
    }
    
    //车主-出租情况
    public function carInfo()
    {
        $page = I('get.page',1); 
        $rows = I('get.rows',8); 
        $start_time = I('start_time')? strtotime(I('start_time')): strtotime('2016-01-01');       
        $end_time   = I('end_time')  ? strtotime(I('end_time').' 23:59:59') : time();        
        $overdue = I('overdue');

        $time = time();  
        $end_times = $time + 60*60*24*7;

        $OrderInfoWhere['oi.status']  = 5;
        $OrderInfoWhere['oi.user_id'] = $this->user['user_id'];                              

        //出租数量
        $car_count = M('OrderInfo')->alias('oi')
                                   ->join('tp_order as o on oi.order_id = o.order_id')
                                   ->where($OrderInfoWhere)
                                   ->getField('sum(paly_num)');
        
        //过期数量                                   
        $overdue_count = M('OrderInfo')->alias('oi')
                                       ->join('tp_order as o on oi.order_id = o.order_id')
                                       ->where($OrderInfoWhere)
                                       ->where(['end_time'=>['LT',time()]])
                                       ->getField('sum(paly_num)');
                                            
        //即将过期数量                                   
        $will_be_overdue_count = M('OrderInfo')->alias('oi')
                                               ->join('tp_order as o on oi.order_id = o.order_id')
                                               ->where($OrderInfoWhere)
                                               // ->fetchSql()
                                               ->where(['end_time'=>['BETWEEN',"{$time},{$end_times}"]])
                                               ->getField('sum(paly_num)');
                                               // echo $will_be_overdue_count;
        $money_where = $OrderInfoWhere;
        $money_where['p.pay_type']   = 2;                                       
        $money_where['p.pay_status'] = 2;   
        $money_count = M('OrderInfo')->alias('oi')->join('tp_pay as p on oi.order_id = p.order_id')->where($money_where)->getField('sum(money)');

        I('old_time1')?$OrderInfoWhere['end_time'] = ['LT',$time]:null;
        I('old_time2')?$OrderInfoWhere['end_time'] = ['BETWEEN',"$time,$end_times"]:null;

        $count     = M('OrderInfo')->field('o.order_sn,o.start_time,o.end_time,oi.paly_num,oi.zdlzj')
                                   ->alias('oi')
                                   ->join('tp_order as o on oi.order_id = o.order_id')
                                   ->where($OrderInfoWhere)
                                   // ->fetchSql()
                                   ->where(['o.start_time'=>['BETWEEN',"$start_time,$end_time"]])
                                   ->count();
                                   // echo $count;exit;
        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->assign('where',['start_time'=>date('Y-n-j',$start_time),'end_time'=>date('Y-n-j',$end_time),'old_time1'=>I('old_time1'),'old_time2'=>I('old_time2')]);
            $this->assign('count',['car_count'=>$car_count,'overdue_count'=>$overdue_count,'will_be_overdue_count'=>$will_be_overdue_count,'money_count'=>$money_count]);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;
        $num = ($page - 1) * $rows;

        $OrderInfo = M('OrderInfo')->field('o.order_sn,o.start_time,o.end_time,oi.paly_num,oi.zdlzj')
                                   ->alias('oi')
                                   ->join('tp_order as o on oi.order_id = o.order_id')
                                   ->where($OrderInfoWhere)
                                   ->where(['o.start_time'=>['BETWEEN',"$start_time,$end_time"]])
                                   ->order('oi.add_time DESC')
                                   ->limit($num,$rows)
                                   ->select();

        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);        
        $this->assign('OrderInfo',$OrderInfo);
        $this->assign('where',['start_time'=>date('Y-n-j',$start_time),'end_time'=>date('Y-n-j',$end_time),'old_time1'=>I('old_time1'),'old_time2'=>I('old_time2')]);
        $this->assign('count',['car_count'=>$car_count,'overdue_count'=>$overdue_count,'will_be_overdue_count'=>$will_be_overdue_count,'money_count'=>$money_count]);
        $this->display();
    }

    //客户租车情况
    public function userCarInfo()
    {
        $user_id = $this->user['user_id'];
        $page = I('get.page',1); 
        $rows = I('get.rows',8); 

        $start_time = I('start_time')? strtotime(I('start_time')): strtotime('2017-01-01');       
        $end_time   = I('end_time')  ? strtotime(I('end_time'). ' 23:59:59') : time();    
        $where['user_id'] = $user_id;
        $where['order_status'] = 5;

        //统计租车总数 以及 租金总金额
        $order_count   = M('Order')->field('sum(number) as number_count,sum(mprice) as mprice_count')->where($where)->find();
        
        //统计已经到期的租车数量
        $overdue_count = M('Order')->where($where)->where(['end_time'=>['LT',time()]])->getField('sum(number)');
        
        //统计即将到期的租车数量
        $time = time();  
        $end_times = $time + 60*60*24*7; 
        $will_be_overdue_count = M('Order')->where($where)
                                           ->where(['end_time'=>['BETWEEN',"{$time},{$end_times}"]])
                                           ->getField('sum(number)');
        //统计在线支付金额
        $money_where['o.user_id']      = $user_id; 
        $money_where['o.order_status'] = 5; 
        $money_where['p.pay_type']     = 2;                                       
        $money_where['p.pay_status']   = 2;

        $money_count = M('Order')->alias('o')
                                 ->join('tp_pay as p on o.order_id = p.order_id')
                                 ->where($money_where)
                                 ->getField('sum(money)');
        //已到期的租车查询
        I('old_time') ? $where['end_time'] = ['LT',time()]:null;
        //即将到期的租车查询
        I('old_time2') ? $where['end_time'] = ['BETWEEN',"$time,$end_times"]:null;
        $count = M('Order')->where($where)->where(['playmoney_time'=>['BETWEEN',"$start_time,$end_time"]])->count();

        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);        
            $this->assign('count',['car_count'=>$order_count['number_count'],'overdue_count'=>$overdue_count,'will_be_overdue_count'=>$will_be_overdue_count,'money_count'=>$money_count,'mprice_count'=>$order_count['mprice_count']]);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;
        $num = ($page - 1) * $rows;

        $Order = M('Order')->where($where)->where(['start_time'=>['BETWEEN',"$start_time,$end_time"]])->order('add_time DESC')->limit($num,$rows)->select();

        $this->assign('OrderInfo',$Order);
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);        
        $this->assign('where',['start_time'=>date('Y-n-j',$start_time),'end_time'=>date('Y-n-j',$end_time),'old_time'=>$old_time,'old_time2'=>$old_time2]);
        $this->assign('count',['car_count'=>$order_count['number_count'],'overdue_count'=>$overdue_count,'will_be_overdue_count'=>$will_be_overdue_count,'money_count'=>$money_count,'mprice_count'=>$order_count['mprice_count']]);
        $this->display();
    }

    public function vue_data()
    {
        $array = [['name'=>111],['name'=>222],['name'=>333]];
        json_App($array);
    }

    //股东中心
    public function shareHolders()
    {
    	if($this->user['level_id'] != 4){
    		header('location:'.U('Home/User/userInfo'));
    		exit;
    	}
		// $this->user['agent_area'] = 28240;

        $user_id    = $this->user['user_id'];
        // $user_id    = 2652;
        $start_time = I('start_time') ? strtotime(I('start_time')) : strtotime('2017-1-1');
        $end_time   = I('end_time') ? strtotime(I('end_time').' 23:59:59') : time();
        $page       = I('page',1);
        $rows       = I('rows',8);

        $province = M('Region')->find($this->user['agent_area']);
        $city     = M('Region')->where(['parent_id'=>$this->user['agent_area']])->select();
        $city_id  = I('city_id',$city[0]['id']);
        $shareHolders['address'] = $province['name'];
        $shareHolders['city_id'] = $city_id;
		$this->assign('citys',$city);
        $this->assign('where',['start_time'=>date('Y-m-d',$start_time),'end_time'=>date('Y-m-d',$end_time)]);

        $where['af.add_time'] = array('between',"{$start_time},{$end_time}");
        $where['af.user_id']  = $user_id;
        $where['af.city'] = $city_id;

        //全省成交总额
        $shareHolders['payments_fee'] = M('AgentFee')->alias('af')
	                                   ->join('tp_users as u1 on af.consumers_id = u1.user_id')
	                                   ->join('tp_order as o on af.order_id = o.order_id')
	                                   ->join('tp_goods as g on o.goods_id = g.goods_id')
	                                   ->where(['af.user_id'=>$user_id])
	                                   ->sum('af.payments_fee');
		//全省成交数量
	    $sql = "SELECT o.number
				FROM tp_agent_fee af
				INNER JOIN tp_users AS u1 ON af.consumers_id = u1.user_id
				INNER JOIN tp_order AS o ON af.order_id = o.order_id
				INNER JOIN tp_goods AS g ON o.goods_id = g.goods_id
				WHERE af.user_id = {$user_id} 
				GROUP BY af.order_id";

		$car_number_count = M('')->query($sql);
		foreach ($car_number_count as $key => $val) {
			$shareHolders['car_number_count'] += $val['number'];
		}

	    //该市成交总金额                               
        $shareHolders['payments_fee_city']   = M('AgentFee')->alias('af')
	                                   ->join('tp_users as u1 on af.consumers_id = u1.user_id')
	                                   ->join('tp_order as o on af.order_id = o.order_id')
	                                   ->join('tp_goods as g on o.goods_id = g.goods_id')
	                                   ->where(['af.user_id'=>$user_id,'af.city'=>$city_id])
	                                   ->sum('af.payments_fee');
	    //该市成交数量
	    $sql = "SELECT o.number
				FROM tp_agent_fee af
				INNER JOIN tp_users AS u1 ON af.consumers_id = u1.user_id
				INNER JOIN tp_order AS o ON af.order_id = o.order_id
				INNER JOIN tp_goods AS g ON o.goods_id = g.goods_id
				WHERE af.user_id = {$user_id} 
				AND af.city = {$city_id}
				GROUP BY af.order_id";

		$car_number = M('')->query($sql);
		foreach ($car_number as $key => $val) {
			$shareHolders['car_number'] += $val['number'];
		}

        $count   = M('AgentFee')->alias('af')
                                ->join('tp_users as u1 on af.consumers_id = u1.user_id')
                                ->join('tp_order as o on af.order_id = o.order_id')
                                ->join('tp_goods as g on o.goods_id = g.goods_id')
                                ->where($where)
                                ->count();
        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);        
			$this->assign('shareHolders',$shareHolders);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;
        $num = ($page - 1) * $rows;

        $AgentFee = M('AgentFee')->alias('af')
                                 ->field('af.add_time,af.order_sn,g.pinpai,g.dunwei,g.cart_type,g.menjia,g.mj_height,g.is_yt,g.shuju,o.address,u1.nickname,u1.mobile,o.number,o.mprice,o.tenancy,af.reward')
                                 ->join('tp_users as u1 on af.consumers_id = u1.user_id')
                                 ->join('tp_order as o on af.order_id = o.order_id')
                                 ->join('tp_goods as g on o.goods_id = g.goods_id')
                                 ->where($where)
                                 ->order('add_time DESC')
                                 ->page($page,$rows)
                                 ->select();

		$this->assign('is_yt',C('is_yt'));
		$this->assign('AgentFee',$AgentFee);
		$this->assign('shareHolders',$shareHolders);
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();    
    }

    //加盟商中心
    public function JoiningTrader()
    {
    	if($this->user['level_id'] != 3){
    		header('location:'.U('Home/User/userInfo'));
    		exit;
    	}

        $user_id    = $this->user['user_id'];
        // $user_id    = 2651;
        $start_time = I('start_time') ? strtotime(I('start_time')) : strtotime('2017-1-1');
        $end_time   = I('end_time') ? strtotime(I('end_time').' 23:59:59') : time();
        $page       = I('page',1);
        $rows       = I('rows',8);

        $where['af.add_time'] = ['BETWEEN',[$start_time,$end_time]];
        $where['af.user_id']  = $user_id;

		// $this->user['agent_area'] = 28241;
        $this->assign('where',['start_time'=>date('Y-m-d',$start_time),'end_time'=>date('Y-m-d',$end_time)]);

		$JoiningTrader = S('JoiningTrader'.$user_id);

		if(!$JoiningTrader){
	        $city     = M('Region')->find($this->user['agent_area']);
	        $province = M('Region')->find($city['parent_id']);
	        $JoiningTrader['address'] = $province['name'] . $city['name'];

	        $JoiningTrader['payments_fee']   = M('AgentFee')->alias('af')
		                                   ->join('tp_users as u1 on af.consumers_id = u1.user_id')
		                                   ->join('tp_order as o on af.order_id = o.order_id')
		                                   ->join('tp_goods as g on o.goods_id = g.goods_id')
		                                   ->where(['af.user_id'=>$user_id])
		                                   ->sum('af.payments_fee');

	        $JoiningTrader['reward']   		 = M('AgentFee')->alias('af')
		                                   ->join('tp_users as u1 on af.consumers_id = u1.user_id')
		                                   ->join('tp_order as o on af.order_id = o.order_id')
		                                   ->join('tp_goods as g on o.goods_id = g.goods_id')
		                                   ->where(['af.user_id'=>$user_id])
		                                   ->sum('af.reward');
		    $sql = "SELECT o.number
					FROM tp_agent_fee af
					INNER JOIN tp_users AS u1 ON af.consumers_id = u1.user_id
					INNER JOIN tp_order AS o ON af.order_id = o.order_id
					INNER JOIN tp_goods AS g ON o.goods_id = g.goods_id
					WHERE af.user_id = {$user_id}
					GROUP BY af.order_id";

			$car_number = M('')->query($sql);
			foreach ($car_number as $key => $val) {
				$JoiningTrader['car_number'] += $val['number'];
			}
			S('JoiningTrader'.$user_id,$JoiningTrader,3600);
		}


        $count   = M('AgentFee')->alias('af')
                                ->join('tp_users as u1 on af.consumers_id = u1.user_id')
                                ->join('tp_order as o on af.order_id = o.order_id')
                                ->join('tp_goods as g on o.goods_id = g.goods_id')
                                ->where($where)
                                ->count();
        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);        
			$this->assign('JoiningTrader',$JoiningTrader);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;
        $num = ($page - 1) * $rows;

        $AgentFee = M('AgentFee')->alias('af')
                                 ->field('af.add_time,af.order_sn,g.pinpai,g.dunwei,g.cart_type,g.menjia,g.mj_height,g.is_yt,g.shuju,o.address,u1.nickname,u1.mobile,o.number,o.mprice,o.tenancy,af.reward')
                                 ->join('tp_users as u1 on af.consumers_id = u1.user_id')
                                 ->join('tp_order as o on af.order_id = o.order_id')
                                 ->join('tp_goods as g on o.goods_id = g.goods_id')
                                 ->where($where)
                                 ->order('add_time DESC')
                                 ->page($page,$rows)
                                 ->select();

		$this->assign('is_yt',C('is_yt'));
		$this->assign('AgentFee',$AgentFee);
		$this->assign('JoiningTrader',$JoiningTrader);
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();
    }
}