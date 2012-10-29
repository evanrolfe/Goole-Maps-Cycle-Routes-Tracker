<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Hello World Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class RoutesController extends JController
{
	function display()
	{
		parent::display();
	}


	public function front_page(){
		JRequest::setVar( 'layout', 'front' );

		parent::display();	
	}

	public function show()
	{
		JRequest::setVar( 'layout', 'single' );

		parent::display();		
	}

	public function postcomment()
	{
		$comment = JRequest::getVar('comment',null,'post');
		$app = JFactory::getApplication();
		if($comment){
 			$model =& $this->getModel();

			$name = JRequest::getVar('name',null,'post');
			$email = JRequest::getVar('email',null,'post');
			$route_id = JRequest::getVar('route_id',null,'post');

			if($model->postComment($email,$name,$comment,$route_id)){
				$msg = "Comment posted successfully";
			}else{
				$db =& JFactory::getDBO();
				$msg = 'There was an error posting your comment:<br>'.$db->getErrorMsg();
			}

			$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-3", $msg);			
		}else{
			$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-3", 'There was an error posting your comment!');			
		}
	}

/*=======================================
|	User Functions
|	the user must be logged into joomla
*=======================================*/
	public function delete_image(){
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();

		$id = JRequest::getVar('id',null,'get');
		$image = $model->getImage($id);
		jimport('joomla.filesystem.file');

		if($user->get('id') && $user->get('id') == $image->user_id){
			if($model->deleteImage($id)){
				JFile::delete(JPATH_COMPONENT.'/uploads/'.$image->url);
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$image->route_id."#tabs-5", "Your image has been successfully deleted.");						
			}else{
				$db =& JFactory::getDBO();
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$image->route_id."#tabs-5". "Your image was not been deleted due to an error!");						
			}
		}else{
			$app->redirect(JURI::base()."index.php?option=com_routes", "You do not have the permission to delete that image!");						
		}
	}

	public function delete(){
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();

		$id = JRequest::getVar('id',null,'get');
		$route = $model->getRoute($id);

		if($user->get('id') && $user->get('id') == $route->author_id){
			if($model->deleteRoute($id)){
				$app->redirect(JURI::base()."index.php?option=com_routes", "Your track has been successfully deleted.");						
			}else{
				$db =& JFactory::getDBO();
				$app->redirect(JURI::base()."index.php?option=com_routes". "Your track was not been deleted due to an error!");						
			}
		}else{
			$app->redirect(JURI::base()."index.php?option=com_routes", "You do not have the permission to delete that track!");						
		}
	}

	public function update(){
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();

		$id = JRequest::getVar('route_id',null,'post');
		$route = $model->getRoute($id);

		if($user->get('id') && $user->get('id') == $route->author_id){
			$input = array();
			$input['title'] = JRequest::getVar('title',null,'post');
			$input['description'] = JRequest::getVar('description',null,'post');

			$input['continent'] = JRequest::getVar('continent',null,'post');
			$input['country'] = JRequest::getVar('country',null,'post');
			$input['author_id'] = $user->get('id');

			//Surface field
			$surface['paved'] = JRequest::getVar('surface_paved',null,'post');
			$surface['unpaved'] = JRequest::getVar('surface_unpaved',null,'post');
			$surface['gravel'] = JRequest::getVar('surface_gravel',null,'post');
			$surface['downhill'] = JRequest::getVar('surface_downhill',null,'post');

			$input['surface'] = '';

			foreach($surface as $key => $val){
				if($surface[$key] != null)
					$input['surface'] .= $key.", ";
			}

			$input['surface'] = substr($input['surface'],0,-2);

			//Category field
			$category['all'] = JRequest::getVar('category_all',null,'post');
			$category['mountain'] = JRequest::getVar('category_mountain',null,'post');
			$category['downhill'] = JRequest::getVar('category_downhill',null,'post');

			$input['category'] = '';
			foreach($category as $key => $val){
				if($category[$key] != null)
					$input['category'] .= $key.", ";
			}

			$input['category'] = substr($input['category'],0,-2);

			//Data Derived from the google map
			$input['distance'] = JRequest::getVar('distance',null,'post');
			$input['waypoints'] = JRequest::getVar('waypoints',null,'post');
			$input['elevations'] = JRequest::getVar('elevations',null,'post');
			$input['polyline_encoded'] = JRequest::getVar('polyline_encoded',null,'post','',JREQUEST_ALLOWRAW); //Must allow raw characters otherwise it will screw up the encoding

			if($model->updateRoute($id, $input)){
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$id, "Your track has been successfully updated.");						
			}else{
				$db =& JFactory::getDBO();
				$app->redirect(JURI::base()."index.php?option=com_routes&task=edit&id=".$id, "Your track was not updated due to an error!");						
			}

			parent::display();
		}else{
			$app->redirect(JURI::base()."index.php?option=com_routes", "You do not have the permission to edit that track!");						
		}
	}

	public function edit(){
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();

		$id = JRequest::getVar('id',null,'get');
		$route = $model->getRoute($id);


		if($user->get('id') && $user->get('id') == $route->author_id){
			JRequest::setVar( 'route', $route );
			JRequest::setVar( 'layout', 'edit' );
			parent::display();
		}else{
			$app->redirect(JURI::base()."index.php?option=com_routes", "You do not have the permission to edit that track!");						
		}
	}


	public function create()
	{
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();

		if($user->get('id')){
			JRequest::setVar( 'layout', 'create_july' );
			parent::display();
		}else{
			$app->redirect(JURI::base()."index.php?option=com_routes", "You must be logged in to create a track!");						
		}
	}


	function postroute()
	{
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();

		if($user->get('id')){
			$input = array();
			$input['title'] = JRequest::getVar('title',null,'post');
			$input['description'] = JRequest::getVar('description',null,'post');
			$input['continent'] = JRequest::getVar('continent',null,'post');
			$input['country'] = JRequest::getVar('country',null,'post');
			$input['author_id'] = $user->get('id');

			//Surface field
			$surface['paved'] = JRequest::getVar('surface_paved',null,'post');
			$surface['unpaved'] = JRequest::getVar('surface_unpaved',null,'post');
			$surface['gravel'] = JRequest::getVar('surface_gravel',null,'post');
			$surface['downhill'] = JRequest::getVar('surface_downhill',null,'post');

			$input['surface'] = '';

			foreach($surface as $key => $val){
				if($surface[$key] != null)
					$input['surface'] .= $key.", ";
			}

			$input['surface'] = substr($input['surface'],0,-2);

			//Category field
			$category['all'] = JRequest::getVar('category_all',null,'post');
			$category['mountain'] = JRequest::getVar('category_mountain',null,'post');
			$category['downhill'] = JRequest::getVar('category_downhill',null,'post');

			$input['category'] = '';
			foreach($category as $key => $val){
				if($category[$key] != null)
					$input['category'] .= $key.", ";
			}

			$input['category'] = substr($input['category'],0,-2);

			//Data Derived from the google map
			$input['distance'] = JRequest::getVar('distance',null,'post');
			$input['waypoints'] = JRequest::getVar('waypoints',null,'post');
			$input['elevations'] = JRequest::getVar('elevations',null,'post');
			$input['polyline_encoded'] = JRequest::getVar('polyline_encoded',null,'post','',JREQUEST_ALLOWRAW); //Must allow raw characters otherwise it will screw up the encoding

			$return_id = $model->postRoute($input);

			if($return_id){
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$return_id, "Your track has been successfully saved.");						
			}else{
				$db =& JFactory::getDBO();
				$app->redirect(JURI::base()."index.php?option=com_routes&task=create", "Your track was not saved due to an error!");						
			}

/*
			echo "Inserted with ID: ".$return_id;

			echo "<pre>";
			print_r($input);
			echo "</pre>";
			$app->close();
*/
		}else{
			header('HTTP/1.1 403 Forbidden');
			$app->close();		
		}
	}

	public function upload()
	{
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
		$route_id = JRequest::getVar('route_id',null,'get');

		if($user->get('id') && $route_id){
			JRequest::setVar( 'layout', 'upload' );
			parent::display();
		}else{
			echo "error";
			$app->close();
		}
	}

	public function postimage()
	{

		global $mainframe;

		$user =& JFactory::getUser();
		$app = JFactory::getApplication();

		jimport('joomla.filesystem.file');

		$max = ini_get('upload_max_filesize');

		$uploaddir = JPATH_COMPONENT.'/uploads/';

		$file = JRequest::getVar('userfile', null, 'files', 'array');

		if (isset($file['name'])) {
 			$model =& $this->getModel();
			$route_id = JRequest::getVar('route_id', null, 'post');
			$user_id = $user->get('id');

			$source = $file['tmp_name'];
			$destination = $uploaddir . JFile::makeSafe($file['name']);

			if($file['size'] > 500000){
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-5", "Error: that image is too large. Max file size: 500kb");	
			}

			//1. Check file with that name does not already exist
			if (JFile::exists($destination)) {
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-5", "Error: an image with that name already exists! Please rename your image and upload again.");						
			}

			//2. Check file type
			$type = strtolower($file['type']);
			if(!($type == 'image/jpeg' || $type == 'image/gif' || $type == 'image/png')){
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-5", "Error: File type not supported.");
			}

			$name = JFile::makeSafe($file['name']);
			//3. Upload the file
			if(JFile::upload($source, $destination) && $model->postImage($name,$route_id,$user_id)){
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-5", "Your image has been successfully uploaded.");
			}else{
				$app->redirect(JURI::base()."index.php?option=com_routes&task=show&id=".$route_id."#tabs-5", "Your image could not be uploaded due to an error!");
			}

		}else{
			echo "Invalid request";
			$app->close();
		}
	}

	public function kml()
	{
		$app = JFactory::getApplication();
 		$model =& $this->getModel();
        $routes = $model->getAllRoutes();


		// Creates an array of strings to hold the lines of the KML file.
		$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
		$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
		$kml[] = ' <Folder>';

			$kml[] = "     <Style id='0'>";
			$kml[] = "       <LineStyle>";
			$kml[] = "         <width>4</width>";
			$kml[] = "         <color>6414F050</color>";
			$kml[] = "       </LineStyle>";
			$kml[] = "     </Style>";
			$kml[] = "     <Style id='1'>";
			$kml[] = "       <LineStyle>";
			$kml[] = "         <width>4</width>";
			$kml[] = "         <color>64F0FF14</color>";
			$kml[] = "       </LineStyle>";
			$kml[] = "     </Style>";
			$kml[] = "     <Style id='2'>";
			$kml[] = "       <LineStyle>";
			$kml[] = "         <width>4</width>";
			$kml[] = "         <color>641400FF</color>";
			$kml[] = "       </LineStyle>";
			$kml[] = "     </Style>";
			$kml[] = "     <Style id='3'>";
			$kml[] = "       <LineStyle>";
			$kml[] = "         <width>4</width>";
			$kml[] = "         <color>647800F0</color>";
			$kml[] = "       </LineStyle>";
			$kml[] = "     </Style>";
			$kml[] = "     <Style id='origin'>";
			$kml[] = "       <IconStyle>";
			$kml[] = "         <Icon>";
			$kml[] = "           <href>".JURI::base()."components/com_routes/assets/icons/icon_a.png"."</href>";
			$kml[] = "         </Icon>";
			$kml[] = "       </IconStyle>";
			$kml[] = "     </Style>";
			$kml[] = "     <Style id='waypoint'>";
			$kml[] = "       <IconStyle>";
			$kml[] = "         <Icon>";
			$kml[] = "           <href>".JURI::base()."components/com_routes/assets/icons/icon_pin.png"."</href>";
			$kml[] = "         </Icon>";
			$kml[] = "       </IconStyle>";
			$kml[] = "     </Style>";
			$kml[] = "     <Style id='destination'>";
			$kml[] = "       <IconStyle>";
			$kml[] = "         <Icon>";
			$kml[] = "           <href>".JURI::base()."components/com_routes/assets/icons/icon_b.png"."</href>";
			$kml[] = "         </Icon>";
			$kml[] = "       </IconStyle>";
			$kml[] = "     </Style>";
		$i=0;
		foreach($routes as $route){
			$polyline = $this->decodePolylineToArray($route->polyline_encoded);
			$color = $i % 4;
			$kml[] = "    <Placemark id='route_".$route->id."'>";
			$kml[] = "      <styleUrl>#".$color."</styleUrl>";
			$kml[] = "      <name>".$route->title."</name>";
			$kml[] = "      <LineString>";
			$kml[] = "        <altitudeMode>relative</altitudeMode>";
			$kml[] = "        <coordinates>";
			foreach($polyline as $point){
			$kml[] = $point[1].",".$point[0];				
			}
			$kml[] = "        </coordinates>";
			$kml[] = "      </LineString>";
			$kml[] = "    </Placemark>";
			$i++;

			//Draw markers
			$waypoints = json_decode($route->waypoints_elevations);
			for($i=0; $i<sizeof($waypoints); $i++){

				if($i==0){
					$iconstyle = 'origin';
				}elseif($i<(sizeof($waypoints)-1)){
					$iconstyle = 'waypoints';
				}else{
					$iconstyle = 'destination';	
				}

			  $kml[] = '    <Placemark id="">';
			  $kml[] = '      <name>' . htmlentities($row['name']) . '</name>';
			  $kml[] = '      <description></description>';
			  $kml[] = '      <styleUrl>'.$iconstyle.'</styleUrl>';
			  $kml[] = '      <Point>';
			  $kml[] = '        <coordinates>'.$waypoints[$i][1].','.$waypoints[$i][0].'</coordinates>';
			  $kml[] = '      </Point>';
			  $kml[] = '    </Placemark>';
			}
		}
		$kml[] = '  </Folder>';
		$kml[] = '</kml>';
		$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
header('Content-Disposition: attachment; filename="routes.kml"');
		echo $kmlOutput;

		$app->close();	
	}

/*=======================================
|	JSON API Functions
|	these will only return json data
*=======================================*/
	public function json_rate()
	{
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();
		
		$route_id = JRequest::getVar('route_id',null,'post');	
		$rating = JRequest::getVar('rating',null,'post');
		$user_id = $user->get('id');
		if($user_id && $route_id && $user_id){

			if($model->postRating($route_id,$user_id,$rating)){
				echo '{"status" : "success"}';
			}else{
				echo '{"status" : "fail"}';				
			}
		}else{
				echo '{"status" : "fail: not all necessary post vars were set!"}';							
		}

		$app->close();
	}

	public function json_avg_rating()
	{
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();
		
		$route_id = JRequest::getVar('route_id',null,'get');	
		if($route_id){
			if($rating = $model->getAvgRating($route_id)){
				echo '{"status" : "success", "avg" : '.$rating.'}';
			}else{
				echo '{"status" : "not_rated"}';				
			}
		}else{
			echo '{"status" : "fail: not all necessary post vars were set!"}';							
		}

		$app->close();		
	}
	public function json_create()
	{
		$user =& JFactory::getUser();
		$app = JFactory::getApplication();
 		$model =& $this->getModel();

		if($user->get('id')){
			$input = array();
			$input['title'] = JRequest::getVar('title',null,'post');
			$input['polyline'] = JRequest::getVar('polyline',null,'post');
			$input['json_waypoints'] = JRequest::getVar('json_waypoints',null,'post');
			$input['continent'] = JRequest::getVar('continent',null,'post');
			$input['country'] = JRequest::getVar('country',null,'post');
			$input['author_id'] = $user->get('id');
			$input['surface'] = JRequest::getVar('surface',null,'post');
			$input['category'] = JRequest::getVar('category',null,'post');
			$input['distance'] = JRequest::getVar('distance',null,'post');
			$input['difficulty'] = JRequest::getVar('difficulty',null,'post');

			$return_id = $model->postRoute($input);

			if($return_id){
				echo '{ "status" : "success", "id" : '.$return_id.'}';
			}else{
				echo '{ "status" : "fail", "message" : "mysql error"}';
			}
				$app->close();
		}else{
			header('HTTP/1.1 403 Forbidden');
			$app->close();		
		}
	}

	public function json()
	{
		$app = JFactory::getApplication();
 		$model =& $this->getModel();
        $routes = $model->getAllRoutes();

		echo "[";
		for($i=0; $i<sizeof($routes); $i++){
			 $this->print_row_json($routes[$i]);

			if($i < (sizeof($routes)-1)){
				echo ",";
			}
		}
		echo "]";
		$app->close();
	}

	public function json_single()
	{
		$app = JFactory::getApplication();
 		$model =& $this->getModel();
		$id = JRequest::getVar('id',null,'get');

		$route = $model->getRoute($id);

		$this->print_row_json($route);

		$app->close();
		
	}

	protected function calc_gain($waypoints_elevations){
		$data = json_decode($waypoints_elevations);
		$elevations = array();
		foreach($data as $point){
			$elevations[] = $point[2];
		}

		$gain = 0;
		for($i=1; $i<sizeof($elevations); $i++){
			$diff = $elevations[$i]-$elevations[$i-1];
			if($diff > 0)
				$gain += $diff;
		}

		return $gain;
	}

	//Difficulty determined by formula: (H/D*100)*4 + H²/D + D/1000 + (T-1000)/100
	//Where H = difference in height; D = distance in meters; T = top of mountain in meters
	protected function calc_difficulty($waypoints_elevations, $distance){
		$data = json_decode($waypoints_elevations);

		//1. Find highest point (T)
		$t = 0.0;
		foreach($data as $point){
			if($point[2] > $top)
				$t = $point[2];
		}

		//2. Find difference in height (H)
		$h = $data[sizeof($data)-1][2] - $data[0][2];

		//3. Distance
		$d = $distance;

		//4. Calculate difficulty
		$result = ($h/$d*100)*4+($h*$h/$d)+($d/1000);

		//4.2 Include this last term if the max height > 1000m
		if($t > 1000)
			$result += $t-1000/100;

		return floor($result);
	}

	protected function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}

	protected function print_row_json($row)
	{
		$encoded = addslashes($row->polyline_encoded);
		$gain = $this->calc_gain($row->elevations);
		$difficulty = $this->calc_difficulty($row->elevations,$row->distance);
		echo '{"id" : '.$row->id.', "author_id" : '.$row->author_id.', "difficulty" : '.$difficulty.', "gain" : '.$gain.', "rating" : "'.$row->rating.'", "title" : "'.$row->title.'", "description" : "'.$row->description.'", "distance" : '.$row->distance.', "country" : "'.$row->country.'", "continent" : "'.$row->continent.'", "surface" : "'.$row->surface.'", "category" : "'.$row->category.'", "polyline_encoded" : "'.$encoded.'", "waypoints" : '.$row->waypoints.', "elevations" : '.$row->elevations.'}';
	}
}
