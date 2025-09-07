(function ($) {
	"use strict";
	var editor;
	$('.advance-table').DataTable({
		dom: 'Bfrtip',
		buttons: [
		'copy', 'csv', 'excel', 'pdf', 'print'
		],
		responsive: true
	});
	
	

})(jQuery);
