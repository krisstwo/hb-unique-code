$(function(){
   /*$("#check_code").on('click', function (e) {
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
   });*/

    $("#to_step_2").on('click', function (e) {
        e.preventDefault();
        $('#step_1').hide();
        $('#step_2').show();
    });

    $("#to_step_3").on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/submit_customer_information',
            type: 'POST',
            data: $("#step_2 form").serialize(),
            success: function(msg) {
                if(msg == 'ok'){
                    $('#step_2').hide();
                    $('#step_3').show();
                } else {
                    alert(msg);
                }
            },
            error: function() {
                alert('une erreur interne est survenue. veuillez réessayer ultérieurement');
            }
        });
    });

    $("#to_step_4").on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/submit_reservation',
            type: 'POST',
            data: $("#step_3 form").serialize()+'&'+$("#step_2 form").serialize(),
            success: function(msg) {
                if(msg == 'ok'){
                    alert('Votre demande de réservation a bien été envoyée !\n\n- L’hôtel vous donnera une réponse par mail d’ici 12 heures\n- Pensez à vérifier vos courriers indésirables\n- Vous ne pouvez pas faire une autre demande de réservation tant que celle-ci est en cours.')
                } else {
                    alert('error');
                }
            },
            error: function() {
                alert('une erreur interne est survenue. veuillez réessayer ultérieurement');
            }
        });
        //$('#step_3').hide();
        //$('#step_4').show();
    });
});
