<?php
// Plugin Name: Pokemon api plugin
// Description: laat pokemon zien die je in de settings kan aanpassen.
// Version: 1.0
// Author: Floris



if (!defined('ABSPATH')) exit;

// settings page code
add_action('wp_enqueue_scripts', 'pap_load_styles');
function pap_load_styles()
{
    wp_enqueue_style(
        'pap-style',
        plugin_dir_url(__FILE__) . 'style.css'
    );
}


add_action('admin_menu', 'pap_add_menu');
function pap_add_menu()
{
    add_menu_page('Pokémon api plugin', 'Pokemon api plugin', 'manage_options', 'pokemon-api-plugin', 'pap_settings_page');
}

add_action('admin_init', 'pap_register_settings');

// options in options page
function pap_register_settings()
{
    register_setting('pap_settings', 'pap_pokemon');
    register_setting('pap_settings', 'pap_mode');
    register_setting('pap_settings', 'pap_show_image');
    register_setting('pap_settings', 'pap_max_abilities');
}
// options page itself
function pap_settings_page()
{
?>
    <div class="wrap">
        <h1>Pokémon Viewer</h1>
        <form method="post" action="options.php">
            <?php settings_fields('pap_settings'); ?>
            <table class="form-table">

                <tr>
                    <th>Pokemon naam</th>
                    <td><input type="text" name="pap_pokemon" value="<?php echo esc_attr(get_option('pap_pokemon', 'pikachu')); ?>"></td>
                </tr>

                <tr>
                    <th>Weergavemodus</th>
                    <td>
                        <select name="pap_mode">
                            <option value="basic" <?php selected(get_option('pap_mode'), 'basic'); ?>>Basic</option>
                            <option value="stats" <?php selected(get_option('pap_mode'), 'stats'); ?>>Stats</option>
                            <option value="full" <?php selected(get_option('pap_mode'), 'full'); ?>>Full</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>Toon afbeelding</th>
                    <td><input type="checkbox" name="pap_show_image" value="1" <?php checked(1, get_option('pap_show_image')); ?>></td>
                </tr>

                <tr>
                    <th>Max abilities</th>
                    <td><input type="number" name="pap_max_abilities" value="<?php echo esc_attr(get_option('pap_max_abilities', 3)); ?>"></td>
                </tr>

            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }


// API connection code
function pap_get_pokemon_data($pokemon)
{
    $url = "https://pokeapi.co/api/v2/pokemon/$pokemon";
    $response = wp_remote_get($url);

    if (is_wp_error($response)) return false;

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}

// shortcode code

add_shortcode('pokemon_api_plugin', 'pap_shortcode');
function pap_shortcode()
{
    $pokemon = strtolower(get_option('pap_pokemon', 'pikachu'));
    $mode = get_option('pap_mode', 'basic');
    $show_image = get_option('pap_show_image');
    $max = intval(get_option('pap_max_abilities', 3));

    $data = pap_get_pokemon_data($pokemon);
    if (!$data) {
    return "<div class='pokemon-viewer pokemon-error'>
                <h2>❌ Pokémon niet gevonden</h2>
                <p>Controleer de naam in de plugin instellingen.</p>
            </div>";
}


    $output = "<div class='pokemon-viewer'><h2>" . ucfirst($data['name']) . "</h2>";

    if ($show_image) {
        $output .= "<img src='" . $data['sprites']['front_default'] . "'>";
    }

    if ($mode != 'basic') {
        $output .= "<h3>Stats</h3><ul>";
        foreach ($data['stats'] as $stat) {
            $output .= "<li>" . $stat['stat']['name'] . ": " . $stat['base_stat'] . "</li>";
        }
        $output .= "</ul>";
    }

    if ($mode == 'full') {
        $output .= "<h3>Abilities</h3><ul>";
        $i = 0;
        foreach ($data['abilities'] as $ab) {
            if (++$i > $max) break;
            $output .= "<li>" . $ab['ability']['name'] . "</li>";
        }
        $output .= "</ul>";
    }

    return $output . "</div>";
}
