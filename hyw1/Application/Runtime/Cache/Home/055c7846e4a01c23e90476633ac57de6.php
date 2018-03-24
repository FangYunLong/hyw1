<?php if (!defined('THINK_PATH')) exit();?><form action="<?php echo U('Goods/goodsSearch');?>" method="post">
    <div class="souk">
      <input name="keyword" type="text" class="shur2"  value="请输入关键字  如品牌、吨位、名称、型号" onfocus="if(this.value=='请输入关键字  如品牌、吨位、名称、型号'){this.value='';this.style.color='#444'}" onblur="if(this.value==''){this.value='请输入关键字  如品牌、吨位、名称、型号';this.style.color='#999'}"/>
    </div>
    <div clsas="souk2">
      <input name="" type="submit" value='' class="anniu2"/>
    </div>
    <div class="clear"></div>
</form>