<?php
/**
 * Class for registering settings and sections and for display of the settings form(s).
 */
if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Settings_WP_Fields' ) ):
class WooCommerce_Bulk_Order_Form_Settings_WP_Fields {
	/**
	 * @var float
	 */
	public $version = 2.0;

	/**
	 * Validated settings errors
	 *
	 * @var array
	 */
	public $settings_errors;

	public function __construct( array $errors = array() ) {
		$this->settings_errors = $errors;
	}

	/**
	 * Displays a text input setting field.
	 *
	 * @param array $args
	 */
	public function callback_text( array $args ): void {
		$args['size'] = ( isset( $args['size'] ) && $args['size'] ) ? $args['size'] : 'regular';
		$type         = ! empty( $args['text_type'] ) ? esc_attr( $args['text_type'] ) : 'text';
		$args         = $this->get_arguments( $args ); // escapes all attributes
		$value        = (string) esc_attr( $this->get_option( $args ) );
		$error        = $this->get_setting_error( $args['id'] );
		$disabled     = ( isset( $args['disabled'] ) || ( isset( $args['pro_option'] ) && ! class_exists( 'WooCommerce_Bulk_Order_Form_Pro' ) ) ) ? ' disabled' : '';
		$html         = sprintf( '<input type="%6$s" id="%1$s_%2$s" name="%1$s[%2$s]" value="%3$s"%4$s%5$s %7$s />', $args['section'], $args['id'], $value, $args['attr'], $error, $type, $disabled );

		echo wp_kses( $args['before'] . $html . $args['after'] . $this->description( $args['desc'] ), WC_BOF_ALLOWED_HTML );
	}


	/**
	 * Displays a textarea.
	 *
	 * @param array $args
	 */
	public function callback_textarea( array $args ): void {
		$size     = ( isset( $args['size'] ) && $args['size'] ) ? $args['size'] : 'regular';
		$args     = $this->get_arguments( $args ); // escapes all attributes
		$value    = (string) esc_textarea( $this->get_option( $args ) );
		$error    = $this->get_setting_error( $args['id'] );
		$disabled = ( isset( $args['disabled'] ) || ( isset( $args['pro_option'] ) && ! class_exists( 'WooCommerce_Bulk_Order_Form_Pro' ) ) ) ? ' disabled' : '';
		$html     = sprintf( '<textarea id="%1$s_%2$s" name="%1$s[%2$s]"%4$s%5$s %6$s>%3$s</textarea>', $args['section'], $args['id'], $value, $args['attr'], $error, $disabled );

		echo wp_kses( $args['before'] . $html . $args['after'] . $this->description( $args['desc'] ), WC_BOF_ALLOWED_HTML );
	}


	/**
	 * Displays a select dropdown.
	 *
	 * @param array $args
	 */
	public function callback_select( array $args ): void {
		$args     = $this->get_arguments( $args ); // escapes all attributes
		$value    = array_map( 'esc_attr', array_values( (array) $this->get_option( $args ) ) );
		$multiple = ( preg_match( '/multiple="multiple"/', strtolower( $args['attr'] ) ) ) ? '[]' : '';
		$value    = ( '[]' === $multiple ) ? $value : $value[0];
		$disabled = ( isset( $args['disabled'] ) || ( isset( $args['pro_option'] ) && ! class_exists( 'WooCommerce_Bulk_Order_Form_Pro' ) ) ) ? ' disabled' : '';
		$html     = sprintf( '<select id="%1$s_%2$s" name="%1$s[%2$s]%4$s"%3$s %5$s>', $args['section'], $args['id'], $args['attr'], $multiple, $disabled );
		foreach ( (array) $args['options'] as $opt => $label ) {
			if ( '[]' === $multiple ) {
				$selected = ( in_array( $opt, $value ) ) ? ' selected="selected" ' : '';
			} else {
				$selected = selected( $value, $opt, false );
			}
			$html .= sprintf( '<option value="%s"%s>%s</option>', $opt, $selected, $label );
		}
		$html .= sprintf( '</select>' );
		echo wp_kses( $args['before'] . $html . $args['after'] . $this->description( $args['desc'] ), WC_BOF_ALLOWED_HTML );
	}


	/**
	 * Displays a single checkbox.
	 *
	 * @param array $args
	 */
	public function callback_checkbox( array $args ): void {
		$args     = $this->get_arguments( $args ); // escapes all attributes
		$value    = (string) esc_attr( $this->get_option( $args ) );
		$error    = $this->get_setting_error( $args['id'], ' style="border: 1px solid red; padding: 2px 1em 2px 0; "' );
		$html     = '';
		$disabled = ( isset( $args['disabled'] ) || ( isset( $args['pro_option'] ) && ! class_exists( 'WooCommerce_Bulk_Order_Form_Pro' ) ) ) ? ' disabled' : '';
		$input    = sprintf( '<input type="checkbox" id="%1$s_%2$s" name="%1$s[%2$s]" value="on"%4$s%5$s %6$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ), $args['attr'], $disabled );
		$html     .= sprintf( '<label for="%1$s_%2$s"%5$s>%3$s %4$s</label>', $args['section'], $args['id'], $input, $args['desc'], $error );

		echo wp_kses( $html, WC_BOF_ALLOWED_HTML );
	}


	/**
	 * Displays multiple checkboxes.
	 *
	 * @param array $args
	 */
	public function callback_multicheckbox( array $args ): void {
		$args  = $this->get_arguments( $args ); // escapes all attributes
		$value = array_map( 'esc_attr', array_values( (array) $this->get_option( $args ) ) );
		$count = count( $args['options'] );
		$html  = '<fieldset>';
		$i     = 0;
		foreach ( (array) $args['options'] as $opt => $label ) {
			$error    = $this->get_setting_error( $opt, ' style="border: 1px solid red; padding: 2px 1em 2px 0; "' );
			$checked  = ( in_array( $opt, $value ) ) ? ' checked="checked" ' : '';
			$disabled = ( isset( $args['disabled'] ) || ( isset( $args['pro_option'] ) && ! class_exists( 'WooCommerce_Bulk_Order_Form_Pro' ) ) ) ? ' disabled' : '';
			$input    = sprintf( '<input type="checkbox" id="%1$s_%2$s_%3$s" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s%5$s %6$s />', $args['section'], $args['id'], $opt, $checked, $args['attr'], $disabled );
			$html     .= sprintf( '<label for="%1$s_%2$s_%4$s"%6$s>%3$s %5$s</label>', $args['section'], $args['id'], $input, $opt, $label, $error );
			$html     .= ( isset( $args['row_after'][ $opt ] ) && $args['row_after'][ $opt ] ) ? $args['row_after'][ $opt ] : '';
			$html     .= ( ++$i < $count ) ? '<br/>' : '';
		}

		$html .= '</fieldset>' . $this->description( $args['desc'] );
		echo wp_kses( $html, WC_BOF_ALLOWED_HTML );
	}


	/**
	 * Displays radio buttons.
	 *
	 * @param array $args
	 */
	public function callback_radio( array $args ): void {
		$args    = $this->get_arguments( $args ); // escapes all attributes
		$value   = (string) esc_attr( $this->get_option( $args ) );
		$options = array_keys( (array) $args['options'] );
		// make sure one radio button is checked
		if ( empty( $value ) && ( isset( $options[0] ) && $options[0] ) ) {
			$value = $options[0];
		} elseif ( ! empty( $value ) && ( isset( $options[0] ) && $options[0] ) ) {
			if ( ! in_array( $value, $options ) ) {
				$value = $options[0];
			}
		}
		$html  = '<fieldset>';
		$i     = 0;
		$count = count( $args['options'] );
		foreach ( (array) $args['options'] as $opt => $label ) {
			$disabled = ( isset( $args['disabled'] ) || ( isset( $args['pro_option'] ) && ! class_exists( 'WooCommerce_Bulk_Order_Form_Pro' ) ) ) ? ' disabled' : '';
			$input    = sprintf( '<input type="radio" id="%1$s_%2$s_%3$s" name="%1$s[%2$s]" value="%3$s"%4$s%5$s %6$s />', $args['section'], $args['id'], $opt, checked( $value, $opt, false ), $args['attr'], $disabled );
			$html     .= sprintf( '<label for="%1$s_%2$s_%4$s">%3$s%5$s</label>', $args['section'], $args['id'], $input, $opt, ' <span>' . $label . '</span>' );
			$html     .= ( isset( $args['row_after'][ $opt ] ) && $args['row_after'][ $opt ] ) ? $args['row_after'][ $opt ] : '';
			$html     .= ( ++ $i < $count ) ? '<br/>' : '';
		}

		echo wp_kses( '</fieldset>' . $html . $this->description( $args['desc'] ), WC_BOF_ALLOWED_HTML );
	}


	/**
	 * Displays type 'content' field.
	 *
	 * @param array $args
	 */
	public function callback_content( array $args ): void {
		if ( isset( $args['content'] ) ) {
			echo wp_kses( $args['content'], WC_BOF_ALLOWED_HTML );
		}
		if ( isset( $args['desc'] ) ) {
			echo wp_kses( $this->description( $args['desc'] ), WC_BOF_ALLOWED_HTML );
		}
	}


	/**
	 * Displays field with the action hook '{$page_hook}_add_extra_field'.
	 *
	 * @param array $args
	 */
	function callback_extra_field( array $args ): void {
		if ( isset( $args['callback'] ) && $args['callback'] ) {
			if ( isset( $args['page_hook'] ) && $args['page_hook'] ) {
				do_action( $args['page_hook'] . '_add_extra_field', $args );
			}
		}
	}


	/**
	 * Returns a field description.
	 *
	 * @param string $desc Description of field.
	 */
	public function description( string $desc = '' ): string {
		if ( $desc ) {
			return sprintf( '<p class="description">%s</p>', $desc );
		}

		return '';
	}


	/**
	 * Returns validation errors for a settings field.
	 *
	 * @param string $setting_id Settings field ID.
	 * @param string $attr Style to override the default error style.
	 *
	 * @return string Empty string or inline style attribute.
	 */
	protected function get_setting_error( string $setting_id, string $attr = '' ): string {
		$display_error = '';

		if ( ! empty( $this->settings_errors ) ) {
			foreach ( $this->settings_errors as $error ) {
				if ( isset( $error['setting'] ) && $error['setting'] === $setting_id ) {
					if ( '' === $attr ) {
						// todo: don't use inline styles
						$display_error = ' style="border: 1px solid red;"';
					} else {
						$display_error = $attr;
					}
				}
			}
		}

		return $display_error;
	}


	/**
	 * Escapes and creates additional attributes for a setting field.
	 *
	 * @param string|array $args Arguments of a setting field.
	 * @param string|bool  $class Type of field.
	 *
	 * @return array All arguments and attributes
	 */
	protected function get_arguments( $args = '', $class = false ): array {
		// escape section, id and options used in attributes
		$args['section'] = esc_attr( $args['section'] );
		$args['id']      = esc_attr( $args['id'] );

		if ( isset( $args['options'] ) && $args['options'] ) {
			$options = array();
			foreach ( (array) $args['options'] as $key => $value ) {
				$options[ esc_attr( $key ) ] = $value;
			}
			$args['options'] = $options;
		}

		// additional parameters
		$attr_string = '';
		$defaults    = $attr = array();

		if ( isset( $args['attr'] ) && $args['attr'] ) {
			$attr = $args['attr'];
		}

		// set defaults for a textarea field
		if ( 'textarea' === $args['type'] ) {
			$defaults = array( 'rows' => '5', 'cols' => '55' );
		}

		// todo: add action to add additional defaults

		$attr['class'] = isset( $attr['class'] ) ? trim( $attr['class'] ) : '';

		if ( isset( $args['size'] ) && $args['size'] ) {
			if ( 'text' === $args['type'] || 'textarea' === $args['type'] ) {
				$attr['class'] .= sprintf( ' %1$s-%2$s', $args['size'], $args['type'] );
			}
		}


		if ( $class ) {
			if ( ! preg_match( '/\s' . preg_quote( (string) $class, '/' ) . '\s/', $attr['class'] ) ) {
				$attr['class'] = ' ' . (string) $class;
			}
		}

		if ( '' === $attr['class'] ) {
			unset( $attr['class'] );
		}

		// create attribute string
		foreach ( $attr as $key => $arg ) {
			$arg         = ( 'class' === $arg ) ? sanitize_html_class( $arg ) : esc_attr( $arg );
			$attr_string .= ' ' . trim( $key ) . '="' . trim( $arg ) . '"';
		}

		$args['attr'] = $attr_string;

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return string|array
	 */
	public function get_option( array $args ) {
		if ( isset( $args['value'] ) ) {
			return $args['value'];
		}

		// get the value for the setting field from the database
		$options = get_option( $args['section'] );

		// return the value if it exists
		if ( isset( $options[ $args['id'] ] ) ) {
			return $options[ $args['id'] ];
		}

		// return the default value
		return ( isset( $args['default'] ) ) ? $args['default'] : '';
	}

} // class WooCommerce_Bulk_Order_Form_Settings_WP_Fields

endif; // class exists