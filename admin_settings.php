<?php
	if(end(explode("/",$_SERVER['SCRIPT_FILENAME']))!="admin_settings.php"):
	function select($name,$val){
		if(get_option($name)==$val){
			echo ' selected="selected" ';
		}
	}
?>
<div class="wrap">

<div id="icon-options-general" class="icon32"><br></div><h2>LinkDir Settings</h2>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" name="__linkdir_linkdir_save" id="linkdir_save">

<?php if(is_post): ?>
<style type="text/css">
	div.error{
		background-color:#F18E8F;
		border:6px solid #D5304B;
		padding:8px;
		font-weight:bold;
		color:#FFF;
		margin:10px 0;
	}
	div.saved{
		background-color:#9FD562;
		border:6px solid #62C830;
		padding:8px;
		font-weight:bold;
		color:#FFF;
		margin:10px 0;
	}
</style>

<?php if(count($error)>0): ?>
<div class="error"><?php foreach($error as $x){echo $x."<br />";} ?></div>
<?php else: ?>
<div class="saved">Changes have been saved.</div>
<?php endif; endif; ?>

<table class="form-table">
	<tr valign="top">
        <th scope="row"><label for="linkback">'Link Back to Us'</label></th>
        <td><textarea id="linkback" name="__linkdir_link_back" style="width:22em;"><?php echo htmlspecialchars(stripslashes(get_option("link_back"))); ?></textarea></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="recaptcha">Use reCaptcha?</label></th>
        <td><select name="__linkdir_recaptcha" id="recaptcha"><option value="1"<?php select("recaptcha",1); ?>>Yes</option><option value="0"<?php select("recaptcha",0); ?>>No</option></select>
        <span class="description">Highly recommended, should stop most of the spam links.</span></td>
    </tr>
    <tr valign="top" class="recaptcha">
        <th scope="row"><label for="recaptcha_public">Recaptcha Public Key</label></th>
        <td><input type="text" name="__linkdir_recaptcha_public" id="recaptcha_public" value="<?php echo get_option("recaptcha_public"); ?>" class="regular-text" /></td>
    </tr>
    <tr valign="top" class="recaptcha">
        <th scope="row"><label for="recaptcha_private">Recaptcha Private Key</label></th>
        <td><input type="text" name="__linkdir_recaptcha_private" id="recaptcha_private" value="<?php echo get_option("recaptcha_private"); ?>" class="regular-text" /></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nofollow">Use rel="nofollow" attribute?</label></th>
        <td><select name="__linkdir_nofollow" id="nofollow"><option value="1"<?php select("nofollow",1); ?>>Yes</option><option value="0"<?php select("nofollow",0); ?>>No</option></select></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="newlinks">Allow users to submit new links?</label></th>
        <td><select name="__linkdir_allow_new_links" id="newlinks"><option value="1"<?php select("allow_new_links",1); ?>>Yes</option><option value="0"<?php select("allow_new_links",0); ?>>No</option></select></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="links">How to open directory links?</label></th>
        <td><select name="__linkdir_links" id="links"><option value="_blank"<?php select("links","_blank"); ?>>In the new tab/window</option><option value="_self"<?php select("links","_self"); ?>>In the same tab/window</option></select></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="recheck_hits">How frequently should we re-check pageranks?</label></th>
        <td><select name="__linkdir_recheck_hits" id="recheck_hits">
        	<option value="48"<?php select("recheck_hits",48); ?>>Every 48 Hours</option>
        	<option value="24"<?php select("recheck_hits",24); ?>>Every 24 Hours</option>
			<option value="12"<?php select("recheck_hits",12); ?>>Every 12 Hours</option>
        </select> <span class="description">More about how it works <a href="#">here</a>.</span></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="catsnum">Show the number of links in the category?</label></th>
        <td><select name="__linkdir_show_links_num" id="catsnum"><option value="1"<?php select("show_links_num",1); ?>>Yes</option><option value="0"<?php select("show_links_num",0); ?>>No</option></select></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="design">Design</label></th>
        <td><select name="__linkdir_design" id="design"><?php

if($handle = opendir(LINKDIR_ROOT."/design/")) {
	while(false !== ($file = readdir($handle))) {
		if($file != "." && $file != ".." && end(explode(".",$file))=="css"){?>
	<option name="__linkdir_<?php echo $file; ?>"<?php select("design",$file); ?>><?php echo substr($file,0,strlen($file)-4); ?></option>
<?php		}
	}
	closedir($handle);
}
		?></select> <span class="description">CSS files for customization are located in <code><?php echo LINKDIR_URL."design/"; ?></code></span></td>
    </tr>
	<tr valign="top">
        <th scope="row"><label for="show_thumbnails">Show Screenshots?</label></th>
        <td><select name="__linkdir_show_thumbnails" id="show_thumbnails"><option value="1"<?php select("show_thumbnails",1); ?>>Yes</option><option value="0"<?php select("show_thumbnails",0); ?>>No</option></select> <span class="description">Powered by thumbalizr.com</span></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="pw">Hide 'Powered By' line?</label></th>
        <td><select name="__linkdir_plugin_powered" id="pw"><option value="0"<?php select("plugin_powered",0); ?>>Yes</option><option value="1"<?php select("plugin_powered",1); ?>>No</option></select></td>
    </tr>
</table>


<p class="submit"><input id="submit" class="button-primary" type="submit" value="Save Changes" name="submit"></p>

    </form>
</div>
<?php endif; ?>