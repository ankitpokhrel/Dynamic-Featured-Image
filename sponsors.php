<?php

// Avoid direct calls to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/**
 * Plugin sponsors.
 *
 * @version 3.6.8
 */
class PluginSponsor {
    /* Recommend plugins.
     *
     * @since 3.6.8
     */
    protected static $sponsors = array(
        'mailoptin' => 'mailoptin/mailoptin.php',
    );

    /**
     * PluginSponsor constructor.
     *
     * @since 3.6.8
     */
    public function __construct() {
        // admin notices.
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );
        add_action( 'network_admin_notices', array( $this, 'admin_notice' ) );

        add_action( 'admin_init', array( $this, 'dismiss_admin_notice' ) );
    }

    /**
     * Dismiss admin notice.
     *
     * @since 3.6.8
     * @access public
     *
     * @return void
     */
    public function dismiss_admin_notice() {
        if ( ! isset( $_GET['mo-adaction'] ) || $_GET['mo-adaction'] != 'mo_dismiss_adnotice' ) {
            return;
        }

        $url = admin_url();
        update_option( 'mo_dismiss_adnotice', 'true' );

        wp_redirect( $url );
        exit;
    }

    /**
     * Add admin notices.
     *
     * @since 3.6.8
     * @access public
     *
     * @return void
     */
    public function admin_notice() {
        if ( get_option( 'mo_dismiss_adnotice', 'false' ) == 'true' ) {
            return;
        }

        if ( $this->is_plugin_installed( 'mailoptin' ) && $this->is_plugin_active( 'mailoptin' ) ) {
            return;
        }

        $dismiss_url = esc_url_raw(
            add_query_arg(
                array(
                    'mo-adaction' => 'mo_dismiss_adnotice',
                ),
                admin_url()
            )
        );

        $this->notice_css();

        $install_url = wp_nonce_url(
            admin_url( 'update.php?action=install-plugin&plugin=mailoptin' ),
            'install-plugin_mailoptin'
        );

        $activate_url = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=mailoptin%2Fmailoptin.php' ),
            'activate-plugin_mailoptin/mailoptin.php' );
        ?>
        <div class="mo-admin-notice notice notice-success">
            <div class="mo-notice-first-half">
                <p>
                    <?php
                    printf(
                        __( 'Free optin form plugin that will %1$sincrease your email list subscribers%2$s and keep them engaged with %1$sautomated and schedule newsletters%2$s.' ),
                        '<span class="mo-stylize"><strong>', '</strong></span>' );
                    ?>
                </p>
                <p style="text-decoration: underline;font-size: 12px;">Recommended by Dynamic Featured Image plugin</p>
            </div>
            <div class="mo-notice-other-half">
                <?php if ( ! $this->is_plugin_installed( 'mailoptin' ) ) : ?>
                    <a class="button button-primary button-hero" id="mo-install-mailoptin-plugin"
                       href="<?php echo $install_url; ?>">
                        <?php _e( 'Install MailOptin Now for Free!' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( $this->is_plugin_installed( 'mailoptin' ) && ! $this->is_plugin_active( 'mailoptin' ) ) : ?>
                    <a class="button button-primary button-hero" id="mo-activate-mailoptin-plugin"
                       href="<?php echo $activate_url; ?>">
                        <?php _e( 'Activate MailOptin Now!' ); ?>
                    </a>
                <?php endif; ?>
                <div class="mo-notice-learn-more">
                    <a target="_blank" href="https://mailoptin.io">Learn more</a>
                </div>
            </div>
            <a href="<?php echo $dismiss_url; ?>">
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( 'Dismiss this notice' ); ?>.</span>
                </button>
            </a>
        </div>
        <?php
    }

    /**
     * Check if plugin is installed.
     *
     * @param $key
     *
     * @return bool
     */
    protected function is_plugin_installed( $key ) {
        $installed_plugins = get_plugins();

        return isset( $installed_plugins[ self::$sponsors[ $key ] ] );
    }

    /**
     * Check if plugin is active.
     *
     * @param $key
     *
     * @return bool
     */
    protected function is_plugin_active( $key )  {
        return is_plugin_active( self::$sponsors[ $key ] );
    }

    /**
     * Styles for notice.
     *
     * @return void
     */
    protected function notice_css() {
        ?>
        <style type="text/css">
            .mo-admin-notice {
                background: #fff;
                color: #000;
                border-left-color: #46b450;
                position: relative;
            }

            .mo-admin-notice .notice-dismiss:before {
                color: #72777c;
            }

            .mo-admin-notice .mo-stylize {
                line-height: 2;
            }

            .mo-admin-notice .button-primary {
                background: #006799;
                text-shadow: none;
                border: 0;
                box-shadow: none;
            }

            .mo-notice-first-half {
                width: 66%;
                display: inline-block;
                margin: 10px 0;
            }

            .mo-notice-other-half {
                width: 33%;
                display: inline-block;
                padding: 20px 0;
                position: absolute;
                text-align: center;
            }

            .mo-notice-first-half p {
                font-size: 14px;
            }

            .mo-notice-learn-more a {
                margin: 10px;
            }

            .mo-notice-learn-more {
                margin-top: 10px;
            }
        </style>
        <?php
    }
}
