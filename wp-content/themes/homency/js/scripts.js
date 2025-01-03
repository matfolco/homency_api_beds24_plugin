jQuery(document).ready(function($) {

    let clickedButtonId = null;

    // Capture quel bouton submit a été cliqué
    $('#reserver').click(function() {
        clickedButtonId = 'reserver';
    });

    $('#reserver2').click(function() {
        clickedButtonId = 'reserver2';
    });


    $('#bookingForm').on('submit', function(event) {
        event.preventDefault(); // Empêche l'envoi du formulaire

        // Récupère les valeurs des champs du formulaire
        const nbvoyageursmax = parseInt($('#nbvoyageursmax').val());
        const checkin = $('#arrivee').val();
        const checkout = $('#depart').val();
        const numadult = parseInt($('#numadult').val());
        const numchild = parseInt($('#numchild').val()) || 0;
        const propid = $('#propid').val();
        const roomid = $('#roomid').val();
        let offerid;

        if (clickedButtonId === "reserver") {
            offerid = 1;  
        } else if (clickedButtonId === "reserver2") {
            offerid = 2;
        }
        
        // Formate les dates pour l'URL
        const checkinDate = new Date(checkin);
        const checkoutDate = new Date(checkout);

        // Formatage des dates pour l'affichage
        const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
        const checkinFormatted = checkinDate.toLocaleDateString('fr-FR', options).replace(/\./g, '');
        const checkoutFormatted = checkoutDate.toLocaleDateString('fr-FR', options).replace(/\./g, '');

        // Calcul de la durée du séjour en jours
        const timeDifference = checkoutDate - checkinDate;
        const numnight = timeDifference / (1000 * 60 * 60 * 24);

        if ((numadult + numchild) <= nbvoyageursmax) {
            if (numnight > 0) {
                // Génère l'URL
                let url = `https://www.beds24.com/booking2.php?ownerid=63412&propid=${propid}&width=960&page=book3&limitstart=0&checkin=${encodeURIComponent(checkinFormatted)}&checkin_hide=${checkin}&checkout=${encodeURIComponent(checkoutFormatted)}&checkout_hide=${checkout}&numnight=${numnight}&numadult=${numadult}&numchild=${numchild}&br${offerid}-${roomid}=Réserver`;

                // Ajoute le paramètre lang=en si l'URL contient /en/
                if (window.location.pathname.includes('/en/')) {
                    url += '&lang=en';
                } else {
                    url += '&lang=fr';
                }

                // Redirige l'utilisateur vers l'URL générée
                window.open(url.toString(), '_blank');
            } else {
                alert('La date de départ doit être postérieure à la date d\'arrivée.');
            }
        } else {
            alert('Le nombre total de voyageurs ne doit pas dépasser ' + nbvoyageursmax + '.');
        }
    });


    $('#plus-de-filtres-btn').on('click', function(event) {
        event.preventDefault();
        $('#plus-de-filtres-ctn').toggleClass('off on');
        if ($('#plus-de-filtres-ctn').hasClass('on')) {
            $(this).html('- de filtres');
        } else {
            $(this).html('+ de filtres');
        }
    });








    // Calcul des nuits et / ou date de fin

    $('#arrivee').on('change', function() {
        calculateNights();
        updateDepartMinDate();
        checkDatesConsistency();
    });

    $('#depart').on('change', function() {
        calculateNights();
        checkDatesConsistency();
    });

    $('#nuits').on('change', function() {
        calculateDeparture();
    });

     function updateDepartMinDate() {
        const arriveeVal = $('#arrivee').val();
        const arriveeDate = new Date(arriveeVal);
        arriveeDate.setDate(arriveeDate.getDate() + 1);
        const minDepartVal = arriveeDate.toISOString().split('T')[0];
        $('#depart').attr('min', minDepartVal);
    }

    function checkDatesConsistency() {
        const arriveeVal = $('#arrivee').val();
        let departVal = $('#depart').val();
        const arriveeDate = new Date(arriveeVal);
        let departDate = new Date(departVal);

        if (arriveeDate >= departDate) {
            departDate = new Date(arriveeDate);
            departDate.setDate(departDate.getDate() + 1);
            departVal = departDate.toISOString().split('T')[0];
            $('#depart').val(departVal);
        }

        calculateNights();
    }

    function calculateNights() {
        const arriveeVal = $('#arrivee').val();
        const departVal = $('#depart').val();

        if (arriveeVal && departVal) {
            const arriveeDate = new Date(arriveeVal);
            const departDate = new Date(departVal);

            // Debug: Afficher les dates dans la console
            console.log('Arrivée Date:', arriveeDate);
            console.log('Départ Date:', departDate);

            if (!isNaN(arriveeDate) && !isNaN(departDate) && arriveeDate < departDate) {
                const timeDifference = departDate - arriveeDate;
                const nights = timeDifference / (1000 * 3600 * 24);
                $('#nuits').val(nights);
            }
        }
    }

    function calculateDeparture() {
        const arriveeVal = $('#arrivee').val();
        const nights = parseInt($('#nuits').val(), 10);

        if (arriveeVal && nights > 0) {
            const arriveeDate = new Date(arriveeVal);
            const departDate = new Date(arriveeDate);

            departDate.setDate(arriveeDate.getDate() + nights);

            $('#depart').val(departDate.toISOString().split('T')[0]);
        }
    }

    // Initial call to update nights or departure date based on existing values
    if ($('#arrivee').val() && $('#depart').val() && $('#nuits').val()) {
        calculateNights();
    } else if ($('#arrivee').val() && $('#nuits').val()) {
        calculateDeparture();
    }




    /* Galerie photo */

    if ($(".single-logements .galerie figure").length > 0) {

        var maxDataId = 0; // Initialiser avec une valeur minimale

        $(".single-logements .galerie figure").each(function() {
            var dataId = $(this).data("id"); // Récupérer la valeur de data-id
            if (dataId > maxDataId) {
                maxDataId = dataId; // Mettre à jour maxDataId si une valeur supérieure est trouvée
            }
        });

        $(".single-logements .galerie .nav .next").click(function() {
            var current = parseInt($(".single-logements .galerie figure.on").attr("data-id"));
            if (current < maxDataId) {
                var target = current + 1;
                nextPic(current, target);
                lazyLoad(target, "next");
                
            } else {
                var target = 0;
                nextPic(current, target);
                lazyLoad(target, "next");
            }
        });
        $(".single-logements .galerie .nav .back").click(function() {
            var current = parseInt($(".single-logements .galerie figure.on").attr("data-id"));
            if (current > 0) {
                var target = current - 1;
                nextPic(current, target);
                lazyLoad(target, "back");
            } else {
                var target = maxDataId;
                nextPic(current, target);
                lazyLoad(target, "back");
            }
        });

        function lazyLoad(target, direction) {
            //Lazy Load
            if (direction =="next") {
                var lltarget = target + 1;
            } else {
                var lltarget = target - 1;
            }
            var lltargetSrc = $(".single-logements .galerie figure[data-id='" + lltarget + "'] img").attr("data-src");
            $(".single-logements .galerie figure[data-id='" + lltarget + "'] img").attr("src", lltargetSrc);

        }
        function nextPic(current, target) {
            var targetElement = $(".single-logements .galerie figure[data-id='" + target + "']");
            var currentElement = $(".single-logements .galerie figure[data-id='" + current + "']");

            currentElement.css("z-index", 5);
            targetElement.css("z-index", 10).fadeIn(100, function() {
                // Une fois que l'élément cible est affiché, cacher l'élément courant
                currentElement.toggleClass('off on').css("z-index", 1).hide();
            }).toggleClass('off on');
        };
    }

    /* Galerie photo */



// Correction problème placeholder date sur iphone et Android sur le formulaire de logement classique

var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
var isAndroid = /Android/.test(navigator.userAgent);

if (isIOS || isAndroid) {
    $('.form-recherche-logement #depart, .form-recherche-logement #arrivee').addClass('show-placeholder');
}

function checkValue(input) {
    if ($(input).val()) {
        $(input).addClass('has-value').removeClass('focus');
    } else {
        $(input).removeClass('has-value focus');
    }
}

$('#depart, #arrivee').each(function() {
    checkValue(this); // Initial check

    $(this).on('focus', function() {
        $(this).addClass('focus');
    });

    $(this).on('blur', function() {
        checkValue(this);
    });

    $(this).on('change', function() {
        checkValue(this);
    });
});

// Correction problème placeholder date sur iphone et Android sur le formulaire de réservation





});