<?php
require("conn.php");
require(MAC_ROOT.'/inc/common/360_safe3.php');

if($MAC['api']['art']['status']==0){ echo "closed"; exit; }
if($MAC['api']['art']['charge'] == 1) {
	$h = $_SERVER['REMOTE_ADDR'];
	if (!$h) {
		echo '域名未授权！请联系QQ：'.$MAC['site']['qq'];
		exit;
	}
	else {
		$auth = $MAC['api']['art']['auth'];
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


if($ac=='detail')
{
	$cp = 'api';
	$cn = 'artdetail-json-' . $t . "-" . $pg . "-" . $wd . "-" . $h . "-" . $from ."-" .$ty . "-". str_replace(",","",$ids); ;
	echoPageCache($cp,$cn);
	
	$sql = "select * from {pre}art where 1=1 ";
	$sql1 = "select count(*) from {pre}art where 1=1 ";
	
	if(!empty($ids)){
		$arr = explode(',',$ids);
		for($i=0;$i<count($arr);$i++){
			$arr[$i] = intval($arr[$i]);
		}
		$ids = join(',',$arr);
		unset($arr);
		$sql .= " AND a_id in (". $ids .")";
		$sql1 .= " AND a_id in (". $ids .")";
	}
	if($t>0){
		$sql .= " AND a_type =".$t;
		$sql1 .= " AND a_type =".$t;
	}
	if($h>0){
		$todaydate = date('Y-m-d');
		$tommdate = date('Y-m-d',strtotime('-'.$h.' hours')); 
		
		$todayunix = strtotime($todaydate);
		$tommunix = strtotime($tommdate);
		$whereStr = ' AND a_time>= '. $tommunix . ' AND a_time<='. $todayunix;
		
		$sql .=  $whereStr;
		$sql1 .= $whereStr;
	}
	if ($MAC['api']['art']['artfilter'] != "") { 
		$sql .= " ". $MAC['api']['art']['artfilter']." "; 
		$sql1 .= " ". $MAC['api']['art']['artfilter']." "; 
	}
	
	$nums = $db->getOne($sql1);
	$nums = intval($nums);
	$pagecount=ceil($nums/$MAC['api']['art']['pagesize']);
	$sql = $sql ." limit ".($MAC['api']['art']['pagesize'] * ($pg-1)).",".$MAC['api']['art']['pagesize'];
	$rs = $db->query($sql);
	
	$json = [];
	$json['code'] = 1;
	$json['msg'] = '数据列表';
	$json['page'] = $pg;$json['pagecount'] = 0;$json['limit'] = $MAC['api']['art']['pagesize'];$json['total'] = 0;
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
		    if(substr($row["a_pic"],0,4)=="http"){ $temppic = $row["a_pic"]; } else { $temppic = $MAC['api']['art']['imgurl'] . $row["a_pic"]; }
		    
		    $typearr =  $MAC_CACHE['arttype'][$row["a_type"]];
		    
			
			$json['list'][] = array(
				'art_time'=>date('Y-m-d H:i:s',$row["a_time"]),
				'art_id'=>$row["a_id"],
				'art_name'=>$row["a_name"],
				'art_enname'=>$row['a_enname'],
				'art_subname'=>$row['a_subname'],
				'art_letter'=>$row['a_letter'],
				'art_color'=>$row['a_color'],
				'art_tag'=>$row['a_tag'],
				
				'type_id'=>$row["a_type"],
				'type_name'=>$typearr["t_name"],
				'art_pic'=>$temppic,
					
				'art_from'=>$row["a_from"],
				'art_author'=>$row["a_author"],
				'art_remark'=>$row["a_remarks"],
					
						'art_lock'=>$row["a_lock"],
						'art_level'=>$row["a_level"],
						'art_hits'=>$row["a_hits"],
						'art_hits_day'=>$row["a_dayhits"],
						'art_hits_week'=>$row["a_weekhits"],
						'art_hits_month'=>$row["a_monthhits"],
						'art_up'=>$row["a_up"],
						'art_down'=>$row["a_down"],
						
						'art_content'=> str_replace('[art:page]','$$$',$row["a_content"]),
						
					
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
	$cn = 'artlist-json-' . $t . "-" . $pg . "-" . $wd . "-" . $h ."-" .$from ."-" .$ty;
	echoPageCache($cp,$cn);

	
	//视频列表开始
	if (maccms_fiela_art_source !="") {
		$tempmaccms_fiela_art_source = ",".maccms_table_art.".".maccms_fiela_art_source;
	}
	
	$sql = "select a_id,a_name,a_enname,a_type,a_time,a_author,a_from,a_remarks from {pre}art where 1=1 ";
	$sql1 = "select count(*) from {pre}art where 1=1 ";
	
	if ($t > 0) { $where .= " and a_type=" . $t; }
	if ($MAC['api']['art']['artfilter'] != "") { $where .= " ". $MAC['api']['art']['artfilter']." "; }
	if ($wd !="") { $where .= " and a_name like '%".$wd."%' "; }
	
	
	$sql .= $where. " order by a_time desc";
	$sql1 .= $where;
	
	$nums= $db->getOne($sql1);
	$nums = intval($nums);
	$pagecount=ceil($nums/$MAC['api']['art']['pagesize']);
	$sql = $sql ." limit ".($MAC['api']['art']['pagesize'] * ($pg-1)).",".$MAC['api']['art']['pagesize'];
	$rs = $db->query($sql);	
	if(!$rs){
		$nums=0;
		echo "err";exit;
	}
	$json=[];
	$json['code'] = 1;
	$json['msg'] = '数据列表';
	$json['page'] = $pg;$json['pagecount'] = 0;$json['limit'] = $MAC['api']['art']['pagesize'];$json['total'] = 0;
	$json['list'] = [];
	$json['class'] = [];
	if($nums==0){
		
	}
	else{
		
		$json['page'] = $pg;$json['pagecount'] = $pagecount;$json['limit'] = $MAC['api']['art']['pagesize'];$json['total'] = $nums;
		
		while ($row = $db ->fetch_array($rs))
	  	{
            if($GLOBALS['MAC']['app']['filtertags'] != '2') {
                $row = array_map('filter_tags', $row);
            }
	  		$dt = $from!='' ? $from : replaceStr($row["a_playfrom"],'$$$',',');
	  		$typearr = $MAC_CACHE['arttype'][$row["a_type"]];
			
			$json['list'][] = array(
				'art_id'=>$row["a_id"],
				'art_name'=>$row["a_name"],
				'type_id'=>$row["a_type"],
				'type_name'=>$typearr["t_name"],
				'art_en'=>$row["a_enname"],
				'art_time'=>date('Y-m-d H:i:s',$row["a_time"]),
				'art_author'=>$row['a_author'],
				'art_from'=>$row['a_from'],
				'art_remarks'=>$row['a_remarks'],
				);
	  	}
	}
	unset($rs);
	//视频列表结束
	
	//分类列表开始

	$sql = "select * from {pre}art_type where 1=1 ";
	if ($MAC['api']['art']['typefilter'] != "") { $sql .= $MAC['api']['art']['typefilter'] ; }
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