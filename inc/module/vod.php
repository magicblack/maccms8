<?php
if(!defined('MAC_ROOT')){
	exit('Access Denied');
}
if($method=='index')
{
	$tpl->C["siteaid"] = 10;
	if($tpl->P['pg']<1){ $tpl->P['pg']=1; }
	$tpl->P['cp'] = 'app';
	$tpl->P['cn'] = 'vod_index'.$tpl->P['pg'];
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	$tpl->H = loadFile(MAC_ROOT_TEMPLATE."/vod_index.html");
	getDbConnect();
	$tpl->mark();
	$tpl->pageshow();
}

elseif($method=='map')
{
	$tpl->C["siteaid"] = 11;
	$tpl->P['cp'] = 'app';
	$tpl->P['cn'] = 'vod_map';
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	$tpl->H = loadFile(MAC_ROOT_TEMPLATE."/vod_map.html");
	getDbConnect();
	$tpl->mark();
}

elseif($method=='list')
{
	$tpl->C["siteaid"] = 12;
    $tpl->P['cp'] = 'vodlist';
	$tpl->P['cn'] = $tpl->P['id'].'-'.$tpl->P['pg'].'-'.$tpl->P['order'].'-'.$tpl->P['by'].'-'.$tpl->P['year'].'-'.$tpl->P['letter'].'-'.$tpl->P['class'].'-'.urlencode($tpl->P['area']).'-'.urlencode($tpl->P['lang']);
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	$tpl->P['vodtypeid'] = $tpl->P['id'];
	
	$tpl->T = $MAC_CACHE['vodtype'][$tpl->P['vodtypeid']];
	if(!is_array($tpl->T)){ showMsg("获取数据失败，请勿非法传递参数","../"); }
	getDbConnect();
	if(!getUserPopedom($tpl->P['id'], 'list')){ showMsg('您没有权限浏览此列表页', '../user/'); }
	$tpl->P['dp']=true;
	$tpl->loadlist ('vod');
	$tpl->P['dp']=false;
	$tpl->mark();
	$tpl->pageshow();
}

elseif($method=='type')
{
	$tpl->C["siteaid"] = 12;
    $tpl->P['cp'] = 'vodtype';
	$tpl->P['cn'] = $tpl->P['id'].'-'.$tpl->P['pg'] ;
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	$tpl->P['vodtypeid'] = $tpl->P['id'];
	
	$tpl->T = $MAC_CACHE['vodtype'][$tpl->P['vodtypeid']];
	if(!is_array($tpl->T)){ showMsg("获取数据失败，请勿非法传递参数","../"); }
	getDbConnect();
	if(!getUserPopedom($tpl->P['id'], 'list')){ showMsg('您没有权限浏览此列表页', '../user/'); }
	$tpl->P['dp']=true;
	$tpl->loadtype ('vod');
	$tpl->P['dp']=false;
	$tpl->mark();
	$tpl->pageshow();
}

elseif($method=='topicindex')
{
	$tpl->C["siteaid"] = 13;
	$tpl->P['cp'] = 'vodtopicindex';
	$tpl->P['cn'] = $tpl->P['pg'];
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	getDbConnect();
	$tpl->H = loadFile(MAC_ROOT_TEMPLATE."/vod_topicindex.html");
	$tpl->mark();
	$tpl->pageshow();
}

elseif($method=='topic')
{
	$tpl->C["siteaid"] = 14;
	$tpl->P['cp'] = 'vodtopic';
	$tpl->P['cn'] = $tpl->P['id'].'-'.$tpl->P['pg'].'-'.$tpl->P['order'].'-'.$tpl->P['by'];
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	$tpl->P['vodtopicid'] = $tpl->P['id'];
	$tpl->T = $MAC_CACHE['vodtopic'][$tpl->P['vodtopicid']];
	if (!is_array($tpl->T)){ showMsg("获取数据失败，请勿非法传递参数","../"); }
	getDbConnect();
	$tpl->loadtopic('vod');
	$tpl->pageshow();
}

elseif($method=='search')
{
	$tpl->C["siteaid"] = 15;
	$wd = trim(be("all", "wd")); $wd = chkSql($wd);
	if(!empty($wd)){ $tpl->P["wd"] = $wd; }

	//if(empty($tpl->P["wd"]) && empty($tpl->P["ids"]) && empty($tpl->P["pinyin"]) && empty($tpl->P["starring"]) && empty($tpl->P["directed"]) && empty($tpl->P["area"]) && empty($tpl->P["lang"]) && empty($tpl->P["year"]) && empty($tpl->P["letter"]) && empty($tpl->P["tag"]) && empty($tpl->P["type"]) && empty($tpl->P["typeid"]) && empty($tpl->P["classid"]) ){ alert ("搜索参数不正确"); }
	
	if ( $tpl->P['pg']==1 && getTimeSpan("last_searchtime") < $MAC['app']['searchtime']){ 
		showMsg("请不要频繁操作，时间间隔为".$MAC['app']['searchtime']."秒",MAC_PATH);
		exit;
	}

    if(intval($MAC['app']['searchlen'])<1){
        $MAC['app']['searchlen'] = 10;
    }

    if(mb_strlen($wd) > $MAC['app']['searchlen']){
        $wd = substring($wd,$MAC['app']['searchlen']);
        $tpl->P["wd"] = $wd;
    }
	
    $tpl->P['cp'] = 'vodsearch';
	$tpl->P['cn'] = urlencode($tpl->P['wd']).'-'.$tpl->P['pg'].'-'.$tpl->P['order'].'-'.$tpl->P['by'].'-'.$tpl->P['ids']. '-'.$tpl->P['pinyin']. '-'.$tpl->P['type'].  '-'.$tpl->P['year']. '-'.$tpl->P['letter'].'-'.$tpl->P['typeid'].'-'.$tpl->P['classid'].'-'.urlencode($tpl->P['area']) .'-'.urlencode($tpl->P['lang'])  .'-'.urlencode($tpl->P['tag']) .'-'.urlencode($tpl->P['starring']) .'-'.urlencode($tpl->P['directed']) ;
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	$tpl->P["where"]='';
	$tpl->P["des"]='';
	
	getDbConnect();
	
	
	if (!empty($tpl->P["year"])){
		$tpl->P["key"]=$tpl->P["year"];
		$tpl->P["des"] = $tpl->P["des"] ."&nbsp;上映年份为".$tpl->P["year"];
	}
    if (!empty($tpl->P["letter"])){
    	$tpl->P["key"]=$tpl->P["letter"];
    	$tpl->P["des"] = $tpl->P["des"] . "&nbsp;首字母为" . $tpl->P["letter"];
    }
    if(!empty($tpl->P["area"])){
    	$tpl->P["key"]=$tpl->P["area"];
    	$tpl->P["des"] = $tpl->P["des"] . "&nbsp;地区为" . $tpl->P["area"];
    }
    if (!empty($tpl->P["lang"])){
    	$tpl->P["key"]=$tpl->P["lang"];
    	$tpl->P["des"] = $tpl->P["des"] . "&nbsp;语言为" . $tpl->P["lang"];
    }
    
    if($tpl->P["wd"]=='{wd}'){ $tpl->P["wd"]=''; }
    if (!empty($tpl->P["wd"])) {
    	$tpl->P["key"]=$tpl->P["wd"] ;
    	$tpl->P["des"] = $tpl->P["des"] . "&nbsp;名称或主演为" . $tpl->P["wd"];
    }
    
    if (!empty($tpl->P["pinyin"])){
    	$tpl->P["key"]=$tpl->P["pinyin"] ;
    	$tpl->P["des"] = $tpl->P["des"] . "&nbsp;拼音为" . $tpl->P["pinyin"];
    }
	    
	if (!empty($tpl->P["starring"])){
		$tpl->P["key"]=$tpl->P["starring"] ;
		$tpl->P["des"] = $tpl->P["des"] . "&nbsp;主演为" . $tpl->P["starring"];
	}
	
	if (!empty($tpl->P["directed"])){
		$tpl->P["key"]=$tpl->P["directed"] ;
		$tpl->P["des"] = $tpl->P["des"] . "&nbsp;导演为" . $tpl->P["directed"];
	}
    
    if (!empty($tpl->P["tag"])){
		$tpl->P["key"]=$tpl->P["tag"] ;
		$tpl->P["des"] = $tpl->P["des"] . "&nbsp;Tag为" . $tpl->P["tag"];
	}
	
    $tpl->P['typepid'] = 0;
	if(!empty($tpl->P["typeid"])){
		$typearr = $MAC_CACHE['vodtype'][$tpl->P['typeid']];
		if (is_array($typearr)){
			$tpl->P['typepid'] = $typearr['t_pid'];
			if (empty($tpl->P["key"])){ $tpl->P["key"]= $typearr["t_name"];  }
			$tpl->P["des"] = $tpl->P["des"] . "&nbsp;分类为" . $typearr["t_name"];
		}
		unset($typearr);
	}
	if(!empty($tpl->P["classid"])){
		$classarr = $MAC_CACHE['vodclass'][$tpl->P['classid']];
		if (is_array($classarr)){
			if (empty($tpl->P["key"])){ $tpl->P["key"]= $classarr["c_name"];  }
			$tpl->P["des"] = $tpl->P["des"] . "&nbsp;剧情分类为" . $classarr["c_name"];
		}
		unset($classarr);
	}
	if(!empty($tpl->P["ids"])){
		$arr = explode(',',$tpl->P["ids"]);
		for($i=0;$i<count($arr);$i++){
			$arr[$i] = intval($arr[$i]);
		}
		$tpl->P["ids"] = join(',',$arr);
	}
	
	$tpl->H = loadFile(MAC_ROOT_TEMPLATE."/vod_search.html");
	$tpl->mark();
	$tpl->pageshow();
	
	$colarr = array('{page:des}','{page:key}','{page:now}','{page:order}','{page:by}','{page:wd}','{page:wdencode}','{page:pinyin}','{page:letter}','{page:year}','{page:starring}','{page:starringencode}','{page:directed}','{page:directedencode}','{page:area}','{page:areaencode}','{page:lang}','{page:langencode}','{page:typeid}','{page:typepid}','{page:classid}');
	$valarr = array($tpl->P["des"],$tpl->P["key"],$tpl->P["pg"],$tpl->P["order"],$tpl->P["by"],$tpl->P["wd"],urlencode($tpl->P["wd"]),$tpl->P["pinyin"],$tpl->P["letter"],$tpl->P['year']==0?'':$tpl->P['year'],$tpl->P["starring"],urlencode($tpl->P["starring"]),$tpl->P["directed"],urlencode($tpl->P["directed"]),$tpl->P["area"],urlencode($tpl->P["area"]),$tpl->P["lang"],urlencode($tpl->P["lang"]),$tpl->P['typeid'],$tpl->P['typepid'] ,$tpl->P['classid']  );
	
	$tpl->H = str_replace($colarr, $valarr ,$tpl->H);
    unset($colarr,$valarr);
    $linktype = $tpl->getLink('vod','search','',array('typeid'=>$tpl->P['typepid']));
    $linkyear = $tpl->getLink('vod','search','',array('year'=>''));
    $linkletter = $tpl->getLink('vod','search','',array('letter'=>''));
    $linkarea = $tpl->getLink('vod','search','',array('area'=>''));
    $linklang = $tpl->getLink('vod','search','',array('lang'=>''));
    $linkclass = $tpl->getLink('vod','search','',array('classid'=>''));
    
    
    $linkorderasc = $tpl->getLink('vod','search','',array('order'=>'asc'));
    $linkorderdesc = $tpl->getLink('vod','search','',array('order'=>'desc'));
    $linkbytime = $tpl->getLink('vod','search','',array('by'=>'time'));
    $linkbyhits = $tpl->getLink('vod','search','',array('by'=>'hits'));
    $linkbyscore = $tpl->getLink('vod','search','',array('by'=>'score'));
    
    $tpl->H = str_replace(array('{page:linkyear}','{page:linkletter}','{page:linkarea}','{page:linklang}','{page:linktype}','{page:linkclass}','{page:linkorderasc}','{page:linkorderdesc}','{page:linkbytime}','{page:linkbyhits}','{page:linkbyscore}',), array($linkyear,$linkletter,$linkarea,$linklang,$linktype,$linkclass,$linkorderasc,$linkorderdesc,$linkbytime,$linkbyhits,$linkbyscore) ,$tpl->H);
    $_SESSION["last_searchtime"]  = time();
}

elseif($method=='detail')
{
	$tpl->C["siteaid"] = 16;
	$tpl->P['cp'] = 'vod';
	$tpl->P['cn'] = $tpl->P['id'];
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	getDbConnect();
	$sql = "SELECT * FROM {pre}vod WHERE d_hide=0 AND d_id=" . $tpl->P['id'];
	$row = $db->getRow($sql);
	if(!$row){ showMsg("获取数据失败，请勿非法传递参数",MAC_PATH); }
	if(!getUserPopedom($row["d_type"], "vod")){ showMsg ("您没有权限浏览内容页", MAC_PATH."index.php?m=user-index.html"); }
	$tpl->T = $MAC_CACHE['vodtype'][$row['d_type']];
	$tpl->D = $row;
	unset($row);
	$tpl->loadvod("detail");
	$tpl->replaceVod();
	$tpl->playdownlist ("play");
	$tpl->playdownlist ("down");
}
	
elseif($method=='play')
{
	$tpl->C["siteaid"] = 17;
    $tpl->P['cp'] = 'vodplay';
	$tpl->P['cn'] = $tpl->P['id'].'-'.$tpl->P['src'].'-'.$tpl->P['num'];
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	getDbConnect();
	$sql = "SELECT * FROM {pre}vod WHERE d_hide=0 AND d_id=" . $tpl->P['id'];
	$row = $db->getRow($sql);
	if(!$row){ showMsg("获取数据失败，请勿非法传递参数",MAC_PATH); }
	if(!getUserPopedom($row["d_type"],"play")){ 
		showMsg ("您没有权限浏览播放页",MAC_PATH."index.php?m=user-index.html"); 
	}
	if ($MAC['user']['status']==1){
		$uid = intval($_SESSION['userid']);
		if($row["d_stint"]>0 && $uid==0 ){ showMsg ("此为收费数据请先登录再观看",MAC_PATH."index.php?m=user-index.html"); }
		
		$rowu = $db->getRow("SELECT * FROM {pre}user where u_id=".$uid);
		if ($rowu){
			$stat =false;
			$upoint = $rowu["u_points"];
			$playf = ",".$tpl->P['id']."-".$tpl->P['src']."-".$tpl->P['num'].",";
			if($rowu["u_flag"]==1){
				if(time() >= $rowu["u_end"]){ $msg = "对不起,您的会员时间已经到期,请联系管理员续费!"; }
			}
			elseif ($rowu["u_flag"] == 2){
				if(($rowu["u_start"]>= $rowu["u_ip"]) &&  ($rowu["u_ip"] <= $rowu["u_end"])){$stat=true; }
				if(!$stat){ $msg = "对不起,您登录IP段不在受理范围，请联系管理员续费!";}
			}
			else{
				if ($rowu["u_points"] < $row["d_stint"]){
					if(strpos(",".$rowu["u_plays"],$playf)){ $stat=true; }
					if(!$stat){ $msg = "对不起,您的积分不够，无法观看收费数据，请推荐本站给您的好友、赚取更多积分";}
				}
				$upoint = $rowu["u_points"] - $row["d_stint"];
			}
			if (!empty($msg)){ alertUrl ($msg,MAC_PATH."index.php?m=user-index.html");exit;}
			if (strpos(",".$rowu["u_plays"],$playf) > 0){ $stat = true;}
			if (!$stat){
				$uplays = ",".$rowu["u_plays"].$playf;
				$uplays = str_replace(",,",",",$uplays);
				$db->Update ("{pre}user" ,array("u_points","u_plays"),array($upoint,$uplays),"u_id=".$uid);
			}
		}
		unset($rowu);
	}
	$tpl->T = $MAC_CACHE['vodtype'][$row['d_type']];
	$tpl->D = $row;
	unset($row);
	$tpl->loadvod('play');
	$tpl->replaceVod();
	$tpl->playdownlist('play');
	$tpl->H = str_replace('[vod:playnum]',$tpl->P['num'],$tpl->H);
	$tpl->H = str_replace('[vod:playsrc]',$tpl->P['src'],$tpl->H);
	$tpl->getUrlName('play');
	$tpl->H = str_replace('[vod:playerinfo]', '<script>' .$tpl->getUrlInfo('play'). ' </script>'. "\n" ,$tpl->H);
	$tpl->H = str_replace('[vod:player]', '<script src="'.$MAC['site']['installdir'].'js/playerconfig.js?t='.MAC_TSP.'"></script><script src="'.$MAC['site']['installdir'].'js/player.js?t='.MAC_TSP.'"></script>'. "\n" ,$tpl->H);
	$tpl->playdownlist ("down");
	
}

elseif($method=='down')
{
	$tpl->C["siteaid"] = 18;
	$tpl->P['cp'] = 'voddown';
	$tpl->P['cn'] = $tpl->P['id'].'-'.$tpl->P['src'].'-'.$tpl->P['num'];
	echoPageCache($tpl->P['cp'],$tpl->P['cn']);
	getDbConnect();
	$sql = "SELECT * FROM {pre}vod WHERE d_hide=0 AND d_id=" . $tpl->P['id'];
	$row = $db->getRow($sql);
	if(!$row){ showMsg("获取数据失败，请勿非法传递参数",MAC_PATH); }
	if (!getUserPopedom($row["d_type"],"down")){ showMsg ("您没有权限浏览播放页",MAC_PATH."index.php?m=user-index.html"); }
	if ($MAC['user']['status']==1){
		$uid = intval($_SESSION['userid']);
		if($row["d_stint"]>0 && $uid==0 ){ showMsg ("此为收费数据请先登录再观看",MAC_PATH."index.php?m=user-index.html"); }
		$rowu = $db->getRow("SELECT * FROM {pre}user where u_id=".$uid);
		if ($rowu){
			$stat =false;
			$upoint = $rowu["u_points"];
			$downf = ",".$tpl->P['id']."-".$tpl->P['src']."-".$tpl->P['num'].",";
			if($rowu["u_flag"]==1){
				if(time() >= $rowu["u_end"]){ $msg = "对不起,您的会员时间已经到期,请联系管理员续费!"; }
			}
			elseif ($rowu["u_flag"] == 2){
				if(($rowu["u_start"]>= $rowu["u_ip"]) &&  ($rowu["u_ip"] <= $rowu["u_end"])){$stat=true; }
				if(!$stat){ $msg = "对不起,您登录IP段不在受理范围，请联系管理员续费!";}
			}
			else{
				if ($rowu["u_points"] < $row["d_stint"]){
					if(strpos(",".$rowu["u_downs"],$downf)){ $stat=true; }
					if(!$stat){ $msg = "对不起,您的积分不够，无法下载收费数据，请推荐本站给您的好友、赚取更多积分";}
				}
				$upoint = $rowu["u_points"] - $row["d_stint"];
			}
			if (!empty($msg)){ alertUrl ($msg,MAC_PATH."index.php?m=user-index.html");exit;}
			if (strpos(",".$rowu["u_downs"],$downf) > 0){ $stat = true;}
			if (!$stat){
				$udowns = ",".$rowu["u_downs"].$downf;
				$udowns = str_replace(",,",",",$udowns);
				$db->Update ("{pre}user" ,array("u_points","u_downs"),array($upoint,$udowns),"u_id=".$uid);
			}
		}
		unset($rowu);
	}
	$tpl->T = $MAC_CACHE['vodtype'][$row['d_type']];
	$tpl->D = $row;
	unset($row);
	$tpl->loadvod ("down");
	$tpl->replaceVod();
	$tpl->playdownlist ("down");
	$tpl->H = str_replace("[vod:downnum]",$tpl->P["num"],$tpl->H);
	$tpl->H = str_replace("[vod:downsrc]",$tpl->P["src"],$tpl->H);
	$tpl->getUrlName("down");
	$tpl->H = str_replace("[vod:downinfo]", "<script>" .$tpl->getUrlInfo("down"). " </script>". "\n" ,$tpl->H);
	$tpl->H = str_replace('[vod:downer]', '<script src="'.$MAC['site']['installdir'].'js/playerconfig.js?t='.MAC_TSP.'"></script><script src="'.$MAC['site']['installdir'].'js/player.js?t='.MAC_TSP.'"></script>'. "\n" ,$tpl->H);
	$tpl->playdownlist('play');
}

else
{
	showErr('System','未找到指定系统模块');
}
?>