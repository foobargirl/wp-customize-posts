<?php
/**
 * Customize Post Date Control Class
 *
 * @package WordPress
 * @subpackage Customize
 */

/**
 * Class WP_Customize_Post_Date_Control
 */
class WP_Customize_Post_Date_Control extends WP_Customize_Dynamic_Control {

	/**
	 * Posts component.
	 *
	 * @var WP_Customize_Posts
	 */
	public $posts_component;

	/**
	 * Constructor.
	 *
	 * @throws Exception If posts component not available.
	 *
	 * @param WP_Customize_Manager $manager Manager.
	 * @param string               $id      Control id.
	 * @param array                $args    Control args.
	 */
	public function __construct( WP_Customize_Manager $manager, $id, array $args ) {
		if ( ! isset( $manager->posts ) || ! ( $manager->posts instanceof WP_Customize_Posts ) ) {
			throw new Exception( 'Missing Posts component.' );
		}
		$this->posts_component = $manager->posts;
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Type of control, used by JS.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'post_date';

	/**
	 * Render the Underscore template for this control.
	 *
	 * @access protected
	 * @codeCoverageIgnore
	 */
	protected function content_template() {
		$data = $this->json();
		?>
		<#
		_.defaults( data, <?php echo wp_json_encode( $data ) ?> );
		data.input_id = 'input-' + String( Math.random() );
		#>
		<span class="customize-control-title"><label for="{{ data.input_id }}">{{ data.label }}</label></span>
		<div class="date-inputs">
			<select id="{{ data.input_id }}" class="date-input month" data-component="month">
				<# _.each( data.choices, function( choice ) { #>
					<#
					if ( _.isObject( choice ) && ! _.isUndefined( choice.text ) && ! _.isUndefined( choice.value ) ) {
						text = choice.text;
						value = choice.value;
					}
					#>
					<option value="{{ value }}">{{ text }}</option>
				<# } ); #>
			</select>

			<input type="number" size="2" maxlength="2" autocomplete="off" class="date-input day" data-component="day" min="1" max="31" />,
			<input type="number" size="4" maxlength="4" autocomplete="off" class="date-input year" data-component="year" min="1000" max="9999" />
			@ <input type="number" size="2" maxlength="2" autocomplete="off" class="date-input hour" data-component="hour" min="0" max="23" />:<?php
			?><input type="number" size="2" maxlength="2" autocomplete="off" class="date-input minute" data-component="minute" min="0" max="59" />
		</div>
		<span class="description customize-control-description">
			<span class="scheduled-countdown"></span>
			<?php
			$tz_string = get_option( 'timezone_string' );
			if ( $tz_string ) {
				$tz = new DateTimezone( get_option( 'timezone_string' ) );
				$formatted_gmt_offset = $this->posts_component->format_gmt_offset( $tz->getOffset( new DateTime() ) / 3600 );
				$tz_name = str_replace( '_', ' ', $tz->getName() );

				/* translators: 1: timezone name, 2: gmt offset  */
				$date_control_description = sprintf( __( 'This site\'s dates are in the %1$s timezone (currently UTC%2$s).', 'customize-posts' ), $tz_name, $formatted_gmt_offset );
			} else {
				$formatted_gmt_offset = $this->posts_component( get_option( 'gmt_offset' ) );

				/* translators: %s: gmt offset  */
				$date_control_description = sprintf( __( 'Dates are in UTC%s.', 'customize-posts' ), $formatted_gmt_offset );
			}
			?>
			<span class="timezone-info"><?php echo esc_html( $date_control_description ); ?></span>
			<button type="button" class="button button-secondary reset-time"><?php esc_html_e( 'Reset to current time', 'customize-posts' ) ?></button>
		</span>
		<?php
	}
}
