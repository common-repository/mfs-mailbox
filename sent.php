<?php
/**
 *File Name: sent.php
 *Description: 
 *Version: 1.0
 *Author:Mindfire Solutions
 *Author URI: http://www.mindfiresolutions.com/
 */

global $wpdb;
$all_mail_ids = '';

/* include the mail-list.php file for listing of mails */
include_once( 'mail-list.php' );

/* it stores current user's ID */
$user_id = get_current_user_id();
$total_mails = get_total_sent_mails($user_id);
$limit = 10;

/* Calculation for pagination */
if($total_mails > 0)
{
	$current = max( 1, $_GET['paged'] );
	$total_pages = ceil($total_mails / $limit);
	$start = $current * $limit - $limit;
}
	
/* Get all the sent mails */
$mails = get_sent_mails( $user_id, $start, $limit );

foreach($mails as $mail) {
	if( '' == $all_mail_ids ) {
		$all_mail_ids = $mail['id'];
	} else {
		$all_mail_ids .= ','.$mail['id'];
	}
}
?>
<div class="wrap">
<h2 id="page-title" class="sent-title">Sent Mails</h2>
<?php
echo $_SESSION['message'];
unset($_SESSION['message']);
?>
<form action="#" method="post" name="sent-delete" id="sent-delete">
	<?php wp_nonce_field('name_of_my_action','name_of_nonce_field'); ?>
	<input type="submit" id="delete-mail" value="DELETE" class="button-secondary" name="delete-mail" onclick="return confirm('Do you want to really delete?')" />
	<input type="hidden" id="all-mail-ids" name="all-mail-ids" value="<?php echo $all_mail_ids; ?>" />	
	<input type="hidden" id="mail-ids" name="mail-ids" value="" />
</form>
<?php

/* if the trash is clicked */
if( isset($_POST['delete-mail']) && wp_verify_nonce( $_POST['name_of_nonce_field'], 'name_of_my_action' ) ) {
	
	/* this is checking for the mail ids needs to be deleted by the user */
	if(isset($_POST['mail-ids']) && !empty($_POST['mail-ids'])) {
		
		$user_id = get_current_user_id();
		$trash_ids = '';
		/* this is for deleting all records of the page */
		if( 'all' == $_POST['mail-ids']) {
			
			if(isset($_POST['all-mail-ids']) && !empty($_POST['all-mail-ids'])) {
				$trash_ids = '(' . $_POST['all-mail-ids'] . ')';
				/* Create raw sql query*/
				$trash_mails = "DELETE FROM {$wpdb->prefix}who_can_see
								WHERE mail_id IN " . $trash_ids .
									"AND user_id =" . $user_id;
				
				/* Prepare raw sql query*/
				$trash_mails_query = $wpdb->prepare( $trash_mails );
				
				/* Execute the sql query */
				$result = $wpdb->query( $trash_mails_query );
			}
			$_SESSION['message'] = "<p class='success'>Messages deleted successfully</p>";
			?>
			<script>
				window.location = root + "/wp-admin/admin.php?page=sent-mail/";
			</script>
		<?php	
		} else {
			$trash_ids .= '(' . $_POST['mail-ids'] . ')';
			
			/* Create raw sql query*/
			$trash_mails = "DELETE FROM {$wpdb->prefix}who_can_see
								WHERE mail_id IN " . $trash_ids .
									"AND user_id =" . $user_id;
			
			/* Prepare raw sql query*/
			$trash_mails_query = $wpdb->prepare( $trash_mails );
			
			/* Execute the sql query */
			$result = $wpdb->query( $trash_mails_query );
			
			$_SESSION['message'] = "<p class='success'>Message deleted successfully</p>";
			?>
			<script>
				window.location = root + "/wp-admin/admin.php?page=sent-mail/";
			</script>
		<?php
		}
	}
}

/* Check whether the current user is logged in or not*/
if ( $user_id != 0 ) {
	
	/* Send the mails array to the mfs_sent_mails function to show in HTML format*/	
	mfs_sent_mails( $mails, $current, $total_pages, $start, $total_mails );
	echo "</div>";
} else {
	echo 'You have no permission to visit this page';
	echo "</div>";
}

/**
 * Function Name: get_sent_mails
 * Description: This function will return mails sent by the logged in user
 */
function get_sent_mails( $user_id, $start, $limit ) {
	global $wpdb;
	
	/* Create raw sql query*/
	$sent_mails = " SELECT * 
					FROM {$wpdb->prefix}mfs_mailbox INNER JOIN {$wpdb->prefix}who_can_see
								ON id = mail_id
					WHERE sender_id =  '%d'
					   AND folder_id NOT IN ( '3' )
					   AND user_id = %d
					ORDER BY time DESC
					LIMIT %d, %d ";
	
	/* Prepare raw sql query*/
	$sent_mails_query = $wpdb->prepare( $sent_mails, $user_id, $user_id, $start, $limit );
	
	/* Execute the sql query */
	$result = $wpdb->get_results( $sent_mails_query, ARRAY_A );
	
	return $result;
	
}

/**
* Function Name: get_total_mails
* Description: This function will return total number of mails recieved by the logged in user
*/
function get_total_sent_mails( $user_id ) {
    global $wpdb;
    
    /* Create raw sql query */
    $sent_mails = " SELECT count( id ) 
					FROM {$wpdb->prefix}mfs_mailbox INNER JOIN {$wpdb->prefix}who_can_see
						ON id = mail_id
					WHERE sender_id =  '%d'
					   AND folder_id NOT IN ( '3' )
					   AND user_id = %d";
					    
    /* Prepare raw sql query*/
    $sent_mails_query = $wpdb->prepare( $sent_mails, $user_id, $user_id );
    
    /* Execute the sql query */
    $result = $wpdb->get_var( $sent_mails_query );
    
    return $result;
    
}
?>