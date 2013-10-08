require.config({
	paths : {
		bootstrap : 'bootstrap/bootstrap.min',
		text : 'text',
	},
	shim : {
		'jquery.notifier' : [ 'jquery' ]
	}
});

require(["jquery", "bootstrap", 'jquery.pnotify'], function( $ ) {
	var pageName = $('body').attr( 'class' );	
	require([ 'pages/' + pageName ], function( page ) {
		page.init();
	});	
});