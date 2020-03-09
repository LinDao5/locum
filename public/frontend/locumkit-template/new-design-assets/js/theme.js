/* ============ For Sticky Header ================ */
var headerTop = $('header.header').offset().top;
$(window).scroll(function() {    
    var scroll = $(window).scrollTop();    
    if (scroll > headerTop) {
        $(".headbar").addClass("navbar-fixed-top");
        $('header.header .navbar-default .navbar-brand img').css({width: '60px', transition: 'all 0.4s ease 0s'});
        
    } else {
        $(".headbar").removeClass("navbar-fixed-top");
        $('header.header .navbar-default .navbar-brand img').css({width: '79px', transition: 'all 0.4s ease 0s'});        
    }
});

/* ============ Scroll to Top ================ */
$(window).scroll(function() {
    if ($(this).scrollTop() >= 50) {        // If page is scrolled more than 50px
        $('#return-to-top').fadeIn(200);    // Fade in the arrow
    } else {
        $('#return-to-top').fadeOut(200);   // Else fade out the arrow
    }
});
$('#return-to-top').click(function() {      // When arrow is clicked
    $('body,html').animate({
        scrollTop : 0                       // Scroll to top of body
    }, 500);
});

$(document).ready(function(){
    $('.hidden2').slideDown(1000);
});

/* == Video player pop up == */
$('#fs-pop-video').click(function(){
   $('#sremail').modal('show');
   $('#sremail #video-iframe').html('<iframe width="100%" height="100%" src="'+$(this).attr('data-src')+'" frameborder="0"  allowfullscreen allow="autoplay"></iframe>');
});
$('div#sremail button.close').click(function(){
    $('#sremail').modal('hide');
    $('#sremail #video-iframe').html(''); 
});

/*use for manage bank status*/
       $('input#modal-in_bank').change(function(){
        var c = this.checked ? '1' : '0';
        if(c==1){
            
            $('#fordisplay').show();
        }else{
            $('#fordisplay').hide();
            }
       });

       $('input#modal-ex_bank').change(function(){
        var c = this.checked ? '1' : '0';
        if(c==1){
            
            $('#fordisplayex').show();
        }else{
            $('#fordisplayex').hide();
            }
       }); 

    function managebankincome(id) {
	$('#fordisplay').hide();
	$('#in_bankdate').val('');
	$('#modal-in_bank').attr('checked', false); 
    $('#in_bankid').val(id);
    $('#manage-bank-income').modal('show');
    }   
    function managebankexpanse(id) {
	 $('#fordisplayex').hide();
	$('#ex_bankdate').val('');
	$('#modal-ex_bank').attr('checked', false); 
    $('#ex_bankid').val(id);
    $('#manage-bank-expense').modal('show');
    }
    /*use for manage bank status end*/
    
    
    
    $('div.alert-modal button.close-alert').click(function(){
        messageBoxClose();
    }); 
    $('div.alert-modal button.close.hide-pop-up').click(function(){
        messageBoxClose();
    });


    function messageBoxClose(){
        $('div#alert-modal').removeClass('in');
        $('div#alert-modal').css('display','none');
        $('div#alert-confirm-modal').removeClass('in');
        $('div#alert-confirm-modal').css('display','none');
    }
    function messageBoxOpen(msg, url){
        $('div#alert-modal #alert-message').html(msg);
        $('div#alert-modal').addClass('in');
        $('div#alert-modal').css('display','block'); 
        if(url != null ) {  
           $('button.close-alert').attr('onClick','window.location.replace("'+url+'")');
        }
    }
function set_thousand_number_format(nStr){
   nStr += '';
   x = nStr.split('.');
   x1 = x[0];
   x2 = x.length > 1 ? '.' + x[1] : '';
   var rgx = /(\d+)(\d{3})/;
   while (rgx.test(x1)) {
     x1 = x1.replace(rgx, '$1' + ',' + '$2');
   }
   return x1 + x2;        
 }




 $(document).ready(function() {
        $('input.financein_bankdate').datepicker({
            maxDate: '0',
            dateFormat: 'dd/mm/yy'
        });
    });  
       $(document).ready(function() {
        $('input.financeex_bankdate').datepicker({
            maxDate: '0',
            dateFormat: 'dd/mm/yy'
        });
    }); 


$(".readonly").keydown(function(e){
e.preventDefault();
});
 
$(document).ready(function() {
  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      //event.preventDefault();
      //return false;
    }
  });

$('p.finance-price').each(function(){
var hprice =  $(this).text().split('£');
$(this).text('£'+set_thousand_number_format(hprice[1]));

});
$('h2.mar0').each(function(){
var hprice =  $(this).text().split('£');
$(this).text('£'+set_thousand_number_format(hprice[1]));

});


});









