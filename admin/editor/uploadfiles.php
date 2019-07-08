<?php
require(dirname(__FILE__) .'/../admin_conn.php');
chkLogin();
$action=be("get","action");
$id=be("get","id");
$path=be("get","path");
$tm=time();
$fpah='../../upload/files/';
if(!file_exists($fpah)){ mkdir($fpah); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title>附件上传 - 苹果CMS</title>
<link rel="stylesheet" type="text/css" href="../tpl/images/style.css" />
<link rel="stylesheet" type="text/css" href="../../images/jq/jquery.uploadify.css" />
<script type="text/javascript" src="../../js/jquery.js"></script>
<script type="text/javascript" src="../../js/jq/jquery.cookie.js"></script>
<script type="text/javascript" src="../../js/jq/jquery.uploadify.js?<?php echo $tm;?>"></script>
<script type="text/javascript" src="../tpl/js/adm.js"></script>
<script language="javascript">
$(function(){
	$('#file_upload').uploadify({
		'swf'      : '../../js/uploadify.swf',
		'uploader' : 'upload.php?action=<?php echo $action;?>&id=<?php echo $id;?>&path=<?php echo $path;?>',
		'formData' : {"SESSION_ID":'<?php echo session_id()?>'},
		'multi': true,
		'fileTypeExts': '<?php echo $MAC["upload"]["filter"]?>',
		'fileTypeDesc': '请选择文件',
		'simUploadLimit' :1,
		'uploadLimit' :10,
		'buttonText' :'请选择文件',
		'onSelectError':function (file, errorCode, errorMsg){
			switch(errorCode){
				case -100:
					alert('任务数量超出队列限制');
					break;
				case -110:
					alert('文件大小超出限制');
					break;
				case -120:
					alert('文件大小为0');
					break;
				case -130:
					alert('文件类型不符合要求，支持的文件类型有【<?php echo $MAC["upload"]["filter"]?>】');
					break;
				default:
					alert('发生错误');
					break;
			}
		},
		'onUploadSuccess':function(file, data, response){
			//alert(data);
			var r = eval("(" + data + ")");
			if(r.status=="true"){
				var o = $("#<?php echo $id;?>",window.parent.document);
				
				var txt = o.val();
				var sp = "\r";
				
				if( txt=="" || txt.substr(txt.length-1)=="\n" || txt.substr(txt.length-1)=="\r" || txt.substr(txt.length-1)=="\r\n" ){
					sp="";
				}
				
	 			$("#<?php echo $id;?>",window.parent.document).val(txt + sp + r.file);
	 		}
	 		else{
	 			alert(r.msg);
	 		}
 		},
 		'onQueueComplete':function(queueData){
 			
 		},
 		'onUploadError':function(file, errorCode, errorMsg, errorString){
 			alert(errorString);
 		}
	});
});
</script>
</head>
<body>
<div id="upload_down">
<form>
<div id="queue"></div>
<input id="file_upload" name="file_upload" type="file" multiple="true">
</form>
</div>
</body>
</html>