<?php
// Plugin name: FlorisPlugin
// Description: plugin voor saai school vak
// Author: Floris
// Version: 1.0

add_action('wp_footer', 'florisplugin_display_message');

function florisplugin_display_message() {
    echo '<p style="text-align: center; font-size: 20px; color: blue;">Dit is een bericht van FlorisPlugin!</p>';
}
