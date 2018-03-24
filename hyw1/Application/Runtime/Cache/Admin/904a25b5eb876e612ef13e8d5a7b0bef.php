<?php if (!defined('THINK_PATH')) exit();?>﻿<form method="post" enctype="multipart/form-data" target="_blank" id="form-order">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
              <!--  <td style="width: 1px;" class="text-center">
                &lt;!&ndash;
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);">
                &ndash;&gt;
                </td>  -->
                <td style="width: 1px;" class="text-center">
                    <a href="javascript:sort('goods_id');">ID</a>
                </td>
                <td style="width: 130px;" class="text-center">
                    <a href="javascript:sort('goods_name');">商品名称</a>
                </td>
                <td style="width: 80px;" class="text-center">
                    <a href="javascript:sort('pinpai');">品牌</a>
                </td>                                
                <td style="width: 170px;" class="text-center">
                    <a href="javascript:sort('cart_type');">车类型</a>
                </td>
                <td style="width: 60px;" class="text-center">
                    <a href="javascript:sort('dunwei');">吨位(kg)</a>
                </td>                
                <td style="width: 150px;" class="text-center">
                    <a href="javascript:sort('menjia');">门架</a>
                </td>                
                <td style="width: 60px;" class="text-center">
                    <a href="javascript:sort('mj_height');">提升高度</a>
                </td>
<!--                 <td style="width: 60px;" class="text-center">
                    <a href="javascript:sort('chexing');">车型</a>
                </td>   
                <td style="width: 60px;" class="text-center">
                    <a href="javascript:sort('chezhong');">车种</a>
                </td> -->                   
                <td style="width: 100px;" class="text-center">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($goodsList)): $i = 0; $__LIST__ = $goodsList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><tr>
                    <td class="text-center"><?php echo ($list["goods_id"]); ?></td>
                    <td class="text-center"><?php echo (getSubstr($list["goods_name"],0,33)); ?></td>
                    <td class="text-center"><?php echo ($list["pinpai"]); ?></td>
                    <td class="text-center"><?php echo ($list["cart_type"]); ?></td>
                    <td class="text-center"><?php echo ($list[dunwei]*1000); ?></td>
                    <td class="text-center"><?php echo ($list["menjia"]); ?></td>
                    <td class="text-center"><?php echo ($list["mj_height"]); ?></td>
<!--                     <td class="text-center"><?php echo ($list["chexing"]); ?></td>
                    <td class="text-center"><?php echo ($list["chezhong"]); ?></td> -->
                    <td class="text-center">
                       
                        <a href="<?php echo U('Admin/Goods/addEditGoods',array('id'=>$list['goods_id']));?>" class="btn btn-primary" title="编辑"><i class="fa fa-pencil"></i></a>
                        <a href="javascript:void(0);" onclick="del('<?php echo ($list[goods_id]); ?>')" class="btn btn-danger" title="删除"><i class="fa fa-trash-o"></i></a>
                    </td>                        
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
    </div>
</form>
<div class="row">
    <div class="col-sm-3 text-left"></div>
    <div class="col-sm-9 text-right"><?php echo ($page); ?></div>
</div>
<script>
    // 点击分页触发的事件
    $(".pagination  a").click(function(){
        cur_page = $(this).data('p');
        ajax_get_table('search-form2',cur_page);
    });
	
    /*
     * 清除静态页面缓存
     */
    function ClearGoodsHtml(goods_id)
    {
    	$.ajax({
				type:'GET',
				url:"<?php echo U('Admin/System/ClearGoodsHtml');?>",
				data:{goods_id:goods_id},
				dataType:'json',
				success:function(data){
					layer.alert(data.msg, {icon: 2});								 
				}
		});
    }
    /*
     * 清除商品缩列图缓存
     */
    function ClearGoodsThumb(goods_id)
    {
    	$.ajax({
				type:'GET',
				url:"<?php echo U('Admin/System/ClearGoodsThumb');?>",
				data:{goods_id:goods_id},
				dataType:'json',
				success:function(data){
					layer.alert(data.msg, {icon: 2});								 
				}
		});
    }		
</script>