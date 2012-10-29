<?php
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * Hello Model
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class RoutesModelRoutes extends JModel
{
    /**
    * Gets the greeting
    * @return string The greeting to be displayed to the user
    */

    function getAllRoutes()
    {
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__routes';
		$db->setQuery($query);
		$routes = $db->loadObjectList();

		foreach($routes as $route){
			if(!$route->rating = $this->getAvgRating($route->id)){
				$route->rating = 0;
			}
		}

		return $routes;
    }

    function getImages($route_id)
    {
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__routes_images WHERE route_id='.$route_id;
		$db->setQuery($query);
		$images = $db->loadObjectList();

		return $images;
    }

	function deleteImage($id){
		$db =& JFactory::getDBO();
		$query = 'DELETE FROM #__routes_images WHERE id='.$id;
		$db->setQuery($query);

		return $db->query();	
	}

	function getUserName($id)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__users WHERE id='.$id;
		$db->setQuery($query);
		$user = $db->loadObject();

		return $user->username;
	}

	function getImage($id){
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__routes_images WHERE id='.$id;
		$db->setQuery($query);
		return $db->loadObject();
	}

	function getRoute($id)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__routes WHERE id='.$id;
		$db->setQuery($query);
		$route = $db->loadObject();

		$query = 'SELECT * FROM #__routes_comments WHERE route_id='.$id;
		$db->setQuery($query);
		$comments = $db->loadObjectList();		
		$route->comments = $comments;

		$query = 'SELECT * FROM #__users WHERE id='.$route->author_id;
		$db->setQuery($query);
		$user = $db->loadObject();

		$route->author = $this->getUserName($user->id);
		$route->difficulty = $this->calc_difficulty($route->elevations, $route->distance);

		return $route;
	}

	function getAvgRating($route_id)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__routes_ratings WHERE route_id='.$route_id;
		$db->setQuery($query);
		$db->query();

		$num_ratings = $db->getNumRows();

		 if($num_ratings > 0){
		 	//1. Calculate the sum of all ratings
			$ratings = $db->loadObjectList();
			$sum = 0;
			foreach($ratings as $rating){
				$sum += $rating->rating;
			}

			return $sum/$num_ratings;
		 }else{
		 	return false;
		 }

	}

	function getRating($route_id,$user_id)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT * FROM #__routes_ratings WHERE route_id='.$route_id.' AND user_id='.$user_id;
		$db->setQuery($query);
		if($rating = $db->loadObject()){
			return $rating->rating;	
		}else{
			return false;
		}
	}

	function viewRoute($id)
	{
		$db =& JFactory::getDBO();

		$query = 'UPDATE #__routes SET views = views+1 WHERE id='.$id;
		$db->setQuery($query);
		$db->query();	
	}

	function deleteRoute($id){
		$db =& JFactory::getDBO();
		$query = 'DELETE FROM #__routes_ratings WHERE route_id='.$id;
		$db->setQuery($query);
		$db->query();
		$query = 'DELETE FROM #__routes WHERE id='.$id;
		$db->setQuery($query);
		return $db->query();	
	}

	function updateRoute($id, $data){
		$db =& JFactory::getDBO();

		$query = "UPDATE #__routes SET ";

		$i=1;

		foreach($data as $key => $value){
			if($key == 'distance' || $key == 'difficulty'){
				$query .= $key."=".$value;
			}else{
				$query .= $key."='".$value."'";
			}


			if($i<sizeof($data))
				$query .= ", ";

			$i++;
		}

		$query .= " WHERE id=".$id;

		$db->setQuery($query);
		return $db->query();
		//return $query;
	}

	function postRoute($data)
	{
		$db =& JFactory::getDBO();

		$query = "INSERT INTO #__routes (title, description, continent, country, author_id, surface, category, distance, waypoints, elevations, polyline_encoded) VALUES ('".$data['title']."','".$data['description']."','".$data['continent']."','".$data['country']."',".$data['author_id'].",'".$data['surface']."','".$data['category']."',".$data['distance'].",'".$data['waypoints']."','".$data['elevations']."','".$data['polyline_encoded']."')";

		$db->setQuery($query);
		if($db->query()){
			return $db->insertid();
		}else{
			return $db->getErrorMsg();
		}
	}

	function postImage($path,$route_id,$user_id)
	{
		$db =& JFactory::getDBO();
		$query = "INSERT INTO #__routes_images (route_id, user_id, url) VALUES (".$route_id.",".$user_id.",'".$path."')";

		$db->setQuery($query);
		return $db->query();	
	}

	function postRating($route_id,$user_id,$rating)
	{
		$db =& JFactory::getDBO();

		//Check if the user's rating already exists
		if($this->getRating($route_id,$user_id)){
			//If it does then update the rating to the new value
			$query = 'UPDATE #__routes_ratings SET rating = '.$rating.' WHERE route_id='.$route_id.' AND user_id='.$user_id;
		}else{
			//Otherwise insert the rating
			$query = "INSERT INTO #__routes_ratings (route_id, user_id, rating) VALUES (".$route_id.",".$user_id.",".$rating.")";
		}

		$db->setQuery($query);
		return $db->query();	
	}

	function postComment($email,$name,$comment,$route_id)
	{
		$db =& JFactory::getDBO();
		$query = "INSERT INTO #__routes_comments (route_id, name, email, comment) VALUES (".$route_id.", '".$name."', '".$email."', '".$comment."')";
		$db->setQuery($query);
		return $db->query();	
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
}
