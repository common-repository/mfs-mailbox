<?php
/**
 *File Name: trash.php
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
	$total_mails = get_total_trash_mails( $user_id );
	$limit = 10;
	
	/* code for pagination */		 
	if($total_mails > 0)
	{
		$current = max( 1, $_GET['paged'] );
		$total_pages = ceil($total_mails / $limit);
		$start = $current * $limit - $limit;
	}
	
	/* Get all the trashed mails */
     $mails = get_trash_mails( $user_id, $start, $limit );
		
	foreach($mails as $mail) {
		if( '' == $all_mail_ids ) {
			$all_mail_ids = $mail['id'];
		} else {
			$all_mail_ids .= ','.$mail['id'];
		}
	}
	
	?>
	<div class="wrap">
	<h2 id="page-title" class="trash-title">Trash Mails</h2>
	<?php
	echo $_SESSION['message'];
	unset($_SESSION['message']);
	?>
	<form action="#" method="post" name="sent-delete" id="sent-delete">
		<?php wp_nonce_field('name_of_my_action','name_of_nonce_field'); ?>
		<input type="submit" id="delete-mail" value="DELETE" class="button-secondary" name="delete-mail" onclick="return confirm('Do you want to really delete?')"/>
		<input type="hidden" id="all-mail-ids" name="all-mail-ids" value="<?php echo $all_mail_ids; ?>" />	
		<input type="hidden" id="mail-ids" name="mail-ids" value="" />
	</form>
	<?php
	
	/* Get the current logged in user ID*/
	$user_id = get_current_user_id();
	
	/* if the trash is clicked */
	if( isset($_POST['delete-mail']) && wp_verify_nonce( $_POST['name_of_nonce_field'], 'name_of_my_action' ) ) {
		
		/* this is checking for the mail ids needs to be deleted by the user */
		if(isset($_POST['mail-ids']) && !empty($_POST['mail-ids'])) {
			
			$trash_ids = '';
			/* this is for deleting all records of the page */
			if( 'all' == $_POST['mail-ids']) {
				
				if(isset($_POST['all-mail-ids']) && !empty($_POST['all-mail-ids'])) {
					$trash_ids = '(' . $_POST['all-mail-ids'] . ')';
					/* Create raw sql query*/
					$trash_mails = "UPDATE {$wpdb->prefix}mfs_mailbox
								   SET folder_id = '0' 
								   WHERE id IN " . $trash_ids;
									
					/* Prepare raw sql query*/
					$trash_mails_query = $wpdb->prepare( $trash_mails );
					
					/* Execute the sql query */
					$result = $wpdb->query( $trash_mails_query );
				}
				$_SESSION['message'] = "<p class='success'>Messages deleted successfully</p>";
				?>
				<script>
					window.location = root + "/wp-admin/admin.php?page=trash-mail/";
				</script>
				
			<?php			
			} else {
				$trash_ids .= '(' . $_POST['mail-ids'] . ')';
				
				/* Create raw sql query*/
				$trash_mails = "UPDATE {$wpdb->prefix}mfs_mailbox
								   SET folder_id = '0' 
								   WHERE id IN " . $trash_ids;
				
				/* Prepare raw sql query*/
				$trash_mails_query = $wpdb->prepare( $trash_mails );
				
				/* Execute the sql query */
				$result = $wpdb->query( $trash_mails_query );
				
				$_SESSION['message'] = "<p class='success'>Messages deleted successfully</p>";
				?>
				<script>
					window.location = root + "/wp-admin/admin.php?page=trash-mail/";
				</script>
			<?php
			}
		}
		
	}
	
	/* Check whether the current user is logged in or not*/
	if ( $user_id != 0 ) {
		
		/* Send the mails array to the mfs_show_data function to show in HTML format*/
		mfs_show_mails( $mails, $current, $total_pages, $start, $total_mails, 4 );
		echo "</div>";
	} else {
		echo 'You have no permission to visit this page';
		echo "</div>";
	}
	
	/**
	 * Function Name: get_trash_mails
	 * Description: This function will return mails trashed by the logged in user
	 */
	function get_trash_mails( $user_id, $start, $limit  ) {
		global $wpdb;
		
		/* Create raw sql query*/
		$trash_mails_query = "SELECT * 
							  FROM {$wpdb->prefix}mfs_mailbox
							  WHERE FIND_IN_SET(receiver_ids, %d) 
								AND folder_id = '4'
							  ORDER BY time DESC
						       LIMIT %d, %d";
		
		/* Prepare raw sql query*/
		$trash_mails_query = $wpdb->prepare( $trash_mails_query, $user_id, $start, $limit );
		
		/* Execute the sql query and send the data to the calling function*/
		return $wpdb->get_results( $trash_mails_query, ARRAY_A );
		
	}
		
	/**
	 * Function Name: get_total_trash_mails
	 * Description: This function will return total number of mails trashed by the logged in user
	 */
	function get_total_trash_mails( $user_id ) {
		global $wpdb;
		
		/* Create raw sql query*/
		$trash_mails_query = "SELECT count( id ) 
							  FROM {$wpdb->prefix}mfs_mailbox
							  WHERE FIND_IN_SET(receiver_ids, %d) 
							  AND folder_id = '4'";
	 
		/* Prepare raw sql query*/
		$trash_mails_query = $wpdb->prepare( $trash_mails_query, $user_id );
		
		/* Execute the sql query */
		$result = $wpdb->get_var( $trash_mails_query );
		
		return $result;
		
	}
?>