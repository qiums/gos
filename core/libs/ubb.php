<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
require SYS_PATH. 'libs'.DS .'ubb.php';
class QC_Lib_ubb extends Lib_ubb{
	function callback($matches){
		$key=empty($this->custom_tags)?4:5;
		$str = $matches[$key];
		if ($matches[1]=='p' AND !empty($this->custom_tags) AND preg_match('/\[('.$this->custom_tags.').[^\]]*?\]/is',$str)) return $str;
		return call_user_func(array('QC_UbbTags',"parse"),$matches[1],$str,$matches[$key-1],$this->edit);
	}
}
class QC_UbbTags extends UbbTags{
	static public function parse($tag,$str,$args,$edit){
		$me = &getInstance('QC_UbbTags');
		if (array_key_exists($tag,$me->short_tags)) return '<'.$me->short_tags[$tag].'>'.nl2br($str).'</'.$me->short_tags[$tag].'>';
		if (method_exists($me,"parse_{$tag}")) return call_user_func(array($me,"parse_{$tag}"),$str,$args,$edit);
		return '<div id="'.$tag.'-'.$args.'" class="ubbparse ubb'.$tag.'"></div>';
	}
	function parse_localimg($str,$args,$edit){
		//import('libs.image');
		$args=explode('|',$args);
		$args[0] = preg_replace('/^'.str_replace(array('\\','/'),array('\\\\','\/'),gc('env.webroot')).'+/is','/',$args[0]);
		$args[0]=preg_replace('/\_\d+x\d+(\_water)*/is','',$args[0]);
		if ($edit) return '<img src="'.(upwebroot(FALSE).ltrim($args[0],'/')).'" class="localimg" title="'.$str.'" />';
		$style = 'style="';
		$class = 'dload localimg';
		if (isset($args[3])){
			$class .= ' '.$args[3].'img';
			$style .= 'float:'.$args[3].';';
		}
		$style .= '"';
		$src = fileurl($args[0]);
		return '<span class="'.$class.'"><a href="'.$src.'" rel="gallery" title="'.$str.'" '.$style.'></a>'.
			(empty($str)?'':"<strong>{$str}</strong>").'</span>';
	}
	function parse_img($str,$args){
		$args=explode('|',$args);
		$style = (isset($args[1])?'width:'.$args[1].'px;':'')
			.(isset($args[2])?'height:'.$args[2].'px;':'');
		$class = 'preimg remoteimg';
		if (isset($args[3])){
			$class .= ' '.$args[3].'img';
			$style .= 'float:'.$args[3].';';
		}
		if ($style) $style = "style=\"{$style}\"";
		return '<a href="'.$args[0].'" class="'.$class.'" rel="gallery" title="'.$str.'"'.$style.'></a>';
	}
	function parse_download($str, $args){
		//if (
	}
}
