<?php
/**
 *
//
 * 微信交互类
 */
namespace Home2\Controller;
use Think\Controller;
use Home2\Logic\UsersLogic;

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