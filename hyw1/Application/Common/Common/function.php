<?php

/**
 * 转换SQL关键字
 *
 * @param unknown_type $string
 * @return unknown
 */
function strip_sql($string) {
	$pattern_arr = array(
            "/\bunion\b/i",
            "/\bselect\b/i",
            "/\bupdate\b/i",
            "/\bdelete\b/i",
            "/\boutfile\b/i",
            "/\bor\b/i",
            "/\bchar\b/i",
            "/\bconcat\b/i",
            "/\btruncate\b/i",
            "/\bdrop\b/i",            
            "/\binsert\b/i", 
            "/\brevoke\b/i", 
            "/\bgrant\b/i",      
            "/\breplace\b/i", 
            "/\balert\b/i", 
            "/\brename\b/i",            
            "/\bcreate\b/i",
            "/\bmaster\b/i",
            "/\bdeclare\b/i",
            "/\bsource\b/i",
            "/\bload\b/i",
            "/\bcall\b/i", 
            "/\bexec\b/i",         
            "/\bdelimiter\b/i",            
	);
	$replace_arr = array(
            'ｕｎｉｏｎ',
            'ｓｅｌｅｃｔ',
            'ｕｐｄａｔｅ',
            'ｄｅｌｅｔｅ',
            'ｏｕｔｆｉｌｅ',
            'ｏｒ',
            'ｃｈａｒ',
            'ｃｏｎｃａｔ',
            'ｔｒｕｎｃａｔｅ',
            'ｄｒｏｐ',            
            'ｉｎｓｅｒｔ',
            'ｒｅｖｏｋｅ',
            'ｇｒａｎｔ',
            'ｒｅｐｌａｃｅ',
            'ａｌｅｒｔ',
            'ｒｅｎａｍｅ',
            'ｃｒｅａｔｅ',
            'ｍａｓｔｅｒ',
            'ｄｅｃｌａｒｅ',
            'ｓｏｕｒｃｅ',
            'ｌｏａｄ',
            'ｃａｌｌ',                     
            'ｅｘｅｃ',         
            'ｄｅｌｉｍｉｔｅｒ',
	);

	return is_array($string) ? array_map('strip_sql', $string) : preg_replace($pattern_arr, $replace_arr, $string);
}

/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 
 */
function convert_arr_key($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val;        
	}
	return $arr2;
}

/**
 * 获取数组中的某一列
 * @param type $arr 数组
 * @param type $key_name  列名
 * @return type  返回那一列的数组
 */
function get_arr_column($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[] = $val[$key_name];        
	}
	return $arr2;
}


/**
 * 获取url 中的各个参数  类似于 pay_code=alipay&bank_code=ICBC-DEBIT
 * @param type $str
 * @return type
 */
function parse_url_param($str){
    $data = array();
    $parameter = explode('&',end(explode('?',$str)));
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}

function getIpAddress()
    {

        $IPaddress='';

        if (isset($_SERVER)){

            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){

                $IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];

            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {

                $IPaddress = $_SERVER["HTTP_CLIENT_IP"];

            } else {

                $IPaddress = $_SERVER["REMOTE_ADDR"];

            }

        } else {

            if (getenv("HTTP_X_FORWARDED_FOR")){

                $IPaddress = getenv("HTTP_X_FORWARDED_FOR");

            } else if (getenv("HTTP_CLIENT_IP")) {

                $IPaddress = getenv("HTTP_CLIENT_IP");

            } else {

                $IPaddress = getenv("REMOTE_ADDR");

            }

        }
        return $IPaddress;
    }

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param string $type
 * @return array
 */
function array_sort($arr, $keys, $type = 'desc')
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($key_value);
    } else {
        arsort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}


/**
 * 多维数组转化为一维数组
 * @param 多维数组
 * @return array 一维数组
 */
function array_multi2single($array)
{
    static $result_array = array();
    foreach ($array as $value) {
        if (is_array($value)) {
            array_multi2single($value);
        } else
            $result_array [] = $value;
    }
    return $result_array;
}




/**
 * 友好时间显示
 * @param $time
 * @return bool|string
 */
function friend_date($time)
{
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}


/**
 * 返回状态和信息
 * @param $status
 * @param $info
 * @return array
 */
function arrayRes($status, $info, $url = "")
{
    return array("status" => $status, "info" => $info, "url" => $url);
}
       
/**
 * @param $arr
 * @param $key_name
  * @param $key_name2
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个数组
 */
function get_id_val($arr, $key_name,$key_name2)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val[$key_name2];
	}
	return $arr2;
}

/**
 *  自定义函数 判断 用户选择 从下面的列表中选择 可选值列表：不能为空
 * @param type $attr_values
 * @return boolean
 */
function checkAttrValues($attr_values)
{        
    if((trim($attr_values) == '') && ($_POST['attr_input_type'] == '1'))        
        return false;
    else
        return true;
 }
 
 // 定义一个函数getIP() 客户端IP，
function getIP(){            
    if (getenv("HTTP_CLIENT_IP"))
         $ip = getenv("HTTP_CLIENT_IP");
    else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if(getenv("REMOTE_ADDR"))
         $ip = getenv("REMOTE_ADDR");
    else $ip = "Unknow";
    
    if(preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $ip))          
        return $ip;
    else
        return '';
}
// 服务器端IP
 function serverIP(){   
  return gethostbyname($_SERVER["SERVER_NAME"]);   
 }  
 
 
 /**
  * 自定义函数递归的复制带有多级子目录的目录
  * 递归复制文件夹
  * @param type $src 原目录
  * @param type $dst 复制到的目录
  */                        
//参数说明：            
//自定义函数递归的复制带有多级子目录的目录
function recurse_copy($src, $dst)
{
	$now = time();
	$dir = opendir($src);
	@mkdir($dst);
	while (false !== $file = readdir($dir)) {
		if (($file != '.') && ($file != '..')) {
			if (is_dir($src . '/' . $file)) {
				recurse_copy($src . '/' . $file, $dst . '/' . $file);
			}
			else {
				if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
					if (!is_writeable($dst . DIRECTORY_SEPARATOR . $file)) {
						exit($dst . DIRECTORY_SEPARATOR . $file . '不可写');
					}
					@unlink($dst . DIRECTORY_SEPARATOR . $file);
				}
				if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
					@unlink($dst . DIRECTORY_SEPARATOR . $file);
				}
				$copyrt = copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
				if (!$copyrt) {
					echo 'copy ' . $dst . DIRECTORY_SEPARATOR . $file . ' failed<br>';
				}
			}
		}
	}
	closedir($dir);
}

// 递归删除文件夹
function delFile($dir,$file_type='') {
	if(is_dir($dir)){
		$files = scandir($dir);
		//打开目录 //列出目录中的所有文件并去掉 . 和 ..
		foreach($files as $filename){
			if($filename!='.' && $filename!='..'){
				if(!is_dir($dir.'/'.$filename)){
					if(empty($file_type)){
						unlink($dir.'/'.$filename);
					}else{
						if(is_array($file_type)){
							//正则匹配指定文件
							if(preg_match($file_type[0],$filename)){
								unlink($dir.'/'.$filename);
							}
						}else{
							//指定包含某些字符串的文件
							if(false!=stristr($filename,$file_type)){
								unlink($dir.'/'.$filename);
							}
						}
					}
				}else{
					delFile($dir.'/'.$filename);
					rmdir($dir.'/'.$filename);
				}
			}
		}
	}else{
		if(file_exists($dir)) unlink($dir);
	}
}

 
/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function combineDika() {
	$data = func_get_args();
	$data = current($data);
	$cnt = count($data);
	$result = array();
    $arr1 = array_shift($data);
	foreach($arr1 as $key=>$item) 
	{
		$result[] = array($item);
	}		

	foreach($data as $key=>$item) 
	{                                
		$result = combineArray($result,$item);
	}
	return $result;
}


/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
*/
function combineArray($arr1,$arr2) {		 
	$result = array();
	foreach ($arr1 as $item1) 
	{
		foreach ($arr2 as $item2) 
		{
			$temp = $item1;
			$temp[] = $item2;
			$result[] = $temp;
		}
	}
	return $result;
}
/**
 * 将二维数组以元素的某个值作为键 并归类数组
 * array( array('name'=>'aa','type'=>'pay'), array('name'=>'cc','type'=>'pay') )
 * array('pay'=>array( array('name'=>'aa','type'=>'pay') , array('name'=>'cc','type'=>'pay') ))
 * @param $arr 数组
 * @param $key 分组值的key
 * @return array
 */
function group_same_key($arr,$key){
    $new_arr = array();
    foreach($arr as $k=>$v ){
        $new_arr[$v[$key]][] = $v;
    }
    return $new_arr;
}

/**
 * 获取随机字符串
 * @param int $randLength  长度
 * @param int $addtime  是否加入当前时间戳
 * @param int $includenumber   是否包含数字
 * @return string
 */
function get_rand_str($randLength=6,$addtime=1,$includenumber=0){
    if ($includenumber){
        $chars='abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    }else {
        $chars='abcdefghijklmnopqrstuvwxyz';
    }
    $len=strlen($chars);
    $randStr='';
    for ($i=0;$i<$randLength;$i++){
        $randStr.=$chars[rand(0,$len-1)];
    }
    $tokenvalue=$randStr;
    if ($addtime){
        $tokenvalue=$randStr.time();
    }
    return $tokenvalue;
}

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
	//return array($http_code, $response,$requestinfo);
}

/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array){
    if(!is_array($array))
        return trim($array);
    return array_map('trim_array_element',$array);
}

/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function check_mobile($mobile){
    if(preg_match('/1[34578]\d{9}$/',$mobile))
        return true;
    return false;
}

/**
 * 检查邮箱地址格式
 * @param $email 邮箱地址
 */
function check_email($email){
    if(filter_var($email,FILTER_VALIDATE_EMAIL))
        return true;
    return false;
}


/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
      if(mb_strlen($string,'utf-8')>$length){
          $str = mb_substr($string, $start, $length,'utf-8');
          return $str.'...';
      }else{
          return $string;
      }
}


/**
 * 判断当前访问的用户是  PC端  还是 手机端  返回true 为手机端  false 为PC 端
 * @return boolean
 */
/**
　　* 是否移动端访问访问
　　*
　　* @return bool
　　*/
function isMobile()
{
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    return true;

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
    // 找不到为flase,否则为true
    return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            return true;
    }
        // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
    // 如果只支持wml并且不支持html那一定是移动设备
    // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
            return false;
 } 

//php获取中文字符拼音首字母
function getFirstCharter($str){
      if(empty($str))
      {
            return '';          
      }
      $fchar=ord($str{0});
      if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
      $s1=iconv('UTF-8','gb2312',$str);
      $s2=iconv('gb2312','UTF-8',$s1);
      $s=$s2==$str?$s1:$str;
      $asc=ord($s{0})*256+ord($s{1})-65536;
     if($asc>=-20319&&$asc<=-20284) return 'A';
     if($asc>=-20283&&$asc<=-19776) return 'B';
     if($asc>=-19775&&$asc<=-19219) return 'C';
     if($asc>=-19218&&$asc<=-18711) return 'D';
     if($asc>=-18710&&$asc<=-18527) return 'E';
     if($asc>=-18526&&$asc<=-18240) return 'F';
     if($asc>=-18239&&$asc<=-17923) return 'G';
     if($asc>=-17922&&$asc<=-17418) return 'H';
     if($asc>=-17417&&$asc<=-16475) return 'J';
     if($asc>=-16474&&$asc<=-16213) return 'K';
     if($asc>=-16212&&$asc<=-15641) return 'L';
     if($asc>=-15640&&$asc<=-15166) return 'M';
     if($asc>=-15165&&$asc<=-14923) return 'N';
     if($asc>=-14922&&$asc<=-14915) return 'O';
     if($asc>=-14914&&$asc<=-14631) return 'P';
     if($asc>=-14630&&$asc<=-14150) return 'Q';
     if($asc>=-14149&&$asc<=-14091) return 'R';
     if($asc>=-14090&&$asc<=-13319) return 'S';
     if($asc>=-13318&&$asc<=-12839) return 'T';
     if($asc>=-12838&&$asc<=-12557) return 'W';
     if($asc>=-12556&&$asc<=-11848) return 'X';
     if($asc>=-11847&&$asc<=-11056) return 'Y';
     if($asc>=-11055&&$asc<=-10247) return 'Z';
     return null;
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
    $pay_date['money'] = sprintf('%.2f',$orderAmt);
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
            <orderAmt>'.$pay_date['money'].'</orderAmt>
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

//去除省市名称与APP上的差异
function delDiffer($id='')
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

function encrypt($str){
    return md5(C("AUTH_CODE").$str);
}

/**
 * 骏媒短信接口
 * $mobile  手机号
 * $content  内容
 */
function send_sms_code($mobile,$content){
    Vendor('sms.HttpClient','','.class.php');
    $pageContents = HttpClient::quickPost('http://211.147.242.161:8888/sms.aspx', array(
        'action' => 'send',
        'userid' =>'6037',
        'account'=>'粤力租贷',
        'password'=>'ylzd789',
        'mobile'=>$mobile,
        'content'=>$content,
        'sendtime'=>'',
        'extno'=>''
    ));
    $x = new SimpleXmlElement($pageContents);
    if ($x->returnstatus=='Success')
        //return "发送成功";
        return true;
    else
        return false;
        // return $x->message;
}

$level_list = array();
function getLevelCat($id)
{
    global $level_list;
    $CarCate = M('CarCate')->where(['id'=>$id])->find();
    $level_list[$CarCate['level']] = $CarCate;
    if($CarCate['parent_id'] === 0){
        return $level_list;
    }else{
        return getLevelCat($CarCate['parent_id']);
    }    
}

//根据pid 获取子分类
function getCat($parent_id)
{
    if($parent_id === 0){
        $list = M('CarCate')->where(['parent_id'=>$parent_id])->select();
    }else{
        $list = M('CarCate')->where(['parent_id'=>$parent_id])->order('name ASC')->select();
    }
    foreach ($list as $key => $val) {
        $count = M('CarCate')->where(['parent_id'=>$val['id']])->order('name ASC')->select();
        if($count < 1){
            unset($list[$key]);
        }
    }
    return $list;
}

//年月租商品查询筛选条件过滤
function carCatWhere($data='')
{
    if($data['pinpai'] == 'other'){
        // $pinpai = implode(',',C('pinpai'));
        // $data['pinpai'] = ['not in',$pinpai];
        $whereData['cc1.name'] = '其它';
    }else{
        $whereData['cc1.name'] = $data['pinpai'];
    }

    if($data['cart_type'] == 'other'){
        // $cart_type = implode(',',C('cart_type'));
        // $data['cart_type'] = ['not in',$cart_type];
        $whereData['cc2.name'] = '其它';
    }else{
        $whereData['cc2.name'] = $data['cart_type'];
    }

    if(!empty($data['dunwei'])&&$data['dunwei']!='other'){
        $data['dunwei'] = (float)$data['dunwei'];
    }

    if($data['dunwei'] == 'other'){
        // $dunwei = implode(',',C('dunwei'));
        // $data['dunwei'] = ['not in',$dunwei];
        $whereData['cc3.name'] = '其它';
    }else{
        $whereData['cc3.name'] = $data['dunwei'];
    }

    if($data['menjia'] == 'other'){
        // $menjia = implode(',',C('menjia'));
        // $data['menjia'] = ['not in',$menjia];
        $whereData['cc4.name'] = '其它';
    }else{
        $whereData['cc4.name'] = $data['menjia'];
    }

    if($data['mj_height'] == 'other'){
        // $mj_height = implode(',',C('mj_height'));
        // $data['mj_height'] = ['not in',$mj_height];
        $whereData['cc5.name'] = '其它';
    }else{
        $whereData['cc5.name'] = $data['mj_height'];
    } 

    $whereData = array_filter($whereData);
    return $whereData;
}

/**
 * @param $cid     模块化第五层级分类id 
 * @param $bydc    备用电池  1  2
 * @param $is_yt   冷库:0  防爆:1
 * @param tenancy  租期（月）
 * @param yhours   年使用小时数（小时） 
 */
function rent($cid='',$tenancy=12,$yhours=1800,$bydc=0,$is_yt=2)
{
    // $cid = 130;
    if(!$cid){
        return false;
    }

    $level = getLevelCat($cid);
    //车价
    foreach($level as $key => $val){
        $chejia += $val['money'];
    }

    if($is_yt == 0){
        //冷库车 车价增加
        $chejia += 20000 * 1.21 / 36 / 0.95;
    }

    if($is_yt == 1){
        //防爆车 车价增加300000
        $chejia += 300000;
    }

    // $chejia *= 0.95;
    $origin      = $level[1]['type'];       //进口 0  国产 1
    $cart_type   = $level[2]['chexing'];    //车型号
    $dunwei      = $level[3]['name'];       //吨位
    $tire_fee    = $level[3]['tire_fee'];   //轮胎价
    $battery_fee = $level[3]['battery_fee'];//电池价
    $battery     = 1 + $bydc;               //电池数 = 1 + 备用电池数
    $yajin       = 10000;                   //押金

    //年使用时间是600的倍数
    $yhours  = ceil($yhours / 600) * 600;
    //租期转换为季数 季数是2的倍数
    $tenancy = ceil($tenancy / 2);
    $chexing = $cart_type == 'FD/G' ? 1 : 2;//柴油1 电车2

    //折旧年限
    if($origin == 1){
        $x =  8 - $yhours / 600;//国产
    }else{
        $x = 12 - $yhours / 600;//进口
    }

    //M值
    //第三档次 普通档次
    $M = 750 / $tenancy;
    //第一档次
    if($dunwei >= 6 && $dunwei <= 10){
        $M = 1800 / $tenancy;
    }
    //第二档次
    if($dunwei>=4&&$dunwei<=5.5&&$chexing==1){
        $M = 1200 / $tenancy;
    }
    if($dunwei==3.5 && $chexing==2){
        $M = 1200 / $tenancy;
    }
    //季数大于6 则 M = 0
    if($tenancy > 6){
        $M = 0;
    }

    //利息
    if($chexing == 1){
        // $chejia *= 0.95;
        //柴油利息 = (车价-押金+)*0.21/36
        $interest = ($chejia - $yajin) * 0.21 / 36;
    }else{
        //电车利息 = (车价-押金+备用电池数*电池价)*0.21/36
        $interest = ($chejia - $yajin + $bydc * $battery_fee) * 0.21 / 36;
    }

    //折旧
    if($chexing == 1){
        //柴油  = [车价*(1-0.7的x次方)]/(12*x)
        $deprec = ($chejia * ( 1 - pow(0.7,$x))) / (12 * $x); 
    }else{
        //电车  = [1.8*电池价*电池数+(含电池车价-电池价)*(1-0.75的x次方)] / (12*x)
        $deprec = (1.8 * $battery_fee * $battery + ($chejia + $bydc * $battery_fee - $battery_fee) * (1 - pow(0.75,$x))) / (12 * $x);
    }

    //轮胎折旧   = 轮胎价/0.9/2400*年使用小时/12
    $tire_deprec = $tire_fee / 0.9 / 2400 * $yhours / 12;

    switch($cart_type){
        case 'FD/G':
            if($dunwei >= 2 && $dunwei <= 3.5){
                if($origin == 0){
                    //进口
                    $service_fee = ($yhours / 600) * 100 + 300;//服务费   
                    $u1 = 0.5;//公式不定系数1
                    $u2 = 1;  //公式不定系数2
                }else{
                    //国产
                    $service_fee = ($yhours / 600) * 150 + 300;                      
                    $u1 = 0.4;
                    $u2 = 1;
                }
            }elseif($dunwei >= 4 && $dunwei <= 5.5){
                if($origin == 0){
                    $service_fee = ($yhours / 600) * 150 + 500;                      
                    $u1 = 0.5;
                    $u2 = 2;
                }else{
                    $service_fee = ($yhours / 600) * 200 + 500;                      
                    $u1 = 0.4;
                    $u2 = 1.5;
                }
            }elseif($dunwei >= 6 && $dunwei <= 10){
                if($origin == 0){
                    return false;
                }else{
                    $service_fee = ($yhours / 600) * 300 + 500;                      
                    $u1 = 0.4;
                    $u2 = 2;
                }                                          
            }else{
                return false;
            }
        break;
        case 'FB':
            if($dunwei >= 1 && $dunwei <= 3){
                if($origin == 0){
                    $service_fee = ($yhours / 600) * 100 + 200;                      
                    $u1 = 0.25;
                    $u2 = 1;
                }else{
                    return false;
                }
            }elseif($dunwei >= 3.5 && $dunwei <= 5){
                if($origin == 0){
                    $service_fee = ($yhours / 600) * 120 + 300;                      
                    $u1 = 0.25;
                    $u2 = 2;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        break;
        case 'FBR':
            if($dunwei >= 1 && $dunwei <= 2.5){
                if($origin == 0){
                    $service_fee = ($yhours / 600) * 80 + 200;                      
                    $u1 = 0.3;
                    $u2 = 1;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        break;
        case 'FBRE':
            if($dunwei >= 1.4 && $dunwei <= 2){
                if($origin == 0){
                    $service_fee = ($yhours / 600) * 100 + 250;                      
                    $u1 = 0.3;
                    $u2 = 1;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        break;
    }

    //最终服务费
    $service_fee += ($yhours - 1800) / 10 + $tire_deprec;

    //裸租价    = [折旧+$u1×车价/($x×12)-$u2*年使用时间/12+利息+$M-(年使用时间-1800)/10-轮胎折旧]÷0.95+(年使用时间-1800)/10+轮胎折旧
    $car_rent = ($deprec + $u1 * $chejia / ($x * 12) - $u2 * $yhours / 12 + $interest + $M - ($yhours - 1800) / 10 - $tire_deprec);

    // $rent_money = $deprec+$u1*$chejia/(12*$x)-$u2*$yhours/12+$interest+$M-($yhours-1800)/10-$tire_deprec;
    $user_rent = $car_rent / 0.95 + $service_fee;

    $fee = ['car_rent'=>round($car_rent),'user_rent'=>round($user_rent)];

    // echo '年使用时间：',$yhours,'<br>';
    // echo '季数：',$tenancy,'<br>';
    // echo '车价：',$chejia,'<br>';
    // echo '押金：',$yajin,'<br>';
    // echo '产地：',$origin==1?'国产':'进口','<br>';
    // echo '品牌：',$level[1]['name'],'<br>';
    // echo '车型：',$cart_type,'<br>';
    // echo '吨位：',$dunwei,'<br>';
    // echo '门架：',$level[4]['name'],'<br>';
    // echo '门架高度：',$level[5]['name'],'<br>';
    // echo '轮胎价：',$tire_fee,'<br>';
    // echo '车种：',$chexing==1?'柴油':'电车','<br>';
    // if($chexing != 1){
    // echo '电池价：',$battery_fee,'<br>';
    // echo '电池数：',$battery,'<br>';    
    // }
    // echo 'M值：',$M,'<br>';
    // echo 'u1：',$u1,'<br>';
    // echo 'u2：',$u2,'<br>';
    // echo '利息：',$interest,'<br>';
    // echo '折旧年限：',$x,'<br>';
    // echo '折旧：',$deprec,'<br>';
    // echo '轮胎折旧：',$tire_deprec,'<br>';
    // echo '裸租价：',$car_rent,'<br>';
    // echo '客户端：',$user_rent,'<br><br><br>';
    return $fee;
}