define( ['jquery'], function ( $ ) {
	var $table = $('#access_group');
	
    function init() {
        updateVars();
        bindEvents();
    };
    
    function updateVars() {
    };
    
    function bindEvents() {
    	$table.on( 'click', 'td i', eventChangePermission );
    };
    
    function eventChangePermission(){
    	var $el=$(this),
    		group_id=$el.data('group_id'), 
    		resource_id=$el.data('resource_id'),
    		action,
    		success, error, 
    		data, pnotifyData = { styling: 'bootstrap' };
    	
    	action = 1;
    	if ( $el.hasClass('icon-ok') ) {
    		action = 0;
    	}
    	
    	success = function(data) {
    		console.log(data);
    		if ( !data['error'] ) {
    			$el.attr('class', data['class']);
    			//$.notifier.notice();
    			pnotifyData = {
    					type: 'success',
        				text: data['message']
    			};
    		}
    		else {
    			pnotifyData = {
    					type: 'error',
        				text: data['error']
    			};    			
    		}
    		
    		$.pnotify( pnotifyData );
    	};
    	error = function(jqXHR, textStatus, errorThrown){
			  pnotifyData = {
				  type: 'error',
				  text: jqXHR.responseText
			  };
			  $.pnotify( pnotifyData );
    	};
    	
    	data = {
  			  'group_id': group_id,
			  'resource_id': resource_id,
			  'action': action
		};
    	$.ajax({
    		  type: 'POST',
    		  url: '/?_action=Access_Resource&_prefix=Miaox_DevOffice',
    		  data: data,
    		  success: success,
    		  error: error,
    		  dataType: 'json'
		});
    };
	
	return {
    	init: init
    }
})