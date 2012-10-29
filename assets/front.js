var base_url = $("#base_url").text();
var unit = 'km';

var gmap;
var cluster;

var chart;
var elevationService;
var mouse_marker = null;

var markers = [];
var overlays = [];
var polylines = [];

var bounds;
var data_retrieved = false;

var table;

var location_continent;
var location_country;

var colors = ['9966CC', 'A32638', '00FFFF', 'FFFF31'];

var continent_bounds = [
[[13.934251672484372, -25.02991871679683],[11.361754319228133, 54.24742503320317],[-36.164334426730306, 25.94664378320317],[35.898104172792806, -5.1666374667968284]], //0.Africa
[[0.8900468628967868, 79.73570628320317],[66.02256039952397, -171.98304371679683],[59.091480024699436, 51.25914378320317]],		//1. Asia	
[[7.024760574560512, 98.89586253320317],[5.802016079063261, -169.17054371679683],[-52.475973262624066, 164.81383128320317]],		//2. Australia	
[[70.90592463442097, 26.47398753320317],[66.02256039952398, -23.62366871679683],[36.04036957102038, -6.2213249667968284]],		//3. Europe	
[[70.2632266147852, -159.50257496679683],[47.52474835510831, -53.33069996679683],[8.765840731203976, -80.40101246679683]],		//4. North America
[[13.250824592879619, -69.50257496679683],[-6.47887842120482, -34.69788746679683],[-56.163800231668596, -68.62366871679683],[-9.264591730225606, -88.13538746679683]] //5.South America		
];

function calcWeight(zoom){
	if(zoom < 3){
		return 1;

	}else if(zoom < 7){
		return 3.5;

	}else if(zoom < 12){
		return 5;
	}else{
		return 7;
	}
}

function draw_polyline(route, color){
	var polyline = new google.maps.Polyline({
		path: google.maps.geometry.encoding.decodePath(route.polyline_encoded),
		strokeColor: "#"+color,
		strokeOpacity: 0.7,
		strokeWeight: calcWeight(gmap.getZoom()),
		route_id: route.id,
		original_color: "#"+color
	});


	polyline.setMap(gmap);
	polylines.push(polyline);

	//MOUSEOVER => highlight polyline and change marker colors
	google.maps.event.addListener(polyline, 'mouseover', function(event) {
		highlight_route(route.id);
	});

	//MOUSEOUT => de-highlight and change marker colors back to original
	google.maps.event.addListener(polyline, 'mouseout', function(event) {
		de_highlight_route(route.id);
	});


	//CLICK => open info window
	google.maps.event.addListener(polyline, 'click', function(event) {
		var gain_unit = (unit == 'km') ? 'm' : 'ft';
		var url = base_url+'index.php?option=com_routes&task=show&id='+route.id;
		var content = "<b>"+route.title+"</b><br>Distance: "+(route.distance)+" "+unit+"<br>Gain: "+(route.gain)+" "+gain_unit+"<br>Difficulty: "+route.difficulty+"<br><a href='"+url+"'>View Details</a>";
		var options = {content: content,
						position: event.latLng};

		var info = new google.maps.InfoWindow(options);
		info.open(gmap);
	});
}

function highlight_route(route_id){
		//1. Find markers belonging to this route
		for(var i=0; i<markers.length; i++){
			if(markers[i].route_id == route_id)

				markers[i].setIcon(base_url+"components/com_routes/assets/icons/black_"+markers[i].letter+".png");
		}

		//2. Find polyline for this route and highlight it
		for(var i=0; i<polylines.length; i++){
			if(polylines[i].route_id == route_id)
				polylines[i].setOptions({strokeColor: 'black'});
		}
}

function de_highlight_route(route_id){
		//1. Find markers belonging to this route
		for(var i=0; i<markers.length; i++){
			if(markers[i].route_id == route_id)
				markers[i].setIcon(base_url+"components/com_routes/assets/icons/"+markers[i].original_color.slice(0,7)+"_"+markers[i].letter+".png");
		}

		//2. Find polyline for this route and highlight it

		for(var i=0; i<polylines.length; i++){
			if(polylines[i].route_id == route_id)
				polylines[i].setOptions({strokeColor: polylines[i].original_color});
		}
}


function draw_marker(position, icon, route_id, letter, color,route){
	//alert(position);

	//Construct google maps marker
	var marker = new google.maps.Marker({
		position: position,
		map:gmap,
		icon: icon,
		draggable: false,
		route_id: route_id,
		letter: letter,
		original_color: color
	});

	markers.push(marker);
	//bounds.extend(position);
	cluster.addMarker(marker);

	//MOUSEOVER => highlight polyline and change marker colors
	google.maps.event.addListener(marker, 'mouseover', function(event) {
		highlight_route(route_id);
	});

	//MOUSEOUT => de-highlight and change marker colors back to original
	google.maps.event.addListener(marker, 'mouseout', function(event) {
		de_highlight_route(route_id);
	});

	//CLICK => open info window
	google.maps.event.addListener(marker, 'click', function(event) {
		var gain_unit = (unit == 'km') ? 'm' : 'ft';
		var url = base_url+'index.php?option=com_routes&task=show&id='+route.id;
		var content = "<b>"+route.title+"</b><br>Distance: "+(route.distance)+" "+unit+"<br>Gain: "+(route.gain)+" "+gain_unit+"<br>Difficulty: "+route.difficulty+"<br><a href='"+url+"'>View Details</a>";
		var options = {content: content,
						position: event.latLng};

		var info = new google.maps.InfoWindow(options);
		info.open(gmap);
	});

	return marker;
}

function get_route_by_id(id){
	var route;
	for(var i=0; i<data_retrieved.length; i++){
		if(data_retrieved[i].id == id){
			route = data_retrieved[i];
		}
	}

	return route;
}

function polyline_with_route_id_exists(id){
	for(var i=0; i<polylines.length; i++){
		if(polylines[i].route_id == id)
			return true;
	}

	return false;
}

function route_markers_visible(id){
	for(var i=0; i<markers.length; i++){
		if(markers[i].route_id == id && markers[i].getMap() == gmap)
			return true;
	}

	return false;
}

function draw_routes(routes){


	//1. Draw the markers
	for(var i=0; i<routes.length; i++){
		var origin = new google.maps.LatLng(routes[i].waypoints[0][0],routes[i].waypoints[0][1]);
		var destination = new google.maps.LatLng(routes[i].waypoints[routes[i].waypoints.length-1][0],routes[i].waypoints[routes[i].waypoints.length-1][1]);
		var color = colors[(i % 4)];

		draw_marker(origin,base_url+"components/com_routes/assets/icons/"+color+"_a.png", routes[i].id, 'a', color, routes[i]);		//Draw origin
		draw_marker(destination,base_url+"components/com_routes/assets/icons/"+color+"_b.png", routes[i].id, 'b', color, routes[i]);		//Draw origin
	}

	google.maps.event.addListener(cluster,"clusteringend", function(c){
		//2. Draw the polylines
		for(var i=0; i<markers.length; i++){
			//only draw if a polyline for this route_id has not already been drawn and if the routes markers are shown in the cluster
			if(!polyline_with_route_id_exists(markers[i].route_id) && route_markers_visible(markers[i].route_id)){
				route = get_route_by_id(markers[i].route_id);
				draw_polyline(route, markers[i].original_color);
				//alert("Drawing a polyline in "+route.country+"\n Route markers visible: "+route_markers_visible(markers[i].route_id));
			}
		}
	});

/*
		//Only draw the polyline if the origin and destination markers are visible
		//i.e. if marker.getMap() != null
		if(origin_marker.getMap() && dest_marker.getMap()){
			draw_polyline(routes[i],"#"+color);
		}
*/

}

function clear_polylines(){
	for(var i=0; i<polylines.length; i++){
		polylines[i].setMap(null);
	}	
	polylines = [];
}

function clear_markers(){
	for(var i=0; i<markers.length; i++){
		markers[i].setMap(null);
	}	
	markers = [];
}

function initialize() {
	//Create map and display it in div
	var myOptions = {
		zoom: 2,
		center: new google.maps.LatLng(46.151241,14.995462999999972),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	gmap = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
	
	cluster = new MarkerClusterer(gmap, [],{maxZoom: 4});

	query_routes_url(base_url+"index.php?option=com_routes&task=json");

	google.maps.event.addListener(gmap, 'zoom_changed', function(event) {
		//query_routes_url(base_url+"index.php?option=com_routes&task=json");
		draw_everything(true);
	});
}

function query_routes_url(url)
{
	clear_polylines();
	clear_markers();
	cluster.clearMarkers();
	//bounds = new google.maps.LatLngBounds();
		$.ajax({
			url: url,
			type: "GET",
			//data: 	{ id : $("#track_id").text()},
			dataType: 'json',
			success: function(routes){
				data_retrieved = routes;
				draw_everything();
			},
			error: function(data,status){
				alert("Error retrieving the route!"+status);
			}
		});	
}

function draw_everything(redraw){
	//Remove the markers from the cluster because they will be added again
	cluster.removeMarkers(markers);

	//Remove all polylines/markers because they will be drawn again
	clear_polylines();
	clear_markers();

	if(!redraw){
		//Convert to miles if necessary
		for(var i=0; i<data_retrieved.length; i++){
			//1. Convert from meters to km
			data_retrieved[i].distance = data_retrieved[i].distance/1000;

			//2. Convert from km to miles
			if(unit == 'miles'){
				data_retrieved[i].distance = data_retrieved[i].distance*0.621371192;

				//Convert meters to feets
				data_retrieved[i].gain = data_retrieved[i].gain*3.2808399;
			}

			//3. Truncate the result to 1 decimal place
			data_retrieved[i].distance = parseFloat(data_retrieved[i].distance).toFixed(1);
			data_retrieved[i].gain = parseFloat(data_retrieved[i].gain).toFixed(1);
		}
	}
	

	draw_routes(data_retrieved);
	//gmap.fitBounds(bounds);
}
