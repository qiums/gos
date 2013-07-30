<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_tree_model extends model{
	public $treeinfo;
	public $tid = 0;
	private $fields = NULL;
	private $cachedata = array();

	function get_tree($id=0,$cache=FALSE){
		if (is_null($id)) return array();
		if (is_bool($id)){
			$cache = $id;
			$id = 0;
		}
		$this->tid = $id;
		$cachename = 'com.tree_info';
		if ($cache OR FALSE===($tree = cache::q($cachename))){
			$tree = $this->attr('datatype', 3)->findAll();
			cache::q($cachename, $tree);
		}
		if ($id){
			if (is_numeric($id)) return $tree[$id];
			foreach ($tree as $val){
				if ($val['alias']==$id) return $val;
			}
			return array();
		}
		return $tree;
	}
	function data($tid, $mid=0, $cid=0){
		$this->treeinfo = $this->get_tree($tid);
		if (!$this->treeinfo) return array();
		$this->tid = $this->treeinfo['id'];
		$tree = $this->qfind();
		foreach ($tree as $key=>$one){
			if ($mid>0 AND isset($one['mid'])){
				if (!isset($one['mid'][$mid]) OR
					($cid>0 AND FALSE===cstrpos($one['mid'][$mid],$cid))){
					unset($tree[$key]);
				}
			}
		}
		return $tree;
	}
	function build(&$data){
		if (!$data['rootid']) $data['rootid'] = $data['id'];
		if (!$data['alias']) $data['alias'] = replace_alias($data['dataname']);
		$data['fullnode'] = ($data['node']==$data['id']) ? $data['id'] : ("{$data['node']},{$data['id']}");
		$data['fullalias'] = $this->full_alias($data);
		if ($data['options']) $data += parse_ini_string($data['options'],TRUE);
	}
	function full_alias($data){
		static $cache = array();
		$cache[$data['id']] = $data;
		$node = explode(',', $data['fullnode']);
		$alias = array();
		foreach ($node as $id){
			$alias[] = !$cache[$id]['alias'] ? replace_alias($cache[$id]['dataname']) : $cache[$id]['alias'];
		}
		return join('/', $alias);
	}
	public function all(){
		return $this->qfind();
	}
	private function qfind($cache=FALSE){
		if (!$this->treeinfo){
			if (!$this->tid) return array();
			$tree = $this->get_tree($this->tid);
		}else{
			$tree = $this->treeinfo;
		}
		if (!$tree) return array();
		$cachename = 'com.tree_'.$tree['alias'];
		if (!$cache AND isset($this->cachedata[$tree['alias']])){
			$data = $this->cachedata[$tree['alias']];
		}elseif ($cache OR FALSE===($data = cache::q($cachename))){
			if ($tree['treetable']==$this->gc('treedata_table')) $cond['tid'] = $tree['id'];
			$this->callback()
				->order()
				->attr('datatype', 3)
				->where($cond);
			$data = $this->db()->findAll($tree['treetable']);
			cache::q($cachename, $data);
		}
		if (!isset($this->cachedata[$tree['alias']])) $this->cachedata[$tree['alias']] = $data;
		if ($this->fields){
			$in = array_flip(explode(',', $this->fields));
			foreach ($data as $key=>$one){
				$data[$key] = array_intersect_key($one, $in);
			}
			$this->fields = NULL;
		}
		return $data;
	}
	function get($id, $use=''){
		$tree = $this->qfind();
		if (!is_array($id)){
			if (is_numeric($id)){
				$rs = isset($tree[$id]) ? $tree[$id] : array();
				if ($use AND isset($rs[$use])) return $rs[$use];
				return $rs;
			}
			$id = explode(',',$id);
		}
		if (!$use) $use = is_numeric($id) ? 'id' : 'alias';
		$rs = array();
		foreach ($tree as $tid=>$one){
			if (!isset($one[$use])) continue;//return array();
			if (in_array($one[$use], $id)) $rs[$tid] = $one;
		}
		if (count($rs)==1) return current($rs);
		return $rs;
	}
	function dget($id=0, $key='hot', $rstype=0){
		$cat = $this->qfind();
		$rs = array();
		if ($id>0) $rs[$id] = array();
		foreach ($cat as $one){
			if (!$id AND !$one['pid'] AND !isset($rs[$one['id']]))
				$rs[$one['id']] = array();
			if (!$one[$key] OR ($id>0 AND cstrpos($one['node'],$id)===FALSE)) continue;
			$rs[($id>0?$id:$one['rootid'])][] = $one;
		}
		if (!$id AND !$rstype){
			foreach ($rs as $one){
				$tmp = array_merge((array)$tmp, $one);
			}
			return $tmp;
		}
		return $id>0 ? current($rs) : $rs;
	}
	function root($by='byid'){
		$tree = $this->qfind();
		if(!$tree) return array();
		$data = $sort = array();
		foreach ($tree as $cid=>$one){
			if (!empty($one['pid'])) continue;
			$data[$cid] = $one;
			$order[$cid] = $one['orderby'];
			$sort[$cid] = $one['dataname'];
		}
		('byid'==$by) ? ksort($data,SORT_NUMERIC)
			: array_multisort($order, SORT_NUMERIC, SORT_ASC, $sort, SORT_STRING, SORT_ASC, $data);
		if ('byname'==$by){
			$rs = array();
			foreach ($data as $index=>$one){
				unset($data[$index]);
				$rs[$one['id']] = $one;
			}
			return $rs;
		}
		return $data;
	}
	function child($id){
		$tree = $this->qfind();
		$data = array();
		foreach ($tree as $cid=>$one){
			if ($one['pid']!=$id) continue;
			$data[$cid] = $one;
		}
		return $data;
	}
	// 取得某一级的数据
	function depth($depth=0, $num=0, $sort='byid'){
		$data = $this->qfind();
		$sort = array();
		foreach ($data as $cid=>$one){
			if ($depth!=$one['depth']){
				unset($data[$cid]);
				continue;
			}
			$sort[$cid] = $one['dataname'];
		}
		('byid'==$sort)?ksort($data):array_multisort($sort, SORT_ASC, $data);
		if ('byname'==$sort){
			$rs = array();
			foreach ($data as $index=>$one){
				unset($data[$index]);
				$rs[$one['id']] = $one;
			}
			$data = $rs;
			unset($rs);
		}
		return !$num ? $data : array_slice($data, 0, $num);
	}
	//取得所有下级（下级的下级等）
    function sub($id, $self=0, $cond=array()){
		$tree = $this->qfind();
		$data = array();
		if (is_string($cond)) $cond = str2array($cond,'',',');
		foreach ($tree as $aid=>$one){
			if (FALSE===(cstrpos($one['node'],$id))) continue;
			if ($cond){
				$key = key($cond);
				if (!isset($one[$key]) OR current($cond)!=$one[$key]) continue;
			}
			$data[$aid] = $one;
		}
		if (!$self) unset($data[$id]);
		return $data;
    }
	function siblings($id){
		$data = $this->get($id);
		if (!$data['rootid']) return $this->root();
		$tree = $this->qfind();
		$rs = array();
		foreach ($tree as $cid=>$one){
			if ($one['rootid']!=$data['rootid'] OR $one['node']!=$data['node']) continue;
			$rs[$cid] = $one;
		}
		return $rs;
	}
	function parents($id,$self=FALSE){
		$data = $this->get($id);
		if ($data['node']==$data['id']) return array($data);
		$tree = $this->qfind();
		$parent = array();
		foreach ($tree as $cid=>$one){
			if (FALSE===(strpos(','.$data['node'].',',','.$one['id'].','))) continue;
			$parent[$cid] = $one;
		}
		if ($self) $parent[] = $data;
		return $parent;
	}
	function delete_data($cond){
		$tree = $this->get_tree($this->tid);
		$table = $tree['treetable'];
		Db::delete($table,$cond);
		$this->qfind(TRUE);
	}
	function title($id,$sort='desc'){
		$rs = $this->parents($id,TRUE);
		//$count = count($rs);
		$title=array();
		$sort=='desc' ? arsort($rs) : asort($rs);
		foreach ($rs as $one){
			$title[] = $one['dataname'];
		}/**/
		return $title;
	}
	/* 当前位置 */
	function position($url,$id=0,$all=FALSE){
		$pos = array();
		if (FALSE!==strpos($id,',')) $id = substr(strrchr($id,','), 1);
		if (!$id) return $pos;
		$result = $this->parents($id, TRUE);//
		if (!empty($result)){
			if (is_array($url)){
				$key = explode('=', $url[1]);
				$url = $url[0];
			}
			foreach ($result as $val){
				if (!empty($url)){
					$tmp = url(preg_replace('/\[(.[^\}\]]*?)\]/ies', '\$val[\'$1\']', $url),
						($key[0] AND isset($val[$key[0]])) ? array((isset($key[1]) ? $key[1] : $key[0]) => $val[$key[0]]) : '');
					$tmp = preg_replace('/\/(\d+)\-\1$/is','/\\1',$tmp);
				}
				$pos[] = empty($url)?$val['dataname']:'<a href="'.$tmp.'">'.$val['dataname'].'</a>';
			}
			if (!$all){
				$last = array_pop($pos);
				$pos[] = $val['dataname'];
			}
		}
		return $pos;
	}
}
