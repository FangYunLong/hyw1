<?php


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

    $dsn="mysql:dbname=hyw;host=localhost";
    $db_user='root';
    $db_pass='root';
    try{
     $pdo=new PDO($dsn,$db_user,$db_pass);
     $pdo->query("SET NAMES utf8");
    }catch(PDOException $e){
     echo '数据库连接失败'.$e->getMessage();
    }

    if(!$user_id){
        return false;
    }

    $sql   = "SELECT * FROM tp_users WHERE user_id =".$user_id;
    $Users = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)[0];
    // $Users = M('Users')->find($user_id);
    if(!$Users){
        return false;
    }

    $time = time();

    // //增加我的消息
    $sql = "INSERT INTO tp_msg(`type`,`is_read`,`is_del`,`user_id`,`content`,`public_time`) VALUE('1','0','1','{$user_id}','{$content1}','{$time}')";
    $pdo->exec($sql);

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