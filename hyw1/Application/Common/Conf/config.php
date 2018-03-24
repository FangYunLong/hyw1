<?php
//  加载常量配置文件
require_once('constant.php');

return array(
    /* 加载公共函数 */
    'LOAD_EXT_FILE' =>'common',
    'AUTH_CODE' => "TPSHOP", //安装完毕之后不要改变，否则所有密码都会出错
    //'URL_CASE_INSENSITIVE' => false, //URL大小写不敏感
    'LOAD_EXT_CONFIG'=>'db,route', // 加载数据库配置文件
    'LOAD_EXT_CONFIG'=>'db', // 加载数据库配置文件
    //'URL_MODEL'=>2, // 如果需要 隐藏 index.php  打开这行"URL_MODEL"注释 同时在apache环境下 开启 伪静态模块，
	//memcache缓存开启
    'DATA_CACHE_TYPE' => 'Memcache',
    'MEMCACHE_HOST'  => 'tcp://127.0.0.1:11211',
    'DATA_CACHE_TIME' => '3600',
    'TMPL_CACHE_ON' => true,//禁止模板编译缓存 
    'HTML_CACHE_ON' => true,//禁止静态缓存
    /*
     * RBAC认证配置信息
     */

    'SESSION_AUTO_START'        => true,
    'USER_AUTH_ON'              => true,
    'USER_AUTH_TYPE'            => 1,         // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'             => 'authId',  // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'            => 'administrator',
    'USER_AUTH_MODEL'           => 'User',    // 默认验证数据表模型
    'AUTH_PWD_ENCODER'          => 'md5',     // 用户认证密码加密方式
    'USER_AUTH_GATEWAY'         => '/Public/login',// 默认认证网关
    'NOT_AUTH_MODULE'           => 'Public',  // 默认无需认证模块
//     'REQUIRE_AUTH_MODULE'       => '',        // 默认需要认证模块
//     'NOT_AUTH_ACTION'           => '',        // 默认无需认证操作
//     'REQUIRE_AUTH_ACTION'       => '',        // 默认需要认证操作
    'GUEST_AUTH_ON'             => false,     // 是否开启游客授权访问
    'GUEST_AUTH_ID'             => 0,         // 游客的用户ID
    'DB_LIKE_FIELDS'            => 'title|remark',
    'RBAC_ROLE_TABLE'           => 'think_role',
    'RBAC_USER_TABLE'           => 'think_role_user',
    'RBAC_ACCESS_TABLE'         => 'think_access',
    'RBAC_NODE_TABLE'           => 'think_node',
    'SHOW_PAGE_TRACE'           =>0,         //显示调试信息
    //'RBAC_ERROR_PAGE'         => '/Public/tp404.html',
    //'ERROR_PAGE'=>'/Index/Index/error_page.html',
    'ERROR_PAGE'=>'/index.php/Home/Tperror/tp404.html',    
    // 表单令牌验证相关的配置参数
    'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true 
    'TAGLIB_LOAD'   => true,
    'APP_AUTOLOAD_PATH'  =>'@.TagLib',
    'TAGLIB_BUILD_IN'  =>  'cx,tpshop', // tpshop 为自定义标签类名称
    'TMPL_TEMPLATE_SUFFIX'  =>  '.html',     // 默认模板文件后缀
    'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置  默认为html  去除默认的 否则很多地址报错

    'ORDER_STATUS' => array(
        0 => '待抢单',
        // 1 => '已取消',
        2 => '已过期',
        3 => '待匹配',                
        4 => '待支付',
        5 => '已完成',
        // 6 => '已作废',
    ),
    'SHIPPING_STATUS' => array(
        0 => '未发货',
        1 => '已发货',
    	2 => '部分发货'	        
    ),
    'PAY_STATUS' => array(
        0 => '未支付',
        1 => '已支付',
    ),
    'SEX' => array(
        0 => '保密',
        1 => '男',
        2 => '女'
    ),
    'COUPON_TYPE' => array(
    	0 => '面额模板',
        1 => '按用户发放',   		
        2 => '注册发放',
        3 => '邀请发放',
    	4 => '线下发放'	
    ),
	'PROM_TYPE' => array(
		0 => '默认',
		1 => '抢购',
		2 => '团购',
		3 => '优惠'			
	),
    // 订单用户端显示状态
    'WAITPAY'=>' AND pay_status = 0 AND order_status = 0 AND pay_code !="cod" ', //订单查询状态 待支付
    'WAITSEND'=>' AND (pay_status=1 OR pay_code="cod") AND shipping_status !=1 AND order_status in(0,1) ', //订单查询状态 待发货
    'WAITRECEIVE'=>' AND shipping_status=1 AND order_status = 1 ', //订单查询状态 待收货    
    'WAITCCOMMENT'=> ' AND order_status=2 ', // 待评价 确认收货     //'FINISHED'=>'  AND order_status=1 ', //订单查询状态 已完成 
    'FINISH'=> ' AND order_status = 4 ', // 已完成
    'CANCEL'=> ' AND order_status = 3 ', // 已取消
    'CANCELLED'=> 'AND order_status = 5 ',//已作废
    
    'ORDER_STATUS_DESC' => array(
        'WAITPAY' => '待支付',
        'WAITSEND'=>'待发货',
        'WAITRECEIVE'=>'待收货',
        'WAITCCOMMENT'=> '待评价',
        'CANCEL'=> '已取消',
        'FINISH'=> '已完成', //
        'CANCELLED'=> '已作废'
    ),
    
    /**
     *  订单用户端显示按钮     
        去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
        取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0 
        确认收货  AND shipping_status=1 AND order_status=0 
        评价      AND order_status=1 
        查看物流  if(!empty(物流单号))   
        退货按钮（联系客服）  所有退换货操作， 都需要人工介入   不支持在线退换货
     */
    
    // 'site_url'=>'http://www.tp-shop.cn', // tpshop 网站域名 已经改写入数据库
    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    //'DEFAULT_MODULE'        =>  'Index',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称    
    
    'APP_SUB_DOMAIN_DEPLOY'   =>    0, // 开启子域名或者IP配置
    'APP_SUB_DOMAIN_RULES'    =>    array( 
         'm.tpshop.com'   => 'Mobile/',  // 手机访问网站
    ),    
        
    'DEFAULT_FILTER'        => 'strip_sql,htmlspecialchars',   // 系统默认的变量过滤机制

    /**
     * coreseek/sphinx全文检索引擎配置
     */
    'SPHINX_HOST'         =>      '127.0.0.1',
    'SPHINX_PORT'         =>      '9312',

    /* 短信发送的参数 */
    'SMS'=>array(
        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        'accountSid'=> '8a216da8560cf8ae01560ddaf84d008d',

        //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        'accountToken'=> 'ef9bfaf3fe864d659c024569a3e6c377',

        //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
        //在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
        'appId'=>'8a216da8560cf8ae01560ddaf8b10093',

        //请求地址
        //沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
        //生产环境（用户应用上线使用）：app.cloopen.com
        'serverIP'=>'sandboxapp.cloopen.com',

        //请求端口，生产环境和沙盒环境一致
        'serverPort'=>'8883',

        //REST版本号，在官网文档REST介绍中获得。
        'softVersion'=>'2013-12-26',
    ),

    /* 自动运行配置 */   
    'CRON_CONFIG_ON' => true, // 是否开启自动运行   
    'CRON_CONFIG' => array(   
        '测试执行定时任务' => array('Api/Long/overCheck', '5', ''), //路径(格式同R)、间隔秒（0为一直运行）、指定一个开始时间   
    ),  

    'level_id'  => array('','用户','车主','代理商','股东'),
    //车类型
    'cart_type' => array('平衡重式柴油叉车','平衡重液化汽油叉车','平衡重式电动叉车','座驾前移式电动叉车','前移式叉车'),
    //吨位
    'dunwei'    => array('1','1.3','1.4','1.5','1.6','1.8','2','2.5','3','3.5','4','4.5','5','5.5','6','7','10'),
    //门架
    'menjia'    => array('二节标准门架','二节全自由提升型门架','三节全自由提升型门架'),
    //品牌
    'pinpai'    => array('三菱力至优','丰田','林德','合力','杭叉'),
    //门架提升高度
    'mj_height' => array(2500,2700,3000,3300,3500,3700,4000,4300,4500,4700,5000,5500,6000,6500,7000,7300,7500,8000,8500,9000,9500,10000,11200),
    //车种
    'chezhong'  => array('柴油','电车'),
    //冷库/防爆
    'is_yt'     => array('冷库','防爆','普通'),
    //状况
    'is_status' => array('一般','良好','优秀'),
    //备用电池  
    'bydc'      => array(1,2),
    //属具
    'shuju'     => array('侧移器','旋转器','纸片'),
    //银行卡
    'bank'      => array('工商银行','光大银行','广发银行','建设银行','交通银行','民生银行','农业银行','平安银行','邮政银行','邮政储蓄银行','招商银行','中国银行'),
    //后台菜单
    'group'     => array('system'=>'系统设置','access'=>'权限管理','member'=>'会员管理','goods'=>'叉车管理','order'=>'订单管理','xiaoxi'=>'消息管理','content'=>'文章管理','distribut'=>'分销管理','gudong'=>'股东后台','count'=>'统计报表','siji'=>'司机求职'),
    //提现状态
    'money_status' => array('全部','未处理','已处理','审核不通过'),    
    //临时租订单状态
    'temp_status'  => array('待抢单','已取消','已完成','已失效'),
    //性别
    'sex'          => array('保密','男','女'),
    //学历
    'xueli'        => array('小学','初中','中专','高中','专科','本科','硕士','博士'),
    //经验
    'jingyan'      => array(1=>'1年',2=>'2年',3=>'3年',4=>'4年',5=>'5年','5+'=>'5年以上','10+'=>'10年以上'),
    //年月租租车订单状态提示语
    'user_order_tips'=> array('正在匹配中','','无人抢单，订单失效','系统匹配中','该订单未完成，去','该订单已完成'),
    //临时租租车订单状态提示语
    'user_temp_tips' => array('','该订单未完成','该订单已取消','该订单已完成','该订单已过时效'),
    //年月租抢单订单状态提示语
    'car_order_tips' => array('','客户筛选中，请耐心等待','','您的条件不满足客户需求','筛选成功，等待客户支付','该订单已完成'),
    //临时租抢单订单状态提示语    
    'car_temp_tips'  => array('','','','订单已完成','订单未完成'),
    //华东地区
    'east_china'   => array(19280=>'山东',10808=>'江苏',14234=>'安徽',12596=>'浙江',17359=>'江西',16068=>'福建',10543=>'上海',47493=>'台湾'),
    //华南地区
    'south_china'  => array(28240=>'广东',30164=>'广西',47494=>'香港',47495=>'澳门',31563=>'海南',25579=>'湖南'),
    //华北地区
    'north_china'  => array(1=>'北京',338=>'天津',636=>'河北',3102=>'山西',21387=>'河南',24022=>'湖北',41877=>'陕西',45753=>'宁夏'),        
    //西南地区
    'southwest'    => array(33007=>'四川',39556=>'云南',37906=>'贵州',41103=>'西藏',31929=>'重庆',43776=>'甘肃',45286=>'青海',46047=>'新疆'),
    //东北地区
    'northeast'    => array(5827=>'辽宁',7531=>'吉林',8558=>'黑龙江',4670=>'内蒙古'),
            
 );