<?php defined('_JEXEC') or die('Restricted access'); 
$asset_url = JURI::base()."components/com_routes/assets/"; ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=geometry&sensor=false&key=AIzaSyCsCpXNCbwxziCgcOWdriOFGFMYre4ZeuI"></script>
<link type="text/css" href="<?= $asset_url; ?>jquery-ui-1.8.20.custom.css" rel="Stylesheet" />	
<script type="text/javascript" src="<?= $asset_url; ?>jquery.validate.js"></script>
<script type="text/javascript" src="<?= $asset_url; ?>countries.js"></script>
<script type="text/javascript" src="<?= $asset_url; ?>create.js"></script>
<script type="text/javascript" src="<?= $asset_url; ?>jqModal.js"></script>
<link type="text/css" href="<?= $asset_url; ?>jqModal.css" rel="Stylesheet" />	
<script type="text/javascript">
$(function(){
	$("#form").validate({
		//rules : { country : { notEqual : "not_selected"} },
		messages : { distance : 'Please select your route by clicking on the map above',
					country: 'Please select a continent and country.'}
	});

	var load_preview = function(hash){
		$.post('index.php?option=com_routes&task=preview&tmpl=component', 
		{preview : true, polyline: $("#polyline_form").val(), polyline_encoded: $("#polyline_encoded").val() , json_waypoints: $("#json_waypoints_form").val()},
		function(data) {
			hash.w.css('opacity',0.88).show();
			$('#preview').html(data);
		});
	};

	$("#preview").jqm();
//{onShow: function(hash){ hash.w.css('opacity',0.88).show(); google.maps.event.trigger(preview_map, "resize"); preview()}});
});

</script>
 <body onload="initialize()">
<div class='ui-state-hover'>
	<table width='100%' style='table-layout: fixed;'>
		<tr>
			<td valign="top"><b>Actions:</b><br><input type='submit' value='Undo' onclick='undo()'>
							<input type='submit' value='Complete Track' onclick='complete_track()'>
							<input type='submit' value='Clear Track' onclick='clear_track()'>
							<input type='submit' value='Preview' onclick='' class="jqModal">
			<form action="<?= JURI::base(); ?>index.php?option=com_routes&task=preview" method='post'>
			<input type='hidden' name='waypoints_elevation_prev' id='json_waypoints_form'>
			<input type='hidden' name='polyline_encoded_prev' id='polyline_encoded'>
							<input type='submit' value='Chart' name='preview' onclick='save()'></form></td>
			<td valign="top"><div id='distance'></div><div id='test'></div></td>
		</tr>
	</table>
</div><br>
		<div id="map_canvas" style="width:100%; height:300px"></div><br>
<div id='base_url' style='display:none'><?= JURI::base(); ?></div>
<form action="<?= JURI::base(); ?>index.php?option=com_routes&task=postroute" method='post' id='form'>
			<input type='hidden' name='author_id' value="<?= $this->user_id; ?>">
			<input type='text' name='polyline' id='polyline_form'>
			<input type='text' name='json_waypoints' id='waypoints'>
			<input type='text' name='encoded' id='asdf'>
			<textarea name='distance' id='distance_form' class='required' style='width: 0px; height: 0px; position: absolute;'></textarea>
		<table>
			<tr>
				<td>Title: </td>
				<td><input type='text' name='title' class='required'></td>
			</tr>
			<tr>
				<td>Description: </td>
				<td><textarea type='text' name='description' class='required'></textarea></td>
			</tr>
			<tr>
				<td>Continent: </td>
				<td>
			<select onchange="select_continent(this.value)" class='required'>
					<option value="0">Select Continent</option>
					<option value="1">Africa</option>
					<option value="2">Asia</option>
					<option value="3">Australia and Oceania</option>
					<option value="4">Europe</option>
					<option value="5">North America</option>
					<option value="6">South America</option>
			</select>
				</td>
			</tr>
			<tr>
				<td>Country: </td>
				<td><select id='country' name='country' class='required'><option value=''>Select Country</option></select></td>
			</tr>
<tr>
				<td>Surface: </td>
				<td>
			<select name='surface' class='required'>
					<option value="">Select Surface</option>
					<option value="Paved">Paved</option>
					<option value="Unpaved">Unpaved</option>
					<option value="Gravel">Gravel</option>
					<option value="Downhill">Downhill</option>
			</select></td>
			</tr>
			<tr>
				<td>Category: </td>
				<td>
			<select name='category' class='required'>
					<option value="">Select Category</option>
					<option value="Suitable for all bicycles">Suitable for all bicycles</option>
					<option value="Suitable for mountain bikes">Suitable for mountain bikes</option>
					<option value="Suitable for downhill bikes">Suitable for downhill bikes</option>
			</select>
</td>
			</tr>
			<tr>
				<td>Difficulty (1-10): </td>
				<td>
			<select name='difficulty' class='required'>
				<? for($i=1; $i<=10; $i++): ?>
					<option value="<?= $i; ?>"><?= $i; ?></option>
				<? endfor; ?>
			</select>
</td>
			</tr>
			<tr>
				<td>Start Address: </td>
				<td><textarea name="start_address" class='required'></textarea></td>
			</tr>
			<tr>
				<td>End Address: </td>
				<td><textarea name="end_address" class='required'></textarea></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
			</tr>			
		</table>
		<input type='submit' name='submit' onclick='save()' value='Create'></form>

<div id='preview' class="jqmWindow" float='center'>
<a href="#" class="jqmClose">Close</a>
<hr>
<div id="preview_map_canvas" style="width:100%; height:300px"></div><br>
<div id="preview_chart_canvas" style="width:400px;"></div>
</div>

<div id='chart'></div>
