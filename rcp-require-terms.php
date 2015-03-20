<?php
/*
 * Plugin Name: Restrict Content Pro Terms And Conditions
 * Version: 1.0
 * Plugin URI: https://github.com/abrudtkuhl/restrict-content-pro-require-terms
 * Description: Adds an "Accept Terms And Conditions" checkbox to Restrict Content Pro plugin
 * Author: Andy Brudktuhl
 * Author URI: http://youmetandy.com
 * Requires at least: 4.0
 * Tested up to: 4.1
 *
 * Text Domain: restrict-content-pro-require-terms
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Andy Brudtkuhl
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class RCP_Terms {

  /**
   * Private variables
   */
  private $options;

  /**
   * Constructor
   */
  public function __construct() {
    if ( ! is_admin() ) {
      add_action( 'rcp_after_register_form_fields', array( $this, 'terms_field' ) );
      add_action( 'rcp_form_errors', array( $this, 'check_for_agreement' ) );
    } else {
      add_action( 'admin_menu', array( $this, 'admin_menu' ) );
      add_action( 'admin_init', array( $this, 'admin_init' ) );
    }
  }

  /**
   * Admin Settings
   */
  public function admin_menu() {
    add_submenu_page( 'rcp-members', __( 'Terms', 'rcp' ), __( 'Terms', 'rcp' ), 'rcp_view_members', 'rcp-terms', array( $this, 'admin_page' ) );
  }

  public function admin_page() {
    $this->options = get_option( 'rcp_terms_options' );
    ?>
    <div class="wrap">
      <h2>RCP Terms and Conditions</h2>
      <form method="post" action="options.php">
        <?php
          // This prints out all hidden setting fields
          settings_fields( 'rcp_terms_option_group' );
          do_settings_sections( 'rcp-terms-settings-admin' );
          submit_button();
        ?>
      </form>
    </div>

  <?php }

  public function admin_init() {
    register_setting(
      'rcp_terms_option_group', // Option group
      'rcp_terms_options', // Option name
      array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
      'rcp_terms_admin_section', // ID
      '', // Title
      array( $this, 'admin_print_section_info' ), // Callback
      'rcp-terms-settings-admin' // Page
    );

    add_settings_field(
      'rcp_terms_label', // ID
      'Label', // Title
      array( $this, 'rcp_terms_label_callback' ), // Callback
      'rcp-terms-settings-admin', // Page
      'rcp_terms_admin_section' // Section
    );

    add_settings_field(
      'rcp_terms_link', // ID
      'Link', // Title
      array( $this, 'rcp_terms_link_callback' ), // Callback
      'rcp-terms-settings-admin', // Page
      'rcp_terms_admin_section' // Section
    );
  }

  public function rcp_terms_label_callback()
  {
    printf(
      '<input type="text" id="rcp_terms_label" name="rcp_terms_options[rcp_terms_label]" value="%s" placeholder="Please accept Terms and Conditions" />',
      isset( $this->options['rcp_terms_label'] ) ? esc_attr( $this->options['rcp_terms_label']) : ''
    );
  }

  public function rcp_terms_link_callback()
  {
    printf(
      '<input type="text" id="rcp_terms_link" name="rcp_terms_options[rcp_terms_link]" value="%s" placeholder="http://" />',
      isset( $this->options['rcp_terms_link'] ) ? esc_attr( $this->options['rcp_terms_link']) : ''
    );
  }

  public function admin_print_section_info() {
    print 'Enter the Label and Link you wish to show up on the front end registration form of Restrict Content Pro<br /><br />
          <strong>Label:</strong> This is the next next to the textbox in the registration form <br />
          <em>Optional; Default: Please Accept Terms and Conditions</em><br /><br />
          <strong>Link:</strong> A link to your Terms and Conditions for them to view. Will change the label to a link that opens in a new window<br />
          <em>Optional; Default: no link</em>
          ';
  }

  public function sanitize( $input )
  {
    $new_input = array();

    if( isset( $input['rcp_terms_label'] ) )
      $new_input['rcp_terms_label'] = sanitize_text_field( $input['rcp_terms_label'] );

    if( isset( $input['rcp_terms_link'] ) )
      $new_input['rcp_terms_link'] = sanitize_text_field( $input['rcp_terms_link'] );

    return $new_input;
  }


  /**
   * Render fields in RCP Registration Form
   */
  public function terms_field() {
    $options = get_option( 'rcp_terms_options' );
    $link = isset( $options[ 'rcp_terms_link' ] ) ? $options[ 'rcp_terms_link' ] : '';
    $label = (isset( $options[ 'rcp_terms_label' ] ) && !empty( $options [ 'rcp_terms_label' ] )) ? $options[ 'rcp_terms_label' ] : 'Please Accept Terms and Conditions';
    ob_start(); ?>
		<p>
			<input name="rcp_terms_agreement" id="rcp_terms_agreement" class="require" type="checkbox"/>
			<label for="rcp_terms_agreement">
        <?php if ( !empty ( $link ) ) : ?>
          <a href="<?php echo $link; ?>" target="_blank"><?php echo $label; ?></a>
        <?php else: ?>
          <?php echo $label; ?>
        <?php endif; ?>
      </label>
		</p>
  	<?php
  	echo ob_get_clean();
  }

  public function check_for_agreement( $posted ) {
    if ( ! isset( $posted['rcp_terms_agreement'] ) ) {
      rcp_errors()->add('agree_to_terms', __('You must agree to the terms to continue', 'rcp'), 'register' );
    }
  }
}

$rcp_terms = new RCP_Terms;
