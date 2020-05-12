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

$ver = '2017.10.17.11.07';
$ac = be("get","ac");
$t = intval(be("get","t"));
$pg = intval(be("get","pg"));
$h= intval(be("get","h"));
$wd = be("get","wd"); $wd = chkSql($wd);
$ids= be("all","ids"); $ids = chkSql($ids);
if ($pg < 1){ $pg=1;}
$from =''; //不为空时只显示某种资源例如youku

if($ac=='videolist')
{
	$cp = 'api';
	$cn = 'videolist' . $t . "-" . $pg . "-" . $wd . "-" . $h ."-".$from. "-" . str_replace(",","",$ids); ;
	echoPageCache($cp,$cn);
	
	$xmla = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xmla .= "<rss version=\"5.1\">";
	
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
		$todaydate = date('Y-m-d',strtotime('+1 days'));
		$tommdate = date('Y-m-d',strtotime('-'.$h.' hours')); 
		
		$todayunix = strtotime($todaydate);
		$tommunix = strtotime($tommdate);
		$whereStr = ' AND d_time>= '. $tommunix . ' AND d_time<'. $todayunix;
		$sql .=  $whereStr;
		$sql1 .= $whereStr;
	}
	if ($MAC['api']['vod']['vodfilter'] != "") { 
		$sql .= " ". $MAC['api']['vod']['vodfilter']." "; 
		$sql1 .= " ". $MAC['api']['vod']['vodfilter']." "; 
	}
	if ($from!=''){ 
		$sql .= " and d_playfrom like '%".$from."%'"; 
		$sql1 .= " and d_playfrom like '%".$from."%'"; 
	}
	
	$nums = $db->getOne($sql1);
	$pagecount=ceil($nums/$MAC['api']['vod']['pagesize']);
	$sql = $sql ." limit ".($MAC['api']['vod']['pagesize'] * ($pg-1)).",".$MAC['api']['vod']['pagesize'];
	$rs = $db->query($sql);
	if (!$rs){
		echo "err：" . "<br>" .$sql;exit;
	}
	else{
		$xml .= "<list page=\"".$pg."\" pagecount=\"".$pagecount."\" pagesize=\"".$MAC['api']['vod']['pagesize']."\" recordcount=\"".$nums."\">";
		
		while ($row = $db ->fetch_array($rs))
		{
            if($GLOBALS['MAC']['app']['filtertags'] != '2') {
                $row = array_map('filter_tags', $row);
            }
			$tempurl = urlDeal($row["d_playurl"],$row["d_playfrom"],$from);
		    if(substr($row["d_pic"],0,4)=="http"){ $temppic = $row["d_pic"]; } else { $temppic = $MAC['api']['vod']['imgurl'] . $row["d_pic"]; }
		    
		    $typearr =  $MAC_CACHE['vodtype'][$row["d_type"]];
		    $xml .= "<video>";
		    $xml .= "<last>". date('Y-m-d H:i:s',$row["d_time"]) ."</last>";
			$xml .= "<id>".$row["d_id"]."</id>";
			$xml .= "<tid>".$row["d_type"]."</tid>";
			$xml .= "<name><![CDATA[".$row["d_name"]."]]></name>";
			$xml .= "<type>".$typearr["t_name"]."</type>";
			$xml .= "<pic>".$temppic."</pic>";
			$xml .= "<lang>".$row["d_lang"]."</lang>";
			$xml .= "<area>".$row["d_area"]."</area>";
			$xml .= "<year>".$row["d_year"]."</year>";
			$xml .= "<state>".$row["d_state"]."</state>";
			$xml .= "<note><![CDATA[".$row["d_remarks"]."]]></note>";
			$xml .= "<actor><![CDATA[".$row["d_starring"]."]]></actor>";
			$xml .= "<director><![CDATA[".$row["d_directed"]."]]></director>";
			$xml .= "<dl>".$tempurl."</dl>";
			$xml .= "<des><![CDATA[".$row["d_content"]."]]></des>";
			$xml .= "</video>";
		}
		$xml .= "</list>";
	}
	unset($rs);
	$xmla .= $xml . "</rss>";
	setPageCache($cp,$cn,$xmla);
	echo $xmla;
}

else
{
	$cp = 'api';
	$cn = 'list' . $t . "-" . $pg . "-" . $wd . "-" . $h ."-" .$from;
	echoPageCache($cp,$cn);
	
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= "<rss version=\"5.1\">";
	
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
	
	$nums= $db -> getOne($sql1);
	$pagecount=ceil($nums/$MAC['api']['vod']['pagesize']);
	$sql = $sql ." limit ".($MAC['api']['vod']['pagesize'] * ($pg-1)).",".$MAC['api']['vod']['pagesize'];
	$rs = $db->query($sql);	
	if (!$rs){
		$nums=0;
		echo "err：" . "<br>" .$sql;exit;
	}
	
	if($nums==0){
		$xml .= "<list page=\"".$pg."\" pagecount=\"0\" pagesize=\"".$MAC['api']['vod']['pagesize']."\" recordcount=\"0\">";
	}
	else{
		$xml .= "<list page=\"".$pg."\" pagecount=\"".$pagecount."\" pagesize=\"".$MAC['api']['vod']['pagesize']."\" recordcount=\"".$nums."\">";
		
		while ($row = $db ->fetch_array($rs))
	  	{
            if($GLOBALS['MAC']['app']['filtertags'] != '2') {
                $row = array_map('filter_tags', $row);
            }
	  		$typearr = $MAC_CACHE['vodtype'][$row["d_type"]];
			$xml .= "<video>";
			$xml .= "<last>".date('Y-m-d H:i:s',$row["d_time"])."</last>";
			$xml .= "<id>".$row["d_id"]."</id>";
			$xml .= "<tid>".$row["d_type"]."</tid>";
			$xml .= "<name><![CDATA[".$row["d_name"]."]]></name>";
			$xml .= "<type>".$typearr["t_name"]."</type>";
			if ($from!=''){
				$xml .= "<dt>".$from."</dt>";
			}
			else{
				$xml .= "<dt>".replaceStr($row["d_playfrom"],'$$$',',')."</dt>";
			}
			
			$xml .= "<note><![CDATA[".$row["d_remarks"]."]]></note>";
			$xml .= "</video>";
	  	}
	}
	unset($rs);
	$xml .= "</list>";
	//视频列表结束
	
	//分类列表开始
	$xml .= "<class>";
	$sql = "select * from {pre}vod_type where 1=1 ";
	if ($MAC['api']['vod']['typefilter'] != "") { $sql .= $MAC['api']['vod']['typefilter'] ; }
	$rs = $db->query($sql);
	while ($row = $db ->fetch_array($rs))
	{
        if($GLOBALS['MAC']['app']['filtertags'] != '2') {
            $row = array_map('filter_tags', $row);
        }
		$xml .= "<ty id=\"". $row["t_id"] ."\">". $row["t_name"] . "</ty>";
	}
	unset($rs);
	$xml .= "</class>";
	//分类列表结束
	$xml .= "</rss>";
	setPageCache($cp,$cn,$xml);
	echo $xml;
}

function urlDeal($urls,$froms,$from)
{
	$arr1 = explode("$$$",$urls); $arr1count = count($arr1);
	$arr2 = explode("$$$",$froms); $arr2count = count($arr2);
	for ($i=0;$i<$arr2count;$i++){
		if ($arr1count >= $i){
			if($from!=''){
				if($arr2[$i]==$from){
					$str = $str . "<dd flag=\"". $arr2[$i] ."\"><![CDATA[" . $arr1[$i]. "]]></dd>";
				}
			}
			else{
				$str = $str . "<dd flag=\"". $arr2[$i] ."\"><![CDATA[" . $arr1[$i]. "]]></dd>";
			}
		}
	}
	$str = replaceStr($str,chr(10),"#");
	$str = replaceStr($str,chr(13),"#");
	$str = replaceStr($str,"##","#");
	return $str;
}
?>