jQuery(document).ready(function($) {
    function checkAvailabilityAndPrice() {
        console.log("checkAvailabilityAndPrice called");

        var roomId = $('#roomid').val();
        var arrival = $('#arrivee').val();
        var departure = $('#depart').val();
        var numAdults = parseInt($('#numadult').val());
        var numChildren = parseInt($('#numchild').val());
        var taxeSejour = parseFloat($('#taxesejourhidden').val());
        var fraisService = parseFloat($('#fraisservicehidden').val());
        var fraisMenage = parseFloat($('#fraismenagehidden').val());
        var fraisLinge = parseFloat($('#fraislingehidden').val());
        var nonce = beds24_params.nonce;

        console.log("Params:", { roomId, arrival, departure, numAdults, numChildren });

        if (arrival && departure && numAdults) {
            var ajaxUrl = beds24_params.custom_ajax_url;
            console.log("AJAX URL:", ajaxUrl);

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: {
                    action: 'custom_ajax_handler',
                    roomId: roomId,
                    arrival: arrival,
                    departure: departure,
                    numAdults: numAdults,
                    numChildren: numChildren,
                    nonce: nonce
                },
                success: function(response) {
                    console.log("Response:", response);
                    if (response.success && response.data && response.data.data && response.data.data.length > 0) {
                        var offerData = response.data.data[0];
                        if (offerData.offers && offerData.offers.length > 0) {
                            var price = offerData.offers[0].price;
                            var unitsAvailable = offerData.offers[0].unitsAvailable;

                            var nonRefundablePrice = offerData.offers[1] ? offerData.offers[1].price : null;
                            var nonRefundableAvailable = offerData.offers[1] ? offerData.offers[1].unitsAvailable : 0;

                            console.log("Price:", price, "Units Available:", unitsAvailable);
                            console.log("Non-Refundable Price:", nonRefundablePrice, "Non-Refundable Available:", nonRefundableAvailable);

                            var arrivalDate = new Date(arrival);
                            var departureDate = new Date(departure);
                            var timeDiff = Math.abs(departureDate - arrivalDate);
                            var nbNuits = Math.ceil(timeDiff / (1000 * 3600 * 24));

                            console.log("Number of nights:", nbNuits);

                            $('#prixsejour span').html(price);
                            var taxesejourtotal = taxeSejour * numAdults * nbNuits;
                            var totalprice = price + fraisService + fraisMenage + fraisLinge + taxesejourtotal;
                            totalprice = totalprice.toFixed(2).replace('.', ',');

                            var totalpricenonrefundable = nonRefundablePrice + fraisService + fraisMenage + fraisLinge + taxesejourtotal;
                            totalpricenonrefundable = totalpricenonrefundable.toFixed(2).replace('.', ',');
                            $('#prixtotal span').html(totalprice + " €");
                            $('#availability').html((unitsAvailable ? '<span class="available">Disponible</span>' : '<span class="unavailable">Non disponible</span>'));
                            $('.prix').show();
                            $('#prixtotal').removeClass('off');
                            if (unitsAvailable) {
                                $('#reserver').prop('disabled', false);
                            } else {
                                $('#reserver').prop('disabled', true);
                            }

                            if (nonRefundablePrice !== null) {
                                $('#prixtotalnonremboursable span').html(totalpricenonrefundable + " €");
                                $('#prixtotalnonremboursable').removeClass('off');
                                $('#reserver2').prop('disabled', !nonRefundableAvailable);
                                $('#reserver2').show();
                            } else {
                                $('#prixtotalnonremboursable').html('');
                                $('#prixtotalnonremboursable').addClass('off');
                                $('#reserver2').hide();
                            }
                        } else {
                            $('#prixtotal').addClass('off');
                            $('#availability').html('<span class="unavailable">Non disponible</span>');
                            $('#reserver').prop('disabled', true);
                            $('#reserver2').hide();
                            $('.prix').hide();
                        }
                    } else {
                        console.error('Erreur dans la réponse du serveur:', response);
                        $('#reserver').prop('disabled', true);
                        $('#reserver2').hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur lors de la requête AJAX:', status, error);
                    $('#reserver').prop('disabled', true);
                    $('#reserver2').hide();
                }
            });
        } else {
            console.error('Paramètres manquants:', { roomId, arrival, departure, numAdults, numChildren });
            $('#reserver').prop('disabled', true);
            $('#reserver2').hide();
        }
    }

    function adjustTravelers(triggeredElement) {
        const nbvoyageursmax = parseInt($('#nbvoyageursmax').val());
        let numadult = parseInt($('#numadult').val()) || 0;
        let numchild = parseInt($('#numchild').val()) || 0;

        // Empêcher le nombre d'adultes de descendre en dessous de 1
        if (numadult < 1) {
            numadult = 1;
            $('#numadult').val(numadult);
        }

        // Empêcher le nombre d'enfants de descendre en dessous de 0
        if (numchild < 0) {
            numchild = 0;
            $('#numchild').val(numchild);
        }

        const totalTravelers = numadult + numchild;

        if (totalTravelers > nbvoyageursmax) {
            if (triggeredElement.attr('id') === 'numadult') {
                numchild = nbvoyageursmax - numadult;
                $('#numchild').val(numchild);
            } else {
                numadult = nbvoyageursmax - numchild;
                if (numadult < 1) {
                    numadult = 1;
                    $('#numchild').val(nbvoyageursmax - 1); // Ajuster le nombre d'enfants pour maintenir au moins un adulte
                }
                $('#numadult').val(numadult);
            }
        }
    }

    // Déclenchament des requêtes
    let timeoutNum;
    let timeoutDates;

    
    function debounce(callback, delay) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                callback.apply(this, arguments);
            }, delay);
        };
    }

    
    $('#numadult, #numchild').on('input', debounce(function() {
        adjustTravelers($(this));
        checkAvailabilityAndPrice();
    }, 500));

    $('#arrivee, #depart').on('change', debounce(function() {
        checkAvailabilityAndPrice();
    }, 500)); 

    checkAvailabilityAndPrice();


});
