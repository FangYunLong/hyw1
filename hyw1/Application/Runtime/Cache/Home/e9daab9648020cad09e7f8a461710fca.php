<?php if (!defined('THINK_PATH')) exit();?>
<?php if(is_array($goodsList)): foreach($goodsList as $key=>$goodsList): ?><li>
	<a href="<?php echo U('Goods/GoodsInfo',['goods_id'=>$goodsList['goods_id']]);?>">
		<img src="<?php echo ($goodsList["zm_pic"]); ?>" onload="javascript:DrawImage1(this,294,212)" width="294" height="212" />
	</a>
	<span>
		<a target='_block' href="<?php echo U('Goods/GoodsInfo',['goods_id'=>$goodsList['goods_id']]);?>">
			<?php echo ($goodsList["goods_name"]); ?>
		</a>
	</span>
    <!-- <div class="yearJia"><?php if($users['level_id'] == 2): echo ($goodsList["car_bzzj"]); ?>666<?php else: echo ($goodsList["user_bzzj"]); ?>777<?php endif; ?>元/月</div> -->
</li><?php endforeach; endif; ?>