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
		parent::index();
		$this->view("venue_list");
	}
	public function index(){
		parent::index();
		$this->view("{$this->archives->config['prefix']}_list");
	}
	// 地图模式
	public function map(){
		$this->qdata['mode'] = 'map';
		$this->assign('channel', $this->channel->get('venue'));
		$this->view("venue_list");
	}
	// 根据坐标查询附近商家
	public function near(){
		$channel = $this->archives->config;
		$latlng = $this->gp('latlng');
		if ($latlng){
			$latlng = explode('x', $latlng);
			$latlng = near_latlng($latlng[0], $latlng[1], $this->gp('distance', 3));
			$fields = $this->channel->get_fields($channel['id'], 4);
			$cond = $this->archives->apply_cond($fields);
			$cond['maplat'] = "BETWEEN {$latlng['x'][0]} AND {$latlng['y'][0]}";
			$cond['maplng'] = "BETWEEN {$latlng['x'][1]} AND {$latlng['y'][1]}";
			$data = $this->archives
				->page($this->gp('page'), $this->gp('limit'))->order()
				->where($cond)->findAll();
			$this->qdata['pagedata'] = $this->archives->pagedata;
			if ($_ENV['ajaxreq']) return $this->output(1, '', array('data'=>$data, 'page'=>$this->qdata['pagedata']));
			$this->assign('arrdata', $data);
		}
		$this->assign(array(
			'seokeywords' => $channel['keywords'],
			'seodesc' => $channel['description'],
			'pagetitle' => trim("Search / {$channel['module_name']}",'/ '),
			))
			->view(array(
				"{$channel[prefix]}_list",
				'archives_list'
			));
	}
}
