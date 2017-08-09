<?php
/**
 * Uninstall script.
 *
 * @since 0.0.0
 * @package dynamic-featured-image
 * @author Ankit Pokhrel <info@ankitpokhrel.com, https://ankitpokhrel.com>
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// cleanup plugin data.
delete_post_meta_by_key( 'dfiFeatured' );
delete_post_meta_by_key( '_dfi_link_to_image' );
