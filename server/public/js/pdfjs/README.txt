
PDF在线预览

1、file传参是比较简单的方式,只要知道了文件名跟类型就行了。但是这种方式用在项目中的话只能打开项目里的pdf文件，换句话说就是PDF.js默认是不能打开项目外文件系统的文件。

如果pdf文件与viewer.html不在一层目录中，改成相对路径即可。
示例：
/pdfjs/web/viewer.html?file=./compressed.tracemonkey-pldi-09.pdf

2、文件流方式实现在线展示pdf文件

1)
修改viewer.js
var DEFAULT_URL = 'compressed.tracemonkey-pldi-09.pdf'  里面是PDF的路径
删除该变量定义

2)
通过ajax的方式获取文件流数据，并处理。

-------------------------------------------------------------
JS代码
var DEFAULT_URL = "";//注意，删除的变量在这里重新定义
var PDFData = "";
$.ajax({
    type:"post",
    async:false,
    dataType:"json",
    url:文件流请求地址,
    success:function(data){
       var pdfData = atob(data);
    }
});
var rawLength = PDFData.length;
//转换成pdf.js能直接解析的Uint8Array类型,见pdf.js-4068
var array = new Uint8Array(new ArrayBuffer(rawLength));
for(i = 0; i < rawLength; i++) {
  array[i] = PDFData.charCodeAt(i) & 0xff;
}
DEFAULT_URL = array;
-------------------------------------------------------------

将上面的代码作为js文件或是代码片段，加入viewer.html（上面的代码要放在<script src="viewer.js"></script>引入之前）。

-------------------------------------------------------------
后端PHP代码
$str = file_get_contents($_REQUEST['url']);
exit(json_encode(chunk_split(base64_encode($str))));
-------------------------------------------------------------

