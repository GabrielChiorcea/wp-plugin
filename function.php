

add_action('user_register', 'mlc_seteaza_rang_initial_bronze');
function mlc_seteaza_rang_initial_bronze($user_id) {
    // Doar dacă nu are deja un rang setat
    if (!get_user_meta($user_id, 'mlc_rank', true)) {
        update_user_meta($user_id, 'mlc_rank', 'Bronze');
    }
}

add_action('woocommerce_account_dashboard', 'mlc_afiseaza_rang_user', 1);
function mlc_afiseaza_rang_user() {
    $user_id = get_current_user_id();
    if (!$user_id) return;

    $rang = get_user_meta($user_id, 'mlc_rank', true);
    if (!$rang) $rang = 'Bronze'; // fallback default

    // Poți schimba culoarea în funcție de rang mai jos
    $culoare = [
        'Bronze' => '#cd7f32',
        'Silver' => '#c0c0c0',
        'Gold'   => '#ffd700',
        'Platinum' => '#e5e4e2',
    ][$rang] ?? '#999';

    echo '<div class="mlc-rang-box">';
    echo '<strong>Rangul tău de fidelitate:</strong> ';
    echo '<span class="mlc-rang-label" style="background-color:' . esc_attr($culoare) . ';">' . esc_html($rang) . '</span>';
    echo '</div>';
}


add_action('woocommerce_account_dashboard', 'mlc_afiseaza_puncte_fidelitate_in_myaccount', 2);
add_action('woocommerce_account_dashboard', 'mlc_afiseaza_cupoane_deblocate_in_myaccount', 3);


function mlc_afiseaza_cupoane_deblocate_in_myaccount() {
    $user_id = get_current_user_id();
    if (!$user_id) return;

    $deblocate = get_user_meta($user_id, 'mlc_cupoane_deblocate', true);
    if (empty($deblocate) || !is_array($deblocate)) {
        echo '<p>Nu ai cupoane încă.</p>';
        return;
    }

    echo '<h2>Cupoanele tale deblocate</h2>';

    echo "<style>
        .mlc-cupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .mlc-cupon-box {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            text-align: center;
        }
        .mlc-cupon-box img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .mlc-cupon-box code {
            background-color: #eee;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin-top: 8px;
        }
    </style>";

    echo '<div class="mlc-cupon-grid">';
    foreach ($deblocate as $cupon_id) {
        $titlu = get_the_title($cupon_id);
        $descriere = get_field('descriere_scurta', $cupon_id);
        $cod = get_field('cod_cupon', $cupon_id);
        $img = get_the_post_thumbnail_url($cupon_id, 'medium');

        echo '<div class="mlc-cupon-box">';
        if ($img) echo "<img src='$img' alt='Imagine cupon'>";
        echo "<h3>$titlu</h3>";
        if ($descriere) echo "<p>$descriere</p>";
        if ($cod) echo "<code>$cod</code>";
        echo '</div>';
    }
    echo '</div>';
}


function mlc_afiseaza_puncte_fidelitate_in_myaccount() {
	$user = wp_get_current_user();
    $username = $user->user_login;
    $user_id = get_current_user_id();
    if (!$user_id) return;

    $puncte = (int)get_user_meta($user_id, 'puncte_fidelitate', true);

    echo '<div class="mlc-box-puncte" style="background:#f0f7ff; border-left:5px solid #0073aa; padding:15px; margin:20px 0; border-radius:8px;">';
    echo "<strong> Bine ai venit $username! Punctele tale de fidelitate:</strong> <span style='font-size:1.2rem; color:#0073aa;'>$puncte</span>";
    echo '</div>';
}

add_filter( 'woocommerce_account_menu_items', 'custom_remove_my_account_tabs' );
function custom_remove_my_account_tabs( $items ) {
    unset( $items['orders'] );     // Elimină comenzi
    unset( $items['downloads'] );  // Elimină descărcări
    unset( $items['edit-address'] ); // Elimină adrese (dacă vrei)
    // unset( $items['edit-account'] ); // Elimină "Account details"
    // unset( $items['customer-logout'] ); // Nu recomand :)
    return $items;
}



add_filter( 'woocommerce_account_menu_items', 'custom_rename_my_account_dashboard', 999 );
function custom_rename_my_account_dashboard( $items ) {
    if ( isset( $items['dashboard'] ) ) {
        $items['dashboard'] = 'Puncte și Cupoane';  // textul nou
    }
    return $items;
}

// add_action('woocommerce_before_shop_loop', 'custom_search_and_category_buttons', 5);

// function custom_search_and_category_buttons() {
//     if (!is_shop() && !is_product_category()) return;

//     // SEARCH BAR WooCommerce
//     echo '<div class="shop-search-bar">';
//     echo '<form role="search" method="get" class="woocommerce-product-search" action="' . esc_url(home_url('/')) . '">
//         <label class="screen-reader-text" for="woocommerce-product-search-field">Caută produse:</label>
//         <input type="search" id="woocommerce-product-search-field" class="search-field" placeholder="Caută produse…" value="' . get_search_query() . '" name="s" />
//         <button type="submit" value="Search">🔍</button>
//         <input type="hidden" name="post_type" value="product" />
//     </form>';
//     echo '</div>';

//     // CATEGORII BUTOANE
//     $terms = get_terms([
//         'taxonomy' => 'product_cat',
//         'hide_empty' => true,
//         'parent' => 0
//     ]);

//     if (!empty($terms) && !is_wp_error($terms)) {
//         echo '<div class="shop-category-buttons">';
//         foreach ($terms as $term) {
//             $term_link = get_term_link($term);
//             $active_class = (is_product_category($term->slug)) ? ' active-cat' : '';
//             echo '<a class="cat-button' . $active_class . '" href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a>';
//         }
//         echo '</div>';
//     }
// }
// 
// 
// Afișează căutarea + butoanele în shop și categorie
add_action('woocommerce_before_shop_loop', 'custom_search_and_category_buttons', 5);

function custom_search_and_category_buttons() {
    if (!is_shop() && !is_product_category()) return;

    echo '<div class="shop-live-search">';
    echo '<input type="text" id="woo-live-search-input" placeholder="Caută produse...">';
    echo '<ul id="woo-live-search-results"></ul>';
    echo '</div>';

    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => 0
    ]);

    if (!empty($terms) && !is_wp_error($terms)) {
        echo '<div class="shop-category-buttons">';
        foreach ($terms as $term) {
            $term_link = get_term_link($term);
            $active_class = (is_product_category($term->slug)) ? ' active-cat' : '';
            echo '<a class="cat-button' . $active_class . '" href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a>';
        }
        echo '</div>';
    }
}

add_action('wp_ajax_woo_custom_live_search', 'woo_custom_live_search');
add_action('wp_ajax_nopriv_woo_custom_live_search', 'woo_custom_live_search');

function woo_custom_live_search() {
    $term = sanitize_text_field($_GET['term']);

    $args = [
        'post_type' => 'product',
        'posts_per_page' => 8,
        's' => $term,
        'post_status' => 'publish',
    ];

    $query = new WP_Query($args);
    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            $results[] = [
                'label' => get_the_title(),
                'url' => get_permalink(),
                'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                'price' => strip_tags(wc_price(wc_get_price_to_display($product)))
            ];
        }
    }

    wp_reset_postdata();
    wp_send_json($results);
}



// 1. Adaugă tab-ul „Puncte” în meniul My Account
add_filter( 'woocommerce_account_menu_items', 'mlc_adauga_tab_puncte', 40 );
function mlc_adauga_tab_puncte( $items ) {
    $items['puncte'] = 'Despre puncte si rang';
    return $items;
}

// 2. Înregistrează endpoint-ul pentru tab-ul „Puncte”
add_action( 'init', 'mlc_adauga_endpoint_puncte' );
function mlc_adauga_endpoint_puncte() {
    add_rewrite_endpoint( 'puncte', EP_ROOT | EP_PAGES );
}

// 3. Afișează conținutul pentru tab-ul „Puncte”
add_action( 'woocommerce_account_puncte_endpoint', 'mlc_afiseaza_continut_puncte' );
function mlc_afiseaza_continut_puncte() {
    ?>
    <h2>Despre punctele tale de fidelitate</h2>
    <p><strong>Cum se acordă punctele:</strong></p>
    <ul>
        <li>Primești 1 punct pentru fiecare 10 RON cheltuiți în magazin.</li>
        <li>Punctele se acumulează automat după finalizarea comenzii.</li>
        <li>Poți primi puncte bonus la promoții speciale.</li>
    </ul>

    <p><strong>Cum se folosesc punctele:</strong></p>
    <ul>
        <li>Punctele pot fi transformate în cupoane de reducere.</li>
        <li>Poți vedea toate cupoanele disponibile în tab-ul „Cupoane” din contul tău.</li>
        <li>Folosirea cupoanelor este simplă și rapidă la checkout.</li>
    </ul>

    <p>Dacă ai întrebări despre sistemul de puncte, te rugăm să ne contactezi!</p>
    <?php
}

// 4. (Opțional) Schimbă titlul paginii pentru tab-ul „Puncte”
add_filter( 'the_title', 'mlc_schimba_titlu_puncte', 10, 2 );
function mlc_schimba_titlu_puncte( $title, $id ) {
    if ( is_wc_endpoint_url( 'puncte' ) && is_account_page() && in_the_loop() && get_the_ID() === $id ) {
        return 'Puncte de fidelitate';
    }
    return $title;
}



