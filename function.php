# Asta a fost adaugata in function php ca sa apara cuponul la user in my account 


```php

add_action('woocommerce_account_dashboard', 'mlc_afiseaza_cupoane_deblocate_in_myaccount');

function mlc_afiseaza_cupoane_deblocate_in_myaccount() {
    $user_id = get_current_user_id();
    if (!$user_id) return;

    $deblocate = get_user_meta($user_id, 'mlc_cupoane_deblocate', true);
    if (empty($deblocate) || !is_array($deblocate)) {
        echo '<p>Nu ai cupoane deblocate încă.</p>';
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

```

//asta ca sa editesz my account 
add_filter( 'woocommerce_account_menu_items', 'custom_remove_my_account_tabs' );
function custom_remove_my_account_tabs( $items ) {
    unset( $items['orders'] );     // Elimină comenzi
    unset( $items['downloads'] );  // Elimină descărcări
    unset( $items['edit-address'] ); // Elimină adrese (dacă vrei)
    // unset( $items['edit-account'] ); // Elimină "Account details"
    // unset( $items['customer-logout'] ); // Nu recomand :)
    return $items;
}
