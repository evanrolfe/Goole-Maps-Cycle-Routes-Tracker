var base_url = $("#base_url").text();
var unit = 'km';

var gmap;
var directionsService = new google.maps.DirectionsService();

var history = [];

//"Instance" variables that define a map (independent vars)
var origin;
var waypoints = [];
var destination;

//These will be generated from the "instance" vars everytime the map is refresh (dependent vars)
var overlays = [];				//Only used for clearing markers
var directionsRenderers = [];	//Only used for clearing routes

var directionsPolyline = []; 					//used to export the route drawn
var distance=0;

//Elevation service
var elevationService = new google.maps.ElevationService();

//Preview map
var preview_map;

google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(initialize);

//Save the current map state to the history array
function save_to_history(){
	var state = new Object();

	if(origin){
		state.origin = new google.maps.LatLng(origin.lat(), origin.lng());

		if(destination)
			state.destination = new google.maps.LatLng(destination.lat(), destination.lng());

		//Make a deep copy of each waypoint
		state.waypoints = []
		for(var i=0; i<waypoints.length; i++){
			state.waypoints.push(new google.maps.LatLng(waypoints[i].lat(),waypoints[i].lng()));
		}
	}else{
		state.origin = null;
		state.destination = null;
		state.waypoints = [];
	}
	history.push(state);
}

//Restore the previous state from the history array
function undo()
{
	history.pop();
	var prev_state = history[history.length-1];

	//If there is a previous state then render it
	if(prev_state){
		origin = prev_state.origin;
		destination = prev_state.destination;
		waypoints = prev_state.waypoints;
		refresh_map(true);

		if(!history[history.length-2]){
			clear_track();
		}
	//Otherwise clear the map
	}else{
		clear_track();
	}
}

//Clears the overlays and sets the instance variables to null
function clear_track(){
	origin = null;
	destination = null;
	waypoints = [];
	distance = 0;
	$("#distance").html("Distance: 0 "+unit);
	refresh_map();
}

//This function is called whenever a change to the map is made i.e. whenever the map is clicked or Undo/Clear track is called
//Dependent on the variables: origin,destination,waypoints and dependent on whether or not it has been called from an undo function call
function refresh_map(undo){
	//1. Clear the overlays
	clear_overlays()
		$("#preview_submit").hide();
	//2. Draw origin marker if only the origin is set
	if(origin && !destination && waypoints.length == 0){
		draw_marker(origin,"icon_a.png");

	//3. Draw origin and destination marker and route between them if origin and destination are set but waypoints are not set
	}else if(origin && destination && waypoints.length == 0){
		draw_marker(origin,"icon_a.png");
		draw_marker(destination,"icon_b.png");
		draw_route([origin,destination]);
		$("#preview_submit").show();
	//4. Otherwise draw origin,destination,waypoints marker and route through them
	}else if(origin && destination && waypoints.length > 0){
		var route = get_route_array();				//Get the entire route array (includes origin,waypoints,destination)

		draw_marker(origin,"icon_a.png");			//Draw the origin marker
		draw_marker(destination,"icon_b.png");		//Draw the destination marker

		for(var i=0; i<waypoints.length; i++){  	//Draw waypoint markers
			draw_marker(waypoints[i], "icon_pin.png");
		}

		draw_route(route);							//Draw the route
		$("#preview_submit").show();
	}

	//5. Save to the history unless refresh has been called by the undo function
	if(!undo)
		save_to_history()

	//6. Update the hidden form fields of polyline
	update_form();
}

function update_form(){
	//If a route has been selected
	if(origin && destination){
		//1. update the elevations field
		set_waypoints_elevations_form();
	}else{
		//Otherwise set both fields blank
		$("#waypoints_elevations").val('');
		$("#polyline_encoded").val('');
		$("#distance_form").val('');

		$("#distance").html("Distance: 0 "+unit);
	}
}

function change_unit(){
	unit = (unit == 'km') ? 'miles' : 'km';
	refresh_map(true);
}

function preview(){

	url = base_url+"index.php?option=com_routes&task=preview&tmpl=component";
	$.ajax({
		url: url,
		type: 'post',
		data: {polyline_encoded: $("#polyline_encoded").val(), waypoints_elevations: $("#waypoints_elevations").val()},
		dataType: 'html',
		success: function(data) {
			myWindow = window.open('','','resizable=yes,scrollbars=yes,width=700,height=700');
			myWindow.document.body.innerHTML = data;
		},
		error: function(data) {
			alert("There was an error generating the preview!");
		}
	});


}

//Complete the track in a circuit following the same route back
function complete_track(){
	if(waypoints.length > 0){
		//1.push the destination onto the waypoints
		waypoints.push(destination);
		waypoints = circuit_array(waypoints);
		destination = origin;
	}else{
		waypoints.push(destination);
		destination = origin;
	}

	refresh_map();
}

function draw_preview_chart(){
	var waypoints_elevations = JSON.parse($("#waypoints_elevations").val());
	// Set a callback to run when the Google Visualization API is loaded.
	var elevations = [];
	elevations.push(['Distance','Elevation']);
	var index;
	//var distance_km = route.distance/1000;
	for(var i=0; i<waypoints_elevations.length; i++){
		index = (distance/(waypoints_elevations.length-1))*(i);
		elevations.push([(index/1000),waypoints_elevations[i][2]]);
	}


	var data = google.visualization.arrayToDataTable(elevations);

	var options = {
		title: 'Elevation Profile',
		focusTarget: 'category',
		curveType: 'function',
		interpolateNulls: true,
		//chartArea: { width: 650},
		//width: 800,
		tooltip: { trigger: 'none' },
		legend: { position: 'none' },
		hAxis: {title: 'Distance (km)', titleTextStyle: {color: 'black'}},
		vAxis: {title: 'Elevation (m)', titleTextStyle: {color: 'black'}}
	};

	var chart = new google.visualization.LineChart(document.getElementById('preview_chart_canvas'));
	chart.draw(data, options);
}

function initialize() {
	//1. Draw Map in Preview Div
	//Create map and display it in div
	var myOptions = {
		zoom: 1,
		center: new google.maps.LatLng(53.385899,-1.493084),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};


	gmap = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
	$("#distance").html("Distance: 0 "+unit);

	//Draw the route that has already been created
	var waypoints_elevations = JSON.parse($("#waypoints_elevations").val());
	

	origin = new google.maps.LatLng(waypoints_elevations[0][0],waypoints_elevations[0][1]);
	for(var i=1; i<waypoints_elevations.length-1; i++){
		waypoints.push(new google.maps.LatLng(waypoints_elevations[i][0],waypoints_elevations[i][1]));
	}
	destination = new google.maps.LatLng(waypoints_elevations[waypoints_elevations.length-1][0],waypoints_elevations[waypoints_elevations.length-1][1]);
	refresh_map();


	//Zoom in on route
	var bounds = new google.maps.LatLngBounds();
	bounds.extend(destination);
	bounds.extend(origin);
	for(var i=0; i<waypoints.length; i++){
		bounds.extend(waypoints[i]);
	}

	gmap.fitBounds(bounds);

	//Update countries dropdown
	$("#country").append("<option value=''>-----------------Africa------------------------</option>");
	$("#country").append(africa);
	$("#country").append("<option value=''>-----------------Asia--------------------------</option>");
	$("#country").append(asia);
	$("#country").append("<option value=''>-----------------Australia---------------------</option>");
	$("#country").append(australia);
	$("#country").append("<option value=''>-----------------Europe------------------------</option>");
	$("#country").append(europe);
	$("#country").append("<option value=''>-----------------North America-----------------</option>");
	$("#country").append(north);
	$("#country").append("<option value=''>-----------------South America-----------------</option>");
	$("#country").append(south);

	$("#country").val($("#country_code").html());

/*
	//When the map is clicked do:
	google.maps.event.addListener(gmap, 'click', function(event) {

		//If neither dest nor origin nor waypoints have been set then set the origin
		if(!destination && !origin){
			origin = event.latLng;
			refresh_map();

		//if origin has been set but not the dest then set the dest
		}else if(!destination && origin){
			destination = event.latLng;
			refresh_map();

		//otherwise extend the route
		}else if(destination && origin){
			waypoints.push(destination);
			destination = event.latLng;
			refresh_map();
		}
	});
*/
}
