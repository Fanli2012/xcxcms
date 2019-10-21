function messageNotice(message)
{
	$("#messageBox").html(message);
	$("#messageBox").show();
	setInterval(function(){$("#messageBox").hide();},3000);
}

/**
 * 时间戳转日期格式
 * @param {int} timestamp 时间戳
 * @param {string} format 日期格式Y-m-d H:i:s
 * @return {string} 2018-12-31 00:00:00
 */
function formatDateTime(timestamp,format='Y-m-d H:i:s')
{
    if(String(timestamp).length == 10)
    {
        timestamp = timestamp * 1000;
    }
    
    var date = new Date(timestamp); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
    var year = date.getFullYear(),
        month = date.getMonth()+1,//月份是从0开始的
        day = date.getDate(),
        hour = date.getHours(),
        min = date.getMinutes(),
        sec = date.getSeconds();
        
    var preArr = Array.apply(null,Array(10)).map(function(elem, index) {
        return '0'+index;
    }); //开个长度为10的数组 格式为 00 01 02 03
    
    var newTime = format.replace(/Y/g,year)
                        .replace(/m/g,preArr[month]||month)
                        .replace(/d/g,preArr[day]||day)
                        .replace(/H/g,preArr[hour]||hour)
                        .replace(/i/g,preArr[min]||min)
                        .replace(/s/g,preArr[sec]||sec);
                        
    return newTime;
}






