<script type="text/javascript" src="/js/tooltip.js"></script>
<script type="text/javascript" src="/js/jquery.shorten.1.0.js"></script>
<script type="text/javascript">
<!--
	function toggle_visibility(id,caller) {	
		var pos = caller.innerHTML.indexOf('add')
		if(pos >= 0)
			caller.innerHTML = caller.innerHTML.replace('add', 'hide');
		else
			caller.innerHTML = caller.innerHTML.replace('hide', 'add');
		var e = document.getElementById(id);
		if(e.style.display == 'block')
			e.style.display = 'none';
		else
			e.style.display = 'block';
	}
//-->
</script>