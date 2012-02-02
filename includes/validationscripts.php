<script src="/js/jquery-1.5.2.min.js" type="text/javascript"></script>
<script src="/js/passwordStrengthMeter.js" type="text/javascript" ></script>
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
	$("#usrstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Must be less than <strong>20</strong> characters.</font>');
	$("#username").removeClass('object_ok'); // if necessary
	$("#username").addClass("object_error");
	}
	else
	{
	$("#usrstatus").html('<img src="/images/loader.gif" align="absmiddle">&nbsp;Checking availability...');

    $.ajax({
    type: "POST",
    url: "username-check.php",
    data: "username="+ usr,
    success: function(msg){  

   $("#usrstatus").ajaxComplete(function(event, request, settings){ 

	if(msg == 'OK')
	{
        $("#username").removeClass('object_error'); // if necessary
		$("#username").addClass("object_ok");
		$(this).html('<img src="/images/tick.gif" align="absmiddle">&nbsp;&quot;'+usr+'&quot;&nbsp;is available!');
	}
	else
	{
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
	$("#usrstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">' +
'Must be at least <strong>5</strong> characters.</font>');
	$("#username").removeClass('object_ok'); // if necessary
	$("#username").addClass("object_error");
	}

});

});

//-->

</script>
<script type="text/javascript">
<!--

var confirmationentered = false;

$(document).ready(function(){

$("#password").keyup(function() { 

var pw = $("#password").val();
var pwc = $("#passwordconfirm").val();

if(pw.length >= 6)
	{
	if(pw.length > 32)
		{
		$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">' +
		'Must be less than <strong>32</strong> characters.</font>');
		$("#password").removeClass('object_ok'); // if necessary
		$("#password").addClass("object_error");
		}
	else
		{
		$('#pwstatus').html(passwordStrength($('#password').val(),$('#username').val()));
		$("#password").removeClass('object_error'); // if necessary
		$("#password").addClass("object_ok");
		
		if((confirmationentered == true))
			{
				if(pw != pwc)
					{
					$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">' +
					'Passwords must match.</font>');
					$("#password").removeClass('object_ok'); // if necessary
					$("#passwordconfirm").removeClass('object_ok'); // if necessary
					$("#passwordconfirm").addClass("object_error");
					}
				else
					{
					$("#password").removeClass('object_error'); // if necessary
					$("#password").addClass("object_ok");
					$("#passwordconfirm").removeClass('object_error'); // if necessary
					$("#passwordconfirm").addClass("object_ok");
					}
			}
		}
	}
else
	{
	$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">' +
'Must be at least <strong>6</strong> characters.</font>');
	$("#password").removeClass('object_ok'); // if necessary
	$("#password").addClass("object_error");
	}

});

$("#passwordconfirm").keyup(function() { 

confirmationentered = true;

var pw = $("#password").val();
var pwc = $("#passwordconfirm").val();

if(pw != pwc)
	{
	$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">' +
	'Passwords must match.</font>');
	$("#password").removeClass('object_ok'); // if necessary
	$("#passwordconfirm").removeClass('object_ok'); // if necessary
	$("#passwordconfirm").addClass("object_error");
	}
else
	{
	$('#pwstatus').html(passwordStrength($('#password').val(),$('#username').val()));
	$("#password").removeClass('object_error'); // if necessary
	$("#password").addClass("object_ok");
	$("#passwordconfirm").removeClass('object_error'); // if necessary
	$("#passwordconfirm").addClass("object_ok");
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
        $("#email").removeClass('object_error'); // if necessary
		$("#email").addClass("object_ok");
		$(this).html('<img src="/images/tick.gif" align="absmiddle">&nbsp;Address validated.');
	}
	else
	{
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
	$("#emailstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">' +
'Too short to validate.</font>');
	$("#email").removeClass('object_ok'); // if necessary
	$("#email").addClass("object_error");
	}

});

});

//-->

</script>