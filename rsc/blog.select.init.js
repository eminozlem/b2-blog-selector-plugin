$.noConflict();
jQuery(document).ready(function($){
  $('select.blog_sel_w').bind('change', function () {
	  alert("123");
	  var url = $(this).find(':selected').data('url')
	  if (url) { // require a URL
		  window.location = url; // redirect
	  }
	  return false;
  });
});