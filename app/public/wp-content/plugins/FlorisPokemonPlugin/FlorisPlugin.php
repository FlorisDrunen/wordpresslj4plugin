<?php
/*
Plugin Name: Pokémon Viewer
Description: Toont Pokémon via de PokeAPI met instelbare opties.
Version: 1.1
Author: Jouw Naam
*/

if (!defined('ABSPATH')) exit;

/* ========== SETTINGS PAGE ========== */

add_action('admin_menu', 'pv_add_menu');
function pv_add_menu() {
    add_menu_page('Pokémon Viewer', 'Pokémon Viewer', 'manage_options', 'pokemon-viewer', 'pv_settings_page');
}

add_action('admin_init', 'pv_register_settings');
function pv_register_settings() {
    register_setting('pv_settings', 'pv_pokemon');
    register_setting('pv_settings', 'pv_mode');
    register_setting('pv_settings', 'pv_show_image');
    register_setting('pv_settings', 'pv_max_abilities');
}

function pv_settings_page() {
?>
<div class="wrap">
<h1>Pokémon Viewer</h1>
<form method="post" action="options.php">
<?php settings_fields('pv_settings'); ?>
<table class="form-table">

<tr><th>Pokémon naam</th>
<td><input type="text" name="pv_pokemon" value="<?php echo esc_attr(get_option('pv_pokemon', 'pikachu')); ?>"></td></tr>

<tr><th>Weergavemodus</th>
<td>
<select name="pv_mode">
<option value="basic" <?php selected(get_option('pv_mode'),'basic'); ?>>Basic</option>
<option value="stats" <?php selected(get_option('pv_mode'),'stats'); ?>>Stats</option>
<option value="full" <?php selected(get_option('pv_mode'),'full'); ?>>Full</option>
</select>
</td></tr>

<tr><th>Toon afbeelding</th>
<td><input type="checkbox" name="pv_show_image" value="1" <?php checked(1, get_option('pv_show_image')); ?>></td></tr>

<tr><th>Max abilities</th>
<td><input type="number" name="pv_max_abilities" value="<?php echo esc_attr(get_option('pv_max_abilities', 3)); ?>"></td></tr>

</table>
<?php submit_button(); ?>
</form>
</div>
<?php }

/* ========== API FUNCTIONS (zoals PowerPoint) ========== */

function pv_get_pokemon_data($pokemon) {
    $url = "https://pokeapi.co/api/v2/pokemon/$pokemon";
    $response = wp_remote_get($url);

    if (is_wp_error($response)) return false;

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}

/* ========== SHORTCODE ========== */

add_shortcode('pokemon_viewer', 'pv_shortcode');
function pv_shortcode() {

    $pokemon = strtolower(get_option('pv_pokemon', 'pikachu'));
    $mode = get_option('pv_mode', 'basic');
    $show_image = get_option('pv_show_image');
    $max = intval(get_option('pv_max_abilities', 3));

    $data = pv_get_pokemon_data($pokemon);
    if (!$data) return "Geen Pokémon gevonden.";

    $output = "<div class='pokemon-viewer'><h2>".ucfirst($data['name'])."</h2>";

    if ($show_image) {
        $output .= "<img src='".$data['sprites']['front_default']."'>";
    }

    if ($mode != 'basic') {
        $output .= "<h3>Stats</h3><ul>";
        foreach ($data['stats'] as $stat) {
            $output .= "<li>".$stat['stat']['name'].": ".$stat['base_stat']."</li>";
        }
        $output .= "</ul>";
    }

    if ($mode == 'full') {
        $output .= "<h3>Abilities</h3><ul>";
        $i = 0;
        foreach ($data['abilities'] as $ab) {
            if (++$i > $max) break;
            $output .= "<li>".$ab['ability']['name']."</li>";
        }
        $output .= "</ul>";
    }

    return $output."</div>";
}
