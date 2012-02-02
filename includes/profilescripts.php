<script type="text/javascript" src="/js/tooltip.js"></script>
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
<script type="text/javascript">
<!--
	function toggle_more(id,caller) {	
		var pos = caller.innerHTML.indexOf('more')
		if(pos >= 0)
			caller.innerHTML = caller.innerHTML.replace('more', 'less');
		else
			caller.innerHTML = caller.innerHTML.replace('less', 'more');
		var e = document.getElementById(id);
		element.setAttribute('style', 'max-height: 20px');
	}
//-->
</script>