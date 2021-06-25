var mac_flag=1;         //播放器版本
var mac_second=5;       //播放前预加载广告时间 1000表示1秒
var mac_width=0;      //播放器宽度0自适应
var mac_height=580;     //播放器高度
var mac_widthmob=0;      //手机播放器宽度0自适应
var mac_heightmob=400;     //手机播放器高度
var mac_widthpop=704;   //弹窗窗口宽度
var mac_heightpop=560;  //弹窗窗口高度
var mac_showtop=1;     //美化版播放器是否显示头部
var mac_showlist=1;     //美化版播放器是否显示列表
var mac_autofull=0;     //是否自动全屏,0否,1是
var mac_buffer="//union.maccms.la/html/buffer.html";     //缓冲广告地址
var mac_prestrain="//union.maccms.la/html/prestrain.html";  //预加载提示地址
var mac_parse="";  //接口地址
var mac_colors="000000,F6F6F6,F6F6F6,333333,666666,FFFFF,FF0000,2c2c2c,ffffff,a3a3a3,2c2c2c,adadad,adadad,48486c,fcfcfc";   //背景色，文字颜色，链接颜色，分组标题背景色，分组标题颜色，当前分组标题颜色，当前集数颜色，集数列表滚动条凸出部分的颜色，滚动条上下按钮上三角箭头的颜色，滚动条的背景颜色，滚动条空白部分的颜色，滚动条立体滚动条阴影的颜色 ，滚动条亮边的颜色，滚动条强阴影的颜色，滚动条的基本颜色
//缓存开始
var mac_play_list={"dplayer":{"status":1,"sort":908,"show":"DPlayer_H5\u64ad\u653e\u5668","des":"dplayer.js.org","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"videojs":{"status":1,"sort":907,"show":"Videojs-H5\u64ad\u653e\u5668","des":"videojs.com","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"iva":{"status":1,"sort":906,"show":"iva-H5\u64ad\u653e\u5668","des":"videojj.com","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"iframe":{"status":1,"sort":905,"show":"iframe\u5916\u94fe\u6570\u636e","des":"iframe\u5d4c\u5165","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"link":{"status":1,"sort":904,"show":"\u5916\u94fe\u6570\u636e","des":"\u5916\u90e8\u7f51\u7ad9\u64ad\u653e\u94fe\u63a5","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"swf":{"status":1,"sort":903,"show":"Flash\u6587\u4ef6","des":"swf","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"flv":{"status":1,"sort":902,"show":"flv\u6587\u4ef6","des":"flv","ps":"0","parse":"","tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"}};var mac_down_list={"http":{"status":1,"sort":190,"show":"HTTP\u4e0b\u8f7d","des":"","ps":null,"parse":null,"tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"ftp":{"status":1,"sort":180,"show":"FTP\u4e0b\u8f7d","des":"","ps":null,"parse":null,"tip":"\u65e0\u9700\u5b89\u88c5\u4efb\u4f55\u63d2\u4ef6"},"xunlei":{"status":1,"sort":90,"show":"\u8fc5\u96f7\u4e0b\u8f7d","des":"xunlei.com","ps":null,"parse":null,"tip":"\u9700\u8981\u5b89\u88c5\u8fc5\u96f7\u4e0b\u8f7d\u5de5\u5177"},"kuaiche":{"status":1,"sort":80,"show":"\u5feb\u8f66\u4e0b\u8f7d","des":"kuaiche.com","ps":null,"parse":null,"tip":"\u9700\u8981\u5b89\u88c5\u5feb\u8f66\u4e0b\u8f7d\u529f\u80fd"},"bt":{"status":1,"sort":70,"show":"BT\u4e0b\u8f7d","des":"bt.com","ps":null,"parse":null,"tip":"\u9700\u8981\u5b89\u88c5BT\u4e0b\u8f7d\u5de5\u5177"}};var mac_server_list={"webplay":{"status":1,"sort":1,"show":"\u8fdc\u53e4\u670d\u52a1\u5668","des":"maccmsc.com","ps":null,"parse":null,"tip":"\u8fdc\u53e4\u670d\u52a1\u566822"}};
//缓存结束