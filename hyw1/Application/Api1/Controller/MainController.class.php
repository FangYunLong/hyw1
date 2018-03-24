<?php
/**
 *Author:Lonelytears
*: 2016-12-27
 */
namespace Api\Controller;


class MainController extends BaseController {

	/**
     * 菜单、优惠--优惠列表
     *get请求
	*/
	public function prefer()
    {
        $res = M('Goods')->field('goods_id,zm_pic')->where(array('is_prefer'=>1,'is_on_sale'=>1))->select();
        if(!empty($res)){
        	exit(json_encode(array('status'=>1,'msg'=>'获取优惠列表成功','result'=>$res)));
        }else{
        	exit(json_encode(array('status'=>-1,'msg'=>'获取优惠列表失败','result'=>$res)));
        }
    }

	/**
     * 菜单、优惠--优惠详情
     * post请求
	*/
	public function preferInfo()
    {
      	$goods_id = I('post.goods_id');
        
        $res = M('Goods')->field('goods_id,goods_name,bzzj,zdlzj,zm_pic')->where(['goods_id'=>$goods_id])->select()[0];
        if(!empty($res)){
        	exit(json_encode(array('status'=>1,'msg'=>'获取优惠详情成功','result'=>$res)));
        }else{
        	exit(json_encode(array('status'=>-1,'msg'=>'获取优惠详情失败','result'=>$res)));
        }
    }

	/**
     * 菜单--新增意见反馈
     * post请求
	*/
	public function opinion()
    {
        $token   = I('post.token');
        $user_id = S($token);

        if(empty($user_id)){
        	exit(json_encode(array('status'=>-1,'msg'=>'缺少token')));
        }
        if(empty(I('post.content'))){
        	exit(json_encode(array('status'=>-1,'msg'=>'反馈内容为空')));
        }        
        $Opinion_data['content']   = I('post.content');
        $Opinion_data['user_id']   = $user_id;
        $Opinion_data['add_time']  = time();

        $res = M('Opinion')->add($Opinion_data);

        if($res){
        	exit(json_encode(array('status'=>1,'msg'=>'新增意见反馈成功！')));
        }else{
        	exit(json_encode(array('status'=>-1,'msg'=>'未知错误！')));
        }    	
    }


}