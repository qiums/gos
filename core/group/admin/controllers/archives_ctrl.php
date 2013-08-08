<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class archives_controller extends common_controller{

	public function __call($name, $args){
		$this->archives = $this->channel->get($name);
		if ($this->archives->config){
			return $this->index();
		}
		return parent::__call($name, $args);
	}
	public function index(){
		if (!$this->archives->config) return $this->output(0, 'not_found_channel');
		if (count($_GET)===1){
			$ac = key($_GET);
			if (method_exists($this, $ac)) return $this->$ac();
		}
		$channel = $this->archives->config;
		if ($this->vars['sub_menu']){
			$this->vars['sub_menu']['archives/'.$channel['prefix'].'/add'] = '[+] '. lang('button.add').' '. $channel['channel_name'];
		}
		$this->assign('tabletit', $this->vars['sub_pagetit']);
		// 搜索字段
		$fields = $this->channel->get_fields($channel['id'], 4);
		$search = $this->channel->get_search($fields);
		$list = array_keys($this->channel->get_fields($channel['id'], 5));
		$cond = $this->archives->apply_cond($fields);
		$data = $this->archives
			->attr('fields', join(',', $list))
			->page($this->gp('page'), $this->gp('limit'))->order()
			->where($cond)->findAll();
		$this->qdata['pagedata'] = $this->archives->pagedata;
		$this->assign('form', $this->form->render($search, request::get(), 1));
		$this->assign('arrdata', $data);
		$this->assign('channel', $channel);
		$this->assign('supe_option', lang('supe_option'));
		$this->view(array("{$channel['prefix']}_home", 'archives_home'));
	}
	public function search(){
		if ($this->post){
			$mid = $this->post['mid'];
			unset($this->post['mid']);
			foreach ($this->post as $key=>$val){
				unset($this->post[$key]);
				if (''!==$val){
					$key = str_replace('sd_', '', $key);
					$this->post[$key] = $val;
				}
			}
			$url = url("admin/archives/{$mid}", $this->post);
			redirect($url, FALSE);exit;
		}
		return $this->output(0, 'not_request_data');
	}
	public function update(){
		if ('remove'===$this->post['upkey']) return $this->remove();
		$ids = $this->post['id'] ? $this->post['id'] : $this->post['ids'];
		$mid = $this->post['mid'];
		if (!$ids OR !$mid) return $this->output(0, 'not_request_data');
		if (!is_array($ids)) $ids = explode(',', $ids);
		$updata = $this->post['updata'] ? $this->post['updata'] : array($this->post['upkey']=>$this->post['updata']);
		if (!$updata) return $this->output(0, 'not_request_data');
		foreach ($ids as $id){
			$this->archives->db()->where(array('aid'=>$id, 'mid'=>$mid))->update('arcindex', $updata);
		}
		$this->output(1, 'update_success');
	}
	public function save(){
		$id = (int)$this->post['id'];
		$mid = (int)$this->post['mid'];
		$channel = $this->channel->get($mid);
		if (!$mid OR !$channel) return $this->output(0, 'not_request_data');
		$this->archives = $channel;
		if (!$this->form->validate($this->channel->get_fields($channel['id'], 3), $this->post)){
			exit($this->form->error());
		}
		$id = $this->archives->save($id, $this->post);
		if ($id) return $this->output(1, 'supe_success', array('id'=>$id));
		return $this->output(0, 'unknown_error');
	}
	private function add(){
		$channel = $this->archives->config;
		if (!$this->vars['tabletit']) $this->assign('tabletit', '[+] '. lang('button.add').' '. $channel['channel_name']);
		$group = array('common', 'extend');
		if ($channel['form_group']) $group = array_merge ($group, explode(',', $channel['form_group']));
		if ($channel['pic_category']) $group[] = 'picpanel';
		$form = array();
		foreach ($group as $k=>$one){
			$fields = $this->channel->get_fields($channel['id'], 3, $k);
			if ($channel['pic_category']) unset($fields['arcindex.coverpic']);
			$form[$k] = $this->form->render($fields, $this->vars['data']);
		}
		$this->assign('form_group', $group);
		$this->assign('form', $form);
		$this->assign('channel', $channel);
		$this->view(array("{$channel['prefix']}_supe", 'archives_supe'));
	}
	private function edit(){
		$id = current($_GET);
		if (!$id) return $this->ouput(0, 'not_request_data');
		$channel = $this->archives->config;
		if ($this->vars['sub_menu']){
			$this->vars['sub_menu']['archives/'.$channel['prefix'].'/add'] = '[+] '. lang('button.add').' '. $channel['channel_name'];
		}
		$data = $this->archives->join('arcindex.aid', '*', 'mid')
			->join('contents.aid', 'content','mid')->where('id', $id)->find();
		if ($channel['enable_ubb']){
			$this->ubb->edit = TRUE;
			$this->ubb->custom_tags = 'venue|download|events';
			$data['content'] = $this->ubb->replace($data['content']);
		}
		$this->assign('data', $data);
		$this->assign('tabletit', lang('button.edit'). ' '. $channel['channel_name']);
		return $this->add();
	}
	private function remove(){
		$ids = $this->post['ids'];
		if (!$ids OR !$this->post['mid']) return $this->ouput(0, 'not_request_data');
		if (!is_array($ids)) $ids = explode(',', $ids);
		$this->archives = $this->channel->get($this->post['mid']);
		$res = $this->archives->join('arcindex.aid', NULL, 'mid')
			->join('arcdata.aid', NULL, 'mid')
			->join('contents.aid', NULL,'mid')->where('id', $ids)->delete();
		if ($res) $this->output(1, 'delete_success');
		return $this->output(0, 'unknown_error');
	}
}