<?php
/*
 * Copyright (c) 2010 Björn Ahrens
 *
 * This file is part of "Unprintable Blog"
 *
 * "Unprintable Blog" is based on wp-mpdf 2.5 by Florian Krauthan.
 *
 * "Unprintable Blog" is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * "Unprintable Blog" is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with "Unprintable Blog". If not, see <http://www.gnu.org/licenses/>.
 */

 
function gcp_pdf_admin_display($slug) {
	$icon_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/img/unprintable_32.png';
	?>
	<div class="wrap">
		<div style="background:url('<?php echo $icon_path;?>') no-repeat;"  class="icon32"></div>
		<?php
		if ($slug==='gcp_pdf') { ?>
			<h2><?php echo 'Unprintable Blog - '.__('Settings','gcp-pdf'); ?></h2>
			<?php
			gcp_pdf_admin_options();
		} else if ($slug==='gcp_pdf_cache') { ?>
			<h2><?php echo 'Unprintable Blog - '.__('Cache','gcp-pdf'); ?></h2>
			<?php
			gcp_pdf_admin_cache();
		} else if ($slug==='gcp_pdf_customnames') { ?>
			<h2><?php echo 'Unprintable Blog - '.__('Custom PDF Names','gcp-pdf'); ?></h2>
			<?php
			gcp_pdf_admin_pdfname();
		} else if ($slug==='gcp_pdf_bwlists') { ?>
			<h2><?php echo 'Unprintable Blog - '.__('Black/White Lists','gcp-pdf'); ?></h2>
			<?php
			gcp_pdf_admin_allowedprintedpages();
			gcp_pdf_admin_loginneededpages();
		} else if ($slug==='gcp_pdf_stats') { ?>
			<h2><?php echo 'Unprintable Blog - '.__('Download Statistics','gcp-pdf');?></h2>
			<?php
			gcp_pdf_admin_stats();
		}
		?>
	</div>
	<?php
}

function gcp_pdf_admin_options() { 
	$icon_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/img/';
	?>
	<div id="poststuff" class="metabox-holder">
	
	<div style="min-height:90px;">
		<div style="float:right">
			<a href="http://www.greencomputingportal.de"><img height="82" width="272" src="<?php echo $icon_path;?>GCP_logo.png"/><br/></a>
		</div>
		<?php _e('Unnecessary printing not only means unnecessary cost of paper and inks, but also avoidable environmental impact on producing and shipping these supplies. Reducing printing can make a small but a significant impact. Take action to help reduce paper waste - make your blog "unprintable".','gcp-pdf'); ?>
	</div>
	
	<?php
	if(isset($_POST['save_options'])) {
		update_option('gcp_pdf_theme', $_POST['theme']);
		update_option('gcp_pdf_noprint_theme', $_POST['noprinttheme']);
        update_option('gcp_pdf_code_page', $_POST['codepage']);
        update_option('gcp_pdf_cron_user', $_POST['cron_user']);
		update_option('gcp_pdf_caching', isset($_POST['caching']));
		update_option('gcp_pdf_geshi', isset($_POST['geshi']));
		update_option('gcp_pdf_geshi_linenumbers', isset($_POST['geshi_linenumbers']));
		update_option('gcp_pdf_stats', isset($_POST['stats']));
		update_option('gcp_pdf_debug', isset($_POST['debug']));
		update_option('gcp_pdf_noprintdiv', isset($_POST['noprintdiv']));
		update_option('gcp_pdf_showmetabox', isset($_POST['showmetabox']));
		update_option('gcp_pdf_appendbutton', isset($_POST['appendbutton']));
		
		if(isset($_POST['allow_all'])) {
			update_option('gcp_pdf_allow_all', true);
		}
		else {
			update_option('gcp_pdf_allow_all', $_POST['use_list_as']);
		}
		
		if(!isset($_POST['need_login'])) {
			update_option('gcp_pdf_need_login', false);
		}
		else {
			update_option('gcp_pdf_need_login', $_POST['login_use_list_as']);
		}
		
		echo '<div class="updated"><p>'.__('Options saved','gcp-pdf').'</p></div>';
	}
	
	?>
	<form action="?page=<?php echo $_GET['page']; ?>" method="post">
	
	<div class="postbox">
		<h3 class="hndle"><?php _e('General Settings','gcp-pdf'); ?></h3>
		<div class="inside">
			<table class="form-table"><tbody>
				<tr><th scope="row"><label for="appendbutton"><?php _e('Append PDF button to posts','gcp-pdf'); ?></label></th>
				<td><input type="checkbox" name="appendbutton" <?php if (get_option('gcp_pdf_appendbutton')==true) echo 'checked="checked"'; ?>/></td></tr>

				<tr><th scope="row"><label for="noprintdiv"><?php _e('Show "No-Print" message when printing','gcp-pdf'); ?></label></th>
				<td><input type="checkbox" name="noprintdiv" <?php if (get_option('gcp_pdf_noprintdiv')==true) echo 'checked="checked"'; ?>/></td></tr>

				<tr><th scope="row"><label for="noprinttheme"><?php _e('"No-Print" template','gcp-pdf'); ?></label></th>
				<td><select name="noprinttheme">
				<?php
				//Search for Themes
				if($dir = opendir(WP_GCP_PDF_NOPRINTTHEME_PATH)) {
					while($file = readdir($dir)) {
						if(!is_dir($path.$file) && $file != "." && $file != "..")  {
							if(strtolower(substr($file, count($file)-4))=='php') {
								echo '<option value="'.substr($file, 0, count($file)-5).'" ';
								if(get_option('gcp_pdf_noprint_theme')==substr($file, 0, count($file)-5)) {
									echo 'selected="selected"';
								}
								echo '>'.str_replace('_', ' ', substr($file, 0, count($file)-5)).'</option>';
							}
						}
					}
				}
				?>
				</select></td></tr>
				
				<tr><th scope="row"><label for="caching"><?php _e('PDF caching','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="caching" <?php	if(get_option('gcp_pdf_caching')==true) echo 'checked="checked"'; ?> /></td></tr>

				<tr><th scope="row"><label for="stats"><?php _e('Download stats','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="stats" <?php if(get_option('gcp_pdf_stats')==true) echo 'checked="checked"'; ?> /></td></tr>

				<tr><th scope="row"><label for="debug"><?php _e('Enable Debuging','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="debug" <?php if(get_option('gcp_pdf_debug')==true) echo 'checked="checked"'; ?> /></td></tr>

				<tr><th scope="row"><label for="showmetabox"><?php _e('Show metabox when editing posts','gcp-pdf'); ?> <label/></th>
				<td><input type="checkbox" name="showmetabox" <?php if (get_option('gcp_pdf_showmetabox')==true) echo 'checked="checked"'; ?> /></td></tr>
				
				<tr><th scope="row"><label for="cron_user"><?php _e('User for generating per Cron','gcp-pdf');?></label></th>
				<td><select name="cron_user">
					<option value="" <?php if(get_option('gcp_pdf_cron_user')=='') echo 'selected="selected"';?>><?php _e('None','gcp-pdf');?></option>
					<option value="auto" <?php if(get_option('gcp_pdf_cron_user')=='auto') echo 'selected="selected"';?>><?php _e('Auto','gcp-pdf');?></option>
					<?php
					//Cron generating User
					global $wpdb;
					$aUsersID = $wpdb->get_col($wpdb->prepare('SELECT ID FROM '.$wpdb->users.' ORDER BY user_nicename ASC')); 
					foreach($aUsersID as $iUserID) {
						$user = get_userdata($iUserID);
						echo '<option value="'.$iUserID.'" ';
						if($iUserID == get_option('gcp_pdf_cron_user')) {
							echo 'selected="selected"';
						}
						echo '>'.$user->user_nicename.'</option>';
					}
					?>
				</select></td></tr>
			</tbody></table>
		</div>
	</div>

	<div class="postbox">
		<h3 class="hndle"><?php _e('PDF Settings','gcp-pdf'); ?></h3>
		<div class="inside">
			<table class="form-table"><tbody>		
				<tr><th scope="row"><label for="theme"><?php _e('Theme','gcp-pdf'); ?></label></th>
				<td><select name="theme">
				<?php
				//Search for Themes
				if($dir = opendir(WP_GCP_PDF_THEME_PATH)) {
					while($file = readdir($dir)) {
						if(!is_dir($path.$file) && $file != "." && $file != "..")  {
							if(strtolower(substr($file, count($file)-4))=='php') {
								echo '<option value="'.substr($file, 0, count($file)-5).'" ';
								if(get_option('gcp_pdf_theme')==substr($file, 0, count($file)-5)) {
									echo 'selected="selected"';
								}
								echo '>'.str_replace('_', ' ', substr($file, 0, count($file)-5)).'</option>';
							}
						}
					}
				}
				?>
				</select></td></tr>
				
				<tr><th scope="row"><label for="codepage"><?php _e('Codepage','gcp-pdf');?></label></th>
				<td><select name="codepage">
				<?php
				$CODEPAGES_ARRAY = array('utf-8', 'win-1251', 'win-1252', 'iso-8859-2', 'iso-8859-4', 'iso-8859-7', 'iso-8859-9', 'big5', 'gbk', 'uhc', 'shift_jis');
				$cur_cp = get_option('gcp_pdf_code_page');
				if($cur_cp == '') $cur_cp = 'utf-8';
				foreach($CODEPAGES_ARRAY as $cp) {
					echo '<option value="'.$cp.'" ';
					if($cur_cp==$cp) {
						echo 'selected="selected"';
					}
					echo '>'.$cp.'</option>';
				}
				?>
				</select></td></tr>
	
				<tr><th scope="row"><label for="geshi"><?php _e('GeSHi Parsing','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="geshi" <?php if(get_option('gcp_pdf_geshi')==true) echo 'checked="checked"'; ?> /></td></tr>

				<tr><th scope="row"><label for="geshi_linenumbers"><?php _e('GeSHi line numbers','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="geshi_linenumbers" <?php if(get_option('gcp_pdf_geshi_linenumbers')==true) echo 'checked="checked"'; ?> /></td></tr>
			</tbody></table>
		</div>
	</div>
	
	<div class="postbox">
		<h3 class="hndle"><?php _e('Access Settings','gcp-pdf');?></h3>
		<div class="inside">
			<table class="form-table"><tbody>
				<tr><th scope="row"><label for="allow_all"><?php _e('Allow PDF download of all posts','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="allow_all" <?php if(get_option('gcp_pdf_allow_all')==1) echo 'checked="checked"'; ?> /></td></tr>

				<tr><th scope="row"><label for="use_list_as"><?php _e('If not use list as','gcp-pdf');?></label></th>
				<td><select name="use_list_as">
					<option value="2" <?php if(get_option('gcp_pdf_allow_all')==2) echo 'selected="selected"'; ?>><?php _e('Whitelist','gcp-pdf');?></option>
					<option value="3" <?php if(get_option('gcp_pdf_allow_all')==3) echo 'selected="selected"'; ?>><?php _e('Blacklist','gcp-pdf');?></option>
				</select></td></tr>

	
				<tr><th scope="row"><label for="need_login"><?php _e('Login needed','gcp-pdf');?></label></th>
				<td><input type="checkbox" name="need_login" <?php if(get_option('gcp_pdf_need_login')!=0) echo 'checked="checked"'; ?> /></td></tr>
	
				<tr><th scope="row"><label for="login_use_list_as"><?php _e('If checked use list as','gcp-pdf');?></label></th>
				<td><select name="login_use_list_as">
					<option value="2" <?php if(get_option('gcp_pdf_need_login')==2) echo 'selected="selected"'; ?>><?php _e('Whitelist','gcp-pdf');?></option>
					<option value="3" <?php if(get_option('gcp_pdf_need_login')==3) echo 'selected="selected"'; ?>><?php _e('Blacklist','gcp-pdf');?></option>
				</select></td></tr>
			</tbody></table>
		</div>
	</div>
		
	<p><input class="button-primary" type="submit" value="<?php _e('Save changes','gcp-pdf');?>" name="save_options" /> <input class="button-primary" type="reset" value="<?php _e('Reset','gcp-pdf');?>"/></p>
	</form>

	</div>
	<?php
}

function gcp_pdf_admin_listposts() {
	echo '<select name="post">';
	echo '<optgroup label="'.__('Draft','gcp-pdf').'">';
	$posts = get_posts('numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=draft');
	foreach($posts as $post) {
		if($post->post_type=='attachment') continue;
			
		echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
	}
	echo '</optgroup>';
	echo '<optgroup label="'.__('Planned','gcp-pdf').'">';
	$posts = get_posts('numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=future');
	foreach($posts as $post) {
		if($post->post_type=='attachment') continue;
			
		echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
	}
	echo '</optgroup>';
	echo '<optgroup label="'.__('Private','gcp-pdf').'">';
	$posts = get_posts('numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=private');
	foreach($posts as $post) {
		if($post->post_type=='attachment') continue;
		
		echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
	}
	echo '</optgroup>';
	echo '<optgroup label="'.__('Published','gcp-pdf').'">';
	$posts = get_posts('numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=publish');
	foreach($posts as $post) {
		if($post->post_type=='attachment') continue;
		
		echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
	}
	echo '</optgroup>';
	echo '</select>';
}

function gcp_pdf_admin_allowedprintedpages() { ?>
	<h3><?php _e('Download posts','gcp-pdf');?></h3>
	
	<?php
	global $wpdb;
	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	
	if(isset($_GET['delallowedprintedpage'])) {
		$wpdb->query('UPDATE '.$table_name.' SET general=0 WHERE id='.$_GET['delallowedprintedpage'].' LIMIT 1');
		
		echo '<div class="updated"><p>'.sprintf(__('Post with id "%s" deleted from list.','gcp-pdf'),$_GET['delallowedprintedpage']).'</p></div>';
	}
	if(isset($_GET['clearallowedpage'])) {
		$wpdb->query('UPDATE '.$table_name.' SET general=0');
		
		echo '<div class="updated"><p>'.__('All posts deleted from list.','gcp-pdf').'</p></div>';
	}
	if(isset($_POST['addallowedpage'])) {
		$page = get_post($_POST['post']);
		if($page!=null) {
			$sql = 'SELECT id FROM '.$table_name.' WHERE post_id='.$page->ID.' AND post_type="'.$page->post_type.'" LIMIT 1';
			$db_id = $wpdb->get_var($sql);
			if($db_id == null) {
				$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, 1, 0, "", 0)';
				$wpdb->query($wpdb->prepare($sql, $page->post_type, $page->ID));
			}
			else {
				$sql = 'UPDATE '.$table_name.' SET general=1 WHERE id=%d LIMIT 1';
				$wpdb->query($wpdb->prepare($sql, $db_id));
			}
				
			echo '<div class="updated"><p>'.__('Post has been added.','gcp-pdf').'</p></div>';
		}
		else {
			echo '<div class="error"><p>'.__('Post not found.','gcp-pdf').'</p></div>';
		}
	}
	else if(isset($_GET['addallowedpage'])) { ?>
		<form action="?page=<?php echo $_GET['page'];?>" method="post">
			<table class="form-table"><tbody>
				<tr><th scope="row"><label for="post"><?php _e('Post','gcp-pdf');?></label></th>
				<td><?php gcp_pdf_admin_listposts(); ?></td></tr>
			</table>
				
			<input class="button-secondary" type="submit" value="<?php _e('Add Entry','gcp-pdf');?>" name="addallowedpage" />
		</form>
	<?php } ?>
	
	<p><a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;addallowedpage=1"><?php _e('New Entry','gcp-pdf');?></a> <a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;clearallowedpage=1"><?php _e('Clear All Entries','gcp-pdf');?></a>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Post type','gcp-pdf');?></th>
				<th><?php _e('Title','gcp-pdf');?></th>
				<th><?php _e('Actions','gcp-pdf');?></th>
			</tr>
		</thead>
		<tbody>
		<?php
	$sql = 'SELECT id,post_type,post_id FROM '.$table_name.' WHERE general=1';
	$data = $wpdb->get_results($sql, OBJECT);
	for($i=0;$i<count($data);$i++) {
		echo '<tr>';
		echo '<td>'.$data[$i]->post_type.'</td>';
		if($data[$i]->post_type=='post') {
			$post = get_post($data[$i]->post_id);
			echo '<td>'.$post->post_title.'</td>';
		}
		else {
			$page = get_page($data[$i]->post_id);
			echo '<td>'.$page->post_title.'</td>';
		}
		echo '<td><a class="button-secondary" href="?page='.$_GET['page'].'&amp;delallowedprintedpage='.$data[$i]->id.'">'.__('Delete','gcp-pdf').'</a></td>';
		echo '</tr>';
	}
		?>
		</tbody>
	</table>
	<?php
}

function gcp_pdf_admin_pdfname() { ?>
	<?php
	global $wpdb;
	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	
	if(isset($_GET['delcustomname'])) {
		$wpdb->query('UPDATE '.$table_name.' SET pdfname="" WHERE id='.$_GET['delcustomname'].' LIMIT 1');
		
		echo '<div class="updated"><p>'.sprintf(__('Custom PDF name for post with id "%s" deleted.','gcp-pdf'),$_GET['delcustomname']).'</p></div>';
	}
	if(isset($_GET['clearcustomname'])) {
		$wpdb->query('UPDATE '.$table_name.' SET pdfname=""');
		
		echo '<div class="updated"><p>'.__('All custom PDF names deleted.','gcp-pdf').'</p></div>';
	}
	
	if(isset($_POST['addcustomname'])) {
		$page = get_post($_POST['post']);
		if($page!=null) {
			$sql = 'SELECT id FROM '.$table_name.' WHERE post_id='.$page->ID.' AND post_type="'.$page->post_type.'" LIMIT 1';
			$db_id = $wpdb->get_var($sql);
				
			$pdfname = $_POST['pdfname'];
			if($db_id == null) {
				$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, 0, 0, %s, 0)';
				$wpdb->query($wpdb->prepare($sql, $page->post_type, $page->ID, $pdfname));
			}
			else {
				$sql = 'UPDATE '.$table_name.' SET pdfname=%s WHERE id=%d LIMIT 1';
				$wpdb->query($wpdb->prepare($sql, $pdfname, $db_id));
			}
				
			echo '<div class="updated"><p>'.__('Custom PDF name has been added.','gcp-pdf').'</p></div>';
		}
		else {
			echo '<div class="error"><p>'.__('Post not found.','gcp-pdf').'</p></div>';
		}
	}
	else if(isset($_GET['addcustomname'])) { ?>
		<form action="?page=<?php echo $_GET['page']; ?>" method="post">
			<table class="form-table"><tbody>
				<tr><th scope="row"><label for="post"><?php _e('Post','gcp-pdf');?></label></th>
				<td><?php gcp_pdf_admin_listposts(); ?></td></tr>

				<tr><th scope="row"><label for="pdfname"><?php _e('Custom PDF name','gcp-pdf');?></th>
				<td><input type="text" name="pdfname" value=""  size="60"/></td></tr>
			</tbody></table>
			<input class="button-secondary" type="submit" value="<?php _e('Add Entry','gcp-pdf');?>" name="addcustomname"/>
		</form>
	<?php } ?>
	
	<p><a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;addcustomname=1"><?php _e('New Entry','gcp-pdf');?></a> <a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;clearcustomname=1"><?php _e('Clear All Entries','gcp-pdf');?></a>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Post type','gcp-pdf');?></th>
				<th><?php _e('Title','gcp-pdf');?></th>
				<th><?php _e('PDF name','gcp-pdf');?></th>
				<th><?php _e('Actions','gcp-pdf');?></th>
			</tr>
		</thead>
		<tbody>
	<?php
	$sql = 'SELECT id,post_type,post_id,pdfname FROM '.$table_name.' WHERE pdfname!=""';
	$data = $wpdb->get_results($sql, OBJECT);
	for($i=0;$i<count($data);$i++) {
		echo '<tr>';
		echo '<td>'.$data[$i]->post_type.'</td>';
		if($data[$i]->post_type=='post') {
			$post = get_post($data[$i]->post_id);
			echo '<td>'.$post->post_title.'</td>';
		}
		else {
			$page = get_page($data[$i]->post_id);
			echo '<td>'.$page->post_title.'</td>';
		}
		echo '<td>'.$data[$i]->pdfname.'</td>';
		echo '<td><a class="button-secondary" href="?page='.$_GET['page'].'&amp;delcustomname='.$data[$i]->id.'">'.__('Delete','gcp-pdf').'</a></td>';
		echo '</tr>';
	} ?>
		</tbody>
	</table>
	<?php
}

function gcp_pdf_admin_stats() {
	?>
	<?php
	global $wpdb;
	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	
	if(isset($_GET['resetstat'])) {
		$wpdb->query('UPDATE '.$table_name.' SET downloads=0 WHERE id='.$_GET['resetstat'].' LIMIT 1');
		
		echo '<div class="updated"><p>'.sprintf(__('Stats reseted for post with id "%s".','gcp-pdf'),$_GET['resetstat']).'</p></div>';
	}
	if(isset($_GET['clearstats'])) {
		$wpdb->query('UPDATE '.$table_name.' SET downloads=0');
		
		echo '<div class="updated"><p>'.__('All stats reseted.','gcp-pdf').'</p></div>';
	}
	?>
	
	<p><a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;clearstats=1"><?php _e('Clear All','gcp-pdf');?></a></p>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Downloads','gcp-pdf');?></th>
				<th><?php _e('Post type','gcp-pdf');?></th>
				<th><?php _e('Title','gcp-pdf');?></th>
				<th><?php _e('Actions','gcp-pdf');?></th>
			</tr>
		</thead>
	<?php
	$sql = 'SELECT id,post_type,post_id,downloads FROM '.$table_name.' ORDER BY downloads DESC';
	$data = $wpdb->get_results($sql, OBJECT);
	for($i=0;$i<count($data);$i++) {
		echo '<tr>';
		echo '<td>'.$data[$i]->downloads.'</td>';
		echo '<td>'.$data[$i]->post_type.'</td>';
		if($data[$i]->post_type=='post') {
			$post = get_post($data[$i]->post_id);
			echo '<td>'.$post->post_title.'</td>';
		}
		else {
			$page = get_page($data[$i]->post_id);
			echo '<td>'.$page->post_title.'</td>';
		}
		echo '<td><a class="button-secondary" href="?page='.$_GET['page'].'&amp;resetstat='.$data[$i]->id.'">'.__('Clear','gcp-pdf').'</a></td>';
		echo '</tr>';
	}
	?>
	</table>
	<?php
}

function gcp_pdf_admin_loginneededpages() { ?>

	<h3><?php _e('Login needed','gcp-pdf');?></h3>
	
	<?php
	global $wpdb;
	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	
	if(isset($_GET['delloginneededpages'])) {
		$wpdb->query('UPDATE '.$table_name.' SET login=0 WHERE id='.$_GET['delloginneededpages'].' LIMIT 1');
		
		echo '<div class="updated"><p>'.sprintf(__('Post with id "%s" deleted from list.','gcp-pdf'),$_GET['delloginneededpages']).'</p></div>';
	}
	if(isset($_GET['clearloginneededpages'])) {
		$wpdb->query('UPDATE '.$table_name.' SET login=0');
		
		echo '<div class="updated"><p>'.__('All posts deleted from list.','gcp-pdf').'</p></div>';
	}
	if(isset($_POST['addneedloginpage'])) {
		$page = get_post($_POST['post']);
		if($page!=null) {
			$sql = 'SELECT id FROM '.$table_name.' WHERE post_id='.$page->ID.' AND post_type="'.$page->post_type.'" LIMIT 1';
			$db_id = $wpdb->get_var($sql);
			if($db_id == null) {
				$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, 0, 1, "", 0)';
				$wpdb->query($wpdb->prepare($sql, $page->post_type, $page->ID));
			}
			else {
				$sql = 'UPDATE '.$table_name.' SET login=1 WHERE id=%d LIMIT 1';
				$wpdb->query($wpdb->prepare($sql, $db_id));
			}
				
			echo '<div class="updated"><p>'.__('Post has been added.','gcp-pdf').'</p></div>';
		}
		else {
			echo '<div class="error"><p>'.__('Post not found.','gcp-pdf').'</p></div>';
		}
	}
	else if(isset($_GET['addneedloginpage'])) { ?>
		<form action="?page=<?php echo $_GET['page'];?>" method="post">
			<table class="form-table">
				<tr><th scope="row"><label for="post"><?php _e('Post','gcp-pdf');?></label></th>
				<td><?php gcp_pdf_admin_listposts(); ?></td></tr>
			</table>
			<input class="button-secondary" type="submit" value="<?php _e('Add Entry','gcp-pdf');?>" name="addneedloginpage" />
		</form>
		<?php
	} ?>
	
	<p><a class="button-primary" href="?page=<?php echo $_GET['page']; ?>&amp;addneedloginpage=1"><?php _e('New Entry','gcp-pdf');?></a> <a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;clearloginneededpages=1"><?php _e('Clear All Entries','gcp-pdf');?></a></p>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Post type','gcp-pdf');?></th>
				<th><?php _e('Title','gcp-pdf');?></th>
				<th><?php _e('Actions','gcp-pdf');?></th>
			</tr>
		</thead>
		<tbody>
		<?php
	$sql = 'SELECT id,post_type,post_id FROM '.$table_name.' WHERE login=1';
	$data = $wpdb->get_results($sql, OBJECT);
	for($i=0;$i<count($data);$i++) {
		echo '<tr>';
		echo '<td>'.$data[$i]->post_type.'</td>';
		if($data[$i]->post_type=='post') {
			$post = get_post($data[$i]->post_id);
			echo '<td>'.$post->post_title.'</td>';
		}
		else {
			$page = get_page($data[$i]->post_id);
			echo '<td>'.$page->post_title.'</td>';
		}
		echo '<td><a class="button-secondary" href="?page='.$_GET['page'].'&amp;delloginneededpages='.$data[$i]->id.'">'.__('Delete','gcp-pdf').'</a></td>';
		echo '</tr>';
	}
		?>
		</tbody>
	</table>
	<?php
}

function gcp_pdf_admin_cache() {
	?>
	<?php	
	if(isset($_GET['delfile'])) {
		if(file_exists(dirname(__FILE__).'/cache/'.$_GET['delfile']))
			unlink(dirname(__FILE__).'/cache/'.$_GET['delfile']);
		if(file_exists(dirname(__FILE__).'/cache/'.$_GET['delfile'].'.cache'))
			unlink(dirname(__FILE__).'/cache/'.$_GET['delfile'].'.cache');
			
		echo '<div class="updated"><p>'.__('Cache file deleted','gcp-pdf').' ("'.$_GET['delfile'].'")</p></div>';
	}
	if(isset($_GET['clearcache'])) {
		if($dir = opendir(dirname(__FILE__).'/cache')) {
			while($file = readdir($dir)) {
				if(!is_dir($path.$file) && $file != "." && $file != "..")  {
					unlink(dirname(__FILE__).'/cache/'.$file);
				}
			}
		}
		
		echo '<div class="updated"><p>'.__('Cache is cleared','gcp-pdf').'</p></div>';
	}
	?>
	
	<p><a class="button-primary" href="?page=<?php echo $_GET['page'];?>&amp;clearcache=1"><?php _e('Clear Cache','gcp-pdf');?></a></p>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('File date','gcp-pdf');?></th>
				<th><?php _e('File name','gcp-pdf');?></th>
				<th><?php _e('Actions','gcp-pdf');?></th>
			</tr>
		</thead>
	<?php
	if($dir = opendir(dirname(__FILE__).'/cache')) {
		while($file = readdir($dir)) {
			if(!is_dir($path.$file) && $file != "." && $file != "..")  {
				if(strtolower(substr($file, strlen($file)-5))=='cache') {
					$pdffilename = substr($file, 0, strlen($file)-6);
					echo '<tr>';
					echo '<td style="padding: 5px;">'.file_get_contents(dirname(__FILE__).'/cache/'.$file).'</td>';
					echo '<td style="padding: 5px;"><a href="../wp-content/plugins/wp-gcp-pdf/cache/'.$pdffilename.'">'.$pdffilename.'</a></td>';
					echo '<td style="padding: 5px;"><a class="button-secondary" href="?page='.$_GET['page'].'&amp;delfile='.$pdffilename.'">'.__('Delete','gcp-pdf').'</a></td>';
					echo '</tr>';
				}
			}
		}
	}
	?>
	</table>
	<?php
}

function gcp_pdf_admin_help ($text) {
	if ($_GET['page']==='gcp_pdf') {		
		$text='<h5>'. __('General Settings','gcp-pdf').'</h5/>';
		$text.='<ul>';
		$text.='<li><i>'.__('Append PDF button to posts','gcp-pdf').'</i> - '.__('Append a pdf download link to each post','gcp-pdf').'</li>';
		$text.='<li><i>'.__('Show "No-Print" message when printing','gcp-pdf').'</i> - '.__('If checked, when printing using the browsers print function a message gets printed informing the user of your Unprintable Blog and to use the pdf-download instead. When not checkd only a blank page gets printed','gcp-pdf').'</li>';
		$text.='<li><i>'.__('"No-Print" template:','gcp-pdf').'</i> - '.__('Template to be used for the message shown when printing a page via the browsers print function.','gcp-pdf');
		$text.='<li><i>'.__('PDF caching','gcp-pdf').'</i> - '.__('Caching of generated pdf files. Caching is highly recommended to improve performance.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('Download stats','gcp-pdf').'</i> - '.__('Track number of downloads for each PDF. The statsitics page is only displayed when this option is activated.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('Enabled Debugging','gcp-pdf').'</i> - '.__('If enabled, the html used for generating the pdf will be saved in the folder "debug" inside the plugin dir for troubleshooting.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('Show metabox when editing posts','gcp-pdf').'</i> - '.__('Show a metabox for black/white listing and custom pdf file name when editing a post.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('User for generating per Cron','gcp-pdf').'</i> - '.__('Select a user name to pre-generate pdfs of your blog posts per cron when that user logs on. If none is selected pdfs will be generated on download. Only use cron when caching is enabled.','gcp-pdf').'</li>';
		$text.='</ul>';
		$text.='<h5>'.__('PDF Settings','gcp-pdf').'</h5>';
		$text.='<ul>';
		$text.='<li><i>'.__('Theme','gcp-pdf').'</i> - '.__('Select theme to be used for generating pdf files. Themes are located in the folder "themes" inside the plugin dir.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('Codepage','gcp-pdf').'</i> - '.__('Codepage to be used for pdf.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('GeSHi parsing','gcp-pdf').'</i> - '.__('Enable syntax highlighting in printed pdfs. Syntax highlighting will be applied to code blocks wrapped with &lt;pre lang="LANGUAGE"&gt;...&lt;/pre&gt;. For more details visit <a href="http://qbnz.com/highlighter/">GeSHi website</a>','gcp-pdf').'</li>';
		$text.='<li><i>'.__('GeSHi line numbers','gcp-pdf').'</i> - '.__('Print line numbers in code blocks when syntax highlighting is enabled.','gcp-pdf').'</li>';
		$text.='</ul>';
		$text.='<h5>'.__('Access Settings','gcp-pdf').'</h5>';
		$text.='<ul>';
		$text.='<li><i>'.__('Allow PDF download of all posts','gcp-pdf').'</i> - '.__('If checked all blog posts can be downloaded as PDF.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('If not use list as','gcp-pdf').'</i> - '.__('If <i>Allow PDF download of all pages</i> is not checked, select if the "Download posts" list should be used as blacklist (download of all posts except for those on the list) or whitelist (download only posts on the list).','gcp-pdf').'</li>';
		$text.='<li><i>'.__('Login needed','gcp-pdf').'</i> - '.__('If checked some or all posts can only be downloaded by logged in users.','gcp-pdf').'</li>';
		$text.='<li><i>'.__('If checked use list as','gcp-pdf').'</i> - '.__('Select if "Login needed" list should be used as whitelist (all pdf downloads require login, except for posts on the list) or as blacklist (only pdf downloads for posts on list require login).','gcp-pdf').'</li>';
		$text.='</ul>';
		$text.='<br/><a href="http://www.greencomputingportal.de/forum/">Support Forum</a>';
	}
	return $text;
}


?>
