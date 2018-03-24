<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>好运旺租叉车</title>
<link href="/Public/css/base.css" rel="stylesheet" type="text/css" />
<link href="/Public/css/style.css" rel="stylesheet" type="text/css" />
</head>


<body>
<div class="top">
    <!--------头部开始-------------->
    <link href="/Public/hyw/css/base.css" rel="stylesheet" type="text/css" />
<link href="/Public/hyw/css/style.css" rel="stylesheet" type="text/css" />
<!-- <link href="/Public/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<script src="/Public/hyw/js/jquery1.8.3.min.js"></script>
<script src="/Public/hyw/js/script.js"></script>
<script src="/Public/js/vue.js"></script>


<div class="top">
 <div class="toplink">
   <div class="toplink2">
    <div class="tophun ">
     欢迎光临东莞市粤力叉车租赁有限公司官网</div>
    <div class="topdeng">
      <?php  $user = session('user'); if(!$user){ ?>
      <a href="<?php echo U('Login/login');?>" class="huiy1">会员登录</a> 
      <a href="<?php echo U('Login/regist');?>" class="huiy2">免费注册</a>
      <?php }else{ ?>
      <a href="<?php echo U('User/userInfo');?>" style='color:red'>个人中心</a>
      <a href="<?php echo U('Login/logout');?>">退出登录</a>
      <?php } ?>  
      <a  href="javascript:void(0)" onclick="SetHome(this,'http://hyw.web66.cn:8092')">设为首页 |</a>   
      <a href="javascript:void(0)" onclick='AddFavorite("好运旺租叉车",location.href)'>添加收藏 |</a>  
      <a href="/index.php/Home/News/about/cat_id/96.html">联系我们</a>
    </div>
   <div class="clear"></div>
   </div>
 </div>
 <div class="logoTel" style='margin-top:29px;'>
  <div class="logo"><a href="http://hyw.web66.cn:8092/"><img src="/Public/hyw/images/logo.gif" width="409" height="59" /></a></div>
  <div class="tel"><img src="/Public/hyw/images/tel.gif" width="279" height="59" /></div>
  <div class="clear"></div>
 </div>
 <div class="menu"  style='margin-top:25px;'>
   <div class="caid">
    <ul class="menuLink">
     <li><a href="<?php echo U('Index/index');?>" class="current">首页</a></li>
     <li><a href="<?php echo U('Goods/goodsList');?>">叉车租赁</a>
       <ul>
            <li><a href="<?php echo U('Goods/goodsList');?>">年月租</a></li>
            <li><a href="<?php echo U('Goods/goodsTemp');?>">临时租</a></li>
       </ul>
     </li>
     <li><a href="<?php echo U('Special/index');?>">好运商城</a>
        <ul>
            <li><a href="<?php echo U('Special/index');?>">特价车</a></li>
            <!-- <li><a href="<?php echo U('MallShop/Industry');?>">工业品</a></li> -->
        </ul>
     </li>
     <li> <a href="<?php echo U('News/articleList',array('cat_id'=>$art_cat[0]['cat_id']));?>">资讯中心</a>
       <ul>
            <?php if(is_array($art_cat)): foreach($art_cat as $key=>$art_cat): ?><li><a href="<?php echo U('News/articleList',array('cat_id'=>$art_cat['cat_id']));?>"><?php echo ($art_cat["cat_name"]); ?></a></li><?php endforeach; endif; ?>
            <li><a href="<?php echo U('News/news1');?>">租赁问答</a></li>
       </ul>
     </li>
     <li><a href="<?php echo U('service/service');?>">服务网络</a></li>
     <li><a href="<?php echo U('service/join');?>">加盟合作</a></li>
     <li class="last"><a href="<?php echo U('News/about',array('cat_id'=>$about_cat[0]['cat_id']));?>">关于我们</a>
       <ul>
          <?php if(is_array($about_cat)): foreach($about_cat as $key=>$about_cat): ?><li><a href="<?php echo U('News/about',array('cat_id'=>$about_cat['cat_id']));?>"><?php echo ($about_cat["cat_name"]); ?></a></li><?php endforeach; endif; ?>
          <li><a href="<?php echo U('News/download');?>">下载专区</a></li>
       </ul>
     </li> 
    </ul>
   </div>
 </div>
<script type="text/javascript">  
// var vue_msge = new Vue({
//   el:'.vue_msg',
//   data:{
//     vue_msg:[]
//   },
//   created:function(){
//         var url="<?php echo U('Home/User/vue_data');?>";
//         $.getJSON(url,function(items){
//           console.log( typeof items );
//             this.vue_msg = items;
//         }.bind(this));
//   }         
// })

function addFavorite2() {
  var url = window.location;
  var title = document.title;
  var ua = navigator.userAgent.toLowerCase();
  if (ua.indexOf("360se") > -1) {
  alert("由于360浏览器功能限制，请按 Ctrl+D 手动收藏！");
  }
  else if (ua.indexOf("msie 8") > -1) {
  window.external.AddToFavoritesBar(url, title); //IE8
  }
  else if (document.all) {
  try{
  window.external.addFavorite(url, title);
  }catch(e){
  alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
  }
  }
  else if (window.sidebar) {
  window.sidebar.addPanel(title, url, "");
  }
  else {
  alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
  }
}

function addfavorite()
{
  if (document.all)
  {
   window.external.addFavorite('http://www.jb51.net','脚本之家');
  }
  else if (window.sidebar)
  {
   window.sidebar.addPanel('脚本之家', 'http://www.jb51.net', "");
  }
} 

function flag(){
 var userAgentInfo = navigator.userAgent;
 var Agents = ["Android", "iPhone",
             "SymbianOS", "Windows Phone",
             "iPad", "iPod"];
 var flag = true;
 for (var v = 0; v < Agents.length; v++) {
     if (userAgentInfo.indexOf(Agents[v]) > 0) {
         flag = false;
         break;
     }
 }
 return flag;
}

//判断是否是微信端
function isWeiXin(){ 
  var ua = navigator.userAgent.toLowerCase(); 
  if(ua.indexOf('micromessenger') != -1) { 
     return true; 
  } else { 
     return false; 
  } 
}

if(flag()==false){
  $('body').css({'width':'1200px'});
}

if(isWeiXin()){
  $('body').css({'width':'1200px'});
}
</script>
<script type="text/javascript"> 
// 设置为主页 
function SetHome(obj,vrl){ 
try{ 
obj.style.behavior='url(#default#homepage)';obj.setHomePage(vrl); 
} 
catch(e){ 
if(window.netscape) { 
try { 
netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); 
} 
catch (e) { 
alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。"); 
} 
var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch); 
prefs.setCharPref('browser.startup.homepage',vrl); 
}else{ 
alert("您的浏览器不支持，请按照下面步骤操作：\n 1.打开浏览器设置。\n 2.点击设置网页。\n 3.输入："+vrl+"点击确定。"); 
} 
} 
} 

</script>
<script  type="text/javascript">
//设为首页
function SetHome1(obj,url){
    try{
        obj.style.behavior='url(#default#homepage)';
        obj.setHomePage(url);
    }catch(e){
        if(window.netscape){
            try{
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
            }catch(e){
                alert("抱歉，此操作被浏览器拒绝！\n\n请在浏览器地址栏输入“about:config”并回车然后将[signed.applets.codebase_principal_support]设置为'true'");
            }
        }else{
            alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将【"+url+"】设置为首页。");
        }
    }
}
//收藏本站
function AddFavorite(title, url) {
    try {
        window.external.addFavorite(url, title);
    }
    catch (e) {
        try {
            window.sidebar.addPanel(title, url, "");
        }
        catch (e) {
            alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
        }
    }
}
//保存到桌面
function toDesktop(sUrl,sName){
try {
    var WshShell = new ActiveXObject("WScript.Shell");
    var oUrlLink =          WshShell.CreateShortcut(WshShell.SpecialFolders("Desktop")     + "\\" + sName + ".url");
    oUrlLink.TargetPath = sUrl;
    oUrlLink.Save();
    }  
catch(e)  {  
          alert("当前IE安全级别不允许操作！");  
}
}    
</script>
    <!--------头部结束-------------->
<div class="now">
    <div class="now1">您当前的位置：<a href="<?php echo U('Index/index');?>">首页</a> > 资讯中心</div>
    <div class="now2"><form action="" method="get">
     <div class="souk"><input name="" type="text" class="shur2"  value="请输入关键字  如品牌、吨位、名称、型号" onfocus="if(this.value=='请输入关键字  如品牌、吨位、名称、型号'){this.value='';this.style.color='#444'}" onblur="if(this.value==''){this.value='请输入关键字  如品牌、吨位、名称、型号';this.style.color='#999'}"/></div>
     <div clsas="souk2">
      <input name="" type="button" class="anniu2"/>
     </div>
     <div class="clear"></div>
    </form></div>
    <div class="clear"></div>
   </div>
    <div class="neiBanner"><a href="<?php echo ($ad["ad_link"]); ?>"><img src="<?php echo ($ad["ad_code"]); ?>" width="1198" height="298" /></a></div>
 <!--   <div class="neiBanner"><img src="/Public/images/neibn8.jpg" width="1198" height="296" /></div>-->
    <div class="neiNav">
    <ul class="navlist">
      <?php if(is_array($about_cate)): foreach($about_cate as $key=>$about_cate): ?><li>
          <a href="<?php echo U('News/about',array('cat_id'=>$about_cate['cat_id']));?>" ><?php echo ($about_cate["cat_name"]); ?></a>
        </li><?php endforeach; endif; ?>
      <li>
        <a href="<?php echo U('News/download');?>"class='current'>下载专区</a>
      </li>
    </ul>
   </div>
</div>
<div class="neiCent2">
<div class="xinwen">
    <ul class="xiazlist">
        <?php if(is_array($Topic)): foreach($Topic as $k=>$vo): ?><li><a href="<?php echo U('News/downloaded',array('topic_id'=>$vo['topic_id']));?>"><?php echo ($vo['topic_title']); ?></a><span><a href="<?php echo U('News/downloaded',array('topic_id'=>$vo['topic_id']));?>"><img src="/Public/images/tub5.gif" width="16" height="16" /></a></span><span1><?php echo (date('Y-m-d',$vo['ctime'])); ?></span1></li><?php endforeach; endif; ?>
    </ul>
</div>

<div class="mypage">
<a href="<?php echo U('News/download',array('page'=>$page['page']-1));?>" class="page1">上一页</a> 
<?php if(is_array($pageRows)): foreach($pageRows as $key=>$pageRows): ?><a href="<?php echo U('News/download',array('page'=>$pageRows));?>" style="<?php if($page["page"] == $pageRows): ?>background:#039cf4;color:#fff<?php endif; ?>"><?php echo ($pageRows); ?></a><?php endforeach; endif; ?> 
<a href="<?php echo U('News/download',array('page'=>$page['page']+1));?>" class="page2">下一页</a>
<span>共<?php echo ($page["pages"]); ?>页　 　当前第<?php echo ($page["page"]); ?>页 </span> 
</div>

</div>
<!------底部开始------------>

<link href="/Public/hyw/css/base.css" rel="stylesheet" type="text/css" />
<link href="/Public/hyw/css/style.css" rel="stylesheet" type="text/css" />

<script src="/Public/hyw/js/jquery1.8.3.min.js"></script>
<script src="/Public/hyw/js/script.js"></script>

<div class="foot">
  <div class="foot2">
  <div class="footlogo">
   <div class="logo2" style='margin-left:20px'><img src="/Public/hyw/images/logo2.gif" width="389" height="95" /></div>
   <div class="footliax">
    <p class="tongy">全国统一热线：</p>
    <p> <?php echo ($config["mobile"]); ?>      </p>
    <p>地址：<?php echo ($config["address"]); ?></p>
    </div>
    <div class="erwm">
     <div class="erwm1"><img src="<?php echo ($config["android_img"]); ?>" width="86" height="85" /><span>安卓二维码</span></div>
     <div class="erwm1"><img src="<?php echo ($config["ios_img"]); ?>" width="86" height="85" /><span>苹果二维码</span></div>
     <div class="erwm1"><img src="<?php echo ($config["weixin_img"]); ?>" width="86" height="85" /><span>微信公众号</span></div>
     <div clsas="clear"></div>
    </div>
  </div>
  <div class="footmenu">
   <div class="fmen1">
    <div class="fmen1tit">叉车租赁  </div>
    <ul class="fmenu1">
     <li><a href="<?php echo U('Goods/goodsList');?>">年月租</a></li>
     <li><a href="<?php echo U('Goods/goodsTemp');?>">临时租</a></li>
    </ul>
   </div>
   <div class="fmen1">
    <div class="fmen1tit">好运商城 </div>
    <ul class="fmenu1">
     <li><a href="<?php echo U('Special/index');?>">特价叉车</a></li>
     <!-- <li>其他</li> -->
    </ul>
   </div>
   <div class="fmen1">
      <div class="fmen1tit">最新资讯 </div>
        <ul class="fmenu1">
           <li><a href="<?php echo U('Home/News/articleList/cat_id/102');?>">市场动态</a></li>
           <li><a href="<?php echo U('News/news1');?>">租赁问答</a></li>
        </ul>
      </div>
      <div class="fmen1">
        <div class="fmen1tit">加盟合作 </div>
        <ul class="fmenu1">
          <li><a href="<?php echo U('service/join');?>">加盟合作</a></li>   
        </ul>
      </div>
      <div class="fmen1">
        <div class="fmen1tit">联系我们 </div>
          <ul class="fmenu1">
             <li><a href="<?php echo U('Home/News/about/cat_id/96');?>">联系方式</a></li>
             <li><a href="<?php echo U('Home/News/about/cat_id/95');?>">公司简介</a></li>
             <li><a href="<?php echo U('Home/News/download');?>">下载专区</a></li>
          </ul>
        </div>
   <div class="clear"></div>
  </div>
  <div class="clear"></div>
  </div>
  <div class="copyright">
   <div class="coyr2">      
      Copyright © 2014 好运旺租叉车电商平台 东莞市粤力叉车租赁有限公司 All Rights Reserved<br/>备案号：<?php echo ($config["record_no"]); ?> 网站版权：<?php echo ($config["store_copyright"]); ?> 技术支持：
      <a target='_block' href=" http://www.gzyunhong.com/">云鸿科技</a> 
    </div>
  </div>
  <script>
    $.ajax({ 
        type:'get',
        url: "<?php echo U('Public/search');?>", 
        success: function(data){
          $('.now2').html('');
          $('.now2').append(data);
        }
    });
  </script>
 
<!------底部结束------------>

 
</body>
</html>