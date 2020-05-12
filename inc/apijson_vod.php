<?php
require("conn.php");
require(MAC_ROOT.'/inc/common/360_safe3.php');

if($MAC['api']['vod']['status']==0){ echo "closed"; exit; }
if($MAC['api']['vod']['charge'] == 1) {
	$h = $_SERVER['REMOTE_ADDR'];
	if (!$h) {
		echo '域名未授权！请联系QQ：'.$MAC['site']['qq'];
		exit;
	}
	else {
		$auth = $MAC['api']['vod']['auth'];
		$auths = array();
		if(!empty($auth)){
			$auths = explode('#',$auth);
			foreach($auths as $k=>$v){
				$auths[$k] = gethostbyname(trim($v));
			}
		}
		if($h != 'localhost' && $h != '127.0.0.1') {
			if(!in_array($h, $auths)){
				echo '域名未授权！请联系QQ：'.$MAC['site']['qq'];
				exit;
			}
		}
	}
}


getDbConnect();

$ac = be("get","ac");
$t = intval(be("get","t"));
$pg = intval(be("get","pg"));
$h= intval(be("get","h"));
$wd = be("get","wd"); $wd = chkSql($wd);
$ids= be("all","ids"); $ids = chkSql($ids);
if ($pg < 1){ $pg=1;}
$ty = be("get","ty");
$from =''; //不为空时只显示某种资源例如youku


if($ac=='videolist' || $ac=='detail')
{
	$cp = 'api';
	$cn = 'videolist-json-' . $t . "-" . $pg . "-" . $wd . "-" . $h . "-" . $from ."-" .$ty . "-". str_replace(",","",$ids); ;
	echoPageCache($cp,$cn);
	
	$sql = "select * from {pre}vod where 1=1 ";
	$sql1 = "select count(*) from {pre}vod where 1=1 ";
	
	if(!empty($ids)){
		$arr = explode(',',$ids);
		for($i=0;$i<count($arr);$i++){
			$arr[$i] = intval($arr[$i]);
		}
		$ids = join(',',$arr);
		unset($arr);
		$sql .= " AND d_id in (". $ids .")";
		$sql1 .= " AND d_id in (". $ids .")";
	}
	if($t>0){
		$sql .= " AND d_type =".$t;
		$sql1 .= " AND d_type =".$t;
	}
	if($h>0){
		$todaydate = date('Y-m-d');
		$tommdate = date('Y-m-d',strtotime('-'.$h.' hours')); 
		
		$todayunix = strtotime($todaydate);
		$tommunix = strtotime($tommdate);
		$whereStr = ' AND d_time>= '. $tommunix . ' AND d_time<='. $todayunix;
		
		$sql .=  $whereStr;
		$sql1 .= $whereStr;
	}
	if ($MAC['api']['vod']['vodfilter'] != "") { 
		$sql .= " ". $MAC['api']['vod']['vodfilter']." "; 
		$sql1 .= " ". $MAC['api']['vod']['vodfilter']." "; 
	}
	
	$nums = $db->getOne($sql1);
	$nums = intval($nums);
	$pagecount=ceil($nums/$MAC['api']['vod']['pagesize']);
	$sql = $sql ." limit ".($MAC['api']['vod']['pagesize'] * ($pg-1)).",".$MAC['api']['vod']['pagesize'];
	$rs = $db->query($sql);
	
	$json = [];
	$json['code'] = 1;
	$json['msg'] = '数据列表';
	$json['page'] = $pg;$json['pagecount'] = 0;$json['limit'] = $MAC['api']['vod']['pagesize'];$json['total'] = 0;
	$json['list'] = [];
	if (!$rs){
		echo "err";exit;
	}
	else{
		$json['page'] = $pg;$json['pagecount'] = $pagecount;$json['limit'] = $MAC['api']['vod']['pagesize'];$json['total'] = $nums;
		
		while ($row = $db ->fetch_array($rs))
		{
            if($GLOBALS['MAC']['app']['filtertags'] != '2') {
                $row = array_map('filter_tags', $row);
            }
			$tmp = urlDeal($row["d_playurl"],$row["d_playfrom"],$row["d_playserver"],$row["d_playnote"],$from);
		    if(substr($row["d_pic"],0,4)=="http"){ $temppic = $row["d_pic"]; } else { $temppic = $MAC['api']['vod']['imgurl'] . $row["d_pic"]; }
		    
		    $typearr =  $MAC_CACHE['vodtype'][$row["d_type"]];
		    
			
			$json['list'][] = array(
				'vod_time'=>date('Y-m-d H:i:s',$row["d_time"]),
				'vod_id'=>$row["d_id"],
				'vod_name'=>$row["d_name"],
				'vod_enname'=>$row['d_enname'],
				'vod_subname'=>$row['d_subname'],
				'vod_letter'=>$row['d_letter'],
				'vod_color'=>$row['d_color'],
				'vod_tag'=>$row['d_tag'],
				
				'type_id'=>$row["d_type"],
				'type_name'=>$typearr["t_name"],
				'vod_pic'=>$temppic,
				'vod_lang'=>$row["d_lang"],
				'vod_area'=>$row["d_area"],
				'vod_year'=>$row["d_year"],
				'vod_remark'=>$row["d_remarks"],
				'vod_actor'=>$row["d_starring"],
				'vod_director'=>$row["d_directed"],
					
						'vod_serial'=>$row["d_state"],
							
						'vod_lock'=>$row["d_lock"],
						'vod_level'=>$row["d_level"],
						'vod_hits'=>$row["d_hits"],
						'vod_hits_day'=>$row["d_dayhits"],
						'vod_hits_week'=>$row["d_weekhits"],
						'vod_hits_month'=>$row["d_monthhits"],
						'vod_duration'=>$row["d_duration"],
						'vod_up'=>$row["d_up"],
						'vod_down'=>$row["d_down"],
						'vod_score'=>$row["d_score"],
						'vod_score_all'=>$row["d_scoreall"],
						'vod_score_num'=>$row["d_scorenum"],
						
						'vod_points_play'=>$row["d_stint"],
						'vod_points_down'=>$row["d_stintdown"],
						
					
						'vod_play_from'=> $tmp['from'],
						'vod_play_note'=>$tmp["note"],
						'vod_play_server'=>$tmp["server"],
						'vod_play_url'=>$tmp['url'],
						
						'vod_down_from'=>$row["d_downfrom"],
						'vod_down_note'=>$row["d_downnote"],
						'vod_down_server'=>$row["d_downserver"],
						'vod_down_url'=>$row["d_downurl"],
						'vod_content'=>$row["d_content"],
						
					
				);
				
		}
	}
	unset($rs);
	$xml = json_encode($json);
	setPageCache($cp,$cn,$xml);
	echo $xml;
}

else
{
	$cp = 'api';
	$cn = 'list-json-' . $t . "-" . $pg . "-" . $wd . "-" . $h ."-" .$from ."-" .$ty;
	echoPageCache($cp,$cn);

	
	//视频列表开始
	if (maccms_field_vod_source !="") {
		$tempmaccms_field_vod_source = ",".maccms_table_vod.".".maccms_field_vod_source;
	}
	
	$sql = "select d_id,d_name,d_enname,d_type,d_time,d_remarks,d_playfrom,d_addtime from {pre}vod where 1=1 ";
	$sql1 = "select count(*) from {pre}vod where 1=1 ";
	
	if ($t > 0) { $where .= " and d_type=" . $t; }
	if ($MAC['api']['vod']['vodfilter'] != "") { $where .= " ". $MAC['api']['vod']['vodfilter']." "; }
	if ($wd !="") { $where .= " and d_name like '%".$wd."%' "; }
	if ($from!=''){ $where .= " and d_playfrom like '%".$from."%'"; }
	
	
	$sql .= $where. " order by d_time desc";
	$sql1 .= $where;
	
	$nums= $db->getOne($sql1);
	$nums = intval($nums);
	$pagecount=ceil($nums/$MAC['api']['vod']['pagesize']);
	$sql = $sql ." limit ".($MAC['api']['vod']['pagesize'] * ($pg-1)).",".$MAC['api']['vod']['pagesize'];
	$rs = $db->query($sql);	
	if(!$rs){
		$nums=0;
		echo "err";exit;
	}
	$json=[];
	$json['code'] = 1;
	$json['msg'] = '数据列表';
	$json['page'] = $pg;$json['pagecount'] = 0;$json['limit'] = $MAC['api']['vod']['pagesize'];$json['total'] = 0;
	$json['list'] = [];
	$json['class'] = [];
	if($nums==0){
		
	}
	else{
		
		$json['page'] = $pg;$json['pagecount'] = $pagecount;$json['limit'] = $MAC['api']['vod']['pagesize'];$json['total'] = $nums;
		
		while ($row = $db ->fetch_array($rs))
	  	{
            if($GLOBALS['MAC']['app']['filtertags'] != '2') {
                $row = array_map('filter_tags', $row);
            }
	  		$dt = $from!='' ? $from : replaceStr($row["d_playfrom"],'$$$',',');
	  		$typearr = $MAC_CACHE['vodtype'][$row["d_type"]];
			
			$json['list'][] = array(
				'vod_id'=>$row["d_id"],
				'vod_name'=>$row["d_name"],
				'type_id'=>$row["d_type"],
				'type_name'=>$typearr["t_name"],
				'vod_en'=>$row["d_enname"],
				'vod_time'=>date('Y-m-d H:i:s',$row["d_time"]),
				'vod_remarks'=>$row['d_remarks'],
				'vod_play_from'=>$dt,
				);
	  	}
	}
	unset($rs);
	//视频列表结束
	
	//分类列表开始

	$sql = "select * from {pre}vod_type where 1=1 ";
	if ($MAC['api']['vod']['typefilter'] != "") { $sql .= $MAC['api']['vod']['typefilter'] ; }
	$rs = $db->query($sql);
	while ($row = $db ->fetch_array($rs))
	{
        if($GLOBALS['MAC']['app']['filtertags'] != '2') {
            $row = array_map('filter_tags', $row);
        }
		$json['class'][] = array(
			'type_id'=>$row['t_id'],
			'type_name'=>$row['t_name']
			);
	}
	unset($rs);
	//分类列表结束
	$xml = json_encode($json);
	setPageCache($cp,$cn,$xml);
	echo $xml;
}

function urlDeal($urls,$froms,$servers,$notes,$from)
{	
	if($from !=''){
		$urls = replaceStr($urls,chr(10),"#");
		$urls = replaceStr($urls,chr(13),"#");
		$urls = replaceStr($urls,"##","#");
		
		$arr_url = explode("$$$",$urls);
		$arr_from = explode("$$$",$froms);
		$arr_server = explode("$$$",$servers);
		$arr_note = explode("$$$",$notes);
		
		$key = array_search($from,$arr_from);
		
		$froms = $from;
		$urls = $arr_url[$key];
		$servers = $arr_server[$key];
		$notes = $arr_note[$key];
	}
	
	$res=[
		'from'=>$froms,
		'url'=>$urls,
		'server'=>$servers,
		'note'=>$notes,
		];
	return $res;
}
?>