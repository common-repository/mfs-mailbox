<?php
/**
 *File Name: show-mail.php
 *Description: this will show the message body 
 *Version: 1.0
 *Author:Mindfire Solutions
 *Author URI: http://www.mindfiresolutions.com/
 */
	global $wpdb;
	
	/* this is the mail id */
	$id = $_GET['mail-id'];
	
	/* Get the current logged in user ID*/
	$user_id = get_current_user_id();
	
	/* Create raw sql query*/
	$msg_body = "SELECT * 
				FROM {$wpdb->prefix}mfs_mailbox
				WHERE id = '%d' ";
	
	/* Prepare raw sql query*/
	$msg_body_query = $wpdb->prepare( $msg_body, $id );
	
	/* Execute the sql query */
	$result = $wpdb->get_results( $msg_body_query, ARRAY_A );
	
	$table = "{$wpdb->prefix}mfs_mailbox";
	$data  = array( 'read_unread' => 1 );
	$where = array( 'id' => $id );
	$wpdb->update( $table, $data, $where );
	
	foreach( $result as $val ) {
		
		/* this is for checking whether the user is the sender/reciever */
		// $sender_name = get_userdata( $val['sender_id'] )->first_name . ' ' . get_userdata( $val['sender_id'] )->last_name;
		$sender_name = get_userdata( $val['sender_id'] )->user_login ;
		
		//$receiver_name = get_userdata( $val['receiver_ids'] )->first_name . ' ' . get_userdata( $val['receiver_ids'] )->last_name;
		
			$user_name = '';		
			if( is_serialized( $val['receiver_ids'] ) ) { 
				$receiver_ids_arr = maybe_unserialize($val['receiver_ids']); 
				foreach($receiver_ids_arr as $receiver_id) {
					$single_name = get_userdata( $receiver_id )->user_login;
					$receiver_name .= $single_name . ', ';
				}
			}	
			else {
				$receiver_name = get_userdata( $val['receiver_ids'] )->user_login;
			}
		

		$status = $val['status'];
		$subject = stripslashes( $val['subject'] );
		$message_body = stripslashes( $val['message_body'] );
		$time = $val['time'];
		
		
		/*** Attchemnt Urls ***/
		$attachment_query = "SELECT meta_value 
							FROM {$wpdb->prefix}mfs_mailbox_meta
							WHERE mail_id = %d 
							AND meta_key = 'attachments'";
							
		/* Prepare raw sql query*/
		$attachment_query = $wpdb->prepare( $attachment_query, $id );

		/* Execute the sql query */
		$attachments_result = $wpdb->get_var( $attachment_query );

		if(!empty($attachments_result)) {
			$attachment_urls = maybe_unserialize($attachments_result); 
		}
		
	}
	$sub_query = '';
	
	if( $_GET['type'] == 'mail-box' ) {
		$sub_query .= ' receiver_ids = %d AND status=""';
	} else if( $_GET['type'] == 'sent-mail' ) {
		$sub_query .= ' sender_id = %d AND status=""';
	} else if( $_GET['type'] == 'drafts-mail' ) {
		$sub_query .= ' sender_id = %d AND status="draft"';
	} else if( $_GET['type'] == 'trash-mail' ) {
		$sub_query .= ' receiver_ids = %d AND status="trash"';
	}
	
	$prev_id = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(id) FROM {$wpdb->prefix}mfs_mailbox WHERE id < %d AND $sub_query", $id, $user_id ) );
	$next_id = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(id) FROM {$wpdb->prefix}mfs_mailbox WHERE id > %d AND $sub_query", $id, $user_id ) );
	
	if( $prev_id == '' && $next_id == '' ) {
		$prev_link = "#";
		$next_link = "#";
	} else if( $prev_id == '' && $next_id != '' ) {
		$prev_link = "#";
		$next_link = "?page=show-mail&type=" . $_GET['type'] . "&mail-id=" . $next_id;
	} else if( $prev_id != '' && $next_id == '' ) {
		$prev_link = "?page=show-mail&type=" . $_GET['type'] . "&mail-id=" . $prev_id;
		$next_link = "#";
	} else if( $prev_id != '' && $next_id != '' ) {
		$prev_link = "?page=show-mail&type=" . $_GET['type'] . "&mail-id=" . $prev_id;
		$next_link = "?page=show-mail&type=" . $_GET['type'] . "&mail-id=" . $next_id;
	}
	
	?>
	<div id="message-content">
		<p>
			<span class="show-label">From :</span>
			<span class="show-cnt"><?php echo $sender_name ;?></span>
		</p>
		<p>
			<span class="show-label">To :</span>
			<span class="show-cnt"><?php echo rtrim($receiver_name, ", ") ;?></span>
		</p>
		<p>
			<span class="show-label">Subject :</span>
			<span class="show-cnt"><?php echo $subject ;?></span>
		</p>
		<p>
			<span class="show-label">Date :</span>
			<span class="show-cnt"><?php echo $time ;?></span>
		</p>
		
		<?php 
		if(!empty($attachment_urls)) {	
		?>
			<p>
				<span class="show-label">Attachments</span>
				<?php 
				foreach($attachment_urls as $single_attachment_url) {
					echo '<span class="show-cnt"><a href="'. $single_attachment_url .'" target="_blank">'. basename($single_attachment_url) .'</a></span>';		
				}
				?>
			</p>
		<?php
		}
		?>
		
		<div>
			<a href="?page=compose-mail&type=reply&reply-mail=<?php echo $id;?>" id="reply-mail" class="button-secondary">Reply</a>
			<a href="?page=compose-mail&type=forward&forward-mail=<?php echo $id;?>" id="forward-mail" class="button-secondary">Forward</a>
			<div id="prev-next-link">
			<a href="<?php echo $prev_link ?>" id="prev-mail-link">Prev</a>
			<a href="<?php echo $next_link; ?>" id="next-mail-link">Next</a>
			</div>
		</div>
	</div>
	<div class="msg-body">
		<?php echo stripslashes( $message_body ); ?> 
	</div>
	<?php
?>
