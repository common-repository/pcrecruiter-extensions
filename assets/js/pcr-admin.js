// Validate Settings
function validatePCRfeed(){
		var id_length = jQuery('#id_number').val().length;
		if(id_length < 40 && jQuery('#activation').prop('checked')){
			jQuery('#id_number').css('border-color','red');
			jQuery('#activation').prop('checked',false)
			return false;
		} else {
			return true;
		}
};