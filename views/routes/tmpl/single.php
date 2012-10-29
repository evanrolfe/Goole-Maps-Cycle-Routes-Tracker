<?php defined('_JEXEC') or die('Restricted access'); 
$asset_url = JURI::base()."components/com_routes/assets/"; 

//Random Sum Generator
//This sets the numbers that must be entered in the form to verify the user is human
$nums = array(array(1,2,3),array(2,4,6),array(10,1,11),array(9,1,10));
$random = $nums[rand(0,3)];
?>
<head>

<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=geometry&key=AIzaSyCsCpXNCbwxziCgcOWdriOFGFMYre4ZeuI&sensor=false"></script>

<script type="text/javascript" src="<?php echo $asset_url; ?>jquery.validate.js"></script>
<link type="text/css" href="<?php echo $asset_url; ?>jquery-ui-1.8.20.custom.css" rel="Stylesheet" />	
<link type="text/css" href="<?php echo $asset_url; ?>jquery-ui-1.8.21.custom.css" rel="Stylesheet" />	

<script type="text/javascript" src="<?php echo $asset_url; ?>jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>galleria/galleria-1.2.7.js"></script>
<link type="text/css" href="<?php echo $asset_url; ?>galleria/themes/classic/galleria.classic.css" rel="Stylesheet" />	
  <script type="text/javascript" src="<?php echo $asset_url; ?>rate/js/jquery.raty.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>countries.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>single.js"></script>
<script type="text/javascript" src="<?php echo $asset_url; ?>lightbox/js/jquery.lightbox-0.5.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>lightbox/css/jquery.lightbox-0.5.css" media="screen" />
<script type="text/javascript" src="<?php echo $asset_url; ?>jqModal.js"></script>
<script type="text/javascript">
$(function(){
			
	//$("#preview_div").jqm();
	//$("#map-tab").click(function(){ redraw(); });

	$("#tabs").tabs("select", window.location.hash);

	//Comment Form Validation
	$("#commentForm").validate({
	rules: {
		name : { maxlength : 255}
	  },
	messages:{ sum : "Please enter the correct sum" }
	});


	//Rating
	$('#star').raty({
		score    : $("#my_rating").text(),
		targetText : 'Your rating: ',
		click: function(score, evt) {
			$.ajax({
				url: base_url+"index.php?option=com_routes&task=json_rate",
				type: "POST",
				data: 	{ 
							route_id : $("#track_id").text(), 
							user_id : $("#user_id").text(), 
							rating : score
						},
				dataType: 'json',
				success: function(data){
					if(data.status=='success'){
						alert("Thank you for rating this track.");
						loadRating();
					}else{
						alert("Your rating was not counted due to an error!");								
					}
				},
				error: function(data){
					alert("Your rating was not counted due to an error!");
				}
			});						
	  	}
	});	
			
});
</script>

</head>
<div id='track_id' style='display:none'><?php echo $this->route->id; ?></div>
<div id='user_id' style='display:none'><?php echo $this->user_id; ?></div>
<div id='my_rating' style='display:none'><?php echo $this->my_rating; ?></div>
<div id='base_url' style='display:none'><?php echo JURI::base(); ?></div>
<div id='breadcrumbs'></div><br>
<div style='align: right; width: 100%;'>
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
			<td valign="top"><b>Title:</b> <?php echo $this->route->title; ?><br><b>Author:</b> <?php echo $this->route->author; ?><br><b>Category:</b> <?php echo $this->route->category; ?> bikes
<?php if($this->user_id == $this->route->author_id){ ?><br><b>Options:</b> 
<a href="<?php echo JURI::base(); ?>index.php?option=com_routes&task=edit&id=<?php echo $this->route->id; ?>">Edit</a> /
<a href="<?php echo JURI::base(); ?>index.php?option=com_routes&task=delete&id=<?php echo $this->route->id; ?>">Delete</a><?php } ?></td>
			<td valign="top"><b>Difficulty:</b> <?php echo $this->route->difficulty; ?><br><div id='distance'><b>Distance:</b></div><div id='gain'><b>Gain:</b></div></td>
			<td valign="top">
<?php if($this->user_id){ ?>
<div id='rating'><b>Your rating:</b><div id="star"></div></div>
<?php } ?>
<div id='avg_rate'></div><b>Views:</b> <?php echo $this->route->views; ?></td>
		</tr>
	</table>
</div><br>

<div id="tabs"  style="width:99%">
	<ul>
		<li><a href="#tabs-1" id='map-tab'>Map</a></li>
		<li><a href="#tabs-2">Track Details</a></li>
		<li><a href="#tabs-3">Comments</a></li>
		<li><a href="#tabs-4">Track Images</a></li>
<?php if($this->user_id){ ?>
		<li><a href="#tabs-5">Upload Image</a></li>
<?php } ?>
	</ul>
	<div id="tabs-1">
		<div id="map_canvas" style="width:100%; height:300px"></div><br>
		<div id="chart_canvas" style="width:100%;"></div>
	</div>
	<div id="tabs-2">
		<b>Author: </b><?php echo $this->route->author; ?><br><br>
		<b>Title: </b><?php echo $this->route->title; ?><br><br>
		<b>Description: </b><?php echo $this->route->description; ?><br><br>
		<b>Track Surface: </b><?php echo $this->route->surface; ?><br><br>
		<b>Created on: </b><?php echo $this->route->created_on; ?><br><br>
	</div>
	<div id="tabs-3">
		<?php foreach($this->route->comments as $comment){ ?>
			<div>
			<b><?php echo $comment->name; ?></b> (<?php echo $comment->created_on; ?>)<br>
			<?php echo $comment->comment; ?><br><br>
			</div>
		<?php } ?>
		<?php if(sizeof($this->route->comments) == 0){ ?>
		There are no comments for this track.<br>
		<?php } ?>
<?php if($this->user_id){ ?>
		<br>
		<form id='commentForm' action="<?php echo JURI::base(); ?>index.php?option=com_routes&task=postcomment" method="post">
			<input type='hidden' name='route_id' value="<?php echo $this->route->id; ?>" />
			<input type='hidden' id='target_num' name='target_num' value="<?php echo $random[2]; ?>" />
		<table>
			<tr>
				<td>Email: </td>
				<td><input type='text' name='email' class='required email' value="<?php echo $this->logged_in_user->email; ?>" /></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td>Name : </td>
				<td><input type='text' name='name' class='required' value="<?php echo $this->logged_in_user->name; ?>"/></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td>Comment:</td>
				<td><textarea name="comment" class='required track-desc' /></textarea></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td></td>
				<td align='right'>
					<button name="submit" type="submit" class="ui-button ui-state-default ui-corner-all ">
							<span style="padding: 10px">Post</span>
					</button>
				</td>
			</tr>			
		</table>
		</form>
<?php } ?>
	</div>
	<div id="tabs-4">

<script type="text/javascript">
</script>
		<?php if($this->images){ ?>
			<script type="text/javascript">
			$(function() {
				$('#gallery a').lightBox({
										imageLoading: base_url+"components/com_routes/assets/lightbox/images/loading.gif",
										imageBtnClose: base_url+"components/com_routes/assets/lightbox/images/close.gif",
										imageBtnPrev: base_url+"components/com_routes/assets/lightbox/images/prev.gif",
										imageBtnNext: base_url+"components/com_routes/assets/lightbox/images/next.gif"
										});
			});
			</script>
			<div id='gallery'>
				<?php foreach($this->images as $image){ ?>
				<a href="<?php echo JURI::base().'components/com_routes/uploads/'.$image->url; ?>"><img src="<?php echo JURI::base().'components/com_routes/uploads/'.$image->url; ?>" width='100px' height='100px' /></a>
				<?php } ?>
			</div>
		<?php }else{ ?>
			No images have been uploaded yet.
		<?php } ?>
	</div>
<?php if($this->user_id){ ?>
	<div id='tabs-5'>
		<form action="<?php echo JURI::base(); ?>index.php?option=com_routes&task=postimage" method="post" enctype="multipart/form-data">
		<form enctype="multipart/form-data" action="upload.php" method="POST">
			<input type="hidden" value="<?php echo $this->route->id; ?>" name="route_id">
			<input name="userfile" type="file" value='Browse'/><br>
			<button type="submit" class="ui-button ui-state-default ui-corner-all ">
					<span style="padding: 10px">Upload</span>
			</button>
		</form>
		<?php if($this->images){ ?><br>
			You may delete the following images:<br>
			<table>
				<?php foreach($this->images as $image){ ?>
					<?php if($image->user_id == $this->user_id){ ?>
						<tr>
							<td><img src="<?php echo JURI::base().'components/com_routes/uploads/'.$image->url; ?>" width='100px' height='100px' /></td>
							<td><a href="<?php echo JURI::base().'index.php?option=com_routes&task=delete_image&id='.$image->id; ?>">Delete</a></td>
						</tr>
					<?php } ?>
				<?php } ?>
			</table>
		<?php } ?>

	</div>
<?php } ?>
</div>

		<div id="chart_canvas2" style="width:100%;"></div>
