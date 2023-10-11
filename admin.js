jQuery(document).ready(function($) {

	var setTabSel = (a) => {
		$('nav a').removeClass("sel").css("font-weight","normal");
		a.addClass("sel").css("font-weight","bold");
		$(".tabs>div").hide();
		$(".tabs>div").each(function(){
			if("#" + $(this).attr("id") == a.attr("href")) {
				$(this).show();
			}
		});
	}
	
	$('nav a').on("click",function(e){
		if($(this).attr("href").indexOf("#")==0) {
			e.preventDefault();
			setTabSel($(this));
		}	
	});
	setTabSel($("nav a.sel"));

    $('input[type=range]').on("change",function(){
        let divider = typeof($(this).data("divider"))=="undefined" ? 1 : parseInt($(this).data("divider"));
        $(this).next("span").text( parseInt($(this).val())/divider  );
    })

	if(location.hash!=""){
		if($(location.hash).length > 0 && $('a[href^="'+location.hash+'"]').length > 0) {
			setTabSel($('a[href^="' + location.hash +'"]'));
		}	
	}	

	// ----------------------

	$('#generatortext').on("click",function(e){
		e.preventDefault();
		jQuery('body').append("<span class='waitingAI right'></span>");

		var formData = new FormData();

		formData.append( 'action', 'generatortext' );
		formData.append( 'prompt', $('#prompt').val() );
		formData.append( 'temperature',  $('#temperature').val() );
		formData.append( 'maxtokens',  $('#maxtokens').val() );
		formData.append( 'model',  $('#model').val() );	
		formData.append( 'current',  $('#response').text() );	
	
		const response = fetch( ajaxurl, {
			method: 'POST',
			body: formData,
			headers: {
				'Accept': 'application/json',
			}
		}).then( res => res.json() )
		.then( data => {
			if(typeof(data.choices) == "object") {
				console.log(data);
				$('#response').html($("#response").text() + data.choices[0].message.content);
				$('#resptok').html(data.usage.completion_tokens);
				$('#promtok').html(data.usage.prompt_tokens);
                if(data.choices[0].finish_reason == "length") {
                    alert("Increase maxtokens and try again to complete the response!");
                }
				$('#promptcost').html(data.usage.prompt_cost);
				$('#responsecost').html(data.usage.completion_cost);
				$('#querycost').html(data.usage.total_cost);
				$('#totcost').html(data.usage.global_cost);
				



			}

		
		  	jQuery('body .waitingAI').remove();
		} )
		.catch( err => console.log( err ) );
	

	});

	function copyToClipboard(element) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($(element).text()).select();
		document.execCommand("copy");
		$temp.remove();
	}

	jQuery("#copytext").on("click",function(e){
		e.preventDefault();
		copyToClipboard("#response");
		$(this).parent().append("<span id='copyok'>Done</span>");
		setTimeout(function(){
			$("#copyok").fadeOut("slow",function(){$(this).remove();});
		},5000)
	});

	jQuery("#createpost").on("click",function(e){
		e.preventDefault();
        jQuery('body').append("<span class='waitingAI right'></span>");

		var formData = new FormData();

		formData.append( 'action', 'createpost' );
		formData.append( 'response', $('#response').text() );
        formData.append( 'temperature',  $('#temperature').val() );
		formData.append( 'maxtokens',  $('#maxtokens').val() );
		
		
		const response = fetch( ajaxurl, {
			method: 'POST',
			body: formData,
			headers: {
				'Accept': 'application/json',
			}
		}).then( res => res.json() )
		.then( data => {
			if(data.msg == "ok") {
				console.log(data);
				$(this).parent().append("<span id='createok'>Done. <a href='post.php?post=" + data.id + "&action=edit' target='_blank'>Open post</a></span>");
				/*setTimeout(function(){
					$("#createok").fadeOut("slow",function(){$(this).remove();});
				},10000);*/
			}

		
		  	jQuery('body .waitingAI').remove();
		} )
		.catch( err => console.log( err ) );
	});

	




} );