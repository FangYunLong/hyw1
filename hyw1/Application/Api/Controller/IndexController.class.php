<?php
/**
 *
//
 * Author: 当燃      
*: 2015-09-09
 */
namespace Api\Controller;


class IndexController extends BaseController {

    public function index(){

        $this->display();
    }

    public function push()
    {
        // $res = Jpush(2612,'','666','666',true);
        $res = Jpush(2617,['type'=>7],'666','666',true);
        $res = Jpush(2612,['type'=>7],'666','666',true);
        dump($res);
    }

    /**
     * 模拟post进行url请求
     * @param string $url
     * @param array $post_data
     */
    function request_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {
            return false;
        }

        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }
    //调用模拟post请求
    function testAction(){
       // $url = 'http://www.tpshop.com/index.php/Api/Login/userlogin';
       // $url = 'http://www.tpshop.com/index.php/Api/user/delCollect';//删除收藏
        //$url = 'http://www.tpshop.com/index.php/Api/user/addCollect';//加入收藏
        //$url = 'http://www.tpshop.com/index.php/Api/user/collect';//收藏列表
        //$url = 'http://www.tpshop.com/index.php/Api/user/resume';//我的求职简历
        //$url = 'http://www.tpshop.com/index.php/Api/user/addresume';//我的求职简历-添加
        //$url = 'http://www.tpshop.com/index.php/Api/user/hidresume';//我的求职简历-隐藏或公开
        //$url = 'http://www.tpshop.com/index.php/Api/specialCart/publishCart';//我的特价车--发布
        /*$url = 'http://www.tpshop.com/index.php/Api/specialCart/specialCartList';//我的特价车-详情*/
        /*$url = 'http://www.tpshop.com/index.php/Api/temporary/addorder';//临时租-订单提交*/
        /*$url = 'http://www.tpshop.com/index.php/Api/temporary/cancelOrder';//临时租-取消订单*/
       /* $url = 'http://www.tpshop.com/index.php/Api/temporary/finshOrder';//临时租-超时订单*/
         $url = 'http://www.tpshop.com/index.php/Api/login/userlogin';//年月租
         $url = 'http://hyw.web66.cn:8090/index.php/Api/index/ad';//年月租


        /*$post_data['appid']       = '10';
        $post_data['appkey']      = 'cmbohpffXVR03nIpkkQXaAA1Vf5nO4nQ';
        $post_data['member_name'] = 'zsjs124';
        $post_data['password']    = '123456';
        $post_data['email']    = 'zsjs124@126.com';*/
        /*$post_data['username']    = '13800138006';
        $post_data['password']    = '123456';//测试登录*/
        /*$post_data['collect_id'] ='121';
        $post_data['user_id'] ='1';//测试删除收藏*/
        /*$post_data['goods_id'] ='121';
        $post_data['user_id'] ='1';//测试加入收藏*/
        //post_data['user_id'] ='2';//测试收藏列表
        /*$post_data['user_id'] ='2';//
        $post_data['user_name'] ='陌君颜';
        $post_data['sex'] ='0';
        $post_data['age'] ='30';
        $post_data['jingyan'] ='1年';
        $post_data['xueli'] ='本科';
        $post_data['mobile'] ='18312706605';
        $post_data['address'] ='广东深圳';//测试简历
        $post_data['is_hidden'] =0;*/
        /*$post_data['user_id'] ='2';//
        $post_data['is_hidden'] ='1';//*/
       /* //特价车发布
        $post_data['user_id'] ='2';
        $post_data['pinpai'] ='三菱';
        $post_data['dunwei'] ='200吨';
        $post_data['cart_type'] ='平衡重式柴油叉车';
        $post_data['menjia'] ='二节标准门架';
        $post_data['mj_height'] ='300mm';
        $post_data['shuju'] ='推拉器';
        $post_data['is_yt'] ='2';
        $post_data['cart_age'] ='2年';
        $post_data['use_hours'] ='1年';
        $post_data['dcsj'] ='30小时';
        $post_data['description'] ='好运旺叉车';
        $post_data['address'] ='广州天河';
        $post_data['buytime'] ='2016-12-15';
        $post_data['username'] ='lonelytears';
        $post_data['mobile'] ='18312706605';
        $post_data['is_special'] ='1';
        $post_data['special_price'] ='3800';*/
       /* $post_data['user_id'] ='2';
        $post_data['goods_id'] ='152';//特价车详情*/
        /*$post_data['user_id'] ='2';
        $post_data['is_special'] ='1';*/
        /*$post_data['user_id'] ='2';
        $post_data['dunwei'] ='200';
        $post_data['username'] ='lonelytears';
        $post_data['mobile'] ='13800138000';
        $post_data['address'] ='广州天河';
        $post_data['add_time'] ='2016-12-19';//临时租-订单提交*/
        /*$post_data['user_id'] ='2';
        $post_data['temp_id'] ='1';*/
        /*//goods_id、tenancy(租期)、yhours（使用小时数）、mprice（月租金）、number、mobile、use_user（收车人）、address（地址）、invoice（发票抬头）
        $post_data['goods_id'] ='151';
        $post_data['tenancy'] ='12个月';
        $post_data['yhours'] ='1000小时';
        $post_data['mprice'] ='3500元';
        $post_data['number'] ='1';
        $post_data['mobile'] ='13800138000';
        $post_data['use_user'] ='剑随风';
        $post_data['address'] ='广州天河';
        $post_data['invoice_title'] ='剑随风';
        $post_data['user_id'] ='2';//提交订单*/
       /* $post_data['user_id'] ='2';
        $post_data['order_id'] ='806';*/
        $post_data['username'] ='18312706605';
        $post_data['password'] ='18312706605';
        /*$post_data['password2'] ='18312706605';
        $post_data['level_id'] ='1';
        $post_data['code'] ='2347';*/



        //$post_data = array();
        $res = $this->request_post($url, $post_data);
        print_r($res);
    }


    //首页轮播图api
    public function ad()
    {
        $today['ip'] = $this->getIPaddress();
        $today['add_time'] = time();
        $today['status'] = 1;
        M('UserIp')->add($today);        

        $data = M('Ad')->field('ad_code')->where(array('ad_address'=>'首页banner'))->select();
        foreach ($data as $k=>$v) {
            $data[$k]['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
        }
        exit(json_encode(array('status'=>1,'msg'=>'首页轮播图','result'=>$data)));
    }
    //
	//车主抢单栏目
    public function banner()
    {
        $data = M('Ad')->field('ad_code')->where(array('ad_address'=>'车主抢单栏目banner'))->select();
        foreach ($data as $k=>$v) {
            $data[$k]['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
        }
        exit(json_encode(array('status'=>1,'msg'=>'首页轮播图','result'=>$data)));
    }

    //临时租的广告
    public function tempAdvert()
    {
        $data = M('Ad')->field('ad_id,ad_code,end_time')->where(array('ad_id'=>['in','71,72,73,74,82,75']))->order('ad_id')->select();
        $array = ['img1','img2','img3','img4','img5'];

        foreach($data as $k => $v){
            
            if($v['ad_id']==82){
                continue;
            }

            if($v['end_time'] < time()){
                $list[$array[$k]] = $data[5]['ad_code'];
            }else{
                $list[$array[$k]] = $v['ad_code'];
            }
        }

        json_App(['status'=>1,'msg'=>'成功','data'=>$list]);
    }

    //分享
    public function share()
    {
        $share = tpCache('sms');
        $shareInfo['title']    =  $share['title'];
        $shareInfo['img']      =  APP_URL . $share['img'];
        $shareInfo['android']  =  $share['android'];
        $shareInfo['ios']      =  $share['IOS'];
        $shareInfo['describe'] =  $share['describe'];

        if($shareInfo){
            json_App(['status'=>1,'msg'=>'成功','shareInfo'=>$shareInfo]);
        }else{
            json_App(['status'=>'-1','msg'=>'失败']);
        }
    } 

    //关于我们 1
    public function about()
    {
        $this->display();
    }

    //好运旺租叉车车主交易细则 2
    public function agreement()
    {
        $this->display();
    }    

    //好运旺租叉车客户交易细则 3
    public function userAgreement()
    {
        $this->display();
    }  

    //好运旺租叉车特价车交易细则 4
    public function special()
    {
        $this->display();
    }

    //好运旺租叉车平台服务条款 5
    public function terms()
    {
        $this->display();
    }

    // //好运旺租叉车条款
    // public function terms()
    // {
    //     $this->display();
    // }

    //租车指南 6
    public function carGuide()
    {
        $this->display();
    }    

    //免责声明 7
    public function liability()
    {
        $this->display();
    }

}