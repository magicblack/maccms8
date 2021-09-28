<?php
if(!defined('MAC_ADMIN')){
    exit('Access Denied');
}
ob_end_clean();
ob_implicit_flush(true);
ini_set('max_execution_time', '0');
$backurl=getReferer();
$files=[];

function listDir($dir)
{
    global $files;
    if(is_dir($dir)){
        if ($dh = opendir($dir)) {
            while (($file= readdir($dh)) !== false){
                $tmp = str_replace(['//','../'],['/','./'],convert_encoding($dir.$file, "UTF-8", "GB2312"));
                if((is_dir($dir."/".$file)) && $file!="." && $file!=".."){
                    listDir($dir."/".$file."/");
                } else{
                    if($file!="." && $file!=".."){
                        $files[$tmp] = ['md5'=>md5_file($dir.$file)];
                    }
                }
            }
            closedir($dh);
        }
    }
}

if($method=='file')
{
    $ck = be('all','ck');
    $ft = be('arr','ft');
    if(empty($ck)){
        $ck = $p['ck'];
    }
    if(empty($ft)){
        $ft='1,2';
    }
    if($ck!=''){
        echo '<style type="text/css">body{font-size:12px;color: #333333;line-height:21px;}span{font-weight:bold;color:#FF0000}</style>';
        ob_flush();
        flush();

        $arr=explode('/',$_SERVER["SCRIPT_NAME"]);
        $adpath=$arr[count($arr)-2];

        $url = base64_decode("aHR0cDovL3VwZGF0ZS5tYWNjbXMubGEv") . "v8/mac_files_".MAC_VERSION.'.html';
        $html = getPage($url, "utf-8");
        $html = str_replace('.\/admin\/','.\/'.$adpath.'\/',$html);
        $json = json_decode($html,true);
        if(!$json){
            showMsg('获取官方文件数据失败，请重试','');
            exit;
        }

        listDir('../');
        if(!is_array($files)){
            showMsg('获取本地文件列表失败，请重试');
            exit;
        }
        $show_add = strpos($ft,'1');
        $show_edit = strpos($ft,'2');
        foreach($files as $k=>$v){
            $color = '';
            $msg = 'ok';
            if(empty($json[$k]) && $show_add!==false){
                $color = 'BlueViolet';
                $msg = '新增文件';
            }
            elseif(!empty($json[$k]) && $v['md5'] != $json[$k]['md5'] && $show_edit!==false){
                $color = 'red';
                $msg = '与原版有差异';
            }
            if($color!='') {
                //$this->_files[$k]['jc'] = $color;
                echo $k . '---' . "<font color=$color>" . $msg . '</font><br>';
                ob_flush();
                flush();
            }
        }
        exit;
    }
    $plt->set_file('main', $ac.'_'.$method.'.html');
}

elseif($method=='data')
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

        $check_arr = array('{if-',':php',"<script","<iframe");
        $rel_val = array(
            array (
                "/\{if-(.*?)endif-(.*?)\}/is",
            ),
            array (
                "/{maccms:php(.*?)}([\s\S]+?){\/maccms:php}/is",
            ),
            array (
                "/<script[\s\S]*?<\/(.*)>/is",
                "/<script[\s\S]*?>/is",
            ),
            array(
                "/<iframe[\s\S]*?<\/(.*)>/is",
                "/<iframe[\s\S]*?>/is",
            )
        );

        foreach ($col_list as $k1 => $v1) {
            $pre_tb = str_replace($pre, '', $k1);
            $si = array_search($pre_tb, $tables);

            if ($pre_tb !== $tables[$tbi]){
                continue;
            }

            echo '开始检测' . $k1 . '表...<br>';
            ob_flush();flush();

            $where = array();
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
                    $update = array();
                    $col_id = $cols[$si] . '_id';
                    $col_name = $cols[$si] . '_name';
                    $val_id = $v3[$col_id];;
                    $val_name = strip_tags($v3[$col_name]);
                    $ck = false;
                    $where2 = ''.$col_id.'='.$val_id;
                    $field=array();
                    foreach ($v3 as $k4 => $v4) {

                        if ($k4 != $col_id) {
                            $val = $v4;
                            foreach ($check_arr as $kk => $vv) {
                                foreach($rel_val[$kk] as $k5=>$v5){
                                    $val = preg_replace($v5, "", $val);
                                }
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
        jump('?m=safety-data-ck-1-tbi-'.$tbi,$MAC['app']['maketime']);
        exit;
    }

    $plt->set_file('main', $ac.'_'.$method.'.html');
}

else
{
    showErr('System','未找到指定系统模块');
}
?>