<?php
$departure = '';
$arrival = '';
$numAdults = 1;
$numEnfants = 0;
$numNights = 0;

if (!empty($_GET)) {
    $arrival = isset($_GET['arrivee']) ? esc_attr($_GET['arrivee']) : '';
	$departure = isset($_GET['depart']) ? esc_attr($_GET['depart']) : '';
	$numAdults = isset($_GET['adultes']) ? (int)$_GET['adultes'] : 1;
	$numEnfants = isset($_GET['enfants']) ? (int)$_GET['enfants'] : 0;

	if ($arrival && $departure) {
	    $arrivalDate = new DateTime($arrival);
	    $departureDate = new DateTime($departure);
	    $interval = $departureDate->diff($arrivalDate);
	    $numNights = $interval->days;
	}

	$options = isset($_GET['options']) ? (array)$_GET['options'] : [];

	$quartier = isset($_GET['quartier']) && is_array($_GET['quartier']) ? array_filter(array_map('sanitize_text_field', $_GET['quartier']), function($value) {
	    return !empty($value) && $value !== '';
	}) : [];

	$nb_chambres = isset($_GET['nb_chambres']) && is_array($_GET['nb_chambres']) ? array_filter(array_map('intval', $_GET['nb_chambres']), function($value) {
	    return $value > 0;
	}) : [];

	$nb_sdb = isset($_GET['nb_sdb']) ? (int)$_GET['nb_sdb'] : '';
	$residence = isset($_GET['residence']) && is_array($_GET['residence']) ? array_filter(array_map('sanitize_text_field', $_GET['residence']), function($value) {
	    return !empty($value) && $value !== '';
	}) : [];

	// Appliquer stripslashes pour enlever l'échappement excessif
	$residence = array_map('stripslashes', $residence);

}

echo do_shortcode('[recherche-reservation arrivee="' . esc_attr($arrival) . '" depart="' . esc_attr($departure) . '" adultes="' . esc_attr($numAdults) . '" enfants="' . esc_attr($numEnfants) . '" nuits="' . $numNights . '"]');
?>