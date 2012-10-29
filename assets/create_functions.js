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

function update_point(old_latLng, new_latLng)
{
	//If it is the origin
	if(origin.equals(old_latLng)){
		origin = new_latLng;
		refresh_map();
	//Else if it is the destination		
	}else if(destination.equals(old_latLng)){
		destination = new_latLng;
		refresh_map();		
	//Otherwise it is a waypoint
	}else{
		update_waypoint(old_latLng, new_latLng);
	}
}

function update_waypoint(old_latLng, new_latLng)
{
	//1.Find the index of the original waypoint
	var i;
	for(i=0; i<waypoints.length; i++){
		if(waypoints[i].equals(old_latLng))
			break;
	}
	//2.Update the waypoint to its new position (from the drag)
	waypoints[i] = new_latLng;
	//3.
	refresh_map();	
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

function partition_arr(array, partition_size){
	tmp_arr = [];
	out_arr = [];

	//Duplicate the elements at index congruent to 0 modulo the partition size (except for head element)
	for(var i=0; i<array.length; i++){
		tmp_arr.push(array[i]);

		if(i % (partition_size-1) == 0 && i >0)
			tmp_arr.push(array[i]);
	}

	//Now slice the tmp_arr and return as an array of partitions
	//Determine the number of partitions
	num_parts = Math.ceil(tmp_arr.length/partition_size);
	ranges = [];
	for(var i=0; i<num_parts;i++){
		first = i*partition_size;
		second = (i*partition_size)+partition_size;
		ranges.push(tmp_arr.slice(first,second));
	}

	return ranges;
}

//Input an array of max 10 latlngs where first marker is start and last is destination
function draw_short_route(points){
	//Initialize Directions Service Renderer for this indivudal route
	directionsRenderers.push(new google.maps.DirectionsRenderer({draggable: false}));
	var renderer = directionsRenderers[directionsRenderers.length-1];

	renderer.setMap(gmap);
	//renderer.setPanel(document.getElementById("directions"));
	renderer.suppressMarkers = true;
	renderer.preserveViewport = true;

	var waypts = [];

	//Parse the waypoints if they exist
	if(points.length > 2){
		for(var i=1; i<points.length-1; i++){
			waypts.push({location: points[i],
						stopover: false});
		}
	}

	//Parameters for the directions service request
	var request = {
		origin: points[0],
		destination: points[points.length-1],
		waypoints: waypts,
		optimizeWaypoints: false,
		travelMode: google.maps.TravelMode.WALKING
	};


	//Make the request
	directionsService.route(request, function(response, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			//If the directions were received then display them on the map
			renderer.setDirections(response);

			for(var i=0; i<response.routes[0].overview_path.length; i++){
				directionsPolyline.push(response.routes[0].overview_path[i]);
			}

			//Update the form with the encoded polyline
			var encoded = google.maps.geometry.encoding.encodePath(directionsPolyline);
			$("#polyline_encoded").val(encoded.replace(/\\/g,"\\\\"));

			set_waypoints_elevations_form2(directionsPolyline);

			for(var j=0; j<response.routes[0].legs.length; j++){
				distance += response.routes[0].legs[j].distance.value;
			}

			//Update form with the distance
			//If the units has been set to miles then display distance in miles
			html_distance = (unit == 'miles') ? distance*0.621371192 : distance;
			$("#distance").html("Distance: "+((html_distance/1000).toFixed(1))+" "+unit);

			$("#distance_form").val(distance);
		}else{
			alert('Error drawing the route on the map!');
		}
	});	
}

//Split points into partitions of size 9 so that each route has 7 waypoints so one more waypoint can always be added
function draw_route(points)
{
	var partition_size = 9;
	distance = 0;
	//Partition if necessary
	if(points.length > partition_size){

		var partitions = partition_arr(points,partition_size);

		for(var i=0; i<partitions.length; i++){
			draw_short_route(partitions[i]);
		}
	}else{
		draw_short_route(points);
	}

}

//Save all points in the line and they're corresponding elevations to the form in JSON format
function set_waypoints_elevations_form2(points){
	//1. Extract the route array of latlngs
	var options = { locations : points };

	//Get elevations for the points
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

			//4. Set the form field as the JSON array of elevations and waypoints
			$("#waypoints_elevations").val(JSON.stringify(output_arr));
			//alert(JSON.stringify(output_arr));
		}else{
			alert("Error: could not retreive the elevations from google elevations service! Please refresh and try again.\n"+status);
		}
	});


	//===================================================
	//New FORMAT
	//===================================================

	//1. Save waypoints
	var waypoints_coords = [];
	for(var i=0; i<waypoints.length; i++){	
		waypoints_coords.push([waypoints[i].lat(), waypoints[i].lng()]);
	}
	$("#waypoints_db").val(JSON.stringify(waypoints_coords));

	//2. Save elevations from polyline
}


//Clears the overlays on the map
//But leaves the instance variables origin,destination,waypoints unchanged
function clear_overlays()
{
	//clear the renderers (routes)
	for(var i=0; i<directionsRenderers.length; i++){
		directionsRenderers[i].setMap(null);
	}

	directionsRenderers = [];
	directionsPolyline = [];
	//Clear the overlays
	for(var i=0; i<overlays.length; i++){
		overlays[i].setMap(null);
	}
	overlays = [];
}

//This combines the origin, destination and waypoints into a single array of latlngs
function get_route_array(){
	var route = [origin];

	if(waypoints.length > 0)
		$.merge(route,waypoints);

	if(destination)
		route.push(destination);
	return route;
}
