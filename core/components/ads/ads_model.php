<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_ads_model extends Model{
	/*public function build(&$data){
		$data['fileurl'] = fileurl($data['fileurl']);
		$data['url'] = url('dir=&app=ads&ac=click&id='.$data['id']);
	}*/
	public function filter($position='all', $pages='all', $module='all', $category='all',$fields='*'){
		$ads = $this->cache();
		$data = $sort = $by = $ids = array();
		foreach ($ads as $id=>$row){
			$nopage = ('all'!=$pages AND $row['pages']!='all' AND FALSE===strpos($row['pages'], $pages));
			$nomodule = ('all'!=$module AND $row['modules']!='all' AND FALSE===strpos($row['modules'], $module));
			if ('all'!=$position AND ($row['position']!=$position OR $nopage)) continue;
			if ($nomodule) continue;
			if ('*'!=$category AND $row['category']!='all'){
				$cate = str2array($row['category']);
				if ($cate[$module]){
					$arcate = array_reverse(explode(',', $category));
					$in = FALSE;
					foreach ($arcate as $cid){
						if (FALSE!==cstrpos($cate[$module], $cid)){
							$in = TRUE;
							$sort[$id] = 0;
							break;
						}
					}
					if (!$in) continue;
				}
			}
			if (!isset($sort[$id])){
				if (!$nopage){
					$sort[$id] = 1;
				}elseif (!$nomodule){
					$sort[$id] = 2;
				}else{
					$sort[$id] = 3;
				}
			}
			$by[$id] = $row['sortby'];
			$ids[$id] = $id;
			$row['fileurl'] = fileurl($row['pathname']);
			$row['clickurl'] = url('cp/ads/click', 'id='.$row['id']);//dump($row);die;
			$data[$id] = ('*'==$fields)?$row:array_intersect_key($row,array_flip(explode(',',$fields)));
		}
		array_multisort($sort, SORT_NUMERIC, SORT_ASC,
			$by, SORT_NUMERIC, SORT_DESC,
			$ids, SORT_NUMERIC, SORT_ASC, $data);
		return $data;
	}
	public function show($position, $pages='*', $module='*', $category='*'){
		if (!$module) $module = '*';
		if (!$pages) $pages = '*';
		if (!$category) $category = '*';
		$data = $this->filter($position, $pages, $module, $category);
		$limit = $this->attr('limit');
		if (1===$limit) return array_shift($data);
		if (1<$limit) return array_slice($data, $limit);
		return $data;
	}
	public function get($id){
		$ads = $this->cache();
		return isset($ads[$id]) ? $ads[$id] : $this->where('id', $id)->find();
	}
	public function cache($cache=FALSE){
		$cachename = 'com.ads_data';
		if ($cache OR FALSE===($data = cache::fq($cachename))){
			$data = $this->property('rstype',3)->callback()->where('published', 1)->findAll();
			cache::fq($cachename, $data);
		}
		return $data;
	}
	public function apply_cond($fields=array(), $post=array()){
		$cond = parent::apply_cond($fields, $post);
		//$cond['sid'] = $GLOBALS['city_data']['id'];
		return $cond;
	}
	public function save($p){
		$time = D::get('curtime');
		$id = (int)$p['id'];
		$p['publock'] = 0;
		if (isset($p['begindate'])){
			$p['published'] = intval((!$p['begindate'] OR $p['begindate'] < $time) AND (!$p['enddate'] OR $p['enddate'] > $time));
		}
		unset($p['id']);
		if (!$id){
			$p['createtime'] = $time;
			$id = $this->insert($p);
		}else{
			$this->update($p, array('id'=>$id));
		}
		$this->cache(TRUE);
		return $id;
	}
	public function insert_click($id){
		$insert = array('adid' => $id) + $this->client_info();
		return $this->db()->insert($this->config['click_table'], $insert);
	}
	public function insert_pageview($id){
		$insert = array('adid' => $id) + $this->client_info();
		return $this->db()->insert($this->config['view_table'], $insert);
	}
	private function client_info(){
		return array(
			'visit_time' => D::get('curtime'),
			'ip_address' => response::ip(),
			'user_agent' => response::user_agent(),
			'visit_url' => url('#current#'),
		);
	}
}
?>