<?php
	$room_ids_array = [];

	if (!empty($_GET)) {
		// Appel de la fonction pour obtenir les offres
		$room_ids_with_units_and_price = beds24_get_offers($arrival, $departure, $numAdults, $numEnfants);
		$room_ids_array = array_keys($room_ids_with_units_and_price);

		/*
		var_dump($room_ids_array);

		echo "nb voyageurs : " . $numAdults + $numEnfants;
		*/

	}

	// Déterminez si les paramètres GET sont présents
    $get_params_present = !empty($_GET);


    // Construire la requête normale
    $args = array(
        'numberposts' => -1,
        'posts_per_page' => 999,
        'post_type' => 'logements',
        'meta_key' => 'control_priority', // Ordre indiqué dans Beds 24
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'publie_sur_le_site',
                'value' => '1', // True est stocké comme '1' dans ACF
                'compare' => '='
            )
        )
    );


	$meta_query_options = array('relation' => 'AND');



    if ($get_params_present) {


		if (!empty($room_ids_array)) {
		    $args['meta_query'][] = array(
		        'key' => 'room_id',
		        'value' => $room_ids_array,
		        'compare' => 'IN'
		    );
		} else {
			if (!empty($arrival) && !empty($departure)) {
			    // Si le tableau est vide, ajoutez une condition impossible
			    $args['meta_query'][] = array(
			        'key' => 'room_id',
			        'value' => 'no_value_should_match_this',
			        'compare' => '='
			    );
			}
		}

        $args['meta_query'][] = array(
            'key' => 'nb_voyageurs',
            'value' => $numAdults + $numEnfants,
            'type' => 'NUMERIC',
            'compare' => '>='
        );

        if (!empty($options)) {

            foreach ($options as $option) {
                $meta_query_options[] = array(
                    'key' => 'equipements_filtres',
                    'value' => $option,
                    'compare' => 'LIKE'
                );
            }

        }

        if (!empty($quartier)) {
		    $args['meta_query'][] = array(
		        'key' => 'quartier',
		        'value' => $quartier,
		        'compare' => 'IN'
		    );
		}


        if (!empty($nb_chambres)) {
            $args['meta_query'][] = array(
                'key' => 'nb_chambres',
                'value' => $nb_chambres,
                'compare' => 'IN'
            );
        }

        if ($nb_sdb) {
            $args['meta_query'][] = array(
                'key' => 'nb_sdb',
                'value' => $nb_sdb,
                'compare' => '>='
            );
        }

	    if (!empty($residence)) {
	        $args['meta_query'][] = array(
	            'key' => 'residence',
	            'value' => $residence,
	            'compare' => 'IN'
	        );
	    }
    } else {
    	if (isset($is_chalet) && $is_chalet == 1) {
			$args['meta_query'][] = array(
	            'key' => 'residence',
	            'value' => 'chalet',
	            'compare' => '='
	        );
		} elseif(isset($is_chalet) && $is_chalet == 0) {
			$args['meta_query'][] = array(
	            'key' => 'residence',
	            'value' => array('chalet'),
	            'compare' => 'NOT IN'
	        );
		}
    }

	$args['meta_query'][] = $meta_query_options;

    $the_query = new WP_Query($args);

	if ( $the_query->have_posts() ) :
	    $nb_resultats = $the_query->found_posts;
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$nom = get_the_title();
			$photo = get_the_post_thumbnail_url('', 'medium-large');
			$photo_id = get_post_thumbnail_id( $post->ID );
			$photo_alt = get_post_meta($photo_id, '_wp_attachment_image_alt', true);
			$quartier = get_field('quartier');
			$ville = get_field('ville');
			$nb_voyageurs = get_field('nb_voyageurs');
			$nb_max_adultes = get_field('nb_max_adultes');
			$superficie = get_field('superficie');
			$frais_service = get_field('frais_service');
			$frais_menage = get_field('frais_menage');
			$frais_linge = get_field('frais_linge');
			$taxe_sejour = get_field('taxe_sejour');
			$nb_jours_min = get_field('nb_jours_min');
			$regles = get_field('regles');
			$reglement_etablissement = get_field('reglement_etablissement');
			$conditions_annulation = get_field('conditions_annulation');
			$nb_couchages = get_field('nb_couchages');
			$nb_chambres = get_field('nb_chambres');
			$nb_sdb = get_field('nb_sdb');
			$prix_indicatif = intval(get_field('prix_indicatif'));
			if (isset($prix_indicatif)) {
				$prix_indicatif_total = floatval($prix_indicatif) * 7 + floatval($frais_service) + floatval($frais_menage) + floatval($frais_linge);

			}
			$descriptif = get_field('descriptif');
			$room_id = get_field('room_id');
			if (isset($room_ids_with_units_and_price[$room_id])) {
				$prix_reservation = $room_ids_with_units_and_price[$room_id];
			}
			if (isset($prix_reservation) && isset($numAdults) && $numNights) {
				$prix_total = $prix_reservation + $frais_service + $frais_menage + $frais_linge + $taxe_sejour * $numAdults * $numNights;
				if (floor($prix_total) != $prix_total) {
				    $prix_total = number_format($prix_total, 2, ',', '');
				} else {
				    $prix_total = number_format($prix_total, 0, '', '');
				}
			}

	?>



	<div class="vignette-logement-ctn">
		<div class="vignette-logement">
			<a href="<?php echo the_permalink() . '?arrivee=' . esc_attr($arrival) . '&depart=' . esc_attr($departure) . '&adultes=' . esc_attr($numAdults) . '&enfants=' . esc_attr($numEnfants); ?>">
				<div class="photo">
					<figure>
						<img src="<?php echo $photo ?>" alt="<?php echo $photo_alt; ?>">
					</figure>
				</div>
				<div class="description">
					<h3><?php echo $nom; ?></h3>
					<div class="localisation"><?php echo $ville . ' - ' . $quartier; ?></div>
					<?php include(get_stylesheet_directory() ."/parts/plus-d-infos.php"); ?>
					<hr>
					<?php if (!empty($prix_reservation)) {
						echo '<div class="prix">Prix du séjour : <span>' . $prix_total . ' €</span></div>';
					} else {
						echo '<div class="prix">À partir de <span>' . $prix_indicatif_total . ' €</span>/semaine</div>';

					}; ?>
				</div>
			</a>
		</div>
	</div>
		<?php
				endwhile;
				wp_reset_postdata();
			else :
				echo "<span class=\"no-results\">Aucun logement correspondant à vos critères de recherche n'a été trouvé</span>";
			endif;
		?>
	</div> <?php //</div class="logements-ctn"> ?>