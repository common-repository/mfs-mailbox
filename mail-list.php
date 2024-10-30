<?php
/**
 *File Name: mail-list.php
 *Description: 
 *Version: 1.0
 *Author:Mindfire Solutions
 *Author URI: http://www.mindfiresolutions.com/
 */

/**
 * Function Name: get_time_in_format
 * Description: This function will create mail time in proper format
 */
function get_time_in_format($db_time) {
	$day = $month = $year = '';
	if( date('Y', strtotime($db_time)) < date('Y') ) {
		$year = date('Y', strtotime($db_time));
		$day = date('d', strtotime($db_time));
		$month = date('m', strtotime($db_time));
		$time = $month . '/' . $day . '/' . $year;
	} else {
		if( date('j', strtotime($db_time)) < date('j') ) {
			$month = date('M', strtotime($db_time));
			$day = date('d', strtotime($db_time));
			$time = $month . ' ' . $day;
		} else {
			$day = date('H:m A', strtotime($db_time));
			$time = $day;
		}
	}
	return $time;
}

/**
 * Function Name: mfs_show_mails
 * Description: This function will show all mails recieved/trash by the logged in user
 */
function mfs_show_mails( $data, $current, $total_pages, $start, $total_mails, $folder_id ) {
	
	$type = '';
	if( 'mail-box' == $_GET['page'] ) {
		$type = '&type=mail-box';
	} else if( 'trash-mail' == $_GET['page'] ) {
		$type = '&type=trash-mail';
	}
	
	/* code for pagination */
	?>
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $total_mails?> <?php echo 'mails'; ?></span>
			<?php
			echo paginate_links(
						array(
							'current' 	=> $current,
							'prev_text'	=> '&laquo; ' . __('Prev'),
							'next_text'    	=> __('Next') . ' &raquo;',
							'base' 		=> @add_query_arg('paged','%#%'),
							'format'  	=> '?page_id=$searchId',
							'total'   	=> $total_pages
						)
			);
			?>
		</div>
	</div>
	
	<!-- this is the table for showing all mails recieved/trash by the logged in user as per the status -->
	<table width="100%" cellpadding="5" cellspacing="0" border="0" class="wp-list-table widefat">
		<tr>
			<th class="manage-column"><input type="checkbox" id="check-all"></th>
			<th class="manage-column">From</th>
			<th class="manage-column">Subject</th>
			<th class="manage-column">Date</th>
		</tr>
		<?php
		
		if( empty($data) ) {
		?>
			<tr>
				<td colspan=4 class="empty_msg">
				<?php 
				if( $folder_id == 1 )
					echo "You have not any message in inbox";
				else if( $folder_id == 4)
					echo "You have not any message in trash";
				?>
				</td>
			</tr>
		<?php
		}
		
		foreach( $data as $value ) {
			$user_id = $value['sender_id'];
			$user_name = get_userdata( $value['sender_id'] )->user_login;
			$class = (2 == $value['read_unread']) ? 'unread' : '';
			/* Get the time in proper format */
			$time = get_time_in_format($value['time']);
			?>
			<tr class="<?php echo $class; ?>">
				<td><input type="checkbox" class="check-mail" id="check_<?php echo $value['id'] ;?>" value="<?php echo $value['id'] ;?>"></td>
				<td><?php echo $user_name ;?></td>
				<td><a href="?page=show-mail<?php echo $type; ?>&mail-id=<?php echo $value['id']; ?>"><?php echo stripslashes( $value['subject'] ) ;?></a></td>
				<td><?php echo $time ;?></td>
			</tr>
			<?php
		}
		?>
	</table>
	
	<!-- code for pagination -->
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $total_mails?> <?php echo 'mails'; ?></span>
			<?php
			echo paginate_links(
						array(
							'current' 	=> $current,
							'prev_text'	=> '&laquo; ' . __('Prev'),
							'next_text'    	=> __('Next') . ' &raquo;',
							'base' 		=> @add_query_arg('paged','%#%'),
							'format'  	=> '?page_id=$searchId',
							'total'   	=> $total_pages
						)
			);
			?>
		</div>
	</div>
	<?php
}

/**
 * Function Name: mfs_sent_mails
 * Description: This function will show all mails sent by current logged in user
 */
function mfs_sent_mails( $data, $current, $total_pages, $start, $total_mails ) {
	
	echo $_SESSION['message'];
	//unset($_SESSION['message']);
	/* code for pagination */
	?>
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $total_mails?> <?php echo 'mails'; ?></span>
			<?php
			echo paginate_links(
						array(
							'current' 	=> $current,
							'prev_text'	=> '&laquo; ' . __('Prev'),
							'next_text'    	=> __('Next') . ' &raquo;',
							'base' 		=> @add_query_arg('paged','%#%'),
							'format'  	=> '?page_id=$searchId',
							'total'   	=> $total_pages
						)
			);
			?>
		</div>
	</div>
	
	<!-- this is the table for showing all mails sent by the logged in user as per the status -->
	<table width="100%" cellpadding="5" cellspacing="0" border="0" class="wp-list-table widefat">
		<tr>
			<th class="manage-column"><input type="checkbox" id="check-all" /></th>
			<th class="manage-column">To</th>
			<th class="manage-column">Subject</th>
			<th class="manage-column">Date</th>
		</tr>
		
		<?php
		if( empty($data) ) {
		?>
			<tr>
				<td colspan=4 class="empty_msg">You have not any message in sent</td>
			</tr>
		<?php
		}

		foreach( $data as $value ) {
			$user_name = '';
		
			if( is_serialized( $value['receiver_ids'] ) ) { 
				$receiver_ids_arr = maybe_unserialize($value['receiver_ids']); 
				foreach($receiver_ids_arr as $receiver_id) {
					$single_name = get_userdata( $receiver_id )->user_login;
					$user_name .= $single_name . ', ';
				}
			}	
			else {
				$user_name = get_userdata( $value['receiver_ids'] )->user_login;
			}
			
			$user_id = $value['receiver_ids'];		
			$class = (2 == $value['read_unread']) ? 'unread' : '';
			/* Get the time in proper format */
			$time = get_time_in_format($value['time']);
			?>
			<tr class="<?php echo $class; ?>">
				<td>
					<input type="checkbox" class="check-mail" id="check_<?php echo $value['id'] ;?>" value="<?php echo $value['id'] ;?>" /></td>
				<td><?php echo rtrim($user_name, ", ");?></td>
				<td><a href="?page=show-mail&type=sent-mail&mail-id=<?php echo $value['id']; ?>"><?php echo stripslashes( $value['subject'] ) ;?></a></td>
				<td><?php echo $time ;?></td>
			</tr>
			<?php
		}
		?>
	</table>
	
	<!-- code for pagination -->
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $total_mails?> <?php echo 'mails'; ?></span>
			<?php
			echo paginate_links(
						array(
							'current' 	=> $current,
							'prev_text'	=> '&laquo; ' . __('Prev'),
							'next_text'    	=> __('Next') . ' &raquo;',
							'base' 		=> @add_query_arg('paged','%#%'),
							'format'  	=> '?page_id=$searchId',
							'total'   	=> $total_pages
						)
			);
			?>
		</div>
	</div>
	<?php
	unset($_SESSION['message']);
}

/**
 * Function Name: mfs_draft_mails
 * Description: This function will show all draft mails by current logged in user
 */
function mfs_draft_mails( $data, $current, $total_pages, $start, $total_mails ) {
	/* code for pagination */
	?>
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $total_mails?> <?php echo 'mails'; ?></span>
			<?php
			echo paginate_links(
						array(
							'current' 	=> $current,
							'prev_text'	=> '&laquo; ' . __('Prev'),
							'next_text'    	=> __('Next') . ' &raquo;',
							'base' 		=> @add_query_arg('paged','%#%'),
							'format'  	=> '?page_id=$searchId',
							'total'   	=> $total_pages
						)
			);
			?>
		</div>
	</div>
	
	<!-- this is the table for showing all mails recieved/trash by the logged in user as per the status -->
	<table width="100%" cellpadding="5" cellspacing="0" border="0" class="wp-list-table widefat">
		<tr>
			<th class="manage-column"><input type="checkbox" id="check-all"></th>
			<th class="manage-column">To</th>
			<th class="manage-column">Subject</th>
			<th class="manage-column">Date</th>
		</tr>
		
		<?php
		if( empty($data) ) {
		?>
			<tr>
				<td colspan=4 class="empty_msg">You have not any message in draft</td>
			</tr>
		<?php
		}
		
		foreach( $data as $value ) {
			$user_id = $value['receiver_ids'];
			$user_name = get_userdata( $value['receiver_ids'] )->user_login;
			$class = (2 == $value['read_unread']) ? 'unread' : '';
			/* Get the time in proper format */
			$time = get_time_in_format($value['time']);
			?>
			<tr class="<?php echo $class; ?>">
				<td><input type="checkbox" class="check-mail" id="check_<?php echo $value['id'] ;?>" value="<?php echo $value['id'] ;?>" /></td>
				<td><?php echo $user_name ;?></td>
				<td><a href="?page=compose-mail&type=drafts-mail&mail-id=<?php echo $value['id']; ?>"><?php echo stripslashes( $value['subject'] ) ;?></a></td>
				<td><?php echo $time;?></td>
			</tr>
			<?php
		}
		?>
	</table>
	
	<!-- code for pagination -->
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $total_mails?> <?php echo 'mails'; ?></span>
			<?php
			echo paginate_links(
						array(
							'current' 	=> $current,
							'prev_text'	=> '&laquo; ' . __('Prev'),
							'next_text'    	=> __('Next') . ' &raquo;',
							'base' 		=> @add_query_arg('paged','%#%'),
							'format'  	=> '?page_id=$searchId',
							'total'   	=> $total_pages
						)
			);
			?>
		</div>
	</div>
	<?php
}
?>