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
class AboutController extends BaseController {
    public function index(){      
        $this->display();
    }

    /**
     *  好运旺简介
     */
    public function about1(){
        $companyInfo = M("Article")->where("cat_id = 95")->select();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('company',$companyInfo);
        $this->display();
    }

    /**
     *  联系我们
     */
    public function ContactUs(){
        $aboutInfo = M("Article")->where("cat_id = 96")->order('add_time desc')->limit('1')->select();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('about',$aboutInfo);
        $this->display();
    }

    /**
     *  荣誉资质
     */
    public function Honor(){
        $aboutInfo = M("Article")->where("cat_id = 97")->order('add_time desc')->limit('1')->select();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('about',$aboutInfo);
        $this->display();
    }


    /**
     *  成功案例
     */
    public function SuccessfulCases(){
        $aboutInfo = M("Article")->where("cat_id = 98")->order('add_time desc')->limit('1')->select();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('about',$aboutInfo);
        $this->display();
    }


    /**
     *  发展历程
     */
    public function DevelopCourse(){
        $aboutInfo = M("Article")->where("cat_id = 99")->order('add_time desc')->limit('1')->select();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('about',$aboutInfo);
        $this->display();
    }


    /**
     *  招贤纳士
     */
    public function Recruit(){
        $count = M('resume')->where("1=1")->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $JobInfo = M("resume")
            ->where("1=1")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order("user_id desc")
            ->select();
        $Ad = M('Ad')->where(['ad_id'=>81])->find();
        $this->assign('ad',$Ad);
        $this->assign('page',$show);
        $this->assign('JobInfo',$JobInfo);
        $this->display();
    }



    public function detail($user_id){
        $user_info =M("resume")->where("user_id=$user_id")->find();
        //dump($user_info);
        $this->assign("user_info",$user_info);
        $this->display();
    }

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
        $this->assign('aid',$aid);
        //dump($after);
        $this->assign('after',$after);

        $this->assign('articleInfo',$articleInfo);
        $this->display();
    }



    
}