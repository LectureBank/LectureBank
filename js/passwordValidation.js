var confirmationentered = false;

$(document).ready(function(){

$("#password").keyup(function() { 

var pw = $("#password").val();
var pwc = $("#passwordconfirm").val();
var username;

if($('#username').length == 0)
	{
		username = "password";
	}
else
	{
		username = $("#username").val();
	}

if(pw.length >= 6)
	{
	if(pw.length > 32)
		{
		$("#pwstatus").addClass('error');
		$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;' +
		'Must be less than <strong>32</strong> characters.');
		$("#password").removeClass('object_ok'); // if necessary
		$("#password").addClass("object_error");
		}
	else
		{
		$("#pwstatus").removeClass('error');
		$('#pwstatus').html(passwordStrength(pw,username));
		$("#password").removeClass('object_error'); // if necessary
		$("#password").addClass("object_ok");
		
		if((confirmationentered == true))
			{
				if(pw != pwc)
					{
					$("#pwstatus").addClass('error');
					$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;' +
					'Passwords must match.');
					$("#password").removeClass('object_ok'); // if necessary
					$("#passwordconfirm").removeClass('object_ok'); // if necessary
					$("#passwordconfirm").addClass("object_error");
					}
				else
					{
					$("#pwstatus").removeClass('error');
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
	$("#pwstatus").addClass('error');
	$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;' +
'Must be at least <strong>6</strong> characters.');
	$("#password").removeClass('object_ok'); // if necessary
	$("#password").addClass("object_error");
	}

});

$("#passwordconfirm").keyup(function() { 

confirmationentered = true;

var pw = $("#password").val();
var pwc = $("#passwordconfirm").val();
var username;

if($('#username').length == 0)
	{
		username = "password";
	}
else
	{
		username = $("#username").val();
	}

if(pw != pwc)
	{
	$("#pwstatus").addClass('error');
	$("#pwstatus").html('<img src="/images/error.png" align="absmiddle">&nbsp;' +
	'Passwords must match.');
	$("#password").removeClass('object_ok'); // if necessary
	$("#passwordconfirm").removeClass('object_ok'); // if necessary
	$("#passwordconfirm").addClass("object_error");
	}
else
	{
	$("#pwstatus").removeClass('error');
	$('#pwstatus').html(passwordStrength(pw,username));
	$("#password").removeClass('object_error'); // if necessary
	$("#password").addClass("object_ok");
	$("#passwordconfirm").removeClass('object_error'); // if necessary
	$("#passwordconfirm").addClass("object_ok");
	}

});

});