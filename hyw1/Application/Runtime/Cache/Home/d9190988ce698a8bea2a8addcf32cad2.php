<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo ($config["store_name"]); ?></title>
<meta name="keywords" content="<?php echo ($config["store_keyword"]); ?>"/>
<meta name="description" content="<?php echo ($config["store_desc"]); ?>"/>
<script type="text/javascript" src="/Public/hyw/js/pptBox.js"></script>

</head>


<body>
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
<script type="text/javascript">  
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
 <div class="banner">
  <div class="fouse">
    <div id="xxx"  >
     <script>
     var box =new PPTBox();
     box.width = 854; //宽度
     box.height = 469;//高度
     box.autoplayer = 3;//自动播放间隔时间
     // box.add({"url":"图片地址","title":"悬浮标题","href":"链接地址"})
     <?php foreach($banner as $k => $v){ ?>
        box.add({"url":"<?php echo $v['ad_code'];?>","title":"<?php echo $v['ad_name']; ?>","href":"<?php echo $v['ad_link']; ?>"})
     <?php }?>
     // box.add({"url":"{$banner[1]['ad_code']}","href":"#","title":"悬浮提示标题3"}) 
     // box.add({"url":"{$banner[2]['ad_code']}","href":"#","title":"悬浮提示标题4"})  
     box.show();
    </script>
</div>
  </div>
  <div class="htub">
   <div class="zutub1">
     <a href="<?php echo U('Goods/goodsList');?>">年月租</a>       
     <span>1-36个月任意租</span>
   </div>
   <div class="zutub2">
     <a href="<?php echo U('Goods/goodsTemp');?>">临时租</a>       
     <span>连人带车，随叫随到</span>
   </div>
   <div class="zutub3">
     <a href="<?php echo U('Driver/index');?>">招司机</a>       
     <span>求职招聘，快速免费</span>
   </div>
   <div class="zutub4">
     <a href="<?php echo U('Special/index');?>">特价车</a>       
     <span>专业评估，价格劲爆</span>
   </div>
   <div class="clear"></div>
  </div>
  <div class="clear"></div>
 </div>  
 </div>

 <div class="hCent">
   <div class="hsousuo">
  <div class="guanjic">
    <form id="form1" name="form1" method="post" action="<?php echo U('Goods/goodsSearch');?>">
    <div class="keyword">
      <div class="keyword2"><input name="keyword" type="text"  class="kye" value="请输入关键字" onfocus="if(this.value=='请输入关键字'){this.value='';this.style.color='#444'}" onblur="if(this.value==''){this.value='请输入关键字';this.style.color='#999'}"/></div>
      <div class="sobtn"><input value='' type="submit" class="sobtn2"/></div>
      <div class="clear"></div>
    </div>   
    </form>
     <div class="kwez">热门关键字：杭州、合力、台励福，杭州用HC，合力为HELI等</div>
  </div>
  <div class="tel2"><img src="/Public/hyw/images/tel2.gif" width="261" height="86" /></div>
  <div class="clear"></div>
  </div>
 <div class="haicha">
   <div class="htltle1"><img src="/Public/hyw/images/h-titl1.png" width="1187" height="63" /></div>
   <div class="haichac">
     <div class="haic">
       <div class="htij">
          <div class="hbit1">叉车品牌</div>
          <div class="htiaoj2 where1" value='pinpai'>
            <a href="javascript:void(0)" value=''>不限</a>
            <a href="javascript:void(0)" value='三菱力至优'>三菱力至优</a>
            <a href="javascript:void(0)" value='丰田'>丰田</a> 
            <a href="javascript:void(0)" value='林德'>林德</a> 
            <a href="javascript:void(0)" value='合力'>合力</a> 
            <a href="javascript:void(0)" value='杭叉'>杭叉</a> 
            <a href="javascript:void(0)" value='other'>其它</a>
          </div>
       </div>
       <div class="htij">
          <div class="hbit1">车 型</div>
          <div class="htiaoj2 where1" value='cart_type'>
            <a href="javascript:void(0)" value=''>不限</a> 
            <a href="javascript:void(0)" value='柴油叉车'>柴油叉车</a>
            <a href="javascript:void(0)" value='液化汽油叉车'>液化汽油叉车</a>
            <br>
            <a href="javascript:void(0)" value='站驾前移式电车'>站驾前移式电车</a>
            <a href="javascript:void(0)" value='平衡重式电车'>平衡重式电车</a> 
            <!-- <a href="javascript:void(0)" value='前移式电动叉车'>前移式电动叉车</a> -->
            <br>
            <a href="javascript:void(0)" value='座驾前移式电车'>座驾前移式电车</a>
            <a href="javascript:void(0)" value='other'>其它</a>
          </div>
       </div>

       <div class="htij">
        <div class="hbit1">叉车吨位（kg）</div>
        <div class="htiaoj2" value='dunwei' > 
            <a href="javascript:void(0)" value=''>不限</a>&nbsp;
<!--             <?php if(is_array($dunwei)): foreach($dunwei as $key=>$dunwei): ?><a href="javascript:void(0)" value='<?php echo ($dunwei); ?>'>1000</a><?php endforeach; endif; ?> -->
            <a href="javascript:void(0)" value='1'>1000</a> 
            <!-- <a href="javascript:void(0)" value='1.3'>1300</a>  -->
            <a href="javascript:void(0)" value='1.4'>1400</a> 
            <a href="javascript:void(0)" value='1.5'>1500</a> 
            <a href="javascript:void(0)" value='1.6'>1600</a> 
            <!-- <a href="javascript:void(0)" value='1.8'>1800</a>  -->
            <a href="javascript:void(0)" value='2'>2000</a> 
            <a href="javascript:void(0)" value='2.5'>2500</a> 
            <a href="javascript:void(0)" value='3'>3000</a> 
            <a href="javascript:void(0)" value='3.5'>3500</a> 
            <a href="javascript:void(0)" value='4'>4000</a> 
            <a href="javascript:void(0)" value='4.5'>4500</a> 
            <a href="javascript:void(0)" value='5'>5000</a> 
            <a href="javascript:void(0)" value='5.5'>5500</a> 
            <a href="javascript:void(0)" value='6'>6000</a> 
            <a href="javascript:void(0)" value='7'>7000</a> 
            <a href="javascript:void(0)" value='10'>10000</a>  
            <a href="javascript:void(0)" value='other'>其它</a>
        </div>
       </div>
       <div class="htij">
        <div class="hbit1">门架类型</div>
        <div class="htiaoj2" value='menjia' > 
            <a href="javascript:void(0)" value=''>不限</a>
            <a href="javascript:void(0)" value='二节门架'>二节门架</a> 
            <a href="javascript:void(0)" value='自由提升门架'>自由提升门架</a>
            <!-- <a href="javascript:void(0)" value='other'>其它</a> -->
        </div>
       </div>
       <div class="htij">
        <div class="hbit1">门架提升高度（mm）</div>
        <div class="htiaoj2" value='mj_height' >
            <a href="javascript:void(0)" value=''>不限</a>&nbsp;
            <!-- <a href="javascript:void(0)" value='2500'>2500</a>  -->
            <!-- <a href="javascript:void(0)" value='2700'>2700</a>  -->
            <!-- <a href="javascript:void(0)" value='3300'>3300</a>  -->
            <a href="javascript:void(0)" value='3000'>3000</a> 
            <a href="javascript:void(0)" value='3500'>3500</a> 
            <!-- <a href="javascript:void(0)" value='3700'>3700</a>  -->
            <a href="javascript:void(0)" value='4000'>4000</a>  
            <!-- <a href="javascript:void(0)" value='4300'>4300</a>  -->
            <a href="javascript:void(0)" value='4500'>4500</a> 
            <a href="javascript:void(0)" value='4700'>4700</a> 
            <a href="javascript:void(0)" value='5000'>5000</a> 
            <a href="javascript:void(0)" value='5500'>5500</a> 
            <a href="javascript:void(0)" value='6000'>6000</a> 
            <a href="javascript:void(0)" value='6500'>6500</a> 
            <a href="javascript:void(0)" value='7000'>7000</a> 
            <a href="javascript:void(0)" value='7500'>7500</a> 
            <a href="javascript:void(0)" value='8000'>8000</a> 
            <a href="javascript:void(0)" value='8500'>8500</a> 
            <a href="javascript:void(0)" value='9000'>9000</a> 
            <a href="javascript:void(0)" value='9500'>9500</a> 
            <a href="javascript:void(0)" value='10000'>10000</a> 
            <a href="javascript:void(0)" value='10000'>11200</a> 
            <a href="javascript:void(0)" value='other'>其它</a>
        </div>
       </div>
       <div class="htij">
        <div class="hbit1">备用电池</div>
        <div class="htiaoj2" value='bydc' >
            <a href="javascript:void(0)" value='0'>无</a> 
            <a href="javascript:void(0)" value='1'>1组 </a>
            <a href="javascript:void(0)" value='2'>2组</a>
            <!-- <a href="javascript:void(0)" value='other'>其他</a> -->
        </div>
       </div> 
       <div class="htij">
        <div class="hbit1">属具</div>
        <div class="htiaoj2" value='shuju' >
            <a href="javascript:void(0)" value=''>无</a> 
            <a href="javascript:void(0)" value='侧移器'>侧移器</a> 
            <a href="javascript:void(0)" value='旋转器'>旋转器</a> 
            <a href="javascript:void(0)" value='纸夹'>纸夹</a> 
            <a href="javascript:void(0)" value='其他'>其它</a>
        </div>
       </div>
       <div class="htij">
        <div class="hbit1">更多要求</div>
        <div class="htiaoj2" value='is_yt' >
            <a href="javascript:void(0)" value='2'>普通</a> 
            <a href="javascript:void(0)" value='0'>冷库</a>
            <a href="javascript:void(0)" value='1'>防爆</a> 
        </div>
       </div>
     </div>
     <div class="haiche">
      <ul class="hailist">
      </ul>
     </div>
     <div class="clear"></div>
   </div>
 </div>

 <div class="hxiaz">
   <div class="xiaz1">
     <div class="xiatit">叉车租赁</div>
     <ul class="xiazwe">
       <li><a href="<?php echo U('News/download');?>">客户交易细则</a></li>
       <li><a href="<?php echo U('News/download');?>">车主交易细则</a></li>
       <li><a href="<?php echo U('News/download');?>">临时租交易细则</a></li>
       <li> <a href="<?php echo U('News/download');?>">运输协议</a></li>
       <li><a href="<?php echo U('News/download');?>">租车流程</a></li>
     </ul>
   </div>
   <div class="xiaz2">
    <div class="xiatit2">公司资料</div>
    <ul class="xiazwe2">
       <li><a href="<?php echo U('News/download');?>">营业执照</a></li>       
     </ul>
   </div>
   <div class="xiaz3"><a href="<?php echo U('News/download');?>"><img src="/Public/hyw/images/btn1.png" width="127" height="36" /></a></div>
   <div class="clear"></div>
 </div>
 <div class="haicha">
   <div class="htltle1"><img src="/Public/hyw/images/h-titl2.gif" width="1187" height="63" /></div>
    <script> 
	<!-- 
	/*tab切换脚本*/ 
	function setTab(name,cursel,n){ 
	/*参数意义:name为标签id标识,即不带数字的,cursel为标签数字,n为一共有几个标签*/
		for(i=1;i<=n;i++){ 
			var menu=document.getElementById(name+i); /*循环取得所有标签*/
			var con=document.getElementById("tab_"+name+"_"+i); /*循环取得所有的内容块*/
			menu.className=i==cursel?"current":""; /*判断自身的数字是否等于current*/
			con.style.display=i==cursel?"block":"none"; /*如果是则显示出当前标签所应的内容块,否则隐藏*/
		} 
	} 
	//--> 
	</script>
   <div class="hpinp">
   <a id="one1" onmouseover="setTab('one',1,6)" class="current" >三菱力至优</a> 
   <a id="one2" onmouseover="setTab('one',2,6)">丰田</a> 
   <a id="one3" onmouseover="setTab('one',3,6)">林德</a> 
   <a id="one4" onmouseover="setTab('one',4,6)">合力</a> 
   <a id="one5" onmouseover="setTab('one',5,6)">杭叉</a> 
   <a id="one6" onmouseover="setTab('one',6,6)">其它</a> 
   <!-- <a href="#" id="one7" onmouseover="setTab('one',7,7)">其它</a> -->
   </div>
   <div class="zzsc" id="tab_one_1">
	<ul> 
        <?php if(is_array($goods1)): foreach($goods1 as $key=>$goods1): ?><li class="last2">
        	<a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods1['goods_id']]);?>">
              <img src="<?php echo ($goods1["zm_pic"]); ?>" width='291' height='239'/>
          </a>
          <span><?php echo ($goods1["pinpai"]); echo ($goods1["cart_type"]); ?></span>
        	<a target='_block' href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods1['goods_id']]);?>"><div class="shubt">           
            <p>
            <?php echo ($goods1["pinpai"]); echo ($goods1["cart_type"]); ?></p></div></a>    
        </li><?php endforeach; endif; ?>
	</ul>
</div>
<div class="zzsc" id="tab_one_2" style="display:none;">
	<ul>
    	  <?php if(is_array($goods2)): foreach($goods2 as $key=>$goods2): ?><li>
            <a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods2['goods_id']]);?>"><img src="<?php echo ($goods2["zm_pic"]); ?>" width='291' height='239' /></a>
            <span><?php echo ($goods2["pinpai"]); echo ($goods2["cart_type"]); ?></span>
            <a target='_block' href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods2['goods_id']]);?>"><div class="shubt"><p><?php echo ($goods2["pinpai"]); echo ($goods2["cart_type"]); ?></p></div> </a>
          </li><?php endforeach; endif; ?>
	</ul>
</div>
<div class="zzsc" id="tab_one_3" style="display:none;">
	<ul>
        <?php if(is_array($goods3)): foreach($goods3 as $key=>$goods3): ?><li>
            <a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods3['goods_id']]);?>"><img src="<?php echo ($goods3["zm_pic"]); ?>" width='291' height='239' /></a>
            <span><?php echo ($goods3["pinpai"]); echo ($goods3["cart_type"]); ?></span>
            <a target='_block' href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods3['goods_id']]);?>"><div class="shubt"><p><?php echo ($goods3["pinpai"]); echo ($goods3["cart_type"]); ?></p></div></a> 
          </li><?php endforeach; endif; ?>    	   
	</ul>
</div>
<div class="zzsc" id="tab_one_4" style="display:none;">
	<ul>
        <?php if(is_array($goods4)): foreach($goods4 as $key=>$goods4): ?><li>
            <a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods4['goods_id']]);?>"><img src="<?php echo ($goods4["zm_pic"]); ?>" width='291' height='239' /></a>
            <span><?php echo ($goods4["pinpai"]); echo ($goods4["cart_type"]); ?></span>
            <a target='_block' href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods4['goods_id']]);?>"><div class="shubt"><p><?php echo ($goods4["pinpai"]); echo ($goods4["cart_type"]); ?></p></div> </a>
          </li><?php endforeach; endif; ?>    	
	</ul>
</div>
<div class="zzsc" id="tab_one_5" style="display:none;">
	<ul>
        <?php if(is_array($goods5)): foreach($goods5 as $key=>$goods5): ?><li>
            <a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods5['goods_id']]);?>"><img src="<?php echo ($goods5["zm_pic"]); ?>" width='291' height='239' /></a>
            <span><?php echo ($goods5["pinpai"]); echo ($goods5["cart_type"]); ?></span>
            <a target='_block' href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods5['goods_id']]);?>"><div class="shubt"><p><?php echo ($goods5["pinpai"]); echo ($goods5["cart_type"]); ?></p></div></a> 
          </li><?php endforeach; endif; ?>
	</ul>
</div>
<div class="zzsc" id="tab_one_6" style="display:none;">
	<ul>
        <?php if(is_array($goods7)): foreach($goods7 as $key=>$goods7): ?><li>
            <a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods7['goods_id']]);?>"><img src="<?php echo ($goods7["zm_pic"]); ?>" width='291' height='239' /></a>
            <span><?php echo ($goods7["pinpai"]); echo ($goods7["cart_type"]); ?></span>
            <a target='_block' href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods7['goods_id']]);?>"><div class="shubt"><p><?php echo ($goods7["pinpai"]); echo ($goods7["cart_type"]); ?></p></div> </a>
          </li><?php endforeach; endif; ?>
	</ul>
</div>
<!-- <div class="zzsc" id="tab_one_7" style="display:none;">
  <ul>
        <?php if(is_array($goods7)): foreach($goods7 as $key=>$goods7): ?><li>
            <a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods7['goods_id']]);?>"><img src="<?php echo ($goods7["zm_pic"]); ?>" width='291' height='239' /></a>
            <span><?php echo ($goods7["pinpai"]); echo ($goods7["cart_type"]); ?></span>
            <div class="shubt"><p><a href="<?php echo U('Goods/carDataInfo',['goods_id'=>$goods7['goods_id']]);?>"><?php echo ($goods7["pinpai"]); echo ($goods7["cart_type"]); ?></a></p></div> 
          </li><?php endforeach; endif; ?>
  </ul>
</div> -->
   <script src="js/jquery.min.js"></script>
<script>
$(function(){
	$('.zzsc li').hover(function(){
		$('.shubt',this).stop().animate({
			height:'241px'
		});
	},function(){
		$('.shubt',this).stop().animate({
			height:'0'
		});
	});
});
</script>
   </div>
   
    <div class="haicha">
   <div class="htltle2"><img src="/Public/hyw/images/h-titl3.png" width="1187" height="63" /></div>
   <div class="hnews">
    <div class="hnews1">
     <div class="hnewtu"><a href="<?php echo U('Home/News/articleList/cat_id/5');?>"><img src="/Public/hyw/images/tu6.jpg" width="379" height="213" /></a><span><a href="<?php echo U('Home/News/articleList/cat_id/5');?>">公告动态</a></span></div>
     <ul class="hxin">
      <?php if(is_array($New["dynamic"])): foreach($New["dynamic"] as $key=>$dynamic): ?><li>
          <span><?php echo (date('Y-m-d',$dynamic["add_time"])); ?></span> 
          <a target='_block' href="<?php echo U('News/articleInfo',array('article_id'=>$dynamic['article_id']));?>"><?php echo ($dynamic["title"]); ?></a>
        </li><?php endforeach; endif; ?>
     </ul>
    </div>
    <div class="hnews1">
     <div class="hnewtu"><a href="<?php echo U('Home/News/articleList/cat_id/90');?>"><img src="/Public/hyw/images/tu5.jpg" width="379" height="213" /></a><span><a href="<?php echo U('Home/News/articleList/cat_id/90');?>">行业新闻</a></span></div>
     <ul class="hxin">
      <?php if(is_array($New["company"])): foreach($New["company"] as $key=>$company): ?><li>
          <span><?php echo (date('Y-m-d',$company["add_time"])); ?></span> 
          <a  target='_block' href="<?php echo U('News/articleInfo',array('article_id'=>$company['article_id']));?>"><?php echo ($company["title"]); ?></a>
        </li><?php endforeach; endif; ?>
     </ul>
    </div>
    <div class="hnews2">
     <div class="hnewtu"><a href="<?php echo U('Home/News/articleList/cat_id/89');?>"><img src="/Public/hyw/images/tu7.jpg" width="379" height="213" /></a><span><a href="<?php echo U('Home/News/articleList/cat_id/89');?>">公司新闻</a></span></div>
     <ul class="hxin">
      <?php if(is_array($New["industry"])): foreach($New["industry"] as $key=>$industry): ?><li>
          <span><?php echo (date('Y-m-d',$industry["add_time"])); ?></span> 
          <a  target='_block' href="<?php echo U('News/articleInfo',array('article_id'=>$industry['article_id']));?>"><?php echo ($industry["title"]); ?></a>
        </li><?php endforeach; endif; ?>
     </ul>
    </div>
    <div class="clear"></div>
   </div>
   </div>
   <div class="youqi">
    <div class="htitl3"><a href="#" class="current">友情链接</a> 
    <!-- <a href="link.html">热门城市</a> -->
    </div>
    <div class="ylist">
      <?php if(is_array($link)): foreach($link as $key=>$link): ?><a href="<?php echo ($link["link_url"]); ?>" <?php if($link["target"] == 1): ?>target='_block'<?php endif; ?>><?php echo ($link["link_name"]); ?></a> |<?php endforeach; endif; ?>   
    </div>
   </div>
 </div>
<!--------底部开始-------------->

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
 
<!--------底部结束-------------->

<form id='goodsList' action="" method='post'>
  <input type="hidden" value='' name='pinpai' />
  <input type="hidden" value='' name='cart_type' />
  <input type="hidden" value='' name='dunwei' />
  <input type="hidden" value='' name='menjia' />
  <input type="hidden" value='' name='mj_height' />
  <input type="hidden" value='' name='bydc' />
  <input type="hidden" value='' name='shuju' />
  <input type="hidden" value='' name='is_yt' />
</form>
<div class="scroll" id="scroll" style="display:none;">
		回到顶部
</div>
    <script type="text/javascript">
	$(function(){
		showScroll();
		function showScroll(){
			$(window).scroll( function() { 
				var scrollValue=$(window).scrollTop();
				scrollValue > 100 ? $('div[class=scroll]').fadeIn():$('div[class=scroll]').fadeOut();
			} );	
			$('#scroll').click(function(){
				$("html,body").animate({scrollTop:0},200);	
			});	
		}
	})
	</script>
  <script>
ajaxgoods();
$('.htiaoj2 a').click(function(){
    var wheres = $(this).attr('value');
    $(this).siblings().css({'background-color':'#39abfb'});
    $(this).css({'background-color':'#f6b662'});
    var parents = $(this).parent().attr('value');
    $('input[name="'+parents+'"]').val(wheres);
    ajaxgoods();
});
function ajaxgoods()
{
    $.ajax({ 
        type:'post',
        url: "<?php echo U('Index/ajaxGoods');?>", 
        data: $('#goodsList').serialize(), 
        success: function(data){
          $('.hailist').html('');
          $('.hailist').append(data);
        }
    });
}
</script> 
<script>
  function DrawImage(ImgD,iwidth,iheight){      
    //参数(图片,允许的宽度,允许的高度)      
    var image=new Image();      
    image.src=ImgD.src;      
    if(image.width>0 && image.height>0){      
      if(image.width/image.height>= iwidth/iheight){      
          if(image.width>iwidth){        
              ImgD.width=iwidth;      
              ImgD.height=(image.height*iwidth)/image.width;      
          }else{      
              ImgD.width=image.width;        
              ImgD.height=image.height;      
          }      
      }else{      
          if(image.height>iheight){        
              ImgD.height=iheight;      
              ImgD.width=(image.width*iheight)/image.height;              
          }else{      
              ImgD.width=image.width;        
              ImgD.height=image.height;      
          }      
      }      
    }      
}    
</script>
</body>
</html>