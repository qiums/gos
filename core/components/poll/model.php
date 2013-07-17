<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class app_poll_model extends Model{
	function findAll($cond, $haveopt=0){
		$order = isset($cond['order'])?$cond['order']:array('id'=>'desc');
		//$this->fields = isset($cond['fields'])?$cond['fields']:'*';
		$cond['pollid'] = 0;
		if (!isset($cond['nojoin'])){
			$this->join_table('members', array('id,uid','username,nickname,avatar'));
		}
		if ('running'==$cond['filter']){
			$time = D::get('curtime');
			$order = array('sortby'=>'desc','bdate'=>'asc');
			$cond['edate'] = ">= $time";
		}
		unset($cond['filter'],$cond['order'],$cond['fields']);
		$root = parent::findAll($cond, 3);
		if (!$haveopt) return $root;
		$rs = isset($root['loopdata'])?$root['loopdata']:$root;
		$ids = array_keys($rs);
		if (!$rs) return $rs;
		$data=parent::findAll(array('pollid'=>$ids),array('sortby'=>'desc','createtime'=>'desc'),-1);
		foreach ($rs as $key=>$one){
			$rs[$key]['options']=array();
			foreach ($data as $option){
				if ($one['id']!=$option['pollid']) continue;
				$rs[$key]['options'][]=$one;
			}
		}
		return $rs;
	}
	function find($cond=array()){
		$order = isset($cond['order'])?$cond['order']:array('id'=>'desc');
		$cond['pollid']=0;
		$cond['published']=1;
		if ('running'==$cond['filter']){
			$time = D::get('curtime');
			$dbconf = Db::get('conf');
			$tab = $dbconf[Db::get('index')]['prefix'].$this->conf['data_table'];
			$cond[] = "($tab.`bdate`<= $time OR $tab.`bdate`=0) AND ($tab.`edate`>= $time OR $tab.`edate`=0)";
			$order = array('sortby'=>'desc','bdate'=>'asc');
		}
		unset($cond['filter'],$cond['order']);
		$one = parent::find($cond,$order);
		if (!$one) return array();
		$one['options'] = parent::findAll(array('pollid'=>$one['id'],'published'=>1),array('sortby'=>'desc','createtime'=>'desc'));
		return $one;
	}
	function single($args){
		$args = S::split_string($args);
		$data = $this->find($args+array('published'=>1));
		$content = IctplTags::parse(file_get_contents(dirname(__FILE__).'/views/layout.html'));
		eval('?>'.$content);
	}
}
?>