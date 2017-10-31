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

    /**
     * Hotel view reservation page
     */

    $('a.action-hotel-refuse-reservation').click(function (e) {
        e.preventDefault();
        if (window.confirm('Êtes vous sur de refuser cette réservation ?'))
        {
            window.location.href = e.target.href;
        }

    });


    /**
     * Hotel refuse reservation page
     */

    $('#check-in-date').datepicker({
        dateFormat: "dd/mm/yy"
    });

    $('form#hotel-refuse-reservation').validate({
        rules: {
            reason: {
                required: true
            },
            'check-in-date': {
                required: true
            },
            nights: {
                required: true
            }
        },
        errorClass: 'has-error',
        validClass: '',
        highlight: function (element, errorClass, validClass) {
            $(element).parent('.form-group').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).parent('.form-group').removeClass(errorClass).addClass(validClass);
        },
        errorPlacement: function () {
        },
        errorContainer: 'form#hotel-refuse-reservation .errors',
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(result) {
                    if(result && result.error)
                        return;

                    $('#modal-flash-success .content').text('Votre proposition a bien été envoyée au client');
                    $('#modal-flash-success').modal('show');
                }
            });

            return false;
        }
    });

    $('a.action-hotel-accept-reservation').click(function (e) {
       e.preventDefault();
        if (window.confirm('Êtes vous sur d\'accepter cette réservation ?'))
        {
            window.location.href = e.target.href;
        }

    });

    /**
     * Customer reservation action page
     */

    $('a.action-customer-decline-reservation').click(function (e) {
        e.preventDefault();
        if (window.confirm('Êtes vous sur de refuser cette réservation ?'))
        {
            $.ajax({
                url: $(e.target).attr('href'),
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if(result && result.error)
                        return;

                    window.location.href = '/';//TODO: fetch url from twig
                }
            });
        }

    });

    $('a.action-customer-accept-reservation').click(function (e) {
        e.preventDefault();
        if (window.confirm('Êtes vous sur d\'accepte cette réservation ?'))
        {
            $.ajax({
                url: $(e.target).attr('href'),
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if(result && result.error)
                        return;

                    $('#modal-flash-success .content').html('Votre demande de réservation a bien été envoyée !<br><br>- L’hôtel vous donnera une réponse par mail d’ici 12 heures<br>- Pensez à vérifier vos courriers indésirables<br>- Vous ne pouvez pas faire une autre demande de réservation tant que celle-ci est en cours.');
                    $('#modal-flash-success').modal('show');
                }
            });
        }

    });
});
