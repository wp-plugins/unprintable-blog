<?php
/*
Plugin Name: Unprintable Blog
Plugin URI: http://www.greencomputingportal.de
Description: Prevent printing of Wordpress blog pages with alternative option to download posts as unprintable PDF. Take action to help reduce paper waste - make your blog "unprintable".
Version: 1.0
Author: Bj&ouml;rn Ahrens
Author URI: http://www.bjoernahrens.de

Copyright 2010 Björn Ahrens
*/

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


define('WP_GCP_PDF_ALLOWED_POSTS_DB', 'wp_gcp_pdf_allowed');
define('WP_GCP_PDF_POSTS_DB', 'wp_gcp_pdf_posts');
define('WP_GCP_PDF_FILEEXT', '.pdf');

define('WP_GCP_PDF_WORDPRESS_ROOT', dirname(__FILE__).'/../../../');
define('WP_GCP_PDF_THEME_PATH', dirname(__FILE__).'/themes/');
define('WP_GCP_PDF_NOPRINTTHEME_PATH', dirname(__FILE__).'/noprint/');
define('WP_GCP_PDF_PLUGIN_URL',WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));


require_once(dirname(__FILE__).'/php4.inc.php');

function gcp_pdf_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	if($wpdb->get_var('SHOW TABLES LIKE "'.$table_name.'"') != $table_name) {
		$sql = 'CREATE TABLE ' . $table_name . ' (
	  		id mediumint(9) NOT NULL AUTO_INCREMENT,
	  		post_type VARCHAR(4) DEFAULT "post" NOT NULL,
	  		post_id mediumint(9) NOT NULL,
	  		general smallint(1) NOT NULL,
	  		login smallint(1) NOT NULL,
	  		pdfname VARCHAR(255) DEFAULT "" NOT NULL,
	  		downloads int(11) NOT NULL,
	  		UNIQUE KEY id (id)
		);';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		
		add_option('gcp_pdf_theme', 'default');
		add_option('gcp_pdf_geshi', false);
		add_option('gcp_pdf_geshi_linenumbers', true);
		add_option('gcp_pdf_caching', true);
		add_option('gcp_pdf_allow_all', true);
		add_option('gcp_pdf_need_login', false);
		add_option('gcp_pdf_stats', false);
		add_option('gcp_pdf_debug', false);
        add_option('gcp_pdf_cron_user', '');
        add_option('gcp_pdf_code_page', 'utf-8');
		add_option('gcp_pdf_noprintdiv', true);
		add_option('gcp_pdf_noprinttheme', 'default');
		add_option('gcp_pdf_showmetabox', false);
		add_option('gcp_pdf_appendbutton',true);
	}
	
	
   	$table_name_old = $wpdb->prefix . WP_GCP_PDF_ALLOWED_POSTS_DB;
	if($wpdb->get_var('SHOW TABLES LIKE "'.$table_name_old.'"') == $table_name_old) {
		//Copy old data
		$sql = 'SELECT id,post_type,post_id,enabled FROM '.$table_name_old;
		$data = $wpdb->get_results($sql, OBJECT);
		foreach($data as $dsatz) {
			$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, %d, %d, %s, 0)';
			$wpdb->query($wpdb->prepare($sql, $dsatz->post_type, $dsatz->post_id, $dsatz->enabled, false, ''));
		}
		
		//Remove table
		$wpdb->query('DROP TABLE '.$table_name_old);
	}
}

function gcp_pdf_output($wp_content = '', $do_pdf = false , $outputToBrowser=true, $pdfName = '') {
	global $post;
	$pdf_ofilename = $post->post_name . WP_GCP_PDF_FILEEXT;
	if(!empty($pdfName)) {
		$pdf_filename = $pdfName . WP_GCP_PDF_FILEEXT;
	}
	else {
		$pdf_filename = $pdf_ofilename;
	}
	
	/**
	 * Geshi Support
	 */
	if(get_option('gcp_pdf_geshi')==true) {
		require_once(dirname(__FILE__).'/geshi.inc.php');
		$wp_content = ParseGeshi($wp_content);
	}

	/**
	 * Run the content default filter
	 */
	$wp_content = apply_filters('the_content', $wp_content);

	/**
	 * Run the gcp-pdf filter
	 */
	$wp_content = gcp_pdf_filter($wp_content, $do_pdf);
	 
	
	if($do_pdf === false) {
		echo $wp_content;
	}
	else {
		define('_MPDF_PATH',dirname(__FILE__).'/mpdf/');
        define('_MPDF_TEMP_PATH', _MPDF_PATH.'graph_cache/');
		require_once(_MPDF_PATH.'mpdf.php');
		
		global $pdf_margin_left;
		global $pdf_margin_right;
		global $pdf_margin_top;
		global $pdf_margin_bottom;
		global $pdf_margin_header;
		global $pdf_margin_footer;

		if($pdf_margin_left=='') $pdf_margin_left = 15;
		if($pdf_margin_right=='') $pdf_margin_right = 15;
		if($pdf_margin_top=='') $pdf_margin_top = 16;
		if($pdf_margin_bottom=='') $pdf_margin_bottom = 16;
		if($pdf_margin_header=='') $pdf_margin_header = 9;
		if($pdf_margin_footer=='') $pdf_margin_footer = 9;

		global $pdf_orientation;
		if($pdf_orientation=='') $pdf_orientation = 'P';
		
        $cp = 'utf-8';
        if(get_option('gcp_pdf_code_page')!='') {
             $cp = get_option('gcp_pdf_code_page');
        }

		$mpdf=new mPDF($cp, 'A4', '', '', $pdf_margin_left, $pdf_margin_right, $pdf_margin_top, $pdf_margin_bottom, $pdf_margin_header, $pdf_margin_footer, $pdf_orientation); 

		//$mpdf->SetUserRights();
		
		//Disallow Printing of PDF
		$mpdf->SetProtection(array('copy','modify','annot-forms'));
		
		$mpdf->title2annots = false;
		//$mpdf->annotMargin = 12;
		$mpdf->use_embeddedfonts_1252 = true;	// false is default
		$mpdf->SetBasePath(WP_GCP_PDF_THEME_PATH);

		//Set PDF Template if it's set
		global $pdf_template_pdfpage;
		global $pdf_template_pdfpage_page;
		global $pdf_template_pdfdoc;
		if(isset($pdf_template_pdfdoc)&&$pdf_template_pdfdoc!='') {
            $mpdf->SetImportUse();
			$mpdf->SetDocTemplate(WP_GCP_PDF_THEME_PATH.$pdf_template_pdfdoc, true);
		}
		else if(isset($pdf_template_pdfpage)&&$pdf_template_pdfpage!=''&&isset($pdf_template_pdfpage_page)&&is_numeric($pdf_template_pdfpage_page)) {
            $mpdf->SetImportUse();			
            $pagecount = $mpdf->SetSourceFile(WP_GCP_PDF_THEME_PATH.$pdf_template_pdfpage);
			if($pdf_template_pdfpage_page<1) $pdf_template_pdfpage_page = 1;
			else if($pdf_template_pdfpage_page>$pagecount) $pdf_template_pdfpage_page = $pagecount;
			$tplId = $mpdf->ImportPage($pdf_template_pdfpage_page);
			$mpdf->UseTemplate($tplId);
		}


		$user_info = get_userdata($post->post_author);
		$mpdf->SetAuthor($user_info->first_name.' '.$user_info->last_name.' ('.$user_info->user_login.')');
		$mpdf->SetCreator('Unprintable Blog for Wordpress');
		
		
		//The Header and Footer
		global $pdf_footer;
		global $pdf_header;
		
		$mpdf->startPageNums();	// Required for TOC use after AddPage(), and to use Headers and Footers
		$mpdf->setHeader($pdf_header);
		$mpdf->setFooter($pdf_footer);
		
		
		if(get_option('gcp_pdf_theme')!=''&&file_exists(WP_GCP_PDF_THEME_PATH.get_option('gcp_pdf_theme').'.css')) {
			//Read the StyleCSS
			$tmpCSS = file_get_contents(WP_GCP_PDF_THEME_PATH.get_option('gcp_pdf_theme').'.css');
			$mpdf->WriteHTML($tmpCSS, 1);
		}
		
		//My Filters
		require_once(dirname(__FILE__).'/myfilters.inc.php');
		$wp_content = gcp_pdf_myfilters($wp_content);

		if(get_option('gcp_pdf_debug') == true) {
			file_put_contents(dirname(__FILE__).'/debug/'.get_option('gcp_pdf_theme').'_'.$pdf_ofilename.'.html', $wp_content);
		}

		//die($wp_content);
		$mpdf->WriteHTML($wp_content);
		
		if(get_option('gcp_pdf_caching')==true) {
			file_put_contents(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_ofilename.'.cache', $post->post_modified_gmt);
			$mpdf->Output(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_ofilename, 'F');
			if($outputToBrowser==true) {			
				$mpdf->Output($pdf_filename, 'I');
			}
		}
		else {
			if($outputToBrowser==true) {
				$mpdf->Output($pdf_filename, 'I');
			}
		}
	}
}

function gcp_pdf_filter($wp_content = '', $do_pdf = false, $convert = false) {
	$delimiter1 = 'screen';
	$delimiter2 = 'print';

	if($do_pdf === false ) {
		$d1a = '[' . $delimiter1 . ']';
		$d1b = '[/' . $delimiter1 . ']';
		$d2a = '\[' . $delimiter2 . '\]';
		$d2b = '\[\/' . $delimiter2 . '\]';
	} else {
		$d1a = '[' . $delimiter2 . ']';
		$d1b = '[/' . $delimiter2 . ']';
		$d2a = '\[' . $delimiter1 . '\]';
		$d2b = '\[\/' . $delimiter1 . '\]';
	}

	format_to_post('the_content');

	$wp_content = str_replace($d1a , '', $wp_content);
	$wp_content = str_replace($d1b , '', $wp_content);

	$ctpdf_wp_content = preg_replace("/$d2a(.*?)$d2b/s", '', $wp_content);


	if($convert == true) {
		$wp_content = mb_convert_encoding($wp_content, "ISO-8859-1", "UTF-8");
	}

	return $wp_content;
}

function gcp_pdf_mysql2unix($timestamp) {
	// stolen cold-blooded from the Polyglot plugin
	$year = substr($timestamp,0,4);
	$month = substr($timestamp,5,2);
	$day = substr($timestamp,8,2);
	$hour = substr($timestamp,11,2);
	$minute = substr($timestamp,14,2);
	$second = substr($timestamp,17,2);
	return mktime($hour,$minute,$second,$month,$day,$year);
}

function gcp_pdf_pdfbutton($opennewtab=false, $buttontext = '', $logintext = 'Login!', $print_button = true) {
	//Check if button should displayed
	if(get_option('gcp_pdf_allow_all')!=1 || get_option('gcp_pdf_need_login')!=0) {
		global $wpdb;
		global $post;
		$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
		$sql = 'SELECT general,login FROM '.$table_name.' WHERE post_id='.$post->ID.' AND post_type="'.$post->post_type.'" LIMIT 1';
		$dsatz = $wpdb->get_row($sql);
		
		if(get_option('gcp_pdf_allow_all')==2&&$dsatz->general==false || get_option('gcp_pdf_allow_all')==3&&$dsatz->general==true) {
			return;
		}
		else if((get_option('gcp_pdf_need_login')==2&&$dsatz->login==false || get_option('gcp_pdf_need_login')==3&&$dsatz->login==true)&&is_user_logged_in()!=true) {
			if(empty($buttontext)) {
				$buttontext = '<img src="'.WP_GCP_PDF_PLUGIN_URL.'/img/pdf_lock.png" alt="'.$logintext.'" title="'.__('You must login first','gcp-pdf').'" border="0" />';
			}
			else {
				$buttontext = $logintext;
			}
			
			$pdf_button = '<a id="pdfbutton" href="'.wp_login_url(get_permalink()).'" title="'.__('You must login first','gcp-pdf').'">'.$buttontext.'</a>';
			
			if($print_button === true) {
				echo $pdf_button;
				return;
			} else {
				return $pdf_button;
			}
		}
	}
	
	
	//Print the button
	if(empty($buttontext))
		$buttontext = '<img src="'.WP_GCP_PDF_PLUGIN_URL.'/img/pdf.png" alt="'.__('This page as PDF','gcp-pdf').'" border="0" />';
	
	$x = !strpos(apply_filters('the_permalink', get_permalink()), '?') ? '?' : '&amp;';
	$pdf_button = '<a title="'.__('This page as PDF','gcp-pdf').'" ';
	if($opennewtab==true) $pdf_button .= 'target="_blank" ';
	$pdf_button .= 'id="pdfbutton" href="' . apply_filters('the_permalink', get_permalink()) . $x . 'output=pdf">' . $buttontext . '</a>';
	
	if($print_button === true) {
		echo $pdf_button;
	} else {
		return $pdf_button;
	}
}

function gcp_pdf_readcachedfile($name, $pdfname) {
	$fp = fopen(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$name, 'rb');
	if(!$fp) die(__('Couldn\'t read cache file','gcp-pdf'));
	fclose($fp);
	
	Header('Content-Type: application/pdf');
	Header('Content-Length: '.filesize(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$name));
	Header('Content-disposition: inline; filename='.$pdfname);
	
	echo file_get_contents(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$name, FILE_BINARY | FILE_USE_INCLUDE_PATH);
}

function gcp_pdf_exec($outputToBrowser='') {
	if($outputToBrowser=='') $outputToBrowser = true;
	else $outputToBrowser = false;
	
	if($_GET['output'] == 'pdf') {
		//Check if this Page is allowed to be printed as PDF
		global $wpdb;
		global $post;
		$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
		$sql = 'SELECT id,general,login,pdfname FROM '.$table_name.' WHERE post_id='.$post->ID.' AND post_type="'.$post->post_type.'" LIMIT 1';
		$dsatz = $wpdb->get_row($sql);
		
		if(get_option('gcp_pdf_allow_all')==2&&$dsatz->general==false || get_option('gcp_pdf_allow_all')==3&&$dsatz->general==true) {
			return;
		}
		else if((get_option('gcp_pdf_need_login')==2&&$dsatz->login==false || get_option('gcp_pdf_need_login')==3&&$dsatz->login==true)&&is_user_logged_in()!=true&&$outputToBrowser==true) {
			wp_redirect(wp_login_url(get_permalink()));
			return;
		}

		//Update download stats if enabled
		if(get_option('gcp_pdf_stats')==true) {
			if($dsatz == null) {
				$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, %d, %d, %s, 1)';
				$wpdb->query($wpdb->prepare($sql, $post->post_type, $post->ID, false, false, ''));
			}
			else {
				$sql = 'UPDATE '.$table_name.' SET downloads=downloads+1 WHERE id=%d LIMIT 1';
				$wpdb->query($wpdb->prepare($sql, $dsatz->id));
			}
		}
		
		//Check for Caching option
		if(get_option('gcp_pdf_caching')==true) {
			$pdf_filename = $post->post_name . WP_GCP_PDF_FILEEXT;
			if(file_exists(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename.'.cache')&&file_exists(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename)) {
				$createDate = file_get_contents(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename.'.cache');
				if($createDate==$post->post_modified_gmt) {
					//We could Read the Cached file
					if($outputToBrowser==true) {
						if(!empty($dsatz->pdfname)) {
							gcp_pdf_readcachedfile($pdf_filename, $dsatz->pdfname.'.pdf');
						}
						else {
							gcp_pdf_readcachedfile($pdf_filename, $pdf_filename);
						}
						exit;
					}
					else {
						return;
					}
				}
			}
		} 
		
		require(WP_GCP_PDF_THEME_PATH.get_option('gcp_pdf_theme').'.php');
		gcp_pdf_output($pdf_output, true, $outputToBrowser, $dsatz->pdfname);
		
		if($outputToBrowser==true) {
			exit;
		}
	}
}

function gcp_pdf_admin_page() {
	require_once('wp-gcp-pdf_admin.php');
	gcp_pdf_admin_display('gcp_pdf');
}
function gcp_pdf_admin_page_cache() {
	require_once('wp-gcp-pdf_admin.php');
	gcp_pdf_admin_display('gcp_pdf_cache');
}
function gcp_pdf_admin_page_customnames() {
	require_once('wp-gcp-pdf_admin.php');
	gcp_pdf_admin_display('gcp_pdf_customnames');
}
function gcp_pdf_admin_page_bwlists() {
	require_once('wp-gcp-pdf_admin.php');
	gcp_pdf_admin_display('gcp_pdf_bwlists');
}
function gcp_pdf_admin_page_stats() {
	require_once('wp-gcp-pdf_admin.php');
	gcp_pdf_admin_display('gcp_pdf_stats');
}


function gcp_pdf_create_admin_menu() {
	$icon_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/img/unprintable_16.png';
	add_menu_page( 'Unprintable Blog', 'Unprintable', 9, 'gcp_pdf','gcp_pdf_admin_page',$icon_path);	
	
	add_submenu_page( 'gcp_pdf', 'Unprintable Blog - '.__('Settings','gcp-pdf'), __('Settings','gcp-pdf'), 9, 'gcp_pdf', 'gcp_pdf_admin_page');
	add_submenu_page( 'gcp_pdf', 'Unprintable Blog - '.__('Black/White Lists','gcp-pdf'), __('Black/White Lists','gcp-pdf'), 9, 'gcp_pdf_bwlists', 'gcp_pdf_admin_page_bwlists');
	add_submenu_page( 'gcp_pdf', 'Unprintable Blog - '.__('Cache','gcp-pdf'), __('Cache','gcp-pdf'), 9, 'gcp_pdf_cache', 'gcp_pdf_admin_page_cache');
	add_submenu_page( 'gcp_pdf', 'Unprintable Blog - '.__('Custom PDF Names','gcp-pdf'), __('PDF Names','gcp-pdf'), 9, 'gcp_pdf_customnames', 'gcp_pdf_admin_page_customnames');
	if(get_option('gcp_pdf_stats')==true) {
		add_submenu_page( 'gcp_pdf', 'Unprintable Blog - '.__('Statistics','gcp-pdf'), __('Statistics','gcp-pdf'), 9, 'gcp_pdf_stats', 'gcp_pdf_admin_page_stats');
	}
	
	if (get_option('gcp_pdf_showmetabox')==true) {
		if(function_exists('add_meta_box')) {
			add_meta_box('gcp_pdf_admin', 'Unprintable Blog', 'gcp_pdf_admin_printeditbox', 'post', 'normal', 'high');
			add_meta_box('gcp_pdf_admin', 'Unprintable Blog', 'gcp_pdf_admin_printeditbox', 'page', 'normal', 'high');
		}
		else {
			add_action('dbx_post_advanced', 'gcp_pdf_admin_printeditbox_old');
			add_action('dbx_page_advanced', 'gcp_pdf_admin_printeditbox_old');
		}
	}
}

function gcp_pdf_admin_printeditbox() {
	global $wpdb;
	global $post;
	
	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	$sql = 'SELECT * FROM '.$table_name.' WHERE post_id='.$post->ID.' AND post_type="'.$post->post_type.'" LIMIT 1';
	$datas = $wpdb->get_row($sql);	

	echo '<input type="hidden" name="wp_gcp_pdf_noncename" id="wp_gcp_pdf_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
	
	
	echo '<table border="0">';
	echo '<tr><td>'.__('Put on whitelist/blacklist for "Print posts"','gcp-pdf').'</td><td><input ';
	if($datas->general == true) {
		echo 'checked="checked" ';
	}
	echo 'type="checkbox" name="wp_gcp_pdf_candownload" /></td></tr>';
	
	echo '<tr><td>'.__('Put on whitelist/blacklist for "Login needed"','gcp-pdf').'</td><td><input ';
	if($datas->login == true) {
		echo 'checked="checked" ';
	}
	echo 'type="checkbox" name="wp_gcp_pdf_needlogin" /></td></tr>';
	
	echo '<tr><td>'.__('Set a custom PDF name','gcp-pdf').'</td><td><input type="text" name="wp_gcp_pdf_pdfname" value="';
	echo $datas->pdfname;
	echo '" /> '.__('(without .pdf at the end)','gcp-pdf').'</td></tr>';
	echo '</table>';
}

/* Prints the edit form for pre-WordPress 2.5 post/page */
function gcp_pdf_admin_printeditbox_old() {

	echo '<div class="dbx-b-ox-wrapper">' . "\n";
  	echo '<fieldset id="gcp_pdf_admin" class="dbx-box">' . "\n";
  	echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">wp-gcp-pdf</h3></div>';   
   
  	echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';

  	// output editing form

  	gcp_pdf_admin_printeditbox();

  	// end wrapper

  	echo '</div></div></fieldset></div>'."\n";
}


function gcp_pdf_admin_savepost($post_id) {
	if(!wp_verify_nonce($_POST['wp_gcp_pdf_noncename'], plugin_basename(__FILE__))) {
    	return $post_id;
  	}
	
	if('page' == $_POST['post_type']) {
    	if(!current_user_can('edit_page', $post_id))
     		return $post_id;
  	} else if($_POST['post_type'] == 'post') {
    	if(!current_user_can('edit_post', $post_id))
      		return $post_id;
  	}
	else
		return $post_id;


	$post_id = wp_is_post_revision($post_id);
	if($post_id==0) return $post_id;
	
	
	global $wpdb;
   	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
	
	$canPrintAsPDF = isset($_POST['wp_gcp_pdf_candownload']);
	$needLogin = isset($_POST['wp_gcp_pdf_needlogin']);
	$pdfOutputName = $_POST['wp_gcp_pdf_pdfname'];
	
	$sql = 'SELECT id FROM '.$table_name.' WHERE post_id='.$post_id.' AND post_type="'.$_POST['post_type'].'" LIMIT 1';
	$db_id = $wpdb->get_var($sql);
	
	if($db_id == null) {
		$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, %d, %d, %s, 0)';
		$wpdb->query($wpdb->prepare($sql, $_POST['post_type'], $post_id, $canPrintAsPDF, $needLogin, $pdfOutputName));
	}
	else {
		$sql = 'UPDATE '.$table_name.' SET general=%d , login=%d , pdfname=%s WHERE id=%d LIMIT 1';
		$wpdb->query($wpdb->prepare($sql, $canPrintAsPDF, $needLogin, $pdfOutputName, $db_id));
	}
}


function gcp_pdf_admin_deletepost($post_id) {
	$post = get_post($post_id);
	
	if('page' == $post->post_type) {
    	if(!current_user_can('edit_page', $post_id))
     		return $post_id;
  	} else if($post->post_type == 'post') {
    	if(!current_user_can('edit_post', $post_id))
      		return $post_id;
  	}
	else
		return $post_id;
	
	
	//Clear the db entry if it exist
	global $wpdb;
   	$table_name = $wpdb->prefix . WP_GCP_PDF_POSTS_DB;
   	
   	$sql = 'DELETE FROM '.$table_name.' WHERE post_id=%d AND post_type=%s LIMIT 1';
   	$wpdb->query($wpdb->prepare($sql, $post_id, $post->post_type));
   	
   	
	//Clear the cache from a post
	$pdf_filename = $post->post_name . WP_GCP_PDF_FILEEXT;
	if(file_exists(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename.'.cache')) {
		unlink(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename.'.cache');
	}
	if(file_exists(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename)) {
		unlink(dirname(__FILE__).'/cache/'.get_option('gcp_pdf_theme').'_'.$pdf_filename);
	}
}


function gcp_pdf_html_head () {
	// Add styles to prevent printing
	// this is achieved by setting visiblity and display of all elements to hidden / none except the gcp_pdf_noprint-div which
	// is displayed only when printing
	if (get_option('gcp_pdf_noprintdiv')==true) {
		echo '	<style type="text/css">@media screen {#gcp_pdf_noprint {display: none;visibility:hidden;}} @media print { body * {visibility: hidden; display: none;} #gcp_pdf_noprint, #gcp_pdf_noprint *{display: block; visibility:visible} }</style>';
	} else {
		echo '	<style type="text/css">@media print { body * {visibility: hidden; display: none;} }</style>';
	}
}

function gcp_pdf_html_footer () {
	// Add noprint-div do footer
	// div show text "please dont print" when trying to print the webpage
	if (get_option('gcp_pdf_noprintdiv')==true) {
		echo '<div id="gcp_pdf_noprint" style="text-align:center">';

		require_once (WP_GCP_PDF_NOPRINTTHEME_PATH.get_option('gcp_pdf_noprint_theme').'.php');
		echo '</div>';
	}
}

function gcp_pdf_contentfilter ($content) {
	if (is_singular() && get_option('gcp_pdf_appendbutton')==true) {
		$content.='<p>'.gcp_pdf_pdfbutton(false, '', 'Login!', false).'</p>';
	}
	return $content;
}

function gcp_pdf_admin_help_hook ($text) {
	require_once('wp-gcp-pdf_admin.php');
	return gcp_pdf_admin_help($text);
}

function gcp_pdf_init() {
	$plugin_dir = 'wp-content/plugins/'.basename(dirname(__FILE__));
	load_plugin_textdomain('gcp-pdf',$plugin_dir.'/lang');

	add_action('wp_head', 'gcp_pdf_html_head');  
	add_action('wp_footer', 'gcp_pdf_html_footer');  
	add_action('delete_post', 'gcp_pdf_admin_deletepost');
	add_action('template_redirect', 'gcp_pdf_exec', 98);
	add_action('admin_menu', 'gcp_pdf_create_admin_menu');
	add_filter('the_content', 'gcp_pdf_contentfilter');
	add_action('save_post', 'gcp_pdf_admin_savepost');
	add_action('contextual_help', 'gcp_pdf_admin_help_hook');
}
add_action('init', 'gcp_pdf_init');

register_activation_hook(__FILE__, 'gcp_pdf_install');
?>
