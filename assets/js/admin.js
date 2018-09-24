/* Coming Soon Features
 *
 * Links to Login Pages
 * Links to External Blocked Iframes (FB)
*/


// JS Below:

jQuery(document).ready(function($){
	$('.menu a[data-attr]').click(function(){
		var attr = $(this).attr('data-attr');
		
		$('.menu a, main div').removeClass('active');
		$(this).addClass('active');
		$('main div.'+attr).addClass('active');
	});
	
	$('.menu').on('click', '.collapse', function(){
		$('.menu a span').hide();
		$(this).removeClass('collapse').addClass('grow');
		$(this).find('svg').removeClass('fa-chevron-circle-left').addClass('fa-chevron-circle-right');
		$('.menu').css({'width':60}).addClass('increase');
		$('main').css({'width':'calc( 100% - 60px )'});
	});
	
	$('.menu').on('click', '.grow', function(){
		$('.menu a span').show();
		$(this).removeClass('grow').addClass('collapse');
		$(this).find('svg').removeClass('fa-chevron-circle-right').addClass('fa-chevron-circle-left');
		$('.menu').css({'width':230}).removeClass('increase');
		$('main').css({'width':'calc( 100% - 230px )'});
	});
});