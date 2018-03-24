<?php
/**
 *
//
* IT宇宙人 2015-08-10 $
 */ 
namespace Home2\Controller;
use Home2\Logic\CartLogic;
use Home2\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;
class NewsController extends BaseController {
    public function index(){      
        $this->display();
    }

    //文章列表
    public function articleList()
    {
        $Ad = M('Ad')->where(['ad_id'=>78])->find();
        $this->assign('ad',$Ad);
        $cat_id = I('cat_id');
        $page = I('page',1);
        $rows = I('rows',10);

        $count = M('Article')->where(['cat_id'=>$cat_id])->count();

        if($count < 1){
            exit;
        }

        $pages = ceil($count / $rows) ;
        $page < 1 ? $page = 1 : null;
        $page > $pages ? $page = $pages : null;

        $ArticleCat = M('ArticleCat')->find($cat_id);

        $Article = M('Article')->where(['cat_id'=>$cat_id,'is_open'=>1])->order('add_time DESC')->page($page,$rows)->select();
        $this->assign('list',$Article);
        $this->assign('page',['cat_id'=>$cat_id,'cat_name'=>$ArticleCat['cat_name'],'page'=>$page,'pages'=>$pages]);
        $this->assign('pageRows',pageRows($page,$pages));
        $this->display();
    }

    //文章详情
    public function articleInfo()
    {
        $article_id = I('article_id');
        M('')->query('UPDATE tp_article SET click=click + 1 WHERE article_id='.$article_id);
        $article = M('Article')->find($article_id);
        $next = M('Article')->where(['article_id'=>['LT',$article_id],'cat_id'=>$article['cat_id']])->order('article_id DESC')->limit(1)->select()[0];
        $prev = M('Article')->where(['article_id'=>['GT',$article_id],'cat_id'=>$article['cat_id']])->order('article_id ASC')->limit(1)->select()[0];
        // dump($prev);
        $ad= M("Ad")->where("ad_id=78")->find;
        $this->assign('$ad',$ad);
        $this->assign('next',$next);
        $this->assign('prev',$prev);
        $this->assign('article',$article);
        $this->display();
    }

    //关于我们
    public function about()
    {
        $cat_id = I('cat_id');
        $article = M('Article')->where(['cat_id'=>$cat_id])->find();
        $article_id = $article['article_id'];
        M('')->query('UPDATE tp_article SET click=click + 1 WHERE article_id='.$article_id);

        $parent = M('ArticleCat')->find($cat_id);
        $parent_id = $parent['parent_id'];
        $article['cat_name'] = $parent['cat_name'];
        $next_cat_id = M('ArticleCat')->where(['cat_id'=>['LT',$cat_id],'parent_id'=>$parent_id])->order('cat_id DESC')->limit(1)->select()[0]['cat_id'];
        $prev_cat_id = M('ArticleCat')->where(['cat_id'=>['GT',$cat_id],'parent_id'=>$parent_id])->order('cat_id ASC')->limit(1)->select()[0]['cat_id'];
        
        $next = M('Article')->where(['cat_id'=>$next_cat_id])->find();
        $prev = M('Article')->where(['cat_id'=>$prev_cat_id])->find();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('next',$next);
        $this->assign('prev',$prev);
        $this->assign('article',$article);        
        $this->display();
    }

    /**
     *  市场动态
     */
    public function news(){
        $Ad = M('Ad')->where(['ad_id'=>78])->find();
        $this->assign('ad',$Ad);
       // $news = M("Article")->where("cat_id = 89")->select();
        $count = M('Article')->where(" 1=1 and cat_id = 89 or cat_id =90 or cat_id = 97 or cat_id =98")->count();
        $where = "1=1 and cat_id = 89 or cat_id =90 or cat_id = 97 or cat_id =98"; //or cat_id =90 or cat_id = 97 or cat_id =98
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $news = M('Article')->alias('a')
                    //->join('tp_article_cat as b on a.cat_id = b.cat_id')
                    //->field("a.*,b.cat_name")
                    ->where($where)
                    ->limit($Page->firstRow.','.$Page->listRows)
                    ->order('add_time DESC')
                    ->select();
        //$this->assign('orderList',$orderList);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('news',$news);
        $this->display();
    }

    /**
     *  租赁问答
     */
    public function news1(){
        $Ad = M('Ad')->where(['ad_id'=>78])->find();
        $this->assign('ad',$Ad);

        $page = I('page',1);
        $rows = I('rows',6);

        $count = M("Article")->where("cat_id = 101 and ask != '' ")->count();

        if($count < 1){
            $this->assign('pageRows',[1]);
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->display();
            exit;
        }

        $pages = ceil($count / $rows);

        $page < 1 ? $page = 1 : null;

        $page > $pages ? $page = $pages : null;

        $Article = M('Article')->where('cat_id = 101 and ask != "" ')->page($page,$rows)->select();

        $this->assign('Article',$Article);
        $this->assign('pageRows',pageRows($page,$pages));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();
    }
    /**
     *  叉车保养知识
     */
    public function news2(){
        $upkeepInfo = M("Article")->where("cat_id = 87")->select();
        $this->assign('upkeepInfo',$upkeepInfo);
        $this->display();
    }

    /**
     *  叉车故障排除
     */
    public function news3(){
        $fault_exclude = M("Article")->where("cat_id = 88")->select();
        $this->assign('fault_exclude',$fault_exclude);
        $this->display();
    }

    /**
     *  行业新闻
     */
    public function trade_news(){
        $tradeInfo = M("Article")->where("cat_id = 89")->select();
        $this->assign('trade',$tradeInfo);
        $this->display();
    }

    /**
     *  公司新闻
     */
    public function company_news(){
        $companyInfo = M("Article")->where("cat_id = 90")->select();
        $this->assign('company',$companyInfo);
        $this->display();
    }

    //公告动态
    public function dynamic()
    {   
        $companyInfo = M("Article")->where("cat_id = 5")->order('add_time DESC')->select();
        $this->assign('company',$companyInfo);
        $this->display();}

    /**
     *  叉车百科
     */
    public function car_ency(){
        $Article =M('Article');
        $car_ency = M("Article")->where("cat_id = 91")->order('article_id asc')->limit('1')->select();
        $article_id = $car_ency[0]['article_id'];
        //上一篇文章
        $front=$Article->where("article_id>".$article_id)->order('article_id asc')->limit('1')->find();
        $fid= !$front ? '没有了': $front['article_id'];
        $this->assign('front',$front);
        $this->assign('fid',$fid);
        //下一篇文章
        $after=$Article->where("article_id<".$article_id)->order('article_id desc')->limit('1')->find();
        $aid= !$after ? '没有了': $after['article_id'];
        $this->assign('after',$after);
        $this->assign('aid',$aid);

        $this->assign('carEncy',$car_ency);
        $this->display();
    }

    /**
     *  叉车证
     */
    public function car_card(){
        $car_card = M("Article")->where("cat_id = 92")->select();
        $article_id = $car_card[0]['article_id'];
        //上一篇文章
        $Article =M("Article");
        $front=$Article->where("article_id>".$article_id)->order('article_id asc')->limit('1')->find();
        $fid= !$front ? '没有了': $front['article_id'];
        $this->assign('front',$front);
        $this->assign('fid',$fid);
        //下一篇文章
        $after=$Article->where("article_id<".$article_id)->order('article_id desc')->limit('1')->find();
        $aid= !$after ? '没有了': $after['article_id'];
        $this->assign('after',$after);
        $this->assign('aid',$aid);
        $this->assign('car_card',$car_card);
        $this->display();
    }

    /**
     *  叉车上牌
     */
    public function car_brand(){
        $car_brand = M("Article")->where("cat_id = 93")->select();
        $article_id = $car_brand[0]['article_id'];
        //上一篇文章
        $Article =M("Article");
        $front=$Article->where("article_id>".$article_id)->order('article_id asc')->limit('1')->find();
        $fid= !$front ? '没有了': $front['article_id'];
        $this->assign('front',$front);
        $this->assign('fid',$fid);
        //下一篇文章
        $after=$Article->where("article_id<".$article_id)->order('article_id desc')->limit('1')->find();
        $aid= !$after ? '没有了': $after['article_id'];
        $this->assign('after',$after);
        $this->assign('aid',$aid);
        $this->assign('car_brand',$car_brand);
        $this->display();
    }

    /**
     *  下载专区
     */
    public function download(){
        $Ad = M('Ad')->where(['ad_id'=>78])->find();
        $this->assign('ad',$Ad);
        $rows = I('rows',8);
        $page = I('page',1);
        $count = M('Topic')->count();
        if($count < 1){
            $this->assign('page',['page'=>0,'pages'=>0]);
            $this->display();
            exit;
        }
        $pages = ceil($count / $rows);
        $page < 1 ? $page = 1 : null ;
        $page > $pages ? $page = $pages : null ;
        $num     = ($page-1)*$rows;        
        
        $Topic = M('Topic')->order('ctime DESC')->limit($num,$rows)->select();
        $this->assign('Topic',$Topic);
        $this->assign('pageRows',pageRows($page,$pages,6));
        $this->assign('page',['page'=>$page,'pages'=>$pages]);
        $this->display();
    }

    //下载文件
    public function downloaded()
    {
        $Topic = M('Topic')->find(I('topic_id'));
        $filepath = $Topic['topic_image'];
        // echo $filepath;exit;
        $filename = iconv("utf-8","gb2312",$filepath);
        //打开资源
        $finfo = finfo_open(FILEINFO_MIME);

        // 获取mime类型
        $mime = finfo_file($finfo,$filename);
        //关闭资源
        finfo_close($finfo);
        
        //获取文件的文件名
        $basename = pathinfo($filename);

        $type = explode('.',$basename["basename"])[1];

        //指定下载文件类型的
        header('Content-Type:' . $mime ); 

        //设置head头信息，告知该文件时下载附件并且制定客户端临时存储名称
        header("Content-Disposition:attachment;filename=".  $Topic["topic_title"].'.'.$type);
        //指定下载文件的描述信息
        //指定文件大小的
        header("Content-Length:".filesize($filename));  

        //将内容输出，以便下载。
        readfile($filename);
    } 

   /**
    *  文章详情页
    */ 
    public function article_Info($article_id){
        $Article = M("Article");
        $articleInfo = M("Article")->alias('a')
            ->join('tp_article_cat as b on a.cat_id = b.cat_id')
            ->field('a.*,b.cat_name')
            ->where("article_id = $article_id")
            ->select();
        $data['click'] = mt_rand(1000,1300);
        $r = D('article')->add($data);
        //上一篇文章
        $front=$Article->where("article_id>".$article_id)->order('article_id asc')->limit('1')->find();
        $fid= !$front ? '没有了': $front['article_id'];
        $this->assign('front',$front);
        $this->assign('fid',$fid);
        //下一篇文章
        $after=$Article->where("article_id<".$article_id)->order('article_id desc')->limit('1')->find();
        $aid= !$after ? '没有了': $after['article_id'];
        $ad = M("Ad")->where("ad_id=78")->find();
        $this->assign('ad',$ad);
        $this->assign('aid',$aid);
        //dump($after);
        $this->assign('after',$after);

        $this->assign('articleInfo',$articleInfo);
        $this->display();
    }

    //获取省/市资料
    public function get_city()
    {
        $id = I('id',0);
        $type = I('type');
        $list = M('Region')->where(['parent_id'=>$id])->select();
        // dump($id);
        // dump($list);exit;
        if($type == 4){
            $Resume = M('Resume')->where(['user_id'=>$this->user['user_id']])->find();
            $province['name'] = ['like',"%{$Resume['province']}%"];
            $province['parent_id'] = 0;
            // dump($Resume);
            $address['province'] = M('Region')->where($province)->find()['id'];
            $city['name'] = ['like',"%{$Resume['city']}%"];
            $city['parent_id'] = $address['province'];
            // $sql = M('Region')->where($province)->fetchSql()->find();
            $address['city'] = M('Region')->where($city)->find()['id'];
            // $sql = M('Region')->where($city)->fetchSql()->find()['id'];
            // dump($sql);
        }
        json_App(['status'=>1,'msg'=>'成功','list'=>$list,'address'=>$address]);
    }

    //获取街道资料
    public function get_area()
    {
        $id = I('id');
        $list = M('Region')->where(['parent_id'=>$id])->select();
        json_App(['status'=>1,'msg'=>'成功','list'=>$list]);
    }

    //提交加盟商信息
    public function addInfo()
    {
        $data = I('post.');

        if(!$data['company']){
            json_App(['status'=>-1,'msg'=>'请填写公司名！']);
        }

        if(!$data['username']){
            json_App(['status'=>-1,'msg'=>'请填写联系人！']);
        }

        if(!$data['mobile']){
            json_App(['status'=>-1,'msg'=>'请填写联系方式！']);
        }

        if(!$data['province']){
            json_App(['status'=>-1,'msg'=>'请选择省份！']);
        }

        if(!$data['address']){
            json_App(['status'=>-1,'msg'=>'请填写详细地址！']);
        }

        $data['add_time'] = time();
        $res = M('Join')->add($data);

        if($res){
            json_App(['status'=>1,'msg'=>'提交成功！']);
        }else{
            json_App(['status'=>-1,'msg'=>'提交失败！']);
        }
    }    
}