<?php if(end(explode("/", $_SERVER['SCRIPT_FILENAME'])) != "admin_blacklists.php"): ?>

<style type="text/css">
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
<div id="icon-options-general" class="icon32"><br></div><h2>LinkDir Blacklists</h2>

<form method="post" action="admin.php?page=linkdir_blacklists" name="__linkdir_linkdir_save" id="linkdir_save">

<?php if($saved): ?><div class="saved">Blacklist has been updated. Please note that the settings apply only to new links - that means existing links will not be deleted.</div><?php endif; ?>

<p>These filters will allow you to filter out the content you don't want to see. Each value should be seperated by a new line.</p>
<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="domains">Forbidden Domain Names</label><br /><span class="description">Won't accept these URLs in the listing. Can be partial (for example, if you put 'biz', it won't accept any *.biz domains)</span></th>
        <td><textarea name="__linkdir_domains" id="domains" style="width:350px;height:400px;"><?php echo htmlspecialchars($forbidden_domains); ?></textarea></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="ips">Forbidden IP Addresses</label><br /><span class="description">Won't accept new URLs from these IPs</span></th>
        <td><textarea name="__linkdir_ips" id="ips" style="width:350px;height:400px;"><?php echo htmlspecialchars($forbidden_ips); ?></textarea></td>
    </tr>
    <tr valign="top">
        <th scope="row">&nbsp;</th>
        <td><p style="text-align:center; width:350px"><input type="submit" name="__linkdir_submit" value="Save" class="button-primary" /></p></td>
    </tr>
</table>

</form>

</div>

<?php endif; ?>