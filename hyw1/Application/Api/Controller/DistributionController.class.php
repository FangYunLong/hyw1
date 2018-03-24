<?php
/**
 * Author:Lonelytears
*: 2016-12-15
 */
namespace Api\Controller;


class DistributionController extends BaseController {

    /**
     * 我的分销-分销列表
     * post
     */
    public function index()
    {
        $token      = I('post.token');
        $stact_time = I('post.stact_time')?strtotime(I('post.stact_time').'/01'):strtotime('2015/01/01');
        $end_time   = I('post.end_time')?strtotime(I('post.end_time').'/31'):time();
        $page       = I('post.page')?I('post.page'):1;
        $rows       = I('post.rows')?I('post.rows'):8;
        $num        = ($page-1) * $rows;
        $user_id    = S($token);

// echo strtotime('2017/1/1');exit;
        if(empty($user_id)){
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        $Users = M('Users')->find($user_id);

        $sql = "SELECT count(r.user_id) AS count,sum(r.money) AS money_count 
                FROM tp_rebate_log AS r 
                LEFT JOIN tp_users AS u 
                ON u.user_id = r.buy_user_id 
                WHERE r.user_id = $user_id AND 
                create_time BETWEEN $stact_time AND $end_time"; 

        $res_count = M('')->query($sql)[0];
        if($res_count['count']<1){
          exit(json_encode(['status'=>2,'msg'=>'没有数据','money_count' => $Users['actual_money']]));
        }
        $pages = ceil($res_count['count'] / $rows);

        $sql = "SELECT u.mobile,r.buy_user_id,r.order_sn,r.create_time,r.goods_price,r.money 
                FROM tp_rebate_log AS r 
                LEFT JOIN tp_users AS u 
                ON u.user_id = r.buy_user_id 
                WHERE r.user_id = $user_id 
                AND create_time BETWEEN $stact_time AND $end_time
                LIMIT $num,$rows";

        $res = M('')->query($sql);

        foreach($res as $k => $v){
            $res[$k]['mobile'] = substr($v['mobile'],0,3).'***'.substr($v['mobile'],-4,4);
        }

        if(!empty($res)){
            exit(
              json_encode([
              'status'      =>    1,
              'msg'         =>    '获取数据成功',
              'pages'       =>    $pages,
              'page'        =>    $page,
              'rows'        =>    $rows,
              'money_count' =>    $Users['actual_money'],
              'list'        =>    $res
            ]));
        }else{
            exit(
              json_encode([
              'status'      =>    -1,
              'msg'         =>    '获取数据失败'
            ]));
        }
    }

    /**
     * 我的分销-添加银行卡
     * post
     */
    public function addBank()
    {
      $token             = I('post.token');
      $user_id           = S($token);
      $data              = I('post.');
      $data['user_id']   = $user_id;
      $data['add_time']  = time();
      if(empty($user_id)){
        exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
      }

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

    /**
     * 我的分销-提现账户-获取用户银行卡
     * post
     */
    public function getBank()
    {
      $token            = I('post.token');
      $user_id          = S($token);

      if(empty($user_id)){
          exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
      }

      $BankAccount      = M('BankAccount')
                            ->field('id,bankname,bank_type,bank_account,cardholder')
                            ->where(['user_id'=>$user_id])
                            ->order('is_del ASC')
                            ->select();
      if(!$BankAccount){
          exit(json_encode(array('status'=>2,'msg'=>'没有数据！','result'=>[])));
      }

      $bank = array_flip(C('bank'));

      foreach($BankAccount as $k => $v){
          $BankAccount[$k]['bank_account'] = substr($v['bank_account'],-4,4);
          $BankAccount[$k]['banklogo']     = APP_URL . '/Public/bank/'.$bank[$v['bankname']].'.png';
      }

      exit(json_encode(array('status'=>1,'msg'=>'获取数据成功','result'=>$BankAccount)));
    }   

    /**
     * 我的分销-提现账户-删除用户银行卡
     * post
     */
    public function delBank()
    {
      $token            = I('post.token');
      $user_id          = S($token);
      $id               = I('post.id');

      if(empty($user_id)){
        exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
      }
      
      $data['id'] = $id;
      $data['user_id'] = $user_id;
      $res = M('BankAccount')->where($data)->delete();

      if($res){
        exit(json_encode(array('status'=>1,'msg'=>'删除成功')));
      }else{
        exit(json_encode(array('status'=>-1,'msg'=>'删除失败')));
      }      
    }   

    /**
     * 根据银行卡号获取银行卡名称和类型
     * post
     */
    public function getBankInfo()
    {
      $token            = I('post.token');
      $user_id          = S($token);
      $BankId           = I('post.bankid');
      
      if(empty($user_id)){
        exit(json_encode(['status'=>-3,'msg'=>'缺少用户token']));
      }
      if(empty($BankId)){
        exit(json_encode(['status'=>-2,'msg'=>'银行卡号不能为空']));
      }

      $BankInfo         = bankInfo($BankId);
      $data             = explode('-',$BankInfo);
      $res['bank_name'] = $data[0];
      $res['bank_type'] = $data[2];
      $res['bank_logo'] = APP_URL . '/Public/bank/'.$res['bank_name'].'.png';

      if($BankInfo){
        json_App([
            'status' => 1,
            'msg'    => '获取数据成功',
            'result' => $res
        ]);
      }else{
        json_App(['status'=>-1,'msg'=>'获取数据失败']);
      }
    }       

    //提现
    public function extractMoney()
    {
        $token   = I('post.token');
        $user_id = S($token);
        $data    = I('post.');

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        if(!$data['id']){
            json_App(['status'=>-1,'msg'=>'请选择银行卡！']);
        }

        if(!$data['name']){
            json_App(['status'=>-1,'msg'=>'请填写姓名！']);
        }

        if(!is_numeric($data['amount'])){
            json_App(['status'=>-1,'msg'=>'提现金额异常！']);
        }

        $Users = M('Users')->find($user_id);

        if(!$Users){
            json_App(['status'=>-1,'msg'=>'数据异常！']);
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
}