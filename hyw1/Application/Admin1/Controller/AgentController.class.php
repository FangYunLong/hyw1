<?php
/**
 *
//
 * Author: 当燃      
*: 2015-09-09
 */

namespace Admin\Controller;

use Think\Page;
use Think\Verify;

class AgentController extends BaseController {

	/**
	 * 供应商列表
	 */
	public function index()
	{
		$supplier_model = M('');
		$db_prefix = C('DB_PREFIX');
		$supplier_count = $supplier_model->table($db_prefix.'suppliers')->where('')->count();
		$page = new Page($supplier_count, 10);
		$show = $page->show();
		$supplier_list = $supplier_model
				->field('s.*,a.admin_id,a.user_name')
				->table($db_prefix.'suppliers s')
				->join('LEFT JOIN '.$db_prefix.'admin AS a ON a.suppliers_id = s.suppliers_id')
				->where('')
				->limit($page->firstRow, $page->listRows)
				->select();
		$this->assign('list', $supplier_list);
		$this->assign('page', $show);
		$this->display();
	}

	/**
	 * 供应商资料
	 */
	public function add()
	{
		$suppliers_id = I('get.suppliers_id', 0);
		if ($suppliers_id) {
			$db_prefix = C('DB_PREFIX');
			$suppliers_model = M('suppliers');
			$info = $suppliers_model
					->field('s.*,a.admin_id,a.user_name')
					->table($db_prefix.'suppliers s')
					->join('LEFT JOIN '.$db_prefix.'admin AS a ON a.suppliers_id = s.suppliers_id')
					->where(array('s.suppliers_id' => $suppliers_id))
					->find();
			$this->assign('info', $info);
		}
		$act = empty($suppliers_id) ? 'add' : 'edit';
		$this->assign('act', $act);
		$admin = M('admin')->field('admin_id,user_name')->where('1=1')->select();
		$this->assign('admin', $admin);
		$this->display();
	}

	/**
	 * 供应商增删改
	 */
	public function supplierHandle()
	{
		$data = I('post.');
		$suppliers_model = M('suppliers');
		//增
		if ($data['act'] == 'add') {
			unset($data['suppliers_id']);
			$count = $suppliers_model->where("suppliers_name='" . $data['suppliers_name'] . "'")->count();
			if ($count) {
				$this->error("此供应商名称已被注册，请更换", U('Admin/Admin/supplier_info'));
			} else {
				$r = $suppliers_model->add($data);
				if (!empty($data['admin_id'])) {
					$admin_data['suppliers_id'] = $suppliers_model->getLastInsID();
					M('admin')->where(array('suppliers_id' => $admin_data['suppliers_id']))->save(array('suppliers_id' => 0));
					M('admin')->where(array('admin_id' => $data['admin_id']))->save($admin_data);
				}
			}
		}
		//改
		if ($data['act'] == 'edit' && $data['suppliers_id'] > 0) {
			$r = $suppliers_model->where('suppliers_id=' . $data['suppliers_id'])->save($data);
			if (!empty($data['admin_id'])) {
				$admin_data['suppliers_id'] = $data['suppliers_id'];
				M('admin')->where(array('suppliers_id' => $admin_data['suppliers_id']))->save(array('suppliers_id' => 0));
				M('admin')->where(array('admin_id' => $data['admin_id']))->save($admin_data);
			}
		}
		//删
		if ($data['act'] == 'del' && $data['suppliers_id'] > 0) {
			$r = $suppliers_model->where('suppliers_id=' . $data['suppliers_id'])->delete();
			M('admin')->where(array('suppliers_id' => $data['suppliers_id']))->save(array('suppliers_id' => 0));
		}

		if ($r !== false) {
			$this->success("操作成功", U('Admin/Admin/supplier'));
		} else {
			$this->error("操作失败", U('Admin/Admin/supplier'));
		}
	}
}