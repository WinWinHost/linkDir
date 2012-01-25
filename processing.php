<?php

// Don't allow loading the page directly from plugin directory
if(end(explode("/",$_SERVER['SCRIPT_FILENAME']))=="processing.php") die();

global $wpdb;

define("LINKDIR_TABLE",$wpdb->prefix."linkdir");
define("LINKDIR_TABLE_HITS",$wpdb->prefix."linkdir_hits");
define("LINKDIR_TABLE_CATS",$wpdb->prefix."linkdir_cats");
define("LINKDIR_VER","1.1");
define("LINKDIR_DB_VER","1.1");
define("TYPE",$_GET['linkdir'],true);
define("ID","'".addslashes($_GET['_id'])."'",true);
define("ID2",$_GET['id'],true);
define("IS_POST",strtolower($_SERVER['REQUEST_METHOD'])=="post"?true:false,true);
$x = "";


$options = array(
	"linkdir_db_version" => LINKDIR_DB_VER,
	"linkdir_core_version" => LINKDIR_VER,
	"link_back"			=> '<a href="'.get_option("siteurl").'">'.get_option("blogname")."</a>",
	"allow_new_links"	=> 1,
	"recaptcha"			=> 0,
	"recaptcha_public"	=> "",
	"recaptcha_private"	=> "",
	"nofollow"			=> 0,
	"links"				=> "_blank",
	"show_links_num"	=> 1,
	"plugin_powered"	=> 1,
	"track_in"			=> 1,
	"track_out"			=> 1,
	"recheck_hits"		=> 24,
	"design"			=> "default.css",
	"linkdir_check"		=> time(),
	"pagerank_status"	=> 1,
	"show_thumbnails"	=> 1,
	"forbidden_ips"		=> serialize(array()),
	"forbidden_domains"	=> serialize(array())
);

$form_options = array(
	"link_back"			=> array("max"=>500),
	"allow_new_links"	=> array("yesno"=>true),
	"recaptcha"			=> array("yesno"=>true),
	"recaptcha_public"	=> array("max"=>50),
	"recaptcha_private"	=> array("max"=>50),
	"nofollow"			=> array("yesno"=>true),
	"show_links_num"	=> array("yesno"=>true),
	"plugin_powered"	=> array("yesno"=>true),
	"track_in"			=> array("yesno"=>true),
	"track_out"			=> array("yesno"=>true),
	"recheck_hits"		=> array("allowed"=>array(12,24,48)),
	"design"			=> array("max"=>50),
	"show_thumbnails"	=> array("yesno"=>true)
);

function check_tags($content){
	if(preg_match("/\[load\_linkdir\]/",$content)){
		$content_modified = load_linkdir($content);
		return $content_modified;
	}else return $content;
}

function linkdir_style(){
	echo '<link rel="stylesheet" type="text/css" href="'.get_option("siteurl").LINKDIR_URL."design/".get_option("design").'.css" />';
}

function load_linkdir($content){
	x('<div class="linkdir_block" id="linkdir">');
		x('<div class="linkdir_navi">');
			x('<a href="'.getlink("home").'">Home</a>');
			get_option("allow_new_links")==1?x('<a href="'.getlink("addnew").'">Add URL</a>'):NULL;
			x('<a href="'.getlink("linkback").'">Link Back to Us</a>');
		x('</div>'); // .navi
		x('<div class="linkdir_body">');
		if(type=="addnew"){
			if(get_option("allow_new_links")==1):
				foreach(form_addnew() as $key => $val){
					$$key = $val;
				}
				if($error){
					x('<p class="linkdir_error">'.$error_msg.'</p>');
				}elseif($success){
					x('<p class="linkdir_success">'.$success_msg.'</p>');
				}
				x('<p>If you want your web page to appear in our directory listing, you can submit it here.</p>');
				x('<form method="post" action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'#linkdir">');
					x('<p><label class="linkdir_label" for="linkdirname">Title:</label> <input type="text" id="linkdirname" name="___title" value="" maxlength="35" /></p>');
					x('<p><label class="linkdir_label" for="linkdirdescription">Short Description:</label> <textarea id="linkdirdescription" name="___desc" /></textarea></p>');
					x('<p><label class="linkdir_label" for="urlinput">Site URL:</label> <input id="urlinput" type="text" name="___url" value="http://" maxlength="1000" /><br /><span class="linkdir_desc">Example: http://google.com/, http://news.yahoo.com/ and so on...</span></p>');
					x('<p><label class="linkdir_label" for="linkdircat">Category:</label> <select name="___cat" id="linkdircat">');
						$cats = load_cats();
						if(count($cats)>0):
							foreach($cats as $id => $name){
								x('<option value="'.$id.'">'.$name.'</option>');
							}
						endif;
					x('</select></p>');
					if(get_option("recaptcha")==1&&strlen(get_option('recaptcha_public'))>0&&strlen(get_option('recaptcha_private'))>0):
						require_once(LINKDIR_ROOT."recaptcha.php");
						x("<p>".recaptcha_get_html(get_option("recaptcha_public"))."</p>");
					endif;
					x('<p><input type="submit" name="___submit" value="Submit Site" /></p>');
				x('</form>');
			else:
				x("<p>Sorry, an option to submit new links is currently disabled. Please check back later.</p>");
			endif;
		}elseif(type=="linkback"){
			x("<p>To get a higher pagerank, you must link back to us by placing this code in your website:</p>");
			x('<p><textarea readonly="readonly" onClick="this.focus();this.select();">'.stripslashes(get_option("link_back")).'</textarea></p>');
			x('<p>The code above will output:</p>');
			x(stripslashes(get_option("link_back")));
		}elseif(type=="cat"){
			if(cat_exists()){
				$cat = get_cat(ID);

				$num = links_in_cat($cat['id']);
				$pages = floor($num/20);
				$page = is_numeric($_GET['_page'])&&$_GET['_page']>0&&$_GET['_page']<=$pages?floor($_GET['_page']):1;
				$links_per_page = 20;
				$limit = "LIMIT ".(($page-1)*$links_per_page).",".$links_per_page;

				x('<h2 class="linkdir_cat_title">'.stripslashes($cat['title']).'</h2>');
				strlen($cat['description'])>0?x('<p class="linkdir_desc">'.stripslashes($cat['description']).'</p>'):NULL;

				x('<div class="linkdir_listing">');

				if(mysql_num_rows(mysql_query("SELECT * FROM ".LINKDIR_TABLE." WHERE cat=".ID))>0):

					$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE." WHERE cat=".ID." ORDER BY pagerank ASC ".$limit) or die($pages." => ".$page." ".mysql_error());
					if(mysql_num_rows($query)>0):
						while($row = mysql_fetch_array($query)){
							x('<div class="linkdir_link_unit">');
								$rel = 'rel="'.(get_option("nofollow")==1?"nofollow":"follow").'"';

								get_option("show_thumbnails")==1?x('<a style="display:block;margin-right:15px;float:left;width:100px;height:68px;background:url(http://api1.thumbalizr.com/?url='.($row['ssl']==1?"https://":"http://").$row['url'].'&width=100) 0 0 no-repeat scroll;" href="'.($row['ssl']==1?"https://":"http://").$row['url']."/".'" onClick="count_out('.$row['id'].')" target="_blank"></a>'):NULL;

								x('<a target="_blank" '.$rel.' href="'.($row['ssl']==1?"https://":"http://").$row['url']."/".'" onClick="count_out('.$row['id'].')">'
								.htmlspecialchars(stripslashes($row['name'])).'</a>');
								 
								

								x(' <span class="linkdir_small_url">'.$row['url'].'</span>');

								x('<p class="linkdir_link_desc">'.htmlspecialchars(stripslashes($row['description'])).'</p>');
							x('</div>');
						}

						x('<script type="text/javascript">');
							x('function count_out(linkdir_id){var xmlhttp;if(window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}xmlhttp.open("GET","/index.php?linkdir_count_click_out="+linkdir_id,true);xmlhttp.send();}');
						x('</script>');
					endif;

					if($pages>1):
						x('<div class="linkdir_pagenavi">');
							$page>1?x('<a href="'.getlink("cat",ID2,($page-1)).'">&laquo;</a>'):NULL;
							$looped = 0;
							$current = $page>3&&$pages>7?($page-3):1;
								while($current<=$pages){
									$class = $current==$page?'class="current"':'';
									x(' <a '.$class.' href="'.getlink("cat",ID2,$current).'">'.$current.'</a> ');
									$current++; $looped++;

									if($looped==7): break; endif;
								}
							$pages!=$page?x('<a href="'.getlink("cat",ID2,($page+1)).'">&raquo;</a>'):NULL;
						x('</div>'); // .linkdir_navi
					endif;

				else:
					x('<p class="linkdir_empty">Sorry, there are no links in this category.</p>');
				endif;
				x('</div>'); // .linkdir_listing
			}else{
				x('<p class="linkdir_empty">Sorry, this category no longer exists.</p>');
			}
		}else{
			$cats = load_cats(true);
			if(count($cats)>0){
				foreach($cats as $id => $data){
					x('<div class="linkdir_unit" href="'.getlink("cat",$id).'">');
						$count = mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE cat=".$id));
						$count = get_option("show_links_num")==1?' <span class="linkdir_num">'.$count.'</span>':'';
						x('<a class="linkdir_title" href="'.getlink("cat",$id).'">'.$data['title'].$count.'</a>');
						strlen($data['description'])>0?x('<a class="linkdir_description" href="'.getlink("cat",$id).'">'.$data['description'].'</a>'):NULL;
					x('</div>'); // .linkdir_unit
				}
			}
		}
		x('</div>'); // .linkdir_body
		get_option("plugin_powered")==1?x('<p style="text-align:center !important;"><a href="'.LINKDIR_MOREINFO.'" title="Powered By WinWinHost" class="linkdir_poweredby"></a></p>'):NULL;
	x('</div>'); // .linkdir_block
	return x(true);
}

function linkdir_statuscheck(){
	global $options;

	if(get_option("linkdir_exists")!="yes"){
		add_option("linkdir_exists","yes");

		foreach($options as $name => $val){
			setopt($name,$val);
		}
	}

	// version check
	if(get_option("lindir_core_version")!=LINKDIR_VER){
		setopt("linkdir_core_version",LINKDIR_VER,true);
	}

	// db version check
	if(get_option("linkdir_db_version")!=LINKDIR_DB_VER){

	}

	// check outbound links
	if(isset($_GET['linkdir_count_click_out'])){
		outbound_track($_GET['linkdir_count_click_out']);
	}

	// check inbound links
	if(!empty($_SERVER['HTTP_REFERER'])&&!linkdir_self()){
		inbound_track();
	}

	// Recount pr?
	if(get_option("pagerank_status")==0){
		recount_pagerank();
		update_option("pagerank_status",1);
	}

	// Recount stats?
	if(get_option("recheck_hits")>0){
		$next_check = get_option("linkdir_check")+get_option("recheck_hits")*3600;

		if($next_check<=time()){
			$limit = get_option("linkdir_check");
			update_option("linkdir_check",time()-1);
			recount_stats($limit);
			update_option("pagerank_status",0);
		}
	}
}

function linkdir_install(){
	mysql_query("CREATE TABLE IF NOT EXISTS `".LINKDIR_TABLE."` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL,
	  `cat` int(11) NOT NULL,
	  `url` varchar(100) NOT NULL,
	  `ssl` tinyint(1) NOT NULL,
	  `description` varchar(250) NOT NULL,
	  `submitted_by` text CHARACTER SET ascii NOT NULL,
	  `submitted_on` int(11) NOT NULL,
	  `hitsin` int(11) NOT NULL,
	  `hitsout` int(11) NOT NULL,
	  `pagerank` int(11) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `url` (`url`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

	mysql_query("CREATE TABLE IF NOT EXISTS `".LINKDIR_TABLE_CATS."` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(50) NOT NULL,
	  `description` varchar(250) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;");

	mysql_query("CREATE TABLE IF NOT EXISTS `".LINKDIR_TABLE_HITS."` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `for` int(11) NOT NULL,
	  `type` varchar(3) NOT NULL,
	  `ip` varchar(35) NOT NULL,
	  `ua` varchar(200) NOT NULL,
	  `time` int(11) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `type` (`type`,`ua`),
	  KEY `time` (`time`),
	  KEY `for` (`for`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
}

function linkdir_menu(){
	add_menu_page("LinkDir Settings","LinkDir Options","level_7","linkdir_settings","linkdir_settings");
	add_submenu_page("linkdir_settings","LinkDir Blacklists","Blacklists","level_7","linkdir_blacklists","linkdir_blacklists");
	add_submenu_page("linkdir_settings","LinkDir Links","Links","level_7","linkdir_links","linkdir_links");
	add_submenu_page("linkdir_settings","LinkDir Categories","Categories","level_7","linkdir_cats","linkdir_cats");
	add_submenu_page("linkdir_settings","LinkDir FAQs","FAQs","level_7","linkdir_faq","linkdir_help");
}

function linkdir_links(){


	require_once("admin_links.php");
}

function linkdir_settings(){
	global $form_options;

	if(is_post){
		foreach($_POST as $key => $value){
			$key = substr($key,(strlen($key)>strlen("__linkdir_")?strlen("__linkdir_"):0));
			$clear = true;

			if(array_key_exists($key,$form_options)){
				$check = $form_options[$key];

				if(isset($check['max'])&&$clear){
					$clear = strlen($value)>$check['max']?false:true;

					if($clear){
						update_option($key,$value);
					}else{
						$error[$key] = "Sorry, an unknown value has been selected on ".$key." field. Please check if the data you're trying to insert is correct and try again.";
					}
				}

				if(isset($check['yesno'])&&$clear){
					$clear = $value==1||$value==0?true:false;
					if($clear){
						update_option($key,$value);
					}else{
						$error[$key] = "Sorry, an unknown value has been selected on ".$key." field. Please check if the data you're trying to insert is correct and try again.";
					}
				}

				if(isset($check['allowed'])&&$clear){
					$clear = in_array($value,$check['allowed']);

					if($clear){
						update_option($key,$value);
					}else{
						$error[$key] = "Sorry, an unknown value has been selected on ".$key." field. Please check if the data you're trying to insert is correct and try again.";
					}
				}
			}
		}
	}

	require_once("admin_settings.php");
}

function linkdir_blacklists(){
	if(is_post&&isset($_POST['__linkdir_ips'])&&isset($_POST['__linkdir_domains'])){

		$domains 	= explode("\n",$_POST['__linkdir_domains']);
		$ips		= explode("\n",$_POST['__linkdir_ips']);

		foreach($domains as $key => $val){
			if(!empty($val)){
				$val = trim($val);
				$domains_update[$val] = $val;
			}
		}

		foreach($ips as $key => $val){
			if(!empty($val)){
				$val = trim($val);
				$ips_update[$val] = $val;
			}
		}

		update_option("forbidden_domains",($domains_update));
		update_option("forbidden_ips",($ips_update));

		$saved = true;
	}

	$forbidden_ips_data = get_option("forbidden_ips");
	$forbidden_domains_data = get_option("forbidden_domains");

	$forbidden_ips_data = !is_array($forbidden_ips_data)?array():$forbidden_ips_data;
	$forbidden_domains_data = !is_array($forbidden_domains_data)?array():$forbidden_domains_data;

	$forbidden_ips = "";
	$forbidden_domains = "";

	if(count($forbidden_ips_data)>0): foreach($forbidden_ips_data as $ip){$forbidden_ips .= $ip."\n";} endif;
	if(count($forbidden_domains_data)>0): foreach($forbidden_domains_data as $domain){$forbidden_domains .= $domain."\n";} endif;

	require_once("admin_blacklists.php");
}

function linkdir_help(){
	require_once("admin_help.php");
}

function linkdir_cats(){
	if(is_post&&isset($_GET['new'])){
		$name = $_POST['__linkdir_name'];
		$desc = $_POST['__linkdir_description'];

		if(strlen($name)>0){
			if(strlen($name)<=35){
				// OK
			}else $error['name'] = "Title is too long (max. 35 characters).";
		}else $error['name'] = "Title cannot be empty.";

		if(strlen($desc)>200){
			$error['cats'] = "Category description is too long (max. 200 characters).";
		}

		if(count($error)==0){
			$name = addslashes($name);
			$description = addslashes($desc);

			mysql_query("INSERT INTO ".LINKDIR_TABLE_CATS." (id,title,description) VALUES (NULL,'$name','$description');");
		}
	}elseif(is_post&&isset($_GET['_edit'])){
		if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." WHERE id='".addslashes($_GET['_edit'])."'"))>0){
			$name = addslashes($_POST['__linkdir_name']);
			$description = addslashes($_POST['__linkdir_description']);

			if(strlen($name)==0){
				$error['name'] = "Title cannot be empty.";
			}

			if(strlen($description)>200){
				$error['cats'] = "Category description is too long (max. 200 characters).";
			}

			if(count($error)==0){
				mysql_query("UPDATE ".LINKDIR_TABLE_CATS." SET title='$name',description='$description' WHERE id='".addslashes($_GET['_edit'])."';");
			}
		}
	}elseif(isset($_GET['_delete'])){
		if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." WHERE id='".addslashes($_GET['_delete'])."'"))>0){
			$id = "'".addslashes($_GET['_delete'])."';";
			mysql_query("DELETE FROM ".LINKDIR_TABLE." WHERE cat=".$id);
			mysql_query("DELETE FROM ".LINKDIR_TABLE_CATS." WHERE id=".$id);
		}
	}
	require_once("admin_cats.php");
}

function linkdir_disable(){
	delete_option("linkdir_exists");
}

function setopt($name,$val,$update = false){
	if(get_option($val)===false){
		add_option($name,$val);
	}else{
		$update?update_option($name,$val):NULL;
	}
}

$linkdir_loaded = false;

function x($add){
	global $linkdir_loaded;
	if($linkdir_loaded==false){
		$GLOBALS['x'] =
"

<!-- The code bellow was generated by a plugin named LinkDir. You can find more information about it by going to ".LINKDIR_MOREINFO.". Thanks! -->

";
		$linkdir_loaded = true;
	}
	if($add===true){
$GLOBALS['x'] =$GLOBALS['x'].
"

<!-- /LINKDIR PLUGIN -->


";
		return $GLOBALS['x'];
	}else{
		$GLOBALS['x'] = $GLOBALS['x'].$add;
	}
}

function getlink($type,$id = "",$page = ""){
        global $post;
        $dir = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ;
        $parsedURL = parse_url ($dir);

        
        $splitPath = explode ('/', $parsedURL['path']); 
        
        $str = get_option("siteurl")."/".$splitPath[2] ."/?".(!isset($_GET['page_id'])?"page_id=".$post->ID."&":"")."linkdir=".$type.(!empty($id)?"&_id=".$id:"");
        //default url 
        //?post_id=2&linkdir=cat&_id=2&page_id=2#linkdir
        
        //seo url
        //?post_id=2&linkdir=cat&_id=2#linkdir
        foreach($_GET as $key => $val){
                if($key!="linkdir"&&$key!="_id"&&$key!="_page"){
                        $str .= "&".urlencode($key)."=".urlencode($val);
                }
        }
        return $str.(!empty($page)?"&_page=".$page:"")."#linkdir";
        
}

function load_cats($full = false){
	if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." LIMIT 0,1;"))>0){
		$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE_CATS." ORDER BY title ASC;");
		while($x = mysql_fetch_array($query)){
			if($full){
				$cats[$x['id']] = $x;
			}else{
				$cats[$x['id']] = $x['title'];
			}
		}
		return $cats;
	}else{
		return array();
	}
}

function form_addnew(){
	if(is_post){
		$error = false;
		$success = false;
		$error_msg = "";
		$success_msg = "";

		if(get_option("recaptcha")==1){
			require_once("recaptcha.php");
			$resp = recaptcha_check_answer(get_option("recaptcha_private"),$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
			
			if($resp->is_valid){
				$continue = true;
			}else{
				$continue = false;
				$error_msg = "The security code you've entered doesn't seem to be valid.";
			}
		}else $continue = true;

		if($continue){

			// Check domain name
			$ssl = substr($_POST['___url'],0,5)=="https"?1:0;
			//$_POST['___url'] = preg_replace("/https\:\/\//","http://",$_POST['___url']);
			//preg_match('@^(?:http://)?([^/]+)@i',$_POST['___url'],$matches);
			//$host = strtolower($matches[1]);
			$host = $_POST['___url'];
			
			//if(strlen($host)<4||!is_valid_domain_name($host)){
				if(strlen($host)<4){
				$error_msg = "The URL you've entered doesn't seem to be valid.";
			}//else{
				// Check if it is blacklisted
			/*	$var = explode(".",$host); krsort($var);
			 
				$add = ""; $domain = ""; $check = array();
				foreach($var as $part){
					$add = empty($domain)?$part:$part.".";
					$domain = $add.$domain;
					echo $check[$domain] = $domain;
				}
				die();

				$forbidden_domains = get_option("forbidden_domains");
				$forbidden_domains = !is_array($forbidden_domains)?array():$forbidden_domains;

				// Run the filter
				foreach($check as $domain_part){
					if(array_key_exists($domain_part,$forbidden_domains)){
						$error_msg = "Sorry, there was an error submitting this URL.";
					}
				}

				// Check if it already exists
				$type1 = substr($host,0,4)=="www."?substr($host,4):$host;
				$type2 = substr($host,0,4)=="www."?$host:"www.".$host;
				//echo $host."<br />";
				//echo $type1."<br />";
				//echo $type2;
				//die();
				$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE." WHERE url='$type1' OR url='$type2';");
				if(mysql_num_rows($query)>0){
					$error_msg = "This URL already exists in our listing.";
				} 
			//}
				*/
			// Category
			if(is_numeric($_POST['___cat'])){
				if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." WHERE id='".addslashes($_POST['___cat'])." LIMIT 0,1';"))>0){
					// OK
				}else $error_msg = "An unknown category has been selected.";
			}else $error_msg = "An unknown category has been selected.";

			// Title
			if(strlen($_POST['___title'])>0){
				if(strlen($_POST['___title'])<=35){
					// OK
				}else $error_msg = "Title is too long (max. 35 characters).";
			}else $error_msg = "Title cannot be empty.";

			// Description
			if(strlen(htmlspecialchars($_POST['___desc']))>150){
				$error_msg = "Description is too long (max. 150 characters).";
			}

			$forbidden_ips = get_option('forbidden_ips');
			$forbidden_ips = !is_array($forbidden_ips)?array():$forbidden_ips;
			
			
			if(array_key_exists($_SERVER['REMOTE_ADDR'],$forbidden_ips)){
				$error_msg = "Sorry, there was an error submitting your URL.";
			}
		}

		$error = !empty($error_msg)?true:false;

		if(!$error){
			
  

			url_new($host,$ssl,$_POST['___cat'],$_POST['___title'],$_POST['___desc']);

			$success_msg = "Your URL has been submitted. Thanks!";
			$success = true;
		}

		return array(
			"error" 		=> $error,
			"error_msg"		=> $error_msg,
			"success"		=> $success,
			"success_msg"	=> $success_msg
		);
	}

	return array("is_post" => false);
}

function url_new($host,$ssl,$cat,$title,$desc){

	
	$host = addslashes($host);
	$title = addslashes($title);
	$desc = addslashes($desc);
	$submitted = addslashes(serialize(array(
		"ip" 	=> $_SERVER['REMOTE_ADDR'],
		"agent"	=> $_SERVER['HTTP_USER_AGENT'],
		"etc"	=> $_SERVER
	)));
    $host = str_replace("https://", "",$host);
    $host = str_replace("http://", "",$host);
    
    

	mysql_query("INSERT INTO ".LINKDIR_TABLE." (name,url,cat,`ssl`,description,submitted_by,submitted_on,hitsin,hitsout,pagerank) VALUES('$title','$host','$cat','$ssl','$desc','$submitted','".time()."','0','0','777');");
	
	return true;
}

function is_valid_domain_name($domain_name){
	$pieces = explode(".",$domain_name);
	foreach($pieces as $piece){
		if(!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i',$piece)||preg_match('/-$/',$piece)){
			return false;
		}
	}
	return true;
}
 

function cat_exists($id = true){
	$id = $id===true?ID:$id;
	return mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE_CATS." WHERE id=".$id." LIMIT 0,1;"))>0?true:false;
}

function get_cat($id){
	return mysql_fetch_array(mysql_query("SELECT * FROM ".LINKDIR_TABLE_CATS." WHERE id=".$id." LIMIT 0,1"));
}

function outbound_track($id){
	if(mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE id='".addslashes($id)."' LIMIT 0,1;"))>0){
		$ua = addslashes(substr($_SERVER['HTTP_USER_AGENT'],0,50));
		mysql_query("INSERT INTO ".LINKDIR_TABLE_HITS." (id,`for`,processed,type,ip,ua,time) VALUES (NULL,".$id.",0,'out','".$_SERVER['REMOTE_ADDR']."','".$ua."','".time()."');");
	}

	// either way, kill the execution of the script
	die();
}

function inbound_track(){
	$_SERVER['HTTP_REFERER'] = preg_replace("/https\:\/\//","http://",$_SERVER['HTTP_REFERER']);
	preg_match('@^(?:http://)?([^/]+)@i',$_SERVER['HTTP_REFERER'],$matches);
	$host = strtolower($matches[1]);

	if(is_valid_domain_name($host)){
		$query = mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE url LIKE '%$host%';");
		if(mysql_num_rows($query)>0){

			$id = mysql_fetch_array($query);
			$ua = addslashes(substr($_SERVER['HTTP_USER_AGENT'],0,50));
			mysql_query("INSERT INTO ".LINKDIR_TABLE_HITS." (id,`for`,processed,type,ip,ua,time) VALUES (NULL,".$id['id'].",0,'in','".$_SERVER['REMOTE_ADDR']."','".$ua."','".time()."');");
		}
	}
}

function linkdir_self(){
	$ok = preg_replace("/https\:\/\//","http://",get_option("siteurl"));
	preg_match('@^(?:http://)?([^/]+)@i',$ok,$matches);
	$host = strtolower($matches[1]);

	$host = preg_replace("/www\./","",$host);

	$ok2 = preg_replace("/https\:\/\//","http://",$_SERVER['HTTP_REFERER']);
	preg_match('@^(?:http://)?([^/]+)@i',$ok2,$matches);
	$host2 = strtolower($matches[1]);
	$host2 = preg_replace("/www\./","",$host2);
    
	return $host==$host2?true:false;
}

function recount_stats($limit){

	/**
	* 	WARNING: This function may slow down the server if the site has big traffic and/or the webserver has a very low PHP memory limit.
	* 	         Please contact your hosting for more info.
	**/

	// inbound links

	if(mysql_num_rows(mysql_query("SELECT * FROM ".LINKDIR_TABLE_HITS." WHERE type='in' AND processed=0"))>0){
		$hits = mysql_query("SELECT DISTINCT id FROM ".LINKDIR_TABLE_HITS." WHERE type='in' AND processed=0");

		// loop all sites that received at least one inbound hit
		while($site = mysql_fetch_array($hits)){

			// loop hits for a specific site
			$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE_HITS." WHERE `for`=".$site['id']." AND type='in' AND processed=0");
			while($row = mysql_fetch_array($query)){
				$in_hits[$row['ip'].$row['ua']] = $row['ip'];
				$in_ips[$row['ip']] = $row['ip'];
			}

			$in_hits_final = 0;

			// will filterout hits that are from the same ip address
			if(count($in_ips)>0):foreach($in_ips as $ip){
				$hits_from_one_ip = count(array_keys($in_hits,$ip));
				$in_hits_final = $in_hits_final+($hits_from_one_ip>=3?3:$hits_from_one_ip);
			}endif;

			mysql_query("UPDATE ".LINKDIR_TABLE." SET hitsin=hitsin+".$in_hits_final." WHERE id=".$site['id']);
		}

	}

	// outbound links

	if(mysql_num_rows(mysql_query("SELECT * FROM ".LINKDIR_TABLE_HITS." WHERE type='out' AND processed=0"))>0){
		$hits = mysql_query("SELECT DISTINCT id FROM ".LINKDIR_TABLE_HITS." WHERE type='out' AND processed=0");

		// loop all sites that received at least one outbound hit
		while($site = mysql_fetch_array($hits)){

			// loop hits for a specific site
			$query = mysql_query("SELECT * FROM ".LINKDIR_TABLE_HITS." WHERE `for`=".$site['id']." AND type='out' AND processed=0");
			while($row = mysql_fetch_array($query)){
				$out_hits[$row['ip'].$row['ua']] = $row['ip'];
				$out_ips[$row['ip']] = $row['ip'];
			}

			$out_hits_final = 0;

			// will filterout hits that are from the same ip address
			if(count($out_ips)):foreach($out_ips as $ip){
				$hits_from_one_ip = count(array_keys($out_hits,$ip));
				$out_hits_final = $out_hits_final+($hits_from_one_ip>=3?3:$hits_from_one_ip);
			}endif;

			mysql_query("UPDATE ".LINKDIR_TABLE." SET hitsout=hitsout+".$out_hits_final."
			 WHERE id=".$site['id']);
		}
	}

	mysql_query("UPDATE ".LINKDIR_TABLE_HITS." SET processed=1;");
}

function recount_pagerank(){
	$get = mysql_query("SELECT id,cat,hitsin,hitsout,pagerank FROM ".LINKDIR_TABLE.";");

	while($x = mysql_fetch_array($get)){
		$category[$x['cat']]			= $x['cat'];
		$score[$x['cat']][$x['id']]		= floor($x['hitsin']*1.5)+$x['hitsout'];
	}

	if(count($category)>0):foreach($category as $id){
		arsort($score[$id],SORT_NUMERIC);

		$pagerank = 1;

		// Resets all URLs
		mysql_query("UPDATE ".LINKDIR_TABLE." SET pagerank=777 WHERE cat=".$id);

		// Updates it again
		foreach($score[$id] as $url_id => $score){
			if($pagerank>50){break;}
			mysql_query("UPDATE ".LINKDIR_TABLE." SET pagerank = ".$pagerank." WHERE id=".$url_id);
			$pagerank++;
		}

	}endif;

}

function links_in_cat($id){
	return mysql_num_rows(mysql_query("SELECT id FROM ".LINKDIR_TABLE." WHERE cat='".addslashes($id)."';"));
}

function linkdir_set($data = array()){
	if(!is_array($data)){
		return false;
	}else{
		if(count($data)>0){
			$query = "";
			foreach($data as $key => $val){
				$query .= " `$key` = '".addslashes($val)."',";
			}
			return substr($query,0,strlen($query)-1);
		}
	}
}
