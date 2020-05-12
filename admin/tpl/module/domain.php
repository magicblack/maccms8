<?php
if(!defined('MAC_ADMIN')){
    exit('Access Denied');
}
$config_file = '../inc/config/domain.php';
$domain_list = array();
if(file_exists($config_file)){
    $domain_list = include $config_file;
}

if($method=='export')
{
    $html = '';
    foreach($domain_list as $k=>$v){
        $html .= $v['site_url'].'$'.$v['site_name'].'$'.$v['site_keywords'].'$'.$v['site_description'].'$'.$v['template_dir'].'$'.$v['mob_template_dir'].'$'.$v['html_dir'].'$'.$v['ads_dir']."\n";
    }

    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=mac_domains.txt");
    echo $html;
    die;
}

else if($method=='import')
{
    $data = file_get_contents($_FILES['file1']['tmp_name']);
    if($data){
        $list = explode(chr(10),$data);

        $domain = array();

        foreach($list as $k=>$v){
            if(!empty($v)) {
                $one = explode('$', $v);
                $domain[$one[0]] = array(
                    'site_url' => $one[0],
                    'site_name' => $one[1],
                    'site_keywords' => $one[2],
                    'site_description' => $one[3],
                    'template_dir' => $one[4],
                    'mob_template_dir' => $one[5],
                    'html_dir' => $one[6],
                    'ads_dir'=>$one[7],
                );
            }
        }

        $configstr = '<?php'. chr(10) .'return '.var_export($domain, true).';'. chr(10) .'?>';
        fwrite(fopen($config_file,'wb'),$configstr);
        redirect('?m=domain-index');
    }
}
else {
    $plt->set_file('main', 'domain.html');

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $tmp = $_POST['domain'];
        $domain= array();
        if(!empty($tmp)) {
            foreach ($tmp['site_url'] as $k => $v) {
                $domain[$v] = array(
                    'site_url' => $v,
                    'site_name' => $tmp['site_name'][$k],
                    'site_keywords' => $tmp['site_keywords'][$k],
                    'site_description' => $tmp['site_description'][$k],
                    'template_dir' => $tmp['template_dir'][$k],
                    'mob_template_dir' => $tmp['mob_template_dir'][$k],
                    'html_dir' => $tmp['html_dir'][$k],
                    'ads_dir' => $tmp['ads_dir'][$k],
                );
            }
        }

        $configstr = '<?php'. chr(10) .'return '.var_export($domain, true).';'. chr(10) .'?>';
        fwrite(fopen($config_file,'wb'),$configstr);
        redirect('?m=domain-index');
    }

    $template_html = '';
    $mob_template_html = '';
    foreach( glob('../template'.'/*',GLOB_ONLYDIR) as $v){
        if(is_dir($v)){
            $v = str_replace('../template/','',$v);
            if($v!='user'){
                $template_html .= "<option value='" .$v. "' " .$sed. ">" .$v. "</option>";
            }
        }
    }
    $plt->set_var('template_html',str_replace("'","\'",$template_html));


    $rn='a';
    $plt->set_block('main', 'list_domain', 'rows_'.$rn);
    $n=1;
    foreach($domain_list as $k=>$v){
        $site_url = $v['site_url'];
        $site_name = $v['site_name'];
        $site_keywords = $v['site_keywords'];
        $site_description = $v['site_description'];
        $template_dir = $v['template_dir'];
        $mob_template_dir = $v['mob_template_dir'];
        $html_dir = $v['html_dir'];
        $ads_dir = $v['ads_dir'];

        $template_select = str_replace('<option value=\''.$v['template_dir'].'\' >','<option value=\''.$v['template_dir'].'\' selected>',$template_html);
        $mob_template_select = str_replace('<option value=\''.$v['mob_template_dir'].'\' >','<option value=\''.$v['mob_template_dir'].'\' selected>',$template_html);

        $plt->set_var('n',$n);
        $plt->set_var('site_url',$site_url);
        $plt->set_var('site_name',$site_name);
        $plt->set_var('site_keywords',$site_keywords);
        $plt->set_var('site_description',$site_description);
        $plt->set_var('template_dir',$template_dir);
        $plt->set_var('mob_template_dir',$mob_template_dir);
        $plt->set_var('html_dir',$html_dir);
        $plt->set_var('ads_dir',$ads_dir);
        $plt->set_var('template_select',$template_select);
        $plt->set_var('mob_template_select',$mob_template_select);

        $n++;
        $plt->parse('rows_'.$rn,'list_domain',true);
    }
    if(empty($domain_list)){
        $plt->set_var('rows_'.$rn,'');
    }


    $arr_len = count($domain_list);
    $plt->set_var('arr_len',$arr_len);



}