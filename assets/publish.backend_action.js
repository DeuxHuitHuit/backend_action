/**
 * Publish backend action script
 * 
 * @author: Deux Huit Huit
 * 
 */
(function ($) {
	
	'use strict';
	
	var tableClick = function (e) {
		console.log('table click');
		
		var t = $(this);
		var td = t.closest('td');
		var tr = td.closest('tr');
		
		e.stopPropagation();
		
		// select
		tr.click();
		
		// delete
		//$('#contents table td.field-backend_action').not(td).find('input.backend-action').remove();
		
		//e.preventDefault();
		
		//return false;
	};
	
	var editClick = function (e) {
		console.log('edit click');
		
		e.stopPropagation();
	};
	
	var init = function () {
		$('#contents table button.backend-action').click(tableClick);
		$('#contents fieldset button.backend-action').click(editClick);
	};
	
	$(init);
	
})(jQuery);