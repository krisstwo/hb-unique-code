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
        if (stepIndex < 1 || stepIndex > 4)
            return;

        $('.step-list .step-list-item').removeClass('active');
        $('.step-list #step-' + stepIndex).addClass('active');

        $('.step-container').hide();
        $('#step_' + stepIndex).show();
    };

    $.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );

    $('#step_3 #date').datepicker({
        dateFormat: "dd/mm/yy",
        minDate: new Date(),
        beforeShowDay: function (date) {
            //if no forumla can't really choose
            if (!selectedFormula)
                return [false, 'forfait-unavailable', 'Ce jour n\'est disponible'];

            //see if a planning satisfies the current date
            var shownYear = date.getFullYear();
            var shownMonth = date.getMonth() + 1;
            var shownDay = date.getDate();

            var isStatisfied = false;
            for (var i in selectedFormula.planning) {
                var planning = selectedFormula.planning[i];
                if(parseInt(planning.year) == shownYear && parseInt(planning.month) == shownMonth && planning.days[shownDay] !== undefined && planning.days[shownDay] > 0)
                    isStatisfied = true;
            }

            return isStatisfied ? [true] : [false, 'forfait-unavailable', 'Ce jour n\'est disponible'];
        },
        onSelect : function(dateText){
            reservationDetail(dateText);
        }
    });

    $("#to_step_2").on('click', function (e) {
        e.preventDefault();
        stepTo(2);
    });

    $("#back_step_1").on('click', function (e) {
        e.preventDefault();
        stepTo(1);
    });

    $("#back_step_2").on('click', function (e) {
        e.preventDefault();
        stepTo(2);
    });

    $.validator.addMethod(
        "regex",
        function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }
    );

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
            gender: {
                required: true
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
                equalTo: "#email"
            },
            phone: {
                required: true,
                regex: /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/
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
            if ((errorMap['code'] && errorMap['code'] === 'This field is required.') || errorMap['last_name'] || errorMap['first_name'] || errorMap['email'] || errorMap['phone'])
                $('#step_2 .error-required').show();

            $('#step_2 .error-re-email').hide();
            if(errorMap['re_email'])
                $('#step_2 .error-re-email').show();

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

    var isReservationSubmitted = false;

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
            //can submit only on a page load
            if(isReservationSubmitted)
                return;

            $('#to_step_4').button('loading');

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize() + '&' + $("#step_2 form").serialize(),
                success: function (result) {
                    if (result && result.error)
                        return;

                    isReservationSubmitted = true;
                    $('#to_step_4').prop('disabled', true);
                    stepTo(4);
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

        $('#hotel-informations').show();
        $('#hotel-informations .content').html(selectedHotel.informations.replace(/(?:\r\n|\r|\n\n|\n)/g, '<br>'));

        initOfferSelect2();
    };

    $('#number_person_1').tooltip();
    $('#number_person_2').tooltip();
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

        initNightsSelect2();
        // select nb person
        $('#number_person_1').closest('label').attr('title', '');
        $('#number_person_1').removeAttr("disabled");
        $('#number_person_1').closest('label').tooltip('destroy');
        if(selectedFormula.persons.indexOf(1) === -1){
            $('#number_person_1').attr('disabled', 'disabled');
            $('#number_person_1').closest('label').attr('title', 'Désolé, cet hôtel ne fournit des prestations pour 1 personne.');
            $('#number_person_1').closest('label').tooltip();
        }

        $('#number_person_2').closest('label').attr('title', '');
        $('#number_person_2').removeAttr("disabled");
        $('#number_person_2').closest('label').tooltip('destroy');
        if(selectedFormula.persons.indexOf(2) === -1){
            $('#number_person_2').attr('disabled', 'disabled');
            $('#number_person_2').closest('label').attr('title', 'Désolé, cet hôtel ne fournit des prestations pour 2 personnes.');
            $('#number_person_2').closest('label').tooltip();
        }

        reservationDetail($('#step_3 #date').val());
        $('#offer_price').val(searchFormulaPrice(selectedFormula, true));
    };

    var reservationDetail = function (date){

        $('#reservation-detail').hide();

        $('#reservation-detail #offer_service_afternoon').val('');
        $('#reservation-detail #offer_service_night').val('');
        $('#reservation-detail #offer_service_morning').val('');

        if (!selectedFormula || !date)
            return false;

        var month = parseInt(date.split('/')[1]);
        var year = parseInt(date.split('/')[2]);
        for (var i in selectedFormula.planning) {
            var planning = selectedFormula.planning[i];

            if(parseInt(planning.year) == year && parseInt(planning.month) == month){
                if((planning.service_afternoon || planning.service_night || planning.service_morning)){
                    $('#reservation-detail').show();
                    $('#reservation-detail .content').html(planning.service_afternoon+'<br><br>'+planning.service_night+'<br><br>'+planning.service_morning);

                    $('#reservation-detail #offer_service_afternoon').val(planning.service_afternoon);
                    $('#reservation-detail #offer_service_night').val(planning.service_night);
                    $('#reservation-detail #offer_service_morning').val(planning.service_morning);
                }
            }
        }
    };

    var searchFormulaPrice = function (formula, without_currency) {
        if (!formula.planning || !formula.planning.length)
            return '';

        var price = 0;
        for (var i in formula.planning) {
            var planning = formula.planning[i];

            for (var day in planning.days) {
                //set the price to fist non-zero value, first
                if (price === 0 && planning.days[day] > 0)
                    price = planning.days[day];
                else if (planning.days[day] > 0 && planning.days[day] < price)//set the lowest price we find
                    price = planning.days[day];
            }
        }

        if(without_currency)
            return price;

        if (price > 0)
            return (' ' + price + ' €').replace('.', ',');
        else
            return '';
    };

    $('#hotel').select2({
        language: 'fr',
        placeholder: 'Saisissez le nom de l\'hôtel *',
        ajax: {
            url: $('#hotel').data('endpoint'),
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

    $('#offer').select2({
        language: 'fr',
        minimumResultsForSearch: -1
    });

    $('#number_night').select2({
        language: 'fr',
        minimumResultsForSearch: -1
    });

    //as we need to refresh the select2, it is wrapped in a function
    var initOfferSelect2 = function () {
        var formulaOptions = [];

        for (var i in selectedHotel.formulas) {
            var forumula = selectedHotel.formulas[i];
            forumula.text = forumula.label;
            formulaOptions.push(forumula);
        }

        //reset value, otherwise it keep showing old options
        $('#offer option').remove();

        $('#offer').select2({
            language: 'fr',
            minimumResultsForSearch: -1,
            data: formulaOptions,
            templateResult: function (d) {
                if (d.id)
                    return $('<span>' + d.text + '</span>' + '<span id="price-' + d.id + '" class="price">' + searchFormulaPrice(d) + '</span>');
                else
                    return $(d.text);
            },
            templateSelection: function (d) {
                if (d.id)
                    return $('<span>' + d.text + '</span>' + '<span id="price-' + d.id + '" class="price">' + searchFormulaPrice(d) + '</span>');
                else
                    return $(d.text);
            }
        });

        if(formulaOptions.length > 0)
            $('#offer').val(formulaOptions[0].id);
        $('#offer').trigger('change');
    };

    $('#offer').change(function (e) {
        selectFormula($('#offer').val());
    });

    //as we need to refresh the select2, it is wrapped in a function
    var initNightsSelect2 = function () {
        var nightsOptions = [];
        if (selectedFormula && selectedFormula.nights && selectedFormula.nights.length) {
            for (var i = 0; i < selectedFormula.nights.length; i++) {
                nightsOptions.push({
                    id: selectedFormula.nights[i],
                    text: selectedFormula.nights[i] == 1 ? '1 nuit' : selectedFormula.nights[i] + ' nuits'
                });
            }
        }

        //reset value, otherwise it keep showing old options
        $('#number_night option').remove();

        $('#number_night').select2({
            language: 'fr',
            minimumResultsForSearch: -1,
            data: nightsOptions
        });

        if(nightsOptions.length > 0)
            $('#number_night').val(nightsOptions[0].id).trigger('change');
        $('#number_night').trigger('change');
    };

    /**
     * Hotel view reservation page
     */

    $('a.action-hotel-refuse-reservation').click(function (e) {
        e.preventDefault();
        confirmBox(null, 'Êtes vous sur de refuser cette réservation ?').done(function () {
            window.location.href = $('a.action-hotel-refuse-reservation').attr('href');
        });

    });


    /**
     * Hotel refuse reservation page
     */

    $('#check-in-date').datepicker({
        dateFormat: "dd/mm/yy",
        minDate: new Date()
    });

    var isRefusalSubmitted  = false;
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
            //only one successful submit
            if(isRefusalSubmitted)
                return;

            $('#hotel-refuse-reservation button[type="submit"]').button('loading');

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(result) {
                    if(result && result.error)
                        return;

                    isRefusalSubmitted = true;
                    window.location.href = $(form).data('redirect');
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
            window.location.href = $('a.action-hotel-accept-reservation').attr('href');
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
