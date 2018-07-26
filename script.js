$(function() {
  $('.msg').fadeOut(10000);
  $('#my_file_area').on('change', function() {
    $('#my_form_area').submit();
  });
  $('#my_file_btn').on('change', function() {
    $('#my_form_btn').submit();
  });
});

/* Isotope js */
$('.grid').isotope({
  // options
  itemSelector: '.grid-item',
  layoutMode: 'fitRows'
});

var elem = document.querySelector('.grid');
var iso = new Isotope( elem, {
  // options
  itemSelector: '.grid-item',
  layoutMode: 'fitRows'
});

// element argument can be a selector string
//   for an individual element
var iso = new Isotope( '.grid', {
  // options
});
