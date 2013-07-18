<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class core_search_controller extends common_controller{
	protected $append_cond = array();

	public function __call($name, $args){
		$this->archives->config = $this->channel->get($name);
		if ($this->archives->config){
			return $this->index();
		}
		return parent::__call($name, $args);
	}
	protected function apply_category(){
		if (is_null($cid = $this->gp('cid'))) return ;
		$channel = $this->archives->config;
		$this->category->block(array('mid'=>$channel['id']));
		$this->qdata['cid'] = $this->category->alias_to_id($cid);
		if ($this->qdata['cid']){
			$pos = $this->category->position('search/[channel]/cid/[id]', $this->qdata['cid']);
			array_unshift($pos, '<a href="'.url('/').'">Home</a>');
			$this->assign('position', $pos);
			$this->assign('lc', $this->category->get($this->qdata['cid']));
			$this->assign('rc', $this->category->get($this->vars['lc']['rootid']));
		}
	}
	protected function index(){
		if (!$this->archives->config) return $this->output(0, 'not_found_channel');
		$ac = key($this->qdata);
		if (method_exists($this, $ac)){
			unset($this->qdata[$ac]);
			return $this->$ac();
		}
		//$this->apply_category();
		$channel = $this->archives->config;
		gc('env.mid', $channel['prefix'], TRUE);
		// 搜索字段
		//$fields = $this->form->data(gc('env.controller'),gc('env.action'))->form_data;
		$fields = $this->channel->get_fields($channel['id'], 4);
		$list = array_keys($this->channel->get_fields($channel['id'], 5));
		$cond = $this->append_cond + $this->archives->apply_cond($fields);
		$data = $this->archives->attr('fields', join(',', $list))
			->page($this->gp('page'), $this->gp('limit'))->order()
			->callback()->where($cond)->findAll();
		$this->append_cond = array();
		$this->qdata['pagedata'] = $this->archives->pagedata;
		if ($_ENV['ajaxreq']) return $this->output(1, '', array('data'=>$data, 'page'=>$this->qdata['pagedata']));
		$this->apply_category();
		$this->assign('form', $this->form->render('', request::get(), 1));
		$this->assign('arrdata', $data);
		$this->assign('channel', $channel);
	}
	public function redirect(){
		if ($this->post){
			$mid = $this->post['mid'];
			unset($this->post['mid']);
			foreach ($this->post as $key=>$val){
				unset($this->post[$key]);
				if (''!==$val){
					$key = str_replace('sd_', '', $key);
					$this->post[$key] = $val;
				}
			}//print_r($this->post);return ;
			$url = url("search/{$mid}", $this->post);
			redirect($url, FALSE);exit;
		}
		return $this->output(0, 'not_request_data');
	}
}
