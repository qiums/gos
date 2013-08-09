<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class search_controller extends core_search_controller{

	public function index(){
		if (!$this->archives->config){
			$mid = $this->gp('mid');
			if (!$mid) return $this->output('Undefined mid');
			$this->archives = $this->channel->get($mid);
		}
		if (!$this->archives->config) return $this->output('Undefined channel');
		if ($this->post['ids']){
			if (!is_array($this->post['ids'])) $this->post['ids'] = explode(',', $this->post['ids']);
			$this->append_cond['id'] = $this->post['ids'];
		}
		parent::index();
		if ($_ENV['ajaxreq']) return ;
		if (!$this->vars['pagetit']){
			$this->assign('pagetit', $this->archives->config['channel_name']);
		}
		$this->view("{$this->archives->config['prefix']}_list");
	}
	/*** 以下方法仅限Venue使用 ***/
	public function venue(){
		$this->archives->config = $this->channel->get('venue');
		if ($this->gp('lat')){
			$map = near_latlng($this->gp('lat'), $this->gp('lng'), $this->gp('dist', 3));
			$this->append_cond = array(
				'maplat' => "BETWEEN {$map['x'][0]} AND {$map['y'][0]}",
				'maplng' => "BETWEEN {$map['x'][1]} AND {$map['y'][1]}",
			);
		}
		self::index();
	}
	// 地图模式
	public function map(){
		$this->qdata['mode'] = 'map';
		$channel = $this->channel->get('venue');
		$this->assign(array(
			'mc' => $channel,
			'pagetit' => (isset($this->qdata['local']) ? "Nearby in {$this->qdata['local']}-" : ''). $channel['channel_name'],
		)
		)->view("venue_list");
	}
	// 附近
	public function near(){
		$id = $this->gp('id');
		if (!$id) return $this->output('Undefined Id');
		$channel = $this->channel->get('venue');
		$this->archives->config = $channel;
		$data = $this->archives->callback()->where('id', $id)->find();
		if ($_ENV['ajaxreq']){
			$map = near_latlng($data['maplat'], $data['maplng'], $this->gp('dist', 3));
			$this->append_cond = array(
				'maplat' => "BETWEEN {$map['x'][0]} AND {$map['y'][0]}",
				'maplng' => "BETWEEN {$map['x'][1]} AND {$map['y'][1]}",
			);
			return parent::index();
		}
		$this->qdata['mode'] = 'map';
		$this->assign(array(
			'mc' => $channel,
			'data' => $data,
			'pagetit' => ('Nearby in '. $data['fulltitle']) .'-'. $channel['channel_name'],
		)
		)->view("venue_list");
	}
	public function branch(){
		$id = $this->gp('id');
		if (!$id) return $this->output('Undefined points Id');
		$channel = $this->channel->get('venue');
		$this->archives->config = $channel;
		$data = $this->archives->callback()->where('id', $id)->find();
		$this->append_cond = array(
			'subject' => $data['subject'],
		);
		$this->assign(array(
			'mc' => $channel,
			'data' => $data,
			'pagetit' => ('All branches for '. $data['subject']) .'-'. $channel['channel_name'],
		)
		);
		self::index();
	}
	/*** 以上方法仅限Venue使用 ***/
}
