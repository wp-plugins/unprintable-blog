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



//Call this script from a cron job to create/update the pdf cache
require_once(dirname(__FILE__).'/../../../wp-config.php');
require_once(dirname(__FILE__).'/wp-gcp-pdf.php');


//Disable the Timeout
set_time_limit(0);


//Check if Caching is enabled or not
if(get_option('gcp_pdf_caching')!=true) {
	echo __('No caching enabled','gcp-pdf').'\n';
	exit(-1);
}


//Do login if is whished
if(get_option('gcp_pdf_cron_user') != '') {
    $userId = get_option('gcp_pdf_cron_user');
    if(get_option('gcp_pdf_cron_user') == 'auto') {
        $aUsersID = $wpdb->get_col($wpdb->prepare('SELECT ID FROM '.$wpdb->users.' LIMIT 1'));
        foreach($aUsersID as $iUserID) {
            $userId = $iUserID;
        }
    }

    wp_set_current_user($userId);
}


//Cache the posts
$_GET['output'] = 'pdf';
echo __('Start cache creating','gcp-pdf').'\n';

$posts = get_posts('numberposts=-1&order=ASC&orderby=title');
foreach($posts as $post) {
    if($post->post_title == '') {
        echo sprintf(__('Skiping pdf creation for post %s: No Title','gcp-pdf').$post->ID).'\n';
        continue;
    }
 
	echo sprintf(__('Create cache pdf for post %s','gcp-pdf'),$post->ID).'\n';
	

	query_posts('p='.$post->ID);
	gcp_pdf_exec('false');
}

echo __('Caching finished','gcp-pdf').'\n';
?>
