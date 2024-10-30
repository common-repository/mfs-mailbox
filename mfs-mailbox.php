<?php
/*
 *Plugin Name: MFS Mailbox
 *Description: This plugin plugin will allow registered users to send mail(s) to other registered users.
 *Version: 1.1
 *Author: Mindfire-Solutions
 *Author URI: http://www.mindfiresolutions.com/
 */

/*=======================================================================================*/
//error_reporting(1);
session_start();
global $unread_count;
/**
 * Actions calling section
 */
register_activation_hook( __FILE__, 'mfs_mailbox_install' );
add_action('admin_menu', 'mfs_mailbox_link');
add_action('admin_menu', 'mfs_mailbox_sub_links');
add_action('init', 'plugin_upgradation');

/** 
 *  Function Name: mfs_mailbox_install
 *  Description: Create table while installing the plugin.
 */
function mfs_mailbox_install() {
	global $wpdb;

	$mfs_mailbox_table_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mfs_mailbox (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  receiver_ids text NOT NULL,
	  sender_id int(11) NOT NULL,
	  subject text NOT NULL,
	  message_body text NOT NULL,
	  time datetime NOT NULL,
	  folder_id tinyint(1) NOT NULL COMMENT '1 for inbox 2 for sent 3 for draft  4 for  trash',
	  read_unread tinyint(1) NOT NULL COMMENT '1 for read 2 for unread',
	  who_can_see text NOT NULL,
	  PRIMARY KEY (id)
	);";
	$wpdb->query($mfs_mailbox_table_sql);

	$who_can_see_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}who_can_see (
			  mail_id int(11) NOT NULL,
			  user_id int(11) NOT NULL
			);";
	$wpdb->query($who_can_see_sql);	
	
	$mfs_mailbox_attachment = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mfs_mailbox_meta (
		id int(11) NOT NULL AUTO_INCREMENT,
		mail_id int(11) NOT NULL,
		meta_key text NOT NULL,
		meta_value text NOT NULL,
		PRIMARY KEY (id)
	);";
	$wpdb->query($mfs_mailbox_attachment);
	
	/** Create a folder for attachment files **/
	$uploads = wp_upload_dir();	
	if(!empty($uploads)) {
		$new_dir = $uploads['basedir'] . '\mfsmailbox';
		wp_mkdir_p($new_dir);
		@chmod($new_dir, 0755);	
	}
}

/** 
 *  Function Name: plugin_upgradation
 *  Description: Fixes for plugin upgradation.
 */
function plugin_upgradation() {
	global $wpdb;
	
	/* For plugin version 1.1 or more & Attachement functionality */
	$tables = $wpdb->get_var("SHOW tables LIKE '{$wpdb->prefix}mfs_mailbox_meta'",0,0);
	if($tables == null) {
		mfs_mailbox_install();
	}
}


/** 
 *  Function Name: mfs_load_js_css_admin
 *  Description: Load css and js files.
 */
function mfs_load_js_css_admin() {
	/* Enqueue plugin style-file */
	wp_register_style('mailbox-css', plugins_url('css/mailbox.css', __FILE__));
	wp_enqueue_style('mailbox-css');
	wp_enqueue_style('fileupload-css', plugins_url('js/file-uploader/fileuploader.css', __FILE__));	
	
	/* Enqueue plugin js-file */
	wp_enqueue_script('mailbox-js', plugins_url('js/mailbox.js', __FILE__), array('jquery'));
	wp_enqueue_script('fileupload-js', plugins_url('js/file-uploader/fileuploader.js', __FILE__), array('jquery'));
	
	wp_localize_script('mailbox-js', 'root', site_url());
	wp_localize_script('mailbox-js', 'fileupload_url', plugins_url('file-upload.php', __FILE__));
	wp_localize_script('mailbox-js', 'attchment_nonce', wp_create_nonce('mfs-mailbox'));
}

function my_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_register_script('my-upload', WP_PLUGIN_URL.'/my-script.js', array('jquery','media-upload','thickbox'));
	wp_enqueue_script('my-upload');
}
 
function my_admin_styles() {
	wp_enqueue_style('thickbox');
}
 
if (isset($_GET['page']) && $_GET['page'] == 'my_plugin_page') {
add_action('admin_print_scripts', 'my_admin_scripts');
add_action('admin_print_styles', 'my_admin_styles');
}

/**
 * Function Name: mfs_mailbox_link
 * Description: Add a menu link as main menu on left sidebar in admin section
 */
function mfs_mailbox_link() {
	if( function_exists( 'add_menu_page' ) ) {
		global $unread_count;
		$unread_count = get_unread_mail_count();
		/* Add link for inbox mails */
		add_menu_page( 'Mail Box',
					   'MailBox (' . $unread_count . ')',
					   0,
					   'mail-box',
					   'mfs_mailbox_inbox',
					   plugins_url('mfs-mailbox/images/mail-box.png')
					 );
	}
}

/**
 * Function Name: mfs_mailbox_sub_links
 * Description: This function will create sublinks for inbox, sent, drafts, trash mails
 */
function mfs_mailbox_sub_links() {
	if( function_exists( 'add_submenu_page' ) ) {
		global $unread_count;
		/* Add link for received mails */
		$inbox = add_submenu_page( 'mail-box',
						  'Inbox',
						  'Inbox (' . $unread_count . ')',
						  0,
						  'mail-box',
						  'mfs_mailbox_inbox'
						);
		/* Add link for sent mails */
		$send = add_submenu_page( 'mail-box',
						  'Sent',
						  'Sent',
						  0,
						  'sent-mail',
						  'mfs_mailbox_sent'
						);
		/* Add link for draft mails */	
		$draft = add_submenu_page( 'mail-box',
						  'Drafts',
						  'Drafts',
						  0,
						  'drafts-mail',
						  'mfs_mailbox_drafts'
						);
		/* Add link for trash mails */	
		$trash = add_submenu_page( 'mail-box',
						  'Trash',
						  'Trash',
						  0,
						  'trash-mail',
						  'mfs_mailbox_trash'
						);
		/* Add link to show mail */	
		$show_mail = add_submenu_page( 'mail-box',
						  '',
						  '',
						  0,
						  'show-mail',
						  'mfs_show_mail'
						);
		/* Add link for compose mails */
		$compose = add_submenu_page( 'mail-box',
						  'Compose',
						  'Compose',
						  0,
						  'compose-mail',
						  'mfs_compose_mail'
						);
		add_action( "admin_print_scripts-$inbox",  	  'mfs_load_js_css_admin' );
		add_action( "admin_print_scripts-$send",   	  'mfs_load_js_css_admin' );
		add_action( "admin_print_scripts-$draft",  	  'mfs_load_js_css_admin' );
		add_action( "admin_print_scripts-$trash",  	  'mfs_load_js_css_admin' );
		add_action( "admin_print_scripts-$show_mail", 'mfs_load_js_css_admin' );
		add_action( "admin_print_scripts-$compose",   'mfs_load_js_css_admin' );
	
	}
}

/**
 * Function Name: mfs_mailbox_inbox
 * Description: This function will include the inbox.php file
 */
function mfs_mailbox_inbox() {
	include_once( 'inbox.php' );
}

/**
 * Function Name: mfs_mailbox_sent
 * Description: This function will include the sent.php file
 */
function mfs_mailbox_sent() {
	include_once( 'sent.php' );
}

/**
 * Function Name: mfs_mailbox_drafts
 * Description: This function will include the drafts.php file
 */
function mfs_mailbox_drafts() {
	include_once( 'drafts.php' );
}

/**
 * Function Name: mfs_mailbox_trash
 * Description: This function will include the trash.php file
 */

function mfs_mailbox_trash() {
	include_once( 'trash.php' );
}

/**
 * Function Name: mfs_show_mail
 * Description: This function will include the show-mail.php file
 */
function mfs_show_mail() {
	include_once( 'show-mail.php' );
}

/**
 * Function Name: mfs_compose_mail
 * Description: This function will include the compose-mail.php file
 */
function mfs_compose_mail() {
	$user_id = get_current_user_id();
	include_once( 'compose-mail.php' );
}

/**
 * Function Name: get_unread_mail_count
 * Description: This function will count of unread mails
 */
function get_unread_mail_count() {
	global $wpdb;
	$user_id = get_current_user_id();
	
	$id_search = ':"'. $user_id .'";';
	$id_search = esc_sql(like_escape($id_search)); 
	$id_search = "%" . $id_search . "%";
			
	
	/* Create raw sql query */
    $inbox_mails_count_sql = "SELECT count( DISTINCT(id) ) 
							  FROM {$wpdb->prefix}mfs_mailbox 
							  INNER JOIN {$wpdb->prefix}who_can_see
								ON id = mail_id
							  WHERE (receiver_ids LIKE %s OR receiver_ids=%d)
								AND folder_id NOT IN ( '3', '4' ) 
								AND read_unread = 2 
								AND user_id = %d";
					    
    /* Prepare raw sql query*/
    $inbox_mails_count_sql = $wpdb->prepare( $inbox_mails_count_sql, $id_search, $user_id, $user_id );
    
    /* Execute the sql query */
    return $inbox_mails_count = $wpdb->get_var( $inbox_mails_count_sql );
}

add_action( 'wp_ajax_mfs_delete_attachment', 'mfs_delete_attachment_function' );
function mfs_delete_attachment_function() {
	check_ajax_referer( 'mfs-mailbox', 'mfs_nonce' );
	$file_name  = $_POST['attachment_file'];
	
	$uploads = wp_upload_dir();	
	$attachment_uploads = $uploads['basedir'] . "\\mfsmailbox\\" . $file_name;	
	
	# delete file if exists
	if (file_exists($attachment_uploads)) { 
		unlink ($attachment_uploads); 
		echo 'success';
		die;		
	}
	echo 'fail'.$attachment_uploads;
	die;
}