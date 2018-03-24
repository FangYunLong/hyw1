<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>管理后台系统</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link href="/Public/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- FontAwesome 4.3.0 -->
 	<link href="/Public/bootstrap/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 --
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="/Public/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
    	folder instead of downloading all of them to reduce the load. -->
    <link href="/Public/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="/Public/plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />   

    <!-- jQuery 2.1.4 -->
    <script src="/Public/plugins/jQuery/jQuery-2.1.4.min.js"></script>
	<script src="/Public/js/global.js"></script>
    <script src="/Public/js/myFormValidate.js"></script>    
    <script src="/Public/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/Public/js/layer/layer-min.js"></script><!-- 弹窗js 参考文档 http://layer.layui.com/-->
    <script src="/Public/js/myAjax.js"></script>
    <script type="text/javascript">
    /**
    * 实时动态强制更改用户录入
    * arg1 inputObject
    **/
    function amount_verify(th){
        var regStrs = [
            ['^0(\\d+)$', '$1'], //禁止录入整数部分两位以上，但首位为0
            ['[^\\d\\.]+$', ''], //禁止录入任何非数字和点
            ['\\.(\\d?)\\.+', '.$1'], //禁止录入两个以上的点
            ['^(\\d+\\.\\d{2}).+', '$1'] //禁止录入小数点后两位以上
        ];
        for(i=0; i<regStrs.length; i++){
            var reg = new RegExp(regStrs[i][0]);
            th.value = th.value.replace(reg, regStrs[i][1]);
        }
    }

    function delfunc(obj){
    	layer.confirm('确认删除？', {
    		  btn: ['确定','取消'] //按钮
    		}, function(){
   				$.ajax({
   					type : 'post',
   					url : $(obj).attr('data-url'),
   					data : {act:'del',del_id:$(obj).attr('data-id')},
   					dataType : 'json',
   					success : function(data){
   						if(data==1){
   							layer.msg('操作成功', {icon: 1});
   							$(obj).parent().parent().remove();
   						}else{
   							layer.msg(data, {icon: 2,time: 2000});
   						}
   						layer.closeAll();
   					}
   				})
    		}, function(index){
    			layer.close(index);
    			return false;// 取消
    		}
    	);
    }
    
    //全选
    function selectAll(name,obj){
    	$('input[name*='+name+']').prop('checked', $(obj).checked);
    }   
    
    function get_help(obj){
        layer.open({
            type: 2,
            title: '帮助手册',
            shadeClose: true,
            shade: 0.3,
            area: ['90%', '90%'],
            content: $(obj).attr('data-url'), 
        });
    }
    
    function delAll(obj,name){
    	var a = [];
    	$('input[name*='+name+']').each(function(i,o){
    		if($(o).is(':checked')){
    			a.push($(o).val());
    		}
    	})
    	if(a.length == 0){
    		layer.alert('请选择删除项', {icon: 2});
    		return;
    	}
    	layer.confirm('确认删除？', {btn: ['确定','取消'] }, function(){
    			$.ajax({
    				type : 'get',
    				url : $(obj).attr('data-url'),
    				data : {act:'del',del_id:a},
    				dataType : 'json',
    				success : function(data){
    					if(data == 1){
    						layer.msg('操作成功', {icon: 1});
    						$('input[name*='+name+']').each(function(i,o){
    							if($(o).is(':checked')){
    								$(o).parent().parent().remove();
    							}
    						})
    					}else{
    						layer.msg(data, {icon: 2,time: 2000});
    					}
    					layer.closeAll();
    				}
    			})
    		}, function(index){
    			layer.close(index);
    			return false;// 取消
    		}
    	);	
    }
    </script>        
  </head>
  <body style="background-color:#ecf0f5;">
 

<script type="text/javascript">
    window.UEDITOR_Admin_URL = "/Public/plugins/Ueditor/";
    var URL_upload = "<?php echo ($URL_upload); ?>";
    var URL_fileUp = "<?php echo ($URL_fileUp); ?>";
    var URL_scrawlUp = "<?php echo ($URL_scrawlUp); ?>";
    var URL_getRemoteImage = "<?php echo ($URL_getRemoteImage); ?>";
    var URL_imageManager = "<?php echo ($URL_imageManager); ?>";
    var URL_imageUp = "<?php echo ($URL_imageUp); ?>";
    var URL_getMovie = "<?php echo ($URL_getMovie); ?>";
    var URL_home = "<?php echo ($URL_home); ?>";    
</script>

<script type="text/javascript" src="/Public/plugins/Ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/Public/plugins/Ueditor/ueditor.all.js"></script>
<link href="/Public/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/Public/plugins/daterangepicker/moment.min.js" type="text/javascript"></script>
<script src="/Public/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>


<!--物流配置 css -start-->
<style>
    ul.group-list {
        width: 96%;min-width: 1000px; margin: auto 5px;list-style: disc outside none;
    }
    ul.group-list li {
        white-space: nowrap;float: left;
        width: 150px; height: 25px;
        padding: 3px 5px;list-style-type: none;
        list-style-position: outside;border: 0px;margin: 0px;
    }
    .edui-popup-content.edui-default{height: auto !important;}
</style>


<div class="wrapper">
    <div class="breadcrumbs" id="breadcrumbs">
	<ol class="breadcrumb">
<!--	<?php if(is_array($navigate_admin)): foreach($navigate_admin as $k=>$v): if($k == '后台首页'): ?><li><a href="<?php echo ($v); ?>"><i class="fa fa-home"></i>&nbsp;&nbsp;<?php echo ($k); ?></a></li>
	    <?php else: ?>    
	        <li><a href="<?php echo ($v); ?>"></a></li><?php endif; endforeach; endif; ?> -->
	</ol>
</div>

    <section class="content">
        <!-- Main content -->
        <div class="container-fluid">
            <div class="pull-right">
                <a href="javascript:history.go(-1)" data-toggle="tooltip" title="" class="btn btn-default" data-original-title="返回"><i class="fa fa-reply"></i></a>
            	</div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-list"></i>叉车详情</h3>
                </div>
                <div class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active" ><a href="#tab_tongyong" data-toggle="tab">叉车信息</a></li>
                        <!-- <li><a href="#tab_tongyong" data-toggle="tab">叉车参数</a></li> -->
                    </ul>
                    <!--表单数据-->
                    <form method="post" id="addEditGoodsForm" action="<?php echo U('Goods/addEditGoods');?>" enctype="multipart/form-data">
                    
                        <!--通用信息-->
                    <div class="tab-content div1">                 	  
                        <div class="tab-pane active" id="tab_tongyong">
                           
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <td>叉车名称:</td>
                                    <td>
                                        <input type="text" value="<?php echo ($goodsInfo["goods_name"]); ?>" name="goods_name" class="form-control" style="width:250px;"/>
                                        <span id="err_goods_name" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>叉车品牌:</td>
                                    <td>&nbsp;&nbsp;&nbsp;
                                        <select name="pinpai" id="pinpai" onchange="getCat($(this).val(),1)"  class="form-control level1" style="width:250px;margin-left:-15px;">
                                            <option value="0">请选择品牌</option>
                                            <?php if(is_array($pinpai)): foreach($pinpai as $key=>$v): ?><option value="<?php echo ($v[id]); ?>" <?php if($v[id] == $LevelCat[1][id]): ?>selected<?php endif; ?>><?php echo ($v[name]); ?>(<?php echo ($v[money]); ?>)</option><?php endforeach; endif; ?>
                                        </select>
                                        <span id="err_pinpai" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>车辆类型:</td>
                                    <td>&nbsp;&nbsp;&nbsp;
                                        <select name="cart_type" id="cart_type" onchange="getCat($(this).val(),2)" class="form-control level2" style="width:250px;margin-left:-15px;">
                                            <option value="0">请选择车类型</option>
                                            <?php if(is_array($cart_type)): foreach($cart_type as $key=>$v): ?><option value="<?php echo ($v['id']); ?>" <?php if($v[id] == $LevelCat[2][id]): ?>selected<?php endif; ?>><?php echo ($v[name]); ?>(<?php echo ($v[money]); ?>)</option><?php endforeach; endif; ?>
                                        </select>
                                        <span id="err_cart_type" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>                                
                                <tr>
                                    <td>吨位(kg):</td>
                                    <td>&nbsp;&nbsp;&nbsp;
                                        <select name="dunwei" id="dunwei" onchange="getCat($(this).val(),3)" class="form-control level3" style="width:250px;margin-left:-15px;">
                                            <option value="0">请选择吨位</option>
                                            <?php if(is_array($dunwei)): foreach($dunwei as $key=>$v): ?><option value="<?php echo ($v[id]); ?>" <?php if($v[id] == $LevelCat[3][id]): ?>selected<?php endif; ?>><?php echo ($v[name]*1000); ?>(<?php echo ($v[money]); ?>)</option><?php endforeach; endif; ?>
                                        </select>
                                        <span id="err_dunwei" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>门架类型:</td>
                                    <td>&nbsp;&nbsp;&nbsp;
                                        <select name="menjia" id="menjia" onchange="getCat($(this).val(),4)"  class="form-control level4" style="width:250px;margin-left:-15px;">
                                            <option value="0">请选择门架</option>
                                            <?php if(is_array($menjia)): foreach($menjia as $key=>$v): ?><option value="<?php echo ($v[id]); ?>" <?php if($v[id] == $LevelCat[4][id]): ?>selected<?php endif; ?>><?php echo ($v[name]); ?>(<?php echo ($v[money]); ?>)</option><?php endforeach; endif; ?>
                                        </select>
                                        <span id="err_menjia" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>门架提升高度:</td>
                                    <td>&nbsp;&nbsp;&nbsp;
                                        <select name="mj_height" id="mj_height"  class="form-control level5" style="width:250px;margin-left:-15px;">
                                            <option value="0">请选择门架提升高度</option>
                                            <?php if(is_array($mj_height)): foreach($mj_height as $key=>$v): ?><option value="<?php echo ($v[id]); ?>" <?php if($v[id] == $LevelCat[5][id]): ?>selected<?php endif; ?>><?php echo ($v[name]); ?>(<?php echo ($v[money]); ?>)</option><?php endforeach; endif; ?>
                                        </select>
                                        <span id="err_mj_heigh" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>车龄:</td>
                                    <td>
                                        <input type="text" value="<?php echo ($goodsInfo["cart_age"]); ?>" name="cart_age" class="form-control" style="width:550px;"/>
                                        <span id="err_cart_age" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>货期:</td>
                                    <td>
                                        <input type="text" value="<?php echo ($goodsInfo["huoqi"]); ?>" name="huoqi" class="form-control" style="width:550px;"/>
                                        <span id="err_huoqi" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>年份:</td>
                                    <td>
                                        <input type="text" value="<?php echo ($goodsInfo["factorytime"]); ?>" name="factorytime" class="form-control" style="width:550px;"/>
                                        <span id="err_huoqi" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>                                
                                <tr>
                                    <td>已使用小时:</td>
                                    <td>
                                        <input type="text" value="<?php echo ($goodsInfo["use_hours"]); ?>" name="use_hours" class="form-control" style="width:550px;"/>
                                        <span id="err_use_hours" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>电池使用时间:</td>
                                    <td>
                                        <input type="text" value="<?php echo ($goodsInfo["dcsj"]); ?>" name="dcsj" class="form-control" style="width:550px;"/>
                                        <span id="err_dcsj" style="color:#F00; display:none;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>差异性描述:</td>
                                    <td>
	                                    <textarea rows="3" cols="80" name="description"><?php echo ($goodsInfo["description"]); ?></textarea>
                                        <span id="err_description" style="color:#F00; display:none;"></span>
                                    </td>                                                                       
                                </tr>

                                <tr>
                                    <td>正面图片：</td>
                                    <td>
                                        <div class="col-sm-3">
                                            <img src="<?php echo ($goodsInfo["zm_pic"]); ?>" width="200">
                                            <input type="hidden" class="form-control" style="width:350px;margin-left:-15px;" name="zm_pic" id="zm_pic" value="<?php echo ($goodsInfo["zm_pic"]); ?>" >
                                            <input type="file" placeholder="上传图片" name="zm_pic" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>侧面图片：</td>
                                    <td>
                                        <div class="col-sm-3">
                                            <img src="<?php echo ($goodsInfo["cm_pic"]); ?>" width="200">
                                            <input type="hidden" class="form-control" style="width:350px;margin-left:-15px;" name="cm_pic" id="cm_pic" value="<?php echo ($goodsInfo["cm_pic"]); ?>" >
                                            <input type="file" placeholder="上传图片" name="cm_pic" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>操作台图片：</td>
                                    <td>
                                        <div class="col-sm-3">
                                            <img src="<?php echo ($goodsInfo["czt_pic"]); ?>" width="200">
                                            <input type="hidden" class="form-control" style="width:350px;margin-left:-15px;" name="czt_pic" id="czt_pic" value="<?php echo ($goodsInfo["czt_pic"]); ?>" >
                                            <input type="file" placeholder="上传图片" name="czt_pic" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>内部图片：</td>
                                    <td>
                                        <div class="col-sm-3">
                                            <img src="<?php echo ($goodsInfo["nb_pic"]); ?>" width="200">
                                            <input type="hidden" class="form-control" style="width:350px;margin-left:-15px;" name="nb_pic" id="nb_pic" value="<?php echo ($goodsInfo["nb_pic"]); ?>" >
                                            <input type="file" placeholder="上传图片" name="nb_pic" />
                                        </div>
                                    </td>
                                </tr>
                                </tbody>                                
                                </table>
                            <div class="tab-content div2">
                            <table class="table table-bordered">
                            <tr>
                                <td width='130'>
                                    叉车描述
                                </td>
                                <td>
                                    <div class="form-group">
                                    <div class="col-sm-8">
                                        <textarea class="span12 ckeditor" id="goods_describe" name="goods_describe" title="">
                                            <?php echo ($goodsInfo["goods_describe"]); ?>
                                        </textarea>
                                    </div>
                                    </div>
                                </td>
                            </tr>                             
                            </table>                        
                            </div>
                        </div>
                    </div>              
                    <div class="pull-right">
                        <input type="hidden" name="goods_id" value="<?php echo ($goodsInfo["goods_id"]); ?>">
                        <input type="submit" value="保存"  class="btn btn-primary" data-toggle="tooltip" data-original-title="保存">
                    </div>
			    </form><!--表单数据-->
                </div>
            </div>
        </div>    <!-- /.content -->
    </section>
</div>
<script type="text/javascript">

function getCat(parent_id,Lv)
{
    Lv++;
    setNull(Lv);
    console.log(666);
    console.log(parent_id);
    if(parent_id == 0){
        console.log(11221);
        return true;
    }
    var obj = '.level'+Lv;
    var str;
    $.ajax({
        type:'POST',
        url:"<?php echo U('Admin/Goods/getCat');?>",
        data:{parent_id:parent_id},
        dataType:'JSON',
        success:function(jsonData){

                switch(jsonData['list'][0]['level']){
                    case 2:
                        str = '<option value="0">请选择车类型</option>';
                    break;
                    case 3:
                        str = '<option value="0">请选择吨位</option>';
                    break;
                    case 4:
                        str = '<option value="0">请选择门架</option>';
                    break;
                    case 5:
                        str = '<option value="0">请选择门架提升高度</option>';
                    break;
                }

                $.each(jsonData.list,function(key,val){
                    if(val['level'] == 3){
                        str += '<option value="'+val['id']+'">'+val['name']*1000+'('+val['money']+')</option>';
                    }else{
                        str += '<option value="'+val['id']+'">'+val['name']+'('+val['money']+')</option>';
                    }
                });
                $(obj).html('');
                $(obj).append(str); 

        }
    });    
}
    

function setNull(lv)
{
    for(lv;lv<6;lv++){
        switch(lv){
            case 2:
                str = '<option value="0">请选择车类型</option>';
            break;
            case 3:
                str = '<option value="0">请选择吨位</option>';
            break;
            case 4:
                str = '<option value="0">请选择门架</option>';
            break;
            case 5:
                str = '<option value="0">请选择门架提升高度</option>';
            break;
        }
        var obj = '.level'+lv;
        $(obj).html('');
        $(obj).append(str);
    }
}

function getCat2(parent_id,Lv)
{
    Lv++;
    var obj = '.level'+Lv;
    console.log(parent_id);
    var str;
    $.ajax({
        type:'POST',
        url:"<?php echo U('Admin/Goods/getCat');?>",
        data:{parent_id:parent_id},
        dataType:'JSON',
        success:function(jsonData){
            if(jsonData.list == false){
                for(Lv;Lv<6;Lv++){
                    var obj2 = '.level'+Lv;
                    $(obj2).html('');
                }
            }else{
                $.each(jsonData.list,function(key,val){
                    if(val['level'] == 3){
                        str += '<option value="'+val['id']+'">'+val['name']*1000+'</option>';
                    }else{
                        str += '<option value="'+val['id']+'">'+val['name']+'</option>';
                    }
                });
                $(obj).html('');
                $(obj).append(str); 
                               
                if(Lv == 5){
                    return true;
                }else{
                    getCat(jsonData['list'][0]['id'],Lv);
                }
            }

        }
    });    
}

</script>

<!--以下是在线编辑器 代码 -->
<script type="text/javascript">
    var editor;
    $(function () {
        //具体参数配置在  editor_config.js 中
        var options = {
            zIndex: 999,
            initialFrameWidth: "100%", //初化宽度
            initialFrameHeight: 400, //初化高度
            focus: false, //初始化时，是否让编辑器获得焦点true或false
            maximumWords: 99999, removeFormatAttributes: 'class,style,lang,width,height,align,hspace,valign',//允许的最大字符数 'fullscreen',
            pasteplain:false, //是否默认为纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴
            autoHeightEnabled: true
         /*   autotypeset: {
                mergeEmptyline: true,        //合并空行
                removeClass: true,           //去掉冗余的class
                removeEmptyline: false,      //去掉空行
                textAlign: "left",           //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
                imageBlockLine: 'center',    //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
                pasteFilter: false,          //根据规则过滤没事粘贴进来的内容
                clearFontSize: false,        //去掉所有的内嵌字号，使用编辑器默认的字号
                clearFontFamily: false,      //去掉所有的内嵌字体，使用编辑器默认的字体
                removeEmptyNode: false,      //去掉空节点
                                             //可以去掉的标签
                removeTagNames: {"font": 1},
                indent: false,               // 行首缩进
                indentValue: '0em'           //行首缩进的大小
            }*/
        };
        editor = new UE.ui.Editor(options);
        editor.render("post_content");
    });  
    
    
    $('#publish_time').daterangepicker({
        format:"YYYY-MM-DD",
        singleDatePicker: true,
        showDropdowns: true,
        minDate:'2016-01-01',
        maxDate:'2030-01-01',
        startDate:'<?php echo (date("Y-m-d",$info["publish_time"])); ?>',
        locale : {
            applyLabel : '确定',
            cancelLabel : '取消',
            fromLabel : '起始时间',
            toLabel : '结束时间',
            customRangeLabel : '自定义',
            daysOfWeek : [ '日', '一', '二', '三', '四', '五', '六' ],
            monthNames : [ '一月', '二月', '三月', '四月', '五月', '六月','七月', '八月', '九月', '十月', '十一月', '十二月' ],
            firstDay : 1
        }
    });
    
    function checkForm(){
        if($('input[name="title"]').val() == ''){
            alert("请填写文章标题！");
            return false;
        }
        if($('#cat_id').val() == '' || $('#cat_id').val() == 0){
            alert("请选择文章类别！");
            return false;
        }
        if($('#post_content').val() == ''){
            alert("请填写文章内容！");
            return false;
        }
        $('#add_post').submit();
    }
</script>
<script type="text/javascript" charset="utf-8" src="/Public/plugins/Ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/plugins/Ueditor/ueditor.all.min.js"> </script>
 <script type="text/javascript" charset="utf-8" src="/Public/plugins/Ueditor/lang/zh-cn/zh-cn.js"></script>
<script>
function button1()
{
    $('.div1').css({'display':'none'});
    $('.div2').css({'display':'block'});
}
function button2()
{
    $('.div1').css({'display':'block'});
    $('.div2').css({'display':'none'});
}                    
</script> 
<script type="text/javascript">  
  
    var editor;
    $(function () {
        //具体参数配置在  editor_config.js 中
        var options = {
            zIndex: 999,
            initialFrameWidth: "153%", //初化宽度
            initialFrameHeight: 400, //初化高度
            focus: false, //初始化时，是否让编辑器获得焦点true或false
            maximumWords: 99999, removeFormatAttributes: 'class,style,lang,width,height,align,hspace,valign',//允许的最大字符数 'fullscreen',
            pasteplain:false, //是否默认为纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴
            autoHeightEnabled: true
         /*   autotypeset: {
                mergeEmptyline: true,        //合并空行
                removeClass: true,           //去掉冗余的class
                removeEmptyline: false,      //去掉空行
                textAlign: "left",           //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
                imageBlockLine: 'center',    //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
                pasteFilter: false,          //根据规则过滤没事粘贴进来的内容
                clearFontSize: false,        //去掉所有的内嵌字号，使用编辑器默认的字号
                clearFontFamily: false,      //去掉所有的内嵌字体，使用编辑器默认的字体
                removeEmptyNode: false,      //去掉空节点
                                             //可以去掉的标签
                removeTagNames: {"font": 1},
                indent: false,               // 行首缩进
                indentValue: '0em'           //行首缩进的大小
            }*/
        };
        editor = new UE.ui.Editor(options);
        editor.render("post_content");
    }); 
</script>
<!--以上是在线编辑器 代码  end-->
<script type="text/javascript">  
  
    var editor;
    $(function () {
        //具体参数配置在  editor_config.js 中
        var options = {
            zIndex: 999,
            initialFrameWidth: "153%", //初化宽度
            initialFrameHeight: 400, //初化高度
            focus: false, //初始化时，是否让编辑器获得焦点true或false
            maximumWords: 99999, removeFormatAttributes: 'class,style,lang,width,height,align,hspace,valign',//允许的最大字符数 'fullscreen',
            pasteplain:false, //是否默认为纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴
            autoHeightEnabled: true
         /*   autotypeset: {
                mergeEmptyline: true,        //合并空行
                removeClass: true,           //去掉冗余的class
                removeEmptyline: false,      //去掉空行
                textAlign: "left",           //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
                imageBlockLine: 'center',    //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
                pasteFilter: false,          //根据规则过滤没事粘贴进来的内容
                clearFontSize: false,        //去掉所有的内嵌字号，使用编辑器默认的字号
                clearFontFamily: false,      //去掉所有的内嵌字体，使用编辑器默认的字体
                removeEmptyNode: false,      //去掉空节点
                                             //可以去掉的标签
                removeTagNames: {"font": 1},
                indent: false,               // 行首缩进
                indentValue: '0em'           //行首缩进的大小
            }*/
        };
        editor = new UE.ui.Editor(options);
        editor.render("goods_describe");
    }); 
</script>
<!--以上是在线编辑器 代码  end-->
<script>
    $(document).ready(function(){
        $(":checkbox[cka]").click(function(){
            var $cks = $(":checkbox[ck='"+$(this).attr("cka")+"']");
            if($(this).is(':checked')){
                $cks.each(function(){$(this).prop("checked",true);});
            }else{
                $cks.each(function(){$(this).removeAttr('checked');});
            }
        });
    });

    function choosebox(o){
        var vt = $(o).is(':checked');
        if(vt){
            $('input[type=checkbox]').prop('checked',vt);
        }else{
            $('input[type=checkbox]').removeAttr('checked');
        }
    }
    /*
     * 以下是图片上传方法
     */
    // 上传商品图片成功回调函数
    function call_back(fileurl_tmp){
        $("#original_img").val(fileurl_tmp);
    	$("#original_img2").attr('href', fileurl_tmp);
    }
 
    // 上传商品相册回调函数
    function call_back2(paths){
        
        var  last_div = $(".goods_xc:last").prop("outerHTML");	
        for (i=0;i<paths.length ;i++ )
        {                    
            $(".goods_xc:eq(0)").before(last_div);	// 插入一个 新图片
                $(".goods_xc:eq(0)").find('a:eq(0)').attr('href',paths[i]).attr('onclick','').attr('target', "_blank");// 修改他的链接地址
            $(".goods_xc:eq(0)").find('img').attr('src',paths[i]);// 修改他的图片路径
                $(".goods_xc:eq(0)").find('a:eq(1)').attr('onclick',"ClearPicArr2(this,'"+paths[i]+"')").text('删除');
            $(".goods_xc:eq(0)").find('input').val(paths[i]); // 设置隐藏域 要提交的值
        } 			   
    }
    /*
     * 上传之后删除组图input     
     * @access   public
     * @val      string  删除的图片input
     */
    function ClearPicArr2(obj,path)
    {
    	$.ajax({
                    type:'GET',
                    url:"<?php echo U('Admin/Uploadify/delupload');?>",
                    data:{action:"del", filename:path},
                    success:function(){
                           $(obj).parent().remove(); // 删除完服务器的, 再删除 html上的图片				 
                    }
		});
		// 删除数据库记录
    	$.ajax({
                    type:'GET',
                    url:"<?php echo U('Admin/Goods/del_goods_images');?>",
                    data:{filename:path},
                    success:function(){
                          //		 
                    }
		});		
    }
 


/** 以下 商品属性相关 js*/
$(document).ready(function(){
	
    // 商品类型切换时 ajax 调用  返回不同的属性输入框
    $("#goods_type").change(function(){        
        var goods_id = $("input[name='goods_id']").val();
        var type_id = $(this).val();
            $.ajax({
                    type:'GET',
                    data:{goods_id:goods_id,type_id:type_id}, 
                    url:"/index.php/admin/Goods/ajaxGetAttrInput",
                    success:function(data){                            
                            $("#goods_attr_table tr:gt(0)").remove()
                            $("#goods_attr_table").append(data);
                    }        
            });			                
    });
	// 触发商品类型
	$("#goods_type").trigger('change');
    $("input[name='exchange_integral']").blur(function(){
        var shop_price = parseInt($("input[name='shop_price']").val());
        var exchange_integral = parseInt($(this).val());
        if (shop_price * 100 < exchange_integral) {

        }
    });
});
 

// 属性输入框的加减事件
function addAttr(a)
{
	var attr = $(a).parent().parent().prop("outerHTML");	
	attr = attr.replace('addAttr','delAttr').replace('+','-');	
	$(a).parent().parent().after(attr);
}
// 属性输入框的加减事件
function delAttr(a)
{
   $(a).parent().parent().remove();
}
 

/** 以下 商品规格相关 js*/
$(document).ready(function(){
	
    // 商品类型切换时 ajax 调用  返回不同的属性输入框
    $("#spec_type").change(function(){        
        var goods_id = '<?php echo ($goodsInfo["goods_id"]); ?>';
        var spec_type = $(this).val();
            $.ajax({
                    type:'GET',
                    data:{goods_id:goods_id,spec_type:spec_type}, 
                    url:"<?php echo U('admin/Goods/ajaxGetSpecSelect');?>",
                    success:function(data){                            
                           $("#ajax_spec_data").html('')
                           $("#ajax_spec_data").append(data);
						   //alert('132');
						   ajaxGetSpecInput();	// 触发完  马上处罚 规格输入框
                    }
            });			                
    });
	// 触发商品规格
	$("#spec_type").trigger('change'); 
});

/** 以下是编辑时默认选中某个商品分类*/
$(document).ready(function(){

	<?php if($level_cat['2'] > 0): ?>// 商品分类第二个下拉菜单
		 get_category('<?php echo ($level_cat[1]); ?>','cat_id_2','<?php echo ($level_cat[2]); ?>');<?php endif; ?>
	<?php if($level_cat['3'] > 0): ?>// 商品分类第二个下拉菜单
		 get_category('<?php echo ($level_cat[2]); ?>','cat_id_3','<?php echo ($level_cat[3]); ?>');<?php endif; ?>

    //  扩展分类
	<?php if($level_cat2['2'] > 0): ?>// 商品分类第二个下拉菜单
		 get_category('<?php echo ($level_cat2[1]); ?>','extend_cat_id_2','<?php echo ($level_cat2[2]); ?>');<?php endif; ?>
	<?php if($level_cat2['3'] > 0): ?>// 商品分类第二个下拉菜单
		 get_category('<?php echo ($level_cat2[2]); ?>','extend_cat_id_3','<?php echo ($level_cat2[3]); ?>');<?php endif; ?>

});

</script>
</body>
</html>