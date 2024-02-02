<?php
/**
 * Plugin Name: Our Metabox
 * Description: A Metabox for practice
 * Plugin URI: https://nahidulislamsayel.co
 * Author: Nahidul Islam Sayel
 * Author URI: https://NahidulIslamSayel.com
 * Version: 1.0
 * License: GPL2 or later
 * Text Domain: our-metabox
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

class OurMetabox {
    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'omb_load_text_domain' ) );
        add_action( 'admin_menu', array( $this, 'omb_add_metabox' ) );
        add_action( 'save_post', array( $this, 'omb_save_location' ) );
    }

    public function omb_load_text_domain() {
        load_plugin_textdomain( 'our-metabox', false, dirname( __FILE__ ) . '/language' );
    }

    private function is_secure($nonce_field, $action, $post_id) {
        $nonce = isset( $_POST[$nonce_field] ) ? $_POST[$nonce_field] : '';

        if ( $nonce == '' ) {
            return false;
        }
        if ( ! wp_verify_nonce( $nonce, $action ) ) {
            return false;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }
        if ( wp_is_post_autosave( $post_id ) ) {
            return false;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return false;
        }

        return true;
    }

    public function omb_save_location($post_id) {
        if ( ! $this->is_secure( 'omb_form_field', 'omb_form', $post_id ) ) {
            return $post_id;
        }

        $location = isset( $_POST['omb_location'] ) ? $_POST['omb_location'] : '';
        $country  = isset( $_POST['omb_country'] ) ? $_POST['omb_country'] : '';
		$location = sanitize_text_field( $location );
		$country = sanitize_text_field( $country );
        if ( $location != '' || $country != '' ) {
            update_post_meta( $post_id, 'omb_location', $location );
            update_post_meta( $post_id, 'omb_country', $country );
        }
    }

    public function omb_add_metabox() {
        add_meta_box(
            'omb',
            __( 'Location Info', 'our-metabox' ),
            array( $this, 'location_info' ),
            'post',
            'side',
            'high'
        );
    }

    public function location_info($post) {
        $location    = get_post_meta( $post->ID, 'omb_location', true );
        $country     = get_post_meta( $post->ID, 'omb_country', true );
        $label1      = __( 'Location', 'our-metabox' );
        $label2      = __( 'Country', 'our-metabox' );
        wp_nonce_field( 'omb_form', 'omb_form_field' );
        $metabox_html = <<<EOD
        <p>
            <label for="omb_location">
                {$label1}
            </label>
            <input type="text" name="omb_location" id="omb_location" value="{$location}"/>
            <label for="omb_country">
                {$label2}
            </label>
            <input type="text" name="omb_country" id="omb_country" value="{$country}"/>
        </p>
        EOD;
        echo $metabox_html;
    }
}

new OurMetabox();
