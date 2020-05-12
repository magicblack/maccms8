<?php
if(!defined('MAC_ROOT')){
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



function cj(){
	global $MAC,$bindcache,$backurl,$typearr,$ac2,$apiurl,$flag,$xt,$ct,$group,$wd,$type,$pg,$hour,$db;
	
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
    

    $html = getPage($url, "utf-8");
	if(empty($html)){
		echo '连接API资源库失败，通常为服务器网络不稳定或禁用了采集';
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
			echo 'XML格式不正确，不支持采集。';
			exit;
		}
    }

    $pgcount = (string)@$xml->list->attributes()->pagecount;
    $pgsize = (string)@$xml->list->attributes()->pagesize;
    $recordcount = (string)@$xml->list->attributes()->recordcount;


	if($recordcount=='0'){
		echo '没有任何可用数据'. jump('?m=collect-list-xt-'.$xt."-ct-".$ct.'-group-'.$group.'-flag-'.$flag.'-apiurl-'.$apiurl,1);
		return;
	}
	
	echo '当前采集任务<strong class="green">'.$pg.'</strong>/<span class="green">'.$pgcount.'</span>页 采集地址&nbsp;'.$url;
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
//echo <<<EOT
//<div>$i.  $d_name  $des  $msg </div>
//EOT;
ob_flush();flush();
	}
	unset($pinyins);
    
    if($pg < $pgcount){
		$pg = $pg+1;
		cj();
	}
	else{
		echo '<br>采集完毕...';
	}
}


if(!empty($ac2) && !empty($apiurl)){
	cj();
}

?>