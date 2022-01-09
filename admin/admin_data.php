<?php
require(dirname(__FILE__) .'/admin_conn.php');
chkLogin();

$name = be("all","name");
$ac = be("all","ac");
$flag = be("all","flag");
$show = intval(be("all","show"));
$id = be("all","id");
$tab=be('all','tab');
$colid=be('all','colid');
$col=be('all','col');
$val=trim(be('all','val'));
$ajax=be('all','ajax');

if($ac=='checkcache'){
	$res='no';
	if(file_exists(MAC_ROOT.'/cache/cache_data.lock')){
		$res='haved';
	}
	echo $res;
}

else if($ac=='getinfo')
{
	$tab = be("all","tab");
	$col = be("all","col");
	$val = be("all","val");
	$col2 = be("all","col2");
	$val2 = be("all","val2");
	$sql = "SELECT * from {pre}".$tab." WHERE ".$col."=".$val;
	if(!empty($col2)){
		$sql.=' and '.$col2 ."=".$val2;
	}
	$rs = $db->queryArray($sql,false);
	$nums = count($rs);
	$str = json_encode($rs);
	
	if($nums==0){
		echo '[]';
	}
	elseif($nums==1){
		echo substr($str,1,strlen($str)-2);
	}
	else{
		echo $str;
	}
	unset($rs);
}

else if($ac=='save')
{
	$tab = be("all","tab");
	$flag = be("all","flag");
    $token = be('post','__token__');
    if($token != $_SESSION['__token__']){
        showErr('System','token失效请刷新页面重试');
        return;
    }
    
	$upcache=false;
	$ismake=false;
	$js='';
	$backurl='';
	
	switch($tab)
	{
		case "link" :
			$id = be("all","l_id");
			$colarr = array("l_name","l_type","l_url","l_sort","l_logo");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			if (!is_numeric($valarr['l_sort'])) { $valarr['l_sort'] = $db->getOne("SELECT MAX(l_sort) FROM {pre}link")+1; }
			$where = "l_id=".$id;
			break;
        case "collect" :
            $id = be("all","c_id");
            $colarr = array("c_name","c_url","c_param","c_type","c_mid");
            for($i=0;$i<count($colarr);$i++){
                $n=$colarr[$i];
                $valarr[$n]=be("all",$n);
            }
            $where = "c_id=".$id;
            break;
		case "vod_type" :
			$id = be("all","t_id");
			$colarr = array("t_name","t_enname","t_sort","t_pid","t_tpl",'t_tpl_list',"t_tpl_vod","t_tpl_play","t_tpl_down","t_key","t_des","t_title");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$valarr['t_name'] = strip_tags($valarr['t_name']);
			if(empty($valarr['t_enname'])) { $valarr['t_enname'] = Hanzi2Pinyin($valarr['t_name']);}
			if(!is_numeric($valarr['t_sort'])) { $valarr['t_sort'] = $db->getOne("SELECT MAX(t_sort) FROM {pre}vod_type")+1; }
			if(empty($id)){
    			$colarr[] = 't_union';
    			$valarr['t_union'] = '';
    		}
			$where = "t_id=".$id;
			$upcache=true;
			break;
		case "vod_class" :
			$id = be("all","c_id");
			$colarr = array("c_name","c_sort","c_pid");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$valarr['c_name'] = strip_tags($valarr['c_name']);
			if (!is_numeric($valarr['c_sort'])) { $valarr['c_sort'] = $db->getOne("SELECT MAX(c_sort) FROM {pre}vod_class where c_pid=".$valarr['c_pid'])+1; }
			$where = "c_id=".$id;
			$upcache=true;
			break;
		case "vod_topic" :
			$uptime = be("all","uptime");
			$id = be("all","t_id");
			$colarr=array('t_name','t_enname','t_sort','t_tpl','t_pic','t_content','t_key','t_des','t_title','t_hide','t_level','t_up','t_down','t_score','t_scoreall','t_scorenum','t_hits','t_dayhits','t_weekhits','t_monthhits','t_addtime','t_time');
			
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$valarr['t_name'] = strip_tags($valarr['t_name']);
			if(empty($valarr['t_enname'])) { $valarr['t_enname'] = Hanzi2Pinyin($valarr['t_name']);}
			if(strlen($valarr['t_addtime'])!=10) { $valarr['t_addtime']=time(); $valarr['t_time']= $valarr['t_addtime']; }
    		if($uptime=='1'){ $valarr['t_time'] =time(); }
    		$valarr['t_sort'] = intval($valarr['t_sort']);
    		$valarr['t_hide'] = intval($valarr['t_hide']);
    		$valarr['t_level'] = intval($valarr['t_level']);
    		$valarr['t_up'] = intval($valarr['t_up']);
    		$valarr['t_down'] = intval($valarr['t_down']);
    		$valarr['t_score'] = floatval($valarr['t_score']);
    		$valarr['t_scoreall'] = intval($valarr['t_scoreall']);
    		$valarr['t_scorenum'] = intval($valarr['t_scorenum']);
    		
    		$valarr['t_hits'] = intval($valarr['t_hits']);
    		$valarr['t_dayhits'] = intval($valarr['t_dayhits']);
    		$valarr['t_weekhits'] = intval($valarr['t_weekhits']);
    		$valarr['t_monthhits'] = intval($valarr['t_monthhits']);
    		
    		
    		if(empty($id)){
    			$colarr[] = 't_hitstime';
    			$valarr['t_hitstime'] = 0;
    		}
			$where = "t_id=".$id;
			$upcache=true;
			break;
		case "art_type" :
			$id = be("all","t_id");
			$colarr = array("t_name","t_enname","t_sort","t_pid","t_tpl",'t_tpl_list',"t_tpl_art","t_key","t_des","t_title");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$valarr['t_name'] = strip_tags($valarr['t_name']);
			if(empty($valarr['t_enname'])) { $valarr['t_enname'] = Hanzi2Pinyin($valarr['t_name']);}
			if (!is_numeric($valarr['t_sort'])) { $valarr['t_sort'] = $db->getOne("SELECT MAX(t_sort) FROM {pre}art_type")+1; }
			if(empty($id)){
    			$colarr[] = 't_union';
    			$valarr['t_union'] = '';
    		}
			$where = "t_id=".$id;
			$upcache=true;
			break;
		case "art_topic" :
			$uptime = be("all","uptime");
			$id = be("all","t_id");
			$colarr=array('t_name','t_enname','t_sort','t_tpl','t_pic','t_content','t_key','t_des','t_title','t_hide','t_level','t_up','t_down','t_score','t_scoreall','t_scorenum','t_hits','t_dayhits','t_weekhits','t_monthhits','t_addtime','t_time');
			
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$valarr['t_name'] = strip_tags($valarr['t_name']);
			if(empty($valarr['t_enname'])) { $valarr['t_enname'] = Hanzi2Pinyin($valarr['t_name']);}
			if(strlen($valarr['t_addtime'])!=10) { $valarr['t_addtime']=time(); $valarr['t_time']= $valarr['t_addtime']; }
    		if($uptime=='1'){ $valarr['t_time'] =time(); }
    		$valarr['t_sort'] = intval($valarr['t_sort']);
    		$valarr['t_hide'] = intval($valarr['t_hide']);
    		$valarr['t_level'] = intval($valarr['t_level']);
    		$valarr['t_up'] = intval($valarr['t_up']);
    		$valarr['t_down'] = intval($valarr['t_down']);
    		$valarr['t_score'] = floatval($valarr['t_score']);
    		$valarr['t_scoreall'] = intval($valarr['t_scoreall']);
    		$valarr['t_scorenum'] = intval($valarr['t_scorenum']);
    		
    		$valarr['t_hits'] = intval($valarr['t_hits']);
    		$valarr['t_dayhits'] = intval($valarr['t_dayhits']);
    		$valarr['t_weekhits'] = intval($valarr['t_weekhits']);
    		$valarr['t_monthhits'] = intval($valarr['t_monthhits']);
    		
    		
    		if(empty($id)){
    			$colarr[] = 't_hitstime';
    			$valarr['t_hitstime'] = 0;
    		}
			$where = "t_id=".$id;
			$upcache=true;
			break;
		case "gbook":
			$id = be("all","g_id");
			$colarr = array("g_reply","g_replytime");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$valarr['g_replytime'] = time();
			$where = "g_id=".$id;
			break;
		case "comment":
			$id = be("all","c_id");
			$colarr = array("c_content");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			$where = "c_id=".$id;
			break;
		case "manager":
			$id = be("all","m_id");
			$m_password = be("all","m_password");
			
			if( $m_password !=""){
				$colarr = array("m_name","m_password","m_levels","m_status");
			}
			else{
				$colarr = array("m_name","m_levels","m_status");
			}
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			if(empty($id)){
				$colarr[] = 'm_random';
				$colarr[] = 'm_logintime';
				$colarr[] = 'm_loginip';
				$colarr[] = 'm_loginnum';
				$valarr['m_random'] = '';
				$valarr['m_logintime']=0;
				$valarr['m_loginip']=0;
				$valarr['m_loginnum']=0;
			}
			$valarr['m_levels'] = be("arr","m_levels");
			if( $m_password !=""){ $valarr['m_password'] = md5($m_password); }
			$where = "m_id=".$id;
			break;
		case "user_group":
			$id = be("all","ug_id");
			$colarr = array("ug_name","ug_type","ug_popedom","ug_upgrade","ug_popvalue");
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			
			$str=be("arr","ug_type");
			$arr = explode(",",$str);
			$ug_type=",";
			for ($i=0;$i<count($arr);$i++){
				if(trim($arr[$i]) !=""){
					$ug_type = $ug_type. trim($arr[$i]) . ",";
				}
			}
			$ug_type = str_replace(",,",",",$ug_type);
			if($ug_type==","){ $ug_type="";}
			$valarr['ug_type'] = $ug_type;
			
			
			$str=be("arr","ug_popedom");
			$arr = explode(",",$str);
			$ug_popedom=",";
			for ($i=0;$i<count($arr);$i++){
				if(trim($arr[$i]) !=""){
					$ug_popedom = $ug_popedom . trim($arr[$i]) . ",";
				}
			}
			$ug_popedom = str_replace(",,",",",$ug_popedom);
			if($ug_popedom==","){ $ug_popedom="";}
			$valarr['ug_popedom'] = $ug_popedom;
			$valarr['ug_upgrade'] = intval( $valarr['ug_upgrade']);
			$valarr['ug_popvalue'] = intval( $valarr['ug_popvalue']);

			$where = "ug_id=".$id;
			$upcache=true;
			break;
		case "user":
			$id = be("all","u_id");
			$u_password = be("all","u_password");
			
			if($u_password!=""){
				$colarr = Array("u_name","u_group","u_password","u_email","u_qq","u_phone","u_question","u_answer","u_status","u_points","u_start","u_end","u_flag");
			}
			else{
				$colarr = Array("u_name","u_group","u_email","u_qq","u_phone","u_question","u_answer","u_status","u_points","u_start","u_end","u_flag");
			}
			
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			if($u_password!=""){
				$valarr['u_password']= md5($u_password); 
			}
			if($valarr['u_flag']==1){
				$u_start= strtotime(be("all","u_starttime"));
			    $u_end = strtotime(be("all","u_endtime"));
			}
			else{
				$u_start= ip2long(be("all","u_startip"));
				$u_end = ip2long(be("all","u_endip"));
			}
			if(empty($id)){
				$colarr[] = 'u_qid';
				$colarr[] = 'u_regtime';
				$colarr[] = 'u_logintime';
				$colarr[] = 'u_loginnum';
				$colarr[] = 'u_extend';
				$colarr[] = 'u_loginip';
				$colarr[] = 'u_random';
				$colarr[] = 'u_fav';
				$colarr[] = 'u_plays';
				$colarr[] = 'u_downs';
				
				$valarr['u_qid'] = '';
				$valarr['u_regtime'] = time();
				$valarr['u_logintime'] = 0;
				$valarr['u_loginnum'] =0;
				$valarr['u_extend'] =0;
				$valarr['u_loginip'] = 0;
				$valarr['u_random'] = '';
				$valarr['u_fav'] = '';
				$valarr['u_plays'] = '';
				$valarr['u_downs'] = '';
				
			}
			$valarr['u_points'] = intval($valarr['u_points']);
			$valarr['u_start']= intval($u_start);
			$valarr['u_end']= intval($u_end);
			$where = "u_id=".$id;
			break;
		case "user_card":
			$num=be('all','num');
			$c_money=be('all','c_money');
			$c_point=be('all','c_point');
			$num = intval($num);
			$colarr = array('c_number','c_pass','c_money','c_point','c_addtime','c_usetime');
			for($i=0;$i<$num;$i++){
				$c_number = getRndStr(16);
				$c_pass = getRndStr(8);
				$c_addtime= time();
				$valarr = Array($c_number,$c_pass,$c_money,$c_point,$c_addtime,0);
				$db->Add ('{pre}user_card',$colarr,$valarr);
			}
			
			$flag='ok';
			break;
		case "art":
			$id = be("all","a_id");
			$uptag = be("all","uptag");
			$uptime = be("all","uptime");
			
			$colarr=array('a_name', 'a_subname', 'a_enname', 'a_letter', 'a_color', 'a_from', 'a_author', 'a_tag', 'a_pic', 'a_type', 'a_level', 'a_hide', 'a_lock', 'a_up', 'a_down', 'a_hits', 'a_dayhits', 'a_weekhits', 'a_monthhits', 'a_addtime', 'a_time', 'a_remarks','a_content');
			
			
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			
			if(strlen($valarr['a_addtime'])!=10) { $valarr['a_addtime']=time(); $valarr['a_time']= $valarr['a_addtime']; }
    		if($uptime=='1'){ $valarr['a_time'] =time(); }
    		$valarr['a_name'] = strip_tags($valarr['a_name']);
    		if(empty($valarr['a_enname'])) { $valarr['a_enname'] = Hanzi2Pinyin($valarr['a_name']);}
			if(empty($valarr['a_letter'])) { $valarr['a_letter'] = strtoupper(substring($valarr['a_enname'],1)); }
			$valarr['a_content'] = be('arr','a_content','[art:page]');
			if($uptag=='1' && $valarr['a_tag']==''){
				$valarr['a_tag'] = getTag($valarr['a_name'],$valarr['a_content']);
			}
			if(empty($valarr['a_remarks'])){
				$valarr['a_remarks'] = substring(strip_tags($valarr['a_content']),100);
			}
    		unset($pinyins);
    		$valarr['a_remarks'] = str_replace("'", "''",$valarr['a_remarks']);
    		$valarr['a_content'] = str_replace("'", "''",$valarr['a_content']);
    		$valarr['a_level'] = intval($valarr['a_level']);
    		$valarr['a_lock'] = intval($valarr['a_lock']);
    		
    		if(empty($id)){
    			$colarr[] = 'a_topic';
    			$colarr[] = 'a_hitstime';
    			$colarr[] = 'a_maketime';
    			$valarr['a_topic'] = '';
    			$valarr['a_hitstime'] =0;
    			$valarr['a_maketime'] =0;
    			
    		}
    		
    		
    		$where = "a_id=".$id;
    		if($GLOBALS['MAC']['view']['artdetail']==2){
		    	$ismake=true;
		    	$js = '<img width=0 height=0 src="index.php?m=make-info-tab-art-no-{id}" />';
		    }
			break;
		case "vod":
			$id = be("all","d_id");
			$uptag = be("all","uptag");
			$uptime = be("all","uptime");
			
			$colarr=array('d_name', 'd_subname', 'd_enname', 'd_letter', 'd_color', 'd_pic', 'd_picthumb', 'd_picslide', 'd_starring', 'd_directed', 'd_tag', 'd_remarks', 'd_area', 'd_lang', 'd_year', 'd_type', 'd_type_expand' , 'd_class', 'd_hide', 'd_lock', 'd_state', 'd_level', 'd_usergroup', 'd_stint', 'd_stintdown', 'd_hits', 'd_dayhits', 'd_weekhits', 'd_monthhits', 'd_duration', 'd_up', 'd_down', 'd_score','d_scoreall', 'd_scorenum', 'd_addtime', 'd_time', 'd_content', 'd_playfrom', 'd_playserver', 'd_playnote', 'd_playurl', 'd_downfrom', 'd_downserver', 'd_downnote', 'd_downurl');
			
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=be("all",$n);
			}
			
			if(empty($id)){
    			$colarr[] = 'd_topic';
    			$colarr[] = 'd_hitstime';
    			$colarr[] = 'd_maketime';
    			$valarr['d_topic'] = '';
    			$valarr['d_hitstime'] =0;
    			$valarr['d_maketime'] =0;
    		}
    		
			if(strlen($valarr['d_addtime'])!=10) { $valarr['d_addtime']=time(); }
    		if($valarr['d_time']!=''){  $valarr['d_time']= strtotime($valarr['d_time']); } else { $valarr['d_time']= $valarr['d_addtime']; }
    		if($uptime=='1'){ $valarr['d_time'] =time(); }
    		$valarr['d_name'] = strip_tags($valarr['d_name']);
    		if(empty($valarr['d_enname'])) { $valarr['d_enname'] = Hanzi2Pinyin($valarr['d_name']);}
			if(empty($valarr['d_letter'])) { $valarr['d_letter'] = strtoupper(substring($valarr['d_enname'],1)); }
			$valarr['d_level'] = intval($valarr['d_level']);
    		$valarr['d_lock'] = intval($valarr['d_lock']);
    		$valarr['d_year'] = intval($valarr['d_year']);
    		$valarr['d_state'] = intval($valarr['d_state']);
    		$valarr['d_stint'] = intval($valarr['d_stint']);
    		$valarr['d_stintdown'] = intval($valarr['d_stintdown']);
    		$valarr['d_hits'] = intval($valarr['d_hits']);
    		$valarr['d_dayhits'] = intval($valarr['d_dayhits']);
    		$valarr['d_weekhits'] = intval($valarr['d_weekhits']);
    		$valarr['d_monthhits'] = intval($valarr['d_monthhits']);
    		$valarr['d_duration'] = intval($valarr['d_duration']);
    		$valarr['d_up'] = intval($valarr['d_up']);
    		$valarr['d_down'] = intval($valarr['d_down']);
    		$valarr['d_score'] = floatval($valarr['d_score']);
    		$valarr['d_scoreall'] = intval($valarr['d_scoreall']);
    		$valarr['d_scorenum'] = intval($valarr['d_scorenum']);
    		
			unset($pinyins);
			if($uptag=='1' && $valarr['d_tag']==''){
				$valarr['d_tag'] = getTag($valarr['d_name'],$valarr['d_content']);
			}
			
			$playurl=be('arr', 'playurl',',,,'); $playfrom=be('arr', 'playfrom'); $playserver=be('arr', 'playserver'); $playnote=be('arr','playnote');
		    $playurlarr=explode(',,,',$playurl); $playfromarr=explode(',',$playfrom); $playserverarr=explode(',',$playserver); 
		    $playnotearr=explode(',',$playnote);
		    $playurlarrlen=count($playurlarr); $playfromarrlen=count($playfromarr); $playserverarrlen=count($playserverarr);
		    if(empty($playurl)) { $playurlarrlen=-1; }
		    
		    
		    $downurl=be('arr', 'downurl',',,,'); $downfrom=be('arr', 'downfrom'); $downserver=be('arr', 'downserver');$downnote=be('arr', 'downnote');
		    $downurlarr=explode(',,,',$downurl); $downfromarr=explode(',',$downfrom); $downserverarr=explode(',',$downserver);
		    $downnotearr=explode(',',$downnote);
		    $downurlarrlen=count($downurlarr); $downfromarrlen=count($downfromarr); $downserverarrlen=count($downserverarr);
		    if(empty($downurl)) { $downurlarrlen=-1; }
		    
		    $valarr['d_class'] = be('arr','d_class');
		    if(!empty($valarr['d_class'])){
		    	$valarr['d_class'] = ','.$valarr['d_class'].',';
		    }
		    
		    $rc = false;
		    for ($i=0;$i<$playfromarrlen;$i++){
		        if ($playurlarrlen >= $i){
		        	if(trim($playfromarr[$i])!='no'){
				        if ($rc){ $d_playurl .= '$$$'; $d_playfrom .= '$$$'; $d_playserver .= '$$$'; $d_playnote.='$$$'; }
				        $d_playfrom .= trim($playfromarr[$i]);
				        $d_playserver .= trim($playserverarr[$i]);
				        $d_playnote .=  trim($playnotearr[$i]);
				        $d_playurl .= str_replace(chr(13),'#',str_replace(chr(10),'',trim($playurlarr[$i])));
				        $rc =true;
			        }
		        }
		    }
		    
		    $rc = false;
		    for ($i=0;$i<$downfromarrlen;$i++){
		        if ($downfromarrlen >= $i){
		        	if(trim($downfromarr[$i])!='no'){
				        if ($rc){ $d_downurl .= '$$$'; $d_downfrom .= '$$$'; $d_downserver .= '$$$'; $d_downnote.='$$$';  }
				        $d_downfrom .= trim($downfromarr[$i]);
				        $d_downserver .= trim($downserverarr[$i]);
				        $d_downnote .=  trim($downnotearr[$i]);
				        $d_downurl .= str_replace(chr(13),'#',str_replace(chr(10),'',trim($downurlarr[$i])));
				        $rc =true;
				    }
		        }
		    }
		    $d_playurl = str_replace("'", "''",$d_playurl);
		    $d_downurl = str_replace("'", "''",$d_downurl);
		    
		    $valarr['d_playfrom']=$d_playfrom;
		    $valarr['d_playserver']=$d_playserver;
		    $valarr['d_playnote']=$d_playnote;
		    $valarr['d_playurl']=$d_playurl;
		    $valarr['d_downfrom']=$d_downfrom;
		    $valarr['d_downserver']=$d_downserver;
		    $valarr['d_downnote']=$d_downnote;
		    $valarr['d_downurl']=$d_downurl;
            // xss过滤、长度裁剪
            $filter_fields = [
                'd_name'        => 255,
                'd_subname'     => 255,
                'd_enname'      => 255,
                'd_pic'         => 255,
                'd_picthumb'    => 255,
                'd_picslide'    => 255,
                'd_starring'    => 255,
                'd_directed'    => 255,
                'd_tag'         => 64,
                'd_remarks'     => 64,
                'd_area'        => 16,
                'd_lang'        => 16,
                'd_type_expand' => 255,
                'd_class'       => 255,
                'd_topic'       => 255,
                'd_playfrom'    => 255,
                'd_playserver'  => 255,
                'd_playnote'    => 255,
                'd_downfrom'    => 255,
                'd_downserver'  => 255,
                'd_downnote'    => 255,
            ];
            foreach ($filter_fields as $filter_field => $field_length) {
                if (!isset($valarr[$filter_field])) {
                    continue;
                }
                $valarr[$filter_field] = mac_filter_xss($valarr[$filter_field]);
                $valarr[$filter_field] = substring($valarr[$filter_field], $field_length);
            }
		    $where = "d_id=".$id;
		    if($GLOBALS['MAC']['view']['voddetail']==2 || $GLOBALS['MAC']['view']['vodplay']==2 || $GLOBALS['MAC']['view']['voddown']==2){
		    	$ismake=true;
		    	$js = '<img width=0 height=0 src="index.php?m=make-info-tab-vod-no-{id}" />';
		    }
			break;
	}
	if($flag=="add"){
		$db->Add('{pre}'.$tab,$colarr,$valarr);
		if($ismake){
			$id=$db->insert_id();
			$js = str_replace('{id}',$id,$js);
		}
	}
	elseif($flag=="edit"){
		$backurl=be("all","backurl");
		$db->Update('{pre}'.$tab,$colarr,$valarr,$where,1);
		if($ismake){
			$js = str_replace('{id}',$id,$js);
		}
	}
	if($upcache){ updateCacheFile(); }
    showMsg('数据已保存'.$js,$backurl);
}

else if($ac=='del')
{
	$tab = be("all","tab");
	$flag = be("all","flag");
	$upcache=false;
	switch($tab)
	{
		case "art":
			$col="a_id";
			$ids = be("get","a_id");
			if(empty($ids)){
				$ids= be("arr","a_id");
			}
			break;
		case "vod":
			$col="d_id";
			$ids = be("get","d_id");
			if(empty($ids)){
				$ids= be("arr","d_id");
			}
			break;
        case "collect" :
            $col="c_id";
            $ids = be("get","c_id");
            if(empty($ids)){
                $ids= be("arr","c_id");
            }
            break;
		case "link" :
			$col="l_id";
			$ids = be("get","l_id");
			if(empty($ids)){
				$ids= be("arr","l_id");
			}
			break;
		case "vod_type":
			$col="t_id";
			$ids = be("get","t_id");
			if(empty($ids)){
				$ids= be("arr","t_id");
			}
			$arr=explode(',',$ids);
			foreach($arr as $a){
				$cc = $db->getOne('select count(*) from {pre}vod_type where t_pid='.$a);
				if($cc>0){
					showMsg('请先删除本类下面的子栏目','');
					return;
				}
				$cc = $db->getOne('select count(*) from {pre}vod where d_type='.$a);
				if($cc>0){
					showMsg('请先删除本类下面的视频','');
					return;
				}
			}
			$upcache=true;
			break;
		case "vod_class":
			$col="c_id";
			$ids = be("get","c_id");
			if(empty($ids)){
				$ids= be("arr","c_id");
			}
			$upcache=true;
			break;
		case "vod_topic" :
			$col="t_id";
			$ids = be("get","t_id");
			if(empty($ids)){
				$ids= be("arr","t_id");
			}
			$arr=explode(',',$ids);
			foreach($arr as $a){
				$cc = $db->getOne('select count(*) from {pre}vod_relation where r_type=2 and r_a='.$a);
				if($cc>0){
					showMsg('请先删除本专题下面的视频','');
					return;
				}
			}
			$upcache=true;
			break;
		case "art_type" :
			$col="t_id";
			$ids = be("get","t_id");
			if(empty($ids)){
				$ids= be("arr","t_id");
			}
			$arr=explode(',',$ids);
			foreach($arr as $a){
				$cc = $db->getOne('select count(*) from {pre}art_type where t_pid='.$a);
				if($cc>0){
					showMsg('请先删除本类下面的子栏目','');
					return;
				}
				$cc = $db->getOne('select count(*) from {pre}art where a_type='.$a);
				if($cc>0){
					showMsg('请先删除本类下面的视频','');
					return;
				}
			}
			$upcache=true;
			break;
		case "art_topic" :
			$col="t_id";
			$ids = be("get","t_id");
			if(empty($ids)){
				$ids= be("arr","t_id");
			}
			$arr=explode(',',$ids);
			foreach($arr as $a){
				$cc = $db->getOne('select count(*) from {pre}art_relation where r_type=2 and r_a='.$a);
				if($cc>0){
					showMsg('请先删除本专题下面的视频','');
					return;
				}
			}
			$upcache=true;
			break;
		case "gbook":
			$col="g_id";
			$ids = be("get","g_id");
			if(empty($ids)){
				$ids= be("arr","g_id");
			}
			break;
		case "manager":
			$col="m_id";
			$ids = be("get","m_id");
			if(empty($ids)){
				$ids= be("arr","m_id");
			}
			break;
		case "user_group":
			$col="ug_id";
			$ids = be("get","ug_id");
			if(empty($ids)){
				$ids= be("arr","ug_id");
			}
			$upcache=true;
			break;
		case "user":
			$col="u_id";
			$ids = be("get","u_id");
			if(empty($ids)){
				$ids= be("arr","u_id");
			}
			break;
		case "user_card":
			$col="c_id";
			$ids = be("get","c_id");
			if(empty($ids)){
				$ids= be("arr","c_id");
			}
			break;
		case "comment":
			$col="c_id";
			$ids = be("get","c_id");
			if(empty($ids)){
				$ids= be("arr","c_id");
			}
			break;
	}
	if (!empty($ids)) { $db->Delete('{pre}'.$tab, $col." in (".$ids.")"); }
	if ($upcache){ updateCacheFile(); }
	redirect ( getReferer() );
}

elseif($ac=='clear')
{
	$sql='truncate TABLE {pre}'.$tab;
	$db->query($sql);
	redirect( getReferer() );
}

elseif($ac=='set')
{
	$sql='UPDATE {pre}'.$tab.' set '.$col.'='.$val.' WHERE '.$colid .' IN('.$id.')';
	$db->query($sql);
	redirect( getReferer() );
}

elseif($ac=='getfields')
{
	if(empty($tab)) { echo '[]'; }
	$rs = $db->query('SHOW COLUMNS FROM '.$tab);
	while ($row = $db ->fetch_array($rs)){
		$dbarr[] = $row['Field'];
	}
	unset($rs);
	echo json_encode($dbarr);;
}

elseif($ac=='getexpandtype')
{
	echo '';
}

elseif($ac=='getclass')
{
	$class = be("all",'class');
	
	$typearr = $MAC_CACHE['vodtype'][$id];
	if($typearr['t_pid']>0){
		$id=$typearr['t_pid'];
	}
	$ids = $class;
	
	$valarr=array();
	$typearr = $MAC_CACHE['vodclass'];
	foreach($typearr as $a){
		if($a['c_pid']==$id){
			$arr=array();
			$arr['id'] = $a['c_id'];
			$arr['name'] = $a['c_name'];
			$arr['chk'] = strpos(',,'.$ids.',,',','.$a['c_id'].',') ? 'true' : 'false';
			array_push($valarr,$arr);
		}
	}
	echo json_encode($valarr);;
}

elseif($ac=='topicdata')
{
	$tid=be('all','tid');
	
	if($tab=='art'){
		$pre='a_';
		if($flag=='add'){
			$nums = $db->getOne('select count(*) from {pre}art_relation where r_type=2 and r_a='.$tid.' and r_b='.$id);
			if($nums==0){
				$db->Add('{pre}art_relation',array('r_type','r_a','r_b'),array(2,$tid,$id));
				$rc=false;
				$a_topic='';
				$rs=$db->query('select r_a from {pre}art_relation where r_type=2 and r_b='.$id);
				while ($row = $db->fetch_array($rs))
				{
					if($rc){ $a_topic.=','; }
					$a_topic .= $row['r_a'];
					$rc=true;
				}
				unset($rs);
				if(!empty($a_topic)){ $a_topic = ','.$a_topic.','; }
				$db->Update('{pre}art',array('a_topic'),array($a_topic),'d_id='.$id);
			}
		}
		elseif($flag=='del'){
			$sql='delete from {pre}art_relation where r_type=2 and r_a='.$tid.' and r_b='.$id;
			$db->query($sql);
			$sql='update {pre}art set a_topic=replace(a_topic,\''.$tid.'\',\'\') where a_id='.$id;
			$db->query($sql);
		}
		$sql='select a_id,a_name,a_enname,a_type,a_author from {pre}art_relation t inner join {pre}'.$tab.' d on d.a_id=t.r_b where t.r_type=2 and t.r_a='.$tid;
	}
	else{
		$pre="d_";
		if($flag=='add'){
			$nums = $db->getOne('select count(*) from {pre}vod_relation where r_type=2 and r_a='.$tid.' and r_b='.$id);
			if($nums==0){
				$db->Add('{pre}vod_relation',array('r_type','r_a','r_b'),array(2,$tid,$id));
				$rc=false;
				$d_topic='';
				$rs=$db->query('select r_a from {pre}vod_relation where r_type=2 and r_b='.$id);
				while ($row = $db->fetch_array($rs))
				{
					if($rc){ $d_topic.=','; }
					$d_topic .= $row['r_a'];
					$rc=true;
				}
				unset($rs);
				if(!empty($d_topic)){ $d_topic = ','.$d_topic.','; }
				$db->Update('{pre}vod',array('d_topic'),array($d_topic),'d_id='.$id);
			}
		}
		elseif($flag=='del'){
			$sql='delete from {pre}vod_relation where r_type=2 and r_a='.$tid.' and r_b='.$id;
			$db->query($sql);
			$sql='update {pre}vod set d_topic=replace(d_topic,\''.$tid.'\',\'\') where d_id='.$id;
			$db->query($sql);
		}
		$sql='select d_id,d_name,d_enname,d_type,d_starring from {pre}vod_relation t inner join {pre}vod d on d.d_id=t.r_b where t.r_type=2 and t.r_a='.$tid;
	}
	$rs = $db->queryArray($sql,false);
	for($i=0;$i<count($rs);$i++){
		$typearr = $GLOBALS['MAC_CACHE'][$tab.'type'][$rs[$i][$pre.'type']];
	 	$alink = "../".$tpl->getLink($tab,'detail',$typearr,$rs[$i],true);
		$alink = str_replace("../".$MAC['site']['installdir'],"../",$alink);
		if (substring($alink,1,strlen($alink)-1)=="/") { $alink .= "index.". $MAC['app']['suffix'];}
		$rs[$i][$pre.'link'] = $alink;
	}
	$str = json_encode($rs);
	if($str!='[]'){
		echo $str;
		return;
	}
	echo '[]';
}

elseif($ac=='typenow')
{
	if($tab=='art'){
		$pre='a';
	}
	else{
		$pre='d';
	}
	
	$typearr=array();
	$sql='select distinct '.$pre.'_type from {pre}'.$tab.' where '.$pre.'_time >='.strtotime("today");
	$rs = $db->queryarray($sql,false);
	foreach($rs as $a){
		array_push($typearr,$a[$pre.'_type']);
	}
	unset($rs);
	echo join(',',$typearr);
}
elseif($ac=='hide')
{
	if($show==1){
		echo '<select id="val" name="val"><option value="">请选择...</option><option value="0">显示</option><option value="1">隐藏</option></select><input type="button" value="确定" onclick="ajaxsubmit(\''.$ac.'\',\''.$tab.'\',\''.$colid.'\',\''.$col.'\',\''.$id.'\');" class=input> <input type="button" value="取消" onclick="closew();" class=input>';
	}
	else{
		$sql='UPDATE {pre}'.$tab.' set '.$col.'='.$val.' WHERE '.$colid .' IN('.$id.')';
		$db->query($sql);
		if($show==2){
			echo 'reload';
		}
		else{
			redirect( getReferer() );
		}
	}
}

elseif($ac=='shift')
{
	if ($show ==1){
		if(strpos($tab,'_type')){
			$selstr=makeSelectAll("{pre}".$tab,"t_id","t_name","t_pid","t_sort",0,"","&nbsp;|&nbsp;&nbsp;","");
		}
		else{
			$selstr=makeSelect("{pre}".$tab,"t_id","t_name","t_sort","","&nbsp;|&nbsp;&nbsp;","");
		}
		echo '<select id="val" name="val"><option value="0">请选择目标</option>' . $selstr .'</select><input type="button" value="确定" onclick="ajaxsubmit(\''.$ac.'\',\''.$tab.'\',\''.$colid.'\',\''.$col.'\',\''.$id.'\');" class=input> <input type="button" value="取消" onclick="closew();" class=input>';
	}
	else{
		if(strpos(','.$tab,'vod')) { $tab2='vod'; } else { $tab2='art'; }
		switch($tab)
		{
			case 'vod_type':
				$tab2 = 'vod';
				$colid2 = 'd_type';
				break;
			case 'art_type':
				$tab2 = 'art';
				$colid2 = 'a_type';
				break;
			case 'vod_topic':
				$tab2 = 'vod_relation';
				$colid2 = 'r_a';
				$where = ' and r_type=2 ';
				break;
			case 'art_topic':
				$tab2 = 'art_relation';
				$colid2 = 'r_a';
				$where = ' and r_type=2 ';
				break;
		}
		$db->query ("UPDATE {pre}".$tab2 ." set ". $colid2 ."=".$val. " WHERE " . $colid2 ." IN(".$id.") ".$where );
		echo "reload";
	}
}


elseif($ac=='level')
{
	if ($show==1){
		echo '<select id="val" name="val"><option value="">请选择推荐</option><option value="1">推荐1</option><option value="2">推荐2</option><option value="3">推荐3</option><option value="4">推荐4</option><option value="5">推荐5</option><option value="0">取消推荐</option></select><input type="button" value="确定" onclick="ajaxsubmit(\''.$ac.'\',\''.$tab.'\',\''.$colid.'\',\''.$col.'\',\''.$id.'\');" class=input><input type="button" value="取消" onclick="closew();" class=input>';
	}
	else{
		if($val!=''){
			$sql='UPDATE {pre}'.$tab.' set '.$col.'='.$val.' WHERE '.$colid .' IN('.$id.')';
			$db->query($sql);
			$idarr = explode(",",$id);
			for ($i=0;$i<count($idarr);$i++){
				echo 'level_'.$idarr[$i].'$&nbsp;<img src="../images/icons/ico'.$val.'.gif" border="0" style="cursor: pointer;" onclick="ajaxshow(\'level_'.$idarr[$i].'\',\''.$ac.'\',\''.$tab.'\',\''.$colid.'\',\''.$col.'\',\''.$idarr[$i].'\');" />|||';
			}
		}
	}
}

elseif($ac=='hits')
{
	if ($show ==1){
		echo '<input id="val" name="val" type="text"  size="5">到<input id="val2" name="val2" type="text"  size="5">之间<input type="button" value="确定" onclick="ajaxsubmit(\''.$ac.'\',\''.$tab.'\',\''.$colid.'\',\''.$col.'\',\''.$id.'\');" class=input> <input type="button" value="取消" onclick="closew();" class=input>';
	}
	else{
		$val = be("all","val");
		$val2 = be("all","val2");
		if (!is_numeric($val)){ $val=1;}
		if (!is_numeric($val2)){ $val2=1000;}
		
		$rs = $db->query("select ".$colid." from {pre}".$tab." where ".$colid." in (" .$id . ")");
		while($row = $db->fetch_array($rs))
		{
			$num3 = rndNum($val,$val2);
			$db->Update ("{pre}".$tab ,array($col),array($num3) ,$colid."=".$row[$colid]);
		}
		unset($rs);
		echo "reload";
	}
}

elseif($ac=='type')
{
	if ($show ==1){
		echo '<select id="val" name="val"><option value="0">请选择栏目</option>' . makeSelectAll("{pre}".$tab."_type","t_id","t_name","t_pid","t_sort",0,"","&nbsp;|&nbsp;&nbsp;","") .'</select><input type="button" value="确定" onclick="ajaxsubmit(\''.$ac.'\',\''.$tab.'\',\''.$colid.'\',\''.$col.'\',\''.$id.'\');" class=input> <input type="button" value="取消" onclick="closew();" class=input>';
	}
	else{
		$db->query ("UPDATE {pre}".$tab . " set ". $col ."=".$val. " WHERE " . $colid ." IN(".$id.")" );
		echo "reload";
	}
}

elseif($ac=='type_bind')
{
	$val = intval($val);
	$bind = be("all","bind");
	
	if ($show ==1){
		echo '<select id="val" name="val"><option value="">取消绑定分类</option>' . makeSelectAll('{pre}'.$tab.'_type','t_id','t_name','t_pid','t_sort',0,'','&nbsp;|&nbsp;&nbsp;','').'</select><input class="input" type="button" value="绑定" onclick="bindsave(\''.$tab.'\',\''.$bind.'\');"><input class="input" type="button" value="取消" onclick="closew();">';
	}
	else{
		$bindcache = @include(MAC_ROOT."/inc/config/config.collect.bind.php");
		if (!is_array($bindcache)) {
			$bindcache = array();
			$bindcache['1_1'] = 0;
		}
		
		$bindinsert[$bind] = $val;
		$bindarray = array_merge($bindcache,$bindinsert);
		$cv = "<?php\nreturn ".var_export($bindarray, true).";\n?>";
		fwrite(fopen(MAC_ROOT."/inc/config/config.collect.bind.php",'wb'),$cv);
	    echo 'ok';
	}
}

elseif($ac=='memcached')
{
	$host = be("all","host");
	$port = be("all","port");
    $user = be("all","user");
    $pass = be("all","pass");
    $type = be("all","type");
	try{
	    if($type=='1'){
            $mem=new Memcache;
            if(!$mem->connect($host,$port)){
                echo '连接失败!';
            }
            else{
                echo 'ok';
            }
        }
	    elseif($type=='2'){
            $mem=new Memcached;
            $mem->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            if(!$mem->addServer($host,$port)){
                echo '连接失败!';
            }
            else{
                //if(!$mem->setSaslAuthData($user,$pass)){
                    //echo '连接成功但用户密码验证失败!';
                //}
                //else{
                    echo 'ok';
                //}
            }
        }
	    else{
            echo 'err';
            exit;
        }
	}
	catch(Exception $e){ 
		echo 'err';
		exit;
	}
}

else{
	redirect( getReferer() );
}

?>