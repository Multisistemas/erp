$(document).ready(function(){
	if ($("form#login").length > 0){
		var form = $("form#login");
		var protocol = location.protocol;
		var slashes = protocol.concat("//");
		var host = slashes.concat(window.location.hostname);

		form.after('<div id="gcontainer" style="text-align: center; margin: 0px auto; max-width: 560px;">'+ 
				'<a id="signinButton" href="'+host+'/index.php/google">'+
				'Login with Google!'+
				'</a></div>');

		$("#signinButton").css({
			'text-decoration':'none',
			'padding':'10px 20px',
			'background-color':'#c53929',
			'color':'#ffffff',
			'border-radius':'5px',
			'font-size':'medium',
		});
	}
});


