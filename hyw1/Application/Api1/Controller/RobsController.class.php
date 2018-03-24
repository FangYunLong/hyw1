<?php
/**
 * Created by PhpStorm.
 * User: Lonelytears
*: 2016/12/12 0012
 * Time: 下午 4:54
 */
namespace Api\Controller;

class RobsController extends BaseController {

    /**
     * 车主抢单栏目-判读车主/客户身份
     *token
    */
    public function lanmu()
    {
        $token = I('post.token');

        $user_id = S($token);
        if (empty($user_id)) {
            exit(json_encode(array('starus'=>-1,'msg'=>'您还没登陆，请先登录')));
        }

        $level_id = M('Users')->where(array('user_id'=>$user_id))->getField('level_id');

        if ($level_id==1) {
            exit(json_encode(array('status'=>1,'msg'=>'此号码已经注册为客户')));
        }elseif($level_id==2) {
            exit(json_encode(array('status'=>2,'msg'=>'车主')));
        }elseif($level_id==3) {
            exit(json_encode(array('status'=>3,'msg'=>'代理商')));
        }else{
            exit(json_encode(array('status'=>-2,'msg'=>'未知错误')));
        }
    }

    /**
     *车主抢单--年月租抢单列表
     *post请求、token（验证车主身份）
     */
    public function lorderInfo()
    {
        $token      = I('post.token_hyw');
        $user_id    = S($token);

        if(empty($user_id)){
            exit(json_encode(array('status'=>-2,'msg'=>'缺少token')));
        }

        //判断是否车主
        $user = M('Users')->where(array('user_id' => $user_id))->find();
        $level_id = $user['level_id'];

        if ($level_id != 2) {
            exit(json_encode(array('status'=>-3,'msg'=>'不是车主')));
        }

        $rows       = I('post.rows')?I('post.rows'):8;
        $page       = I('post.page')?I('post.page'):1;
        $num        = ($page-1)*$rows;
        $pinpai     = I('post.pinpai');
        $dunwei     = I('post.dunwei');
        $chezhong   = I('post.chezhong');
        $limit      = " LIMIT $num,$rows ";
        $where      = ' WHERE o.is_type=0 AND o.is_completed=0 ';
        $where     .= I('post.pinpai')?" AND g.pinpai='{$pinpai}' ":'';
        $where     .= I('post.dunwei')?" AND g.dunwei='{$dunwei}' ":'';
        $where     .= I('post.chezhong')?" AND g.chezhong='{$chezhong}' ":'';        

        $sql = " SELECT o.order_id,o.goods_id,g.address,g.pinpai,g.dunwei,g.chezhong
                 FROM tp_order AS o 
                 LEFT JOIN tp_goods AS g ON g.goods_id=o.goods_id 
                 {$where} ";
        $pages = ceil(count(M()->query($sql))/$rows);
        $res = M()->query($sql.$limit);

        if($res){
            exit(json_encode([
                'status'  =>   1,
                'msg'     =>   '获取数据成功',
                'page'    =>   $page,
                'pages'   =>   $pages,
                'rows'    =>   $rows,
                'result'  =>   $res
            ]));
        }else{
            exit(json_encode(array('status'=>-1,'msg'=>'获取数据失败')));
        }
    }

    /**
     * 车主抢单--车主下单-6-2A3
     *
     * token\zm_pic\cm_pic\czt_pic\nb_pic(四张图片，按顺序)\zdlzj(最低裸租价)
     */
    public function getOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        $data = I('post.');
        $data['user_id']=$user_id;
        // $user_id = 1;
        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        //假如一下子提交4张图片，那么该怎么处理
        //上传图片
        //定义上传路径
        $path="./Public/Upload/ad/";
        $uploadinfo=$this->fileUpload($path);
        
        if(!$uploadinfo){
            json_App(['status'=>-3,'msg'=>'图片错误']);
        }

        $data['zm_pic']  =  $path . $uploadinfo['zm_pic']['savepath']. $uploadinfo['zm_pic']['savename'];
        $data['cm_pic']  =  $path . $uploadinfo['cm_pic']['savepath']. $uploadinfo['cm_pic']['savename'];
        $data['czt_pic'] =  $path . $uploadinfo['czt_pic']['savepath']. $uploadinfo['czt_pic']['savename'];
        $data['nb_pic']  =  $path . $uploadinfo['nb_pic']['savepath']. $uploadinfo['nb_pic']['savename'];

        /*//缩略图
        $image = new \Think\Image();
        //拼接保存路径及名字
        $img=$uploadinfo['thumb']['savepath'].$uploadinfo['thumb']['savename'];
        $sm_path=$path.$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];
        $image->open('./Public/Upload/knowledge/'.$img);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
        $image->thumb(150, 150)->save($sm_path);

        //拼变量
        $data['small']=$uploadinfo['thumb']['savepath'].'sm_'.$uploadinfo['thumb']['savename'];*/

        $res = M('Goods')->add($data);
        if ($res) {
            exit(json_encode(array('status'=>1,'msg'=>'车主下单叉车信息提交成功')));
        }else {
            exit(json_encode(array('status'=>-1,'msg'=>'车主下单叉车信息提交失败')));
        }

    }


    /**
     * 车主抢单--抢单详情回显
     * order_id
     * 返回值：详情列表
     */
    public function orderInfo(){
        $order_id = I('post.order_id');
        // $order_id = '806';
        $res = M('Order')->field('order_sn,goods_id,tenancy,yhours')->where(array('order_id'=>$order_id))->find();
        $goods_id = $res['goods_id'];
        $good = M('Goods')->field('pinpai,dunwei,cart_type,menjia,mj_height,bydc,shuju,is_yt')->where(array('goods_id'=>$goods_id))->find();
        $good['order_sn']=$res['order_sn'];
        $good['tenancy']=$res['tenancy'];
        $good['yhours']=$res['yhours'];
// dump($good);exit;
        exit(json_encode(array('status'=>1,'msg'=>'车主抢单详情','result'=>$good)));
    }
    /**
     * 车主抢单--抢单提交
     * order_id、token
     * 返回值
    */
    public function pushlorder()
    {
        $token      = I('post.token');
        $user_id    = S($token);
        //$user_id  = I('post.user_id');
        $order_id   = I('post.order_id');
        $data[]     = $user_id;
        $data[]     = $order_id;
        self::$j    = self::$j+1;
        $memcache   = new \Think\Cache\Driver\Memcache();//实例化
        //
        $memcache->set("order".self::$j,$data,30,0);//写入队列  30秒有效时间
        //怎么确定哪一个是第一个进来的？==》取第一个，入库后$i归零，
        $arr = $memcache->get("order1");//读取队列
        $order['driver_id'] = $arr[0];//抢单司机
        $order['order_id']  = $arr[1];
        $order['is_play']   = 1;
        $order['push_time'] = date('Y-m-d H:i:s',time());
        if ($user_id != $order['driver_id']) {
            exit(json_encode(array('status'=>-1,'msg'=>'抱歉你手速慢了，订单已被抢')));
        }
        //入库前检查这个单是否已经被抢
        $arr = M('Order')->where(array('order_id'=>$order['order_id']))->find();
        if ($arr['driver_id'] != 0) {
            if ($user_id==$arr['driver_id']) {
                exit(json_encode(array('status'=>1,'msg'=>'你已经抢单成功，无需重复抢单')));
            }
            exit(json_encode(array('status'=>-1,'msg'=>'抱歉你手速慢了，订单已被抢')));
        }
        $order['is_completed'] = 1;
        $res = M('Order')->where(array('order_id'=>$order['order_id']))->save($order);
        self::$j =0;
        if ($res) {
            //订单信息回显
            // $result = M('Temporary')->alias('tb1')->join('tp_users tb2 on tb1.user_id=tb2.user_id')->field('tb1.mobile,tb1.nickname,tb2.dunwei,tb2.address')->where(array('temp_id'=>$order['temp_id']))->find();
           /* $result = M('Temporary')->field('mobile,username,dunwei,address')->where(array('temp_id'=>$order['temp_id']))->find();*/
            //dump($result);die;
            exit(json_encode(array('status'=>1,'msg'=>'立即抢单成功')));
        } else {
            exit(json_encode(array('status'=>-1,'msg'=>'立即抢单失败')));
        }
    }


    //*********     临时租     ***************//
    /**
     * 车主抢单--临时租车主抢单列表
     * get请求
     * 返回值：成功==temp_id(临时租订单id)，dunwei（吨位）、limit（距离当前位置多少米）
    */
    public function tempList()
    {
        //获取当前地址经纬度
        $getIp=$_SERVER["REMOTE_ADDR"];//获取客户端ip
        $ak = M('Interface')->where(['id'=>1])->getField('ak');
        $content = file_get_contents("http://api.map.baidu.com/location/ip?ak={$ak}&ip={$getIp}&coor=bd09ll");//调用百度api
        $json = json_decode($content);
        //$json = json_encode($content);
        $lon = $json->{'content'}->{'point'}->{'x'};//按层级关系提取经度数据
        $lat = $json->{'content'}->{'point'}->{'y'};//按层级关系提取纬度数据
        //获取位置数据表里面的位置信息
        $data = D('Address')->select();//取得所有经纬度信息
        //先求距离，大于10公里的则不显示
        foreach ($data as $k => $v) {
            $limit = $this->getDistance($v['lat'],$v['lon'],$lat,$lon);//求得距离
            if ($limit<10000) {
                //处理用户id集
                $user_ids[] = $v['user_id'];
                $limits[$v['user_id']] = $limit;//距离、单位/米
            }
        }

        if (empty($user_ids)) {
            exit(json_encode(array('status'=>1,'msg'=>'临时租抢单列表没数据')));
        }
        //查询这些用户的所有未抢订单
        $orders = D('Temporary')->field('temp_id,dunwei')->where(array('user_id'=>array('in',$user_ids),'is_play'=>0,'_logic'=>'AND'))->select();
        //拼接数据
        foreach ($limits as $k =>$v) {
            foreach ($orders as $kk =>$vv) {
                if ($vv['user_id']==$k) {
                    $vv['limit'] = $v;
                    $arr[] =$vv;
                }
            }
        }
        if ($orders) {
            exit(json_encode(array('status'=>1,'msg'=>'临时租抢单列表成功','result'=>$arr)));
        } else {
            exit(json_encode(array('status'=>1,'msg'=>'临时租抢单列表没数据','result'=>array())));
        }
    }

    /**
     * 根据两点经纬度计算距离===============================
     *
    */
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
         $earthRadius = 6367000; //approximate radius of earth in meters
         $lat1 = ($lat1 * pi() ) / 180;
         $lng1 = ($lng1 * pi() ) / 180;
         $lat2 = ($lat2 * pi() ) / 180;
         $lng2 = ($lng2 * pi() ) / 180;
         $calcLongitude = $lng2 - $lng1;
         $calcLatitude = $lat2 - $lat1;
         $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
         $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
         $calculatedDistance = $earthRadius * $stepTwo;
         return round($calculatedDistance);
     }

     /**
      * 临时租--车主抢单详情
      * temp_id(订单id)、limit（距离米）
      * post
      * 返回值：limit、temp_id、address（地址）
     */
     public function tempInfo()
     {
         $temp_id = I('post.temp_id');
         $limit = I('post.limit');
         $order = D('Temporary')->field('temp_id,dunwei,address')->where(array('temp_id'=>$temp_id))->find();
         $order['limit'] = $limit;
         exit(json_encode(array('status'=>1,'msg'=>'临时租车主抢单详情','result'=>$order)));
     }

    /**
     * 车主抢单--临时租--立即抢单
     * temp_id（）、token
     */
    public function pushOrder()
    {
        $token = I('post.token');
        $user_id = S($token);
        //$user_id = I('post.user_id');
        $temp_id = I('post.temp_id');
        $data[] = $user_id;
        $data[] = $temp_id;
        self::$i = self::$i+1;
        $memcache = new \Think\Cache\Driver\Memcache();//实例化
        $memcache->set("order".self::$i,$data,30,0);//写入队列   30秒有效时间
        //怎么确定哪一个是第一个进来的？==》取第一个，入库后$i归零，
        $arr = $memcache->get("order1");//读取队列
        $order['driver_id'] = $arr[0];//抢单司机
        $order['temp_id'] = $arr[1];
        $order['is_play'] = 1;
        $order['push_time'] = date('Y-m-d H:i:s',time());
        if ($user_id != $order['driver_id']) {
            exit(json_encode(array('status'=>-1,'msg'=>'抱歉你手速慢了，订单已被抢')));
        }
        //入库前检查这个单是否已经被抢
        $arr = M('Temporary')->where(array('temp_id'=>$order['temp_id']))->find();
        if ($arr['driver_id'] != 0) {
            if ($user_id==$arr['driver_id']) {
                exit(json_encode(array('status'=>1,'msg'=>'你已经抢单成功，无需重复抢单')));
            }
            exit(json_encode(array('status'=>-1,'msg'=>'抱歉你手速慢了，订单已被抢')));
        }
        $res = M('Temporary')->where(array('temp_id'=>$order['temp_id']))->save($order);
        self::$i =0;
        if ($res) {
            //订单信息回显
            // $result = M('Temporary')->alias('tb1')->join('tp_users tb2 on tb1.user_id=tb2.user_id')->field('tb1.mobile,tb1.nickname,tb2.dunwei,tb2.address')->where(array('temp_id'=>$order['temp_id']))->find();
            $result = M('Temporary')->field('mobile,username,dunwei,address')->where(array('temp_id'=>$order['temp_id']))->find();
            //dump($result);die;
            exit(json_encode(array('status'=>1,'msg'=>'立即抢单成功','result'=>$result)));
        } else {
            exit(json_encode(array('status'=>-1,'msg'=>'立即抢单失败')));
        }

    }

    //*********  end   临时租  ***************//

    /**
     * 车主抢单-抢单列表-获取筛选条件的可选项
     * post
     */
    public function getScreening()
    {
        $token   = I('post.token');
        $user_id = S($token);

        if(!$user_id){
            json_App(['status'=>-2,'msg'=>'缺少token']);
        }

        $Goods = M('Goods')->field('pinpai,dunwei,chezhong')->select();

        foreach($Goods as $k => $v)
        {
            $pinpai[]     = $v['pinpai'];
            $dunwei[]     = $v['dunwei'];
            $chezhong[]   = $v['chezhong'];
        }

        sort($dunwei);
        $data['pinpai']   = array_unique($pinpai);
        $data['dunwei']   = array_unique($dunwei);
        $data['chezhong'] = array_unique($chezhong);

        if($data){
            json_App(['status'=>1,'msg'=>'获取数据成功','result'=>$data]);
        }else{
            json_App(['status'=>-1,'msg'=>'获取失败']);
        }
    }
}