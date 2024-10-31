<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 * @link              https://finpose.com
 * @since             1.0.0
 * @package           Finpose
 * @author            info@finpose.com
 */
class orfw_Activator {

	/**
	 * Activation hook
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
    $fv = get_option('orfw_version');
    if(!$fv) {
      self::createTables();
      add_option( 'orfw_version', ORFW_VERSION );
      add_option( 'orfw_db_version', ORFW_DBVERSION );
    } else {
      if($fv!=ORFW_VERSION) {
        update_option( 'orfw_version', ORFW_VERSION );
      }

      $dbv = get_option('orfw_db_version');
      if($dbv && ($dbv!=ORFW_DBVERSION)) {
        self::updateTables();
      }
    }
	}

  public static function createTables() {
    global $wpdb;

      $charset_collate = $wpdb->get_charset_collate();

      if ( ! function_exists('dbDelta') ) {
          require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      }

      dbDelta( $sql );

  }

  public static function updateTables() {
  global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    if ( ! function_exists('dbDelta') ) {
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    dbDelta( $sql );

    update_option( 'orfw_db_version', ORFW_DBVERSION );
  }


}
