$(function(){
   $("#check_code").on('click', function (e) {
       e.preventDefault();
       var code = $('#code').val();
       if(!code){
           alert('Veuillez remplir le chmps code');
           return false;
       }
       $(this).prop('disabled', true);
       $.ajax({
           url: '/check_code',
           type: 'POST',
           data: {
               code: code
           },
           success: function(msg) {
               alert(msg);
               $('#check_code').prop('disabled', false);
           },
           error: function() {
               alert('une erreur interne est survenue. veuillez réessayer ultérieurement');
               $('#check_code').prop('disabled', false);
           }
       });
   });
});
