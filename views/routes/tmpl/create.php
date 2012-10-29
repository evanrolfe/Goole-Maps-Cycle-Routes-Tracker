<?php defined('_JEXEC') or die('Restricted access'); 
$asset_url = JURI::base()."components/com_routes/assets/"; ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=geometry&sensor=false&key=AIzaSyCsCpXNCbwxziCgcOWdriOFGFMYre4ZeuI"></script>
<link type="text/css" href="<?php echo $asset_url; ?>jquery-ui-1.8.20.custom.css" rel="Stylesheet" />	
<link type="text/css" href="<?php echo $asset_url; ?>jquery-ui-1.8.21.custom.css" rel="Stylesheet" />	
<script type="text/javascript" src="<?php echo $asset_url; ?>jquery.validate.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>countries.js"></script>


<script type="text/javascript" src="<?php echo $asset_url; ?>create_new.js"></script>

<body onload="initialize()">
<div id='base_url' style='display:none'><?php JURI::base(); ?></div>

<textarea id='waypoints_db'></textarea>
<textarea id='elevations_db'></textarea>


<div width="100%">
	<a href="<?php echo JURI::base(); ?>index.php?option=com_routes">
		<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">
			<span style="padding: 10px; font-weight: bold">
				View all routes
			</span>
		</button>
	</a>
	<button onclick='change_unit()' class="ui-button ui-state-default ui-corner-all ">
		<span style="padding: 10px">Change Unit</span>
	</button>
</div>

<div class='ui-state-hover'>
	<table width='100%' style='table-layout: fixed;'>
		<tr>
			<td valign="top"><b>Actions:</b><br>
	<button class="ui-button ui-state-default ui-corner-all" onclick="undo()">
		<span style="padding: 10px">Undo</span>
	</button>
	<button class="ui-button ui-state-default ui-corner-all" onclick="clear_track();">
		<span style="padding: 10px">Clear Track</span>
	</button>
	<button class="ui-button ui-state-default ui-corner-all" onclick="complete_track()">
		<span style="padding: 10px">Complete Track</span>
	</button>
<!--						
<input type='submit' value='Undo' onclick='undo()'>
<input type='submit' value='Complete Track' onclick='complete_track()'>
							<input type='submit' value='Clear Track' onclick='clear_track()'>
							<input type='submit' value='Preview' class="jqModal" id='preview_submit' style='display: none'> -->
      </td>
			<td valign="top"><div id='distance'></div><div id='test'></div></td>
		</tr>
	</table>
</div><br>
<form action="<?php echo JURI::base(); ?>index.php?option=com_routes&task=postroute" method='post' id='form'>
Please select which continent and country your route is in:<br>
<select onchange="select_continent(this.value)" class='required' name='continent'>
		<option value="0">Select Continent</option>
		<option value="Africa">Africa</option>
		<option value="Asia">Asia</option>
		<option value="Australia">Australia and Oceania</option>
		<option value="Europe">Europe</option>
		<option value="North America">North America</option>
		<option value="South America">South America</option>
</select><select id='country' onchange="search_country($(this).val())" name='country' class='required'><option value=''>Select Country</option></select>
		<div id="map_canvas" style="width:100%; height:300px"></div><br>


<!-- 
			<input type='text' name='polyline' id='polyline_form'>
			<input type='text' name='json_waypoints' id='waypoints'>
			<input type='text' name='encoded' id='asdf'>
			<textarea name='distance' id='distance_form' class='required' style='width: 0px; height: 0px; position: absolute;'></textarea>

//-->


			<input type='hidden' name='polyline_encoded' id='polyline_encoded'>
			<input type='hidden' name='waypoints_elevations' id='waypoints_elevations'>
			<input type='hidden' name='circuit' id='circuit'>
			<textarea name='distance' id='distance_form' class='required' style='width: 0px; height: 0px; position: absolute;'></textarea>

<div id='create_form' style='display: none'>
					<table cellspacing='5px'>
						<tr>
							<td>Title: </td>
							<td><input type='text' name='title' class='required' size='25'></td>
						</tr>
						<tr><td></td></tr>
						<tr>
							<td>Description: </td>
							<td><textarea type='text' name='description' class='required track-desc' rows="4" cols="60"></textarea></td>
						</tr>
						<tr>
							<td valign='top'>Surface: </td>
							<td>
							<input type="checkbox" name="surface_paved"> Paved<br>
							<input type="checkbox" name="surface_unpaved"> Unpaved<br>
							<input type="checkbox" name="surface_gravel"> Gravel<br>
							<input type="checkbox" name="surface_downhill"> Downhill
						</td>
						</tr>
						<tr><td></td></tr>
						<tr>
							<td valign='top'>Suitable for:</td>
						<td>
							<input type="checkbox" name="category_all"> All bicycles<br>
							<input type="checkbox" name="category_mountain"> Mountain bikes<br>
							<input type="checkbox" name="category_downhill"> Downhill bikes
						</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
						</tr>			
					</table>
					<!-- <input type='submit' name='submit' value='Create'> -->
	<button class="ui-button ui-state-default ui-corner-all" name="submit" type="submit">
		<span style="padding: 10px">Save</span>
	</button>

	</form>
</div>
