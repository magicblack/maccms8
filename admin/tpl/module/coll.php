<?php
if(!defined('MAC_ADMIN')){
	exit('Access Denied');
}
$col_lect=array('c_id','c_name','c_url','c_type','c_mid','c_appid','c_appkey','c_param');

if($method=='list')
{
	$plt->set_file('main', $ac.'_'.$method.'.html');
	$page = intval($p['pg']);
	if ($page < 1) { $page = 1; }

    $where='';
	$sql = 'SELECT count(*) FROM {pre}collect where 1=1 '.$where;
	$nums = $db->getOne($sql);
	$pagecount=ceil($nums/$MAC['app']['pagesize']);
	$sql = "SELECT * FROM {pre}collect where 1=1 ";
	$sql .= $where;
	$sql .= " ORDER BY c_id DESC limit ".($MAC['app']['pagesize'] * ($page-1)) .",".$MAC['app']['pagesize'];
	$rs = $db->query($sql);
	
	if($nums==0){
		$plt->set_if('main','isnull',true);
		return;
	}
	$plt->set_if('main','isnull',false);

	$colarr=$col_lect;
    array_push($colarr,'c_flag','c_acx','c_type_text','c_mid_text','c_param_encode');

	$rn='collect';
	$plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
	while ($row = $db ->fetch_array($rs))
	{
		$valarr=array();
        for($i=0;$i<count($colarr);$i++){
            $n=$colarr[$i];
            $valarr[$n]=$row[$n];
        }
        $valarr['c_acx'] = $row['c_type']==1 ? '' : 'json';

        $valarr['c_type_text'] = $row['c_type']==1 ? 'xml' : 'json';
        $valarr['c_mid_text'] = $row['c_mid']==1 ? '视频' : '文章';
        $valarr['c_flag'] = md5($row['c_url']);
        $valarr['c_param_encode'] = base64_encode($row['c_param']);

		for($i=0;$i<count($colarr);$i++){
			$n = $colarr[$i];
			$v = $valarr[$n];
			$plt->set_var($n, $v );
		}
		
		$plt->parse('rows_'.$rn,'list_'.$rn,true);
	}
	unset($rs);
	$pageurl = '?m=coll-list-pg-{pg}';
	$pages = '共'.$nums.'条数据&nbsp;当前:'.$page.'/'.$pagecount.'页&nbsp;'.pageshow($page,$pagecount,3,$pageurl,'pagego(\''.$pageurl.'\','.$pagecount.')');
	$plt->set_var('pages', $pages );
}

elseif($method=='info')
{
	$plt->set_file('main', $ac.'_'.$method.'.html');
	$id=$p['id'];
	$flag=empty($id) ? 'add' : 'edit';
	$backurl=getReferer();
	
	$colarr=$col_lect;
	array_push($colarr,'flag','backurl');
	
	if($flag=='edit'){
		$row=$db->getRow('select * from {pre}collect where c_id='.$id);
		if($row){
			$valarr=array();
			for($i=0;$i<count($colarr);$i++){
				$n=$colarr[$i];
				$valarr[$n]=$row[$n];
			}
		}
		unset($row);
	}
	
	$valarr['flag']=$flag;
	$valarr['backurl']=$backurl;

	for($i=0;$i<count($colarr);$i++){
		$n = $colarr[$i];
		$v = $valarr[$n];
		$plt->set_var($n, $v );
	}

    $arr=array(
        array('a'=>'type','c'=>$valarr['c_type'],'t'=>0,'n'=>array('xml','json'),'v'=>array(1,2)),
        array('a'=>'mid','c'=>$valarr['c_mid'],'t'=>0,'n'=>array('视频','文章'),'v'=>array(1,2)),
    );
    foreach($arr as $a){
        $colarr=$a['n'];
        $valarr=$a['v'];
        $rn=$a['a'];
        $cv=$a['t']==0 ?'checked':'selected';

        $plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
        for($i=0;$i<count($colarr);$i++){
            $n = $colarr[$i];
            $v = $valarr[$i];

            if($a['l']=='chk'){
                $c = strpos(",".$a['c'],$v) ? $cv: '';
            }
            else{
                $c = $a['c']==$v ? $cv: '';
            }
            $plt->set_var('v', $v );
            $plt->set_var('n', $n );
            $plt->set_var('c', $c );
            $plt->parse('rows_'.$rn,'list_'.$rn,true);
        }
    }

    unset($colarr);
    unset($valarr);

}

else
{
	showErr('System','未找到指定系统模块');
}
?>