<?php
/*
Plugin Name: My Loyalty Coupons
Description: Sistem simplu de fidelizare cu puncte și cupoane.
Version: 1.1
Author: ChatGPT
*/

if (!defined('ABSPATH')) exit;

// Încarcă CSS-ul pluginului pentru toată lumea
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('mlc-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
});


// Înregistrează CPT "Cupoane"
function mlc_register_cpt_cupoane() {
    $labels = [
        'name' => 'Cupoane',
        'singular_name' => 'Cupon',
        'menu_name' => 'Cupoane',
        'add_new_item' => 'Adaugă Cupon Nou',
        'edit_item' => 'Editează Cupon',
        'new_item' => 'Cupon Nou',
        'view_item' => 'Vezi Cupon',
        'search_items' => 'Caută Cupoane',
        'not_found' => 'Nu s-au găsit cupoane',
        'not_found_in_trash' => 'Nu s-au găsit cupoane în coș',
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_position' => 25,
        'show_in_rest' => true,
    ];
    register_post_type('mlc_cupon', $args);
}
add_action('init', 'mlc_register_cpt_cupoane');

function mlc_my_coupons_page_shortcode() {
    $user_id = get_current_user_id();
    $is_logged_in = is_user_logged_in();
    $puncte = $is_logged_in ? (int)get_user_meta($user_id, 'puncte_fidelitate', true) : 0;
    $deblocate = $is_logged_in ? get_user_meta($user_id, 'mlc_cupoane_deblocate', true) : [];
    if (!is_array($deblocate)) $deblocate = [];

    $cup_list = get_posts(['post_type' => 'mlc_cupon', 'posts_per_page' => -1]);

    $output = '<div class="mlc-wrapper">';
    $output .= $is_logged_in
        ? "<h3 class='mlc-puncte-titlu'>Punctele tale: <strong>$puncte</strong></h3>"
        : "<h3 class='mlc-puncte-titlu'>Vezi ce cupoane poți debloca după autentificare:</h3>";

    $output .= '<div class="mlc-cupon-grid">';
    foreach ($cup_list as $cupon) {
        $id = $cupon->ID;
        $titlu = get_the_title($id);
        $cod = get_field('cod_cupon', $id);
        $necesare = get_field('puncte_necesare', $id);
        $descriere = get_field('descriere_scurta', $id);

        $img = '';
        if (has_post_thumbnail($id)) {
            $img_url = get_the_post_thumbnail_url($id, 'medium');
            $img = "<div class='mlc-cupon-img'><img src='$img_url' alt='" . esc_attr($titlu) . "'></div>";
        }

        $btn = '';

        if (!$is_logged_in) {
            $btn = "<p class='mlc-neajuns'>🔒 Loghează-te pentru a debloca. Îți trebuie <strong>$necesare</strong> puncte.</p>";
        } elseif (in_array($id, $deblocate)) {
            $btn = "<p class='mlc-deblocat'><strong>✅ Deblocat</strong><br>Cod: <code>$cod</code></p>";
        } elseif ($puncte >= $necesare) {
            $btn = "<button class='mlc-deblocheaza-cupon' data-id='$id'>Deblochează (-$necesare puncte)</button>";
        } else {
            $btn = "<p class='mlc-neajuns'>⛔ Îți trebuie <strong>$necesare</strong> puncte</p>";
        }

        $output .= "<div class='mlc-cupon-box'>$img<h4>$titlu</h4><p>$descriere</p>$btn</div>";
    }

    $output .= '</div></div>';
    return $output;
}



add_shortcode('my_coupons_page', 'mlc_my_coupons_page_shortcode');

// AJAX: Deblochează cupon
add_action('wp_ajax_mlc_deblocheaza_cupon', function() {
    $user_id = get_current_user_id();
    $id = (int)$_POST['id'];
    $puncte_user = (int)get_user_meta($user_id, 'puncte_fidelitate', true);
    $necesare = (int)get_field('puncte_necesare', $id);

    if ($puncte_user >= $necesare) {
        $puncte_user -= $necesare;
        update_user_meta($user_id, 'puncte_fidelitate', $puncte_user);

        $lista = get_user_meta($user_id, 'mlc_cupoane_deblocate', true) ?: [];
        if (!in_array($id, $lista)) {
            $lista[] = $id;
            update_user_meta($user_id, 'mlc_cupoane_deblocate', $lista);
        }
    }
    wp_die();
});

// JS pentru deblocare
add_action('wp_footer', function() {
    if (!is_user_logged_in()) return;
    ?>
    <script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('mlc-deblocheaza-cupon')) {
            const id = e.target.dataset.id;
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=mlc_deblocheaza_cupon&id=' + id
            }).then(() => location.reload());
        }
    });
    </script>
    <?php
});
