function submit_me(){
jQuery.post(the_ajax_script.ajaxurl, jQuery("#theForm").serialize()
,
function(response_from_the_action_function){
jQuery("#response_area").html(response_from_the_action_function);
}
);
}