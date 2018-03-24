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
class ArticleController extends BaseController {
    public function index(){      
        $this->display();
    }

   /**
    *  文章详情页
    */ 
    public function detail($article_id){
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
        $this->assign('aid',$aid);
        //dump($after);
        $this->assign('after',$after);

        $this->assign('articleInfo',$articleInfo);
        $this->display();
    }



    
}