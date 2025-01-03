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

// $is_chalet = 0; // Sert à filter en amont selon les logements (pour les pages template spécifiques recherche de chalet ou recherche de logement)
include('parts/moteur-de-recherche.php');



?>
			
<div class="container-wrap">
	<div class="<?php if ( $nectar_fp_options['page_full_screen_rows'] !== 'on' ) { echo 'container'; } ?> main-content custom-post-listes">
		<div class="<?php echo apply_filters('nectar_main_container_row_class_name', 'row'); ?>">

			<div class="avertissement">Certains logements sont ouverts du samedi au samedi et d'autres du dimanche au dimanche. N'hésitez pas à nous contacter pour toute demande spécifique.</div>

			<main class="reserver">
				<div class="logements-ctn">
					<?php
					nectar_hook_before_content();

					include('parts/resultats-de-recherche.php');


				nectar_hook_after_content();
				?>
			</main>
		</div>
	</div>
	<?php nectar_hook_before_container_wrap_close(); ?>
</div>
<?php get_footer(); ?>
