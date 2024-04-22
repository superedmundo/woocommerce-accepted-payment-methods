<?php
/*
Plugin Name: WooCommerce Accepted Payment Methods
Plugin URI: http://jameskoster.co.uk/tag/accepted-payment-methods/
Version: 0.8.0
Description: Allows you display which payment methods your online store accepts.
Author: jameskoster
Tested up to: 8.8.2
Author URI: http://jameskoster.co.uk
Text Domain: woocommerce-accepted-payment-methods
Domain Path: /languages/

	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) exit;

/**
 * Localisation
 */
load_plugin_textdomain( 'woocommerce-accepted-payment-methods', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// Definición de la clase principal del plugin
	class WC_apm {

		/**
		 * Static property to hold our singleton instance
		 *
		 */
		static $instance = false;

		/**
		 * This is our constructor
		 *
		 * @return void
		 */
		private function __construct() {

			register_activation_hook  ( __FILE__,  [ $this, 'activar_plugin'    ] );
			register_deactivation_hook( __FILE__,  [ $this, 'desactivar_plugin' ] );

			// Engancha los métodos de la clase a los hooks de WordPress
			add_action( 'init', array( $this, 'inicializar_plugin' ) );
		}

		// Método para inicializar el plugin
		public function inicializar_plugin() {
			// Agrega aquí tu lógica de inicialización del plugin
			// Por ejemplo, puedes registrar hooks adicionales, cargar archivos CSS/JS, etc.
			
			// Init settings
			$this->settings = array(
				array(
					'name' => __( 'Accepted Payment Methods', 'woocommerce-accepted-payment-methods' ),
					'type' => 'title',
					'desc' => sprintf( __( 'To display the selected payment methods you can use the built in widget, the %s shortcode or the %s template tag.', 'woocommerce-accepted-payment-methods' ), '<code>[woocommerce_accepted_payment_methods]</code>', '<code>&lt;?php wc_accepted_payment_methods(); ?&gt;</code>' ),
					'id' => 'wc_apm_options'
				),
				array(
					'name' 		=> __( 'American Express', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the American Express logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_american_express',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Bitcoin', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the Bitcoin logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_bitcoin',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Cash on Delivery', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display Cash on Delivery symbol', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_cash_on_delivery',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Dankort', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the Dankort logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_dankort',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Discover', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the Discover logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_discover',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Google', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the Google logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_google',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Maestro', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the Maestro logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_maestro',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'MasterCard', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the MasterCard logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_mastercard',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'PayPal', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the PayPal logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_paypal',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'Visa', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the Visa logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_visa',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> __( 'MercadoPago', 'woocommerce-accepted-payment-methods' ),
					'desc' 		=> __( 'Display the MercadoPago logo', 'woocommerce-accepted-payment-methods' ),
					'id' 		=> 'wc_apm_mercadopago',
					'type' 		=> 'checkbox'
				),
				array( 'type' => 'sectionend', 'id' => 'wc_apm_options' ),
			);

			// Admin
			add_action( 'woocommerce_settings_checkout', array( $this, 'admin_settings' ), 20 );
			add_action( 'woocommerce_update_options_checkout', array( $this, 'save_admin_settings' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'setup_styles' ) );

			// The shortcode
			add_shortcode( 'woocommerce_accepted_payment_methods', array( $this, 'wc_accepted_payment_methods' ) );

		}

		// Método para activar el plugin
		public function activar_plugin() {

			//error_log('public function activar_plugin()');

			// Agrega aquí la lógica para agregar registros a la base de datos al activar el plugin
			// Por ejemplo, puedes crear tablas personalizadas o agregar opciones a la base de datos
			add_option( 'wc_apm_label', 			'' );
			add_option( 'wc_apm_american_express', 	'no' );
			add_option( 'wc_apm_google', 			'no' );
			add_option( 'wc_apm_mastercard', 		'no' );
			add_option( 'wc_apm_paypal', 			'no' );
			add_option( 'wc_apm_visa', 				'no' );
			add_option( 'wc_apm_discover', 			'no' );
			add_option( 'wc_apm_bitcoin', 			'no' );
			add_option( 'wc_apm_maestro', 			'no' );
			add_option( 'wc_apm_cash_on_delivery', 	'no' );
			add_option( 'wc_apm_dankort', 	        'no' );
			add_option( 'wc_apm_mercadopago',       'no' );
		}

		// Método para desactivar el plugin
		public function desactivar_plugin() {
			// Agrega aquí la lógica para quitar los registros de la base de datos al desactivar el plugin
			// Por ejemplo, puedes eliminar tablas personalizadas o eliminar opciones de la base de datos

			//error_log('public function desactivar_plugin()');

			// Elimina las opciones al desinstalar el plugin
			delete_option( 'wc_apm_label'            );
			delete_option( 'wc_apm_american_express' );
			delete_option( 'wc_apm_google'      	 );
			delete_option( 'wc_apm_mastercard'  	 );
			delete_option( 'wc_apm_paypal'      	 );
			delete_option( 'wc_apm_visa'        	 );
			delete_option( 'wc_apm_discover'    	 );
			delete_option( 'wc_apm_bitcoin'     	 );
			delete_option( 'wc_apm_maestro'     	 );
			delete_option( 'wc_apm_cash_on_delivery' );
			delete_option( 'wc_apm_dankort'     	 );
			delete_option( 'wc_apm_mercadopago' 	 );


		}

		/*-----------------------------------------------------------------------------------*/
		/* Class Functions */
		/*-----------------------------------------------------------------------------------*/

		public function admin_settings() {
			woocommerce_admin_fields( $this->settings );
		}

		public function save_admin_settings() {
			woocommerce_update_options( $this->settings );
		}

		// Setup styles
		public function setup_styles() {
			wp_enqueue_style( 'apm-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );
		}

		public function wc_accepted_payment_methods() {
			$amex 		 = get_option( 'wc_apm_american_express');
			$google 	 = get_option( 'wc_apm_google' 			);
			$mastercard  = get_option( 'wc_apm_mastercard' 		);
			$paypal 	 = get_option( 'wc_apm_paypal' 			);
			$visa 		 = get_option( 'wc_apm_visa' 			);
			$discover 	 = get_option( 'wc_apm_discover' 		);
			$bitcoin 	 = get_option( 'wc_apm_bitcoin' 		);
			$maestro 	 = get_option( 'wc_apm_maestro' 		);
			$cod		 = get_option( 'wc_apm_cash_on_delivery');
			$dankort 	 = get_option( 'wc_apm_dankort'			);
			$mercadopago = get_option( 'wc_apm_mercadopago'		);

			// Display
			echo '<ul class="accepted-payment-methods">';
				if ( $dankort == "yes" ) { echo '<li class="dankort"><span>Dankort</span></li>'; }
				if ( $amex == "yes" ) { echo '<li class="american-express"><span>American Express</span></li>'; }
				if ( $bitcoin == "yes" ) { echo '<li class="bitcoin"><span>Bitcoin</span></li>'; }
				if ( $cod == "yes" ) { echo '<li class="cash-on-delivery"><span>Cash on Delivery</span></li>'; }
				if ( $discover == "yes" ) { echo '<li class="discover"><span>Discover</span></li>'; }
				if ( $google == "yes" ) { echo '<li class="google"><span>Google</span></li>'; }
				if ( $maestro == "yes" ) { echo '<li class="maestro"><span>Maestro</span></li>'; }
				if ( $mastercard == "yes" ) { echo '<li class="mastercard"><span>MasterCard</span></li>'; }
				if ( $paypal == "yes" ) { echo '<li class="paypal"><span>PayPal</span></li>'; }
				if ( $visa == "yes" ) { echo '<li class="visa"><span>Visa</span></li>'; }
				if ( $mercadopago == "yes" ) { echo '<li class="mercadopago"><span>MercadoPago</span></li>'; }
			echo '</ul>';

		}


		/**
		 * If an instance exists, this returns it.  If not, it creates one and
		 * retuns it.
		 *
		 * @return WC_apm
		 */

		public static function getInstance() {
			if ( !self::$instance )
				self::$instance = new self;
			return self::$instance;
		}

	// end class
	}

	// Instancia la clase principal del plugin
	$wc_apm = WC_apm::getInstance();

	// The widget
	class Accepted_Payment_Methods extends WP_Widget {

		function Accepted_Payment_Methods() {
			// Instantiate the parent object
			parent::__construct( false, 'WooCommerce Accepted Payment Methods' );
		}

		function widget( $args, $instance ) {
			// Widget output
			extract( $args );

			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $before_widget;
			if ( ! empty( $title ) )
				echo $before_title . $title . $after_title;
				$wc_apm->wc_accepted_payment_methods();
			echo $after_widget;
		}
		/**
		 * Sanitize widget form values as they are saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = strip_tags( $new_instance['title'] );

			return $instance;
		}

		/**
		 * Back-end widget form.
		 */
		public function form( $instance ) {
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
			}
			else {
				$title = __( 'Accepted Payment Methods', 'woocommerce-accepted-payment-methods' );
			}
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'woocommerce-accepted-payment-methods' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>
			<?php _e( 'Configure which payment methods your store accepts in the', 'woocommerce-accepted-payment-methods' ); ?> <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout' ); ?>"><?php _e( 'WooCommerce settings', 'woocommerce-accepted-payment-methods' ); ?></a>.
			</p>
			<?php
		}

	}

}