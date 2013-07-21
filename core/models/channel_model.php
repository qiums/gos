<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class channel_model extends model{
	private $cache_data = array();

	public function __construct(){
		parent::__construce();
		$this->config['data_table'] = 'channels';
		$this->config['sort_fields'] = 'id,asc';
		return parent::model();
	}
	public function get($channel_name=''){
		$key = 'channels.data';
		$channel = $this->cache_data[$key];
		if (TRUE === $channel_name OR !$channel){
			if (TRUE === $channel_name OR FALSE===($channel = cache::q($key))){
				$data = $this->findAll();
				$channel = array();
				foreach ($data as $i=>$one){
					if (!empty($one['other_setting']))
						$one = array_merge($one, parse_ini_string($one['other_setting'],TRUE));
					unset($one['other_setting']);
					$channel[$one['prefix']] = $one;
				}
				cache::q($key, $channel);
				unset($data);
			}
			$this->cache_data['channels.data'] = $channel;
		}
		if (!$channel) return array();
		if ($channel_name){
			if (is_numeric($channel_name)){
				foreach ($channel as $one)
					if ($one['id']==$channel_name) return $one;
			}
			if (isset($channel[$channel_name])) return $channel[$channel_name];
			return array();
		}
		return $channel;
	}
	public function sget(){
		$data = $this->get();
		$res = array();
		foreach ($data as $one){
			$res[$one['id']] = $one['channel_name'];
		}
		return $res;
	}
	/*
	 * $mode - 0 获取自定义字段 1 获取搜索字段 2 获取列表字段 3 添加/修改内容时的字段(含channel.common的字段)
	 * 4 1+3的组合 5 2+3的组合 大于5相当于0+3的组合
	 */
	public function get_fields($mid, $mode=0, $group=NULL){
		$cache_key = 'channels.field'.$mid;
		$fields = $this->cache_data[$cache_key];
		if (!$fields){
			if (FALSE===($fields = cache::q($cache_key))){
				/*$fields = $this->db()
					->where('mid', $mid)
					->order('sort', 'desc')
					->findAll('fields');
				cache::q($cache_key, $fields);*/
				$fields = array();
			}
			$this->cache_data[$cache_key] = $fields;
		}
		if (3<=$mode){
			$common_fields = $this->cache_data['common.fields'];
			if (!$common_fields){
				if (FALSE === ($common_fields = import('data.form.channel_common'))) return $fields;
				$this->cache_data['common.fields'] = $common_fields;
			}//dump($common_fields);dump($fields);
			$fields = $common_fields + (array)$fields;
		}
		if (!$fields) return array();
		$mode -= 3;
		$filter = array(NULL, 'search', 'islist');
		if ($filter[$mode]){
			foreach ($fields as $key=>&$one){
				if (!isset($one[$filter[$mode]])){
					unset($fields[$key]);
					continue;
				}
				if ('search'===$filter[$mode]){
					$one['attr'] = preg_replace('/(req-\w+|minlength\d+)/is', '', $one['attr']);
					/*unset($fields[$key]);
					$fields[basename(str_replace('.', '/', $key))] = $one;*/
				}
			}
		}
		if (!is_null($group)){
			foreach ($fields as $key=>$one){
				if (intval($group) !== intval($one['group'])) unset($fields[$key]);
			}
		}
		return $fields;
	}
	public function get_search($mid){
		$fields = is_numeric($mid) ? $this->get_fields($mid, 4) : $mid;
		if (!$fields) return array();
		$mode = array();
		foreach ($fields as $key=>$one){
			$search = FALSE!==strpos($one['search'], '|') ? '|' : (FALSE!==strpos($one['search'], '&') ? '&' : NULL);
			if (!$search) continue;
			$name = basename(str_replace('.', '/', $key));
			$tmp = explode($search, $one['search']);
			$mode[$tmp[0]]['option'][$tmp[1]][] = lang("form_label.{$name}");
			unset($fields[$key]);
		}
		if (!$mode) return $fields;
		foreach ($mode as $key => $one) {
			if (1<count($one['option'])) $fields["f{$key}"] = array('type' => 'select', 'option' => $one['option']);
			$fields[$key] = array('type' => 'input:text');
		}
		return $fields;
	}
	public function parse_sort($sort){
		$sort = explode('|', $sort);
		foreach ($sort as $key=>$one){
			$one = explode(',', $one);
			$sort[isset($one[2]) ? $one[2] : $one[0]] = array($one[0], $one[1]);
			unset($sort[$key]);
		}
		return $sort;
	}
	public function parse_option($string){
		$string = explode("\n", $string);
		$rs = array();
		foreach ($string as $one){
			if (!trim($one)) continue;
			$one = explode('=', trim($one));
			$rs[$one[0]] = ('sort_fields'==$one[0]) ? $this->parse_sort($one[1]) : $one[1];
		}
		return $rs;
	}
	public function parse_fields($fields){
		preg_match_all('/<field:([\w\.]+?)\s+(.+?)\/>/is', $fields, $matches);
		$fields = array();
		foreach ($matches[1] as $key=>$name){
			$fields[$name] = $this->parse_field_params($matches[2][$key]);//$m;
			//$fields['search'][$name] = FALSE!==strpos($matches[2][$key], 'search="true"');
		}
		return $fields;
	}
	public function parse_field_params($params){
		preg_match_all('/(\w+?)="(.+?)"/is', $params, $matches);
		$params = array();
		foreach ($matches[1] as $key=>$name){
			$params[$name] = $matches[2][$key];
		}
		return $params;//import('libs.form')->run($params);
	}
	public function parse_field_option($fields){
		$option = explode("\n", $fields['element_option']);
		$rs = array('option' => array());
		foreach ($option as $index=>$one){
			$ex = explode('|', trim($one));
			if ($ex[0] == 'd'){
				$rs['optext'] = (count($ex)==3 AND $ex[1]) ? array($ex[1] => $ex[2]) : $ex[1];
			}else{
				if (count($ex)==2){
					$rs['option'][$ex[0]] = $ex[1];
				}else{
					$rs['option'][$index] = $ex[0];
				}
			}
		}
		if (!$rs['optext']) $rs['optext'] = $fields['field_name'];
		return $rs;
	}
	public function merge_fields($field, $data, $syn, $use_alias=1){
		$field = explode('|', $field);
		if (!$syn AND isset($field[1])) unset($field[1]);
		$updata = array();
		foreach ($field as $row){
			$row = explode(',', $row);
			foreach ($row as $key){
				$alias = str_replace('subdata_','',$key);
				if (isset($data[$alias])) $updata[$use_alias ? $key : $alias] = $data[$alias];
			}
		}
		return $updata;
	}
}
?>