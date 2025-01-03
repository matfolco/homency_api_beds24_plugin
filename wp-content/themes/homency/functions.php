<?php 

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {
		
		$nectar_theme_version = nectar_get_theme_version();
		wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
        wp_enqueue_style( 'font-awesome-6', get_template_directory_uri() . '/fonts/fa-regular-400.css' );

		
    if ( is_rtl() ) {
   		wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
		}
}

// Ajouter de nouvelles tailles d'image personnalisées
add_action('after_setup_theme', 'custom_image_sizes');
function custom_image_sizes() {
    add_image_size('medium-large', 768, 0, false); // 768 pixels de largeur, hauteur automatique
    add_image_size('full-hd', 1920, 0, false); // Priorité à la largeur
    add_image_size('qhd', 2560, 0, false); // Priorité à la largeur
    add_image_size('uhd', 3840, 0, false); // Priorité à la largeur
}

// Désactiver la limitation de la taille des images
add_filter('big_image_size_threshold', '__return_false');

// Ajouter les nouvelles tailles d'image aux options de la médiathèque
add_filter('image_size_names_choose', 'custom_image_sizes_names');
function custom_image_sizes_names($sizes) {
    return array_merge($sizes, array(
        'medium-large' => __('Medium Large'),
        'full-hd' => __('Full HD'),
        'qhd' => __('QHD'),
        'uhd' => __('UHD'),
    ));
}

// Forcer la recompression des images en taille UHD (3840px de large)
add_filter('wp_generate_attachment_metadata', 'resize_and_compress_uhd_image', 10, 2);
function resize_and_compress_uhd_image($metadata, $attachment_id) {
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/' . _wp_get_attachment_relative_path($metadata['file']);
    $sizes_to_compress = ['uhd'];

    foreach ($sizes_to_compress as $size) {
        if (isset($metadata['sizes'][$size])) {
            $size_data = $metadata['sizes'][$size];
            $width = $size_data['width'];
            $height = $size_data['height'];
            $new_file = $upload_dir['path'] . '/' . $size_data['file'];

            // Redimensionner et recomprimer l'image
            $resized_image = wp_get_image_editor($file_path);
            if (!is_wp_error($resized_image)) {
                $resized_image->resize($width, $height, false);
                $resized_image->set_quality(75); // Ajustez la qualité selon vos besoins
                $resized_image->save($new_file);
                $metadata['sizes'][$size]['file'] = wp_basename($new_file);
            }
        }
    }
    return $metadata;
}




function enqueue_leaflet_assets() {

    if (is_singular('logements')) {
      // Enqueue la feuille de style Leaflet
      wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null, 'all');

      // Enqueue le script JavaScript Leaflet
      wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_leaflet_assets');



/*
function custom_mime_types( $mimes ){
        // Autoriser les fichiers svg
        $mimes['svg'] = 'image/svg+xml'; 

        // Interdire les fichiers pdf
        unset( $mimes['pdf'] );
        return $mimes;
}
add_filter('upload_mimes', 'custom_mime_types', 1, 1);

*/

/* Custom JS */


add_action('wp_enqueue_scripts', 'custom_front_script', 98);
function custom_front_script()
{
  wp_register_script('custom-front', get_stylesheet_directory_uri() . '/js/scripts.js');
  wp_enqueue_script('custom-front');
}





/* Woocommerce Repas */

// add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

/*
function alert_livraison() {
   echo "<div class=\"alert-livraison\">Nous livrons uniquement sur la station de l'Alpes d'Huez. Merci d'indiquer </div>";
}
add_action( 'woocommerce_before_checkout_billing_form', 'alert_livraison', 0);

function time_text() {
   echo "<div class=\"time-text\"><h3>Veuillez choisir un créneau de livraison</h3></div>";
}
add_action( 'woocommerce_after_checkout_billing_form', 'time_text', 0);

*/
/* Woocommerce Repas */


/* Logements */


add_action( 'init', 'create_post_type' );
function create_post_type() {
  register_post_type( 'logements',
    array(
      'labels' => array(
        'name' => __( 'Logements' ),
        'singular_name' => __( 'Logement' )
      ),
      'public' => true,
      'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
      'menu_icon' => 'dashicons-admin-multisite',
    )
  );
}

/* Custom Post type */


function logements_add_metabox() {
    add_meta_box(
        'logements_images',
        __('Logement Images', 'textdomain'),
        'logements_images_metabox_callback',
        'logements',
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'logements_add_metabox');

function logements_images_metabox_callback($post) {
    wp_nonce_field('logements_save_images', 'logements_images_nonce');
    $gallery = get_post_meta($post->ID, '_logements_gallery', true);
    ?>

    <div>
        <a href="#" class="button upload_gallery_button"><?php _e('Add Images', 'textdomain'); ?></a>
        <ul id="logements_gallery" class="gallery">
            <?php
            if ($gallery) {
                foreach ($gallery as $image_id) {
                    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                    $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    $caption = wp_get_attachment_caption($image_id);
                    if ($image_url) {  // Vérifier si l'URL de l'image est valide
                        echo '<li class="image" data-id="' . esc_attr($image_id) . '"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt_text) . '"/><a href="#" class="remove_image">Remove</a></li>';
                    }
                }
            }
            ?>
        </ul>
        <input type="hidden" id="logements_gallery_input" name="logements_gallery" value="<?php echo esc_attr(implode(',', (array) $gallery)); ?>">
    </div>

    <?php
}


function logements_save_images($post_id) {
    if (!isset($_POST['logements_images_nonce']) || !wp_verify_nonce($_POST['logements_images_nonce'], 'logements_save_images')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['logements_gallery'])) {
        $gallery = array_map('intval', explode(',', $_POST['logements_gallery']));
        update_post_meta($post_id, '_logements_gallery', $gallery);
    } else {
        delete_post_meta($post_id, '_logements_gallery');
    }
}
add_action('save_post', 'logements_save_images');

function logements_enqueue_admin_scripts() {
    global $typenow;
    if ($typenow == 'logements') {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('logements-admin-script', get_stylesheet_directory_uri() . '/js/logements-admin.js', array('jquery'), null, true);
        wp_enqueue_style('logements-admin-style', get_stylesheet_directory_uri() . '/css/logements-admin.css');
    }
}
add_action('admin_enqueue_scripts', 'logements_enqueue_admin_scripts');



function display_logements_gallery() {
    global $post;
    $gallery = get_post_meta($post->ID, '_logements_gallery', true);
    $gallery_content = array();

    /*
    // Ajout de l'image à la une (thumbnail) du post
    if (has_post_thumbnail()) {
        $thumbnail_id = get_post_thumbnail_id();
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full-hd');
        $thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        $gallery_content[] = array(
            'url' => $thumbnail_url,
            'alt' => $thumbnail_alt
        );
    }
    */


    if ($gallery) {
        foreach ($gallery as $image_id) {
            if (wp_get_attachment_image_url($image_id)) {
                $image_url = wp_get_attachment_image_url($image_id, 'full-hd');
                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                $fullscreen_url = wp_get_attachment_image_url($image_id, 'qhd');
                $caption = wp_get_attachment_caption($image_id);
                $gallery_content[] = array(
                    'url' => $image_url,
                    'alt' => $image_alt,
                    'fullscreen_url' => $fullscreen_url,
                    'caption' => $caption
                );
            }
        }
    }
    return $gallery_content;
}




/* Logements */

/* Tri des logements par control_priority dans le BO */

add_filter('manage_logements_posts_columns', 'set_custom_edit_logements_columns');
function set_custom_edit_logements_columns($columns) {
    $columns['control_priority'] = __('Priority', 'your_text_domain');
    return $columns;
}

add_action('manage_logements_posts_custom_column', 'custom_logements_column', 10, 2);
function custom_logements_column($column, $post_id) {
    switch ($column) {
        case 'control_priority':
            $control_priority = get_post_meta($post_id, 'control_priority', true);
            echo $control_priority ? $control_priority : __('N/A', 'your_text_domain');
            break;
    }
}


add_filter('manage_edit-logements_sortable_columns', 'custom_logements_sortable_columns');
function custom_logements_sortable_columns($columns) {
    $columns['control_priority'] = 'control_priority';
    return $columns;
}


add_action('pre_get_posts', 'custom_logements_orderby');
function custom_logements_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('control_priority' === $query->get('orderby') && $query->get('post_type') == 'logements') {
        $query->set('meta_key', 'control_priority');
        $query->set('orderby', 'meta_value_num');
    }
}

add_action( 'pre_get_posts', 'set_default_sort_for_logements' );
function set_default_sort_for_logements( $query ) {
    // Vérifiez si nous sommes dans l'admin, si c'est la requête principale et pour le post type 'logements'
    if ( is_admin() && $query->is_main_query() && $query->get('post_type') === 'logements' ) {
        // Vérifiez si l'ordre par défaut n'est pas déjà défini
        if ( ! $query->get('orderby') ) {
            $query->set('meta_key', 'control_priority');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
        }
    }
}



/* Tri des logements dans le BO */


/* FancyBox 4 */


/* Fancybox 4 */
add_action('wp_enqueue_scripts', 'fancybox4_enqueue_script', 99);
function fancybox4_enqueue_script()
{
    wp_deregister_script('fancyBox');
    wp_register_script('fancyBox', ("https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"), false, '', true);
    wp_enqueue_script('fancyBox');
    wp_deregister_style('fancyBox');
    wp_enqueue_style('fancyBox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css');
}


/* Fancybox 4 */

/* Chargement de full Calendar */

function enqueue_fullcalendar_scripts() {
    wp_enqueue_script('fullcalendar-core', 'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.14/index.global.min.js', [], '6.1.14', true);
    wp_enqueue_script('fullcalendar-daygrid', 'https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.14/index.global.min.js', ['fullcalendar-core'], '6.1.14', true);
    wp_enqueue_script('fullcalendar-locales', 'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.14/locales-all.global.min.js', ['fullcalendar-core'], '6.1.14', true);

}
add_action('wp_enqueue_scripts', 'enqueue_fullcalendar_scripts');




/* Chargement de full Calendar */




?>