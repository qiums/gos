<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class search_controller extends core_search_controller{

	public function venue(){
		$this->archives->config = $this->channel->get('venue');
		if ($this->gp('lat')){
			$map = near_latlng($this->gp('lat'), $this->gp('lng'), $this->gp('dist', 3));
			$this->append_cond = array(
				'maplat' => "BETWEEN {$map['x'][0]} AND {$map['y'][0]}",
				'maplng' => "BETWEEN {$map['x'][1]} AND {$map['y'][1]}",
			);
		}
		if ($_ENV['ajaxreq']) return parent::index();
		$this->assign(array(
			'pagetit' => $this->archives->config['channel_name'],
		)
		)->view("venue_list");
	}
	public function index(){
		parent::index();
		$this->view("{$this->archives->config['prefix']}_list");
	}
	// 地图模式
	public function map(){
		$this->qdata['mode'] = 'map';
		$channel = $this->channel->get('venue');
		$this->assign('channel', $channel);
		$this->assign(array(
			'pagetit' => (isset($this->qdata['local']) ? "Near by {$this->qdata['local']}-" : ''). $channel['channel_name'],
		)
		)->view("venue_list");
	}
}
