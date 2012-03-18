$(function(){
    $('.error').hide();
    $('.success').hide();
    $('.error_argent').hide();
    $('.error_price').hide();
    $('.formBid').hide();
    
    $('.recruit').click(function(){
    var id = $(this).attr('id');
    var parent = $(this).parent().parent();
    var url_send = $(this).attr('href');
    
    $.ajax({
      type: "POST",
      url: url_send,
      success: function(data)
      {
        var responseData = jQuery.parseJSON(data);
        if(responseData.status == 'error'){
            $('.error').fadeIn();
        }
        else{
            parent.slideUp("slow",function(){$(this).remove();});
            $('.success').fadeIn();
        }
      }
    });
    return false;
  });
  $('.kick').click(function(){
    var id = $(this).attr('id');
    var parent = $(this).parent().parent();
    var url_send = $(this).attr('href');
    
    $.ajax({
      type: "POST",
      url: url_send,
      success: function()
      {
        $('.success').fadeIn();
        parent.slideUp("slow",function(){$(this).remove();});
      }
    });
    return false;
  });
  $('.bid').click(function(){
    $(this).parent().parent().next().fadeIn();
    return false;
  });
  
  $('#time_end').datetimepicker({ 
      dateFormat: 'yy-mm-dd',
      ampm: true,
      timeFormat: 'h:m TT'
  });
    $( "#slider-range" ).slider({
            range: "min",
            value: 1000,
            min: 1000,
            max: 100000,
            slide: function( event, ui ) {
                    $( "#price" ).val(ui.value );
                    $( "#price_slider" ).html(ui.value );
            }
        });
    $( "#price" ).val($( "#slider-range" ).slider( "value" ));
    $( "#price_slider" ).html($( "#slider-range" ).slider( "value" ));
  
});