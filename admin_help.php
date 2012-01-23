<?php if(end(explode("/",$_SERVER['SCRIPT_FILENAME']))!="admin_settings.php"): ?>

<div class="wrap">
<div id="icon-edit-pages" class="icon32"><br></div><h2>LinkDir Documentation</h2>

<style type="text/css">
div.winwin{
	float:right;
	width:208px;
	font-size:10px;
	color:#999;
	text-align:center;
	margin:0 0 15px 15px;
}
div.winwin a{
	display:block;
	width:208px;
	height:45px;
	background:url(<?php echo LINKDIR_URL."/images/winwinhost.png"; ?>) 0 0 no-repeat scroll #333;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	margin-top:4px;
}
</style>

<div class="winwin">THE PLUGIN IS BROUGHT TO YOU BY<a href="http://www.winwinhost.com/" title="Windows Web Hosting">WinWinHost, Inc.</a></div>


<p><strong>1. How can I get it working?</strong></p>
<p>You have to set up a page and put a <code>[load_linkdir]</code> tag where you want the listing to show up. Once you've done that, you may also want to set up the plugin <a href="admin.php?page=linkdir_settings"> the way you want it</a>.</p>


<p><strong>2. I want the listing to show up on my homepage</strong></p>
<p>First, set up a page like it says on #1. After that, head over to <a href="options-reading.php">WordPress Reading Settings</a> and set the front page to display the page you've set up a moment ago.</p>


<p><strong>3. The design doesn't look good / I don't like it</strong></p>
<p>Currently we have only one (default), which can be edited at any time the way you want. For now, it's the only way of customizing it, but we promise we'll add more pre-set designs in further releases. The design files are located in <code><?php echo LINKDIR_URL."design/"; ?></code>.</p>

<p><strong>4. How do pageranks work?</strong></p>
<p>Pageranks are calculated using this formula <code>inbound hits * 1,5 + outbound hits</code>. The plugin runs this formula every <?php echo get_option("recheck_hits"); ?> hours and filters out same IP adresses (maximum 3 hits from one IP, if user agents are different). Then, all links are arranged in descending order by the score, calculated before.</p>


<p><strong>5. I've noticed a bug, what should I do now?</strong></p>
<p>This is a very first release of the plugin, so bugs are predicted to show up. It would be great if you could notice us by clicking <a href="<?php echo LINKDIR_MOREINFO; ?>">here</a>.</p>


<p><strong>6. I feel like it's missing something...</strong></p>
<p>Submit all your ideas <a href="<?php echo LINKDIR_MOREINFO; ?>">here</a>.</p>


</div>

<?php endif; ?>