<?php
	/*
	'软件名称：苹果CMS
	'开发作者：MagicBlack    官方网站：http://www.maccms.com/
	'--------------------------------------------------------
	'适用本程序需遵循 CC BY-ND 许可协议
	'这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
	'不允许对程序代码以任何形式任何目的的再发布。
	'--------------------------------------------------------
	*/
	if(!file_exists('inc/install.lock')) { echo '<script>location.href=\'install.php\';</script>';exit; }
	define('MAC_MODULE','home');
	require('inc/conn.php');
	require(MAC_ROOT.'/inc/common/360_safe3.php');
    $m = be('get','m');
    if(strpos($m,'.')){ $m = substr($m,0,strpos($m,'.')); }
    $par = explode('-',$m);
    $parlen = count($par);
    $ac = $par[0];
    
    if(empty($ac)){ $ac='vod'; $method='index'; }
    
    $col_num = array('id','pg','year','type','typeid','class','classid','src','level','num','aid','vid','uid');
    $col_str = array('wd','ids','pinyin','area','lang','letter','starring','directed','tag','order','by','flag','clear','ref','s','t');
    if($parlen>=2){
    	$method = $par[1];
    	 for($i=2;$i<$parlen;$i+=2){
    	     if(in_array($par[$i],$col_num)){
                 $tpl->P[trim($par[$i])] = intval($par[$i+1]);
             }
             elseif(in_array($par[$i],$col_str)){
                 $tpl->P[trim($par[$i])] = chkSql(htmlspecialchars(urldecode(trim($par[$i+1]))));
             }
        }
    }
    if($tpl->P['pg']<1){ $tpl->P['pg']=1; }
    if(!empty($tpl->P['cp'])){ $tpl->P['cp']=''; }
    unset($col_num,$col_str);

    $tpl->initData();
    $acs = array('vod','art','map','user','gbook','comment','label');
    if(in_array($ac,$acs)){
    	$tpl->P['module'] = $ac;
    	include MAC_ROOT.'/inc/module/'.$ac.'.php';
    }
    else{
    	showErr('System','未找到指定系统模块');
    }
    unset($par);
    unset($acs);
    $tpl->ifex();
    if(!empty($tpl->P['cp'])){ setPageCache($tpl->P['cp'],$tpl->P['cn'],$tpl->H); }
	$tpl->run();
	echo $tpl->H;
?>