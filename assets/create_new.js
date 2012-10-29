var gmap;

var directionsService;
var elevationService = new google.maps.ElevationService();

//"Instance" variables that define a map (independent vars)
var waypoints = [];				//These include the origin and destination
var track_complete = false; 	//If set to true then no further points can be drawn on the map
var polyline_latlngs = [];		//All points that make up the polyline between the waypoints
var distance = 0;

//Overlay variables (these are derived/dependent on the instance vars)
var overlays = [];

var unit = 'km';
var history = [];

var base_url = $("#base_url").text();



//======================================================
//
// CONTINENT/COUNTRY FUNCTIONS
//
//======================================================
//This function is called when the user selects a continent and updates the country dropdown to the corresponding countries
function select_continent(id){
	var options;

	switch(id){
		case 'Africa':		//Africa
			options = africa;
		break;

		case 'Asia':		//Asia
			options = asia;
		break;

		case 'Australia':		//Australia
			options = australia;
		break;

		case 'Europe':		//Europe
			options = europe;
		break;

		case 'North America':		//North America
			options = north;
		break;

		case 'South America':		//South America
			options = south;
		break;
	}
	$("#country").empty();
	$("#country").append(options);
}

function select_country(code){
	// Center map on the country
	var geocoder = new google.maps.Geocoder();

	geocoder.geocode( { 'address': countries_arr[code]}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			gmap.setCenter(results[0].geometry.location);
			gmap.fitBounds(results[0].geometry.viewport);
		}
	});

	$("#create_form").show();
}
//======================================================
//
// UNDO FUNCTIONS
//
//======================================================

//Save the current map state to the history array
function save_to_history(){
	var state = new Object();

	if(waypoints.length > 0){
		state.distance = distance;

		//Make a deep copy of each waypoint and polyline_latlngs
		state.waypoints = $.merge([],waypoints);
		state.polyline_latlngs = $.merge([],polyline_latlngs);

		state.track_complete = track_complete;
		
	}else{
		state.waypoints = [];
		state.polyline_latlngs = [];
		state.track_complete = false;
	}

	//DISTANCE:
	draw_distance();

	history.push(state);
}

//Restore the previous state from the history array
function undo()
{
	history.pop();
	var prev_state = history[history.length-1];

	//If there is a previous state then render it
	if(prev_state){

		//1. Clear overlays
		clear_overlays();

		//2. Restore instance variables
		distance = prev_state.distance;
		waypoints = prev_state.waypoints;
		track_complete = prev_state.track_complete;
		polyline_latlngs = prev_state.polyline_latlngs;

		//3. Draw Polyline
		draw_polyline();
		
		//4. Draw Markers
		draw_markers();
		
	//Otherwise clear the map
	}else{
		waypoints = [];
		polyline_latlngs = [];
		track_complete = false;
		distance = 0;
		clear_overlays();
	}

	//DISTANCE:
	draw_distance();
}

function debug_history(){
	var str = "";

	for(var i=0; i<history.length; i++){
		str += i+") #Waypoints: "+history[i].waypoints.length+"<br> #Points in polyline: "+history[i].polyline_latlngs.length+"<br>Distance: "+history[i].distance+"<hr>";
	}

	$("#test").html(str);
}
//======================================================
//
// OVERLAY FUNCTIONS
//
//======================================================

	//Used in conjunction with create.js (cannot be used alone)
	function draw_marker(position,icon){
		var image = new google.maps.MarkerImage(base_url+"components/com_routes/assets/icons/"+icon,
			new google.maps.Size(40, 37),						// image size
			new google.maps.Point(0,0),							// The origin for this image is 0,0.
			new google.maps.Point(10, 34));						// The anchor for this image


		//Construct google maps marker
		var marker = new google.maps.Marker({
			position: position,
			map:gmap,
			icon: image,
			draggable: true
		});

		//Add it to the overlays array so it can be cleared if needed to
		overlays.push(marker);

		//$("#text").html(marker.position.toString());

		//Make a deep copy of the marker's original latlng object
		var original_latlng = $.extend(true, {}, marker.position);


		//Add the listener to update the route if the marker is dragged
		google.maps.event.addListener(marker, 'dragend', function(event){
			update_point(original_latlng, event.latLng);
		});
	}

	function draw_markers(){
		if(waypoints.length == 1){
			draw_marker(waypoints[0], "icon_a.png");						//ORIGIN
		}else{
			draw_marker(waypoints[0], "icon_a.png");						//ORIGIN
			draw_marker(waypoints[waypoints.length-1], "icon_b.png");		//DESTINATION
			for(var i=1; i<(waypoints.length-1); i++){						//INBETWEEN WAYPOINTS
				draw_marker(waypoints[i], "icon_pin.png");
			}
		}
	}

	function draw_polyline(){
		var polyline = new google.maps.Polyline({
			path: polyline_latlngs,
			strokeColor: "#0055FF",
			strokeOpacity: 0.5,
			strokeWeight: 5
		});

		polyline.setMap(gmap);

		overlays.push(polyline);
	}

	//Clears the overlays on the map
	//But leaves the instance variables origin,destination,waypoints unchanged
	function clear_overlays()
	{
		//Clear the overlays
		for(var i=0; i<overlays.length; i++){
			overlays[i].setMap(null);
		}
		overlays = [];
	}

//======================================================
//
// ROUTE DRAWING FUNCTIONS
//
//======================================================
function add_segment(point1, point2){
	directionsService = new google.maps.DirectionsService();

	//Parameters for the directions service request
	var request = {
		origin: point1,
		destination: point2,
		travelMode: google.maps.TravelMode.WALKING
	};

	//Send the request to google api
	directionsService.route(request, function(result, status) {
		if (status == google.maps.DirectionsStatus.OK) {

			//1. Push the polyline latlngs to the polyline_latlngs array
			polyline_latlngs = $.merge(polyline_latlngs, result.routes[0].overview_path);

			//2. Draw the entire polyline (not just this one segment)
			draw_polyline();

			//3. Add the distance of this segment to the total distance of the track
			for(var i=0; i<result.routes[0].legs.length; i++){
				distance += result.routes[0].legs[i].distance.value;
			}

			//4. Now that we have the polyline we can save this state to history
			save_to_history();

		}else{
			alert("Error getting the directions from google API: "+status);
		}
	});

}

//======================================================
//
// DATA PARSING FUNCTIONS (EXTRACT ROUTE DATA FOR DATABASE)
//
//======================================================

function draw_distance(){
	distance_km = distance/1000;
	distance_miles = distance_km*0.621371192;

	d = (unit == 'km') ? distance_km : distance_miles;

	$("#distance_display").html("Distance: "+d.toFixed(1)+" "+unit);
	$("#distance").val(distance);
}

//This function extracts the relevant data from the route that has been plotted on the map
//in order to be stored in the mysql database
function extract_data(){
	//1. Waypoints
	waypoints_arr = [];
	for(var i=0; i<waypoints.length; i++){
		waypoints_arr.push([waypoints[i].lat(), waypoints[i].lng()]);
	}
	$("#waypoints").val(JSON.stringify(waypoints_arr));

	//2. Encode Polyline
	var encoded = google.maps.geometry.encoding.encodePath(polyline_latlngs);
	encoded = encoded.replace(/\\/g,"\\\\");

	$("#polyline_encoded").val(encoded);

	//3. Get elevations
	var points = distribute_reduction(polyline_latlngs, 400);
	var options = { locations : points };

	elevationService.getElevationForLocations(options, function(results, status) {
		//If the call to google elevation service was successful
		if (status == google.maps.ElevationStatus.OK) {
			//2. Format the results to a json array of from [[latitude,longitude,elevation]]
			var output_arr = [];

			for(var i=0; i<results.length; i++){
				output_arr.push([	results[i].location.lat(),
									results[i].location.lng(),
									results[i].elevation]);
			}

			$("#elevations").val(JSON.stringify(output_arr));

			$("#form").submit();
		}else{
			alert("Error: could not retreive the elevations from google elevations service! Please refresh and try again.\n"+status);
		}
	});
}

function save(){
	//Validate that the user has selected a route on the map
	if(waypoints.length < 2){
		alert("Please select a route by clicking on the map.");
	}else{
		extract_data();
	}
}
//======================================================
//
// ELEVATION FUNCTIONS
//
//======================================================

function distribute_reduction(array, limit, count){
	if(!count)
		count = 0;

	if(array.length < limit){
		return array;
	}else{
		//1. Remove Every 10th element
		for(var i=0; i<array.length; i++){
			if((i % 2) == 0)
				array.splice(i,1);
		}

		//2. Recursively call this function again
		count++;
		//alert("Recursively calling distribute_reduction for the "+count+" th time");
		return distribute_reduction(array, limit, count);
	}
}

function get_elevation_for_points(points){


}

//======================================================
//
// MAP EVENT/INTERACTION FUNCTIONS
//
//======================================================
function change_unit(){
	unit = (unit == 'km') ? 'miles' : 'km';
	draw_distance();
}

//Transform an array into a cuircuit
//Example circuit_array([A,B,C,D])
//returns [A,B,C,D,C,B,A]
function circuit_array(array){
	//Make a deep copy of the array
	var new_arr = [];
	for(var i=array.length-2; i>=0; i--){
		new_arr.push(array[i]);
	}
	return $.merge(array,new_arr);
}

//Completes the track as a circuit
function complete_track(){
	clear_overlays();

	track_complete = true;
	waypoints = circuit_array(waypoints);

	draw_polyline();
	draw_markers();

	save_to_history();
}

function clear_track(){
	track_complete = false;
	waypoints = [];
	polyline_latlngs = [];

	clear_overlays();

	save_to_history();
}

//This function is called when the user clicks to extend the route to a new waypoint
function add_waypoint(latlng){
	clear_overlays();

	waypoints.push(latlng);

	//If only origin has been set
	if(waypoints.length < 2){
		//Draw origin marker
		draw_marker(waypoints[0],"icon_a.png");
		save_to_history();

	}else{
		//Draw all markers
		draw_markers(waypoints);

		//Add this segment to the polyline
		add_segment(waypoints[waypoints.length-2], waypoints[waypoints.length-1]);
	}	
}

function initialize() {
	//1. Draw Map in Preview Div
	//Create map and display it in div
	var myOptions = {
		zoom: 1,
		center: new google.maps.LatLng(46.151241,14.995462999999972),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};


	gmap = new google.maps.Map(document.getElementById('map_canvas'),myOptions);

	//When the map is clicked:
	google.maps.event.addListener(gmap, 'click', function(event) {
		//First check that the track is not complete
		if(track_complete){
			alert("You have already completted the track. Press undo if you wish to add more waypoints.");
		}else{
			add_waypoint(event.latLng);
		}
	});
}
