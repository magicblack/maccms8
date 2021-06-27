<?php
class AppMysql
{
	var $sql_id;
	var $sql_qc=0;
	
	function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8',$newlink=false)
	{
		if(!$this->sql_id=@mysql_connect($dbhost, $dbuser, $dbpw, $newlink)) {
			showErr("DataBase","MYSQL 连接数据库失败,请确定数据库用户名,密码设置正确<br>");
		}
		if(!@mysql_select_db($dbname,$this->sql_id)){
			showErr("DataBase","MYSQL 连接成功,但当前使用的数据库 {$dbName} 不存在<br>");
		} 
		
		if( mysql_get_server_info($this->sql_id) > '4.1' ){
			if($charset){
				mysql_query("SET character_set_connection=$charset,character_set_results=$charset,character_set_client=binary",$this->sql_id);
			}
			else{
				mysql_query("SET character_set_client=binary",$this->sql_id);
			}
			if( mysql_get_server_info($this->sql_id) > '5.0' ){
				mysql_query("SET sql_mode=''",$this->sql_id);
			}
		}
		else{
			showErr("DataBase","本系统仅支持MYSQL4.1以上版本");
		}
	}
	
	function close() {
		$this->sql_qc=0;
		return mysql_close($this->sql_id);
	}
	
	function select_database($dbName)
	{
		return mysql_select_db($dbName, $this->sql_id);
	}
	
	function fetch_array($query, $result_type = MYSQL_ASSOC)
	{
		return mysql_fetch_array($query, $result_type);
	}
	
	function real_escape_string($s){
		return mysql_real_escape_string($s);
	}
		
	function query($sql)
	{
		$this->sql_qc++;
		$sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
		//echo $sql."<br>";
		return mysql_query($sql, $this->sql_id);
	}
	
	function queryArray($sql,$keyf='')
	{
		$array = array();
		$result = $this->query($sql);
		while($r = $this->fetch_array($result))
		{
			if($keyf){
				$key = $r[$keyf];
				$array[$key] = $r;
			}
			else{
				$array[] = $r;
			}
		}
		return $array;
	}
	
	function affected_rows()
	{
		return mysql_affected_rows($this->sql_id);
	}
	
	function num_rows($query)
	{
		return mysql_num_rows($query);
	}
	
	function insert_id()
	{
		return mysql_insert_id($this->sql_id);
	}
	
	function selectLimit($sql, $num, $start = 0)
	{
		if ($start == 0){
			$sql .= ' LIMIT ' . $num;
		}
		else{
			$sql .= ' LIMIT ' . $start . ', ' . $num;
		}
		return $this->query($sql);
	}
	
	function getOne($sql, $limited = false)
	{
		if ($limited == true){
			$sql = trim($sql . ' LIMIT 1');
		}
		$res = $this->query($sql);
		if ($res !== false){
			$row = mysql_fetch_row($res);
			return $row[0];
		}
		else{
			return false;
		}
	}
	function getRow($sql)
	{
		$res = $this->query($sql);
		if ($res !== false){
			return mysql_fetch_assoc($res);
		}
		else{
			return false;
		}
	}
	
	function getAll($sql)
	{
		$res = $this->query($sql);
		if ($res !== false){
			$arr = array();
			while ($row = mysql_fetch_assoc($res)){
				$arr[] = $row;
			}
			return $arr;
		}
		else{
			return false;
		}
	}
	
	function getTableFields($dbName,$tabName)
	{
		$sql = "SELECT * FROM " . $tabName .' limit 1';
		$res = $this->query($sql);
		$fields = array();
		while ($v = mysql_fetch_field($res))
		{
			$fields[] = $v->name;
		}
		return $fields;
	}
	
	function Exist($tabName,$fieldName ,$ID)
	{
		$SqlStr="SELECT * FROM ".$tabName." WHERE ".$fieldName."=".$ID;
		$res=false;
		try{
			$row = $this->getRow($SqlStr);
			if($row){ $res=true; }
			unset($row);
		}
		catch(Exception $e){
		}
		return $res;
	}
	
	function AutoID($tabName,$colname)
	{
		$n = $this->getOne("SELECT Max(".$colname.") FROM [".$tabName."]");
		if (!is_numeric(n)){ $n=0; }
		return $n;
	}
	
	function Add($tabName,$arrFieldName ,$arrValue)
	{
		$res=false;
		if (chkArray($arrFieldName,$arrValue)){
			$sqlcol = "";
			$sqlval = "";
			$rc=false;
			foreach($arrFieldName as $a){
				if($rc){ $sqlcol.=",";}
				$sqlcol .= $a;
				$rc=true;
			}
			$rc=false;
			foreach($arrValue as $b){
				if($rc){ $sqlval.=",";}
				$sqlval .= "'".  $b ."'";
				$rc=true;
			}
			$sql = " INSERT INTO " . $tabName." (".$sqlcol.") VALUES(".$sqlval.")" ;
			//echo $sql."<br>";exit;
			$res = $this->query($sql);
			if($res){
				//echo "ok";
			}
			else{
				//echo "err";
			}
		}
		return $res;
	}
	
	function Update($tabName,$arrFieldName , $arrValue ,$KeyStr,$f=0)
	{
		$res=false;
		if (chkArray($arrFieldName,$arrValue)){
			$sqlval = "";
			$rc=false;
			
			for($i=0;$i<count($arrFieldName);$i++){
				if($rc){ $sqlval.=",";}
				if($f==0){
					$sqlval .= $arrFieldName[$i]."='". $arrValue[$i] ."'";
				}
				else{
					$sqlval .= $arrFieldName[$i]."='". $arrValue[$arrFieldName[$i]] ."'";
				}
				$rc=true;
			}
			$sql = " UPDATE " . $tabName." SET ".$sqlval." WHERE ".$KeyStr."";
			//echo $sql."<br>";
			$res = $this->query($sql);
			if($res){
				//echo "ok";
			}
			else{
				//echo "err";
			}
		}
		return $res;
	}
	
	function Delete($tabName,$KeyStr)
	{
		$res=false;
		$sql = "DELETE FROM ".$tabName." WHERE ".$KeyStr;
		$res = $this->query($sql);
		return $res;
	}
}





class AppMysqli
{
	var $sql_id;
	var $sql_qc=0;
	
	function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8',$newlink=false)
	{
		$this->sql_id = new mysqli($dbhost, $dbuser, $dbpw, $dbname, 3306);
		if(mysqli_connect_errno()) {
			$this->sql_id = false;
			showErr("DataBase","MYSQL 连接数据库失败,请确定数据库用户名,密码设置正确<br>");
		}
		else{
			$this->sql_id->set_charset($charset);
		}
	}
	
	function close() {
		$this->sql_qc=0;
		return mysqli_close($this->sql_id);
	}
	
	function select_database($dbName)
	{
		return mysqli_select_db($dbName, $this->sql_id);
	}
	
	function fetch_array($query, $result_type = MYSQLI_ASSOC)
	{
		return mysqli_fetch_array($query, $result_type);
	}
	
	function real_escape_string($s){
		return mysqli_real_escape_string($this->sql_id,$s);
	}
	function query($sql)
	{
		$this->sql_qc++;
		$sql = str_replace("{pre}",$GLOBALS['MAC']['db']['tablepre'],$sql);
		//echo $sql."<br>";
		return mysqli_query($this->sql_id,$sql);
	}
	
	function queryArray($sql,$keyf='')
	{
		$array = array();
		$result = $this->query($sql);
		while($r = $this->fetch_array($result))
		{
			if($keyf){
				$key = $r[$keyf];
				$array[$key] = $r;
			}
			else{
				$array[] = $r;
			}
		}
		return $array;
	}
	
	function affected_rows()
	{
		return mysqli_affected_rows($this->sql_id);
	}
	
	function num_rows($query)
	{
		return mysqli_num_rows($query);
	}
	
	function insert_id()
	{
		return mysqli_insert_id($this->sql_id);
	}
	
	function selectLimit($sql, $num, $start = 0)
	{
		if ($start == 0){
			$sql .= ' LIMIT ' . $num;
		}
		else{
			$sql .= ' LIMIT ' . $start . ', ' . $num;
		}
		return $this->query($sql);
	}
	
	function getOne($sql, $limited = false)
	{
		if ($limited == true){
			$sql = trim($sql . ' LIMIT 1');
		}
		$res = $this->query($sql);
		if ($res !== false){
			$row = mysqli_fetch_row($res);
			return $row[0];
		}
		else{
			return false;
		}
	}
	function getRow($sql)
	{
		$res = $this->query($sql);
		if ($res !== false){
			return mysqli_fetch_assoc($res);
		}
		else{
			return false;
		}
	}
	
	function getAll($sql)
	{
		$res = $this->query($sql);
		if ($res !== false){
			$arr = array();
			while ($row = mysqli_fetch_assoc($res)){
				$arr[] = $row;
			}
			return $arr;
		}
		else{
			return false;
		}
	}
	
	function getTableFields($dbName,$tabName)
	{
		$sql = "SELECT * FROM " . $tabName .' limit 1';
		$row = $this->query($sql);
		$res = mysqli_fetch_fields($row);
		$fields = array();
		foreach($res as $v)
		{
			$fields[] = $v->name;
		}
		return $fields;
	}
	
	function Exist($tabName,$fieldName ,$ID)
	{
		$SqlStr="SELECT * FROM ".$tabName." WHERE ".$fieldName."=".$ID;
		$res=false;
		try{
			$row = $this->getRow($SqlStr);
			if($row){ $res=true; }
			unset($row);
		}
		catch(Exception $e){
		}
		return $res;
	}
	
	function AutoID($tabName,$colname)
	{
		$n = $this->getOne("SELECT Max(".$colname.") FROM [".$tabName."]");
		if (!is_numeric(n)){ $n=0; }
		return $n;
	}
	
	function Add($tabName,$arrFieldName ,$arrValue)
	{
		$res=false;
		if (chkArray($arrFieldName,$arrValue)){
			$sqlcol = "";
			$sqlval = "";
			$rc=false;
			foreach($arrFieldName as $a){
				if($rc){ $sqlcol.=",";}
				$sqlcol .= $a;
				$rc=true;
			}
			$rc=false;
			foreach($arrValue as $b){
				if($rc){ $sqlval.=",";}
				$sqlval .= "'".  $b."'";
				$rc=true;
			}
			$sql = " INSERT INTO " . $tabName." (".$sqlcol.") VALUES(".$sqlval.")" ;
			//echo $sql."<br>";exit;
			$res = $this->query($sql);
			if($res){
				//echo "ok";
			}
			else{
				//echo "err";
			}
		}
		return $res;
	}
	
	function Update($tabName,$arrFieldName , $arrValue ,$KeyStr,$f=0)
	{
		$res=false;
		if (chkArray($arrFieldName,$arrValue)){
			$sqlval = "";
			$rc=false;

			for($i=0;$i<count($arrFieldName);$i++){
				if($rc){ $sqlval.=",";}
				if($f==0){
					$sqlval .= $arrFieldName[$i]."='".$arrValue[$i] ."'";
				}
				else{
					$sqlval .= $arrFieldName[$i]."='". $arrValue[$arrFieldName[$i]] ."'";
				}
				$rc=true;
			}
			$sql = " UPDATE " . $tabName." SET ".$sqlval." WHERE ".$KeyStr."";
			//echo $sql."<br>";die;
			$res = $this->query($sql);
			if($res){
				//echo "ok";
			}
			else{
				//echo "err";
			}
		}
		return $res;
	}
	
	function Delete($tabName,$KeyStr)
	{
		$res=false;
		$sql = "DELETE FROM ".$tabName." WHERE ".$KeyStr;
		$res = $this->query($sql);
		return $res;
	}
}


class AppFtp{
	var $ftpUrl = "127.0.0.1";
	var $ftpUser = "maccms";
	var $ftpPass = "123456";
	var $ftpDir = "/wwwroot/";
	var $ftpPort = "21";
	var $ftpR = ''; //R ftp资源;
	var $ftpStatus = 0;
	var $ftpStatusDes = "";
	//R 1:成功;2:无法连接ftp; 3:用户错误;
	
	
	function __construct($ftpUrl="", $ftpUser="", $ftpPass="",  $ftpPort="",  $ftpDir="") {
		if($ftpUrl){
			$this->ftpUrl=$ftpUrl;
		}
		if($ftpUser){
			$this->ftpUser=$ftpUser;
		}
		if($ftpPass){
			$this->ftpPass=$ftpPass;
		}
		if($ftpUrl){
			$this->ftpDir=$ftpDir;
		}
		if($ftpPort){
			$this->ftpPost=$ftpPort;
		}
	   if ($this->ftpR = @ftp_connect($this->ftpUrl, $this->ftpPost)) {
	     if (@ftp_login($this->ftpR, $this->ftpUser, $this->ftpPass)) {
			if (!empty($this->ftpDir)) {
				@ftp_chdir($this->ftpR, $this->ftpDir);
			}
	     	@ftp_pasv($this->ftpR, true);
	     	$this->ftpStatus = 1;
	     	$this->ftpStatusDes = "连接ftp成功";
	     }
	     else {
	     	$this->ftpStatus = 3;
	     	$this->ftpStatusDes = "连接ftp成功，但用户或密码错误";
	     }
	   }
	   else {
	     $this->ftpStatus = 2;
	     $this->ftpStatusDes = "连接ftp失败";
	   }
	}

	//R 切换目录;
	function cd($dir) {
	   return ftp_chdir($this->ftpR, $dir);
	}
	//R 返回当前路劲;
	function pwd() {
	   return ftp_pwd($this->ftpR);
	}
	function mkdirs($path)
	{
		$path_arr  = explode('/',$path);
		$file_name = array_pop($path_arr); 
		$path_div  = count($path_arr); 
		foreach($path_arr as $val)
		{
			if(@ftp_chdir($this->ftpR,$val) == FALSE)
			{
				$tmp = @ftp_mkdir($this->ftpR,$val);
				if($tmp == FALSE)
				{
					echo "目录创建失败，请检查权限及路径是否正确！";
					exit;
				}
				@ftp_chdir($this->ftpR,$val);
			}
		}
		for($i=1;$i<=$path_div;$i++)
		{
			@ftp_cdup($this->ftpR);
		}
	}

	//R 创建目录
	function mkdir($directory) {
	   return ftp_mkdir($this->ftpR,$directory);
	}
	//R 删除目录
	function rmdir($directory) {
	   return ftp_rmdir($this->ftpR,$directory);
	}
	//R 上传文件;
	function put($localFile, $remoteFile = ''){
	   if ($remoteFile == '') {
	     $remoteFile = end(explode('/', $localFile));
	   }
	   $res = ftp_nb_put($this->ftpR, $remoteFile, $localFile, FTP_BINARY);
	   while ($res == FTP_MOREDATA) {
	     $res = ftp_nb_continue($this->ftpR);
	   }
	   if ($res == FTP_FINISHED) {
	     return true;
	   } elseif ($res == FTP_FAILED) {
	     return false;
	   }
	}
	//R 下载文件;
	function get($remoteFile, $localFile = '') {
	   if ($localFile == '') {
	     $localFile = end(explode('/', $remoteFile));
	   }
	   if (ftp_get($this->ftpR, $localFile, $remoteFile, FTP_BINARY)) {
	     $flag = true;
	   } else {
	     $flag = false;
	   }
	   return $flag;
	}
	//R 文件大小;
	function size($file) {
	   return ftp_size($this->ftpR, $file);
	}
	//R 文件是否存在;
	function isFile($file) {
	   if ($this->size($file) >= 0) {
	     return true;
	   } else {
	     return false;
	   }
	}
	//R 文件时间
	function fileTime($file) {
	   return ftp_mdtm($this->ftpR, $file);
	}
	//R 删除文件;
	function unlink($file) {
	   return ftp_delete($this->ftpR, $file);
	}
	function nlist($dir = '/service/resource/') {
	   return ftp_nlist($this->ftpR, $dir);
	}
	//R 关闭连接;
	function bye() {
	   return ftp_close($this->ftpR);
	}
}

?>