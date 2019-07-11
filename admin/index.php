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
?>
<?php
    require('admin_conn.php');
    
    $p = array();
    $m = be('get','m');
    $par = explode('-',$m);
    $parlen = count($par);
    $ac = $par[0];
    
    if(empty($ac)){ $ac='admin'; $method='index'; }
    $colnum = array('id','pg');
    if($parlen>=2){
    	$method = $par[1];
    	 for($i=2;$i<$parlen;$i+=2){
            $p[$par[$i]] = in_array($par[$i],$colnum) ? intval($par[$i+1]) : urldecode($par[$i+1]);
        }
    }
    if($p['pg']<1){ $p['pg']=1; }
    unset($colnum);
    
    if($method!='login' && $method!='check'){
    	chkLogin();
    }
    
    $acs = array('vod','art','admin','user','make','collect','coll','system','extend','template','db','domain');
    
    if(in_array($ac,$acs)){
    	$plt = new Template(MAC_ADMIN."/tpl/html/");
    	include 'tpl/module/'.$ac.'.php';
    	$plt->set_file("header", "admin_head.html");
    	$plt->set_file("footer", "admin_foot.html");
    	$plt->parse("head", "header");
		$plt->parse("foot", "footer");
		
		$plt->set_var("MAC_ADMINNAME",getCookie('adminname'));
    	$plt->set_var("MAC_VERSION",MAC_VERSION);
    	$plt->set_var("MAC_URL",MAC_URL);
    	$plt->set_var("MAC_NAME",MAC_NAME);
    	$plt->set_var("MAC_RUNTIME",getRunTime());
    	$plt->parse('mains', 'main');
    	$plt->p("mains");
    	
    	if($method=='wel'){
    		echo '<span style="display:none"><script src="//update.maccms.com/v8/?c=check&v='.MAC_VERSION.'&p='.PHP_VERSION.'&t='.time().'"></script></span>';
    	}
    }
    else{
    	showErr('System','未找到指定系统模块');
    }
    unset($par);
    unset($acs);
    unset($p);
?>