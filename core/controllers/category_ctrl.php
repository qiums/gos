<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class category_controller extends common_controller{
	function __construct(){
		parent::__construct();
	}
	public function data(){
		$data = $this->category
				->fields("id,mid,pid,node,catename,childs,alias")
				->find($this->post);
		foreach ($data as $key=>$one){
			if ($one['redirect']) unset($data[$key]);
		}
		//$data = qcsort($data);
		return $this->output(1, '', qcsort($data));
	}
	public function json(){
		$do = $this->gp('do');
		if ($do AND method_exists($this->category, $do)){
			$this->category->fields("id,mid,pid,node,catename,childs,alias");
			$data = $this->category->$do($this->gp('id', 0));
			return $this->output(1, '', 'array'===$this->gp('res') ? array_values($data) : $data);
		}
		return $this->output(0, 'method_not_exists');
	}
}