<?php
/**
 * @package api_beds24
 * @version 0.5
 */
/*
Plugin Name: API Beds24
Plugin URI: http://www.mathieufolco.com
Description: Appel à l'API Beds24
Author: Mathieu FOLCO
Version: 0.5
Author URI: https://www.mathieufolco.com/
*/


if (!defined('ABSPATH')) {
    exit;
}

/* Récupération des données depuis les formulaires */

if (isset($_POST['generate_token'])) {
    beds24_generate_token();
} elseif (isset($_POST['update_properties'])) {
    // Appeler la fonction pour stocker le JSON

    $result = beds24_get_properties();
    if ($result) {
        echo "JSON a été écrit dans le fichier avec succès.";
    } else {
        echo "Une erreur s'est produite lors de l'écriture du JSON dans le fichier.";
    }
}

/* Récupération des données depuis les formulaires */


/* Script de généréation de token à partir de l'invite code */

function beds24_generate_token() {
    $inviteCode = $_POST['beds24_invite_code'];
    if (!$inviteCode) {
        echo '<div class="error"><p>Please enter the Invite Code before generating the token.</p></div>';
        return;
    }

    $apiKey = 'RcfXdMprXKlK8T8+nGQbfUx'; // Votre clé API Beds24
    $url = 'https://beds24.com/api/v2/authentication/setup';
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'token: ' . $apiKey,
        'code: ' . $inviteCode
    ];
    $response = sendGetRequest($url, [], $headers);

    if ($response) {
        $responseData = json_decode($response, true);
        if (isset($responseData['token']) && isset($responseData['refreshToken'])) {
            update_option('beds24_access_token', $responseData['token']);
            update_option('beds24_refresh_token', $responseData['refreshToken']);
            update_option('beds24_invite_code', $_POST['beds24_invite_code']);

            // Utiliser le jeton de rafraîchissement pour générer de nouveaux jetons
            $url = 'https://beds24.com/api/v2/authentication/token';
            $headers = [
                'Content-Type: application/json',
                'refreshToken: ' . $responseData['refreshToken']
            ];
            $response = sendGetRequest($url, [], $headers);

            if ($response) {
                $responseData = json_decode($response, true);
                if (isset($responseData['token'])) {
                    update_option('beds24_access_token', $responseData['token']);
                }
            }
            echo '<div class="updated"><p>Token generated successfully.</p></div>';
        } else {
            echo '<div class="error"><p>Error generating token.</p></div>';
        }
    } else {
        echo '<div class="error"><p>Error sending request.</p></div>';
    }
}

/* Script de généréation de token à partir de l'invite code */



// Liste de tous les titres de logements


function get_all_residences() {
    $args = array(
        'post_type' => 'logements',
        'post_status' => 'publish',
        'meta_key' => 'control_priority',
        'orderby' => 'meta_value',
        'order' => 'DESC', 
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    $residences = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $residence = get_field('residence');
            if ($residence && !empty(trim($residence))) {
                $residences[] = trim($residence);
            }
        }
    }


    wp_reset_postdata();


    $residences = array_unique($residences);
    
    sort($residences);

    return $residences;
}

// Liste de tous les titres de logements

// Liste de tous les quartiers


function get_all_quartiers() {
    $args = array(
        'post_type' => 'logements',
        'post_status' => 'publish',
        'meta_key' => 'control_priority',
        'orderby' => 'meta_value',
        'order' => 'DESC', 
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    $quartiers = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $quartier = get_field('quartier');
            if ($quartier && !empty(trim($quartier))) {
                $quartiers[] = $quartier;
            }
        }
    }

    wp_reset_postdata();

    $quartiers = array_unique($quartiers);
    sort($quartiers);

    return $quartiers;
}



// Liste de tous les quartiers




/* Génération du formulaire de recherche de logement */

function display_form($atts) {
    $atts = shortcode_atts(
        array(
            'arrivee' => '',
            'depart' => '',
            'adultes' => 0,
            'enfants' => 0,
            'nuits' => 0
        ),
        $atts,
        'recherche-reservation'
    );
    $arrival = esc_attr($atts['arrivee']);
    $departure = esc_attr($atts['depart']);
    $numAdults = (int)$atts['adultes'];
    $numEnfants = (int)$atts['enfants'];
    $numNights = (int)$atts['nuits'];


    ob_start();
    $action = site_url() . "/reserver-logement-location-vacances/";

    $options = isset($_GET['options']) ? (array)$_GET['options'] : [];

    ?>
    <form action="<?php echo $action; ?>" method="GET" class="form-recherche-logement">
        <div class="custom-wrapper">
            <div class="row primary">
                <div class="col">
                    <input type="date" name="arrivee" id="arrivee" min='<?php echo $date = date('Y-m-d'); ?>' max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" value="<?php echo $arrival; ?>" placeholder="arrivée">
                </div>
                <div class="col">
                    <input type="date" name="depart" id="depart" min='<?php echo $date = date('Y-m-d'); ?>' max='<?php echo date('Y-m-d', strtotime('+1 year')); ?>' value="<?php echo $departure; ?>" placeholder="départ">
                </div>
                <div class="col nuits">
                    <select name="nuits" id="nuits">
                        <option value="" <?php echo ($numNights === '') ? 'selected' : ''; ?>>Nuits</option>
                        <?php for ($i = 1; $i <= 30; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($numNights == $i) ? 'selected' : ''; ?>><?php echo $i; ?> nuit<?php echo ($i != 1) ? 's' : ''; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col">
                    <select name="adultes" id="adultes">
                        <?php for ($i = 1; $i <= 14; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($numAdults == $i) ? 'selected' : ''; ?>><?php echo $i; ?> adulte<?php echo ($i != 1) ? 's' : ''; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col">
                    <select name="enfants" id="enfants">
                        <?php for ($i = 0; $i <= 13; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($numEnfants == $i) ? 'selected' : ''; ?>><?php echo $i; ?> enfant<?php echo ($i != 1 && $i != 0) ? 's' : ''; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col button">
                    <input type="hidden" name="action" value="myfilter">
                    <input type="submit" name="action" value="Rechercher">
                </div>
            </div>
            <div class="plus-de-filtres-btn-ctn">
                <button id="plus-de-filtres-btn">+ de filtres</button>
            </div>
            <div id="plus-de-filtres-ctn" class="off">
                <div class="row">
                    <div class="col">
                        <?php // <select name="quartier[]" id="quartier" multiple> ?>
                        <select name="quartier[]" id="quartier">
                            <option value="">Quartier</option>
                            <?php
                            $quartiers = get_all_quartiers();
                            $selected_quartiers = isset($_GET['quartier']) && is_array($_GET['quartier']) ? array_map('esc_attr', $_GET['quartier']) : [];
                            foreach ($quartiers as $quartier) {
                                $selected = in_array($quartier, $selected_quartiers) ? 'selected' : '';
                                echo '<option value="' . esc_attr($quartier) . '" ' . $selected . '>' . esc_html($quartier) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col">
                        <select name="nb_chambres[]" id="nb_chambres">
                            <option value="">Chambres</option>
                            <?php
                            $selected_nb_chambres = isset($_GET['nb_chambres']) && is_array($_GET['nb_chambres']) ? array_map('intval', $_GET['nb_chambres']) : [];
                            for ($i = 1; $i <= 7; $i++) {
                                $selected = in_array($i, $selected_nb_chambres) ? 'selected' : '';
                                echo '<option value="' . $i . '" ' . $selected . '>' . $i . ' chambre' . ($i != 1 ? 's' : '') . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col">
                        <select name="nb_sdb" id="nb_sdb">
                            <option value="">Salles de bain</option>
                            <?php
                            $selected_nb_sdb = isset($_GET['nb_sdb']) ? (int)$_GET['nb_sdb'] : '';
                            for ($i = 1; $i <= 7; $i++) {
                                $selected = ($selected_nb_sdb == $i) ? 'selected' : '';
                                echo '<option value="' . $i . '" ' . $selected . '>' . $i . ' salle' . ($i != 1 ? 's' : '') . ' de bain</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col">
                        <select name="residence[]" id="residence">
                            <option value="">Résidence</option>
                            <?php
                            $residences = get_all_residences();
                            $selected_residence = isset($_GET['residence']) && is_array($_GET['residence']) ? array_filter(array_map('sanitize_text_field', $_GET['residence']), function($value) {
                                return !empty($value) && $value !== '';
                            }) : [];
                            $selected_residence = array_map('stripslashes', $selected_residence);
                            foreach ($residences as $residence) {
                                $selected = in_array($residence, $selected_residence) ? 'selected' : '';
                                echo '<option value="' . esc_attr($residence) . '" ' . $selected . '>' . esc_html($residence) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col">
                    </div>
                    <div class="col">
                    </div>
                </div>

                <h5>Équipements</h5>
                <ul class="options">
                    <li>
                        <input type="checkbox" name="options[]" value="lave-linge" id="lave-linge" <?php echo in_array('lave-linge', $options) ? 'checked' : ''; ?>>
                        <label for="lave-linge">Lave-linge</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="internet" id="internet" <?php echo in_array('internet', $options) ? 'checked' : ''; ?>>
                        <label for="internet">Internet</label>
                    </li>
                    <!-- Ajoutez les autres options de la même manière -->
                </ul>

                <h5>Emplacements</h5>
                <ul class="options">
                    <li>
                        <input type="checkbox" name="options[]" value="skis-aux-pieds" id="skis-aux-pieds" <?php echo in_array('skis-aux-pieds', $options) ? 'checked' : ''; ?>>
                        <label for="skis-aux-pieds">Skis aux pieds</label>
                    </li>
                </ul>

                <h5>Stationnement</h5>
                <ul class="options">
                    <li>
                        <input type="checkbox" name="options[]" value="garage" id="garage" <?php echo in_array('garage', $options) ? 'checked' : ''; ?>>
                        <label for="garage">Garage</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="parking" id="parking" <?php echo in_array('parking', $options) ? 'checked' : ''; ?>>
                        <label for="parking">Parking</label>
                    </li>
                </ul>

                <h5>Bien-être</h5>
                <ul class="options">
                    <li>
                        <input type="checkbox" name="options[]" value="jacuzzi" id="jacuzzi" <?php echo in_array('jacuzzi', $options) ? 'checked' : ''; ?>>
                        <label for="jacuzzi">Jacuzzi</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="sauna" id="sauna" <?php echo in_array('sauna', $options) ? 'checked' : ''; ?>>
                        <label for="sauna">Sauna</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="hammam" id="hammam" <?php echo in_array('hammam', $options) ? 'checked' : ''; ?>>
                        <label for="hammam">Hammam</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="baignoire-a-remous" id="baignoire-a-remous" <?php echo in_array('baignoire-a-remous', $options) ? 'checked' : ''; ?>>
                        <label for="baignoire-a-remous">Baignoire à remous</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="piscine" id="piscine" <?php echo in_array('piscine', $options) ? 'checked' : ''; ?>>
                        <label for="piscine">Piscine</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="spa" id="spa" <?php echo in_array('spa', $options) ? 'checked' : ''; ?>>
                        <label for="spa">Spa</label>
                    </li>
                    <li>
                        <input type="checkbox" name="options[]" value="salle-de-sport" id="salle-de-sport" <?php echo in_array('salle-de-sport', $options) ? 'checked' : ''; ?>>
                        <label for="salle-de-sport">Salle de sport</label>
                    </li>
                </ul>
                <div class="rechercher-secondary">
                    <input type="submit" name="action" value="Rechercher">
                </div>
            </div>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('recherche-reservation', 'display_form');

/* Génération du formulaire de recherche de logement */


/* Stocker le JSON pour le rendre accessible par WP All Import */

function beds24_get_properties() {
    $url = 'https://beds24.com/api/v2/properties';

    $params = [
        'includeLanguages' => 'all',
        'includeTexts' => 'all',
        'includePictures' => true, // Booléen
        'includeOffers' => false, // Booléen
        'includePriceRules' => false, // Booléen
        'includeUpsellItems' => true, // Booléen
        'includeAllRooms' => true // Booléen
    ];
    
    $headers = [
        'Content-Type: application/json',
        'token: ' . get_option('beds24_access_token')
    ];
    $response = sendGetRequest($url, $params, $headers);

    // Stockage des données dans un fichier
    $filePath = plugin_dir_path(__FILE__) . 'json/properties.json';

    // Écriture du JSON dans le fichier
    $fileWritten = file_put_contents($filePath, $response);

    if ($fileWritten !== false) {
        $message = "JSON a été écrit dans le fichier avec succès.";
    } else {
        $message = "Une erreur s'est produite lors de l'écriture du JSON dans le fichier.";
    }

    // URL à appeler dans une tâche CRON
    $cronUrl = add_query_arg(
        [
            'action' => 'beds24_cron_update_properties' // Ajoutez un paramètre d'action pour distinguer la tâche CRON
        ],
        home_url('/')
    );

    $output = '<p>' . esc_html($message) . '</p>';
    $output .= '<p>URL pour la tâche CRON : <code>' . esc_url($cronUrl) . '</code></p>';

    return $output;
}
add_shortcode('properties', 'beds24_get_properties');

/* Stocker le JSON pour le rendre accessible par WP All Import */


/* Nouvelle fonctionnalité : On récupère via les paramètres envoyés par make et on post dans l'API de Beds24 */

// Fonction pour récupérer les paramètres GET et les utiliser comme attributs de shortcode
function get_shortcode_atts_with_get_params($default_atts) {
    // Fusionne les paramètres GET avec les attributs de shortcode par défaut
    $atts = shortcode_atts($default_atts, $_GET);

    // Filtrage des paramètres pour éviter les conflits éventuels
    foreach ($atts as $key => $value) {
        $atts[$key] = sanitize_text_field($value);
    }

    return $atts;
}

function beds24_update_booking_email($booking_id, $new_email, $cle_secrete) {
    if ($cle_secrete == "fjo48éR;d!xSdjfUIT3d8GsZ") {
        $url = 'https://beds24.com/api/v2/bookings';

        // Structure correcte des données
        $data = [
            [
                'id' => $booking_id,
                'email' => $new_email
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'token: ' . get_option('beds24_access_token')
        ];

        // Envoi de la requête POST pour mettre à jour l'email
        $response = sendPostRequest($url, $data, $headers);

        if ($response === false) {
            $message = "Échec de la mise à jour de l'email en raison d'un problème de token.";
        } else {
            // Décoder et afficher la réponse complète
            $decoded_response = json_decode($response, true);

            // Vérifiez que le succès est explicite et que l'email est effectivement mis à jour
            if (isset($decoded_response[0]['success']) && $decoded_response[0]['success'] == 1) {
                $message = "L'email a été mis à jour avec succès.";
            } else {
                $message = "Erreur lors de la mise à jour de l'email. Réponse de l'API.";
            }
        }

        return '<p>' . esc_html($message) . '</p>';
    } else {
        echo "Accès non autorisé";
    }
}

// Shortcode pour l’appel, utilisant les paramètres GET si présents
add_shortcode('update_booking_email', function ($atts) {
    // Définit les attributs par défaut et récupère les paramètres GET
    $default_atts = ['bookID' => '', 'email' => '', 'cle_secrete' => ''];
    $atts = get_shortcode_atts_with_get_params($default_atts);

    if (!$atts['bookID'] || !$atts['email'] || !$atts['cle_secrete']) {
        return '<p>Erreur</p>';
    }

    return beds24_update_booking_email($atts['bookID'], $atts['email'], $atts['cle_secrete']);
});



/* Nouvelle fonctionnalité : On récupère via les paramètres envoyés par make et on post dans l'API de Beds24 */




// Callback pour la tâche CRON
function beds24_cron_update_properties_callback() {
    error_log("L'action CRON beds24_cron_update_properties_callback a été déclenchée.");
    $url = 'https://beds24.com/api/v2/properties';

    $params = [
        'includeLanguages' => 'all',
        'includeTexts' => 'all',
        'includePictures' => true,
        'includeOffers' => false,
        'includePriceRules' => false,
        'includeUpsellItems' => true,
        'includeAllRooms' => true
    ];
    
    $headers = [
        'Content-Type: application/json',
        'token: ' . get_option('beds24_access_token')
    ];
    $response = sendGetRequest($url, $params, $headers);

    // Stockage des données dans un fichier
    $filePath = plugin_dir_path(__FILE__) . 'json/properties.json';

    // Écriture du JSON dans le fichier
    if (file_put_contents($filePath, $response) !== false) {
        error_log("JSON a été écrit dans le fichier avec succès.");
    } else {
        error_log("Une erreur s'est produite lors de l'écriture du JSON dans le fichier.");
    }
    
    wp_die(); // Toujours terminer la fonction avec wp_die() lorsque vous utilisez wp_ajax_*
}

add_action('wp_ajax_beds24_cron_update_properties', 'beds24_cron_update_properties_callback');
add_action('wp_ajax_nopriv_beds24_cron_update_properties', 'beds24_cron_update_properties_callback');

// Planifier la tâche CRON
if (!wp_next_scheduled('beds24_cron_hook')) {
    wp_schedule_event(time(), 'hourly', 'beds24_cron_hook');
}

// Hook pour la tâche CRON
add_action('beds24_cron_hook', function() {
    wp_remote_get(home_url('/?action=beds24_cron_update_properties'));
});

/* Scripts pour afficher le calendrier sur la page single-logements.php */

function get_bookings_for_room($room_id) {
    $url = 'https://beds24.com/api/v2/bookings';
    $params = [
        'roomId' => $room_id,
        'status' => 'confirmed'
    ];
    $headers = [
        'Content-Type: application/json',
        'token: ' . get_option('beds24_access_token')
    ];

    $response = sendGetRequest($url, $params, $headers);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] === true) {
            return $data['data'];
        }
    }
    return false;
}

function get_availability_for_room($room_id) {
    $url = 'https://beds24.com/api/v2/inventory/rooms/availability';
    $params = [
        'roomId' => $room_id
    ];
    $headers = [
        'Content-Type: application/json',
        'token: ' . get_option('beds24_access_token')
    ];

    $response = sendGetRequest($url, $params, $headers);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] === true) {
            error_log('Availability: ' . print_r($data['data'][0]['availability'], true));
            return $data['data'][0]['availability'];
        }
    }
    error_log('No availability found');
    return false;
}

function display_availability_calendar($atts) {
    $atts = shortcode_atts(['room_id' => ''], $atts, 'availability_calendar');
    $room_id = intval($atts['room_id']);
    if (!$room_id) {
        return '<p>Invalid room ID.</p>';
    }

    $bookings = get_bookings_for_room($room_id);
    $availability = get_availability_for_room($room_id);
    
    if (!$bookings && !$availability) {
        return '<p>No data found for this room.</p>';
    }

    ob_start();
    ?>
    <div id="calendar"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var events = [];

            <?php if ($bookings) : ?>
                <?php foreach ($bookings as $booking) : ?>
                events.push({
                    title: 'Réservé',
                    start: '<?php echo $booking["arrival"]; ?>',
                    end: '<?php echo date('Y-m-d', strtotime($booking["departure"] . ' +1 day')); ?>', // FullCalendar exclusive end date
                    color: '#dd2e25'
                });
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($availability) : ?>
                <?php foreach ($availability as $date => $available) : ?>
                    <?php if ($available) : ?>
                        // Vérifier s'il y a une réservation à cette date
                        var isBooked = false;
                        <?php if ($bookings) : ?>
                            <?php foreach ($bookings as $booking) : ?>
                                if (new Date('<?php echo $date; ?>') >= new Date('<?php echo $booking["arrival"]; ?>') && new Date('<?php echo $date; ?>') < new Date('<?php echo $booking["departure"]; ?>')) {
                                    isBooked = true;
                                }
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        if (!isBooked) {
                            events.push({
                                title: 'Disponible',
                                start: '<?php echo $date; ?>',
                                end: '<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>',
                                color: '#86c431'
                            });
                        }
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr', // Initialisation en français
                events: events
            });
            calendar.render();
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('availability_calendar', 'display_availability_calendar');





/* Scripts pour afficher le calendrier sur la page single-logements.php */


/* Script appelé par la page recherche.php qui permet de lister les offres disponibles en fonction des critères de recherche (retourne un tableau d'id de logements) */

function beds24_get_offers($arrival, $departure, $num_adults, $num_enfants = 0) {
    $url = 'https://beds24.com/api/v2/inventory/rooms/offers';
    $params = [
        'arrival' => $arrival,
        'departure' => $departure,
        'numAdults' => intval($num_adults),
        'numChildren' => intval($num_enfants)
    ];
    $headers = [
        'Content-Type: application/json',
        'token: ' . get_option('beds24_access_token')
    ];

    $room_ids_with_units_and_price = array();
    $page = 1;

    do {
        $params['page'] = $page;
        $response = sendGetRequest($url, $params, $headers);

        if ($response) {
            $data = json_decode($response, true);

            if (isset($data['success']) && $data['success'] === true && isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $offer) {
                    if (isset($offer['offers']) && is_array($offer['offers'])) {
                        foreach ($offer['offers'] as $offer_details) {
                            if (isset($offer_details['unitsAvailable']) && $offer_details['unitsAvailable'] == 1) {
                                $room_ids_with_units_and_price[$offer['roomId']] = $offer_details['price'];
                            }
                        }
                    }
                }

                $page++;
            } else {
                break;
            }
        } else {
            break;
        }
    } while (isset($data['pages']['nextPageExists']) && $data['pages']['nextPageExists']);

    return $room_ids_with_units_and_price;
}



/* Script appelé par la page recherche.php qui permet de lister les offres disponibles en fonction des critères de recherche (retourne un tableau d'id de logements) */



/* Requête AJAX qui permet de renvoyer les dispos et prix depuis la page single-logement */
// Enqueue jQuery et le script custom pour AJAX

function enqueue_beds24_scripts() {
    if (is_singular('logements')) {
        wp_enqueue_script('beds24-script', plugins_url('/js/scripts.js', __FILE__), array('jquery'), null, true);

        // Localize script pour passer l'URL personnalisée, le token et le nonce à JavaScript
        wp_localize_script('beds24-script', 'beds24_params', array(
            'custom_ajax_url' => home_url('/custom-ajax-handler'),
            'nonce' => wp_create_nonce('beds24_nonce') // Crée un nonce pour la requête AJAX
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_beds24_scripts');

function custom_ajax_handler() {
    // Check nonce for security
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'beds24_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
        die();
    }

    // Retrieve parameters from the request
    $roomId = isset($_GET['roomId']) ? sanitize_text_field($_GET['roomId']) : '';
    $arrival = isset($_GET['arrival']) ? sanitize_text_field($_GET['arrival']) : '';
    $departure = isset($_GET['departure']) ? sanitize_text_field($_GET['departure']) : '';
    $numAdults = isset($_GET['numAdults']) ? intval($_GET['numAdults']) : 0;
    $numChildren = isset($_GET['numChildren']) ? intval($_GET['numChildren']) : 0;

    if (empty($roomId) || empty($arrival) || empty($departure) || empty($numAdults)) {
        wp_send_json_error('Paramètres manquants ou incorrects');
        wp_die();
    }

    $url = 'https://beds24.com/api/v2/inventory/rooms/offers';
    $params = [
        'roomId' => $roomId,
        'arrival' => $arrival,
        'departure' => $departure,
        'numAdults' => $numAdults,
        'numChildren' => $numChildren,
    ];
    $headers = [
        'Content-Type: application/json',
        'token' => get_option('beds24_access_token') // Utilisation du token stocké en option
    ];

    $response = wp_remote_get($url . '?' . http_build_query($params), ['headers' => $headers]);

    if (is_wp_error($response)) {
        wp_send_json_error('Erreur lors de la requête à l\'API Beds24.');
    } else {
        $responseBody = wp_remote_retrieve_body($response);
        $responseData = json_decode($responseBody, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            wp_send_json_success($responseData);
        } else {
            wp_send_json_error('Erreur lors du décodage JSON: ' . json_last_error_msg());
        }
    }
    wp_die();
}

add_action('wp_ajax_nopriv_custom_ajax_handler', 'custom_ajax_handler');
add_action('wp_ajax_custom_ajax_handler', 'custom_ajax_handler');

// Register a custom URL for the AJAX handler
function custom_rewrite_rule() {
    add_rewrite_rule('^custom-ajax-handler/?$', 'index.php?custom_ajax_handler=1', 'top');
}
add_action('init', 'custom_rewrite_rule');

// Add the custom query var
function custom_query_vars($vars) {
    $vars[] = 'custom_ajax_handler';
    return $vars;
}
add_filter('query_vars', 'custom_query_vars');

// Handle the custom query var
function custom_template_redirect() {
    if (get_query_var('custom_ajax_handler')) {
        custom_ajax_handler();
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'beds24_cron_update_properties') {
        beds24_cron_update_properties_callback();
        exit;
    }
}
add_action('template_redirect', 'custom_template_redirect');



/* Requête AJAX qui permet de renvoyer les dispos et prix depuis la page single-logement */

/* Fonction globale qui permet de faire une requête à l'API */

// Fonction globale qui permet de faire une requête à l'API de type GET avec gestion du token de rafraîchissement
function sendGetRequest($url, $data, $headers = []) {
    $url = $url . '?' . http_build_query($data);
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);

    // Vérifier si le token est expiré et tenter de le rafraîchir
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 401) {
        curl_close($curl);
        if (beds24_refresh_token()) {
            $headers['token'] = get_option('beds24_access_token');
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($curl);
        } else {
            return false;
        }
    }

    curl_close($curl);
    return $response;
}

// Fonction globale qui permet de faire une requête à l'API de type POST avec gestion du token de rafraîchissement
function sendPostRequest($url, $data, $headers = []) {
    // Initialiser cURL pour une requête POST
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); // Convertir les données en JSON

    $response = curl_exec($curl);

    // Vérifier si le token est expiré et tenter de le rafraîchir
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 401) {
        curl_close($curl);
        if (beds24_refresh_token()) {
            $headers['token'] = get_option('beds24_access_token');
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); // Convertir les données en JSON
            $response = curl_exec($curl);
        } else {
            return false;
        }
    }

    curl_close($curl);
    return $response;
}


// Fonction pour rafraîchir le token
function beds24_refresh_token() {
    $refreshToken = get_option('beds24_refresh_token');
    if (!$refreshToken) {
        return false;
    }

    $url = 'https://beds24.com/api/v2/authentication/token';
    $headers = [
        'Content-Type: application/json',
        'refreshToken: ' . $refreshToken
    ];

    $response = sendGetRequest($url, [], $headers);
    if ($response) {
        $responseData = json_decode($response, true);
        if (isset($responseData['token'])) {
            update_option('beds24_access_token', $responseData['token']);
            return true;
        }
    }

    return false;
}


/* Fonction globale qui permet de faire une requête à l'API */

/* Sert à l'import des équipements dans WP All Import */

add_action('pmxi_saved_post', 'update_acf_checkboxes', 10, 1);
function update_acf_checkboxes($post_id) {
    // Vérifie si c'est le bon type de publication
    if (get_post_type($post_id) == 'logements') {
        // Récupère les codes de fonction importés (remplace 'imported_feature_codes' par ta clé de méta réelle)
        $imported_feature_codes = get_post_meta($post_id, 'equipements_services', true);

        if ($imported_feature_codes) {
            // Convertit la chaîne importée en un tableau
            $features_array = explode(',', $imported_feature_codes);

            // Nettoie les éléments du tableau (supprime les espaces vides)
            $features_array = array_map('trim', $features_array);

            // Met à jour le champ ACF avec le tableau
            update_field('equipements_services', $features_array, $post_id);
        }
    }
}

/* Sert à l'import des équipements dans WP All Import */



/* Page d'options dans le BO de WordPress */

function beds24_create_settings_page() {
    add_options_page(
        'Beds24 API Settings',
        'Beds24 API',
        'manage_options',
        'beds24-api-settings',
        'beds24_render_settings_page'
    );
}
add_action('admin_menu', 'beds24_create_settings_page');

function beds24_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Beds24 API Settings</h1>
        <form method="post" action="">
        	<input type="hidden" name="generate_token" value="generate_token">
        	
            <?php
            settings_fields('beds24_settings_group');
            do_settings_sections('beds24-api-settings');
            submit_button();
            ?>
        </form>

        <form method="post" action="">
        	<!-- Bouton pour mettre à jour les propriétés -->
            <h2>Mettre à jour les propriétés</h2>
            <p>Cliquez sur le bouton ci-dessous pour mettre à jour les propriétés et stocker les données dans un fichier JSON.</p>

        	<input type="hidden" name="update_properties" value="update_properties">
            <?php submit_button(); ?>

        </form>

        <?php //  ?>

            
    </div>
    <?php
}

function beds24_register_settings() {
    register_setting('beds24_settings_group', 'beds24_access_token');
    register_setting('beds24_settings_group', 'beds24_refresh_token');
    register_setting('beds24_settings_group', 'beds24_invite_code'); // Nouveau champ pour le code invité

    add_settings_section('beds24_settings_section', 'API Settings', null, 'beds24-api-settings');
    
    add_settings_field('beds24_access_token', 'Access Token', 'beds24_access_token_callback', 'beds24-api-settings', 'beds24_settings_section');
    add_settings_field('beds24_refresh_token', 'Refresh Token', 'beds24_refresh_token_callback', 'beds24-api-settings', 'beds24_settings_section');
    add_settings_field('beds24_invite_code', 'Invite Code', 'beds24_invite_code_callback', 'beds24-api-settings', 'beds24_settings_section'); // Nouveau champ pour le code invité
}
add_action('admin_init', 'beds24_register_settings');

function beds24_access_token_callback() {
    $value = get_option('beds24_access_token', '');
    echo '<input type="text" id="beds24_access_token" name="beds24_access_token" value="' . esc_attr($value) . '" disabled />';
}

function beds24_refresh_token_callback() {
    $value = get_option('beds24_refresh_token', '');
    echo '<input type="text" id="beds24_refresh_token" name="beds24_refresh_token" value="' . esc_attr($value) . '" disabled />';
}

function beds24_invite_code_callback() {
    $value = get_option('beds24_invite_code', '');
    echo '<input type="text" id="beds24_invite_code" name="beds24_invite_code" value="' . esc_attr($value) . '" />';
}

/* Page d'options dans le BO de WordPress */


?>