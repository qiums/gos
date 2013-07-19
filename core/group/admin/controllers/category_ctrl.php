<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class channel_controller extends common_controller{

	public function index(){
		$this->assign('form', $this->form->render());
		//$this->assign('arrdata', $this->channel->get());
		$this->view('category');
	}
	public function edit(){
		$id = $this->qdata['id'];
		if ($id){
			$data = $this->channel->find(array('id'=>$id));
			$this->vars['tabletit'] .= " / {$data['channel_name']}";
			if ('copy' === gc('env.action')){
				unset($data['prefix'], $data['channel_name']);
			}
			$this->assign('data', $data);
		}
		$this->assign('form', $this->form->render('', $data));
		$this->assign('channels', $this->channel->get());
		$this->view('channel');
	}
	public function copy(){
		$this->edit();
	}
	public function save(){exit;
		$ac = $this->post['ac'];
		$id = $this->post['id'];
		unset($this->post['id']);
		if (!$this->form->validate()){
			exit($this->form->error());
		}
		if (!$ac){
			$id = $this->channel->insert($this->post);
		}elseif ('copy'===$ac){
			$cond = AIKE(array('prefix','channel_name'), $this->post);
			array_unshift($cond, 'or');
			if ($this->channel->where($cond)->count()>0) return $this->output(0, 'data_exists');
			$id = $this->channel->insert($this->post);
		}else{
			$this->channel->where('id', $id)->update($this->post);
		}
		return $this->output(1, 'supe_success');
	}
	public function get(){
		$id = $this->qdata['id'];
		if ($id) return $this->output(1, '', $this->channel->where('id', $id)->find());
		return $this->output(0, 'not_request_data');
	}
	public function field(){
		if ($this->qdata['do']){
			$do = "field_{$this->qdata['do']}";
			if (method_exists($this, $do))
				return $this->{$do}();
		}
		if ($this->post){
		}
		$id = $this->qdata['id'];
		if (!$id) return $this->output(0, 'not_request_data');
		$channel = $this->channel->get($id);
		$this->assign('channels', $this->channel->get());
		$this->assign('tabletit', $this->vars['tabletit']. " - {$channel['channel_name']}");
		$this->assign('arrdata', $this->channel->get_fields($id));
		$this->assign('form', $this->form->render());
		$this->view('channel_field');
	}
}