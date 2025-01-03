<?php
/**
 * Homency
 * @version 1.0
 Template Name: Page réserver
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
nectar_page_header( $post->ID );
$nectar_fp_options = nectar_get_full_page_options();

?>
<div class="container-wrap">
	<div class="<?php if ( $nectar_fp_options['page_full_screen_rows'] !== 'on' ) { echo 'container'; } ?> main-content custom-post-listes">
		<div class="row">
			<main>
					<?php
					nectar_hook_before_content();
					?>

					<?php if(have_posts()) : ?>
						<?php while(have_posts()) : the_post();
								$nom = get_the_title();
								$galerie = display_logements_gallery();
								$photo_id = get_post_thumbnail_id( $post->ID );
								$photo_alt = get_post_meta($photo_id, '_wp_attachment_image_alt', true);	
								$quartier = get_field('quartier');
								$ville = get_field('ville');
								$nb_voyageurs = get_field('nb_voyageurs');
								if (get_field('nb_max_adultes')!= "") {
									$nb_max_adultes = get_field('nb_max_adultes');
								} else {
									$nb_max_adultes = $nb_voyageurs;
								}
								$superficie = get_field('superficie');
								$frais_service = get_field('frais_service');
								$frais_menage = get_field('frais_menage');
								$frais_linge = get_field('frais_linge');
								$taxe_sejour = get_field('taxe_sejour');
								$nb_jours_min = get_field('nb_jours_min');
								$regles = get_field('regles');
								$reglement_etablissement = get_field('reglement_etablissement');
								$conditions_annulation = get_field('conditions_annulation');
								$room_id = get_field('room_id');
								$prop_id = get_field('prop_id');
								$nb_chambres = get_field('nb_chambres');
								$nb_sdb = get_field('nb_sdb');
								$prix = get_field('prix');
								$descriptif = get_field('descriptif');
								$points_forts = get_field('points_forts');
								$agencement_rdc = get_field('agencement_rdc');
								$agencement_r_1 = get_field('agencement_r_1');
								$agencement_r_2 = get_field('agencement_r_2');
								$agencement_piece_de_vie = get_field('agencement_piece_de_vie');		
								$agencement_chambres = get_field('agencement_chambres');
								$agencement_salles_de_bains = get_field('agencement_salles_de_bains');
								$equipements_generaux = get_field('equipements_generaux');
								$equipements_electromenager = get_field('equipements_electromenager');
								$equipements_multimedia = get_field('equipements_multimedia');
								$services_accueil = get_field('services_accueil');
								$services_menage_et_linge = get_field('services_menage_et_linge');
								$services_en_option = get_field('services_en_option');
								$services_homency = get_field('services_homency');
								$regles_de_vie = get_field('regles_de_vie');
								$residence = get_field('residence');
								$gps_longitude = get_field('gps_longitude');
								$gps_latitude = get_field('gps_latitude');
								$texte_localisation = get_field('texte_localisation');
								$caution = get_field('caution');



						?>

						<div class="info-logement">
							<h1><?php echo $nom; ?></h1>
							<div class="localisation"><?php echo $ville . ' - ' . $quartier; ?></div>
							<div id="photos">
								<div class="galerie">
								    <?php
								    $i = 0;
								    foreach ($galerie as $photo) { ?>
								        <figure data-id="<?php echo $i; ?>" <?php if ($i == 0) { echo 'class="on"';} else { echo 'class="off"';}; ?>>
								            <a href="<?php echo esc_url($photo['fullscreen_url']); ?>" data-fancybox="gallery" data-caption="<?php echo $photo['caption']; ?>">
								            	<img <?php if ($i != 0 && $i != 1 && $i != count($galerie) - 1) { echo "data-"; } ?>src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>">
								            </a>
								        </figure>

								    <?php 
								    	$i++;
									} ?>
								    <div class="nav">
								    	<div class="back"></div>
								    	<div class="next"></div>
								    </div>
								</div>
							</div>
							<?php include(get_stylesheet_directory() ."/parts/plus-d-infos.php"); ?>
							<section class="descriptif">
								<h3>Descriptif</h3>
								<div class="descriptif-content">
									<?php echo $descriptif; ?>
									<h3>Ses points forts</h3>
									<?php echo $points_forts; ?>
								</div>
							</section>
							<section class="services">
								<h3>Services</h3>
								<div class="services-content">

									<?php
									if (!empty($services_accueil)) {
										echo '<h4 class="services-accueil">Accueil</h4>';
										echo '<ul>';
										foreach($services_accueil as $service_accueil) {
											echo '<li>' . $service_accueil . '</li>';
										}
										echo '</ul>';
									}
									
									if (!empty($services_menage_et_linge)) {
										echo '<h4 class="services-menage-et-linge">Ménage et linge</h4>';
										echo '<ul>';
										foreach($services_menage_et_linge as $service_menage_et_linge) {
											echo '<li>' . $service_menage_et_linge . '</li>';
										}
										echo '</ul>';
									}
									
									if (!empty($services_en_option)) {
										echo '<h4 class="services-en-option">En option</h4>';
										echo '<ul>';
										foreach($services_en_option as $service_en_option) {
											echo '<li>' . $service_en_option . '</li>';
										}
										echo '</ul>';
									}
									?>
									
								</div>
							</section>
							<section class="equipements">
								<h3>Équipements</h3>
								<div class="equipements-content">

									<?php
									if (!empty($equipements_generaux)) {
										echo '<h4 class="equipements-generaux">Généraux</h4>';
										echo $equipements_generaux;
									}
									
									if (!empty($equipements_electromenager)) {
										echo '<h4 class="equipements-electromenager">Électroménager</h4>';
										echo '<ul class="grid-style">';
										foreach($equipements_electromenager as $equipement_electromenager) {
											echo '<li>' . $equipement_electromenager . '</li>';
										}
										echo '</ul>';
									}
									
									if (!empty($equipements_multimedia)) {
										echo '<h4 class="equipements-multimedia">Multimédia</h4>';
										echo '<ul class="grid-style">';
										foreach($equipements_multimedia as $equipement_multimedia) {
											echo '<li>' . $equipement_multimedia . '</li>';
										}
										echo '</ul>';
									}
									?>
									
								</div>
							</section>
							<section class="agencement">
								<h3>Agencement</h3>
								<div class="agencement-content">
									<?php
									if (!empty($agencement_rdc)) {
										echo '<h4 class="etage">Rez-de-Chaussée</h4>';
										echo $agencement_rdc;
									}
									if (!empty($agencement_r_1)) {
										echo '<h4 class="etage">Agencement R+1</h4>';
										echo $agencement_r_1;
									}
									if (!empty($agencement_r_2)) {
										echo '<h4 class="etage">Agencement R+2</h4>';
										echo $agencement_r_2;
									}
									if (!empty($agencement_piece_de_vie)) {
										echo '<h4 class="piece-de-vie">Pièce de vie</h4>';
										echo $agencement_piece_de_vie;
									}
									if (!empty($agencement_chambres)) {
										echo '<h4 class="chambres">Chambres</h4>';
										echo $agencement_chambres;
									}
									if (!empty($agencement_salles_de_bains)) {
										echo '<h4 class="sdb">Salles de bains</h4>';
										echo $agencement_salles_de_bains;
									}
									?>
								</div>
							</section>
							<section class="disponibilites">
								<h4>Disponibilités</h4>
								<div class="calendar-ctn">
									<?php echo do_shortcode('[availability_calendar room_id="' . $room_id . '"]'); ?>
								</div>
							</section>
							<section class="distances">
								<h3>Localisation</h3>
								<div class="distances-content">

									<p>
										<?php echo $texte_localisation; ?>
									</p>

									<div id="map"></div>

									<h4>Coordonnées GPS</h4>
									<ul>
										<li>Latitude : <?php echo $gps_latitude; ?></li>
										<li>Longitude : <?php echo $gps_longitude; ?></li>
									</ul>
									
								</div>
							</section>
							<section class="regles">
								<h3>Règles de vie</h3>
								<div class="regles-content">
									<p>
										<?php echo $regles; ?>
									</p>
								</div>
							</section>
							<section class="conditions-annulation">
								<h3>Conditions d'annulation</h3>
								<div class="conditions-annulation-content">
									<p>
										<?php echo $conditions_annulation; ?>
									</p>
								</div>
							</section>
							
							<section>
								<h3>Votre destination</h3>
								<div class="row">
									<div class="col">
										<img src="/wp-content/uploads/2024/06/alpe-d-huez-768x512.webp" alt="<?php echo $ville; ?>">
									</div>
									<div class="col">
										<h4>ALPE D’HUEZ</h4>
										<p>Située au cœur des Alpes françaises, l’Alpe
										d’Huez est une station de renommée
										internationale qui séduit les visiteurs été comme
										hiver.
										Une altitude élevée vous garantit de bonnes
										conditions d’enneigement.</p>
										<a class="nectar-button large see-through accent-color" role="button" style="visibility: visible;" data-color-override="false" data-hover-color-override="false" data-hover-text-color-override="#fff" href="/stations/alpe-dhuez/" target="_blank">+ d'infos</a>
									</div>
								</div>
							</section>
						</div> <?php //.info-logement ?>

						<div class="sidebar">
							<div class="reservation">
								<form id="bookingForm">
									<section class="dates">
										<div class="row">
											<div class="col">
												<label for="arrivee">Date d'arrivée :</label>
											    <input type="date" id="arrivee" name="arrivee" min='<?php echo $date = date('Y-m-d'); ?>' max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required value="<?php echo isset($_GET['arrivee']) ? $_GET['arrivee'] : ''; ?>">
											</div>
											<div class="col">
											    <label for="depart">Date de départ :</label>
											    <input type="date" id="depart" name="depart" max='<?php echo date('Y-m-d', strtotime('+1 year')); ?>' required value="<?php echo isset($_GET['depart']) ? $_GET['depart'] : ''; ?>">
											</div>
										</div>
									</section>
									<section class="voyageurs">
										<div class="row">
											<div class="col">
											    <label for="numadult">Nombre d'adultes :</label>
											    <input type="number" id="numadult" name="numadult" min="1" max="<?php echo $nb_max_adultes; ?>" required value="<?php echo isset($_GET['adultes']) ? $_GET['adultes'] : 1; ?>">
											</div>
											<div class="col">
											    <label for="numchild">Nombre d'enfants :</label>
											    <input type="number" id="numchild" name="numchild" min="0" max="<?php echo $nb_voyageurs; ?>" value="<?php echo isset($_GET['enfants']) ? $_GET['enfants'] : ''; ?>">
											</div>
										</div>
									</section>
									<section>
										<div id="availability"></div>
									</section>
									<section>
									    <div id="availability"></div>
									</section>
									<section class="prix">
									    <div id="prixsejour">Prix : <span></span></div>
									    <div id="fraismenage">Ménage : <span><?php echo $frais_menage; ?> €</span></div>
									    <div id="fraislinge">Linge : <span><?php echo $frais_linge; ?> €</span></div>
									    <div id="fraisservice">Service : <span><?php echo $frais_service; ?> €</span></div>
									    <div id="taxesejour">Taxe de séjour : <span><?php echo number_format(floatval($taxe_sejour), 2, ',', ' '); ?> € *</span></div>
									    <div id="prixtotal" class="off">Séjour : <span></span></div>
									    <div id="prixtotalnonremboursable" class="off">Séjour non remboursable :<span></span></div>
										<span class="infos">Caution de <?php echo $caution; ?>€ par empreinte de carte bancaire 3 jours avant le début du séjour</span>
									    <span class="infos">* Les taxes de séjour sont indiquées par adulte et par nuit</span>
									</section>
									<input type="hidden" value="<?php echo $nb_voyageurs; ?>" name="nbvoyageursmax" id="nbvoyageursmax">
									<input type="hidden" value="<?php echo number_format(floatval($taxe_sejour), 2); ?>" name="taxesejourhidden" id="taxesejourhidden">
									<input type="hidden" value="<?php echo $frais_service; ?>" name="fraisservicehidden" id="fraisservicehidden">
									<input type="hidden" value="<?php echo $frais_menage; ?>" name="fraismenagehidden" id="fraismenagehidden">
									<input type="hidden" value="<?php echo $frais_linge; ?>" name="fraislingehidden" id="fraislingehidden">
									<input type="hidden" value="<?php echo $prop_id; ?>" name="propid" id="propid">
									<input type="hidden" value="<?php echo $room_id; ?>" name="roomid" id="roomid">
									<button type="submit" id="reserver" disabled>Réserver</button>
									<button type="submit" id="reserver2" disabled>Réserver (non remboursable)</button>
								</form>
								<div class="avertissement"><em>Certains logements sont ouverts du samedi au samedi et d'autres du dimanche au dimanche. N'hésitez pas à nous contacter pour toute demande spécifique.</em></div>
							</div>

						</div><?php //.sidebar ?>

						<?php endwhile;
						wp_reset_postdata();
					endif;

				nectar_hook_after_content();
				?>
			</main>
		</div>
	</div>
	<?php nectar_hook_before_container_wrap_close(); ?>
</div>
<?php get_footer(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([<?php echo $gps_latitude; ?>, <?php echo $gps_longitude; ?>], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([<?php echo $gps_latitude; ?>, <?php echo $gps_longitude; ?>]).addTo(map)
            .bindPopup('<?php echo $nom; ?>')
            .openPopup();
    });
</script>