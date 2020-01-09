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
	$plt->set_file('main', $ac.'_'.$method.'.html');
	
}

elseif($method=='sqlexe')
{
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
    if($_SERVER['REQUEST_METHOD']=="POST"){

        echo '<style type="text/css">body{font-size:12px;color: #333333;line-height:21px;}span{font-weight:bold;color:#FF0000}</style>';
        ob_flush();flush();

        $check_arr = array('{if-','eval(','func','base64_',"script>");
        $rel_val = array("/\{if-(.*?)endif-(.*?)\}/is","/eval\((.*?)\)/is","/func(.*?)\)/is","/base64_(.*?)\)/is","/<script[\s\S]*?<\/script>/is",);

        echo "<font color='red'>开始检测文章分类表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}art_type';
            $col_id = 't_id';
            $col_name ='t_name';
            $col_arr = array('t_name','t_enname','t_key','t_des','t_title','t_union','t_tpl','t_tpl_art');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }


        echo "<font color='red'>开始检测视频分类表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}vod_type';
            $col_id = 't_id';
            $col_name ='t_name';
            $col_arr = array('t_name','t_enname','t_key','t_des','t_title','t_union','t_tpl','t_tpl_list','t_tpl_vod','t_tpl_play','t_tpl_down');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }


        echo "<font color='red'>开始检测文章专题表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}art_topic';
            $col_id = 't_id';
            $col_name ='t_name';
            $col_arr = array('t_name','t_enname','t_tpl','t_pic','t_content','t_key','t_des','t_title');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }


        echo "<font color='red'>开始检测视频专题表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}vod_topic';
            $col_id = 't_id';
            $col_name ='t_name';
            $col_arr = array('t_name','t_enname','t_tpl','t_pic','t_content','t_key','t_des','t_title');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }


        echo "<font color='red'>开始检测视频扩展分类表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}vod_class';
            $col_id = 'c_id';
            $col_name ='c_name';
            $col_arr = array('c_name');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }


        echo "<font color='red'>开始检测评论表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}comment';
            $col_id = 'c_id';
            $col_name ='c_name';
            $col_arr = array('c_name','c_ip','c_content');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }



        echo "<font color='red'>开始检测留言表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}gbook';
            $col_id = 'g_id';
            $col_name ='g_name';
            $col_arr = array('g_name','g_content','g_reply');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }


        echo "<font color='red'>开始检测友情链接表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}link';
            $col_id = 'l_id';
            $col_name ='l_name';
            $col_arr = array('l_name','l_url','l_logo');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }

        echo "<font color='red'>开始检测用户表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}user';
            $col_id = 'u_id';
            $col_name ='u_name';
            $col_arr = array('u_name','u_qid','u_password','u_qq','u_email','u_phone','u_question','u_answer','u_random','u_fav','u_plays');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            echo $sql.'<br>';
            $rs = $db->queryArray($sql);
            if(!$rs){
                echo '未发现<br>';
                continue;
            }
            foreach ($rs as $k2=>$v2){
                $id = $v2[$col_id];
                $name = $v2[$col_name];
                
                foreach($col_arr as $b){
                    $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                    $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                }
                
                $where = "$col_id=".$id;
                unset($v2[$col_id]);
                
                $db->Update($tab,$col_arr,$v2,$where,1);
                echo $id.'、'. $name .' ok<br>';
                ob_flush();flush();
            }
        }

        echo "<font color='red'>开始检测文章表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}art';
            $col_id = 'a_id';
            $col_name ='a_name';
            $col_arr = array('a_name','a_subname','a_enname','a_from','a_author','a_tag','a_pic','a_topic','a_remarks','a_content');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $sql2 = "select count(*) from $tab where ";
            
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; $sql2 .=' or '; }
                $sql .= $b." like '%" . $v1 ."%'";
                $sql2 .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql .= 'limit 1000';
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            $sql2 = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql2);
            
            $cc = $db->getOne($sql2);
            $ps = 1000;
            $pc = ceil($cc / $ps);
            
            echo $sql.'<br>';
            echo '共查询到'.$cc.'条数据,分'.$pc.'次处理<br>';
            ob_flush();flush();
                  
            for($n=0;$n<$pc;$n++){
            
              $rs = $db->queryArray($sql);
              if(!$rs){
                  echo '未发现<br>';
                  continue;
              }
              foreach ($rs as $k2=>$v2){
                  $id = $v2[$col_id];
                  $name = $v2[$col_name];
                  foreach($col_arr as $b){
                      $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                      $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                  }
                  $where = "$col_id=".$id;
                  unset($v2[$col_id]);
                  $db->Update($tab,$col_arr,$v2,$where,1);
                  echo $id.'、'. $name .' ok<br>';
                  ob_flush();flush();
              }
            }
        }


        echo "<font color='red'>开始检测视频表...</font><br>";
        foreach($check_arr as $k1=>$v1){
            $tab='{pre}vod';
            $col_id = 'd_id';
            $col_name ='d_name';
            $col_arr = array('d_name','d_subname','d_enname','d_pic','d_picthumb','d_picslide','d_starring','d_directed','d_tag','d_remarks','d_area','d_lang','d_type_expand','d_class','d_duration','d_content','d_playfrom','d_playserver','d_playnote','d_playurl','d_downfrom','d_downserver','d_downnote','d_downurl');
            $col_str = join(',',$col_arr);

            $sql= "select $col_id,$col_str from $tab where ";
            $sql2 = "select count(*) from $tab where ";
            
            $rc=false;
            foreach($col_arr as $b){
                if($rc){ $sql.=' or '; $sql2 .=' or ';  }
                $sql .= $b." like '%" . $v1 ."%'";
                $sql2 .= $b." like '%" . $v1 ."%'";
                $rc=true;
            }
            $sql .= 'limit 1000';
            $sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
            $sql2 = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql2);
            
            $cc = $db->getOne($sql2);
            $ps = 1000;
            $pc = ceil($cc / $ps);
            
            echo $sql.'<br>';
            echo '共查询到'.$cc.'条数据,分'.$pc.'次处理<br>';
            ob_flush();flush();
            
            for($n=0;$n<$pc;$n++){
           
              $rs = $db->queryArray($sql);
              if(!$rs){
                  echo '未发现<br>';
                  continue;
              }
              foreach ($rs as $k2=>$v2){
                  $id = $v2[$col_id];
                  $name = $v2[$col_name];
                  foreach($col_arr as $b){
                      $v2[$b] = preg_replace( $rel_val[$k1],"",$v2[$b]);
                      $v2[$b] = str_replace( array("'","\\"),"",$v2[$b]);
                  }
                  $where = "$col_id=".$id;
                  unset($v2[$col_id]);
                  $db->Update($tab,$col_arr,$v2,$where,1);
                  echo $id.'、'. $name .' ok<br>';
                  ob_flush();flush();
              }
            }
        }

        echo '清理结束。请再次执行，以免有漏掉的数据<br>';
        ob_flush();flush();

        die;

    }
    $plt->set_file('main', $ac.'_'.$method.'.html');
}

else
{
	showErr('System','未找到指定系统模块');
}
?>