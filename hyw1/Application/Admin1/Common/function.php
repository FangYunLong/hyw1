<?php
/**
 *
//
 * Author: 当燃
*: 2015-09-09
 */




/**
 * 管理员操作记录
 * @param $log_url 操作URL
 * @param $log_info 记录信息
 */
function adminLog($log_info){
    $add['log_time'] = time();
    $add['admin_id'] = session('admin_id');
    $add['log_info'] = $log_info;
    $add['log_ip'] = getIP();
    $add['log_url'] = __ACTION__;
    M('admin_log')->add($add);
}

function fileUploadNews($path='',$files='')
{
    foreach($files as $k => $v){
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
 * 使用phpexcel插件导出excel文件
 */
function down_xls3($data){
    // 导入phpexcel文件
    require(PLUGIN_PATH.'phpexcel/PHPExcel.php');

    // 设置行标题
    //创建新的PHPExcel对象
    $objPHPExcel = new PHPExcel();
    $objProps = $objPHPExcel->getProperties();
     
    //设置表头
    $key = ord("A");
    foreach($data['head'] as $v){
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
        $key += 1;
    }
     
    $column = 2;

    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data['row_data'] as $key => $rows){ //行写入
        $span = ord("A");

        foreach($rows as $keyName=>$value){// 列写入
            $j = chr($span);
            // $objPHPExcel->getActiveSheet()->getStyle($j)->applyFromArray(
      //           array(
      //               'alignment' => array(
      //                   'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
      //               )
      //           )
      //       );
            $objActSheet->getColumnDimension($j)->setWidth(30); 
            $objActSheet->setCellValue($j.$column, $value);
            $span++;
        }       
        $column++;
    }

    // 输出浏览器
    //将excel表输出到浏览器
    $type='';
    if($type=="Excel5"){
        header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件
    }else{
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
    }
    header('Content-Disposition: attachment; filename="'.$data['fileName'].'.xls"');    // 告诉浏览器将输出文件的名称
    header("Cache-Control: max-age=0");     // 禁止缓存

    // 保存到浏览器
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    //$objWriter->save('pjp.xls'); // 保存excel文件到本地
    $objWriter->save('php://output');
    exit;
    //die(mb_convert_encoding($xls,'UTF-8','UTF-8'));
}


/**
 * @cc 获取所有控制器名称
 *
 * @param $module
 *
 * @return array|null
 */
function getController($module){
    if(empty($module)) return null;
    $module_path = APP_PATH . '/' . $module . '/Controller/';  //控制器路径
    if(!is_dir($module_path)) return null;
    $module_path .= '/*.class.php';
    $ary_files = glob($module_path);
    foreach ($ary_files as $file) {
        if (is_dir($file)) {
            continue;
        }else {
            $files[] = basename($file, C('DEFAULT_C_LAYER').'.class.php');
        }
    }
    return $files;
}

function getAction($controller){
    $module = 'Admin';
    if(empty($controller)) return null;
    $content = file_get_contents(APP_PATH . '/'.$module.'/Controller/'.$controller.'Controller.class.php');

    preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
    $functions = $matches[1];

    //排除部分方法
    $inherents_functions = array('_before_index','_after_index','_initialize','__construct','getActionName','isAjax','display','show','fetch','buildHtml','assign','__set','get','__get','__isset','__call','error','success','ajaxReturn','redirect','__destruct','_empty');
    foreach ($functions as $func){
        $func = trim($func);
        if(!in_array($func, $inherents_functions)){
          if (strlen($func)>0)   $customer_functions[] = $func;
        }
    }
    return $customer_functions;
}

function getAdminInfo($admin_id){
	return D('admin')->where("admin_id=$admin_id")->find();
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

function tpversion()
{     
    if(!empty($_SESSION['isset_push']))
        return false;    
    $_SESSION['isset_push'] = 1;    
    error_reporting(0);//关闭所有错误报告
    $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
    $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
    $curent_version = file_get_contents($version_txt_path);
    
    $vaules = array(            
            'domain'=>$_SERVER['HTTP_HOST'], 
            'last_domain'=>$_SERVER['HTTP_HOST'], 
            'key_num'=>$curent_version, 
            'install_time'=>INSTALL_DATE, 
            'cpu'=>'0001',
            'mac'=>'0002',
            'serial_number'=>SERIALNUMBER,
            );     
     $url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
     stream_context_set_default(array('http' => array('timeout' => 3)));
     file_get_contents($url);       
}
 
/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin()
{        
    $navigate = include APP_PATH.'Common/Conf/navigate.php';    
    $location = strtolower('Admin/'.CONTROLLER_NAME);
    $arr = array(
        '后台首页'=>'javascript:void();',
        $navigate[$location]['name']=>'javascript:void();',
        $navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
    );
    return $arr;
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
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 根据id获取地区名字
 * @param $regionId id
 */
function getRegionName($regionId){
    $data = M('region')->where(array('id'=>$regionId))->field('name')->find();
    return $data['name'];
}

function getMenuList($act_list){
	//根据角色权限过滤菜单
    $menu_list = getAllMenu();
	if($act_list != 'all'){
        $right = M('system_menu')->where("id in ($act_list)")->cache(true)->getField('right',true);
		foreach ($right as $val){
			$role_right .= $val.',';
		}
		$role_right = explode(',', $role_right);		
		foreach($menu_list as $k=>$mrr){
			foreach ($mrr['sub_menu'] as $j=>$v){
				if(!in_array($v['control'].'Controller@'.$v['act'], $role_right)){
					unset($menu_list[$k]['sub_menu'][$j]);//过滤菜单
				}
			}
		}
	}
	return $menu_list;
}

function getAllMenu(){
	return	array(
			'system' => array('name'=>'系统设置','icon'=>'fa-cog','sub_menu'=>array(
					array('name'=>'网站设置','act'=>'index','control'=>'System'),
					array('name'=>'广告设置','act'=>'ad','control'=>'index'),
					array('name'=>'友情链接','act'=>'linkList','control'=>'Article'),
					/*array('name'=>'自定义导航','act'=>'navigationList','control'=>'System'),*/
					// array('name'=>'区域管理','act'=>'region','control'=>'Tools'),
					// array('name'=>'权限资源列表','act'=>'right_list','control'=>'System'),

			)),
			'access' => array('name' => '权限管理', 'icon'=>'fa-gears', 'sub_menu' => array(
					array('name' => '管理员列表', 'act'=>'index', 'control'=>'Admin'),
					array('name' => '角色管理', 'act'=>'role', 'control'=>'Admin'),
					// array('name' => '供应商管理', 'act'=>'supplier', 'control'=>'Admin'),
					array('name' => '管理员日志', 'act'=>'log', 'control'=>'Admin'),
			)),

            'gudonga' => array('name' => '股东后台', 'icon'=>'fa-user', 'sub_menu' => array(
               // array('name' => '订单管理', 'act'=>'', 'control'=>'Order'),
                array('name' => '密码修改', 'act'=>'forgivepw', 'control'=>'Shareholder'),
                array('name' => '代理商管理', 'act'=>'agent', 'control'=>'Shareholder'),
                array('name' => '加盟商管理', 'act'=>'joinList', 'control'=>'Shareholder'),
            )),

			'member' => array('name'=>'会员管理','icon'=>'fa-user','sub_menu'=>array(
					array('name'=>'会员列表','act'=>'index','control'=>'User'),
					array('name'=>'会员分组','act'=>'levelList','control'=>'User'),
					//array('name'=>'会员等级','act'=>'levelList','control'=>'User'),
					//array('name'=>'充值记录','act'=>'recharge','control'=>'User'),
					//array('name' => '提现申请', 'act'=>'withdrawals', 'control'=>'User'),
					//array('name' => '汇款记录', 'act'=>'remittance', 'control'=>'User'),
					//array('name'=>'会员整合','act'=>'integrate','control'=>'User'),
			)),
			'goods' => array('name' => '叉车管理', 'icon'=>'fa-book', 'sub_menu' => array(
					// array('name' => '分类管理', 'act'=>'categoryList', 'control'=>'Goods'),
					array('name' => '叉车列表', 'act'=>'goodsList', 'control'=>'Goods'),
					array('name' => '特价车列表', 'act'=>'specialCar', 'control'=>'Goods'),
					array('name' => '添加叉车', 'act'=>'addEditGoods', 'control'=>'Goods'),
					//array('name' => '商品类型', 'act'=>'goodsTypeList', 'control'=>'Goods'),
					//array('name' => '商品规格', 'act' =>'specList', 'control' => 'Goods'),
					//array('name' => '商品属性', 'act'=>'goodsAttributeList', 'control'=>'Goods'),
					//array('name' => '品牌列表', 'act'=>'brandList', 'control'=>'Goods'),
					//array('name' => '商品评论','act'=>'index','control'=>'Comment'),
					//array('name' => '商品咨询','act'=>'ask_list','control'=>'Comment'),
			)),
            // 'goods_arg' => array('name' => '叉车参数', 'icon'=>'fa-book', 'sub_menu' => array(
            //         array('name' => '品牌管理', 'act'=>'categoryBrand', 'control'=>'Goods'),
            //         array('name' => '资料管理', 'act'=>'goodsList', 'control'=>'Goods'),
            // )),
			'order' => array('name' => '订单管理', 'icon'=>'fa-money', 'sub_menu' => array(
					array('name' => '年月租订单', 'act'=>'index', 'control'=>'Order'),
					array('name' => '临时租订单', 'act'=>'indexd', 'control'=>'Order'),
					array('name' => '特价车订单', 'act'=>'indext', 'control'=>'Order'),
					// array('name' => '发货单', 'act'=>'delivery_list', 'control'=>'Order'),
					//array('name' => '快递单', 'act'=>'express_list', 'control'=>'Order'),
					// array('name' => '退货单', 'act'=>'return_list', 'control'=>'Order'),
					// array('name' => '添加订单', 'act'=>'add_order', 'control'=>'Order'),
					// array('name' => '订单日志', 'act'=>'order_log', 'control'=>'Order'),
			)),
	/*		'promotion' => array('name' => '促销管理', 'icon'=>'fa-bell', 'sub_menu' => array(
					array('name' => '抢购管理', 'act'=>'flash_sale', 'control'=>'Promotion'),
					array('name' => '团购管理', 'act'=>'group_buy_list', 'control'=>'Promotion'),
					array('name' => '商品促销', 'act'=>'prom_goods_list', 'control'=>'Promotion'),
					array('name' => '订单促销', 'act'=>'prom_order_list', 'control'=>'Promotion'),
					array('name' => '代金券管理','act'=>'index', 'control'=>'Coupon'),
			)),*/
		/*	'Ad' => array('name' => '广告管理', 'icon'=>'fa-flag', 'sub_menu' => array(
					array('name' => '广告列表', 'act'=>'adList', 'control'=>'Ad'),
					array('name' => '广告位置', 'act'=>'positionList', 'control'=>'Ad'),
			)),*/
            'xiaoxi' => array('name' => '消息管理', 'icon'=>'fa-plug', 'sub_menu' => array(
                array('name' => '消息列表', 'act'=>'msgList', 'control'=>'Msg'),
                array('name' => '意见反馈', 'act'=>'opinion', 'control'=>'Msg'),
            )),
			'content' => array('name' => '文章管理', 'icon'=>'fa-comments', 'sub_menu' => array(
					array('name' => '文章分类', 'act'=>'categoryList', 'control'=>'Article'),
                    array('name' => '文章列表', 'act'=>'articleList', 'control'=>'Article'),
					array('name' => '租赁问答', 'act'=>'help_list', 'control'=>'Article'),
					//array('name' => '公告管理', 'act'=>'notice_list', 'control'=>'Article'),
					array('name' => '下载专区', 'act'=>'topicList', 'control'=>'Topic'),
			)),
		/*	'weixin' => array('name' => '微信管理', 'icon'=>'fa-weixin', 'sub_menu' => array(
					array('name' => '公众号管理', 'act'=>'index', 'control'=>'Wechat'),
					array('name' => '微信菜单管理', 'act'=>'menu', 'control'=>'Wechat'),
					array('name' => '文本回复', 'act'=>'text', 'control'=>'Wechat'),
					array('name' => '图文回复', 'act'=>'img', 'control'=>'Wechat'),
					array('name' => '组合回复', 'act'=>'nes', 'control'=>'Wechat'),
					array('name' => '消息推送', 'act'=>'news', 'control'=>'Wechat'),
			)),*/
			// 'theme' => array('name' => '模板管理', 'icon'=>'fa-adjust', 'sub_menu' => array(
			// 		array('name' => 'PC端模板', 'act'=>'templateList?t=pc', 'control'=>'Template'),
			// 		array('name' => '手机端模板', 'act'=>'templateList?t=mobile', 'control'=>'Template'),
			// )),

			'distribut' => array('name' => '分销管理', 'icon'=>'fa-cubes', 'sub_menu' => array(
					array('name' => '分销列表', 'act'=>'DistributList', 'control'=>'Distribut'),
					array('name' => '提现列表', 'act'=>'extractMoney', 'control'=>'Distribut'),
					// array('name' => '分销关系', 'act'=>'tree', 'control'=>'Distribut'),
					// array('name' => '分销设置', 'act'=>'set', 'control'=>'Distribut'),
					// array('name' => '分成日志', 'act'=>'rebate_log', 'control'=>'Distribut'),
			)),

			/*'tools' => array('name' => '插件工具', 'icon'=>'fa-plug', 'sub_menu' => array(
					array('name' => '插件列表', 'act'=>'index', 'control'=>'Plugin'),
					array('name' => '数据备份', 'act'=>'index', 'control'=>'Tools'),
					array('name' => '数据还原', 'act'=>'restore', 'control'=>'Tools'),
			)),*/


            'siji' => array('name' => '司机求职', 'icon'=>'fa-anchor', 'sub_menu' => array(
                array('name' => '求职列表', 'act'=>'driverList', 'control'=>'Driver'),
            )),

			'count' => array('name' => '统计报表', 'icon'=>'fa-signal', 'sub_menu' => array(
				/*	array('name' => '销售概况', 'act'=>'index', 'control'=>'Report'),*/
				/*	array('name' => '销售排行', 'act'=>'saleTop', 'control'=>'Report'),
					array('name' => '会员排行', 'act'=>'userTop', 'control'=>'Report'),
					array('name' => '销售明细', 'act'=>'saleList', 'control'=>'Report'),*/
					/*array('name' => '会员统计', 'act'=>'user', 'control'=>'Report'),
					array('name' => '财务统计', 'act'=>'finance', 'control'=>'Report'),*/
              /*      array('name' => '订单统计', 'act'=>'', 'control'=>'Report'),
                    array('name' => '租车记录', 'act'=>'', 'control'=>'Report'),
                    array('name' => '车辆记录', 'act'=>'', 'control'=>'Report'),*/
                array('name' => '订单统计', 'act'=>'index', 'control'=>'Report'),
                array('name' => '租车记录统计', 'act'=>'car_rental_record', 'control'=>'Report'),
                array('name' => '车辆统计', 'act'=>'car_stat', 'control'=>'Report'),
			)),
			/*'pickup' => array('name' => '自提点管理', 'icon'=>'fa-anchor', 'sub_menu' => array(
					array('name' => '自提点列表', 'act'=>'index', 'control'=>'Pickup'),
					array('name' => '添加自提点', 'act'=>'add', 'control'=>'Pickup'),
			))*/
	);
}


function respose($res){
	exit(json_encode($res));
}

/**
 * 文件上传
 */
function fileUpload($path){
    $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728 ;// 设置附件上传大小
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    // $upload->savePath  =      './Public/Uploads/'; // 设置附件上传目录
    $upload->rootPath  =      'http://hyw.web66.cn:8092/Public/Uploads/'.$path; // 设置附件上传目录
    // 上传文件
    $info   =   $upload->upload();
    if(!$info) {
        // 上传错误提示错误信息
        $this->error($upload->getError());
    }else{
        // 上传成功
        //$this->success('上传成功！');
        return array('status'=>1);
    }
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

    return $res;
}

function Jpush($user_id='',$extras='',$content='',$content2='',$true=''){
    
    if(!$user_id){
        return false;
    }

    $Users = M('Users')->find($user_id);

    if(!$Users){
        return false;
    }

    //增加我的消息
    addMsg($user_id,$content2);

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

    pushLogs($res);

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
        }else{
            $res = "\r\n".date('Y-m-d H:i:s').$result;
        } 
    }else{      
        //接口调用失败或无响应  
        $res = "\r\n".date('Y-m-d H:i:s').' No response ';      
    }  
    fwrite($fp, $res);
    fclose($fp);
}  

/******************极光推送结束***********************/