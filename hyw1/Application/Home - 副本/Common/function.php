<?php
/**
 * 验证码发送
 * @param $mobile 手机号码
 * @param $content 发送内容
 * @param $type 验证码类型
 */
function send_sms($mobile,$content,$type=''){

}

//检查验证码
function check_verify($mobile,$verify){
    //验证码在缓存中将手机号作为key，验证码为vlaue
    $code=S($mobile);

    if($code==$verify){
        return true;
    }else{
        return false;
    }
}

/**
 * 分页导览
 * @param $page  当前页码
 * @param $pages 总页码数
 * @param $cols  导览几页
 */
function pageRows($page = 1,$pages,$cols = 5)
{
    $data = [];

    if($pages <= $cols){
        while($pages > 0){
            $data[] = $pages;
            $pages--;
        }
        sort($data);
        return $data;
    }elseif($page + ($cols / 2) < $pages && $page < ($cols / 2)){
        while($cols > 0){
            $data[] = $cols;
            $cols--;
        }
        sort($data);
        return $data;
    }elseif($page + ($cols / 2) < $pages && $page >= ($cols / 2)){
        $i = 1 - ceil($cols / 2);
        while($i <= ($cols / 2)){
            $data[] = $page + $i;
            $i++;
        }
        sort($data);
        return $data;
    }elseif($page + ($cols / 2) >= $pages){
        $i = 0;
        while($i < $cols){
            $data[] = $pages - $i;
            $i++;
        }
        sort($data);
        return $data;
    }

}

function fileUploadNews($path='',$files='')
{
    $type = ['jpg','jpeg','png','gif'];
    foreach($files as $key => $val){

        if($val['size'] <= 0){
            continue;
        }

        $types = explode('/',$val['type'])[1];
        // dump($val);
        $restd = in_array($types,$type);
        if(!$restd){          
            return false;
        }
    }
// exit;
    foreach($files as $k => $v){

        if($val['size'] <= 0){
            continue;
        }        

        $str = date('Y-m-d',time()).'/'.md5(time().rand(1,999)).'.'.explode('.',$v['name'])[1];
        $filename = $v['tmp_name'];
        $destination  = $path.$str;
        if(!file_exists($destination)){
            $dirPath = $path.date('Y-m-d',time());
            $resb = mkdir($dirPath,0755,true);
        }
        move_uploaded_file($filename,$destination);
        $res[$k] = $str;
    }
    return $res;
}

function fileUploadNews1($path='',$files='')
{
    $type = ['jpg','jpeg','png','gif'];
    foreach($files as $key => $val){

        if($val['size'] <= 0){
            continue;
        }

        $types = explode('/',$val['type'])[1];
        // dump($val);
        $restd = in_array($types,$type);
        if(!$restd){          
            json_App(['status'=>-1,'msg'=>'图片格式错误！']);
        }
    }
// exit;
    foreach($files as $k => $v){

        if($val['size'] <= 0){
            continue;
        }        

        $str = date('Y-m-d',time()).'/'.md5(time().rand(1,999)).'.'.explode('.',$v['name'])[1];
        $filename = $v['tmp_name'];
        $destination  = $path.$str;
        if(!file_exists($destination)){
            $dirPath = $path.date('Y-m-d',time());
            $resb = mkdir($dirPath,0755,true);
        }
        move_uploaded_file($filename,$destination);
        $res[$k] = $str;
    }
    return $res;
}

/**
 * 文件上传
*/
function fileUpload($path){
    $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728 ;// 设置附件上传大小
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
   // $upload->savePath  =      './Public/Uploads/'; // 设置附件上传目录
   // $upload->rootPath  =      './Public/Uploads/Cart/'; // 设置附件上传目录
    $upload->rootPath  =      $path;; // 设置附件上传目录
    // 上传文件
    $info   =   $upload->upload();
    if(!$info) {
        // 上传错误提示错误信息
       // $this->error($upload->getError());
       exit(json_encode(array('status'=>-1,'msg'=>'上传失败'))) ;
    }else{
        // 上传成功
        //$this->success('上传成功！');
        return array('status'=>1);
    }
}

function json_App($data){
    exit(json_encode($data));
}

/**
 * 返回接口数据
 *@param $success 是否正确
 *@param $code 状态码
 *@param $data 参数数组
 *
 */
function api_show($success='false',$code='00000',$data=array()){
    $data['success']=$success;
    $data['code']=$code;
    echo json_encode($data,true);
    exit;
}

/**  
 * User: Administrator  
*Time: 2014/12/31 10:01  
 */    
function bankInfo($card)    
{    
    require_once APP_PATH.'Common/Conf/bankList.php';    
    $card_8 = substr($card, 0, 8);    
    if (isset($bankList[$card_8])) {    
        return $bankList[$card_8];    
            
    }    
    $card_6 = substr($card, 0, 6);    
    if (isset($bankList[$card_6])) {    
        return $bankList[$card_6];    
            
    }    
    $card_5 = substr($card, 0, 5);    
    if (isset($bankList[$card_5])) {    
        return $bankList[$card_5];    
            
    }    
    $card_4 = substr($card, 0, 4);    
    if (isset($bankList[$card_4])) {    
        return $bankList[$card_4];    
    }    
    return false;
} 

/**
 * 数字签名
 *@param  $params 用户名
 *@param  $token  用户token
 *@param  $secret 签名密钥
 */
function getSignature($params, $token,$secret='lonelytears'){
    $str = '';  //待签名字符串
    //
    $str .= $params;
    $str .= $token;
    //将签名密钥拼接到签名字符串最后面
    $str .= $secret;
    //通过md5算法为签名字符串生成一个md5签名，该签名就是我们要追加的sign参数值
    return md5($str);
}

/**
 * 面包屑导航  用于前台用户中心
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_user()
{    
    $navigate = include APP_PATH.'Common/Conf/navigate.php';    
    $location = strtolower('Home/'.CONTROLLER_NAME);
    $arr = array(
        '首页'=>'/',
        $navigate[$location]['name']=>U('/Home/'.CONTROLLER_NAME),
        $navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
    );
    return $arr;
}

/**
 *  面包屑导航  用于前台商品
 * @param type $id 商品id  或者是 商品分类id
 * @param type $type 默认0是传递商品分类id  id 也可以传递 商品id type则为1
 */
function navigate_goods($id,$type = 0)
{
    $cat_id = $id; //
    // 如果传递过来的是
    if($type == 1){
        $cat_id = M('goods')->where("goods_id = $id")->getField('cat_id');
    }
    $categoryList = M('GoodsCategory')->getField("id,name,parent_id");

    // 第一个先装起来
    $arr[$cat_id] = $categoryList[$cat_id]['name'];
    while (true)
    {
        $cat_id = $categoryList[$cat_id]['parent_id'];
        if($cat_id > 0)
            $arr[$cat_id] = $categoryList[$cat_id]['name'];
        else
            break;
    }
    $arr = array_reverse($arr,true);
    return $arr;
}

/**
 * 发送消息推送
 * @param $user_id 用户id 
 * @param $content 消息内容
 */
function addMsg($user_id='',$content='')
{
    if((!$user_id)||(!$content)){
        return false;
    }
    $data['type'] = 1;
    $data['is_read'] = 0;
    $data['user_id'] = $user_id;
    $data['content'] = $content;
    $data['is_del'] = 1;
    $data['public_time'] = time();
    $res = M('Msg')->add($data);
}

/**
 * 发送模板短信
 * @param to 手机号码集合,用英文逗号分开
 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
 * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
 */
function sendTemplateSMS($to,$datas,$tempId)
{
    require_once VENDOR_PATH . 'SMS/CCPRestSmsSDK.php';

    // 初始化REST SDK
    //global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;

    extract(C('SMS'));

    $rest = new REST($serverIP,$serverPort,$softVersion);
    $rest->setAccount($accountSid,$accountToken);
    $rest->setAppId($appId);

    // 发送模板短信
    $result = $rest->sendTemplateSMS($to,$datas,$tempId);
    if($result == NULL ) {
        return NULL;
    }
    if($result->statusCode!=0) {
        return $result->statusMsg;
    }else{
        return true;
    }
    // 发送模板短信
    /*  echo "Sending TemplateSMS to $to <br/>";
     $result = $rest->sendTemplateSMS($to,$datas,$tempId);
     if($result == NULL ) {
         echo "result error!";
         break;
     }
     if($result->statusCode!=0) {
         echo "error code :" . $result->statusCode . "<br>";
         echo "error msg :" . $result->statusMsg . "<br>";
         //TODO 添加错误处理逻辑
     }else{
         echo "Sendind TemplateSMS success!<br/>";
         // 获取返回信息
         $smsmessage = $result->TemplateSMS;
         echo "dateCreated:".$smsmessage->dateCreated."<br/>";
         echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
         //TODO 添加成功处理逻辑
     } */
}

/***************甬易支付**************/
//参数加密方式
function HmacMd5($data,$key)    
{    
    // RFC 2104 HMAC implementation for php.    
    // Creates an md5 HMAC.    
    // Eliminates the need to install mhash to compute a HMAC    
    // written by shihh
   
    //需要配置环境支持iconv，否则中文参数不能正常处理    
    $key = iconv("GB2312","UTF-8",$key);    
    $data = iconv("GB2312","UTF-8",$data);    
   
    $b = 64; // byte length for md5    
    if (strlen($key) > $b) {    
        $key = pack("H*",md5($key));    
    }    
    $key = str_pad($key, $b, chr(0x00));    
    $ipad = str_pad('', $b, chr(0x36));    
    $opad = str_pad('', $b, chr(0x5c));    
    $k_ipad = $key ^ $ipad ;    
    $k_opad = $key ^ $opad;    
   
    return md5($k_opad . pack("H*",md5($k_ipad . $data)));    
}

/**
 * 模拟post进行url请求
 * @param string $url
 * @param array $post_data
 */
function request_post_yongyi($url = '', $post_data = array()) {

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

/**
 * 发起支付请求
 * @param string orderNo   订单号
 * @param float  orderAmt  订单金额 保留小数点后2位
 * @param int    userId    支付者user_id
 * @param string goodsName 商品名称
 */
function yongyiPays($user_id='',$orderNo='',$orderAmt='',$type='',$remark='',$true='')
{
    require_once APP_PATH . 'Common/Conf/payconfig.php';
    // $orderNo = 'sn'.time().mt_rand(1000,9999);
    // $orderAmt = '0.01';
    $pay_sno = date('YmdHis',time()).mt_rand(1000,9999);
    $pay_date['pay_type'] = $type;
    $pay_date['user_id'] = $user_id;
    $pay_date['money'] = $orderAmt;
    $pay_date['order_id'] = $orderNo;
    $pay_date['pay_status'] = 1;
    switch($type){
        case 1:
            $pay_date['pay_sn'] = 'hywy'.$pay_sno;//支付押金生成的支付单号
        break;
        case 2:
            $pay_date['pay_sn'] = 'hywz'.$pay_sno;//支付租金生成的支付单号
        break;
        case 3: 
            $pay_date['pay_sn'] = 'hywt'.$pay_sno;//支付特价车生成的支付单号
        break;
    }

    $pay_res = M('Pay')->add($pay_date);

    if(!$pay_res){
        return false;
    }

    $xml = '<?xml version="1.0" encoding="GBK" ?>
            <B2CReq>
            <merchantId>'.$merchantId.'</merchantId>
            <orderNo>'.$pay_date['pay_sn'].'</orderNo>
            <orderAmt>'.$orderAmt.'</orderAmt>
            <curType>CNY</curType>
            <returnURL>'.$returnURL.'</returnURL>
            <notifyURL>'.$notifyURL.'</notifyURL>
            <remark>'.$remark.'</remark>
            </B2CReq>'; 

    $tranData = base64_encode($xml);
    $merSignMsg = HmacMd5($xml,$key);  

    $data['interfaceName'] = $interfaceName;
    $data['version'] = $version;
    $data['merchantId'] = $merchantId;
    $data['tranData'] = $tranData;
    $data['merSignMsg'] = $merSignMsg;
    if($true){
        $res = request_post_yongyi($url,$data);
        return $res;    
    }
    $res['data'] = $data;
    $res['url'] = $url;
    return $res;
}

/************甬易支付结束**************/

/****************极光推送*******************/
/*  $receiver 接收者的信息
    all 字符串 该产品下面的所有用户. 对app_key下的所有用户推送消息
    tag(20个)Array标签组(并集): tag=>array('昆明','北京','曲靖','上海');
    tag_and(20个)Array标签组(交集): tag_and=>array('广州','女');
    alias(1000)Array别名(并集): alias=>array('93d78b73611d886a74*****88497f501','606d05090896228f66ae10d1*****310');
    registration_id(1000)注册ID设备标识(并集):  =>array('20effc071de0b45c1a**********2824746e1ff2001bd80308a467d800bed39e');
*/
//$content 推送的内容。
//$m_type 推送附加字段的类型(可不填) http,tips,chat....
//$m_txt 推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
//$m_time 保存离线时间的秒数默认为一天(可不传)单位为秒
function push($receiver='all',$content='',$extras='',$title='',$m_time='86400'){

    $app_key = '1dc3dbf1e9daecd2f38c7201';          //待发送的应用程序(appKey)，只能填一个。
    $master_secret = 'ce1b825385772c2dae6f2c1d';    //主密码
    $url = "https://api.jpush.cn/v3/push";          //推送的地址
    $base64=base64_encode("{$app_key}:{$master_secret}");
    $header=array("Authorization:Basic {$base64}","Content-Type:application/json");
    $data = array();
    $data['platform'] = 'all';          //目标用户终端手机的平台类型android,ios,winphone
    $data['audience'] = $receiver;      //目标用户

    $data['notification'] = array(
        //统一的模式--标准模式
        "alert"=>$content,   
        //安卓自定义
        "android"=>array(
            "alert"=>$content,
            "title"=>$title,
            "builder_id"=>1,
            "extras"=>$extras
        ),
        //ios的自定义
        "ios"=>array(
            "alert"=>$content,
            "badge"=>"1",
            "sound"=>"default",
            "extras"=>$extras
        ),
    );

    //苹果自定义---为了弹出值方便调测
    // $data['message'] = array(
    //     "msg_content"=>$content,
    //     "extras"=>$extras
    // );

    //附加选项
    $data['options'] = array(
        "sendno"=>time(),
        "time_to_live"=>$m_time, //保存离线时间的秒数默认为一天
        "apns_production"=>0,        //指定 APNS 通知发送环境：0开发环境，1生产环境。
    );
    $param = json_encode($data);
    $res = push_curl($param,$header,$url);

    pushLogs($res);

    if($res){       //得到返回值--成功已否后面判断
        return $res;
    }else{          //未得到返回值--返回失败
        return false;
    }
}

function Jpush($user_id='',$extras='',$content='',$content1='',$true=''){

    if(!$user_id){
        return false;
    }

    $Users = M('Users')->find($user_id);

    if(!$Users){
        return false;
    }

    //增加我的消息
    addMsg($user_id,$content1);

    //离线状态，不推送消息
    if(!$Users['login_status']){
        return false;
    }

    if(!$Users['identifier']){
        return false;
    }

    if(!$true){
        $content = '';
    }

    $res = push(array('registration_id'=>array($Users['identifier'])),$content,$extras);

    return $res;
}

//推送的Curl方法
function push_curl($param="",$header="",$url="") {
    if (empty($param)) { return false; }
    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init();                                      //初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);           // 增加 HTTP Header（头）里的字段 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $data = curl_exec($ch);                                 //运行curl
    curl_close($ch);
    return $data;
}

//极光推送错误日志文件
function pushLogs($result){  
    $path = './Public/log/'.date('Y-m-d',time()).'.txt';
    $fp = fopen($path,'a+');
    if($result){  
        if(isset($res_arr['error'])){   //如果返回了error则证明失败  
            //错误信息 错误码  
            $res = "\r\n".date('Y-m-d H:i:s').' error: '.$res_arr['error']['message'].'：'.$res_arr['error']['code'];      
        } 
    }else{      
        //接口调用失败或无响应  
        $res = "\r\n".date('Y-m-d H:i:s').' No response ';      
    }  
    fwrite($fp, $res);
    fclose($fp);
}  

/******************极光推送结束***********************/