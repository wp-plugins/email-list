	
	function elSendemail(site_url){
		var send_email_id = jQuery("#send_email_id").val();
		window.location = site_url + '/?send-email=true&send_email_id=' + send_email_id;
	}
	
	function elConfirmSend(site_url){
		jQuery('<div></div>').appendTo('body')
		    .html('<div><h6>Are you sure you want to send the email?</h6></div>')
		    .dialog({
		        modal: true,
		        title: 'Send Email',
		        zIndex: 10000,
		        autoOpen: true,
		        width: 'auto',
		        resizable: false,
		        buttons: {
		            Yes: function () {
		                // $(obj).removeAttr('onclick');                                
		                // $(obj).parents('.Parent').remove();
				elSendemail(site_url)
		                jQuery(this).dialog("close");
		            },
		            No: function () {
		                jQuery(this).dialog("close");
		            }
		        },
		        close: function (event, ui) {
		            jQuery(this).remove();
		        }
		    });
	}
	
