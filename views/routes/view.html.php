<?php
/**
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_1
 * @license    GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
 */

class RoutesViewRoutes extends JView
{
	function display($tpl = null)
	{
		//If a specific route is requested in the url then display the single route template
		$id = JRequest::getVar('id',null,'get');

		//If the user is logged in
		$user =& JFactory::getUser();
		if($user->get('id')){
			$this->assignRef('user_id',$user->get('id'));
			$this->assignRef('logged_in_user', JFactory::getUser($user->get('id')));
		}

		if($id){
			$model = &$this->getModel();
        	$this->assignRef( 'route', $model->getRoute($id) );
        	$this->assignRef( 'images', $model->getImages($id) );

			//If a specific route has been requested and the user is logged in then get the rating of the route
			if($user->get('id')){
	        	$rating = $model->getRating($id,$user->get('id'));		
			}else{
				$rating = 0;
			}

			$this->assignRef( 'my_rating', $rating);
			//$this->assignRef('comments',$model->getRouteComments($id));
			$model->viewRoute($id);
		}

		parent::display($tpl);
	}

}
