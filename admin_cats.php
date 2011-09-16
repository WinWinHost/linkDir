<?php if(end(explode("/", $_SERVER['SCRIPT_FILENAME'])) != "admin_cats.php"): ?>
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
<?php if(isset($_GET['new'])) { ?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div><h2>LinkDir New Category <a href="admin.php?page=linkdir_cats" class="add-new-h2">Back to The Listing</a></h2>
<?php if(is_post): if(count($error) > 0): ?>
<div class="error"><?php foreach($error as $x) {
                echo $x . "<br />";
            } ?></div>
<?php else: ?>
<div class="saved">Changes have been saved.</div>
<?php endif;
            endif; ?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" name="__linkdir_linkdir_save" id="linkdir_save">
<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="title">Title</label></th>
        <td><input type="text" name="__linkdir_name" id="title" maxlength="35" style="width:350px;" /></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="description">Description</label></th>
        <td><textarea name="__linkdir_description" id="description" style="width:350px;height:80px;"></textarea></td>
    </tr>
</table>
<p class="submit"><input id="submit" class="button-primary" type="submit" value="Add a New Category" name="submit"></p>
</form></div><?php }elseif($_GET['_delete']){ ?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div><h2>LinkDir Category Deleted <a href="admin.php?page=linkdir_cats" class="add-new-h2">Back to The Listing</a></h2>
<div class="error">Category ant its contents were deleted.</div>
</div>
<?php } elseif($_GET['_edit']){
	if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." WHERE id='".addslashes($_GET['_edit'])."'"))):
	$info = mysql_fetch_array(mysql_query("SELECT * FROM ".LINKDIR_TABLE_CATS." WHERE id='".addslashes($_GET['_edit'])."'"));
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div><h2>LinkDir Editing a Category <a href="admin.php?page=linkdir_cats" class="add-new-h2">Back to The Listing</a></h2>
<?php if(is_post): if(count($error) > 0): ?>
<div class="error"><?php foreach($error as $x) {
	echo $x . "<br />";
} ?></div>
<?php else: ?>
<div class="saved">Changes have been saved.</div>
<?php endif;
endif; ?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" name="__linkdir_linkdir_save" id="linkdir_save">
<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="title">Title</label></th>
        <td><input type="text" name="__linkdir_name" id="title" maxlength="35" style="width:350px;" value="<?php echo htmlspecialchars(stripslashes($info['title'])); ?>" /></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="description">Description</label></th>
        <td><textarea name="__linkdir_description" id="description" style="width:350px;height:80px;"><?php echo htmlspecialchars(stripslashes($info['description'])); ?></textarea></td>
    </tr>
</table>
<p class="submit"><input id="submit" class="button-primary" type="submit" value="Save the Category" name="submit"> <a href="admin.php?page=linkdir_cats&_delete=<?php echo htmlspecialchars($_GET['_edit']); ?>" onClick="return confirm('Do you really want to delete this category? All links in this category will also be deleted! There is no undo.')" style="color:red;display:inline-block;position:relative;left:30px;">Delete this category</a></p>
</form></div>
<?php endif; } else { ?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>LinkDir Categories<a href="admin.php?new&page=linkdir_cats" class="add-new-h2">Add New</a></h2>
<?php

            $query = mysql_query("SELECT * FROM " . LINKDIR_TABLE_CATS . " ORDER BY title ASC;");

            if(mysql_num_rows($query) > 0) { ?>
<?php while($row = mysql_fetch_array($query)): ?>
<div style="padding:10px;margin:10px 0; border:1px solid #e6e6e6;max-width:450px;"><strong style="font-size:14px;"><?php echo $row['title']; ?></strong> <span style="color:#ccc;">(<?php echo mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE cat = ".$row['id'])); ?>)</span> <a href="admin.php?page=linkdir_cats&_edit=<?php echo $row['id']; ?>"><img src="<?php echo LINKDIR_URL."images/edit.png"; ?>" width="16" height="16" style="position:relative;top:3px;" /></a> <span class="description" style="display:block;padding:5px 0 5px 20px;"><?php echo empty($row['description'])?"&nbsp;":$row['description']; ?></span>
</div>
<?php endwhile; ?>

<?php } else { ?>
	<p class="gray">There are no categories availavble.</p>
<?php } ?>

</div>
<?php }
        endif; ?>