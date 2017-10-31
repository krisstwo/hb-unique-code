$(function(){

    /**
     * Generic logic
     */

    $(document).ajaxError(function (event, xhr, settings) {
        $('#modal-flash-error .content').text('Une erreur inattendue est survenue.');
        $('#modal-flash-error').modal('show');
    });

    $(document).ajaxSuccess(function (event, xhr, settings) {

        if(xhr.responseJSON && xhr.responseJSON.error){
            $('#modal-flash-error .content').text(xhr.responseJSON.error);
            $('#modal-flash-error').modal('show');
        }
    });


    /**
     * Page specific logic
     */

    /**
     * Steps page
     */
    $('#date').datepicker({
        dateFormat: "dd/mm/yy"
    });

    $("#to_step_2").on('click', function (e) {
        e.preventDefault();
        $('#step_1').hide();
        $('#step_2').show();
    });

    $("#step_2 form").validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            cgv: {
                required: true
            },
            re_email: {
                required: function(element) {
                    return $(element).val() == $("#email").val();
                }
            }
        },
        errorClass : 'has-error',
        validClass : '',
        highlight: function(element, errorClass, validClass) {
            $(element).parent('.form-group').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parent('.form-group').removeClass(errorClass).addClass(validClass);
        },
        errorPlacement: function () {},
        errorLabelContainer: 'ul.error-list',
        wrapper: 'li'
    });

    $("#to_step_3").on('click', function (e) {
        e.preventDefault();
        if($("#step_2 form").valid()) {
            $.ajax({
                url: '/submit_customer_information',
                type: 'POST',
                data: $("#step_2 form").serialize(),
                success: function (msg) {
                    if (msg == 'ok') {
                        $('#step_2').hide();
                        $('#step_3').show();
                    } else {
                        alert(msg);
                    }
                },
                error: function () {
                    alert('une erreur interne est survenue. veuillez réessayer ultérieurement');
                }
            });
        }
    });

    $("#step_3 form").validate();
    $("#to_step_4").on('click', function (e) {
        e.preventDefault();
        if($("#step_3 form").valid()) {
            $.ajax({
                url: '/submit_reservation',
                type: 'POST',
                data: $("#step_3 form").serialize() + '&' + $("#step_2 form").serialize(),
                success: function (msg) {
                    if (msg == 'ok') {
                        alert('Votre demande de réservation a bien été envoyée !\n\n- L’hôtel vous donnera une réponse par mail d’ici 12 heures\n- Pensez à vérifier vos courriers indésirables\n- Vous ne pouvez pas faire une autre demande de réservation tant que celle-ci est en cours.')
                    } else {
                        alert('error');
                    }
                },
                error: function () {
                    alert('une erreur interne est survenue. veuillez réessayer ultérieurement');
                }
            });
            //$('#step_3').hide();
            //$('#step_4').show();
        }
    });
});
