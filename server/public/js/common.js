function messageNotice(message)
{
	$("#messageBox").html(message);
	$("#messageBox").show();
	setInterval(function(){$("#messageBox").hide();},3000);
}










