<?php
if(!defined('MAC_ADMIN')){
	exit('Access Denied');
}
ob_end_clean();
ob_implicit_flush(true);
ini_set('max_execution_time', '0');

$bindcache = @include(MAC_ROOT."/inc/config/config.collect.bind.php");
$backurl = getReferer();
$typearr=array();


$ac2 = $p['ac2'];
$apiurl = $p['apiurl'];
$flag = $p['flag']; //资源标识
$xt = $p['xt'];  //xml类型
$ct = $p['ct'];  //存储类型play or down
$group = $p['group']; //指定播放组
$wd = $p['wd'];
$type = intval($p['type']);
$pg = intval($p['pg']);
$hour = intval($p['hour']);
$mid = intval($p['mid']);
$param = $p['param'];

$pre = 'vod';
if($mid==2){
	$pre = 'art';
}


if($method=='break')
{
    echo getBreak("union"). "正在载入断点续传数据，请稍后......";
}

elseif($method=='union')
{
	$plt->set_file('main', $ac.'_'.$method.'.html');
	$status = chkBreak('union');
	$plt->set_if('main','isbreak',$status);
	$plt->set_var('time', time() );
}

elseif($method=='list')
{
	$plt->set_file('main', $ac.'_'.$method.'.html');
	
	
	$colarr=array('action','type','pg','apiurl','wd','hour','xt','group','xt','ct','flag');
	$valarr=array($method,$type,$pg,$apiurl,$wd,$hour,$xt,$group,$xt,$ct,$flag);
	for($i=0;$i<count($colarr);$i++){
		$n = $colarr[$i];
		$v = $valarr[$i];
		$plt->set_var($n,$v);
	}
	
	$type>0 ? $istype=true : $istype=false;
	$plt->set_if('main','istype',$istype);


	if($xt=='0'){
    	$url = $apiurl . '?action=list&rpage='.$pg.'&rtype='.$type.'&rkey='.urlencode($wd);
	}
    elseif($xt=='1'){
    	$url = $apiurl . '?ac=list&pg=' . $pg . '&rid='.$group . '&t=' . $type . '&wd=' . urlencode($wd);
    }
    elseif($xt=='2'){
    	$url = $apiurl . '-action-list-cid-'. $type . '-h-'. $hour. '-p-' . $pg. '-wd-'. urlencode($wd);
    	$url = str_replace('|','-',$url);
    }
    $url .= base64_decode($param);
    $html = getPage($url, "utf-8");
	if(empty($html)){
		echo '连接API资源库失败，通常为服务器网络不稳定或禁用了采集。请刷新重试！';
		exit;
	}
	
    $xml = @simplexml_load_string($html);
    if(empty($xml)){
		echo 'XML格式不正确，不支持采集';
		exit;
    }

    $pgcount = (string)@$xml->list->attributes()->pagecount;
    $pgsize = (string)@$xml->list->attributes()->pagesize;
    $recordcount = (string)@$xml->list->attributes()->recordcount;


    $key=0;
    $colarr=array('v','n');
    $rn='type';
    $plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
    foreach($xml->class->ty as $ty){
        $typeid = (string)@$ty->attributes()->id;
        $typename = filter_tags((string)$ty);
        $isbind = false;
        $uid = intval( $bindcache[$flag.$typeid] );
        if ($uid>0){
            $isbind=true;
        }
        $valarr=array($typeid,$typename);
        for($i=0;$i<count($colarr);$i++){
            $n = $colarr[$i];
            $v = $valarr[$i];
            $plt->set_var($n,$v);
        }
        $plt->parse('rows_'.$rn,'list_'.$rn,true);
        $plt->set_if('rows_'.$rn,'isbind',$isbind);
        $key++;
    }
    

    if( count($xml->list->video)==0){
        $plt->set_if('main','isnull',true);
        return;
    }
    $plt->set_if('main','isnull',false);
    
    $colarr=array('id','name','typename','from','time','chk','nameencode');
    $rn='data';
	$plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);

    $key=0;
    foreach($xml->list->video as $video){
        $id = (string)$video->id;
        $name = filter_tags((string)$video->name);
        $nameencode = urlencode(substring($name,4));
        $typename = filter_tags((string)$video->type);
        $now = date('Y-m-d',time());

        $time = (string)$video->last;
        $sc = substr_count($time,'-');
        if($sc==1){ $time = date('Y-',time()).$time; }
        $time = getColorDay(strtotime($time));
        $chk = strpos(','.$time,$now)>0 ? 'checked' : '';
        $from = filter_tags((string)$video->dt);
        $valarr=array($id,$name,$typename,$from,$time,$chk,$nameencode);
        for($i=0;$i<count($colarr);$i++){
            $n = $colarr[$i];
            $v = $valarr[$i];
            $plt->set_var($n,$v);
        }
        $plt->parse('rows_'.$rn,'list_'.$rn,true);
        $key++;
    }

    unset($colarr);
    unset($valarr);
    
    $pgurl = '?m=collect-list-pg-{pg}-xt-'.$xt.'-ct-'.$ct.'-group-'.$group.'-flag-'.$flag.'-type-'.$type.'-wd-'.urlencode($wd).'-apiurl-'.$apiurl;
	$pgs = '共'.$recordcount.'条数据&nbsp;当前:'.$pg.'/'.$pgcount.'页&nbsp;'.pageshow($pg,$pgcount,5,$pgurl,'pagego(\''.$pgurl.'\','.$pgcount.')');
	$plt->set_var('pages', $pgs );
}

elseif($method=='cj'){
	headAdmin2('数据采集');
	switch($ac2)
	{
		case 'sel':
			$ids = be("arr", "ids");
			if(empty($ids)) { errMsg ("采集提示", "请选择采集数据");}
			switch($xt)
			{
				case '0': $url = "?action=cjsel&ids=".$ids;
					break;
				case '1': $url = "?ac=videolist&rid=".$group."&ids=".$ids;
					break;
				case '2': $url = "-action-ids-vodids-".$ids."-cid--play--inputer--wd--h-0-p-1";
					break;
			}
			break;
		case 'day':
			switch($xt)
			{
				case '0': $url = "?action=cjday&rday=".$hour."&rpage=".$pg;
					break;
				case '1': $url = "?ac=videolist&rid=".$group."&h=".$hour."&pg=".$pg;
					break;
				case '2': $url = "-action-day-vodids--cid--play--inputer--wd--h-".$hour."-p-".$pg;
					break;
			}
			break;
		case 'type':
			if(empty($type)){ showMsg ("请先进入分类,否则无法使用采集分类!", $backurl); exit; }
			switch($xt)
			{
				case '0': $url = "?action=cjtype&rpage=".$pg."&rtype=". $type;
					break;
				case '1': $url = "?ac=videolist&rid=".$group."&pg=" . $pg . "&t=" . $type;
					break;
				case '2': $url = "-action-all-vodids--cid-".$type."-play--inputer--wd--h-0-p-".$pg;
					break;
			}
			break;
		case 'all':
			switch($xt)
			{
				case '0': $url = "?action=cjall&rpage=".$pg;
					break;
				case '1': $url = "?ac=videolist&rid=".$group."&pg=". $pg;
					break;
				case '2': $url = "-action-all-vodids--cid--play--inputer--wd--h-0-p-" . $pg;
					break;
			}
			break;
	}
	$url = $apiurl.$url;
    
    if($xt=="0"){

    }
    elseif($xt=="1"){

    }
    elseif($xt=="2"){
    	$url = str_replace('|','-',$url);
    }
    
    setBreak ("union", "?m=collect-cj-ac2-".$ac2."-xt-".$xt."-ct-".$ct."-group-".$group."-flag-".$flag."-pg-".$pg."-type-" .$type."-wd-".$wd."-apiurl-".$apiurl);
    echo '采集地址&nbsp;'.$url.'<br>';
   	ob_flush();flush();

    $url .= base64_decode($param);
    $html = getPage($url, "utf-8");
	if(empty($html)){
		echo '连接API资源库失败，通常为服务器网络不稳定或禁用了采集。请刷新重试！';
		exit;
	}
	
	if(strpos($html,'<rss')===false){
		$html = '<?xml version="1.0" encoding="utf-8"?><rss version="5.1">' . $html .'</rss>';
	}
	
    $xml = @simplexml_load_string($html);
    
    if(empty($xml)){
		$labelRule = '<pic>'."(.*?)".'</pic>';
		$labelRule = buildregx($labelRule,"is");
		preg_match_all($labelRule,$html,$tmparr);
		$ec=false;
		foreach($tmparr[1] as $tt){
			if(strpos($tt,'[CDATA')===false){
				$ec=true;
				$ne = '<pic>'.'<![CDATA['.$tt .']]>'.'</pic>';
				$html = str_replace('<pic>'.$tt.'</pic>',$ne,$html);
			}
		}
		if($ec) {
			$xml = @simplexml_load_string($html);
		}
    	if(empty($xml)){
			echo 'XML格式不正确，不支持采集2。';
			exit;
		}
    }
    
    
    
    
    $pgcount = (string)@$xml->list->attributes()->pagecount;
    $pgsize = (string)@$xml->list->attributes()->pagesize;
    $recordcount = (string)@$xml->list->attributes()->recordcount;
    
    
	if($recordcount=='0'){
		echo '没有任何可用数据'. jump('?m=collect-list-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-apiurl-'.$apiurl,1);
		return;
	}
	
	echo '当前采集任务<strong class="green">'.$pg.'</strong>/<span class="green">'.$pgcount.'</span>页<br>';
	ob_flush();flush();
	
	$inrule = $MAC['collect']['vod']['inrule'];
	$uprule = $MAC['collect']['vod']['uprule'];
	$filter = $MAC['collect']['vod']['filter'];

	$players = $GLOBALS['MAC_CACHE']['vodplay'];
	if($ct==1) {
        $players = $GLOBALS['MAC_CACHE']['voddown'];
    }
    $key=0;
    $i=0;
    foreach($xml->list->video as $video){

    	$i++;
        $rc = false;
        $d_id = (string)$video->id;
        $d_name = format_vodname(filter_tags((string)$video->name)); $d_name = str_replace("'", "''",$d_name);
        $d_subname = filter_tags((string)$video->subname); $d_subname = str_replace("'", "''",$d_subname);
        $d_remarks = filter_tags((string)$video->note); $d_remarks = str_replace("'", "''",$d_remarks);
        $d_state = intval((string)$video->state);
        $d_type = $xt=='0'? (string)$video->tid : $flag.(string)$video->tid;
        $d_type = intval( $bindcache[$d_type] );
        $d_starring = filter_tags((string)$video->actor); $d_starring = str_replace("'", "''",$d_starring);
        $d_directed = filter_tags((string)$video->director); $d_directed = str_replace("'", "''",$d_directed);
        $d_pic = filter_tags((string)$video->pic); $d_pic = str_replace("'", "''",$d_pic);
        $d_time = (string)$video->last;
        $d_year = intval((string)$video->year);
        $d_area = filter_tags((string)$video->area); $d_area = str_replace("'", "''",$d_area);
        $d_lang = filter_tags((string)$video->lang); $d_lang = str_replace("'", "''",$d_lang);
        $d_content = filter_tags((string)$video->des); $d_content = str_replace("'", "''",$d_content);

        $d_enname = Hanzi2PinYin($d_name);
        $d_letter = strtoupper(substring($d_enname,1));
        $d_addtime = time();
        $d_time = $d_addtime;
        $d_hitstime = "";
        $d_hits = rand($MAC['collect']['vod']['hitsstart'],$MAC['collect']['vod']['hitsend']);
        $d_dayhits = rand($MAC['collect']['vod']['hitsstart'],$MAC['collect']['vod']['hitsend']);
        $d_weekhits = rand($MAC['collect']['vod']['hitsstart'],$MAC['collect']['vod']['hitsend']);
        $d_monthhits = rand($MAC['collect']['vod']['hitsstart'],$MAC['collect']['vod']['hitsend']);
		
		$d_scorenum = rand(1,500);
        $d_scoreall = $d_scorenum * rand(1,10);
        $d_score = round( $d_scoreall / $d_scorenum ,1);
        
        $d_hide = $MAC['collect']['vod']['hide'];
        if($MAC['collect']['vod']['psernd']==1){
        	$d_content = repPseRnd('vod',$d_content,$i);
        }
        if($MAC['collect']['vod']['psesyn']==1){
        	$d_content = repPseSyn('vod',$d_content);
        }

        $d_type_expand='';
        $d_class='';
        $d_color='';
        $d_picslide='';
        $d_lock=0;
        $d_hitstime=0;
        $d_maketime=0;
        $d_downfrom='';
        $d_downserver='';
        $d_downnote='';
        $d_downurl='';
        $d_playfrom='';
        $d_playserver='';
        $d_playnote='';
        $d_playurl='';
        $d_tag='';
        $msg='';
        
        if($d_type<1) { $des = '<font color="red">分类未绑定，跳过。</font>'; }
        elseif(empty($d_name)) { $des = '<font color="red">数据不完整，跳过。</font>'; }
        elseif(strpos(','.$filter,$d_name)) { $des = '<font color="red">数据在过滤单中，跳过。</font>'; }
        else{
	        $sql = "SELECT * FROM {pre}vod WHERE d_name ='".$d_name."' ";
	        if(strpos($inrule,'b')){ $sql.=' and d_type='.$d_type; }
	        if(strpos($inrule,'c')){ $sql.=' and d_year='.$d_year; }
	        if(strpos($inrule,'d')){ $sql.=' and d_area=\''.$d_area.'\''; }
	        if(strpos($inrule,'e')){ $sql.=' and d_lang=\''.$d_lang.'\''; }
	        if(strpos($inrule,'f')){ $sql.=' and d_starring=\''.$d_starring.'\''; }
	        if(strpos($inrule,'g')){ $sql.=' and d_directed=\''.$d_directed.'\''; }
	        
	        if($MAC['collect']['vod']['tag']==1){
				$d_tag = getTag($d_name,$d_content);
			}
	        
	        $row = $db->getRow($sql);
	        if(!$row){
                if($count=count($video->dl->dd)) {
                    for ($j = 0; $j < $count; $j++) {
                        $f1=  (string)$video->dl->dd[$j]['flag'];
                        if(empty($players[$f1])){
                            continue;
                        }
                        if ($rc) {
                            $d_playfrom .= "$$$";
                            $d_playserver .= "$$$";
                            $d_playnote .= "$$$";
                            $d_playurl .= "$$$";
                        }
                        $d_playfrom .= strip_tags(getFrom( $f1 ));
                        $d_playurl .= strip_tags(getVUrl( (string)$video->dl->dd[$j] ));
                        $d_playserver .= '0';
                        $d_playnote .= '';
                        $rc = true;
                    }
                }
                
	        	if($MAC['collect']['vod']['pic']==1){
		    		$ext = @substr($d_pic,strlen($d_pic)-3);
		    		if($ext!='jpg' || $ext!='bmp' || $ext!='gif'){ $ext='jpg'; }
		    		$fname = time() .$i .'.'. $ext;
		    		$path = "upload/vod/" . getSavePicPath('') . "/";
		    		$thumbpath = "upload/vodthumb/" . getSavePicPath('vodthumb') . "/";
		    		
		    		$ps=false;
		    		$ps = savepic($d_pic,$path,$thumbpath,$fname,'vod',$msg);
		    		if($ps){ $d_pic=$path.$fname; $d_picthumb= $thumbpath.$fname; }
		    	}
		    	$cols = array("d_type",'d_type_expand','d_class',"d_name","d_enname",'d_subname','d_color',"d_letter","d_state","d_remarks","d_tag","d_pic",'d_picslide','d_picthumb',"d_hits","d_dayhits","d_weekhits","d_monthhits","d_score","d_scoreall","d_scorenum","d_starring", "d_directed","d_year","d_area","d_lang","d_addtime","d_time",'d_hitstime','d_maketime',"d_hide",'d_lock',"d_content");
		    	$vals = array($d_type,$d_type_expand,$d_class,$d_name,$d_enname,$d_subname,$d_color,$d_letter,$d_state,$d_remarks,$d_tag,$d_pic,$d_picslide,$d_picthumb,$d_hits,$d_dayhits,$d_weekhits,$d_monthhits,$d_score,$d_scoreall,$d_scorenum,$d_starring,$d_directed,$d_year,$d_area, $d_lang,$d_addtime,$d_time,$d_hitstime,$d_maketime,$d_hide,$d_lock,$d_content);
		    	
		    	if($ct=="1"){
		    		array_push($cols,'d_downfrom','d_downurl','d_downserver','d_downnote','d_playfrom','d_playurl','d_playserver','d_playnote');
                }
                else{
                	array_push($cols,'d_playfrom','d_playurl','d_playserver','d_playnote','d_downfrom','d_downurl','d_downserver','d_downnote');
                }
                array_push($vals,$d_playfrom,$d_playurl,$d_playserver,$d_playnote,'','','','');
                

	        	$db->Add ("{pre}vod", $cols, $vals);
	        	$des= '<font color="green">新加入库，成功。</font>';
	        }
	        else{
                if($row['d_lock']==1){
                	$des= '<font color="red">数据已经锁定，跳过。</font>';
                }
                else{
                	if($ct=="1"){
                		$n_from = $row["d_downfrom"];
		                $n_server = $row["d_downserver"];
		                $n_note = $row['d_downnote'];
		                $n_url = $row["d_downurl"];
                	}
                	else{
		                $n_from = $row["d_playfrom"];
		                $n_server = $row["d_playserver"];
		                $n_note = $row['d_playnote'];
		                $n_url = $row["d_playurl"];
		            }
                	$des = '';

                    if($count=count($video->dl->dd)) {
                        $rc = false;
                        for ($j = 0; $j < $count; $j++) {
                            $f1 = (string)$video->dl->dd[$j]['flag'];
                            if(empty($players[$f1])){
                                continue;
                            }
                            $d_playfrom = strip_tags(getFrom($f1));
                            $d_playurl = strip_tags(getVUrl((string)$video->dl->dd[$j]));
                            if ($n_url == $d_playurl) {
                                $des .= '<font color="red">地址相同，跳过。</font>';
                                continue;
                            } elseif (empty($d_playfrom)) {
                                $des .= '<font color="red">播放器类型为空，跳过。</font>';
                                continue;
                            } elseif (strpos("," . $n_from, $d_playfrom) <= 0) {
                                $rc = true;
                                $des .= '<font color="green">播放组(' . $d_playfrom . ')，新增。</font>';
                                $n_url .= (empty($n_url)? '' : "$$$") . $d_playurl;
		                        $n_from .= (empty($n_from)? '' : "$$$") . $d_playfrom;
		                        $n_server .= (empty($n_server)? '' : "$$$") . $d_playserver;
		                        $n_note .= (empty($n_note)? '' : "$$$") . $d_playnote;
                            } else {
                                $arr1 = explode("$$$", $n_url);
                                $arr2 = explode("$$$", $n_from);
                                $play_key = array_search($d_playfrom, $arr2);
                                if ($arr1[$play_key] == $d_playurl) {
                                    $des .= '<font color="red">播放组(' . $d_playfrom . ')，无需更新。</font>';
                                } else {
                                    $rc = true;
                                    $des .= '<font color="green">播放组(' . $d_playfrom . ')，更新。</font>';
                                    $arr1[$play_key] = $d_playurl;
                                }
                                $n_url = join('$$$', $arr1);
                            }
                        }
                    }

		            if($rc){
		            	
        				if (empty($row["d_pic"]) || strpos(",".$row["d_pic"], "http:")>0) { } else { $d_pic= $row["d_pic"];}
        				if (empty($row["d_picthumb"]) || strpos(",".$row["d_picthumb"], "http:") > 0) { } else { $d_picthumb= $row["d_picthumb"];}
        				if (empty($row["d_picslide"]) || strpos(",".$row["d_picslide"], "http:") > 0) { } else { $d_picslide= $row["d_picslide"];}
        				
	                	$colarr = array();
	                	$valarr = array();
	                	array_push($colarr,'d_time');
	                	array_push($valarr,time());



	                	if(strpos(','.$uprule,'a') && $ct!=1){
	                		array_push($colarr,'d_playfrom','d_playserver','d_playnote','d_playurl');
	                		array_push($valarr,$n_from,$n_server,$n_note,$n_url);
	                	}
	                	if(strpos(','.$uprule,'b') && $ct==1){
	                		array_push($colarr,'d_downfrom','d_downserver','d_downnote','d_downurl');
	                		array_push($valarr,$n_from,$n_server,$n_note,$n_url);
	                	}
	                	if(strpos(','.$uprule,'c')){ array_push($colarr,'d_state'); array_push($valarr,$d_state); }
	                	if(strpos(','.$uprule,'d')){ array_push($colarr,'d_remarks'); array_push($valarr,$d_remarks); }
	                	if(strpos(','.$uprule,'e')){ array_push($colarr,'d_directed'); array_push($valarr,$d_directed); }
	                	if(strpos(','.$uprule,'f')){ array_push($colarr,'d_starring'); array_push($valarr,$d_starring); }
	                	if(strpos(','.$uprule,'g')){ array_push($colarr,'d_year'); array_push($valarr,$d_year); }
	                	if(strpos(','.$uprule,'h')){ array_push($colarr,'d_area'); array_push($valarr,$d_area); }
	                	if(strpos(','.$uprule,'i')){ array_push($colarr,'d_lang'); array_push($valarr,$d_lang); }
	                	if(strpos(','.$uprule,'j')){
	                		if($MAC['collect']['vod']['pic']==1){
					    		$ext = @substr($d_pic,strlen($d_pic)-3);
					    		if($ext!='jpg' || $ext!='bmp' || $ext!='gif'){$ext='jpg';}
					    		$fname = time() .$i .'.'. $ext;
					    		$path = "upload/vod/" . getSavePicPath('') . "/";
		    					$thumbpath = "upload/vodthumb/" . getSavePicPath('vodthumb') . "/";
					    		$ps = savepic($d_pic,$path,$thumbpath,$fname,'vod',$msg);
					    		if($ps){
					    			$d_pic=$path.$fname; $d_picthumb= $thumbpath.$fname; 
					    			array_push($colarr,'d_pic'); array_push($valarr,$d_pic);
					    			array_push($colarr,'d_picthumb'); array_push($valarr,$d_picthumb);
					    		}
					    	}
					    	else{
					    		array_push($colarr,'d_pic'); array_push($valarr,$d_pic);
					    		array_push($colarr,'d_picthumb'); array_push($valarr,$d_picthumb);
					    	}
	                	}
	                	if(strpos(','.$uprule,'k')){ array_push($colarr,'d_content'); array_push($valarr,$d_content); }
	                	if(strpos(','.$uprule,'l')){ array_push($colarr,'d_tag'); array_push($valarr,$d_tag); }
	                	if(strpos(','.$uprule,'m')){ array_push($colarr,'d_subname'); array_push($valarr,$d_subname); }
	                	
	                	if(count($colarr)>0){
	                		$db->Update ("{pre}vod",$colarr,$valarr,"d_id=".$row["d_id"] );
	                	}
	                }
	            }
            }
            unset($row);
		}
echo <<<EOT
<div>$i.  $d_name  $des  $msg </div>
EOT;
ob_flush();flush();
	}
	unset($pinyins);
    
    if ($ac2 == "sel"){
        delBreak ("union");
		echo "<br>数据采集完成";
		jump('?m=collect-list-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-pg-'.$pg.'-type-'.$type.'-apiurl-'. $apiurl,3);
    }
    else{
		if ($pg >= $pgcount){
            delBreak ("union");
            echo "<br>数据采集完成";
            jump('?m=collect-list-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-type-'.$type.'-apiurl-'. $apiurl,1);
        }
        else{
        	jump('?m=collect-cj-ac2-'.$ac2.'-pg-'.($pg+1).'-type-'.$type.'-hour-'.$hour.'-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-apiurl-'. $apiurl,3);
        }
    }
}

elseif($method=='listjson')
{
	$plt->set_file('main', $ac.'_'.$method.'.html');
	
	$colarr=array('action','type','pg','apiurl','wd','hour','xt','group','xt','ct','flag','mid','pre');
	$valarr=array($method,$type,$pg,$apiurl,$wd,$hour,$xt,$group,$xt,$ct,$flag,$mid,$pre);
	for($i=0;$i<count($colarr);$i++){
		$n = $colarr[$i];
		$v = $valarr[$i];
		$plt->set_var($n,$v);
	}
	
	$type>0 ? $istype=true : $istype=false;
	$plt->set_if('main','istype',$istype);


	if($xt=='0'){
    	$url = $apiurl . '?action=list&rpage='.$pg.'&rtype='.$type.'&rkey='.urlencode($wd);
	}
    elseif($xt=='1'){
    	$url = $apiurl . '?ac=list&pg=' . $pg . '&rid='.$group . '&t=' . $type . '&wd=' . urlencode($wd);
    }
    elseif($xt=='2'){
    	$url = $apiurl . '-action-list-cid-'. $type . '-h-'. $hour. '-p-' . $pg. '-wd-'. urlencode($wd);
    	$url = str_replace('|','-',$url);
    }
    $url .= base64_decode($param);
    $html = getPage($url, "utf-8");
	if(empty($html)){
		echo '连接API资源库失败，通常为服务器网络不稳定或禁用了采集。请刷新重试！';
		exit;
	}
		
	$json = json_decode($html,true);
    if(empty($json)){
		echo 'json格式不正确，不支持采集';
		exit;
    }
	
	
    $pgcount = $json['pagecount'];
    $pgsize = $json['pagesize'];;
    $recordcount = $json['recordcount'];;


    $key=0;
    $colarr=array('v','n');
    $rn='type';
    $plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
    foreach($json['class'] as $ty){
        $typeid = $ty['type_id'];
        $typename = $ty['type_name'];
        $isbind = false;
        $uid = intval( $bindcache[$flag.$typeid] );
        if ($uid>0){
            $isbind=true;
        }
        $valarr=array($typeid,$typename);
        for($i=0;$i<count($colarr);$i++){
            $n = $colarr[$i];
            $v = $valarr[$i];
            $plt->set_var($n,$v);
        }
        $plt->parse('rows_'.$rn,'list_'.$rn,true);
        $plt->set_if('rows_'.$rn,'isbind',$isbind);
        $key++;
    }
    

    if( count($json['list'])==0){
        $plt->set_if('main','isnull',true);
        return;
    }
    $plt->set_if('main','isnull',false);
    
    $colarr=array('id','name','typename','from','time','chk','nameencode');
    $rn='data';
	$plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);

    $key=0;
    
    foreach($json['list'] as $one){
        $id = $one[$pre.'_id'];
        $name = $one[$pre.'_name'];
        $nameencode = urlencode(substring($name,4));
        $typename = $one['type_name'];;
        $now = date('Y-m-d',time());

        $time = $one[$pre.'_time'];
        $sc = substr_count($time,'-');
        if($sc==1){ $time = date('Y-',time()).$time; }
        $time = getColorDay(strtotime($time));
        $chk = strpos(','.$time,$now)>0 ? 'checked' : '';
        if($mid==2){
        	$from = $one[$pre.'_from'];
        }
        else{
        	$from = $one[$pre.'_play_from'];
        }
        $valarr=array($id,$name,$typename,$from,$time,$chk,$nameencode);
        for($i=0;$i<count($colarr);$i++){
            $n = $colarr[$i];
            $v = $valarr[$i];
            $plt->set_var($n,$v);
        }
        $plt->parse('rows_'.$rn,'list_'.$rn,true);
        $key++;
    }

    unset($colarr);
    unset($valarr);
    
    $pgurl = '?m=collect-listjson-pg-{pg}-xt-'.$xt.'-ct-'.$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-type-'.$type.'-wd-'.urlencode($wd).'-apiurl-'.$apiurl;
	$pgs = '共'.$recordcount.'条数据&nbsp;当前:'.$pg.'/'.$pgcount.'页&nbsp;'.pageshow($pg,$pgcount,5,$pgurl,'pagego(\''.$pgurl.'\','.$pgcount.')');
	$plt->set_var('pages', $pgs );
}

elseif($method=='cjjson'){
	headAdmin2('数据采集');

	$acc='videolist';
	if($pre=='art'){
	    $acc='detail';
    }
	switch($ac2)
	{
		case 'sel':
			$ids = be("arr", "ids");
			if(empty($ids)) { errMsg ("采集提示", "请选择采集数据");}
			switch($xt)
			{
				case '0': $url = "?action=cjsel&ids=".$ids;
					break;
				case '1': $url = "?ac=".$acc."&rid=".$group."&ids=".$ids;
					break;
				case '2': $url = "-action-ids-vodids-".$ids."-cid--play--inputer--wd--h-0-p-1";
					break;
			}
			break;
		case 'day':
			switch($xt)
			{
				case '0': $url = "?action=cjday&rday=".$hour."&rpage=".$pg;
					break;
				case '1': $url = "?ac=".$acc."&rid=".$group."&h=".$hour."&pg=".$pg;
					break;
				case '2': $url = "-action-day-vodids--cid--play--inputer--wd--h-".$hour."-p-".$pg;
					break;
			}
			break;
		case 'type':
			if(empty($type)){ showMsg ("请先进入分类,否则无法使用采集分类!", $backurl); exit; }
			switch($xt)
			{
				case '0': $url = "?action=cjtype&rpage=".$pg."&rtype=". $type;
					break;
				case '1': $url = "?ac=".$acc."&rid=".$group."&pg=" . $pg . "&t=" . $type;
					break;
				case '2': $url = "-action-all-vodids--cid-".$type."-play--inputer--wd--h-0-p-".$pg;
					break;
			}
			break;
		case 'all':
			switch($xt)
			{
				case '0': $url = "?action=cjall&rpage=".$pg;
					break;
				case '1': $url = "?ac=".$acc."&rid=".$group."&pg=". $pg;
					break;
				case '2': $url = "-action-all-vodids--cid--play--inputer--wd--h-0-p-" . $pg;
					break;
			}
			break;
	}
	$url = $apiurl.$url;
    
    if($xt=="0"){

    }
    elseif($xt=="1"){

    }
    elseif($xt=="2"){
    	$url = str_replace('|','-',$url);
    }
    
    setBreak ("union", "?m=collect-cjjson-ac2-".$ac2."-xt-".$xt."-ct-".$ct."-group-".$group."-flag-".$flag."-pg-".$pg."-mid-".$mid."-type-" .$type."-wd-".$wd."-apiurl-".$apiurl);
    echo '采集地址&nbsp;'.$url.'<br>';
   	ob_flush();flush();
    $url .= base64_decode($param);
    $html = getPage($url, "utf-8");
	if(empty($html)){
		echo '连接API资源库失败，通常为服务器网络不稳定或禁用了采集。请刷新重试！';
		exit;
	}
		
	$json = json_decode($html,true);
    if(empty($json)){
		echo 'json格式不正确，不支持采集';
		exit;
    }
    
    $pgcount = $json['pagecount'];
    $pgsize = $json['pagesize'];
    $recordcount = $json['recordcount'];
    
    
	if($recordcount=='0'){
		echo '没有任何可用数据'. jump('?m=collect-listjson-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-apiurl-'.$apiurl,1);
		return;
	}
	
	echo '当前采集任务<strong class="green">'.$pg.'</strong>/<span class="green">'.$pgcount.'</span>页<br>';
	ob_flush();flush();
	$key=0;
    $i=0;
    
    
    
	if($pre=='vod') {
        $inrule = $MAC['collect']['vod']['inrule'];
        $uprule = $MAC['collect']['vod']['uprule'];
        $filter = $MAC['collect']['vod']['filter'];

        $players = $GLOBALS['MAC_CACHE']['vodplay'];
        $downers = $GLOBALS['MAC_CACHE']['voddown'];


        foreach ($json['list'] as $one) {

            $i++;
            $rc = false;
            $d_id = $one['vod_id'];
            $d_name = format_vodname(filter_tags($one['vod_name']));
            $d_name = str_replace("'", "''", $d_name);
            $d_subname = filter_tags($one['vod_sub']); $d_remarks = str_replace("'", "''",$d_subname);
            $d_remarks = filter_tags($one['vod_remarks']); $d_remarks = str_replace("'", "''", $d_remarks);
            $d_state = intval($one['vod_serial']);
            $d_type = $xt == '0' ? $one['type_id'] : $flag . $one['type_id'];
            $d_type = intval($bindcache[$d_type]);
            $d_starring = filter_tags($one['vod_actor']);  $d_starring = str_replace("'", "''", $d_starring);
            $d_directed = filter_tags($one['vod_director']);  $d_directed = str_replace("'", "''", $d_directed);
            $d_pic = filter_tags($one['vod_pic']);
            $d_pic = str_replace("'", "''", $d_pic);
            $d_time = $one['vod_time'];
            $d_year = intval($one['vod_year']);
            $d_area = filter_tags($one['vod_area']);  $d_area = str_replace("'", "''", $d_area);
            $d_lang = filter_tags($one['vod_lang']);  $d_lang = str_replace("'", "''", $d_lang);
            $d_content = filter_tags($one['vod_content']);  $d_content = str_replace("'", "''", $d_content);

            $d_enname = Hanzi2PinYin($d_name);
            $d_letter = strtoupper(substring($d_enname, 1));
            $d_addtime = time();
            $d_time = $d_addtime;
            $d_hitstime = "";
            $d_hits = rand($MAC['collect']['vod']['hitsstart'], $MAC['collect']['vod']['hitsend']);
            $d_dayhits = rand($MAC['collect']['vod']['hitsstart'], $MAC['collect']['vod']['hitsend']);
            $d_weekhits = rand($MAC['collect']['vod']['hitsstart'], $MAC['collect']['vod']['hitsend']);
            $d_monthhits = rand($MAC['collect']['vod']['hitsstart'], $MAC['collect']['vod']['hitsend']);

            $d_scorenum = rand(1, 500);
            $d_scoreall = $d_scorenum * rand(1, 10);
            $d_score = round($d_scoreall / $d_scorenum, 1);

            $d_hide = $MAC['collect']['vod']['hide'];
            if ($MAC['collect']['vod']['psernd'] == 1) {
                $d_content = repPseRnd('vod', $d_content, $i);
            }
            if ($MAC['collect']['vod']['psesyn'] == 1) {
                $d_content = repPseSyn('vod', $d_content);
            }

            //验证地址
            $cj_play_from_arr = explode('$$$',$one['vod_play_from'] );
            $cj_play_url_arr = explode('$$$',$one['vod_play_url']);
            $cj_play_server_arr = explode('$$$',$one['vod_play_server']);
            $cj_play_note_arr = explode('$$$',$one['vod_play_note']);
            $cj_down_from_arr = explode('$$$',$one['vod_down_from'] );
            $cj_down_url_arr = explode('$$$',$one['vod_down_url']);
            $cj_down_server_arr = explode('$$$',$one['vod_down_server']);
            $cj_down_note_arr = explode('$$$',$one['vod_down_note']);
            foreach($cj_play_from_arr as $kk=>$vv){
                if(empty($vv)){
                    unset($cj_play_from_arr[$kk]);
                    unset($cj_play_url_arr[$kk]);
                    unset($cj_play_server_arr[$kk]);
                    unset($cj_play_note_arr[$kk]);
                    continue;
                }
                if(empty($players[$vv])){
                    unset($cj_play_from_arr[$kk]);
                    unset($cj_play_url_arr[$kk]);
                    unset($cj_play_server_arr[$kk]);
                    unset($cj_play_note_arr[$kk]);
                    continue;
                }
                $cj_play_url_arr[$kk] = rtrim($cj_play_url_arr[$kk],'#');
                $cj_play_server_arr[$kk] = $cj_play_server_arr[$kk];
                $cj_play_note_arr[$kk] = $cj_play_note_arr[$kk];
            }
            foreach($cj_down_from_arr as $kk=>$vv){
                if(empty($vv)){
                    unset($cj_down_from_arr[$kk]);
                    unset($cj_down_url_arr[$kk]);
                    unset($cj_down_server_arr[$kk]);
                    unset($cj_down_note_arr[$kk]);
                    continue;
                }
                if(empty($downers[$vv])){
                    unset($cj_down_from_arr[$kk]);
                    unset($cj_down_url_arr[$kk]);
                    unset($cj_down_server_arr[$kk]);
                    unset($cj_down_note_arr[$kk]);
                    continue;
                }
                $cj_down_url_arr[$kk] = rtrim($cj_down_url_arr[$kk]);
                $cj_down_server_arr[$kk] = $cj_down_server_arr[$kk];
                $cj_down_note_arr[$kk] = $cj_down_note_arr[$kk];
            }
            $one['vod_play_from'] = join('$$$',$cj_play_from_arr);
            $one['vod_play_url'] = join('$$$',$cj_play_url_arr);
            $one['vod_play_server'] = join('$$$',$cj_play_server_arr);
            $one['vod_play_note'] = join('$$$',$cj_play_note_arr);
            $one['vod_down_from'] = join('$$$',$cj_down_from_arr);
            $one['vod_down_url'] = join('$$$',$cj_down_url_arr);
            $one['vod_down_server'] = join('$$$',$cj_down_server_arr);
            $one['vod_down_note'] = join('$$$',$cj_down_note_arr);

            if(empty($one['vod_play_from'])) $one['vod_play_from']='';
            if(empty($one['vod_play_url'])) $one['vod_play_url']='';
            if(empty($one['vod_play_server'])) $one['vod_play_server']='';
            if(empty($one['vod_play_note'])) $one['vod_play_note']='';

            if(empty($one['vod_down_from'])) $one['vod_down_from']='';
            if(empty($one['vod_down_url'])) $one['vod_down_url']='';
            if(empty($one['vod_down_server'])) $one['vod_down_server']='';
            if(empty($one['vod_down_note'])) $one['vod_down_note']='';

            $d_type_expand = '';
            $d_class = '';
            $d_color = '';
            $d_picslide = '';
            $d_lock = 0;
            $d_hitstime = 0;
            $d_maketime = 0;
            $d_downfrom = '';
            $d_downserver = '';
            $d_downnote = '';
            $d_downurl = '';
            $d_playfrom = '';
            $d_playserver = '';
            $d_playnote = '';
            $d_playurl = '';
            $d_tag = '';
            $msg = '';

            if ($d_type < 1) {
                $des = '<font color="red">分类未绑定，跳过。</font>';
            } elseif (empty($d_name)) {
                $des = '<font color="red">数据不完整，跳过。</font>';
            } elseif (strpos(',' . $filter, $d_name)) {
                $des = '<font color="red">数据在过滤单中，跳过。</font>';
            } else {
                $sql = "SELECT * FROM {pre}vod WHERE d_name ='" . $d_name . "' ";
                if (strpos($inrule, 'b')) {
                    $sql .= ' and d_type=' . $d_type;
                }
                if (strpos($inrule, 'c')) {
                    $sql .= ' and d_year=' . $d_year;
                }
                if (strpos($inrule, 'd')) {
                    $sql .= ' and d_area=\'' . $d_area . '\'';
                }
                if (strpos($inrule, 'e')) {
                    $sql .= ' and d_lang=\'' . $d_lang . '\'';
                }
                if (strpos($inrule, 'f')) {
                    $sql .= ' and d_starring=\'' . $d_starring . '\'';
                }
                if (strpos($inrule, 'g')) {
                    $sql .= ' and d_directed=\'' . $d_directed . '\'';
                }

                if ($MAC['collect']['vod']['tag'] == 1) {
                    $d_tag = getTag($d_name, $d_content);
                }

                $row = $db->getRow($sql);
                if (!$row) {
                    if ($MAC['collect']['vod']['pic'] == 1) {
                        $ext = @substr($d_pic, strlen($d_pic) - 3);
                        if ($ext != 'jpg' || $ext != 'bmp' || $ext != 'gif') {
                            $ext = 'jpg';
                        }
                        $fname = time() . $i . '.' . $ext;
                        $path = "upload/vod/" . getSavePicPath('') . "/";
                        $thumbpath = "upload/vodthumb/" . getSavePicPath('vodthumb') . "/";

                        $ps = false;
                        $ps = savepic($d_pic, $path, $thumbpath, $fname, 'vod', $msg);
                        if ($ps) {
                            $d_pic = $path . $fname;
                            $d_picthumb = $thumbpath . $fname;
                        }
                    }
                    $cols = array("d_type", 'd_type_expand', 'd_class', "d_name", "d_enname", 'd_subname', 'd_color', "d_letter", "d_state", "d_remarks", "d_tag", "d_pic", 'd_picslide', 'd_picthumb', "d_hits", "d_dayhits", "d_weekhits", "d_monthhits", "d_score", "d_scoreall", "d_scorenum", "d_starring", "d_directed", "d_year", "d_area", "d_lang", "d_addtime", "d_time", 'd_hitstime', 'd_maketime', "d_hide", 'd_lock', "d_content");
                    $vals = array($d_type, $d_type_expand, $d_class, $d_name, $d_enname, $d_subname, $d_color, $d_letter, $d_state, $d_remarks, $d_tag, $d_pic, $d_picslide, $d_picthumb, $d_hits, $d_dayhits, $d_weekhits, $d_monthhits, $d_score, $d_scoreall, $d_scorenum, $d_starring, $d_directed, $d_year, $d_area, $d_lang, $d_addtime, $d_time, $d_hitstime, $d_maketime, $d_hide, $d_lock, $d_content);

                    $d_playfrom .= strip_tags($one['vod_play_from']);;
                    $d_playurl .= strip_tags($one['vod_play_url']);
                    $d_playserver .= strip_tags($one['vod_play_server']);
                    $d_playnote .= strip_tags($one['vod_play_note']);
                    $d_downfrom .= strip_tags($one['vod_down_from']);
                    $d_downurl .= strip_tags($one['vod_down_url']);
                    $d_downserver .= strip_tags($one['vod_down_server']);
                    $d_downnote .= strip_tags($one['vod_down_note']);

                    array_push($cols, 'd_playfrom', 'd_playurl', 'd_playserver', 'd_playnote');
                    array_push($vals, $d_playfrom, $d_playurl, $d_playserver, $d_playnote);

                    array_push($cols, 'd_downfrom', 'd_downurl', 'd_downserver', 'd_downnote');
                    array_push($vals, $d_downfrom, $d_downurl, $d_downserver, $d_downnote);

                    $db->Add("{pre}vod", $cols, $vals);
                    $des = '<font color="green">新加入库，成功。</font>';
                } else {
                    if ($row['d_lock'] == 1) {
                        $des = '<font color="red">数据已经锁定，跳过。</font>';
                    } else {
                        if ($ct == "1") {
                            $n_from = $row["d_downfrom"];
                            $n_server = $row["d_downserver"];
                            $n_note = $row['d_downnote'];
                            $n_url = $row["d_downurl"];
                        } else {
                            $n_from = $row["d_playfrom"];
                            $n_server = $row["d_playserver"];
                            $n_note = $row['d_playnote'];
                            $n_url = $row["d_playurl"];
                        }
                        $des = '';
                        $colarr = array();
                        $valarr = array();

                        if (strpos(',' . $uprule, 'a')!==false && !empty($one['vod_play_from'])) {
                            $old_play_from = $row['d_playfrom'];
                            $old_play_url = $row['d_playurl'];
                            $old_play_server = $row['d_playserver'];
                            $old_play_note = $row['d_playnote'];
                            foreach ($cj_play_from_arr as $k2 => $v2) {
                                $cj_play_from = $v2;
                                $cj_play_url = $cj_play_url_arr[$k2];
                                $cj_play_server = $cj_play_server_arr[$k2];
                                $cj_play_note = $cj_play_note_arr[$k2];
                                if ($cj_play_url == $row['d_playurl']) {
                                    $des .= '<font color="red">播放地址相同，跳过。</font>';
                                } elseif (empty($cj_play_from)) {
                                    $des .= '<font color="red">播放器类型为空，跳过。</font>';
                                } elseif (strpos("," . $row['d_playfrom'], $cj_play_from) <= 0) {
                                    $color = 'green';
                                    $des .= '<font color="green">播放组(' . $cj_play_from . ')，新增ok。</font>';
                                    if(!empty($old_play_from)){
                                        $old_play_url .="$$$";
                                        $old_play_from .= "$$$" ;
                                        $old_play_server .= "$$$" ;
                                        $old_play_note .= "$$$" ;
                                    }
                                    $old_play_url .= "" . $cj_play_url;
                                    $old_play_from .= "" . $cj_play_from;
                                    $old_play_server .= "" . $cj_play_server;
                                    $old_play_note .= "" . $cj_play_note;
                                    $ec=true;
                                }  elseif (!empty($cj_play_url)) {
                                    $arr1 = explode("$$$", $old_play_url);
                                    $arr2 = explode("$$$", $old_play_from);
                                    $play_key = array_search($cj_play_from, $arr2);
                                    if ($arr1[$play_key] == $cj_play_url) {
                                        $des .= '<font color="red">播放组(' . $cj_play_from . ')，无需更新。</font>';
                                    } else {
                                        $color = 'green';
                                        $des .= '<font color="green">播放组(' . $cj_play_from . ')，更新ok。</font>';
                                        $arr1[$play_key] = $cj_play_url;
                                        $ec=true;
                                    }
                                    $old_play_url = join('$$$', $arr1);
                                }
                            }
                            if($ec) {
                                array_push($colarr, 'd_playfrom', 'd_playserver', 'd_playnote', 'd_playurl');
                                array_push($valarr, $old_play_from, $old_play_server, $old_play_note, $old_play_url);
                            }
                        }

                        if (strpos(',' . $uprule, 'b')!==false && !empty($one['vod_down_from'])) {
                            $old_play_from = $row['d_downfrom'];
                            $old_play_url = $row['d_downurl'];
                            $old_play_server = $row['d_downserver'];
                            $old_play_note = $row['d_downnote'];
                            foreach ($cj_play_from_arr as $k2 => $v2) {
                                $cj_play_from = $v2;
                                $cj_play_url = $cj_play_url_arr[$k2];
                                $cj_play_server = $cj_play_server_arr[$k2];
                                $cj_play_note = $cj_play_note_arr[$k2];
                                if ($cj_play_url == $row['d_downurl']) {
                                    $des .= '<font color="red">播放地址相同，跳过。</font>';
                                } elseif (empty($cj_play_from)) {
                                    $des .= '<font color="red">播放器类型为空，跳过。</font>';
                                } elseif (strpos("," . $row['d_downfrom'], $cj_play_from) <= 0) {
                                    $color = 'green';
                                    $des .= '<font color="green">播放组(' . $cj_play_from . ')，新增ok。</font>';
                                    if(!empty($old_play_from)){
                                        $old_play_url .="$$$";
                                        $old_play_from .= "$$$" ;
                                        $old_play_server .= "$$$" ;
                                        $old_play_note .= "$$$" ;
                                    }
                                    $old_play_url .= "" . $cj_play_url;
                                    $old_play_from .= "" . $cj_play_from;
                                    $old_play_server .= "" . $cj_play_server;
                                    $old_play_note .= "" . $cj_play_note;
                                    $ec=true;
                                }  elseif (!empty($cj_play_url)) {
                                    $arr1 = explode("$$$", $old_play_url);
                                    $arr2 = explode("$$$", $old_play_from);
                                    $play_key = array_search($cj_play_from, $arr2);
                                    if ($arr1[$play_key] == $cj_play_url) {
                                        $des .= '<font color="red">播放组(' . $cj_play_from . ')，无需更新。</font>';
                                    } else {
                                        $color = 'green';
                                        $des .= '<font color="green">播放组(' . $cj_play_from . ')，更新ok。</font>';
                                        $arr1[$play_key] = $cj_play_url;
                                        $ec=true;
                                    }
                                    $old_play_url = join('$$$', $arr1);
                                }
                            }
                            if($ec) {
                                array_push($colarr, 'd_downfrom', 'd_downserver', 'd_downnote', 'd_downurl');
                                array_push($valarr, $old_play_from, $old_play_server, $old_play_note, $old_play_url);
                            }
                        }
                        $rc=true;

                        if ($rc) {

                            if (empty($row["d_pic"]) || strpos("," . $row["d_pic"], "http:") > 0) {
                            } else {
                                $d_pic = $row["d_pic"];
                            }
                            if (empty($row["d_picthumb"]) || strpos("," . $row["d_picthumb"], "http:") > 0) {
                            } else {
                                $d_picthumb = $row["d_picthumb"];
                            }
                            if (empty($row["d_picslide"]) || strpos("," . $row["d_picslide"], "http:") > 0) {
                            } else {
                                $d_picslide = $row["d_picslide"];
                            }


                            array_push($colarr, 'd_time');
                            array_push($valarr, time());

                            if (strpos(',' . $uprule, 'c')) {
                                array_push($colarr, 'd_state');
                                array_push($valarr, $d_state);
                            }
                            if (strpos(',' . $uprule, 'd')) {
                                array_push($colarr, 'd_remarks');
                                array_push($valarr, $d_remarks);
                            }
                            if (strpos(',' . $uprule, 'e')) {
                                array_push($colarr, 'd_directed');
                                array_push($valarr, $d_directed);
                            }
                            if (strpos(',' . $uprule, 'f')) {
                                array_push($colarr, 'd_starring');
                                array_push($valarr, $d_starring);
                            }
                            if (strpos(',' . $uprule, 'g')) {
                                array_push($colarr, 'd_year');
                                array_push($valarr, $d_year);
                            }
                            if (strpos(',' . $uprule, 'h')) {
                                array_push($colarr, 'd_area');
                                array_push($valarr, $d_area);
                            }
                            if (strpos(',' . $uprule, 'i')) {
                                array_push($colarr, 'd_lang');
                                array_push($valarr, $d_lang);
                            }
                            if (strpos(',' . $uprule, 'j')) {
                                if ($MAC['collect']['vod']['pic'] == 1) {
                                    $ext = @substr($d_pic, strlen($d_pic) - 3);
                                    if ($ext != 'jpg' || $ext != 'bmp' || $ext != 'gif') {
                                        $ext = 'jpg';
                                    }
                                    $fname = time() . $i . '.' . $ext;
                                    $path = "upload/vod/" . getSavePicPath('') . "/";
                                    $thumbpath = "upload/vodthumb/" . getSavePicPath('vodthumb') . "/";
                                    $ps = savepic($d_pic, $path, $thumbpath, $fname, 'vod', $msg);
                                    if ($ps) {
                                        $d_pic = $path . $fname;
                                        $d_picthumb = $thumbpath . $fname;
                                        array_push($colarr, 'd_pic');
                                        array_push($valarr, $d_pic);
                                        array_push($colarr, 'd_picthumb');
                                        array_push($valarr, $d_picthumb);
                                    }
                                } else {
                                    array_push($colarr, 'd_pic');
                                    array_push($valarr, $d_pic);
                                    array_push($colarr, 'd_picthumb');
                                    array_push($valarr, $d_picthumb);
                                }
                            }
                            if (strpos(',' . $uprule, 'k')) {
                                array_push($colarr, 'd_content');
                                array_push($valarr, $d_content);
                            }
                            if (strpos(',' . $uprule, 'l')) {
                                array_push($colarr, 'd_tag');
                                array_push($valarr, $d_tag);
                            }
                            if (strpos(',' . $uprule, 'm')) {
                                array_push($colarr, 'd_subname');
                                array_push($valarr, $d_subname);
                            }

                            if (count($colarr) > 0) {
                                $db->Update("{pre}vod", $colarr, $valarr, "d_id=" . $row["d_id"]);
                            }
                        }
                    }
                }
                unset($row);
            }
            echo <<<EOT
<div>$i.  $d_name  $des  $msg </div>
EOT;
            ob_flush();
            flush();
        }
    }
	else {//文章json

        $inrule = $MAC['collect']['art']['inrule'];
        $uprule = $MAC['collect']['art']['uprule'];
        $filter = $MAC['collect']['art']['filter'];

        foreach ($json['list'] as $one) {

            $i++;
            $rc = false;
            $a_id = $one['art_id'];
            $a_name = format_vodname(filter_tags($one['art_name']));
            $a_name = str_replace("'", "''", $a_name);
            $a_remarks = filter_tags($one['art_remarks']); $a_remarks = str_replace("'", "''", $a_remarks);
            $a_subname = filter_tags($one['art_subname']); $a_subname = str_replace("'", "''", $a_remarks);
            $a_type = $xt == '0' ? $one['type_id'] : $flag . $one['type_id'];
            $a_type = intval($bindcache[$a_type]);
            $a_tag = filter_tags($one['art_tag']); $a_tag = str_replace("'", "''", $a_tag);
            $a_from = filter_tags($one['art_from']); $a_from = str_replace("'", "''", $a_from);
            $a_author = filter_tags($one['art_author']); $a_author = str_replace("'", "''", $a_author);
            $a_pic = filter_tags($one['art_pic']);$a_pic = str_replace("'", "''", $a_pic);
            $a_time = $one['art_time'];
            $a_content = filter_tags($one['art_content']); $a_content = str_replace("'", "''", $a_content);
            $a_content = str_replace("$$$", "[art:page]", $a_content);


            $a_enname = Hanzi2PinYin($a_name);
            $a_letter = strtoupper(substring($a_enname, 1));
            $a_addtime = time();
            $a_time = $a_addtime;
            $a_hitstime = "";
            $a_hits = rand($MAC['collect']['art']['hitsstart'], $MAC['collect']['art']['hitsend']);
            $a_dayhits = rand($MAC['collect']['art']['hitsstart'], $MAC['collect']['art']['hitsend']);
            $a_weekhits = rand($MAC['collect']['art']['hitsstart'], $MAC['collect']['art']['hitsend']);
            $a_monthhits = rand($MAC['collect']['art']['hitsstart'], $MAC['collect']['art']['hitsend']);

            $a_scorenum = rand(1, 500);
            $a_scoreall = $a_scorenum * rand(1, 10);
            $a_score = round($a_scoreall / $a_scorenum, 1);

            $a_hide = $MAC['collect']['art']['hide'];
            if ($MAC['collect']['art']['psernd'] == 1) {
                $a_content = repPseRnd('art', $a_content, $i);
            }
            if ($MAC['collect']['art']['psesyn'] == 1) {
                $a_content = repPseSyn('art', $a_content);
            }

            $a_type_expand = '';
            $a_class = '';
            $a_subname = '';
            $a_color = '';
            $a_picslide = '';
            $a_lock = 0;
            $a_hitstime = 0;
            $a_maketime = 0;
            $a_picthumb = '';

            $msg = '';

            if ($a_type < 1) {
                $des = '<font color="red">分类未绑定，跳过。</font>';
            } elseif (empty($a_name)) {
                $des = '<font color="red">数据不完整，跳过。</font>';
            } elseif (strpos(',' . $filter, $a_name)) {
                $des = '<font color="red">数据在过滤单中，跳过。</font>';
            } else {
                $sql = "SELECT * FROM {pre}vod WHERE a_name ='" . $a_name . "' ";
                if (strpos($inrule, 'b')) {
                    $sql .= ' and a_type=' . $a_type;
                }

                if ($MAC['collect']['art']['tag'] == 1) {
                    $a_tag = getTag($a_name, $a_content);
                }

                $row = $db->getRow($sql);
                if (!$row) {

                    if ($MAC['collect']['art']['pic'] == 1) {
                        $ext = @substr($a_pic, strlen($a_pic) - 3);
                        if ($ext != 'jpg' || $ext != 'bmp' || $ext != 'gif') {
                            $ext = 'jpg';
                        }
                        $fname = time() . $i . '.' . $ext;
                        $path = "upload/art/" . getSavePicPath('') . "/";
                        $thumbpath = "upload/artthumb/" . getSavePicPath('artthumb') . "/";

                        $ps = false;
                        $ps = savepic($a_pic, $path, $thumbpath, $fname, 'vod', $msg);
                        if ($ps) {
                            $a_pic = $path . $fname;
                            $a_picthumb = $thumbpath . $fname;
                        }
                    }
                    $cols = array("a_type", "a_name", "a_enname", 'a_subname', 'a_color', "a_letter", "a_from", "a_remarks", "a_tag", "a_pic", "a_hits", "a_dayhits", "a_weekhits", "a_monthhits", "a_author", "a_addtime", "a_time", 'a_hitstime', 'a_maketime', "a_hide", 'a_lock', "a_content");
                    $vals = array($a_type, $a_name, $a_enname, $a_subname, $a_color, $a_letter, $a_from, $a_remarks, $a_tag, $a_pic, $a_hits, $a_dayhits, $a_weekhits, $a_monthhits, $a_author, $a_addtime, $a_time, $a_hitstime, $a_maketime, $a_hide, $a_lock, $a_content);

                    $db->Add("{pre}art", $cols, $vals);
                    $des = '<font color="green">新加入库，成功。</font>';
                } else {
                    if ($row['a_lock'] == 1) {
                        $des = '<font color="red">数据已经锁定，跳过。</font>';
                    } else {
                        $des = '';

                        if ($rc) {

                            if (empty($row["a_pic"]) || strpos("," . $row["a_pic"], "http") > 0) {
                            } else {
                                $a_pic = $row["a_pic"];
                            }

                            $colarr = array();
                            $valarr = array();
                            array_push($colarr, 'a_time');
                            array_push($valarr, time());

                            if (strpos(',' . $uprule, 'a')) {
                                array_push($colarr, 'a_content');
                                array_push($valarr, $a_content);
                            }
                            if (strpos(',' . $uprule, 'b')) {
                                array_push($colarr, 'a_author');
                                array_push($valarr, $a_author);
                            }
                            if (strpos(',' . $uprule, 'c')) {
                                array_push($colarr, 'a_from');
                                array_push($valarr, $a_from);
                            }
                            if (strpos(',' . $uprule, 'e')) {
                                array_push($colarr, 'a_tag');
                                array_push($valarr, $a_tag);
                            }


                            if (strpos(',' . $uprule, 'd')) {
                                if ($MAC['collect']['art']['pic'] == 1) {
                                    $ext = @substr($a_pic, strlen($a_pic) - 3);
                                    if ($ext != 'jpg' || $ext != 'bmp' || $ext != 'gif') {
                                        $ext = 'jpg';
                                    }
                                    $fname = time() . $i . '.' . $ext;
                                    $path = "upload/art/" . getSavePicPath('') . "/";
                                    $thumbpath = "upload/artthumb/" . getSavePicPath('artthumb') . "/";
                                    $ps = savepic($a_pic, $path, $thumbpath, $fname, 'art', $msg);
                                    if ($ps) {
                                        $a_pic = $path . $fname;
                                        $a_picthumb = $thumbpath . $fname;
                                        array_push($colarr, 'a_pic');
                                        array_push($valarr, $a_pic);
                                        array_push($colarr, 'a_picthumb');
                                        array_push($valarr, $a_picthumb);
                                    }
                                } else {
                                    array_push($colarr, 'a_pic');
                                    array_push($valarr, $a_pic);
                                    array_push($colarr, 'a_picthumb');
                                    array_push($valarr, $a_picthumb);
                                }
                            }

                            if (count($colarr) > 0) {
                                $db->Update("{pre}art", $colarr, $valarr, "a_id=" . $row["a_id"]);
                            }
                        }
                    }
                }
                unset($row);
            }
            echo <<<EOT
<div>$i.  $a_name  $des  $msg </div>
EOT;
            ob_flush();
            flush();
        }
    }

	unset($pinyins);
    
    if ($ac2 == "sel"){
        delBreak ("union");
		echo "<br>数据采集完成";
		jump('?m=collect-listjson-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.'-mid-'.$mid.'-param-'.$param.'-pg-'.$pg.'-type-'.$type.'-apiurl-'. $apiurl,3);
    }
    else{
		if ($pg >= $pgcount){
            delBreak ("union");
            echo "<br>数据采集完成";
            jump('?m=collect-listjson-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-type-'.$type.'-apiurl-'. $apiurl,1);
        }
        else{
        	jump('?m=collect-cjjson-ac2-'.$ac2.'-pg-'.($pg+1).'-type-'.$type.'-hour-'.$hour.'-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-mid-'.$mid.'-param-'.$param.'-apiurl-'. $apiurl,3);
        }
    }
}

elseif($method=='vod')
{
	echo '预留功能';
}

elseif($method=='art')
{
	echo '预留功能';
}

elseif($method=='ds')
{
	$plt->set_file('main', $ac.'_'.$method.'.html');
}

elseif($method=='dsgo')
{
	
}

else
{
	showErr('System','未找到指定系统模块');
}
?>