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
		if (isset($data['runtype'])){
			$data['short_rundate'] = short_rundate($data);
			$data['rundate']= get_rundate($data);
		}elseif ($data['begindate']+$data['enddate']>0){
			$data['rundate'] = ($data['begindate']>0?'S:'.D::cdate($data['begindate'],'Y-m-d'):'').
				($data['enddate']>0?'<br />E:'.D::cdate($data['enddate'],'Y-m-d'):'');
		}
		if ($data['description']) $data['description'] = strip_tags($data['description']);
		if (array_key_exists('content', $data) AND is_null($data['content'])) $data['content'] = $data['description'];
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
				if (isset($data['tid'])) $thumb[] =  'type-'.qstrstr($data['tid'],',',TRUE);
				$data['thumb'] = call_user_func_array('ct', $thumb);
			}
		}
		unset($strlen, $thumb);
	}
	public function save($id, $p){
		$time = D::get('curtime');
		$p['mid'] = $this->config['id'];
		$p['sd_arcdata'] = array();
		if (!empty($p['begindate']) OR !empty($p['enddate'])){
			if (!isset($p['sd_published'])){
				$p['sd_published'] = (int)(
					!isset($p['subdata_runtype']) AND (!$p['sd_begindate'] OR $p['sd_begindate']<$time) AND (!$p['sd_enddate'] OR $p['sd_enddate']>$time)
					);
			}else{
				$p['sd_arcdata']['publock'] = (int)(!$p['published']);
			}
		}
		$p['sd_searchtag'] = trim("{$p['subject']} {$p['subtitle']} {$p['sd_keywords']} {$p['sd_description']}");
		$p['sd_alias'] = replace_alias("{$p['subject']} {$p['subtitle']}");
		$append = array('mid'=>$p['mid'], 'aid'=>$id);
		if (!$id) unset($append['aid']);
		if (!$id){
			if (!$p['sd_createtime']) $p['sd_createtime'] = $time;
			$p['sd_arcdata'] += array('mid'=>$p['mid'], 'activetime'=>$time);
			$id = $this->join('arcdata.aid', NULL, $append)
				->join('arcindex.aid', NULL, $append)
				->join('contents.aid', NULL, $append)
				->insert($p);
		}else{
			if (!$p['updatetime']) $p['updatetime'] = $time;
			$this->join('arcdata', NULL, $append)
				->join('arcindex', NULL, $append)
				->join('contents', NULL, $append)
				->where('id', $id)
				->update($p);
		}
		return $id;
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
		}
		$cond['arcindex.delflag'] = $base->gp('delflag', 0);
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
	public function block($ac='', $a='', $fields=array()){
		$a = str2array($a);
		if (!isset($a['mid'])) return array();
		$this->config = $this->channel->get($a['mid']);
		$fields = $this->channel->get_fields($this->config['id'], 4);
		$list = array_keys($this->channel->get_fields($mc['id'], 5));
		$this->callback()->attr('fields', join(',', $list));
		return parent::block($ac, $a, $this->apply_cond($fields, $a));
	}
}
