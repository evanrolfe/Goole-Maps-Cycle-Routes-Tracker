<?php defined('_JEXEC') or die('Restricted access'); 
$asset_url = JURI::base()."components/com_routes/assets/"; ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=geometry&key=AIzaSyCsCpXNCbwxziCgcOWdriOFGFMYre4ZeuI&sensor=false"></script>


<script type="text/javascript" src="<?php echo $asset_url; ?>markerclusterer.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>datatable/jquery.dataTables.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>countries.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>all.js"></script>
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
<div id='breadcrumbs'></div>
		<table width='100%'>

<?php if($this->user_id){ ?>
		<tr>
				<td colspan=2>
					<a href="<?php echo JURI::base(); ?>index.php?option=com_routes&task=create">
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">
						<span style="padding: 10px; font-weight: bold">
							Create Track
						</span>
					</button>
					</a>
				</td>
		</tr>
<?php } ?>

		<tr>
			<td>
				<select onchange="search_continent(this.value)" id='continent'>
					<option value="0">Select Continent</option>
					<option value="AF">Africa</option>
					<option value="AS">Asia</option>
					<option value="AU">Australia and Oceania</option>
					<option value="EU">Europe</option>
					<option value="NA">North America</option>
					<option value="SA">South America</option>
				</select> >> 
				<select id='country' onchange="search_country($(this).val())"><option>Select Country</option></select>
				<button class="ui-button ui-state-default ui-corner-all " id='clear_filter' onclick="clear_search();" style='display: none'>
					<span style="padding: 10px">Clear Search</span>
				</button>
			</td>
			<td align='right'>
<?php if($this->user_id){ ?>
<button id="myfilter" class="ui-button ui-state-default ui-corner-all" onclick="filter_my_routes(<?php echo $this->user_id; ?>)">
	<span style="padding: 10px">My Routes</span>
</button>
<button class="ui-button ui-state-default ui-corner-all" id="clear_myfilter" onclick='table.fnFilter("",1);$("#clear_myfilter").hide();$("#myfilter").show()' style='display: none'>
	<span style="padding: 10px">All Routes</span>
</button>

<?php } ?>
<button onclick='change_unit()' class="ui-button ui-state-default ui-corner-all ">
	<span style="padding: 10px">Change Unit</span>
</button>
			</td>
		</tr>
		</table>
		<div id="map_canvas" style="width:100%; height:300px"></div><br>
		<div id="dynamic" style="width:100%;"></div>


