<?php
/**
 *
//
 * 微信交互类
 */
namespace Home\Controller;
use Think\Controller;
use Home\Logic\UsersLogic;

class PublicController extends Controller{

    public function header()
    {
        $this->display();
    }

    public function footer()
    {
        $this->display();
    }

    //搜索栏部位
    public function srarch(){
        $this->display();
    }
}