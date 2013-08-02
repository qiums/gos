<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
 * Description of admin
 *
 * @author QiuMS
 */
class category_model extends Model{
    public $fields = NULL;
	public $catdata;
	public $datakey=0;
	public $map = FALSE;
	public $cache_conf=array();

	public function block($ac='', $a='', $fields=array()){
		if (is_array($ac)){
			$a = $ac; $ac = '';
		}
		if (!is_array($a)) $a = str2array($a);
		if (!isset($a['mid'])) return array();
		if (!$this->channel) $this->load->model('channel');
		$this->config = array_intersect_key($this->channel->get($a['mid']),
			array('id'=>'', 'prefix'=>'', 'category_table'=>'', 'category_global'=>'')
		);
		$this->config['data_table'] = $this->gc('category_table');
		unset($this->config['category_table'], $a['mid']);
		if ($ac) return parent::block($ac, $a);
		return $this;
	}
	public function __construct(){
		parent::__construct();
		$this->config['data_table'] = 'category';
	}
	public function build(&$data){
		if (!$data['rootid']) $data['rootid'] = $data['id'];
		if (!$data['alias']) $data['alias'] = replace_alias($data['catename']);
		if ($data['options']) $data += parse_ini_string($data['options'],TRUE);
		$data['fullnode'] = ($data['node']===$data['id']) ? $data['id'] : ("{$data['node']},{$data['id']}");
		$data['fullalias'] = (!$data['pid']) ? $data['alias'] : $this->full_alias($data);
		$data['link'] = $data['redirect'] ? $data['redirect'] : url($data['fullalias']);
	}
	public function fields($field){
		$this->fields = $field;
		return $this;
	}
	function find($cond=array()){
		if (!$this->gc('data_table')) return array();
		$cache = is_bool($cond) AND TRUE===$cond;
		$cachename = 'category';
		if (!$cache AND $this->catdata){
			$data = $this->catdata;
		}elseif ($cache OR FALSE===($data = cache::q($cachename))){
			$data = $this->callback()
				->order('depth,asc,orderby,asc,id,asc')
				->attr('datatype', 3)
				->findAll();
			cache::q($cachename, $data);
		}
		if (!$this->catdata) $this->catdata = $data;
		if ($cond AND is_array($cond)){
			foreach ($data as $key=>$one){
				foreach ($cond as $k=>$val){
					if (!isset($one[$k]) OR FALSE===strpos($val, $one[$k])){
						unset($data[$key]);
						break;
					}
				}
			}
		}
		if ($this->fields){
			$in = array_flip(explode(',', $this->fields));
			foreach ($data as $key=>$one){
				$data[$key] = array_intersect_key($one, $in);
			}
			$this->fields = NULL;
		}/**/
		if (!$data) return array();
		return $data;
	}
	function full_alias($data){
		static $cache = array();
		$cache[$data['id']] = $data;
		$node = explode(',', $data['fullnode']);
		$alias = array();
		foreach ($node as $id){
			$alias[] = !$cache[$id]['alias'] ? replace_alias($cache[$id]['catename']) : $cache[$id]['alias'];
		}
		return join('/', $alias);
	}
	function alias_to_id($alias){
		if (is_numeric($alias)) return $alias;
		$cat = $this->find();
		foreach ($cat as $cid=>$one){
			if ($alias==$one['alias']) return $cid;
		}
		return 0;
	}
    function get($id, $use=''){
		$cat = $this->find();
		if (!is_array($id)){
			if (is_numeric($id) && !$use){
				$rs = isset($cat[$id]) ? $cat[$id] : array();
				return $rs;
			}
			$id = $id ? explode(',',$id) : array();
		}
		if (!$use) $use = is_numeric(join('', $id)) ? 'id' : 'fullalias';
		$rs = array();
		foreach ($cat as $cid=>$one){
			if (!isset($one[$use])) continue;//return array();
			if (!$id OR in_array($one[$use], $id)) $rs[$cid] = $one;
		}
		if (count($rs)==1) return current($rs);
		return $rs;
    }
	function dget($id=0,$key='hot', $rstype=0){
		$cat = $this->find();
		$rs = array();
		if ($id>0) $rs[$id] = array();
		foreach ($cat as $one){
			if (!$id AND !$one['pid'] AND !isset($rs[$one['id']]))
				$rs[$one['id']] = array();
			if (!$one[$key] OR ($id>0 AND cstrpos($one['node'],$id)===FALSE)) continue;
			$rs[($id?$id:$one['rootid'])][] = $one;
		}
		if (!$id AND !$rstype){
			foreach ($rs as $one){
				$tmp = array_merge((array)$tmp, $one);
			}
			return $tmp;
		}
		return $id>0 ? current($rs) : $rs;
	}
	private function inmap($map, $id){
		if (!$this->map OR ''===$map) return FALSE;
		return FALSE!==strpos(",{$map},", ",{$id},");
	}
    function root($by='byid'){
		$cat = $this->find();
		$data = $sort=$order=array();
		foreach ($cat as $cid=>$one){
			if (!empty($one['pid']) AND !$this->inmap($one['mapid'],0)) continue;
			if ($this->datakey){
				$data[] = $one;
				$sort[] = $one['catename'];
				$order[] = $one['orderby'];
			}else{
				$data[$cid] = $one;
				$sort[$cid] = $one['catename'];
				$order[$cid] = $one['orderby'];
			}
		}
		('byid'===$by) ? ksort($data,SORT_NUMERIC)
			: array_multisort($order, SORT_NUMERIC, SORT_ASC, $sort, SORT_STRING, SORT_ASC, $data);
		if ('byname'==$by AND !$this->datakey){
			$rs = array();
			foreach ($data as $index=>$one){
				unset($data[$index]);
				$rs[$one['id']] = $one;
			}
			return $rs;
		}
		return $data;
    }
	function siblings($id){
		$rs = $this->get($id);
		if (!$rs['pid']) return $this->root();
		$cat = $this->find();
		$data = array();
		foreach ($cat as $cid=>$one){
			if ($one['pid']!=$rs['pid'] OR $one['depth']!=$rs['depth']) continue;
			if ($this->datakey){
				$data[] = $one;
			}else{
				$data[$cid] = $one;
			}
		}
		return $data;
	}
	//取得直属下级
    function child($id, $cond=array()){
		if (!$id) return $this->root();
		$cat = $this->find();
		$data = array();
		foreach ($cat as $cid=>$one){
			if ($one['pid']!=$id OR $this->inmap($one['mapid'],$id)) continue;
			if ($cond){
				$key = key($cond);
				if (isset($one[$key]) AND current($cond) != $one[$key]) continue;
			}
			if ($this->datakey){
				$data[] = $one;
			}else{
				$data[$cid] = $one;
			}
		}
		return $data;
    }
	function parents($id, $self=FALSE){
		if (FALSE===strpos($id, ',')){
			$data = $this->get($id);
			$node = $data['fullnode'];
		}else{
			$node = $id;
		}
		$parent = array_intersect_key($this->find(), array_flip(explode(',', $node)));
		if (!$self) return array_slice($parent, 0, -1);
		return $parent;
	}
	//取得下级（下级的下级等）
    function sub($id, $self=0, $cond=array()){
		if (!$id) return $this->all();
		$cat = $this->find();
		$data = array();
		foreach ($cat as $cid=>$one){
			if (FALSE===(cstrpos($one['node'],$id))) continue;
			if ($cond){
				$key = key($cond);
				if (isset($one[$key]) AND current($cond) != $one[$key]) continue;
			}
			if ($this->datakey){
				$data[] = $one;
			}else{
				$data[$cid] = $one;
			}
		}
		if (!$self) unset($data[$id]);
		return $data;
    }
	function all(){
		return $this->find();
	}
	// 取得某一级的数据
	function depth($depth=0, $num=0){
		$cat = $this->find();
		foreach ($cat as $cid=>$one){
			if ($depth!=$one['depth']) unset($cat[$cid]);
		}
		return !$num ? $cat : array_slice($cat, $num);
	}
	function title($id,$sort='desc'){
		$rs = $this->parents($id,TRUE);
		//$count = count($rs);
		$title=array();
		$sort=='desc' ? arsort($rs) : asort($rs);
		foreach ($rs as $one){
			$title[] = $one['catename'];
		}/**/
		return $title;
	}
	/* 当前位置 */
	function position($url, $id=0, $all=FALSE){
		$pos = array();
		if (!$id) return $pos;
		//if (FALSE!==strpos($id,',')) $id = substr($id,strrpos($id,',')+1);
		$result = $this->parents($id, TRUE);
		if (!empty($result)){
			if (is_array($url)){
				$key = $url[1];
				$url = $url[0];
			}
			foreach ($result as $val){
				if (!empty($url)){
					$url = str_replace('[channel]', "{$this->config['prefix']}/", $url);
					$tmp = url(preg_replace('/\[(.[^\}\]]*?)\]/ies', '\$val[\'$1\']', $url), ($key AND isset($val[$key])) ? array($key=>$val[$key]) : '');
					$tmp = preg_replace('/\/(\d+)\-\1$/is','/\\1',$tmp);
				}
				$pos[] = empty($url)?$val['catename']:'<a href="'.$tmp.'">'.$val['catename'].'</a>';
			}
			if (!$all){
				$last = array_pop($pos);
				$pos[] = $last['catename'];
			}
		}
		return $pos;
	}
	function move($ids,$newid,$data=array()){
		if ($newid > 0){
			$newcate = $this->get($newid);
			if (!$newcate) return 'Not found new category';
		}
		foreach ($ids as $key=>$id){
			if ($newid AND FALSE!==strpos(','.$newcate['node'].',',','.$id.',')) unset($ids[$key]);
		}
		if (!$ids) return 'Not modify';
		$cate = $this->get($ids);
		$parent=array();
		$tab = $this->config['data_table'];
		foreach ($cate as $cid=>$one){
			if (!isset($parent[$one['pid']])) $parent[$one['pid']] =0;
			$parent[$one['pid']]++;
			$update = array();
			if (!$newid){
				$update['rootid'] = $cid;
				$update['node'] = $cid;
				$update['pid']=0;
				$update['depth']=1;
			}else{
				$update['rootid']=$newcate['rootid'];
				$update['pid']=$newid;
				$update['node']=(!$newcat['pid']?'':$newcate['node'].',').$newid;
				$update['depth']=$newcate['depth']+1;
			}
			Db::update($tab,$update,"id='{$cid}'");
			if ($one['childs']>0){
				$depth = $one['depth']-$update['depth'];
				Db::update($tab, array(
					"`node` = TRIM(',' FROM REPLACE(CONCAT(',',`node`,','), ',{$one['node']},','{$update['node']},'))",
					"`rootid`='{$update['rootid']}'",
					"`depth`=`depth`-{$depth}"
					), "CONCAT(`node`,',') LIKE '{$one['node']},%'");
			}
			if ($data['update_data']=='Y'){
				Db::update($this->config['data_table'], "`cids` = '{$update['node']},{$cid}'", "CONCAT(`cids`,',') LIKE '{$one['node']},{$cid},%'");
			}
		}
		Db::update($tab,array('childs'=>array('update',count($ids))),"id='$newid'");
		foreach ($parent as $id=>$num){
			Db::update($tab,array('childs'=>array('update',0-$num)),"id='$id'");
		}
		$this->find(TRUE);
		return TRUE;
	}
}
