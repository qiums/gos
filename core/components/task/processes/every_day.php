<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
$time = D::add('d', 0, 1);
$dbconf = Db::get('conf');
$dbpre = $dbconf[Db::get('index')]['prefix'];
/**/
//For Events Update
$join = array(
	'arcdata' => array('aid,aid',NULL, array('mid'=> 5)),
	);
$wday = D::cdate('w');
Db::update('events', array('runstat'=>0));
// Today
$cond['and&or'] = array(
	array(
		'events.runtype'=>0
	),
	array(
		'events.runtype'=> 3,
		'arcdata.begindate'=> "{$time}",
	),
	array(
		'events.runtype'=> 2,
		'arcdata.begindate'=> "<= {$time}",
		'arcdata.enddate'=> ">= {$time}",
	),
	array(
		'events.runtype' => 1,
		'events.runweek' => "LIKE %{$wday}%",
		'arcdata.begindate'=>array(
			'or',
			array("<= {$time}", '0'),
		),
		'arcdata.enddate'=>array(
			'or',
			array(">= {$time}", '0'),
		),
	),
);
Db::set('jointable', $join);
Db::update('events', array('runstat'=>2), $cond);
// Upcoming
$next = ($wday+1)%7;
$cond['and&or'] = array(
	array(
		'events.runtype'=> 3,
		'arcdata.begindate'=> "> {$time}",
	),
	array(
		'events.runtype'=> 2,
		'arcdata.begindate'=> "> {$time}",
		//'arcdata.enddate'=>"> {$time}",
	),
	array(
		'events.runtype' => 1,
		//'events.runweek' => "LIKE %{$next}%",
		'arcdata.enddate'=>array(
			'or',
			array("> {$time}", '0'),
		),
	),
);
Db::set('jointable', $join);
Db::update('events', array('runstat'=>1), $cond);
//return ;
//For Article/Album
$join = array(
	'arcdata' => array('aid,id',NULL,array('mid'=>array(2,3))),
	'searchindex'=>array('aid,id', NULL, array('mid'=>array(2,3))),
);
Db::set('jointable', $join);
$cond = array('publock'=>0);
$cond['and&or'] = array(
	array('arcdata.begindate'=>'> 0'),
	array('arcdata.enddate'=>'> 0'),
);
Db::update('archives', array('published'=>1, 'searchindex.published'=>1), $cond);
$cond = array('publock'=>0);
$cond['and&or'] = array(
	array('arcdata.begindate'=> "> {$time}"),
	array(
		'arcdata.enddate'=> array('and',
			array("< {$time}", "> 0"),
		),
	),
);
Db::set('jointable', $join);
Db::update('archives', array('published'=>0, 'searchindex.published'=>0), $cond);

//For Archives Attrib
//Article&Album&Events
$join = array(
	'arcdata' => array('aid,id',NULL,array('mid'=>array(2,3,5))),
);
$cond = array(
	'arcdata.expiretime'=>array('and',
		array("< {$time}", '> 0'),
	),
);
Db::set('jointable', $join);
Db::update('archives', array('attrib'=>0), $cond);
//Listings
$join = array(
	'arcdata' => array('aid,id',NULL,array('mid'=>4)),
);
Db::set('jointable', $join);
Db::update('venues', array('attrib'=>0), $cond);
/***********************
	For Ads
***********************/
/*$cond = array('publock'=>0);
// Set all record to UnPublished
$ads = Model('app.ads');
$ads->update(array('published'=>0), $cond);
$cond += array('begindate'=>"<= $time", 'enddate'=>">= $time");
$ads->update(array('published'=>1), $cond);
$ads->cache(TRUE);
unset($ads);*/
?>