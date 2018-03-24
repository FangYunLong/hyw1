<?php
/**
 * Author:Lonelytears
*: 2016-12-15
 */
namespace Api\Controller;


class TemporaryController extends BaseController {

    /**
     * 临时租提交订单吨位数据回显
     * token
    */
    public function order()
    {
        if (IS_POST) {
            $this->_order();
            exit;
        }
        $cat_list = M('CartArt')->select(array('index'=>'id'));
        $data = array();
        //$child_id = array();
        foreach ($cat_list as $k=>$v) {
            if (isset($v['child']) && $v['id']==2) {//只取出吨位栏目
                $child_id = explode('|',$v['child']);
                //$child_id[]=$v['id'];
                //array_unshift($child_id,$v['id']);//加入父类id
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
        exit(json_encode(array('status'=>1,'msg'=>'临时租提交订单吨位数据回显成功','result'=>$arr)));
    }
    
    /**
     * 临时租--订单提交处理
     * token
     * dunwei
     * username、mobile、address
     *测试通过
     */
    public function addOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        $data = I('post.');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }
        if(!$data['address']){
            exit(json_encode(array('status'=>-1,'msg'=>'请填写用车地点！')));
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

    /**
     * 临时租--取消订单
     *token、temp_id
     * 测试通过
    */
    public function cancelOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        $data = I('post.');
        $data['is_cancel'] = 1;
        $data['status']    = 2;
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }

        $Temp = M('Temporary')->find($data['temp_id']);

        if($Temp['status']==2){
            exit(json_encode(array('status'=>1,'msg'=>'临时租取消订单成功')));
        }

        $res = M('Temporary')->where(array('temp_id'=>$data['temp_id']))->save($data);
        if ($res)
            exit(json_encode(array('status'=>1,'msg'=>'临时租取消订单成功')));
        exit(json_encode(array('status'=>-1,'msg'=>'取消订单失败')));
    }

    //临时租订单-重发订单
    public function againTemp()
    {
        $token   = I('post.token');
        $user_id = S($token); 
        $temp_id = I('post.temp_id');

        if(!$user_id){
            exit(json_encode(['status'=>-2,'msg'=>'缺少token']));
        }

        $data['status']    = 1;
        $data['temp_id']   = $temp_id;
        $data['driver_id'] = NULL;
        $data['push_time'] = NULL;
        $data['add_time']  = date('Y-m-d H:i:s',time());

        $Temp = M('Temporary')->find($temp_id);

        if(!$Temp){
            exit(json_encode(['status'=>-1,'msg'=>'该订单不存在！']));
        }

        if($Temp['user_id']!=$user_id){
            exit(json_encode(['status'=>-1,'msg'=>'该订单不存在！']));
        }

        $res = M('Temporary')->save($data);

        if($res){
            if($Temp['driver_id']){
                $TfData['status'] = 4;
                $TfWhere['user_id'] = $Temp['driver_id'];
                $TfWhere['temp_id'] = $temp_id;
                $content1 = $Temp['temp_sn'].'订单已被客户取消，接单失败！';
                $type    = ['type'=>10,'temp_id'=>$Temp['temp_id']];
                Jpush($Temp['driver_id'],$type,$content,$content1);
                M('TempInfo')->where($TfWhere)->save($TfData);
            }
            exit(json_encode(['status'=>1,'msg'=>'重发成功！']));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'重发失败！']));
        }
    }

    /**
     * 临时租--无人抢单
     *token、temp_id（订单id）
     *
     */
    /*public function overtimeOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        $data = I('post.');
        if (empty($user_id)) {
            exit(json_encode(array('status'=>-1,'msg'=>'非法访问临时租')));
        }
        //

        $time = M('Temporary')->field('add_time')->where(array('temp_id'=>$data['temp_id']))->find();
        if (time()-strtotime($time['add_time'])>(30*60))//30分钟内没人抢单
            exit(json_encode(array('status'=>-1,'msg'=>'司机正忙，请您耐心等待')));
    }*/

    /**
     * 临时租--抢单成功/未抢单
     *token、temp_id（订单id）
     *测试通过
     */
    public function finshOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        $data = I('post.');

        if (empty($user_id)) {
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }

        if (empty($data['temp_id'])) {
            exit(json_encode(array('status'=>-1,'msg'=>'缺少temp_id')));
        }

        $res = M('Temporary')->where(array('temp_id'=>$data['temp_id'],'user_id'=>$user_id,'_logic'=>'AND'))->find();

        //已经抢单
        $data = M('Users')->field('nickname,mobile')
                          ->where(array('user_id'=>$res['driver_id']))
                          ->find();


        if($data){
            $data['temp_id'] = $res['temp_id'];
            json_App(['status'=>1,'msg'=>'抢单司机信息','data'=>$data]);
        }else{
            json_App(['status'=>2,'msg'=>'没有数据','data'=>[]]);
        }
    }

    public function getUserInfo($user_id)
    {   
        $user_id = I('post.user_id')?I('post.user_id'):$user_id;
        if(!$user_id){
            return false;
        }
        $Users = M('Users')->where(['user_id'=>$user_id])->find();
    }


}