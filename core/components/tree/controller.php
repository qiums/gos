<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
//$app_conf = include dirname(__FILE__). DS. 'conf.php';
class com_tree_controller extends common_controller{
	private $tid;

	public function __call($name, $args){
		$this->tid = str_replace('admin_', '', $name);
		if ($_ENV['ajaxreq']) return $this->get_data();
		if ($this->vars['sub_menu']){
			$data = $this->cp->tree->get_tree();
			foreach ($data as $one){
				$this->vars['sub_menu']['cp/tree/'. $one['alias']] = " - {$one['typename']}";
			}
		}
		$data = $this->cp->tree->get_tree($this->tid);
		$this->assign(
			array(
				'tabletit' => $this->vars['sub_pagetit']. ' / '. $data['typename'],
				'form' => $this->form->render($this->cp->tree->config['add_form']),
				'editform' => $this->form->render($this->cp->tree->config['edit_form']),
				'data' => $data,
			)
			)->view('tree_data');
	}
	public function admin_index(){
		if ($this->vars['sub_menu']){
			$this->vars['sub_menu']['cp/tree#add'] = '[+] '. lang('button.add');
		}
		$this->assign(array(
			'tabletit' => $this->vars['sub_pagetit'],
			'arrdata' => $this->cp->tree->get_tree(),
			'form' => $this->form->render($this->cp->tree->config['form_data']),
		))->view('tree_index');
	}
	public function admin_save(){
		$id = $this->post['id'];
		$tid = $this->post['tid'];
		$ac = $id ? 'edit' : 'add';
		unset($this->post['id']);
		if (!$tid) return $this->output('not_defined_tid');
		if (!$this->form->validate($this->cp->tree->config["{$ac}_form"])){
			exit($this->form->error());
		}
		$m = $this->cp->tree;
		$data = $m->get_tree($tid);
		if (!$data) return $this->output('not_found_data');
		$m->property('tid', $tid);
		$m->config['data_table'] = $data['treetable'];
		if (!$id){
			$add = explode("\n", $this->post['adddataname']);
			if (!$add) return $this->output('no_request_data');
			unset($this->post['dataname'], $this->post['options']);
			$pid = (int)$this->post['pid'];
			$id = array();
			if (!$pid){
				$this->post['depth'] = '1';
			}else{
				$parent = $m->get($pid);
				$this->post['rootid'] = $parent['rootid'];
				$this->post['node'] = $parent['node']. ','. $pid;
				$this->post['depth'] = $parent['depth']+1;
			}
			foreach ($add as $row){
				$row = trim($row);
				if (!$row) continue;
				$this->post['dataname'] = $row;
				$this->post['alias'] = replace_alias($row);
				$id[] = $sid = $m->insert($this->post);
				if (!$pid){
					$m->where('id', $sid)->update(array('rootid'=>$sid, 'node'=>$sid));
				}
			}
			if ($pid) $m->where('id', $pid)->setInc('childs', count($id));
		}else{
			unset($this->post['adddataname']);
			if (!$this->post['dataname']) return $this->output('no_request_data');
			$this->post['alias'] = replace_alias($this->post['dataname']);
			$m->where('id', $id)->update($this->post);
		}
		$m->qfind(TRUE);
		return $this->output(1, 'supe_success', $m->get($id));
	}
	public function admin_get(){
		$id = $this->gp('id');
		if (!$id) return $this->output('no_request_data');
		if (!is_null($tid = $this->gp('tid'))){
			return $this->output(1, '',
					$this->cp->tree->property('tid', $tid)->get($id));
		}
		return $this->output(1, '', $this->cp->tree->get_tree($id));
	}
	public function json(){
		$do = $this->gp('do');
		$tid = $this->gp('tid');
		unset($this->qdata['tid'], $this->qdata['do']);
		if (!$tid) return $this->output(0, 'not_define_tid');
		if (method_exists($this->cp->tree, $do)){
			$this->cp->tree->property('tid', $tid)->property('fields', "id,pid,node,dataname,childs,alias,options");
			$data = $this->cp->tree->$do($this->gp('id', 0));
			return $this->output(1, '', 'array'===$this->gp('res') ? array_values($data) : $data);
		}
	}
	private function get_data(){
		$this->load->helper('extend');
		$loop = $this->cp->tree->data($this->tid);
		return $this->output(1, '', QCS($loop));
	}
}
