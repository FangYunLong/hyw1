<?php
/**
 *
//
 * Author: IT宇宙人     
*: 2015-09-09
 */
namespace Admin\Controller;
use Admin\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;

class GoodsController extends BaseController {

    /**
     *  品牌分类列表
     */
    public function categoryBrand(){
        $GoodsLogic = new GoodsLogic();
        $cat_id =846 ;
        $cat_list = $GoodsLogic->goods_cat_list($cat_id);
        //dump($cat_list);
        $this->assign('cat_list',$cat_list);
        $this->display();
    }




    /**
     *  商品分类列表
     */
    public function categoryList(){
        /*$GoodsLogic = new GoodsLogic();
        $cat_list = $GoodsLogic->goods_cat_list();*/
        $cat_list = M('CartArt')->select(array('index'=>'id'));
        $data = array();
        //$child_id = array();
        foreach ($cat_list as $k=>$v) {
            if (isset($v['child'])) {
                $child_id = explode('|',$v['child']);
                //$child_id[]=$v['id'];
                array_unshift($child_id,$v['id']);
                $data[] = $child_id;
            }
        }
        $c = array();
        foreach ($data as $v) {
            $c = array_merge($c,$v);
        }

        $arr = array();
        foreach ($c as $v) {
            $arr[] = $cat_list[$v];
        }
        $arr = array_filter($arr);
        $CartArt = M('CartArt')->where(['parent_id'=>0])->select();

        foreach($CartArt as $key => $val){
            $CartArt2 = M('CartArt')->where(['parent_id'=>$val['id']])->select();
            $data2[] = $val;
            foreach($CartArt2 as $k => $v){
                $data2[] = $v;
            }
        }
        
// dump($data2);
        // dump($arr);
        $this->assign('cat_list',$data2);
        $this->display('categoryList1');        
    }


    //修改分类排序
    public function editCateSort()
    {
        $data = I('post.');
        $res = M('CartArt')->save($data);

        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    //修改分類
    public function editCategory()
    {
        $id = I('id');
        if(IS_GET){
            $CartArt = M('CartArt')->find($id);
            $this->assign('CartArt',$CartArt);
        }else{
            $data = I('post.');
            $data['name'] = I('post.name','',false);
            $parent = M('CartArt')->find($data['parent_id']);
            $data['level'] = $parent['level'] + 1;

            if($id){
                $res = M('CartArt')->save($data);
            }else{
                $res = M('CartArt')->add($data);
            }
            // dump($data);exit;
            if($res){
                $this->success('操作成功！',U('Goods/categoryList'));
            }else{
                $this->error('操作失败',U('Goods/categoryList'));
            }
            exit;
        }
            // exit;
// exit;
        $cat_list = M('CartArt')->where(['parent_id'=>0])->select();
        // dump($CartArt);
        // dump($cat_list);
        $this->assign('cat_list',$cat_list);
        $this->display('_category1');
    }

    /**
     * 租金计算方式
    */
    public function goodsPrice()
    {
        $this->display();
    }
    
    /**
     * 添加修改商品分类
     * 手动拷贝分类正则 ([\u4e00-\u9fa5/\w]+)  ('393','$1'), 
     * select * from tp_goods_category where id = 393
        select * from tp_goods_category where parent_id = 393
        update tp_goods_category  set parent_id_path = concat_ws('_','0_76_393',id),`level` = 3 where parent_id = 393
        insert into `tp_goods_category` (`parent_id`,`name`) values 
        ('393','时尚饰品'),
     */
    public function addEditCategory(){
            if(I('post.image')){
                $_POST['image'] = APP_URL . I('post.image');
            }

            $GoodsLogic = new GoodsLogic();        
            if(IS_GET)
            {
                $goods_category_info = D('GoodsCategory')->where('id='.I('GET.id',0))->find();                                                            
                $level_cat = $GoodsLogic->find_parent_cat($goods_category_info['id']); // 获取分类默认选中的下拉框
                
                $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单                
                $this->assign('level_cat',$level_cat);                
                $this->assign('cat_list',$cat_list);                 
                $this->assign('goods_category_info',$goods_category_info);      
                $this->display('_category');     
                exit;
            }

            $GoodsCategory = D('GoodsCategory'); //

            $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2  表示更新
            //ajax提交验证
            if($_GET['is_ajax'] == 1)
            {
                C('TOKEN_ON',false);
                
                if(!$GoodsCategory->create(NULL,$type))// 根据表单提交的POST数据创建数据对象                 
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '操作失败!',
                        'data'  => $GoodsCategory->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {
                    //  form表单提交
                    C('TOKEN_ON',true);       

                    $GoodsCategory->parent_id = $_POST['parent_id_1'];
                    $_POST['parent_id_2'] && ($GoodsCategory->parent_id = $_POST['parent_id_2']);

                    if($GoodsCategory->id > 0 && $GoodsCategory->parent_id == $GoodsCategory->id)
                    {
                        //  编辑
                        $return_arr = array(
                            'status' => -1,
                            'msg'   => '上级分类不能为自己',
                            'data'  => '',
                        );
                        $this->ajaxReturn(json_encode($return_arr));                        
                    }
                    if($GoodsCategory->commission_rate > 100)
                    {
                        //  编辑
                        $return_arr = array(
                            'status' => -1,
                            'msg'   => '分佣比例不得超过100%',
                            'data'  => '',
                        );
                        $this->ajaxReturn(json_encode($return_arr));                        
                    }                    
                    if ($type == 2)
                    {
                        $GoodsCategory->save(); // 写入数据到数据库
                        $GoodsLogic->refresh_cat($_POST['id']);
                    }
                    else
                    {
                        $insert_id = $GoodsCategory->add(); // 写入数据到数据库
                        $GoodsLogic->refresh_cat($insert_id);
                    }
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',
                        'data'  => array('url'=>U('Admin/Goods/categoryList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }  
            }

    }
    
    //优惠列表
    public function preferList()
    {
        $this->display();
    }

    //叉车参数
    public function paramList()
    {
        $this->display();
    }

    public function ajaxParam()
    {
        $where['is_prefer'] = 2;
        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $show = $Page->show();
        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";

        $goodsList = $model->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();
        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /**
     *  商品列表
     */
    public function ajaxPreferList(){            
        
        $where['is_prefer'] = 1;
        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $show = $Page->show();
        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";

        $goodsList = $model->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();

        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }

    /**
     * 获取商品分类 的筛选规格 复选框
     */
    public function ajaxGetSpecList(){
        $GoodsLogic = new GoodsLogic();
        $_REQUEST['category_id'] = $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0;
        $filter_spec = M('GoodsCategory')->where("id = ".$_REQUEST['category_id'])->getField('filter_spec');        
        $filter_spec_arr = explode(',',$filter_spec);        
        $str = $GoodsLogic->GetSpecCheckboxList($_REQUEST['type_id'],$filter_spec_arr);  
        $str = $str ? $str : '没有可帅选的商品规格';
        exit($str);        
    }
 
    /**
     * 获取商品分类 的筛选属性 复选框
     */
    public function ajaxGetAttrList(){
        $GoodsLogic = new GoodsLogic();
        $_REQUEST['category_id'] = $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0;
        $filter_attr = M('GoodsCategory')->where("id = ".$_REQUEST['category_id'])->getField('filter_attr');        
        $filter_attr_arr = explode(',',$filter_attr);        
        $str = $GoodsLogic->GetAttrCheckboxList($_REQUEST['type_id'],$filter_attr_arr);          
        $str = $str ? $str : '没有可帅选的商品属性';
        exit($str);        
    }    
    
    /**
     * 删除分类
     */
    public function delGoodsCategory(){
        // 判断子分类
        $GoodsCategory = M("CartArt");   
        $count = $GoodsCategory->where("parent_id = {$_GET['id']}")->count("id");   
        if($count > 0){
          $this->error('该分类下还有分类不得删除!',U('Admin/Goods/categoryList'));
        }
        // 判断是否存在商品
        // $goods_count = M('Goods')->where("cat_id = {$_GET['id']}")->count('1');
        // $goods_count > 1 && $this->error('该分类下有商品不得删除!',U('Admin/Goods/categoryList'));
        // 删除分类
        $res = $GoodsCategory->where("id = {$_GET['id']}")->delete();   
        $this->success("操作成功!!!",U('Admin/Goods/categoryList'));
    }
    
    //特价车列表
    public function specialCar()
    {
        $GoodsLogic = new GoodsLogic();        
        //$brandList = $GoodsLogic->getSortBrands();
        $model = M("CartArt");
        $cat_type = $model->where("parent_id = 3")->order('sort ASC')->getField('id,name,parent_id,level');  //车类型
        $cat_kind = $model->where("parent_id = 25")->order('sort ASC')->getField('id,name,parent_id,level');  //车种
        $tonnage = $model->where("parent_id = 2")->order('sort ASC')->getField('id,name,parent_id,level');  //吨位
        $category = $model->where("parent_id = 1")->order('sort ASC')->getField('id,name,parent_id,level');  //品牌
        $categoryList = $GoodsLogic->getSortCategory();
        $this->assign('categoryList',$categoryList);
        $this->assign('cat_type',$cat_type);
        $this->assign('cat_kind',$cat_kind);
        $this->assign('tonnage',$tonnage);
        $this->assign('brandList',$category);        
        $this->display();
    }

    //特价车审核
    public function isSpecialCar()
    {
         $goods_id = I('post.goods_id');
         $data['goods_id'] = $goods_id;
         $Goods = M('Goods')->find($goods_id); 

         if($Goods['is_special_car'] == 1){
            $data['is_special_car'] = 0;
         }else{
            $data['is_special_car'] = 1;
         }

         $res = M('Goods')->save($data);

         if($res){
            exit(json_encode(['status'=>1,'msg'=>'操作成功！','is_special_car'=>$data['is_special_car']]));
         }else{
            exit(json_encode(['status'=>-1,'msg'=>'操作失败！']));
         }
    }

    //特价车上架/下架
    public function ajax_is_on_sale()
    {
         $goods_id = I('post.goods_id');
         $data['goods_id'] = $goods_id;
         $Goods = M('Goods')->find($goods_id); 

         if($Goods['is_on_sale'] == 1){
            $data['is_on_sale'] = 0;
         }else{
            $data['is_on_sale'] = 1;
         }

         $res = M('Goods')->save($data);

         if($res){
            exit(json_encode(['status'=>1,'msg'=>'操作成功！','is_on_sale'=>$data['is_on_sale']]));
         }else{
            exit(json_encode(['status'=>-1,'msg'=>'操作失败！']));
         }
    }

    /**
     *  特价车列表
     */
    public function ajaxSpecialCar(){            
        
        $where  = ' 1 = 1 '; // 搜索条件 
        $where .= ' AND is_special = 1 ';
        I('intro')    && $where = "$where and ".I('intro')." = 1" ;
        $brand_name =I('brand_id');
        $cat_type =I('cat_type');
        $tonnage =I('tonnage');
        $cat_kind =I('cat_kind');
        $is_special =I('is_special');
        I('brand_id') && $where = "$where and (pinpai like '%$brand_name%') ";
        I('cat_type') && $where = "$where and (cart_type like '%$cat_type%') ";
        I('cat_kind') && $where = "$where and (chezhong like '%$cat_kind%') ";
        (I('tonnage') !== '') && $where = "$where and dunwei = ".I('tonnage') ;
        (I('is_special') !== '') && $where = "$where and is_special = $is_special";
        (I('is_on_sale') !== '') && $where = "$where and is_on_sale = ".I('is_on_sale') ;
        (I('is_special_car') !== '') && $where = "$where and is_special_car = ".I('is_special_car') ;
        $cat_id = I('cat_id');
        // 关键词搜索               
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if($key_word)
        {
            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%' or pinpai like '%$key_word%')" ;
        }
        
        if($cat_id > 0)
        {
            $grandson_ids = getCatGrandson($cat_id); 
            $where .= " and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
        }
        
        
        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $show = $Page->show();
        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";
        $goodsList = $model->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();

        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }

    public function SpecialCarInfo()
    {
        $goods_id = I('get.goods_id');
        $goodsInfo = M("Goods")->field('goods_id,pinpai,dunwei,menjia,mj_height,shuju,goods_name,chexing,chezhong,cart_age,username,mobile,special_price,address,cart_type,factorytime,is_yt,huoqi,description,use_hours,dcsj,zm_pic,cm_pic,czt_pic,nb_pic')->where(array('goods_id'=>$goods_id))->find();
        $cat_list = M('CartArt')->select(array('index'=>'id'));
        //$child_id = array();
        $pinpai = $this->getGoodsAttr(846);
        $dunwei = $this->getGoodsAttr(847);
        $cart_type = $this->getGoodsAttr(848);
        $menjia = $this->getGoodsAttr(849);
        $mj_height = $this->getGoodsAttr(850);
        $shuju = $this->getGoodsAttr(851);
        $chezhong = $this->getGoodsAttr(852);
        $chexing = $this->getGoodsAttr(853);
        $bydc = $this->getGoodsAttr(854);
        $bydc = $this->getGoodsAttr(854);
        // $pinpai = $this->gettree($cat_list,1);
        // $dunwei = $this->gettree($cat_list,2);
        // $cart_type = $this->gettree($cat_list,3);
        // $menjia = $this->gettree($cat_list,4);
        // $mj_height = $this->gettree($cat_list,5);
        // $shuju = $this->gettree($cat_list,21);
        // $chezhong = $this->gettree($cat_list,25);
        // $chexing = $this->gettree($cat_list,44);
        // $bydc = $this->gettree($cat_list,49);

        $this->assign('pinpai',$pinpai);
        $this->assign('dunwei',$dunwei);
        $this->assign('cart_type',$cart_type);
        $this->assign('menjia',$menjia);
        $this->assign('mj_height',$mj_height);
        $this->assign('shuju',$shuju);
        $this->assign('chezhong',$chezhong);
        $this->assign('chexing',$chexing);
        $this->assign('bydc',$bydc);
        $this->assign('goodsInfo',$goodsInfo);
        $this->assign('is_yt',C('is_yt'));
        $this->display();        
    }

    /**
     *  商品列表
     */
    public function goodsList(){
        $GoodsLogic = new GoodsLogic();        
        //$brandList = $GoodsLogic->getSortBrands();
        $model = M("CartArt");
        $category  = $model->where("parent_id = 1")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();   //品牌
        $cat_type  = $model->where("parent_id = 3")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();   //车类型：柴油、电车
        $tonnage   = $model->where("parent_id = 2")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();   //吨位
        $door_type = $model->where("parent_id = 4")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();   //门架类型
        $menjia    = $model->where("parent_id = 5")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();   //门架提升高度
        $dianchi   = $model->where("parent_id = 49")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();  //备用电池
        //$type      = $model->whe->field('id,name,parent_id,level,sort')re("parent_id = 1")->select();   //类型：普通、冷库、防爆
        $shuju     = $model->where("parent_id = 21")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();  //属具
        $cat_kind  = $model->where("parent_id = 25")->field('id,name,parent_id,level,sort')->order('sort ASC')->select();  //车种
        $categoryList = $GoodsLogic->getSortCategory();
        $this->assign('door_type',$door_type);
        $this->assign('menjia',$menjia);
        $this->assign('dianchi',$dianchi);
        $this->assign('shuju',$shuju);
        //$this->assign('type',$type);
        $this->assign('categoryList',$categoryList);
        $this->assign('cat_type',$cat_type);
        $this->assign('cat_kind',$cat_kind);
        $this->assign('tonnage',$tonnage);
        $this->assign('brandList',$category);
        $this->display();
    }
    
    /**
     *  商品列表
     */
    public function ajaxGoodsList(){            
        
        $where = ' 1 = 1 '; // 搜索条件 
        $where .= ' AND is_special = 0 ';               
        I('intro') && $where = "$where and ".I('intro')." = 1" ;
        $brand_name = I('brand_id');
        $cat_type   = I('cat_type');
        $tonnage    = I('tonnage');   
        $cat_kind   = I('cat_kind');
        $is_special = I('is_special');  
        $door_type  = I('door_type');  
        $shuju      = I('shuju'); 
        $is_yt      = I('is_yt'); 

        I('brand_id') && $where = "$where and (cc1.name = '$brand_name') ";
        I('cat_type') && $where = "$where and (cc2.name = '$cat_type') ";
        // I('cat_kind') && $where = "$where and (chezhong like '%$cat_kind%') ";
        (I('tonnage') !== '') && $where = "$where and cc3.name = ".I('tonnage') ;
        // I('shuju') && $where = "$where and (shuju  like '%$shuju%') ";              //属具
        I('door_type') && $where = "$where and (cc4.name  = '$door_type') ";     //门架类型
        //(I('door_type') !== '') && $where = "$where and menjia = ".I('door_type') ;      //门架类型
        (I('menjia') !== '') && $where = "$where and cc5.name = ".I('menjia') ;         //门架提升高度
        // (I('dianchi') !== '') && $where = "$where and bydc = ".I('dianchi') ;            //备用电池
        //(I('shuju') !== '') && $where = "$where and shuju = ".I('shuju') ;               //属具
        (I('is_special') !== '') && $where = "$where and is_special = $is_special";
        (I('is_on_sale') !== '') && $where = "$where and is_on_sale = ".I('is_on_sale') ;
        // (I('is_yt') !== '') && $where = "$where and is_yt = ".I('is_yt') ;

        $cat_id = I('cat_id');
        // 关键词搜索               
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if($key_word)
        {
            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%' or pinpai like '%$key_word%')" ;
        }
        
        if($cat_id > 0)
        {
            $grandson_ids = getCatGrandson($cat_id); 
            $where .= " and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
        }
        
        $model = M('Goods');
        $where = " $where and is_prefer = 0 and cid > 0 ";

        $count = $model->alias('g')
                      ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                      ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                      ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                      ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                      ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                      ->where($where)
                      ->count();
                      // dump($count);exit;
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $show = $Page->show();
        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";

        $goodsList = $model->alias('g')
                          ->join('tp_car_cate as cc5 on g.cid=cc5.id')
                          ->join('tp_car_cate as cc4 on cc5.parent_id=cc4.id')
                          ->join('tp_car_cate as cc3 on cc4.parent_id=cc3.id')
                          ->join('tp_car_cate as cc2 on cc3.parent_id=cc2.id')
                          ->join('tp_car_cate as cc1 on cc2.parent_id=cc1.id')
                          ->where($where)
                           ->order($order_str)
                           ->limit($Page->firstRow.','.$Page->listRows)
                           ->select();

        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }

    /**
     * 一个获取分类组
    */
    public function gettree($cat_list,$id)
    {
        $data = array();
        foreach ($cat_list as $k=>$v) {
            if (isset($v['child'])  && $v['id']==$id) {
                $child_id = explode('|',$v['child']);
                //$child_id[]=$v['id'];
                //array_unshift($child_id,$v['id']);//加上父类id
                $data[] = $child_id;
            }
        }
        $c = array();
        foreach ($data as $v) {
            $c = array_merge($c,$v);
        }
        $arr = array();
        foreach ($c as $v) {
            $arr[] = $cat_list[$v];
        }
        return $arr;
    }

    public function getGoodsAttr($parent_id='')
    {
        return M('CartArt')->where(['parent_id'=>$parent_id])->order('sort ASC')->select();
    }

    public function fileUploadNews($path='',$files)
    {
        $type = ['image/jpeg','image/png','image/gif'];
        foreach($files as $k => $v){

            if($v['size'] <= 0){

                continue;
            }

            if(!in_array($v['type'],$type)){
                return false;
            }

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
     * 添加修改商品
     */
    public function addEditGoods()
    {
        if (IS_POST) {
            $data = I('post.');
            $data = array_filter($data);
            $data['goods_name'] = I('goods_name','',false);
  
            //验证分支是否合理
            $data = $this->verifyCat($data);

            $data['add_time'] = time();
            $files = $_FILES;

            //上传图片
            if($files){
                //定义上传路径
                $path="./Public/Upload/cart/";
                $uploadinfo=$this->fileUploadNews($path,$files);
                $uploadinfo['zm_pic']?$data['zm_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['zm_pic']:false;
                $uploadinfo['cm_pic']?$data['cm_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['cm_pic']:false;
                $uploadinfo['czt_pic']?$data['czt_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['czt_pic']:false;
                $uploadinfo['nb_pic']?$data['nb_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['nb_pic']:false;
            }

            if(!$data['zm_pic']){
                $this->error('正面图片必须上传！');exit;
            }
            if(!$data['cm_pic']){
                $this->error('侧面图片必须上传！');exit;
            }
            if(!$data['czt_pic']){
                $this->error('操作台图片必须上传！');exit;
            }
            if(!$data['nb_pic']){
                $this->error('内部图片必须上传！');exit;
            }       
            if(!$data['goods_describe']){
                $this->error('请填写叉车描述！');exit;
            }                              
            // M('Goods')->startTrans();

            if (isset($data['goods_id']) && $data['goods_id'] !='') {

                $res = M('Goods')->save($data);

                // if ($res) {
                //     $bzzj['user_bzzj'] = rentCount(1,$data['goods_id'],12,1800);
                //     $bzzj['car_bzzj']  = rentCount(2,$data['goods_id'],12,1800);                
                //     $bzzj['goods_id']  = $data['goods_id'];

                //     if($bzzj['user_bzzj'] > 0 && $bzzj['car_bzzj'] > 0){
                //         $goods_res = M('Goods')->save($bzzj);
                //         M('Goods')->commit();
                //         $this->success('修改成功',U('Goods/goodsList'));
                //         exit;
                //     }else{
                //         $this->error('参数有误，系统无法计算标准租金，请检查吨位、车型号、车种、产地、售价等参数！');
                //     }
                // } else {
                //     $this->error('修改失败');
                // }                
                // M('Goods')->rollback();
                // exit;
            }else{
                $res = M('Goods')->add($data);
            }


            //dump($res);exit;
            // if ($res) {
            //     $bzzj['user_bzzj'] = rentCount(1,$res,12,1800);
            //     $bzzj['car_bzzj'] = rentCount(2,$res,12,1800);
            //     $bzzj['goods_id'] = $res;
            //     if($bzzj['user_bzzj'] > 0 && $bzzj['car_bzzj'] > 0){
            //         $goods_res = M('Goods')->save($bzzj);
            //         M('Goods')->commit();
            //         $this->success('添加成功','goodsList');
            //         exit;
            //     }else{
            //         $this->error('参数有误，系统无法计算标准租金，请检查吨位、车型号、车种、产地、售价等参数！');
            //     }
            // } else {
            //     $this->error('添加失败');
            // }
            if($res === false){
                $this->error('操作失败，请检查参数！');
            }else{
                $this->success('操作成功！');
            }
            // M('Goods')->rollback();
            exit;
        }

        $goods_id  = I('get.id');
        $goodsInfo = M("Goods")->where(array('goods_id'=>$goods_id))->find();
        $cat_list  = M('CartArt')->select(array('index'=>'id'));
        // $dunwei = $this->getGoodsAttr(2);
        // $cart_type = $this->getGoodsAttr(3);
        // $menjia = $this->getGoodsAttr(4);
        // $mj_height = $this->getGoodsAttr(5);
        // $chezhong = $this->getGoodsAttr(25);
        // $chexing = $this->getGoodsAttr(44);
        // $bydc = $this->getGoodsAttr(49);

        $pinpai = getCat(0);

        if($goods_id){
            $LevelCat = getLevelCat($goodsInfo['cid']);
            $cart_type = getCat($LevelCat[1]['id']);
            $dunwei    = getCat($LevelCat[2]['id']);        
            $menjia    = getCat($LevelCat[3]['id']);        
            $mj_height = getCat($LevelCat[4]['id']);        
        }

        $this->assign('LevelCat',$LevelCat);
        $this->assign('pinpai',$pinpai);
        $this->assign('dunwei',$dunwei);
        $this->assign('cart_type',$cart_type);
        $this->assign('menjia',$menjia);
        $this->assign('mj_height',$mj_height);
        // $this->assign('shuju',$shuju);
        // $this->assign('chezhong',$chezhong);
        // $this->assign('chexing',$chexing);
        // dump($bydc);
        // $this->assign('bydc',$bydc);
        $this->assign('goodsInfo',$goodsInfo);
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'article')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article')));
        $this->assign("URL_Home", "");
        $this->display('_goods');

    } 

    //验证分类分支是否合理
    public function verifyCat($data)
    {
        if(!$data['pinpai']){
            $this->error('请选择品牌！');exit;
        }

        if(!$data['cart_type']){
            $this->error('请选择车类型！');exit;
        }

        if(!$data['dunwei']){
            $this->error('请选择吨位！');exit;
        }

        if(!$data['menjia']){
            $menjia = M('CarCate')->where(['name'=>'二节门架','parent_id'=>$data['dunwei']])->find();
            if(!$menjia){
                $this->error('该分支缺少默认门架类型，请到分类管理手动增加！');exit;
            }else{
                $data['menjia'] = $menjia['id'];
            }
        }

        if(!$data['mj_height']){
            $mj_height = M('CarCate')->where(['name'=>'3000','parent_id'=>$data['menjia']])->find();
            if(!$mj_height){
                $this->error('该分支缺少默认门架提升高度，请到分类管理手动增加！');exit;
            }else{
                $data['mj_height'] = $mj_height['id'];
            }
        }

        $cat_list = getLevelCat($data['mj_height']);

        $sign1 = $cat_list[1]['id'] .'/'. $cat_list[2]['id'] .'/'. $cat_list[3]['id'] .'/'. $cat_list[4]['id'] .'/'. $cat_list[5]['id'];

        $sign2 = $data['pinpai'] .'/'. $data['cart_type'] .'/'. $data['dunwei'] .'/'. $data['menjia'] .'/'. $data['mj_height'];

        if($sign1 === $sign2){
            $data['pinpai']    = $cat_list[1]['name'];
            $data['cart_type'] = $cat_list[2]['name'];
            $data['dunwei']    = $cat_list[3]['name'];
            $data['menjia']    = $cat_list[4]['name'];
            $data['mj_height'] = $cat_list[5]['name'];
            $data['cid']       = $cat_list[5]['id'];
            return $data;
        }else{
            return false;
        }
    }

    /**
     * 添加修改商品
     */
    public function addEditPrefer()
    {
        if (IS_POST) {
            $data = I('post.');
            $data = array_filter($data);
            $data['goods_name'] = I('post.goods_name','',false);
            $data['is_prefer'] = 1;
            $data['add_time']=time();
            //上传图片
            if($_FILES){
                //定义上传路径
                $path="./Public/Upload/cart/";
                $uploadinfo=$this->fileUploadNews($path,$_FILES);
                $uploadinfo['zm_pic']?$data['zm_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['zm_pic']:false;
                // $uploadinfo['cm_pic']?$data['cm_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['cm_pic']:false;
                // $uploadinfo['czt_pic']?$data['czt_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['czt_pic']:false;
                // $uploadinfo['nb_pic']?$data['nb_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['nb_pic']:false;
            }

            if($data['bzzj'] <= $data['preferential']){
                $this->error('原租金必须大于优惠价');exit;
            }

            if (isset($data['goods_id']) && $data['goods_id'] !='') {
                $res = M('Goods')->save($data);
            }else{
                $res = M('Goods')->add($data);
            }

            if($res){
                $this->success('操作成功');
            }else{
                $this->success('操作失败');
            }
                exit;
        }

        $goods_id = I('get.id');
        $goodsInfo = M("Goods")->field('bydc,is_prefer,goods_id,preferential,is_yt,pinpai,dunwei,menjia,mj_height,shuju,goods_name,shoujia,chejia,yajin,khdzj,dcj,cb,bzzj,hdccj,chexing,chezhong,cart_age,huoqi,description,use_hours,dcsj,zm_pic,cm_pic,czt_pic,nb_pic,content,origin,goods_describe')->where(array('goods_id'=>$goods_id))->find();

        // dump($bydc);
        $this->assign('bydc',$bydc);
        $this->assign('goodsInfo',$goodsInfo);
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'article')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article')));
        $this->assign("URL_Home", "");
        $this->display('_prefer');

    } 

    /**
     * 添加修改叉车参数
     */
    public function addEditParam()
    {
        if (IS_POST) {
            $data = I('post.');
            $data = array_filter($data);
            $data['is_prefer'] = 2;
            $data['add_time'] = time();
            // dump($_FILES);exit;
            //上传图片
            if($_FILES){
                //定义上传路径
                $path="./Public/Upload/cart/";
                $uploadinfo=$this->fileUploadNews($path,$_FILES);
                $uploadinfo['zm_pic']?$data['zm_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['zm_pic']:false;
                // $uploadinfo['cm_pic']?$data['cm_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['cm_pic']:false;
                // $uploadinfo['czt_pic']?$data['czt_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['czt_pic']:false;
                // $uploadinfo['nb_pic']?$data['nb_pic']=APP_URL.'/Public/Upload/cart/'. $uploadinfo['nb_pic']:false;
            }

            if (isset($data['goods_id']) && $data['goods_id'] !='') {
                $res = M('Goods')->save($data);
            }else{
                $res = M('Goods')->add($data);
            }

            if($res){
                $this->success('操作成功');
            }else{
                $this->success('操作失败');
            }
                exit;
        }

        $goods_id = I('get.id');
        $goodsInfo = M("Goods")->field('bydc,is_prefer,goods_id,preferential,is_yt,pinpai,cart_type,dunwei,menjia,mj_height,shuju,goods_name,shoujia,chejia,yajin,khdzj,dcj,cb,bzzj,hdccj,chexing,chezhong,cart_age,huoqi,description,use_hours,dcsj,zm_pic,cm_pic,czt_pic,nb_pic,content,origin,goods_describe')->where(array('goods_id'=>$goods_id))->find();
        $pinpai = $this->getGoodsAttr(1);
        $cart_type = $this->getGoodsAttr(3);
        $this->assign('pinpai',$pinpai);
        $this->assign('dunwei',$dunwei);
        $this->assign('cart_type',$cart_type);
        // dump($goodsInfo);
        $this->assign('bydc',$bydc);
        $this->assign('goodsInfo',$goodsInfo);
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'article')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article')));
        $this->assign("URL_Home", "");
        $this->display('_param');

    } 
          
    /**
     * 商品类型  用于设置商品的属性
     */
    public function goodsTypeList(){
        $model = M("GoodsType");                
        $count = $model->count();        
        $Page  = new Page($count,100);
        $show  = $Page->show();
        $goodsTypeList = $model->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('show',$show);
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display('goodsTypeList');
    }
    
    
    /**
     * 添加修改编辑  商品属性类型
     */
    public  function addEditGoodsType(){        
            $_GET['id'] = $_GET['id'] ? $_GET['id'] : 0;            
            $model = M("GoodsType");           
            if(IS_POST)
            {
                    $model->create();
                    if($_GET['id'])
                        $model->save();
                    else
                        $model->add();
                    
                    $this->success("操作成功!!!",U('Admin/Goods/goodsTypeList'));               
                    exit;
            }           
           $goodsType = $model->find($_GET['id']);
           $this->assign('goodsType',$goodsType);
           $this->display('_goodsType');           
    }
    
    /**
     * 商品属性列表
     */
    public function goodsAttributeList(){       
        $goodsTypeList = M("GoodsType")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    }   
    
    /**
     *  商品属性列表
     */
    public function ajaxGoodsAttributeList(){            
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件                        
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;                
        // 关键词搜索               
        $model = M('GoodsAttribute');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page->show();
        $goodsAttributeList = $model->where($where)->order('`order` desc,attr_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodsTypeList = M("GoodsType")->getField('id,name');
        $attr_input_type = array(0=>'手工录入',1=>' 从列表中选择',2=>' 多行文本框');
        $this->assign('attr_input_type',$attr_input_type);
        $this->assign('goodsTypeList',$goodsTypeList);        
        $this->assign('goodsAttributeList',$goodsAttributeList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }   
    
    /**
     * 添加修改编辑  商品属性
     */
    public  function addEditGoodsAttribute(){
                        
            $model = D("GoodsAttribute");                      
            $type = $_POST['attr_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新         
            $_POST['attr_values'] = str_replace('_', '', $_POST['attr_values']); // 替换特殊字符
            $_POST['attr_values'] = str_replace('@', '', $_POST['attr_values']); // 替换特殊字符            
            $_POST['attr_values'] = trim($_POST['attr_values']);

            if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
            {                
                C('TOKEN_ON',false);
                if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象                 
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '提交不成功!',
                        'data'  => $model->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {                   
                   // C('TOKEN_ON',true); //  form表单提交
                    if ($type == 2)
                    {
                        $model->save(); // 写入数据到数据库                        
                    }
                    else
                    {
                        $insert_id = $model->add(); // 写入数据到数据库                        
                    }
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',                        
                        'data'  => array('url'=>U('Admin/Goods/goodsAttributeList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }  
            }                
           // 点击过来编辑时                 
           $_GET['attr_id'] = $_GET['attr_id'] ? $_GET['attr_id'] : 0;       
           $goodsTypeList = M("GoodsType")->select();           
           $goodsAttribute = $model->find($_GET['attr_id']);           
           $this->assign('goodsTypeList',$goodsTypeList);                   
           $this->assign('goodsAttribute',$goodsAttribute);
           $this->display('_goodsAttribute');           
    }  
    
    /**
     * 更改指定表的指定字段
     */
    public function updateField(){
        $primary = array(
                'goods' => 'goods_id',
                'goods_category' => 'id',
                'brand' => 'id',            
                'goods_attribute' => 'attr_id',
                'ad' =>'ad_id',            
        );        
        $model = D($_POST['table']);
        $model->$primary[$_POST['table']] = $_POST['id'];
        $model->$_POST['field'] = $_POST['value'];        
        $model->save();   
        $return_arr = array(
            'status' => 1,
            'msg'   => '操作成功',                        
            'data'  => array('url'=>U('Admin/Goods/goodsAttributeList')),
        );
        $this->ajaxReturn(json_encode($return_arr));
    }
    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     */
    public function ajaxGetAttrInput(){
        $GoodsLogic = new GoodsLogic();
        $str = $GoodsLogic->getAttrInput($_REQUEST['goods_id'],$_REQUEST['type_id']);
        exit($str);
    }
        
    /**
     * 删除商品
     */
    public function delGoods()
    {
        $goods_id = $_GET['id'];
        $error = '';
        
        // // 判断此商品是否有订单
        // $c1 = M('OrderGoods')->where("goods_id = $goods_id")->count('1');
        // $c1 && $error .= '此商品有订单,不得删除! <br/>';
        
        
        //  // 商品团购
        // $c1 = M('group_buy')->where("goods_id = $goods_id")->count('1');
        // $c1 && $error .= '此商品有团购,不得删除! <br/>';   
        
        //  // 商品退货记录
        // $c1 = M('return_goods')->where("goods_id = $goods_id")->count('1');
        // $c1 && $error .= '此商品有退货记录,不得删除! <br/>';
        
        if($error)
        {
            $return_arr = array('status' => -1,'msg' =>$error,'data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
            $this->ajaxReturn(json_encode($return_arr));            
        }
        
        // 删除此商品        
        M("Goods")->where('goods_id ='.$goods_id)->delete();  //商品表
        M("cart")->where('goods_id ='.$goods_id)->delete();  // 购物车
        M("comment")->where('goods_id ='.$goods_id)->delete();  //商品评论
        M("goods_consult")->where('goods_id ='.$goods_id)->delete();  //商品咨询
        M("goods_images")->where('goods_id ='.$goods_id)->delete();  //商品相册
        M("spec_goods_price")->where('goods_id ='.$goods_id)->delete();  //商品规格
        M("spec_image")->where('goods_id ='.$goods_id)->delete();  //商品规格图片
        M("goods_attr")->where('goods_id ='.$goods_id)->delete();  //商品属性     
        M("goods_collect")->where('goods_id ='.$goods_id)->delete();  //商品收藏          
                     
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
        $this->ajaxReturn(json_encode($return_arr));
    }
    
    /**
     * 删除商品类型 
     */
    public function delGoodsType()
    {
        // 判断 商品规格        
        $count = M("Spec")->where("type_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('该类型下有商品规格不得删除!',U('Admin/Goods/goodsTypeList'));
        // 判断 商品属性        
        $count = M("GoodsAttribute")->where("type_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('该类型下有商品属性不得删除!',U('Admin/Goods/goodsTypeList'));        
        // 删除分类
        M('GoodsType')->where("id = {$_GET['id']}")->delete();   
        $this->success("操作成功!!!",U('Admin/Goods/goodsTypeList'));
    }    

    /**
     * 删除商品属性
     */
    public function delGoodsAttribute()
    {         
        // 判断 有无商品使用该属性
        $count = M("GoodsAttr")->where("attr_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('有商品使用该属性,不得删除!',U('Admin/Goods/goodsAttributeList'));                        
        // 删除 属性
        M('GoodsAttribute')->where("attr_id = {$_GET['id']}")->delete();   
        $this->success("操作成功!!!",U('Admin/Goods/goodsAttributeList'));
    }            
    
    /**
     * 删除商品规格
     */
    public function delGoodsSpec()
    {
        // 判断 商品规格项
        $count = M("SpecItem")->where("spec_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('清空规格项后才可以删除!',U('Admin/Goods/specList'));
        // 删除分类
        M('Spec')->where("id = {$_GET['id']}")->delete();   
        $this->success("操作成功!!!",U('Admin/Goods/specList'));
    } 
    
    /**
     * 品牌列表
     */
    public function brandList(){  
        $model = M("Brand"); 
        $where = "";
        $keyword = I('keyword');
        $where = $keyword ? " name like '%$keyword%' " : "";
        $count = $model->where($where)->count();
        $Page  = new Page($count,10);        
        $brandList = $model->where($where)->order("`sort` asc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page->show(); 
        $cat_list = M('goods_category')->where("parent_id = 0")->getField('id,name'); // 已经改成联动菜单
        $this->assign('cat_list',$cat_list);       
        $this->assign('show',$show);
        $this->assign('brandList',$brandList);
        $this->display('brandList');
    }
    
    /**
     * 添加修改编辑  商品品牌
     */
    public  function addEditBrand(){        
            $id = I('id');
            $model = M("Brand");           
            if(IS_POST)
            {
                    $model->create();
                    if($id)
                        $model->save();
                    else
                        $id = $model->add();
                    
                    $this->success("操作成功!!!",U('Admin/Goods/brandList',array('p'=>$_GET['p'])));               
                    exit;
            }           
           $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
           $this->assign('cat_list',$cat_list);           
           $brand = $model->find($id);
           $this->assign('brand',$brand);
           $this->display('_brand');

    }
    
    /**
     * 删除品牌
     */
    public function delBrand()
    {        
        // 判断此品牌是否有商品在使用
        $goods_count = M('Goods')->where("brand_id = {$_GET['id']}")->count('1');        
        if($goods_count)
        {
            $return_arr = array('status' => -1,'msg' => '此品牌有商品在用不得删除!','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
            $this->ajaxReturn(json_encode($return_arr));            
        }
        
        $model = M("Brand"); 
        $model->where('id ='.$_GET['id'])->delete(); 
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
        $this->ajaxReturn(json_encode($return_arr));
    }      
    
    /**
     * 初始化编辑器链接     
     * 本编辑器参考 地址 http://fex.baidu.com/ueditor/
     */
    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'goods'))); // 图片上传目录
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article'))); //  不知道啥图片
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article'))); // 文件上传s
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));  //  图片流
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article'))); // 远程图片管理
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article'))); // 图片管理        
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article'))); // 视频上传
        $this->assign("URL_Home", "");
    }    
    
    
    
    /**
     * 商品规格列表    
     */
    public function specList(){       
        $goodsTypeList = M("GoodsType")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    }
    
    
    /**
     *  商品规格列表
     */
    public function ajaxSpecList(){ 
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件                        
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;        
        // 关键词搜索               
        $model = D('spec');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page->show();
        $specList = $model->where($where)->order('`type_id` desc')->limit($Page->firstRow.','.$Page->listRows)->select();        
        $GoodsLogic = new GoodsLogic();        
        foreach($specList as $k => $v)
        {       // 获取规格项     
                $arr = $GoodsLogic->getSpecItem($v['id']);
                $specList[$k]['spec_item'] = implode(' , ', $arr);
        }
        
        $this->assign('specList',$specList);
        $this->assign('page',$show);// 赋值分页输出
        $goodsTypeList = M("GoodsType")->select(); // 规格分类
        $goodsTypeList = convert_arr_key($goodsTypeList, 'id');
        $this->assign('goodsTypeList',$goodsTypeList);        
        $this->display();         
    }      
    /**
     * 添加修改编辑  商品规格
     */
    public  function addEditSpec(){
                        
            $model = D("spec");                      
            $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新             
            if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
            {                
                C('TOKEN_ON',false);
                if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象                 
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '',
                        'data'  => $model->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {                   
                   // C('TOKEN_ON',true); //  form表单提交
                    if ($type == 2)
                    {
                        $model->save(); // 写入数据到数据库                        
                        $model->afterSave($_POST['id']);
                    }
                    else
                    {
                        $insert_id = $model->add(); // 写入数据到数据库        
                        $model->afterSave($insert_id);
                    }                    
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',                        
                        'data'  => array('url'=>U('Admin/Goods/specList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }  
            }                
           // 点击过来编辑时                 
           $id = $_GET['id'] ? $_GET['id'] : 0;       
           $spec = $model->find($id);
           $GoodsLogic = new GoodsLogic();  
           $items = $GoodsLogic->getSpecItem($id);
           $spec[items] = implode(PHP_EOL, $items); 
           $this->assign('spec',$spec);
           
           $goodsTypeList = M("GoodsType")->select();           
           $this->assign('goodsTypeList',$goodsTypeList);           
           $this->display('_spec');           
    }  
    
    
    /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;        
        $GoodsLogic = new GoodsLogic();
        //$_GET['spec_type'] =  13;
        $specList = D('Spec')->where("type_id = ".$_GET['spec_type'])->order('`order` desc')->select();
        foreach($specList as $k => $v)        
            $specList[$k]['spec_item'] = D('SpecItem')->where("spec_id = ".$v['id'])->order('id')->getField('id,item'); // 获取规格项                
        
        $items_id = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);       
        
        // 获取商品规格图片                
        if($goods_id)
        {
           $specImageList = M('SpecImage')->where("goods_id = $goods_id")->getField('spec_image_id,src');                 
        }        
        $this->assign('specImageList',$specImageList);
        
        $this->assign('items_ids',$items_ids);
        $this->assign('specList',$specList);
        $this->display('ajax_spec_select');        
    }    
    
    /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */    
    public function ajaxGetSpecInput(){     
         $GoodsLogic = new GoodsLogic();
         $goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
         $str = $GoodsLogic->getSpecInput($goods_id ,$_POST['spec_arr']);
         exit($str);   
    }
    
    /**
     * 删除商品相册图
     */
    public function del_goods_images()
    {
        $path = I('filename','');
        M('goods_images')->where("image_url = '$path'")->delete();
    }

    /*******************分类管理*****************/

    //一级分类
    public function level1()
    {
        $this->display();
    }

    //添加品牌 view
    public function edit_pinpai()
    {
        $id = I('id');
        $catInfo  = M('CarCate')->where(['id'=>$id])->find();
        $cat_list = M('CartArt')->where(['parent_id'=>1])->select();
        $this->assign('catInfo',$catInfo);
        $this->assign('cat_list',$cat_list);
        $this->display();
    }

    //删除分类
    public function delCat()
    {
        $id = I('id');

        if(!$id){
            exit(json_encode(['status'=>1,'msg'=>'删除成功！']));
        }        

        $count = M('CarCate')->where(['parent_id'=>$id])->count();

        if($count > 0){
            exit(json_encode(['status'=>-1,'msg'=>'删除失败，该分类下有子类']));
        }

        $goods_count = M('Goods')->where(['cid'=>$id])->count();
        if($goods_count > 0){
            exit(json_encode(['status'=>-1,'msg'=>'删除失败，该分类下有叉车']));
        }        

        $res = M('CarCate')->delete($id);

        if($res){
            exit(json_encode(['status'=>1,'msg'=>'删除成功！']));
        }else{
            exit(json_encode(['status'=>-1,'msg'=>'删除失败！']));
        }

    }

    //新增编辑分类
    public function editCarCate()
    {
        $data = I('post.');
        $data['money'] ? false : $data['money'] = '0.00';

        if(!(is_numeric($data['money'])&&$data['money']>=0)){
            $this->error('请正确填写标准价格！');exit;
        }

        if($data['level'] == 2){
            $pinpai = M('CarCate')->where(['id'=>$data['parent_id']])->find();
            if($data['chexing'] != 'FD/G'&&$pinpai['type'] == 1){
                $this->error('国产叉车没有FB、FBR、FBRE车型');exit;
            }
        }

        //编辑
        if($data['id']){
            $res = M('CarCate')->save($data);
        }else{
        //新增
            $res = M('CarCate')->add($data);
        }

        if($res === false){
            $this->error('操作失败！');
        }else{
            $this->success('操作成功！');
        }
    }

    //新增编辑吨位分类
    public function editCarCateDunwei()
    {
        $data = I('post.');
        $data['money'] ? false : $data['money'] = '0.00';
        $data['battery_fee'] ? false : $data['battery_fee'] = '0.00';
        $data['tire_fee'] ? false : $data['tire_fee'] = '0.00';

        if(!$data['parent_id']){
            $this->error('请选择上级分类！');exit;
        }

        $cart_type = M('CarCate')->where(['id'=>$data['parent_id']])->find();

        if(!$cart_type['chexing']){
            $this->error('上级分类异常！');exit;
        }

        $pinpai = M('CarCate')->where(['id'=>$cart_type['parent_id']])->find();

        if(!$pinpai){
            $this->error('请选择上级分类！');exit;
        }        

        if(!(is_numeric($data['money'])&&$data['money']>=0)){
            $this->error('请正确填写标准价格！');exit;
        }

        //吨位 车类型 产地检测
        switch($cart_type['chexing']){
            case 'FD/G':
                if($pinpai['type'] == 1){
                    if($data['name'] < 2 || $data['name'] > 10){
                        $this->error('国产FD/G吨位范围2-3.5 4-5.5 6-10');exit;
                    }
                    if($data['name'] > 3.5 && $data['name'] < 4){
                        $this->error('国产FD/G吨位范围2-3.5 4-5.5 6-10');exit;
                    }
                    if($data['name'] > 5.5 && $data['name'] < 6){
                        $this->error('国产FD/G吨位范围2-3.5 4-5.5 6-10');exit;
                    }                                        
                }else{
                    if($data['name'] < 2 || $data['name'] > 5.5){
                        $this->error('进口FD/G吨位范围2-3.5 4-5.5');exit;
                    }
                    if($data['name'] > 3.5 && $data['name'] < 4){
                        $this->error('进口FD/G吨位范围2-3.5 4-5.5');exit;
                    }
                }
            break;
            case 'FB':
                if($data['name'] < 1 || $data['name'] > 5){
                    $this->error('进口FB吨位范围1-3 3.5-5');exit;
                }
                if($data['name'] > 3 && $data['name'] < 3.5){
                    $this->error('进口FB吨位范围1-3 3.5-5');exit;
                }
            break;
            case 'FBR':
                if($data['name'] < 1 || $data['name'] > 2.5){
                    $this->error('进口FBR吨位范围1-2.5');exit;
                }
            break;
            case 'FBRE':
                if($data['name'] < 1.4 || $data['name'] > 2){
                    $this->error('进口FBRE吨位范围1.4-2');exit;
                }
            break;
            default:
                $this->error('没有该车类型！');exit;
        }

        //编辑
        if($data['id']){
            $res = M('CarCate')->save($data);
        }else{
        //新增
            $res = M('CarCate')->add($data);

            if($res){
                $menjia['parent_id'] = $res;
                $menjia['name'] = '二节门架';
                $menjia['level'] = 4;
                $res2 = M('CarCate')->add($menjia);
                if($res2){
                    $mj_height['parent_id'] = $res2;
                    $mj_height['name']  = '3000';
                    $mj_height['level'] = 5;
                    $res3 = M('CarCate')->add($mj_height);
                }
            }
        }

        if($res === false){
            $this->error('操作失败！');
        }else{
            $this->success('操作成功！');
        }
    }

    //分类列表
    public function ajaxLevel()
    {
        $parent_id = I('parent_id',0);
        $where['parent_id'] = $parent_id;
        $count = M('CarCate')->where($where)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $show = $Page->show();
        $goodsList = M('CarCate')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出

        if($parent_id > 0){
            $this->assign('parent_id',$parent_id);
            $this->display('ajaxLevel2');
        }else{
            $this->display();
        }
    }

    //二级分类
    public function level2()
    {
        $brandList = M('CarCate')->where(['parent_id'=>0])->select();
        $this->assign('brandList',$brandList);
        $this->display();
    }

    //添加车类型 view
    public function edit_cart()
    {
        $id = I('id');
        $catInfo  = M('CarCate')->where(['id'=>$id])->find();
        $cat_list = M('CarCate')->where(['parent_id'=>0])->order('sort ASC')->select();
        $cart_list = M('CartArt')->where(['parent_id'=>3])->order('sort ASC')->select();
        $chexing  = M('CartArt')->where(['parent_id'=>44])->order('sort ASC')->select();

        $this->assign('chexing',$chexing);
        $this->assign('catInfo',$catInfo);
        $this->assign('cat_list',$cat_list);
        $this->assign('cart_list',$cart_list);
        $this->display();
    }

    //三级分类-吨位
    public function level3()
    {
        $brandList = M('CarCate')->where(['parent_id'=>0])->select();
        $cartList = M('CarCate')->where(['parent_id'=>$brandList[0]['id']])->select();
        $this->assign('cartList',$cartList);
        $this->assign('brandList',$brandList);
        $this->display();
    }

    //分类列表
    public function ajaxLevel3()
    {
        $parent_id = I('parent_id');

        if(!$parent_id){
        	exit;
        }

        $where['parent_id'] = $parent_id;
        $count = M('CarCate')->where($where)->count();
        $Page  = new AjaxPage($count,5);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $show = $Page->show();
        $goodsList = M('CarCate')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('name ASC')->select();

        foreach ($goodsList as $key => $val) {
            $level4 = M('CarCate')->where(['parent_id'=>$val['id']])->select();
            
            foreach ($level4 as $k => $v) {
                $level4[$k]['level5'] = M('CarCate')->where(['parent_id'=>$v['id']])->order('name ASC')->select();
            }
            $goodsList[$key]['level4'] = $level4;   
        }

        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出

        $this->display();
    }

    //添加吨位 view
    public function edit_dunwei()
    {
        $id = I('id');
        $catInfo   = M('CarCate')->where(['id'=>$id])->find();
        $parent    = M('CarCate')->where(['id'=>$catInfo['parent_id']])->find();
        $pinpai    = M('CarCate')->where(['parent_id'=>0])->select();
        $cart_type = M('CarCate')->where(['parent_id'=>$pinpai[0]['id']])->order('sort ASC')->select();
        $dunwei    = M('CartArt')->where(['parent_id'=>2])->order('sort ASC')->select();
 
        $this->assign('chexing',$chexing);
        $this->assign('catInfo',$catInfo);
        $this->assign('parent',$parent);
        $this->assign('pinpai',$pinpai);
        $this->assign('cart_type',$cart_type);
        $this->assign('dunwei',$dunwei);
        $this->display();
    }

    //添加门架 view
    public function edit_menjia()
    {
        $id = I('id');
        if($id){
            $catInfo = M('CarCate')->where(['id'=>$id])->find();
            $parent_id = $catInfo['parent_id'];
            $this->assign('catInfo',$catInfo);
        }else{
            $parent_id = I('parent_id');
        }
        $parent = M('CarCate')->where(['parent_id'=>$parent_id])->select();

        $menjia = M('CartArt')->where(['parent_id'=>4])->order('sort ASC')->select();

        $this->assign('parent_id',$parent_id);
        $this->assign('menjia',$menjia);
        $this->assign('cart_type',$cart_type);
        $this->assign('dunwei',$dunwei);
        $this->display();
    }

    //添加门架 view
    public function edit_mj_height()
    {
        $id = I('id');
        if($id){
            $catInfo = M('CarCate')->where(['id'=>$id])->find();
            $parent_id = $catInfo['parent_id'];
            $this->assign('catInfo',$catInfo);
        }else{
            $parent_id = I('parent_id');
        }
        $parent = M('CarCate')->where(['parent_id'=>$parent_id])->select();

        $mj_height = M('CartArt')->where(['parent_id'=>5])->order('sort ASC')->select();

        $this->assign('parent_id',$parent_id);
        $this->assign('mj_height',$mj_height);
        $this->assign('cart_type',$cart_type);
        $this->assign('dunwei',$dunwei);
        $this->display();
    }

    //根据pid 获取子分类
    public function getCat()
    {
        $parent_id = I('parent_id',0);
        $list = M('CarCate')->where(['parent_id'=>$parent_id])->order('name ASC')->select();
        foreach ($list as $key => $val) {
            $count = M('CarCate')->where(['parent_id'=>$val['id']])->order('name ASC')->select();
            if($count < 1){
                unset($list[$key]);
            }
        }        
        exit(json_encode(['status'=>1,'msg'=>'成功','list'=>$list]));
    }

    public function testLevel()
    {
    	$res = rent(130);
    	rent(240,6,1000);
    }    
}