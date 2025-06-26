<?php
/*
Plugin Name: MLC Puncte Fidelitate Admin Live Search + Reset Cupoane
Description: Căutare live user, adăugare puncte fidelitate cu verificare bon și resetare cupoane într-un box separat.
Version: 1.2
Author: ChatGPT
*/

// 1. Adaugă pagina în admin
add_action('admin_menu', function() {
    add_menu_page(
        'Adaugă Puncte',
        'Adaugă Puncte',
        'manage_options',
        'adauga-puncte',
        'mlc_admin_puncte_page'
    );
});

// 2. Funcția pentru pagina de admin
function mlc_admin_puncte_page() {
    if (!current_user_can('manage_options')) wp_die('Nu ai permisiuni!');

    if (
        isset($_POST['mlc_user_id'], $_POST['mlc_suma_huf'], $_POST['mlc_numar_bon']) &&
        is_numeric($_POST['mlc_user_id']) &&
        is_numeric($_POST['mlc_suma_huf']) &&
        !empty($_POST['mlc_numar_bon'])
    ) {
        $user_id = intval($_POST['mlc_user_id']);
        $suma = floatval($_POST['mlc_suma_huf']);
        $numar_bon = sanitize_text_field($_POST['mlc_numar_bon']);
        $reset_cupoane = isset($_POST['mlc_reset_cupoane']) && $_POST['mlc_reset_cupoane'] === '1';

        $user = get_user_by('ID', $user_id);

        if (!$user) {
            echo '<div style="color:red; padding:10px;">Userul nu a fost găsit.</div>';
        } else {
            $bonuri_folosite = get_user_meta($user_id, 'mlc_bonuri_folosite', true);
            if (!is_array($bonuri_folosite)) $bonuri_folosite = [];

            if (in_array($numar_bon, $bonuri_folosite)) {
                echo '<div style="color:red; padding:10px;">Acest număr de bon a fost deja folosit pentru acest user.</div>';
            } else {
                // Calcul factor în funcție de rang
                $rang = get_user_meta($user_id, 'mlc_rank', true);
                $factor = 1; // implicit Bronze

                if ($rang === 'Silver') {
                    $factor = 2;
                } elseif ($rang === 'Gold') {
                    $factor = 3;
                } elseif ($rang === 'Platinum') {
                    $factor = 4;
                }

                $puncte_de_adaugat = floor($suma / 100) * $factor;
                $puncte_vechi = intval(get_user_meta($user_id, 'puncte_fidelitate', true));
                $puncte_noi = $puncte_vechi + $puncte_de_adaugat;

                update_user_meta($user_id, 'puncte_fidelitate', $puncte_noi);

                // Marcare bon
                $bonuri_folosite[] = $numar_bon;
                update_user_meta($user_id, 'mlc_bonuri_folosite', $bonuri_folosite);

                // Resetare cupoane dacă e bifat
                if ($reset_cupoane) {
                    $cupoane = get_user_meta($user_id, 'mlc_cupoane_deblocate', true);
                    if (!is_array($cupoane)) $cupoane = [];

                    if (!empty($cupoane)) {
                        update_user_meta($user_id, 'mlc_cupoane_deblocate', []);
                        $vizite = intval(get_user_meta($user_id, 'mlc_resetari_cupoane', true));
                        $vizite++;
                        update_user_meta($user_id, 'mlc_resetari_cupoane', $vizite);

                        // Actualizează rangul
                        if ($vizite >= 10) {
                            update_user_meta($user_id, 'mlc_rank', 'Platinum');
                        } elseif ($vizite >= 6) {
                            update_user_meta($user_id, 'mlc_rank', 'Gold');
                        } elseif ($vizite >= 3) {
                            update_user_meta($user_id, 'mlc_rank', 'Silver');
                        } else {
                            update_user_meta($user_id, 'mlc_rank', 'Bronze');
                        }

                        $reset_msg = "Cupoanele au fost resetate.";
                    } else {
                        $reset_msg = "Userul nu avea cupoane active. Nu s-a făcut resetare.";
                    }
                } else {
                    $reset_msg = "Cupoanele nu au fost resetate.";
                }

                echo '<div style="padding:10px; background:#d4edda; color:#155724;">';
                echo "Ai adăugat <strong>$puncte_de_adaugat</strong> puncte userului <strong>{$user->user_login}</strong> (ID: $user_id). Total puncte: <strong>$puncte_noi</strong>.<br>";
                echo $reset_msg;
                echo '</div>';
            }
        }
    }

    ?>
    <h1>Adaugă puncte fidelitate user</h1>
    <form method="post" id="mlc_form" autocomplete="off" style="position:relative; max-width:400px;">
        <label for="mlc_user_search">Caută user (username sau email):</label><br>
        <input type="text" id="mlc_user_search" placeholder="Scrie username sau email" required style="width:100%; padding:8px;">
        <input type="hidden" name="mlc_user_id" id="mlc_user_id" required>

        <ul id="mlc_user_list" style="border:1px solid #ccc; max-height:150px; overflow-y:auto; margin:0; padding:0; list-style:none; display:none; position:absolute; background:#fff; width:100%; z-index:9999;"></ul>

        <br><br>

        <label for="mlc_numar_bon">Număr bon / factură:</label><br>
        <input type="text" name="mlc_numar_bon" id="mlc_numar_bon" required style="width:100%; padding:8px;">

        <br><br>

        <label for="mlc_suma_huf">Suma (HUF):</label><br>
        <input type="number" name="mlc_suma_huf" id="mlc_suma_huf" min="0" step="1" required style="width:100%; padding:8px;">

        <br><br>

        <label>
            <input type="checkbox" name="mlc_reset_cupoane" value="1" style="margin-right:5px;">
            Resetează cupoanele userului după adăugare puncte
        </label>

        <br><br>

        <input type="submit" value="Adaugă puncte" style="padding:10px 20px;">
    </form>

    <style>
        #mlc_user_list li {
            padding: 8px;
            cursor: pointer;
        }
        #mlc_user_list li:hover {
            background-color: #eee;
        }
    </style>

    <script>
    jQuery(document).ready(function($){
        const $input = $('#mlc_user_search');
        const $list = $('#mlc_user_list');
        let timeout = null;

        $input.on('input', function(){
            clearTimeout(timeout);
            const term = $(this).val().trim();

            $('#mlc_user_id').val('');

            if(term.length < 2){
                $list.hide();
                return;
            }

            timeout = setTimeout(function(){
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'mlc_user_search',
                        term: term
                    },
                    success: function(users){
                        if(users.length === 0){
                            $list.hide();
                            return;
                        }
                        $list.empty();
                        users.forEach(function(user){
                            $list.append(`<li data-id="${user.value}">${user.label}</li>`);
                        });
                        $list.show();
                    },
                    error: function(){
                        $list.hide();
                    }
                });
            }, 300);
        });

        $list.on('click', 'li', function(){
            const userId = $(this).data('id');
            const userLabel = $(this).text();

            $input.val(userLabel);
            $('#mlc_user_id').val(userId);
            $list.hide();
        });

        $(document).on('click', function(e){
            if(!$(e.target).closest('#mlc_user_search, #mlc_user_list').length){
                $list.hide();
            }
        });
    });
    </script>
    <?php
}

// 3. AJAX pentru live search user
add_action('wp_ajax_mlc_user_search', function(){
    if (!current_user_can('manage_options')) {
        wp_send_json([]);
    }

    $term = sanitize_text_field($_POST['term'] ?? '');
    if (strlen($term) < 2) {
        wp_send_json([]);
    }

    $users = get_users([
        'search' => '*' . esc_attr($term) . '*',
        'search_columns' => ['user_login', 'user_email', 'display_name'],
        'number' => 10,
    ]);

    $results = [];
    foreach ($users as $user) {
        $label = $user->user_login . ' (' . $user->user_email . ')';
        $results[] = ['label' => $label, 'value' => $user->ID];
    }

    wp_send_json($results);
});
