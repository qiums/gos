<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class archives_model extends model{
	private $post_fields = array();
	public $thumb_fixed = FALSE;
	public $thumb_size = NULL;
	public $thumb;

    public function build(&$data){
		$url = "{$this->config['prefix']}/view/{$data['id']}";
		$data['link'] = !empty($data['extlink']) ? $data['extlink']
			: (gc('env.domain') .url($url));
		$data['fulltitle'] = $data['subject'];
		if ($data['subtitle']) $data['fulltitle'] .= " ({$data['subtitle']})";
		if ($data['filesize']) $data['filesize_text'] = formatSize($data['filesize']);
		/*if ($data['cid']){
			$this->load->model('category')->block(array('mid'=>$this->config['id']));
			$id = GFS($data['cid'], ',', 'end');
			$data['catedata'] = $this->category->get($id);
			//$data['catedata']['rootalias'] = $id;
			$data['catedata']['rootalias'] = current(explode('/',$data['catedata']['fullalias']));
			//unset($data['catedata']['id']);
		}*/
		if (isset($data['runtype'])){
			$data['short_rundate'] = short_rundate($data);
			$data['rundate']= get_rundate($data);
		}elseif ($data['begindate']+$data['enddate']>0){
			$data['rundate'] = ($data['begindate']>0?'S:'.D::cdate($data['begindate'],'Y-m-d'):'').
				($data['enddate']>0?'<br />E:'.D::cdate($data['enddate'],'Y-m-d'):'');
		}
		if ($data['description']) $data['description'] = strip_tags($data['description']);
		$strlen = Base::getInstance()->gp('strlen');
		if (is_numeric($strlen) AND ($data['description'])){
			$data['description'] = csubstr($data['description'], $strlen);
		}
		$thumb = Base::getInstance()->gp('thumb');
		if (is_scalar($thumb) AND function_exists('ct')){
			$thumb_size = gc('image.thumb_size');
			$thumb = $thumb_size[$thumb];
			if ($thumb){
				$thumb = explode('x', $thumb);
				array_unshift($thumb, $data['coverpic']);
				$data['thumb'] = call_user_func_array('ct', $thumb);
			}
		}
		unset($strlen, $thumb);
	}
	public function save($p){
		$time = D::get('curtime');
		$id = (int)$p['id'];
		$conf = $this->conf;
		$p['mid'] = $conf['id'];
		$p['sd_arcdata'] = array();
		if (!empty($p['begindate']) OR !empty($p['enddate'])){
			$p2 = array_intersect_key($p, array_flip(array('begindate','enddate')));
			if ($p2['begindate'] AND !is_float($p2['begindate'])) $p2['begindate'] = D::timestamp($p2['begindate']);
			if ($p2['enddate'] AND !is_float($p2['enddate'])) $p2['enddate'] = D::timestamp($p2['enddate']);
			if (!isset($p['published'])){
				$p['published'] = (int)(
					!isset($p['subdata_runtype']) AND (!$p2['begindate'] OR $p2['begindate']<$time) AND (!$p2['enddate'] OR $p2['enddate']>$time)
					);
			}else{
				$p['publock'] = (int)(!$p['published']);
			}
			$p['subdata_arcdata'] = $p2;
			unset($p2, $p['begindate'],$p['enddate']);
		}
		unset($p['id']);
		$p['subdata_searchindex'] = array(
			'updatetime'=>$time,
			'subject' => $p['subject'],
			'keywords'=>trim("{$p['subject']}#||#{$p['keywords']}#||#{$p['description']}",'#||#'),
		);
		if (isset($p['published'])) $p['subdata_searchindex']['published'] = $p['published'];
		if (isset($p['subdata_filepath'])){
			$filepath = filepath($p['subdata_filepath']);
			if (is_file($filepath)) $p['subdata_filesize']=filesize($filepath);
			unset($filepath);
		}
		$cond = array('aid,id','cond'=>array('aid'=>$id, 'mid'=>$p['mid']));
		if (!$id) unset($cond['cond']);
		$this->join_table('searchindex', $cond);
		if (!$id){
			if (!$p['createtime']) $p['createtime'] = $time;
			$p['sd_searchindex'] += array('mid'=>$p['mid'], 'createtime'=>$time);
			$p['sd_arcdata'] += array('mid'=>$p['mid'],'activetime'=>$time);
			$id = $this->join('arcdata', '', $cond)
				->join_table($this->conf['addon_table'],array('aid,id','cond'=>array('aid'=>$id)))
				->insert($p);
		}else{
			if (!$p['updatetime']) $p['updatetime'] = $time;
			$this->join_table('arcdata', $cond)
				->join_table($this->conf['addon_table'],array('aid,id','cond'=>array('aid'=>$id)))
				->update($p, array('id'=>$id));
		}
		return $id;
	}
	private function apply_pre($cond){
		$base = Base::getInstance();
		if (4==$this->config['id'] AND 'admin'!=$dir){
			if (!isset($cond['events.runstat'])) $cond['events.runstat'] = '> 0';
		}
		if ($base->gp('distance') AND $base->gp('latlng')){
			$this->apply_latlng($cond, $base->gp('latlng'), $base->gp('distance'));
		}
		return $cond;
	}
	public function content(){
		$this->join('contents.aid', '*', 'mid');
		return $this;
	}
	public function apply_cond($fields=array(), $post=array()){
		$base = Base::getInstance();
		if (!$post) $post = $base->qdata;
		$cond = parent::apply_cond($fields, $post);
		$dateline = $post['dateline'];
		if ($dateline){
			if (8===strlen($dateline)){
				$dateline = mktime(0,0,0,(int)substr($dateline,4,2), (int)substr($dateline,6),(int)substr($dateline,0,4));
				//$this->apply_events($cond, $dateline);
			}else{
				$dateline = D::get('curtime')-$dateline;
				$cond['arcindex.createtime'] = "> {$dateline}";
			}
		}
		unset($dateline);
		$dir = gc('env.groupdir');
		if ('admin' !== $dir){
			$cond['arcindex.ischeck'] = $base->gp('ischeck', 1);
			$cond['arcindex.published'] = $base->gp('published', 1);
			$cond['arcindex.delflag'] = $base->gp('delflag', 0);
		}
		/*if ($cond['arcindex.cid']){
			$cat = explode(',', $cond['arcindex.cid']);
			$cond['arcindex.categorytag'] = array('match', 'cat'.end($cat).'tag');
			unset($cat, $cond['arcindex.cid']);
		}*/
		$this->join('arcindex.aid', NULL, 'mid')
			->join('users.id~arcindex.uid', NULL);
		if ($this->gc('addon_table')){
			$this->join("{$this->config['addon_table']}.aid");
		}
		return $cond;
	}
	private function apply_events(&$cond, $dateline){
		$wday = D::cdate($dateline, 'w');
		$cond['and&or'] = array(
			array(
				'events.runtype'=>0
			),
			array(
				'events.runtype'=> 3,
				'arcdata.begindate'=> "{$dateline}",
			),
			array(
				'events.runtype'=> 2,
				'arcdata.begindate'=> "<= {$dateline}",
				'arcdata.enddate'=> ">= {$dateline}",
			),
			array(
				'events.runtype' => 1,
				'events.runweek' => "LIKE %{$wday}%",
				'arcdata.begindate'=>array(
					'or',
					array("<= {$dateline}", '0'),
				),
				'arcdata.enddate'=>array(
					'or',
					array(">= {$dateline}", '0'),
				),
			),
		);
	}
	public function block($ac='', $a=''){
		$a = str2array($a);
		if (!isset($a['mid'])) return array();
		$this->config = $this->channel->get($a['mid']);
		$fields = $this->channel->get_fields($this->config['id'], 4);
		if ($this->config['addon_table']){
			$this->join("{$this->config['addon_table']}.aid");
		}
		return parent::block($ac, $this->apply_cond($fields, $a));
	}
}
