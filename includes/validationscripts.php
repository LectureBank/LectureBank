<script src="/js/passwordStrengthMeter.js" type="text/javascript" ></script>
<script src="/js/passwordValidation.js" type="text/javascript" ></script>
<script type="text/javascript">
<!--
/*
Credits: Bit Repository
Source: http://www.bitrepository.com/web-programming/ajax/username-checker.html
*/

$(document).ready(function(){

$("#username").keyup(function(){ 

var usr = $("#username").val();
usr = $.trim(usr.toLowerCase());

if(usr.length >= 5)
{
	if(usr.length > 20)
	{
	$("#usrstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;Must be less than <strong>20</strong> characters.');
	$("#usrstatus").addClass('error');
	
	$("#username").removeClass('object_ok'); // if necessary
	$("#username").addClass("object_error");
	}
	else
	{
	$("#usrstatus").removeClass('error');
	$("#usrstatus").html('<img src="/images/loader.gif" align="absmiddle">&nbsp;Checking availability...');

    $.ajax({
    type: "POST",
    url: "username-check.php",
    data: "username="+ usr,
    success: function(msg){  

   $("#usrstatus").ajaxComplete(function(event, request, settings){ 

	if(msg == 'OK')
	{
		$("#usrstatus").removeClass('error');
        $("#username").removeClass('object_error'); // if necessary
		$("#username").addClass("object_ok");
		$(this).html('<img src="/images/tick.gif" align="absmiddle">&nbsp;&quot;'+usr+'&quot;&nbsp;is available!');
	}
	else
	{
		$("#usrstatus").addClass('error');
		$("#username").removeClass('object_ok'); // if necessary
		$("#username").addClass("object_error");
		$(this).html(msg);
	}  
	});
    } 
   }); 
   }
}
else
	{
	$("#usrstatus").addClass('error');
	$("#usrstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;' +
'Must be at least <strong>5</strong> characters.');
	$("#username").removeClass('object_ok'); // if necessary
	$("#username").addClass("object_error");
	}

});

});

//-->

</script>
<script type="text/javascript">
<!--

$(document).ready(function(){

$("#email").keyup(function() { 

var email = $("#email").val();

if(email.length >= 6)
{
$("#emailstatus").html('<img src="/images/loader.gif" align="absmiddle">&nbsp;Checking validity...');

    $.ajax({
    type: "POST",
    url: "email-validation.php",
    data: "email="+ email,
    success: function(msg){  

   $("#emailstatus").ajaxComplete(function(event, request, settings){ 

	if(msg == 'OK')
	{
		$("#emailstatus").removeClass('error');
        $("#email").removeClass('object_error'); // if necessary
		$("#email").addClass("object_ok");
		$(this).html('<img src="/images/tick.gif" align="absmiddle">&nbsp;Address validated.');
	}
	else
	{
		$("#emailstatus").addClass('error');
		$("#email").removeClass('object_ok'); // if necessary
		$("#email").addClass("object_error");
		$(this).html(msg);
	}  

   });

 } 

  }); 

}
else
	{
	$("#emailstatus").addClass('error');
	$("#emailstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;' +
'Too short to validate.');
	$("#email").removeClass('object_ok'); // if necessary
	$("#email").addClass("object_error");
	}

});

});

//-->

</script>