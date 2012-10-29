<?php defined('_JEXEC') or die('Restricted access'); 
$asset_url = JURI::base()."components/com_routes/assets/"; ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=geometry&key=AIzaSyCsCpXNCbwxziCgcOWdriOFGFMYre4ZeuI&sensor=false"></script>


<script type="text/javascript" src="<?php echo $asset_url; ?>markerclusterer.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>datatable/jquery.dataTables.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>countries.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>front.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>jquery.easy-confirm-dialog.js"></script>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/blitzer/jquery-ui.css" type="text/css" />
<link type="text/css" href="<?php echo $asset_url; ?>jquery-ui-1.8.21.custom.css" rel="Stylesheet" />	
<style type="text/css" title="currentStyle">

	@import "<?php echo $asset_url; ?>datatable/demo_table.css";
</style>
<script type="text/javascript">
$(function(){
	$(".confirm").easyconfirm();
});
</script>
<script type="text/javascript" src="<?php echo $asset_url; ?>all.js"></script>
<body onload='initialize()'>
<div id='base_url' style='display:none'><?php echo JURI::base(); ?></div>
<div id='user_id' style='display:none'><?php echo $this->user_id; ?></div>
<table>
<tr>
	<td width='25%'>Train locally, think globally</td>
	<td width='65%'><div id="map_canvas" style="width:550px; height:300px"></div></td>
</tr>
<tr>
	<td></td>
	<td></td>
</tr>
</table>
<div class="hob-box">
	<div class="hob-box-top"></div>
	<div class="hob-box-btm">
        <div class="hob-box-title">
        	<div class="box-title-left"><img src="/templates/HopOnBike_template/images/icon2.png" alt="" title=""></div>
        	<div class="box-title-right">Best Tracks</div>
        </div>
        <div class="box-border"></div>
        <div class="hob-box-txt">
			<ul>
			<li><a href="index.php?option=com_track&amp;view=showtrack&amp;track=6&amp;Itemid=53">test 2</a></li>
			<li><a href="index.php?option=com_track&amp;view=showtrack&amp;track=4&amp;Itemid=53">Podgorica - Pšata</a></li>
			<li><a href="index.php?option=com_track&amp;view=showtrack&amp;track=3&amp;Itemid=53">route 66</a></li>
			<li><a href="index.php?option=com_track&amp;view=showtrack&amp;track=2&amp;Itemid=53">ljubljana-podpeč-rakitna</a></li>									</ul>
	  	</div>
    </div>
</div>
