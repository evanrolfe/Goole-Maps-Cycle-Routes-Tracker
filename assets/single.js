var base_url = $("#base_url").text();
var route_id = $("#track_id").text();
var unit = 'km';

var gmap;

var chart;
var elevationService;
var mouse_marker = null;
var markers = [];

var route_data;

// Load the Visualization API and the piechart package.
google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(initialize);

function draw_markers(latlngs)
{
	for(var i=0; i<latlngs.length; i++){
		var icon = base_url+"components/com_routes/assets/icons/";
		if(i==0){
			icon += "icon_a.png";	
		}else if(i==(latlngs.length-1)){
			icon += "icon_b.png";	
		}else{
			icon += "icon_pin.png";	
		}

		markers.push(draw_marker(latlngs[i],icon));
	}
}

function draw_marker(position, icon){
	//Construct google maps marker
	var marker = new google.maps.Marker({
		position: position,
		map:gmap,
		icon: icon,
		draggable: false
	});

	return marker;
}

function initialize() {

	var parsed_data;

	$.ajax({
		url: base_url+"index.php?option=com_routes&task=json_single&id="+route_id,
		type: "GET",
		data: 	{ id : $("#track_id").text()},
		dataType: 'json',
		success: function(route){
			route_data = route;
			draw_page();

			$("#tabs").tabs({show : function(){ google.maps.event.trigger(gmap,'resize'); fit_map_to_route(); } });
		},
		error: function(data,status){
			alert("Error retrieving the route!"+status);
		}
	});	
	loadRating();
}

function draw_page(){
	//Create map and display it in div
	var myOptions = {
		zoom: 10,
		center: new google.maps.LatLng(53.385899,-1.493084),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	gmap = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
	
	//Extract the points from the json string into an array of latlngs

	var route = route_data;
	//Format Distance
		//1. Convert from meters to km
		route.distance = route.distance/1000;

		//2. Convert from km to miles
		if(unit == 'miles'){
			route.distance = route.distance*0.621371192;
			route.gain = route.gain*3.2808399;
		}

		//3. Truncate the result to 1 decimal place
		route.distance = route.distance.toFixed(1);
		route.gain = route.gain.toFixed(1);

		//Print distance to the page
		$("#distance").html("<b>Distance:</b> "+route.distance+" ("+unit+")");

		var gain_unit = (unit == 'km') ? 'm' : 'ft';

		//Print gain to the page
		$("#gain").html("<b>Gain:</b> "+route.gain+" ("+gain_unit+")");

	//1. Draw Polyline		
		var polyline = new google.maps.Polyline({
			path: google.maps.geometry.encoding.decodePath(route.polyline_encoded),
			strokeColor: "#0055FF",
			strokeOpacity: 0.5,
			strokeWeight: 5
		});

		polyline.setMap(gmap);

		var icon = base_url+"components/com_routes/assets/icons/";
	//2. Draw markers
		var wp_latlngs = [];
		var loc;


	for(var i=0; i<route.waypoints.length; i++){
		loc = new google.maps.LatLng(route.waypoints[i][0],route.waypoints[i][1]);

		if(i==0){
			draw_marker(loc, icon+"icon_a.png");
		}else if(i==(route.waypoints.length-1)){
			draw_marker(loc, icon+"icon_b.png");
		}

	}

	//3. Set bounds of map so it fits the route
	fit_map_to_route();

	//4. Draw elevation profile chart
	draw_chart(route);

	//5. "Draw" Breadcrumbs div
	var cont_code = route.continent.substring(0,2).toLowerCase();
	$("#breadcrumbs").html("<b><a href='"+base_url+"index.php?option=com_routes#continent_"+cont_code+"'>"+route.continent+"</a></b> >> <b><a href='"+base_url+"index.php?option=com_routes#country_"+route.country+"'>"+countries_arr[route.country]+"</a></b> >> <b>"+route.title+"</b>");
}

function draw_chart(route){
	var waypoints_elevations = route.elevations;
	// Set a callback to run when the Google Visualization API is loaded.

	//Units
	elevation_unit = (unit == 'miles') ? 'ft' : 'm';

//===============
//Format Data
//===============
	var distance_format = new google.visualization.NumberFormat({
		fractionDigits: 2,
		prefix: 'Distance: ',
		suffix: ' ('+unit+')'
	});

	var elevation_format = new google.visualization.NumberFormat({
		fractionDigits: 2,
		prefix: 'Elevation: ',
		suffix: ' ('+elevation_unit+')'
	});

	var index, elevation;
	var data = new google.visualization.DataTable();
	data.addColumn('number', 'Distance');
	data.addColumn('number', 'Elevation');
	data.addColumn({type:'string', role:'tooltip'});

		for(var i=0; i<waypoints_elevations.length; i++){
			//Calculate the distance along the path that this individual point is at
			index = (route.distance/(waypoints_elevations.length-1))*(i);
			elevation = waypoints_elevations[i][2];

			//Convert to feet/miles if necessary
			if(unit == 'miles'){
				elevation = elevation*3.2808399;
			}

			data.addRow([index, elevation, elevation_format.formatValue(elevation)]);
		}

	distance_format.format(data,0);
	elevation_format.format(data,1);

	var options = {
		title: 'Elevation Profile',
		focusTarget: 'category',
		curveType: 'function',
		interpolateNulls: true,
		//chartArea: { width: 650},
		//width: 800,
		tooltip: { trigger: 'hover', showColorCode: false, textStyle: { fontSize: 9} },
		legend: { position: 'none' },
		hAxis: {title: 'Distance ('+unit+')', titleTextStyle: {color: 'black'}},
		vAxis: {title: 'Elevation ('+elevation_unit+')', titleTextStyle: {color: 'black'}}
	};

	var chart = new google.visualization.LineChart(document.getElementById('chart_canvas'));
	chart.draw(data, options);

	//Add the mousover function
	var mouse_marker = null;

    google.visualization.events.addListener(chart, 'onmouseover', function(e) {
		var location = new google.maps.LatLng(waypoints_elevations[e.row][0],waypoints_elevations[e.row][1]);


		if(mouse_marker){
			mouse_marker.setPosition(location);
		}else{
			mouse_marker = new google.maps.Marker({
			position: location,
			map:gmap,
			icon: base_url+"components/com_routes/assets/icons/icon_bicycle.png",

			draggable: false,
			});
		}
	});

    google.visualization.events.addListener(chart, 'onmouseout', function(e) {
		mouse_marker.setMap(null);
		mouse_marker = null;
	});
}

function fit_map_to_route(){
	var route = route_data;

	//3. Set bounds of map so it fits the route
	var bounds = new google.maps.LatLngBounds();
	var point;

	for(var i=0; i<route.waypoints.length; i++){
		point = new google.maps.LatLng(route.waypoints[i][0],route.waypoints[i][1]);
		bounds.extend(point);
	}

	gmap.fitBounds(bounds);
}

function change_unit(){
	//alert('hello world');//$("#track_id").text());
	//alert(base_url+"index.php?option=com_routes&task=json_single&id="+route_id);
	unit = (unit == 'km') ? 'miles' : 'km';
	initialize();
}

function loadRating()
{
	var url = $("#base_url").text()+"index.php?option=com_routes&task=json_avg_rating&route_id="+$("#track_id").text();
	
	$.ajax({
		url: url,
		type: "GET",
		dataType: 'json',
		success: function(data){
			if(data.status=='success'){
				$("#avg_rate").html("<b>Avg rating: </b>"+data.avg+"/5");
			}else if(data.status=='not_rated'){
				$("#avg_rate").html("Not yet rated");				
			}else{
				$("#avg_rate").html("Not yet rated");
			}
		},
		error: function(data){
			alert("Error: could not obtain the rating of this track!");								
		}
	});					
}
