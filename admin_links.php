<?php if(end(explode("/", $_SERVER['SCRIPT_FILENAME'])) != "admin_links.php"): ?>
<style type="text/css">
a.linkdirbtn{
	display:inline-block;
	background-color:#FFE100;
	padding:8px 15px;
	font-size:14px;
	-webkit-border-radius: 7px;
	-moz-border-radius: 7px;
	border-radius: 7px;
	color:#000;
	text-decoration:none;
}
a.linkdirbtn:hover{
	background-color:#FFD100;
}
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



<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div><h2>LinkDir Links</h2>

<?php

if(isset($_GET['edit'])):

$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE." WHERE id='".addslashes($_GET['edit'])."' LIMIT 1;");
$query2 = mysql_query("SELECT * FROM ".LINKDIR_TABLE." WHERE id='".addslashes($_GET['edit'])."' LIMIT 1;");

if(mysql_num_rows($query)>0):

		if(is_post){

			$x = mysql_fetch_array($query);

			// Check domain name
			$url = $_POST['url'];
			$ssl = substr($url,0,5)=="https"?1:0;
			$url = preg_replace("/https\:\/\//","http://",$url);
			preg_match('@^(?:http://)?([^/]+)@i',$url,$matches);
			$host = strtolower($matches[1]);

			if(strlen($host)<4||!is_valid_domain_name($host)){
				$error_msg = "The URL you've entered doesn't seem to be valid.";
			}else{
				// Check if it already exists
				$type1 = substr($host,0,4)=="www."?substr($host,4):$host;
				$type2 = substr($host,0,4)=="www."?$host:"www.".$host;
				$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE." WHERE (url='$type1' OR url='$type2') AND id!=".$x['id'].";");
				if(mysql_num_rows($query)>0){
					$error_msg = "This URL already exists.";
				}
			}

			// Category
			if(is_numeric($_POST['cat'])){
				if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." WHERE id='".addslashes($_POST['cat'])." LIMIT 0,1';"))>0){
					// OK
				}else $error_msg = "An unknown category has been selected.";
			}else $error_msg = "An unknown category has been selected.";

			// Title
			if(strlen($_POST['title'])>0){
				if(strlen($_POST['title'])<=35){
					// OK
				}else $error_msg = "Title is too long (max. 35 characters).";
			}else $error_msg = "Title cannot be empty.";

			// Description
			if(strlen(htmlspecialchars($_POST['description']))>150){
				$error_msg = "Description is too long (max. 150 characters).";
			}

			$error = empty($error_msg)?false:true;

			if(!$error){
				$set = linkdir_set(array(
					"description"	=> $_POST['description'],
					"ssl"			=> $ssl,
					"name"			=> $_POST['title'],
					"cat"			=> $_POST['cat'],
					"url"			=> $host
				));
				mysql_query("UPDATE ".LINKDIR_TABLE." SET ".$set." WHERE id='".$_GET['edit']."';");

				// Reset pagerank if category has changed (will be recounted on next session)
				if($_POST['cat']!=$x['cat']){
					mysql_query("UPDATE ".LINKDIR_TABLE." SET pagerank=777 WHERE id=".$x['id']);
				}

				print '<div class="saved">Changes have been saved.</div>';
			}else{
				print '<div class="error">'.$error_msg.'</div>';
			}
		}

	$x = mysql_fetch_array($query2);
?>

<form method="post" action="admin.php?page=linkdir_links&edit=<?php echo htmlspecialchars($_GET['edit']); ?>">

<h3>Editing a Link #<?php echo $x['id']; ?></h3>

<p><label for="url">URL:</label><br />
<input style="width:300px;" type="text" name="url" id="url" value="<?php print ($x['ssl']==1?'https://':'http://').$x['url']."/"; ?>" maxlength="50" /></p>

<p><label for="name">Title:</label><br />
<input style="width:300px;" type="text" name="title" id="name" value="<?php echo htmlspecialchars($x['name']); ?>" maxlength="50" /></p>

<p><label for="description">Description:</label><br />
<textarea style="width:300px;height:100px;" name="description" id="description"><?php echo htmlspecialchars($x['description']); ?></textarea></p>

<p><label for="cat">Description:</label><br />
<select name="cat" id="cat"><?php
	$categories = mysql_query('SELECT * FROM '.LINKDIR_TABLE_CATS." ORDER BY title ASC;");

	if(mysql_num_rows($categories)>0):
		while($row = mysql_fetch_array($categories)){
			print '<option value="'.$row['id'].'" '.($row['id']==$x['cat']?'selected="selected"':'').'>'.$row['title'].'</option>';
		}
	endif;
?></select></p>

<p><input type="submit" name="submit" value="Update Link" class="button-primary" /> <a href="admin.php?page=linkdir_links&delete=<?php echo $x['id']; ?>" onClick="return confirm('Do you really want to delete this link? There is no undo!')" style="color:red;display:inline-block;position:relative;left:30px;">Delete this link</a></p></p>
<br /><br />
<p><b>Submitted on:</b> <?php echo date('Y/m/d H:i:s',$x['submitted_on']); ?></p>
<p><b>Submitted by:</b> <?php $y = unserialize($x['submitted_by']); echo $y['ip']; ?></p>
<p><b>Inbound links (all-time): </b><?php echo number_format($x['hitsin'],0,",","."); ?></p>
<p><b>Outbound links (all-time): </b><?php echo number_format($x['hitsout'],0,",","."); ?></p>
<p><b>Pagerank: </b><?php print ($x['pagerank']>50?'&gt;50':$x['pagerank']); ?></p>
</form>

<?php
endif;

else:

if(isset($_GET['delete'])){
	$id = "'".addslashes($_GET['delete'])."'";
	if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE id=".$id))>0){

		// Delete the link itself
		mysql_query("DELETE FROM ".LINKDIR_TABLE." WHERE id=".$id);

		// Delete all its hits
		mysql_query("DELETE FROM ".LINKDIR_TABLE_HITS." WHERE `for` = ".$id);

		print '<div class="saved">Link #'.htmlspecialchars($_GET['delete']).' and its data has been successfully deleted.</div>';
	}
}

?>

<form method="get" action="admin.php?page=linkdir_links" name="linkdir">
	<p><label for="link">Search Links by Domain</label> <input type="text" name="__linkdir_link" id="link" maxlength="50" /> <input class="button" type="submit" name="submit" value="Proceed" /> <input type="checkbox" name="__linkdir_exact" value="1" id="exact" /> <label for="exact">Exact match?</label> </p>
	<input type="hidden" name="search" value="1" />
	<input type="hidden" name="page" value="linkdir_links" />
</form>
	<?php if(isset($_GET['search'])&&!is_post):

			$ready = false;
			$ssl = substr($_GET['__linkdir_link'],0,5)=="https"?1:0;
			$_GET['__linkdir_link'] = preg_replace("/https\:\/\//","http://",$_GET['__linkdir_link']);
			preg_match('@^(?:http://)?([^/]+)@i',$_GET['__linkdir_link'],$matches);
			$host = strtolower($matches[1]);

			if(strlen($host)>4||is_valid_domain_name($host)){
				$where = isset($_GET['__linkdir_exact'])?"WHERE url='$host' LIMIT 1;":"WHERE url LIKE '%$host%';";
				$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE." ".$where);
				$ready = true;
			}else print "<div class=\"error\">The URL you've entered doesn't seem to be valid.</div>";

				if($ready){ ?><?php if(mysql_num_rows($query)>0){ ?>
					<form method="post" action="admin.php?page=linkdir_links" name="deleteselected">
				<?php while($row = mysql_fetch_array($query)){
	?><div class="unit" style="margin:10px 0px;"><input type="checkbox" name="delete_<?php echo $row['id']; ?>" value="yes" /> <a style="display:inline-block;width:400px;padding:5px 5px 5px 19px;-webkit-border-radius: 4px;-moz-border-radius:4px;border-radius:4px;color:#000;text-decoration:none;background:url(<?php echo LINKDIR_URL."images/edit_smaller.png"; ?>) 5px 8px no-repeat scroll #e6e6e6;" href="admin.php?page=linkdir_links&edit=<?php echo $row['id']; ?>"><?php echo $row['name']; ?><span style="font-size:10px;color:#333;float:right;"><?php echo $row['url']; ?></span></span></a><?php
				} }else{
				?><div class="saved">Sorry, we didn't find anything. Please try different terms.</div><?php
				} }
		endif;
		if(is_post):
			// find links to delete
			if(count($_POST)>0){
				$delete = array();
				foreach($_POST as $key => $val){
					if(substr($key,0,6)=="delete"){
						$id = substr($key,7);
						if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE id='".addslashes($id)."';"))){
							$delete[$id] = $id;
						}
					}
				}

				if(count($delete)>0){
					foreach($delete as $id){

						// Delete the link itself
						mysql_query("DELETE FROM ".LINKDIR_TABLE." WHERE id=".$id);

						// Delete hits
						mysql_query("DELETE FROM ".LINKDIR_TABLE_HITS." WHERE `for` = ".$id);

					}

					echo '<div class="saved">'.count($delete).' links have been successfully deleted.</div>';
				}else{
					echo '<div class="error">You did not select any links. Please try again.</div>';
				}
			}
		endif;
	?>
<?php if($ready){if(mysql_num_rows($query)){ ?><p><input type="submit" name="submit" value="Delete Selected" class="button-primary" onClick="return confirm('Do you really want to delete all selected links? There is no undo!')" /></p></form><?php }} ?>
</div>

<?php endif; endif;  ?>