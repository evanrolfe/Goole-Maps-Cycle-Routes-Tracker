<?php defined('_JEXEC') or die('Restricted access'); 
$asset_url = JURI::base()."components/com_routes/assets/"; ?>

<form action="<?php echo JURI::base(); ?>index.php?option=com_routes&task=postimage" method="post" enctype="multipart/form-data">
    Send this file: <input name="userfile" type="file" />
    <input type="submit" value="Send File" />
</form>
