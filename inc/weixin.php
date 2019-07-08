<?php
require("conn.php");
require(MAC_ROOT.'/inc/common/360_safe3.php');
define('TOKEN', $GLOBALS['MAC']['weixin']['token']);

$wechatObj = new wechatCallbackapi();
if (isset($_GET['echostr'])) {
	$wechatObj->valid();
}
else {
	$wechatObj->responseMsg();
}
    
class wechatCallbackapi {
	
	function __construct(){
	}
	
	public function valid() {
		$echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
	}
	
	private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
	public function responseMsg() {
		$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
		if(!$postStr){
            $postStr = @file_get_contents("php://input");
        }
		if (!empty($postStr)) {
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$postType = trim($postObj->MsgType);
			switch ($postType) {
                    case 'text':
                        $res = $this->receiveText($postObj);
                    break;
                    case 'image':
                        $res = $this->receiveImage($postObj);
                    break;
                    case 'location':
                        $res = $this->receiveLocation($postObj);
                    break;
                    case 'voice':
                        $res = $this->receiveVoice($postObj);
                    break;
                    case 'video':
                        $res = $this->receiveVideo($postObj);
                    break;
                    case 'link':
                        $res = $this->receiveLink($postObj);
                    break;
                    case 'event':
                        $res = $this->receiveEvent($postObj);
                    break;
                    default:
                        $res = 'unknow msg type: '.$postType;
                    break;
			}
			echo $res;
		}
		else{
			echo 'other msg';
			exit;
		}
	}
	private function receiveLink($object) {
		$msg = '你发送的是链接已收到，请等待处理';
		$res = $this->transmitText($object, $msg);
		return $res;
	}
	
	private function receiveText($object) {
		$content = trim($object->Content);
        $content = chkSql($content);
        $txt = '请点击下方链接：'. "\n";
        
		if ($GLOBALS['MAC']['weixin']['gjc1'] <> '' && strstr($content, $GLOBALS['MAC']['weixin']['gjc1'])) {
			$res = array();
			$txt .=  '<a href="'.$GLOBALS['MAC']['weixin']['gjcl1'].'">'.$GLOBALS['MAC']['weixin']['gjcm1'].'</a>' . "\n";
			$res[] = array('Title'=>$GLOBALS['MAC']['weixin']['gjcm1'], 'Description'=>'', 'PicUrl'=>$GLOBALS['MAC']['weixin']['gjci1'], 'Url'=>$GLOBALS['MAC']['weixin']['gjcl1']);
		}
		elseif ($GLOBALS['MAC']['weixin']['gjc2'] <> '' && strstr($content, $GLOBALS['MAC']['weixin']['gjc2'])) {
			$res = array();
			$txt .=  '<a href="'.$GLOBALS['MAC']['weixin']['gjcl2'].'">'.$GLOBALS['MAC']['weixin']['gjcm2'].'</a>' . "\n";
			$res[] = array('Title'=>$GLOBALS['MAC']['weixin']['gjcm2'], 'Description'=>'', 'PicUrl'=>$GLOBALS['MAC']['weixin']['gjci2'], 'Url'=>$GLOBALS['MAC']['weixin']['gjcl2']);
		}
		elseif ($GLOBALS['MAC']['weixin']['gjc3'] <> '' && strstr($content, $GLOBALS['MAC']['weixin']['gjc3'])) {
			$res = array();
			$txt .=  '<a href="'.$GLOBALS['MAC']['weixin']['gjcl3'].'">'.$GLOBALS['MAC']['weixin']['gjcm3'].'</a>' . "\n";
			$res[] = array('Title'=>$GLOBALS['MAC']['weixin']['gjcm3'], 'Description'=>'', 'PicUrl'=>$GLOBALS['MAC']['weixin']['gjci3'], 'Url'=>$GLOBALS['MAC']['weixin']['gjcl3']);
		}
		elseif ($GLOBALS['MAC']['weixin']['gjc4'] <> '' && strstr($content, $GLOBALS['MAC']['weixin']['gjc4'])) {
			$res = array();
			$txt .=  '<a href="'.$GLOBALS['MAC']['weixin']['gjcl4'].'">'.$GLOBALS['MAC']['weixin']['gjcm4'].'</a>' . "\n";
			$res[] = array('Title'=>$GLOBALS['MAC']['weixin']['gjcm4'], 'Description'=>'', 'PicUrl'=>$GLOBALS['MAC']['weixin']['gjci4'], 'Url'=>$GLOBALS['MAC']['weixin']['gjcl4']);
		}
		else {
            $res = array();
            $num = 0;
			$all = 7;
			
			if($GLOBALS['MAC']['weixin']['msgtype'] !='1'){
				$all=1;
			}
            getDbConnect();
			
            $sql = "SELECT d_id,d_name,d_pic,d_starring,d_directed,d_area,d_year,d_lang,d_content from {pre}vod WHERE d_name like '%" . $content . "%' or d_enname like '%" . ($content) . "%' ";
            $sql .= ' limit ' . $all;
            
            //echo $sql;die;
            $rs = $GLOBALS['db']->queryArray($sql, false);
            if (!$rs) {
                $res = array();
                $txt .=  '<a href="'.$GLOBALS['MAC']['weixin']['wuziyuanlink'].'">'.$GLOBALS['MAC']['weixin']['wuziyuan'].'</a>' . "\n";
                $res[] = array('Title' => $GLOBALS['MAC']['weixin']['wuziyuan'], 'Description' => '', 'PicUrl' => '', 'Url' => $GLOBALS['MAC']['weixin']['wuziyuanlink']);
                //$res[] = array('Title'=>'更多好玩的东西', 'Description'=>'', 'PicUrl'=>'', 'Url'=> $GLOBALS['MAC']['weixin']['sousuo'].'/ad.html');
            } else {
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                if($GLOBALS['MAC']['weixin']['bofang'] =='2'){
                    $search_url = $http_type . $GLOBALS['MAC']['weixin']['sousuo'] .'/index.php?m=vod-search-wd-' . urlencode($content);
                    $txt .=  '<a href="'.$search_url.'">点击进入搜索页面查看</a>' . "\n";
                    $res[] = array('Title'=>'点击进入搜索页面查看', 'Description'=>'恭喜您找到了相关资源，由于微信限制请进入搜索页查看', 'PicUrl'=>'', 'Url'=>$search_url );
                }
                else {
                    foreach ($rs as $k => $v) {
                        $url = $http_type . $GLOBALS['MAC']['weixin']['sousuo'] . "/index.php?m=vod-detail-id-" . $v['d_id'] . ".html";
                        if ($GLOBALS['MAC']['weixin']['bofang'] > 0) {
                            $url = $http_type . $GLOBALS['MAC']['weixin']['sousuo'] . "/index.php?m=vod-play-id-" . $v['d_id'] . "-src-1-num-1.html";
                        }
                        if (substr($v['d_pic'], 0, 4) != "http") {
                            if ($GLOBALS['MAC']['upload']['remote'] == 1) {
                                $picUrl = $GLOBALS['MAC']['upload']['remoteurl'] . $v['d_pic'];
                            } else {
                                $picUrl = $http_type . $GLOBALS['MAC']['weixin']['sousuo'] . "/" . $v['d_pic'];
                            }
                        } else {
                            $picUrl = $v['d_pic'];
                        }
                        $txt .= '<a href="' . $url . '">' . ($k + 1) . ',' . $v['d_name'] . ' ' . $v['d_remarks'] . '</a>' . "\n";
                        $res[$num] = array('Title' => $v['d_name'], 'Description' => getTextt(20, strip_tags($v["d_content"])), 'PicUrl' => $picUrl, 'Url' => $url);
                    }
                }
            }
        }
		if (is_array($res)){
			if ($GLOBALS['MAC']['weixin']['msgtype'] !='1' && isset($res[0])){
				$r = $this->transmitNews($object, $res);
			}
			else{
				$r = $this->transmitText($object, $txt);
			}
		}
	    return $r;
	}
	
    private function receiveEvent($object) {
        $guanzhu = $GLOBALS['MAC']['weixin']['guanzhu'];
        $msg = '';
        switch ($object->Event) {
            case 'subscribe':
                $msg = $guanzhu;
            break;
            case 'unsubscribe':
                $msg = '拜拜了您内~';
            break;
            case 'CLICK':
                switch ($object->EventKey) {
                    default:
                        $res = '你点击了: '.$object->EventKey;
                    break;
                }
            break;
            default:
                $msg = 'receive a new event: '.$object->Event;
            break;
        }
        $res = $this->transmitText($object, $msg);
        return $res;
    }
    private function transmitText($object, $content) {
        $xmlTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>';
        $res = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $res;
    }
    private function transmitNews($object, $newsArray) {
        if (!is_array($newsArray)) {
            return;
        }
        $itemTpl = '<item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
        </item>';
        $item_str = '';
        foreach($newsArray as $item) {
            $item_str.= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = '<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>%s</ArticleCount>
        <Articles>%s</Articles>
        </xml>';
        $res = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray),$item_str);
        return $res;
    }
    
    private function transmitImage($object, $imageArray) {
        $xmlTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
            <MediaId><![CDATA[%s]]></MediaId>
            </Image>
            </xml>';
		
        $res = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $imageArray['MediaId']);
        return $res;
    }
}
?>