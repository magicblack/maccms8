<?php
@session_start();
@header('Content-Type:text/html;Charset=utf-8');
@date_default_timezone_set('Etc/GMT-8');

define('MAC_ROOT', substr(__FILE__, 0, -13));
require(MAC_ROOT.'/inc/config/config.php');
require(MAC_ROOT.'/inc/config/cache.php');
require(MAC_ROOT.'/inc/common/class.php');
require(MAC_ROOT.'/inc/common/function.php');
require(MAC_ROOT.'/inc/common/template.php');
require(MAC_ROOT."/inc/common/template_diy.php");

@ini_set('display_errors','On');
@ini_set('pcre.backtrack_limit', 99999);
@error_reporting(7);
@set_error_handler('my_error_handler');
@ob_start();

define('MAC_STARTTIME',execTime());
define('MAC_URL','http://www.maccms.com/');
define('MAC_NAME','苹果CMS');
define('MAC_PATH', $MAC['site']['installdir']);

$isMobile = 0;
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|meizu|cldc|midp|iphone|wap|mobile|android)/i";
if((preg_match($uachar, $ua))) {
    $isMobile = 1;
}

$isDomain=0;

$domain = @include MAC_ROOT .'/inc/config/domain.php';
if( is_array($domain) && !empty($domain[$_SERVER['HTTP_HOST']])){
    $one = $domain[$_SERVER['HTTP_HOST']];
    $MAC['site']['name'] = $one['site_name'];
    $MAC['site']['url'] = $one['site_url'];
    $MAC['site']['keywords'] = $one['site_keywords'];
    $MAC['site']['description'] = $one['site_description'];
    $MAC['site']['templatedir'] = $one['template_dir'];
    $MAC['site']['htmldir'] = $one['html_dir'];
    $MAC['site']['adsdir'] = $one['ads_dir'];

    $isDomain=1;
    if(empty($one['mob_template_dir']) || $one['mob_template_dir'] =='no'){
        $MAC['site']['mobtemplatedir'] = $one['template_dir'];
    }
    else{
        $MAC['site']['mobtemplatedir'] = $one['mob_template_dir'];
    }
    $MAC['site']['mobhtmldir'] = $one['html_dir'];
    $MAC['site']['mobadsdir'] = $one['ads_dir'];

}

$TMP_ISWAP = 0;
$TMP_TEMPLATEDIR = $MAC['site']['templatedir'];
$TMP_HTMLDIR = $MAC['site']['htmldir'];

if($MAC['site']['mobstatus']==1 && $isMobile){
    $TMP_ISWAP = 1;
    $TMP_TEMPLATEDIR = $MAC['site']['mobtemplatedir'];
    $TMP_HTMLDIR = $MAC['site']['mobhtmldir'];
}

define('MAC_MOB', $TMP_ISWAP);
define('MAC_ROOT_TEMPLATE', MAC_ROOT.'/template/'.$TMP_TEMPLATEDIR.'/'. $TMP_HTMLDIR .'/');
define('MAC_PATH_TEMPLATE', MAC_PATH.'template/'.$TMP_TEMPLATEDIR.'/');
define('MAC_PATH_TPL', MAC_PATH_TEMPLATE. $TMP_HTMLDIR  .'/');
define('MAC_PATH_ADS', MAC_PATH_TEMPLATE.$MAC['site']['adsdir'] .'/');
?>