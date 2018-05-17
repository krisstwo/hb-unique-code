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
        if (stepIndex < 1 || stepIndex > 5)
            return;

        $('.step-container').hide();
        $('#step_' + stepIndex).show();
    };

    $.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );

    var formatPrice = function (price) {

        return isNaN(parseFloat(price)) ? '0,00 €' : parseFloat(price).toFixed(2).replace('.', ',') + ' €';
    }

    var formatNights = function (nights) {
        return isNaN(parseInt(nights)) ? '0 nuits' : (parseInt(nights) == 1 ? '1 nuit' : parseInt(nights) + ' nuits');
    }

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
        onSelect : function(dateText, instance){
            initOfferSelect2();
            initNightsSelect2();
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

    $("#back_step_3").on('click', function (e) {
        e.preventDefault();
        stepTo(3);
    });

    $.validator.addMethod(
        "regex",
        function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }
    );

    $.validator.addMethod(
        "requiredIfInputNotEmpty",
        function(value, element, input) {
            return !$(input).val() || !$(input).val().length || (value && value.length > 0) ? true : false;
        }
    );

    $("#step_2 form").validate({
        ignore: [], // Hidden elements
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
            if ((errorMap['code'] && errorMap['code'] === 'This field is required.') || errorMap['gender'] || errorMap['last_name'] || errorMap['first_name'] || errorMap['email'] || errorMap['phone'])
                $('#step_2 .error-required').show();

            $('#step_2 .error-re-email').hide();
            if(errorMap['re_email'])
                $('#step_2 .error-re-email').show();

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
        ignore: [], // Hidden elements
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
        }
    });

    $("#to_step_4").on('click', function (e) {
        e.preventDefault();
        if($("#step_3 form").valid()) {

            // Set information on step 4
            $('#step_4 .summary-hotel-name').text($('#hotel-name').val());
            $('#step_4 .summary-reservation-date').text($('#date').val());
            $('#step_4 .summary-reservation-nights').text(formatNights($('#number_night').val()));
            $('#step_4 .summary-offer-name').text($('#offer-name').val());
            $('#step_4 .summary-price').text(formatPrice($('#offer_price').val()));

            stepTo(4);

            return false;
        }
    });

    $("#step_4 form").validate({
        ignore: [], // Hidden elements
        rules: {
            cgv: {
                required: true
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
            $('#step_4 .error-cgv').hide();
            if (errorMap['cgv'])
                $('#step_4 .error-cgv').show();

            this.defaultShowErrors();
        }
    });

    var isReservationSubmitted = false;

    $("#to_step_5").on('click', function (e) {
        e.preventDefault();
        if($("#step_4 form").valid()) {
            //can submit only on a page load
            if(isReservationSubmitted)
                return;

            $('#to_step_5').button('loading');

            $.ajax({
                url: $("#step_4 form").attr('action'),
                type: 'POST',
                data: $("#step_2 form").serialize() + '&' + $("#step_3 form").serialize() + '&' + $("#step_4 form").serialize(),
                success: function (result) {
                    if (result && result.error)
                        return;

                    isReservationSubmitted = true;
                    $('#to_step_5').prop('disabled', true);
                    stepTo(5);
                },
                complete: function () {
                    $('#to_step_5').button('reset');
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

        $('#hotel_phone').val(selectedHotel.phone);
        $('#hotel_address').val(selectedHotel.address);

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
            $('#number_person_1').closest('label').attr('title', 'Désolé, cet hôtel ne fournit des prestations que pour 2 personnes.');
            $('#number_person_1').closest('label').tooltip({
                container: "body"
            });
        }

        $('#number_person_2').closest('label').attr('title', '');
        $('#number_person_2').removeAttr("disabled");
        $('#number_person_2').closest('label').tooltip('destroy');
        if(selectedFormula.persons.indexOf(2) === -1){
            $('#number_person_2').attr('disabled', 'disabled');
            $('#number_person_2').closest('label').attr('title', 'Désolé, cet hôtel ne fournit des prestations que pour 1 personne.');
            $('#number_person_2').closest('label').tooltip({
                container: "body"
            });
        }

        reservationDetail();
        $('#offer_price').val(searchFormulaPrice(selectedFormula));
    };

    var reservationDetail = function (){

        $('#reservation-detail').hide();

        $('#reservation-detail #offer_service_afternoon').val('');
        $('#reservation-detail #offer_service_night').val('');
        $('#reservation-detail #offer_service_morning').val('');

        if (!selectedFormula)
            return false;

        if((selectedFormula.service_afternoon || selectedFormula.service_night || selectedFormula.service_morning)){
            var html_content = '';
            if(selectedFormula.service_afternoon)
                html_content += selectedFormula.service_afternoon+'<br><br>';

            if(selectedFormula.service_night)
                html_content += selectedFormula.service_night+'<br><br>';

            if(selectedFormula.service_morning)
                html_content += selectedFormula.service_morning;

            $('#reservation-detail .content').html(html_content);

            $('#reservation-detail #offer_service_afternoon').val(selectedFormula.service_afternoon);
            $('#reservation-detail #offer_service_night').val(selectedFormula.service_night);
            $('#reservation-detail #offer_service_morning').val(selectedFormula.service_morning);

            $('#reservation-detail').show();
        }
    };

    var searchFormulaPrice = function (formula) {
        if (!formula.planning || !formula.planning.length)
            return '';

        var dayDate = $('#date').datepicker('getDate');
        var price = 0;
        var priceMatchDone = false;
        for (var i in formula.planning) {
            var planning = formula.planning[i];

            for (var day in planning.days) {
                //Break on exact match
                if (dayDate && (dayDate.getMonth() + 1) === parseInt(planning.month) && dayDate.getDate() === parseInt(day)) {
                    price = planning.days[day];
                    priceMatchDone = true;
                    break;
                } else if (price === 0 && planning.days[day] > 0) //set the price to fist non-zero value, first
                    price = planning.days[day];
                else if (planning.days[day] > 0 && planning.days[day] < price)//set the lowest price we find
                    price = planning.days[day];
            }

            if (priceMatchDone) break;
        }
        if (price > 0)
            return price;
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
                if (d.id) {
                    var price = searchFormulaPrice(d);
                    return $('<span>' + d.text + '</span>' + '<span id="price-' + d.id + '" class="price">' + (price ? (' ' + price + ' €').replace('.', ',') : '') + '</span>');
                } else
                    return $(d.text);
            },
            templateSelection: function (d) {
                if (d.id) {
                    var price = searchFormulaPrice(d);
                    return $('<span>' + d.text + '</span>' + '<span id="price-' + d.id + '" class="price">' + (price ? (' ' + price + ' €').replace('.', ',') : '') + '</span>');
                } else
                    return $(d.text);
            }
        });

        //Set value on current selection or select 1st by default
        if(selectedFormula)
            $('#offer').val(selectedFormula.id);
        else if(formulaOptions.length > 0)
            $('#offer').val(formulaOptions[0].id);

        $('#offer').trigger('change');
    };

    //Trigger the formula chage logic when selecting other one
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

        var selectedDate = $('#date').datepicker( "getDate" );
        var disableFrom = 0;
        if(selectedDate && nightsOptions.length) {
            for (var i in selectedFormula.planning) {
                var planning = selectedFormula.planning[i];
                if(parseInt(planning.year) == selectedDate.getFullYear() && parseInt(planning.month) == (selectedDate.getMonth() + 1)){
                    $.each(nightsOptions, function(index, val){
                        if(planning.days[selectedDate.getDate() + index] == 0){
                            disableFrom = index;
                            return false;
                        }
                    });
                }
            }
        }

        if(disableFrom > 0) {
            for (i = disableFrom; i < nightsOptions.length; i++){
                nightsOptions[i]['disabled'] = true;
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
            nights: {
                requiredIfInputNotEmpty: $('#check-in-date')
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
        var noConfirm = $(this).data('no-confirm');
        var btnAction = function () {
            $('a.action-customer-decline-reservation').button('loading');
            $.ajax({
                url: $(e.target).attr('href'),
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result && result.error)
                        return;
                    var redirectURl = $('#customer-decline-redirect-url').val();
                    window.location.href = redirectURl ? redirectURl : '/';
                },
                complete: function () {
                    $('a.action-customer-decline-reservation').button('reset');
                }
            });
        };

        if (noConfirm == true) {
            btnAction();
        } else {
            confirmBox(null, 'Êtes vous sur de refuser cette réservation ?').done(btnAction);
        }

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

                    $('#modal-flash-success-static .content').html('Bravo! Votre réservation est confirmée. <br><br>Vous allez recevoir par mail le détail de votre séjour.<br>Pensez à vérifier vos courriers indésirables<br><br>A noter ! Chaque réservation est soumise aux CGV de l’hôtel. Il est possible que l’hôtel vous contacte pour compléter votre réservation.');
                    $('#modal-flash-success-static').modal('show');
                },
                complete: function () {
                    $('a.action-customer-accept-reservation').button('reset');
                }
            });
        });

    });
});
