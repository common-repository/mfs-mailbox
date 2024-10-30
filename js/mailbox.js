/**
 * Mailbox Integration specific js
 *
 * @package Mailbox
 * @author  Mindfiresolutions
 */

jQuery(document).ready(function($){

    /* Check and uncheck all check boxes */
    jQuery('#check-all').click (function () {

        /* If this is checked then all Checkboxes
         * should be checked otherwise unchecked
         */
        if (jQuery(this).is (':checked')) {
            jQuery('.check-mail').attr('checked', true);
            jQuery('input[name="mail-ids"]').val('all');
        } else {
            jQuery('.check-mail').attr('checked', false);
        }
    });

    /* on clicking of individual checkbox for trashing */
    jQuery('.check-mail').click (function () {

        if (jQuery('#check-all').is (':checked')) {
            jQuery('#check-all').attr('checked', false);
            var trash_ids = new Array();
            trash_ids = jQuery('#all-mail-ids').val().split(',');

            /* this will check the index of the current element if its present */
            if( -1 >= (jQuery.inArray(jQuery(this).val(),trash_ids)) ) {
                /* do nothing */
            } else {
                /* this will remove the current element from the array if its there */
                trash_ids.splice( jQuery.inArray(jQuery(this).val(),trash_ids),1 );

                /* this is for imploding array with delemeter ',' */
                trash_ids.join(',');

                /* the value will be added to the hidden field for trash/delete functionality */
                jQuery('input[name="mail-ids"]').val(trash_ids);
            }

            /* this will check the index of the current element if its present */
            if( -1 >= (jQuery.inArray('all',trash_ids)) ) {
                /* do nothing */
            } else {
                /* this will remove the current element from the array if its there */
                trash_ids.splice( jQuery.inArray('all',trash_ids),1 );

                /* this is for imploding array with delemeter ',' */
                trash_ids.join(',');

                /* the value will be added to the hidden field for trash/delete functionality */
                jQuery('input[name="mail-ids"]').val(trash_ids);
            }
        }

        /* If this is checked then the corresponding value will be stored in the hidden field */
        if (jQuery(this).is (':checked')) {
            /* the checkbox value will be added to the hidden field value for trash/delete purpose */
            if(jQuery('input[name="mail-ids"]').val() == '') {
                jQuery('input[name="mail-ids"]').val(jQuery(this).val());
            } else {
                jQuery('input[name="mail-ids"]').val(jQuery('input[name="mail-ids"]').val() +',' + jQuery(this).val());
            }

        } else {
            var trash_ids = new Array();
            trash_ids = jQuery('input[name="mail-ids"]').val().split(',');

            /* this will check the index of the current element if its present */
            if( -1 >= (jQuery.inArray(jQuery(this).val(),trash_ids)) ) {
                /* do nothing */
            } else {
                /* this will remove the current element from the array if its there */
                trash_ids.splice( jQuery.inArray(jQuery(this).val(),trash_ids),1 );

                /* this is for imploding array with delemeter ',' */
                trash_ids.join(',');

                /* the value will be added to the hidden field for trash/delete functionality */
                jQuery('input[name="mail-ids"]').val(trash_ids);
            }
        }
    });

    /* this is for validating compose form */
    jQuery('#compose-submit').click (function () {

        if( '' == jQuery('#mail-subject').val() ) {
            return confirm("Do you want to send Message without having subject");
        }

        if( '' == jQuery('#mail-message').val() ) {
            return confirm("Do you want to send Message without having message body");
        }

        return true;
    });

    jQuery('#compose-draft').click (function () {
        if( '' == jQuery('#mail-subject').val() ) {
            return confirm("Do you want to save Message without having subject");
        }

        if( '' == jQuery('#mail-message').val() ) {
            return confirm("Do you want to save Message without having message body");
        }

        return true;
    });

    jQuery('#wp-content-editor-tools').remove();

    /* Select/Unselect all the receivers */
    jQuery('#send-to-all').click (function () {
        if (jQuery(this).is (':checked')) {
            jQuery('.userselect').find('option').attr('selected', 'selected');
        } else {
            jQuery('.userselect').find('option:selected').removeAttr("selected");
            jQuery('.userselect').val([]);
        }
    });

    /* Check if any option is unselected, then uncheck the send-to-all checkbox. */
    jQuery('.userselect option').click(function (event) {
        if( jQuery(".userselect option:not(:selected)").length == 0 ) {
            jQuery('#send-to-all').attr('checked', true);
        }
        else {
            jQuery('#send-to-all').attr('checked', false);
        }
    });
		
	/** File uploader **/
	createUploader();
	
	/*** AJAX Call for Attchment deletion ***/
	jQuery('span.qq-upload-delete').live('click', function(event){	
		var attachment_id,
			attachment_cantainer,
			attachment_file_name,
			hidden_input_id,
			hidden_input_elem;
			
		attachment_file_name = jQuery(this).parents('li.qq-upload-success').find('span.qq-upload-complete-file-name').text();
		attachment_id = jQuery(this).parents('li.qq-upload-success').find('span.qq-upload-file-name').text();
		attachment_cantainer = jQuery(this).parents('li.qq-upload-success');
		
		if(attachment_id != undefined) {
			hidden_input = 'input#' + attachment_id ;
			hidden_input_elem = jQuery('form#create-mail-form').find(hidden_input);
		}
		if(hidden_input_elem.length > 0) {
			hidden_input_elem.remove();
		}
		if(attachment_cantainer.length > 0) {
			attachment_cantainer.remove();
		}
		
		var data = {
			action: 'mfs_delete_attachment',
			attachment_file: attachment_file_name,
			mfs_nonce: attchment_nonce
		};
		jQuery.post(ajaxurl, data, function(response) {
			//do nothing
		});		
		
	});
	

});

function createUploader(){  
	var uploader = new qq.FileUploader({
		element: document.getElementById('basicUploadSuccessExample'),
		action: fileupload_url,		
		onComplete: function(id, fileName, responseJSON) {
		
			var url = responseJSON.attchmenturl;
			var fileNameWithExt = url.match(/.*\/(.*)$/)[1];
			var fileNameWithoutExt = url.match(/.*\/([^/]+)\.([^?]+)/i)[1];		
		
			jQuery('<input>').attr({
				type: 'hidden',
				id: fileNameWithoutExt,
				name: 'attachments[]',
				class: 'attachment-files',
				value: url
			}).appendTo('form#create-mail-form');
			
			jQuery('li.qq-upload-success').each(function(index, value) {
				if(jQuery(this).find('span.qq-upload-file-name').text() == "") {
					jQuery(this).find('span.qq-upload-file-name').text(fileNameWithoutExt);
					jQuery(this).find('span.qq-upload-file-name').text(fileNameWithoutExt);
					
					jQuery(this).find('span.qq-upload-complete-file-name').text(fileNameWithExt);
					jQuery(this).find('span.qq-upload-complete-file-name').text(fileNameWithExt);					
				}
			});
		}		
	}); 
}