<script type="text/javascript" src="/js/jquery-1.5.2.min.js"></script>
<script>
var $overlay_wrapper;
var $overlay_panel;

function show_overlay() {
    if ( !$overlay_wrapper ) append_overlay();
    $overlay_wrapper.fadeIn(700);
}

function suppress_hide() {
	$innerClick = true;
}

function force_hide() {
	$innerClick = false;
	$overlay_wrapper.fadeOut(500);
}

function hide_overlay() {
	if ( $innerClick == false ) $overlay_wrapper.fadeOut(500);
	$innerClick = false;
}

function append_overlay() {
    $overlay_wrapper = $('<div id="overlay" onclick="hide_overlay();"></div>').appendTo( $('BODY') );
    $overlay_panel = $('<div id="overlay-panel" onclick="suppress_hide();"></div>').appendTo( $overlay_wrapper );

    $overlay_panel.html( '<a href="#" class="hide-overlay"><img src="../images/close.png" style="position:absolute;float:right;clear:both;top:-10px;right:-10px;"></a><br /><img id="lbmini" src="../images/lbmini.png"><br /><span style="text-align:center;color:red;font-weight:strong;font-variant:small-caps;">you must be logged in to do that.</span><?php include("loginform.php") ?><br />' );

    attach_overlay_events();
}

function attach_overlay_events() {
    $('A.hide-overlay', $overlay_wrapper).click( function(ev) {
        ev.preventDefault();
        force_hide();
    });
}

$(document).ready(function() {
	$(function() {
        show_overlay();
	});
});
</script>