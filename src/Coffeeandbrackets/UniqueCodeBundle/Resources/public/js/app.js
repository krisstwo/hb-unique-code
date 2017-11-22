$(function(){

    /**
     * Generic logic
     */

    $(document).ajaxError(function (event, xhr, settings) {
        if (xhr.statusText === 'abort')
            return;

        $('#modal-flash-error .content').text('Une erreur inattendue est survenue.');
        $('#modal-flash-error').modal('show');
    });

    $(document).ajaxSuccess(function (event, xhr, settings) {

        if(xhr.responseJSON && xhr.responseJSON.error){
            if (xhr.responseJSON.details && xhr.responseJSON.details.length) {
                $('#modal-flash-error .content').html('<h5>' + xhr.responseJSON.error + '</h5>' + (xhr.responseJSON.details).replace(/(?:\r\n|\r|\n)/g, '<br>'));
            } else {
                $('#modal-flash-error .content').text(xhr.responseJSON.error);
            }
            $('#modal-flash-error').modal('show');
        }
    });

    var confirmBox = function (title, content) {
        var deferred = new $.Deferred();

        //bind btn events
        $('#modal-confirmation .confirm').click(function (e) {
            deferred.resolve();
        });
        $('#modal-confirmation .cancel').click(function (e) {
            deferred.reject();
        });

        //set content
        if (title && title.length) {
            $('#modal-confirmation .modal-title').html(title);
        }
        $('#modal-confirmation .modal-body').html(content);

        //show modal
        $('#modal-confirmation').modal('show');

        return deferred;
    };


    /**
     * Page specific logic
     */

    /**
     * Steps page
     */

    var stepTo = function (stepIndex) {
        if (stepIndex < 1 || stepIndex > 3)
            return;

        $('.step-list .step-list-item').removeClass('active');
        $('.step-list #step-' + stepIndex).addClass('active');

        $('.step-container').hide();
        $('#step_' + stepIndex).show();
    };

    $('#date').datepicker({
        dateFormat: "dd/mm/yy"
    });

    $("#to_step_2").on('click', function (e) {
        e.preventDefault();
        stepTo(2);
    });

    $("#step_2 form").validate({
        rules: {
            code: {
                required: true,
                remote: {
                    url: $('#step_2 #code').data('endpoint'),
                    beforeSend: function () {
                        $('#step_2 #code').attr('readonly', true);
                        $('#step_2 #code + .form-control-feedback').show();
                    },
                    complete: function () {
                        $('#step_2 #code + .form-control-feedback').hide();
                        $('#step_2 #code').removeAttr('readonly');
                    },
                    data: {
                        campaignCode: $('#step_2 #campaignCode').val(),
                        code: function() {
                            return $('#step_2 #code').val();
                        }
                    }
                }
            },
            last_name: {
                required: true
            },
            first_name: {
                required: true
            },
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
        onkeyup: false,
        errorClass : 'has-error',
        validClass : '',
        highlight: function(element, errorClass, validClass) {
            $(element).parents('.form-group').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parents('.form-group').removeClass(errorClass).addClass(validClass);
        },
        errorPlacement: function (error, element) {
        },
        showErrors: function (errorMap, errorList) {
            $('#step_2 .error-code').hide();
            if (errorMap['code'] && errorMap['code'] !== 'This field is required.'){
                $('#step_2 .error-code').text(errorMap['code']);
                $('#step_2 .error-code').show();
            }

            $('#step_2 .error-required').hide();
            if ((errorMap['code'] && errorMap['code'] === 'This field is required.') || errorMap['last_name'] || errorMap['first_name'] || errorMap['email'] || errorMap['re_email'])
                $('#step_2 .error-required').show();

            $('#step_2 .error-cgv').hide();
            if (errorMap['cgv'])
                $('#step_2 .error-cgv').show();

            this.defaultShowErrors();
        }
    });

    $('#re_email').bind("cut copy paste",function(e) {
        e.preventDefault();
    });

    $("#to_step_3").on('click', function (e) {
        e.preventDefault();
        if($("#step_2 form").valid()) {
            $('#to_step_3').button('loading');

            $.ajax({
                url: $("#step_2 form").attr('action'),
                type: 'POST',
                data: $("#step_2 form").serialize(),
                success: function (result) {
                    if (result && result.error)
                        return;

                        stepTo(3);
                },
                complete: function () {
                    $('#to_step_3').button('reset');
                }
            });
        }
    });

    $("#step_3 form").validate({
        rules: {
            number_person: {
                required: true
            },
            hotel: {
                required: true
            },
            date: {
                required: true
            },
            number_night: {
                required: true
            },
            offer: {
                required: true
            },
            customer_msg: {
                maxlength: 255
            }
        },
        errorClass: 'has-error',
        validClass: '',
        highlight: function (element, errorClass, validClass) {
            $(element).parents('.form-group').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).parents('.form-group').removeClass(errorClass).addClass(validClass);
        },
        errorPlacement: function () {
        },
        showErrors: function (errorMap, errorList) {
            $('#step_3 .error-required').hide();
            if (errorMap['number_person'] || errorMap['hotel'] || errorMap['date'] || errorMap['offer'])
                $('#step_3 .error-required').show();

            this.defaultShowErrors();
        },
        submitHandler: function(form) {
            $('#to_step_4').button('loading');

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize() + '&' + $("#step_2 form").serialize(),
                success: function (result) {
                    if (result && result.error)
                        return;

                    $('#modal-flash-success .content').html('Votre demande de réservation a bien été envoyée !<br><br>- L’hôtel vous donnera une réponse par mail d’ici 12 heures<br>- Pensez à vérifier vos courriers indésirables<br>- Vous ne pouvez pas faire une autre demande de réservation tant que celle-ci est en cours.');
                    $('#modal-flash-success').modal('show');

                    $('#to_step_4').prop('disabled', true);
                },
                complete: function () {
                    $('#to_step_4').button('reset');
                }
            });

            return false;
        }
    });

    // setp 3 related logic

    var searchResults = [];
    var selectedHotel = null;
    var selectedFormula = null;

    var selectHotel = function (id) {
        selectedHotel = searchResults.find(function (hotel) {
            return hotel.id === id;
        });

        if (!selectedHotel)
            return;

        //keep the name of the hotel for form submission, useful for information retrieval when saving reservation.
        $('#hotel-name').val(selectedHotel.text);

        //set formulas
        var optionTags = [];

        for (var i in selectedHotel.formulas) {
            optionTags.push($('<option value="{1}">{2}</option>'.replace('{1}', selectedHotel.formulas[i].id).replace('{2}', selectedHotel.formulas[i].label)));
        }

        $('#offer').empty().append(optionTags);
        selectFormula(optionTags[0].attr('value'));
        $('#offer').trigger('change.select2');
    };

    var selectFormula = function (id) {
        selectedFormula = selectedHotel.formulas[id];

        if (!selectedFormula)
            return;

        $('#offer-name').val(selectedFormula.label);

        //set available persons for this formula
        $('input[name="number_person"]').each(function (index, el) {
            var el = $(el);
            if (selectedFormula.persons.indexOf(parseInt(el.attr('value'))) === -1) {
                el.attr('disabled', 'disabled');
                el.prop('checked', false);
            } else {
                if (el.attr('disabled')) {
                    el.removeAttr('disabled');
                }
            }
        });

        //set available nights for this formula
        var nightsOptions = $('#number_night option');
        for (var i = 0; i < nightsOptions.length; i++) {
            var el = $(nightsOptions[i]);
            if (selectedFormula.nights.indexOf(parseInt(el.attr('value'))) === -1) {
                el.attr('disabled', 'disabled');
            } else {
                if (el.attr('disabled')) {
                    el.removeAttr('disabled');
                }
            }

            $('#number_night').find('option:first').attr('selected', 'selected');
            $('#number_night').select2({
                minimumResultsForSearch: -1
            });
        }

    };

    $('#hotel').select2({
        language: 'fr',
        placeholder: 'Hôtel *',
        ajax: {
            url: '/ajax-search-hotel',
            dataType: 'json',
            processResults: function (data) {
                var results = [];

                for (var i in data) {
                    data[i].text = data[i].label;
                    results.push(data[i]);
                }

                //save search results for further use (hotel formulas etc)
                searchResults = results;

                return {
                    results: results
                };
            },
            delay: 1000
        },
        minimumInputLength: 3
    });

    $('#hotel').change(function (e) {
        selectHotel($('#hotel').val());
    });

    $('#number_night').select2({
        minimumResultsForSearch: -1
    });
    $('#offer').select2({
        minimumResultsForSearch: -1
    });

    /**
     * Hotel view reservation page
     */

    $('a.action-hotel-refuse-reservation').click(function (e) {
        e.preventDefault();
        confirmBox(null, 'Êtes vous sur de refuser cette réservation ?').done(function () {
            window.location.href = e.target.href;
        });

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
            $('#hotel-refuse-reservation button[type="submit"]').button('loading');

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
                },
                complete: function () {
                    $('#hotel-refuse-reservation button[type="submit"]').button('reset');
                }
            });

            return false;
        }
    });

    $('a.action-hotel-accept-reservation').click(function (e) {
       e.preventDefault();
        confirmBox(null, 'Êtes vous sur d\'accepter cette réservation ?').done(function () {
            window.location.href = e.target.href;
        });

    });

    /**
     * Customer reservation action page
     */

    $('a.action-customer-decline-reservation').click(function (e) {
        e.preventDefault();
        confirmBox(null, 'Êtes vous sur de refuser cette réservation ?').done(function () {
            $('a.action-customer-decline-reservation').button('loading');
            $.ajax({
                url: $(e.target).attr('href'),
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if(result && result.error)
                        return;
                    var redirectURl = $('#customer-decline-redirect-url').val();
                    window.location.href = redirectURl ? redirectURl : '/';
                },
                complete: function () {
                    $('a.action-customer-decline-reservation').button('reset');
                }
            });
        });

    });

    $('a.action-customer-accept-reservation').click(function (e) {
        e.preventDefault();
        confirmBox(null, 'Êtes vous sur d\'accepter cette réservation ?').done(function () {
            $('a.action-customer-accept-reservation').button('loading');
            $.ajax({
                url: $(e.target).attr('href'),
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if(result && result.error)
                        return;

                    $('#modal-flash-success .content').html('Votre demande de réservation a bien été envoyée !<br><br>- L’hôtel vous donnera une réponse par mail d’ici 12 heures<br>- Pensez à vérifier vos courriers indésirables<br>- Vous ne pouvez pas faire une autre demande de réservation tant que celle-ci est en cours.');
                    $('#modal-flash-success').modal('show');
                },
                complete: function () {
                    $('a.action-customer-accept-reservation').button('reset');
                }
            });
        });

    });
});
