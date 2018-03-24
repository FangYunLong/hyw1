<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Home2\Controller;
use Think\Controller;
class BaseController extends Controller {
    public $session_id;
    public $cateTrre = array();
    public $user;
    /*
     * 初始化操作
     */
    public function _initialize() {
      
      $array = ['Goods/goodsInfo','Index/index','GoodsPrivate','Goods/goodsRent'];

      if(in_array(CONTROLLER_NAME."/".ACTION_NAME,$array)){
        $this->cache_base();
      }

      if(in_array(CONTROLLER_NAME,array('User','Pay','GoodsPrivate'))){
          if(!session('user')){
            session('prev_url',$_SERVER['HTTP_REFERER'],60*3);
            if(IS_AJAX){
              exit(json_encode(['status'=>-2,'登录才能访问！']));
            }else{
              header("location:".U('Home/Login/login'));
            }
          }
      }

      $config = tpCache('shop_info');

      $this->base_assign();
      $this->headerCategory();
// dump(ACTION_NAME);
      $this->user = session('user');
      $this->assign('action',ACTION_NAME);
      $this->assign('users',$this->user);
      $this->assign('shuju',session('shuju'));
      $this->assign('config',$config);
    	$this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        // 判断当前用户是否手机                
        if(isMobile())
            cookie('is_mobile','1',3600); 
        else 
            cookie('is_mobile','0',3600);
                  
        $this->public_assign(); 
    }
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign()
    {
       $tpshop_config = array();
       $tp_config = M('config')->cache(true,TPSHOP_CACHE_TIME)->select();       
       foreach($tp_config as $k => $v)
       {
       	  if($v['name'] == 'hot_keywords'){
       	  	 $tpshop_config['hot_keywords'] = explode('|', $v['value']);
       	  }       	  
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }                        
       
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,TPSHOP_CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();              
       $this->assign('brand_list', $brand_list);
       $this->assign('tpshop_config', $tpshop_config);          
    }

    public function getIPaddress()
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


    //头文件分类二级栏目
    public function headerCategory()
    {
      $art_cat['parent_id'] = 1;
      //资讯中心二级分类
      // $art_cat = M('ArticleCat')->where($art_cat)->order('sort_order ASC')->limit(6)->select();
      $art_cat = M('ArticleCat')->alias('ac')->join('tp_article as a on ac.cat_id = a.cat_id')->where($art_cat)->order('ac.sort_order ASC')->group('ac.cat_name')->limit(6)->select();

      $about_cat['parent_id'] = 4;
      $about_cat = M('ArticleCat')->alias('ac')->join('tp_article as a on ac.cat_id = a.cat_id')->where($about_cat)->order('ac.sort_order ASC')->group('ac.cat_name')->limit(6)->select();
      $this->assign('art_cat',$art_cat);
      $this->assign('about_cat',$about_cat);
    }

    //清空缓存
    public function cache_base()
    {
      delFile('./Application/Runtime');
      $html_arr = glob("./Application/Runtime/Html/*.html");
      foreach ($html_arr as $key => $val)
      {
          strstr($val,'Home_Index_index.html') && unlink($val); // 首页                    
          strstr($val,'Home_Goods_goodsList') && unlink($val); // 列表页
          strstr($val,'Home_Channel_index') && unlink($val);  // 频道页
          strstr($val,'Index_Article_articleList') && unlink($val);  // 文章列表页
          strstr($val,'Index_Article_detail') && unlink($val);  // 文章详情
          strstr($val,'Doc_Index_index_') && unlink($val);  // 文章列表页                    
          strstr($val,'Doc_Index_article_') && unlink($val);  // 文章详情                                        
      } 
    }

    public function base_assign()
    {
      $this->assign('sex',C('sex'));
      $this->assign('xueli',C('xueli'));
      $this->assign('jingyan',C('jingyan'));
      $this->assign('is_hidden',['隐藏简历','公开简历']);
    }
}