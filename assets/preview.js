var base_url = $("#base_url").text();
var route_id = $("#track_id").text();
var unit = 'km';

var gmap;

var chart;
var elevationService;
var mouse_marker = null;
var markers = [];

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

function draw_chart(input, elevation_data){
	var data = google.visualization.arrayToDataTable(input);
	var vunit = (unit == 'miles') ? 'ft' : 'm';
	var options = {
		focusTarget: 'category',
		curveType: 'function',
		interpolateNulls: true,
		//chartArea: { width: 650},
		//width: 800,
		tooltip: { trigger: 'none' },
		legend: { position: 'none' },
		hAxis: {title: 'Distance ('+unit+')', titleTextStyle: {color: 'black'}},
		vAxis: {title: 'Elevation ('+vunit+')', titleTextStyle: {color: 'black'}}
	};

	var chart = new google.visualization.LineChart(document.getElementById('chart_canvas'));
	chart.draw(data, options);

	//Add the mousover function
	var mouse_marker = null;

    google.visualization.events.addListener(chart, 'onmouseover', function(e) {
		var location = new google.maps.LatLng(elevation_data[e.row][0][0],elevation_data[e.row][0][1]);


		if(mouse_marker){
			mouse_marker.setPosition(location);
		}else{
			mouse_marker = new google.maps.Marker({
			position: location,
			map:gmap,
			icon: base_url+"components/com_routes/assets/icons/icon_bicycle.png",
			draggable: false
			});
		}


/*
		for(var i=0; i<markers.length; i++){
			if(markers[i].equals()){
				mouse_marker = markers[i];
				break;
			}
		}

		alert(mouse_marker.getPosition());
*/
	});

    google.visualization.events.addListener(chart, 'onmouseout', function(e) {
		mouse_marker.setMap(null);
		mouse_marker = null;
		//mouse_marker.setAnimation(null);
		//mouse_marker = null;
	});
}

function initialize() {
	//Create map and display it in div
	var myOptions = {
		zoom: 10,
		center: new google.maps.LatLng(53.385899,-1.493084),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	gmap = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
	
	//Extract the points from the json string into an array of latlngs
	//var points_arr = JSON.parse(points_str);

	google.maps.event.addListener(gmap, 'click', function(event) {
		//alert(event.latLng.toString());
	});

	var parsed_data = JSON.parse($("#data").text());;

	alert(parsed_data);
}

function change_unit(){
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
			//alert("Error: could not obtain the rating of this track!");								
		}
	});					
}
