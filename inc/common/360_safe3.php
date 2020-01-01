<?php
function arr_foreach($arr)
{
	static $str;
	if (!is_array($arr)) { return $arr;	}
	foreach ($arr as $key => $val ) {
		if (is_array($val)) { arr_foreach($val); } else { $str[] = $val; }
	}
	return implode($str);
}

function chkShow()
{
    $errmsg = "您的提交带有不合法参数,谢谢合作!,操作IP: ".$_SERVER["REMOTE_ADDR"].",操作时间: ".strftime("%Y-%m-%d %H:%M:%S").",操作页面:".$_SERVER["PHP_SELF"].",提交方式: ".$_SERVER["REQUEST_METHOD"];
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {

    }
    else{
        $errmsg = "<div style=\"position:fixed;top:0px;width:100%;height:100%;background-color:white;color:green;font-weight:bold;border-bottom:5px solid #999;\"><br>" . $errmsg ."</div>";
    }
    print $errmsg;
    exit();
}
function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
{
	$StrFiltValue=arr_foreach($StrFiltValue);
	$StrFiltValue=urldecode($StrFiltValue);
	
	if(preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
        chkShow();
	}
	if(preg_match("/".$ArrFiltReq."/is",$StrFiltKey)==1){
        chkShow();
	}
}
function chkSql($s)
{
	global $getfilter,$postfilter,$cookiefilter;
	if(empty($s)){
		return "";
	}
	$s = htmlEncode(htmlspecialchars(urldecode(trim($s))));
    StopAttack(1,$s,$postfilter);
	return $s;
}

function slog($logs)
{
	$ymd = date('Y-m-d-H');
	$now = date('Y-m-d H:i:s');
	$toppath = MAC_ROOT ."/log/$ymd.txt";
	$ts = @fopen($toppath,"a+");
	@fputs($ts, $now .' '. $logs ."\r\n");
	@fclose($ts);
}

$referer=empty($_SERVER['HTTP_REFERER']) ? array() : array($_SERVER['HTTP_REFERER']);

//get拦截规则
$getfilter = "\\<.+javascript:window\\[.{1}\\\\x|<.*=(&#\\d+?;?)+?>|<.*(data|src)=data:text\\/html.*>|\\b(alert\\(|be\\(|eval\\(|confirm\\(|expression\\(|prompt\\(|benchmark\s*?\(.*\)|sleep\s*?\(.*\)|load_file\s*?\\()|<[a-z]+?\\b[^>]*?\\bon([a-z]{4,})\s*?=|^\\+\\/v(8|9)|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT(\\(.+\\)|\\s+?.+?)|UPDATE(\\(.+\\)|\\s+?.+?)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)(\\(.+\\)|\\s+?.+?\\s+?)FROM(\\(.+\\)|\\s+?.+?)|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|UNION([\s\S]*?)SELECT|SELECT|UPDATE|get|post|request|cookie|server|eval|assert|fputs|fopen|global|chr|strtr|pack|system|gzuncompress|shell|base64|file|proc|preg|call|ini|php|print|\\{|\\}|\\(|\\\|\\)";
//post拦截规则
$postfilter = "<.*=(&#\\d+?;?)+?>|<.*data=data:text\\/html.*>|\\b(alert\\(|be\\(|eval\\(|confirm\\(|expression\\(|prompt\\(|benchmark\s*?\(.*\)|sleep\s*?\(.*\)|load_file\s*?\\()|<[^>]*?\\b(onerror|onmousemove|onload|onclick|onmouseover|eval)\\b|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT(\\(.+\\)|\\s+?.+?)|UPDATE(\\(.+\\)|\\s+?.+?)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)(\\(.+\\)|\\s+?.+?\\s+?)FROM(\\(.+\\)|\\s+?.+?)|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|UNION([\s\S]*?)SELECT|SELECT|UPDATE|get|post|request|cookie|server|eval|assert|fputs|fopen|global|chr|strtr|pack|system|gzuncompress|shell|base64|file|proc|preg|call|ini|php|print|\\{|\\}|\\(|\\\|\\)";
//cookie拦截规则
$cookiefilter = "benchmark\s*?\(.*\)|sleep\s*?\(.*\)|be\\(|eval\\(|load_file\s*?\\(|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT(\\(.+\\)|\\s+?.+?)|UPDATE(\\(.+\\)|\\s+?.+?)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)(\\(.+\\)|\\s+?.+?\\s+?)FROM(\\(.+\\)|\\s+?.+?)|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|UNION([\s\S]*?)SELECT|SELECT|UPDATE";


//访问日志记录
//foreach($_GET as $k=>$v){ $getData .= $k.'='.$v.'&'; }
//foreach($_POST as $k=>$v){ $postData .= $k.'='.$v.'&'; }
//foreach($_COOKIE as $k=>$v){ $cookieData .= $k.'='.$v.'&'; }
//$log = $_SERVER['PHP_SELF'] . '---get:' .$getData .'---post:' . $postData ;
//slog($log);


foreach($_GET as $key=>$value){
    if(strlen($value)>52500){
        chkShow();
    }
    $value = preg_replace('#[^\x{4e00}-\x{9fa5}A-Za-z0-9-._]#u','',$value);
   	$_GET[$key] = $value;
	StopAttack($key,$value,$getfilter);
}
foreach($_POST as $key=>$value){
    if(strlen($value)>52500){
        chkShow();
    }
    $value = preg_replace('#[^\x{4e00}-\x{9fa5}A-Za-z0-9-._]#u','',$value);
   	$_POST[$key] = $value;
	StopAttack($key,$value,$postfilter);
}
foreach($_COOKIE as $key=>$value){
	StopAttack($key,$value,$cookiefilter);
}
foreach($referer as $key=>$value){
	//StopAttack($key,$value,$postfilter);
}

?>