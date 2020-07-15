$(document).ready(function(){
	var chk_allow_register = $("#chk_allow_register");

	if (chk_allow_register.val() == 1) {
		chk_allow_register.prop('checked', true);
	} else {
		chk_allow_register.prop('checked', false);
	}

	$("#chk_allow_register").on('click', function(){
		var checkbox = $( "#chk_allow_register" );
		checkbox.val( checkbox[0].checked ? "1" : "0" );
	});
	
});