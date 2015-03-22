<?php
	if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit();
	}

	//cleanup plugin data
	delete_post_meta_by_key('dfiFeatured');
	delete_post_meta_by_key('_dfi_link_to_image');
