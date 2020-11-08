<?php
if(!defined('MAC_ADMIN')){
	exit('Access Denied');
}
ob_end_clean();
ob_implicit_flush(true);
ini_set('max_execution_time', '0');
$backurl=getReferer();

if($method=='sql')
{
    $_SESSION['__token__'] = md5(getRndStr(16));
    $plt->set_var('__token__',$_SESSION['__token__']);
	$plt->set_file('main', $ac.'_'.$method.'.html');
	
}

elseif($method=='sqlexe')
{
    $token = be('post','__token__');
    if($token != $_SESSION['__token__']){
        showErr('System','token失效请刷新页面重试');
        return;
    }

	$rn='sql';
	$plt->set_file('main', $ac.'_'.$method.'.html');
	
	$sql = be("post","sql");
	if (!empty($sql)){
		$sql= stripslashes($sql);
		if (strtolower(substr($sql,0,6))=="select"){
			$isselect=true;
		}
		else{
			$isselect=false;
		}
		$rs = $db->query($sql);
		$num= $db->affected_rows();
	}
	
	if ($isselect){
		if($num==0){
			$plt->set_if('main','isnull',true);
			return;
		}
		$plt->set_if('main','isnull',false);
		$plt->set_if('main','isselect',true);
		$plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
		$i=0;
		if($rs){
		    while($row=$db->fetch_array($rs)){
				if($i==0){
					$strcol = '';
					foreach($row as $k=>$v){
						$strcol .= '<th><strong>'.$k.'</strong></th>';
					}
					$plt->set_var('rows_'.$rn, '<tr>'.$strcol.'</tr>');
				}
				$s='';
				$one='';
				foreach( $row as $k=>$v){
					$one .= '<td>'.strip_tags($v).'</td>';
				}
				$i++;
		  	  	$plt->set_var('data', '<tr>'.$one.'</tr>');
		  	  	$plt->parse('rows_'.$rn,'list_'.$rn,true);
			}
		}
	}
	else{
		$plt->set_if('main','isnull',false);
		$plt->set_if('main','isselect',false);
		$plt->set_var('count',$num);
	}
}

elseif($method=='datarep')
{
    $_SESSION['__token__'] = md5(getRndStr(16));
    $plt->set_var('__token__',$_SESSION['__token__']);

	$plt->set_file('main', $ac.'_'.$method.'.html');
	
	$colarr=array('v','n');
	$valarr = array();
	$rn='table';
	$plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
	$sql='SHOW TABLES FROM `'.$MAC['db']['name'].'`';
	$rs = $db->query($sql);
	while($row = $db ->fetch_array($rs)){
		$v = $row['Tables_in_'.$MAC['db']['name']];
		
		for($i=0;$i<count($colarr);$i++){
			$n = $colarr[$i];
			if($n=='n'){
				$v .= ' ('.getTabName($v) .')';
			}
			//$v = $valarr[$n];
			$plt->set_var($n, $v );
		}
		$plt->parse('rows_'.$rn,'list_'.$rn,true);
	}
	unset($rs);
	
}


elseif($method=='datarepexe')
{
    $token = be('post','__token__');
    if($token != $_SESSION['__token__']){
        showErr('System','token失效请刷新页面重试');
        return;
    }

	$page = intval($p['pg']);
	if ($page < 1) { $page = 1; }
	
	if($page==1){
		$table = be('post','table');
		$field = be('post','field');
		$findstr = be('post','findstr');
		$tostr = be('post','tostr');
		$where = be('post','where');
	}
	$sql = "UPDATE ".$table." set ".$field."=Replace(".$field.",'".$findstr."','".$tostr."') where 1=1 ". $where;
	$db->query($sql);
	showMsg('批量替换完成!SQL执行语句!<br>'.$sql,"");
}

elseif($method=='list')
{
	$rn='db';
	$plt->set_file('main', $ac.'_'.$method.'.html');
	$plt->set_block('main', 'list_'.$rn, 'rows_'.$rn);
	
	$colarr=array('name','time','size','num');
	$num=0;
	$arr = glob('bak'.'/*.sql');
	
	if(!is_array($arr) || count($arr)==0){
		$plt->set_if('main','isnull',true);
		return;
	}
	
	$plt->set_if('main','isnull',false);
	foreach($arr as $a){
		$num++;
		$tmp = explode("-",$a);
		if(intval($tmp[1])==1){
			$name = str_replace('bak/','',$tmp[0]);
			$time = date( 'Y-m-d H:i:s',filemtime($a) );
			$size = round(filesize($a)/1024);
			$valarr=array($name,$time,$size,$num);
			for($i=0;$i<count($colarr);$i++){
				$n = $colarr[$i];
				$v = $valarr[$i];
				$plt->set_var($n, $v );
			}
			$plt->parse('rows_'.$rn,'list_'.$rn,true);
		}
	}
	
}

elseif($method=='getsize')
{
	$file = $p['file'];
	$fsize=0;
	foreach( glob('bak'.'/*.sql') as $f){
		if(strpos($f,$file)>0){
			$fsize = $fsize + round(filesize($f)/1024);
    	}
	}
	echo $fsize;
}

elseif($method=='compress')
{
	$status = $db->query('OPTIMIZE  TABLE `{pre}art` , `{pre}art_relation` , `{pre}art_topic` , `{pre}art_type` , `{pre}comment` , `{pre}gbook` , `{pre}link` , `{pre}manager` , `{pre}user` , `{pre}user_card` , `{pre}user_group` , `{pre}user_pay` , `{pre}user_visit` , `{pre}vod` , `{pre}vod_relation` , `{pre}vod_topic` , `{pre}vod_type`, `{pre}vod_class`  ');
	if($status){
		showMsg('压缩优化成功','?m=db-list');
	}
	else{
		showMsg('压缩优化失败','?m=db-list');
	}
}

elseif($method=='repair')
{
	$status = $db->query('REPAIR TABLE `{pre}art` , `{pre}art_relation` , `{pre}art_topic` , `{pre}art_type` , `{pre}comment` , `{pre}gbook` , `{pre}link` , `{pre}manager` , `{pre}user` , `{pre}user_card` , `{pre}user_group` , `{pre}user_pay` , `{pre}user_visit` , `{pre}vod` , `{pre}vod_relation` , `{pre}vod_topic` , `{pre}vod_type`, `{pre}vod_class`  ');
	if($status){
		showMsg('修复成功','?m=db-list');
	}
	else{
		showMsg('修复失败','?m=db-list');
	}
}

elseif($method=='del')
{
	$file = $p['file'];
	if(empty($file)){
		$file = be('arr','file');
	}
	$arr = explode(',',$file);
	foreach ($arr as $a){
		foreach( glob('bak'.'/*.sql') as $f){
			if(strpos($f,$a)>0){
				unlink($f);
			}
		}
	}
	unset($arr);
	redirect('?m=db-list');
}

elseif($method=='reduction')
{
	$file = $p['file'];
	$num = $p['num'];
	$fcount = $p['fcount'];
	
	if(!is_numeric($num)){ $num=1;} else{ $num=intval($num); }
	if(!is_numeric($fcount)){ $fcount=-1;} else { $fcount = intval($fcount); }
	if($fcount==-1){
		$fcount=0;
	    foreach( glob('bak/*') as $f){
	    	$f = str_replace('bak/','',$f);
			if(strpos(",".$f,$file)>0){
				$fcount++;
	    	}
		}
	}
	
	if($num>$fcount){
		showMsg ( '数据库还原完毕，请重新登录后更新系统缓存', '?m=db-list' );
	}
    else{
    	
    	for($j=1;$j<=$fcount;$j++){
    		if($j==$num){
				$fpath = 'bak/'.$file . '-'.$j.'.sql';
		    	$sqls = file($fpath);
		    	
				foreach($sqls as $sql)
				{
					$sql = str_replace(chr(10),'',$sql);
					$sql = str_replace(chr(13),'',$sql);
					if (!empty($sql)){
						$db->query(trim($sql));
					}
					unset($sql);
				}
				unset($sqls);
			}
	    }
	    ob_flush();flush();
	    showMsg ( '共有'.$fcount.'个备份分卷文件需要还原，正在还原第'.$num.'个文件...', '?m=db-reduction-num-'.($num+1).'-fcount-'.$fcount.'-file-'.$file );
    }
}

elseif($method=='bak')
{
	$fpath = 'bak/' . date('Ymd',time()) . '_'. getRndStr(10) ;
	$sql='';
	$p=1;
	$tables = ' `{pre}art_relation` , `{pre}art_topic` , `{pre}art_type` , `{pre}comment` , `{pre}gbook` , `{pre}link` , `{pre}user` , `{pre}user_card` , `{pre}user_group` , `{pre}user_pay` , `{pre}user_visit`  , `{pre}vod_relation` , `{pre}vod_topic` , `{pre}vod_type`, `{pre}vod_class` , `{pre}art`, `{pre}vod`';
	$tables = str_replace('{pre}',$GLOBALS['MAC']['db']['tablepre'],$tables);
	$tablearr = explode(',',$tables);
	$pagesize = 800;
	
	foreach( $tablearr as $table ){
		$table = trim($table);
		$sql.= make_header($table);
		
		$i=0;
		$fs=array();
		$res = $db->query("SHOW COLUMNS FROM ".$table);
		
 		while ($row = $db->fetch_array($res)){
 			$fs[]=$row['Field']; 
 		}
		unset($res);
		
		$fsd=count($fs)-1;
		$nums = $db->getOne('select count(*) from '.$table);
		$pagecount = 1;
		if($nums>$pagesize){
			$pagecount = ceil($nums/$pagesize);
		}
		
		for($n=1;$n<=$pagecount;$n++){
			$rsdata = $db->getAll('select * from '.$table.' limit '.($pagesize * ($n-1)).','.$pagesize);
			$rscount = count($rsdata);
			$intable = 'INSERT INTO '.$table.' VALUES(';
			for($j=0;$j<$rscount;$j++){
				$line = $intable;
				for($k=0;$k<=$fsd;$k++){
					if($k < $fsd){
						$line.="'".$GLOBALS['db']->real_escape_string($rsdata[$j][$fs[$k]])."',";
					}
					else{
						$line.="'".$GLOBALS['db']->real_escape_string($rsdata[$j][$fs[$k]])."');\r\n";
					}
				}
				$sql.=$line;
				if(strlen($sql)>= 1500000){
					$fname = $fpath . '-'.$p.'.sql' ;
					fwrite(fopen($fname,'wb'),$sql);
					$p++;
					unset($sql);
				}
			}
			unset($rsdata);
		}
		unset($fs);
	}
	unset($tablearr);
	
	$sql .= make_manager( str_replace('{pre}',$GLOBALS['MAC']['db']['tablepre'],'{pre}manager') );
	$fname = $fpath . '-'.$p.'.sql' ;
	fwrite(fopen($fname,'wb'),$sql)	;
	showMsg('备份成功','?m=db-list');
}

elseif($method=='inspect')
{
    $ck = be('all','ck');
    if(empty($ck)){
        $ck = $p['ck'];
    }
    if($ck!=''){
        echo '<style type="text/css">body{font-size:12px;color: #333333;line-height:21px;}span{font-weight:bold;color:#FF0000}</style>';
        ob_flush();flush();

        $sql = 'select * from information_schema.columns where table_schema =\''.$GLOBALS['MAC']['db']['name'].'\' ';
        $schema = $db->queryArray($sql);

        $col_list = array();
        $sql='';
        $pre = $GLOBALS['MAC']['db']['tablepre'];
        foreach($schema as $k=>$v){
            $col_list[$v['TABLE_NAME']][$v['COLUMN_NAME']] = $v;
        }

        $tables = array('art','art_topic','art_type','comment','gbook','link','user','vod','vod_class','vod_topic','vod_type');
        $cols = array('a','t','t','c','g','l','u','d','c','t','t');

        $tbi = intval($p['tbi']);
        if ($tbi >= count($tables)) {
            echo '清理结束,可以多次执行,以免有漏掉的数据<br>';
            die;
        }

        $check_arr = array('{if-','eval(','func','base64_',"script>");
        $rel_val = array("/\{if-(.*?)endif-(.*?)\}/is","/eval\((.*?)\)/is","/func(.*?)\)/is","/base64_(.*?)\)/is","/<script[\s\S]*?<\/script>/is",);


        foreach ($col_list as $k1 => $v1) {
            $pre_tb = str_replace($pre, '', $k1);
            $si = array_search($pre_tb, $tables);

            if ($pre_tb !== $tables[$tbi]){
                continue;
            }

            echo '开始检测' . $k1 . '表...<br>';
            ob_flush();flush();

            $where = [];
            foreach ($v1 as $k2 => $v2) {
                if (strpos($v2['DATA_TYPE'], 'int') === false) {
                    $where[$k2] = mac_like_arr($k2,join(',', $check_arr));
                }
            }

            if (!empty($where)) {
                $field = array_keys($where);
                $where = array_values($where);
                $field[] = $cols[$si] . '_id';
                $sql = 'select '.join(',',$field) . ' from {pre}' . $tables[$tbi] . ' where ' . join('or',$where);
                $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
                echo $sql.'<br>';
                ob_flush();flush();

                $list = $db->queryArray($sql);

                echo '共检测到' . count($list) . '条危险数据...<br>';
                ob_flush();flush();
                foreach ($list as $k3 => $v3) {
                    $update = [];
                    $col_id = $cols[$si] . '_id';
                    $col_name = $cols[$si] . '_name';
                    $val_id = $v3[$col_id];;
                    $val_name = strip_tags($v3[$col_name]);
                    $ck = false;
                    $where2 = ''.$col_id.'='.$val_id;
                    $field=[];
                    foreach ($v3 as $k4 => $v4) {

                        if ($k4 != $col_id) {
                            $val = $v4;
                            foreach ($check_arr as $kk => $vv) {
                                $val = preg_replace($rel_val[$kk], "", $val);
                            }
                            if ($val !== $v4) {
                                $val = str_replace( array("'","\\"),"",$val);
                                $field[] = $k4;
                                $update[$k4] = $val;
                                $ck = true;
                            }
                        }
                    }
                    if ($ck) {
                        $r = $db->Update('{pre}'.$tables[$tbi],$field,$update,$where2,1);
                        echo $val_id . '、' . $val_name . ' ok<br>';
                        ob_flush();flush();
                    }
                }
            }
        }

        $tbi++;
        jump('?m=db-inspect-ck-1-tbi-'.$tbi,$MAC['app']['maketime']);
        exit;
    }

    $plt->set_file('main', $ac.'_'.$method.'.html');
}

else
{
	showErr('System','未找到指定系统模块');
}
?>