<?php
/**
 * Whish Money – manual transfer gateway (order goes "on hold" until the transfer is confirmed).
 */
defined( 'ABSPATH' ) || exit;

class Pointu_Gateway_Whish extends WC_Payment_Gateway {
	public $instructions;
	public $whish_number;

	public function __construct() {
		$this->id                 = 'pointu_whish';
		$this->method_title       = __( 'Whish Money', 'pointu' );
		$this->method_description = __( 'Customer transfers the total to your Whish Money number. The order is placed "On hold" until you confirm the transfer.', 'pointu' );
		$this->has_fields         = false;
		$this->init_form_fields();
		$this->init_settings();
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions' );
		$this->whish_number = $this->get_option( 'whish_number' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array( 'title' => __( 'Enable', 'pointu' ), 'type' => 'checkbox', 'label' => __( 'Enable Whish Money', 'pointu' ), 'default' => 'yes' ),
			'title'        => array( 'title' => __( 'Title', 'pointu' ), 'type' => 'text', 'default' => __( 'Whish Money transfer', 'pointu' ) ),
			'whish_number' => array( 'title' => __( 'Whish number', 'pointu' ), 'type' => 'text', 'default' => '+961 79 067 677' ),
			'description'  => array( 'title' => __( 'Checkout description', 'pointu' ), 'type' => 'textarea', 'default' => __( 'Transfer the order total via Whish Money. Your order will be confirmed once we receive the transfer.', 'pointu' ) ),
			'instructions' => array( 'title' => __( 'Thank-you page / email instructions', 'pointu' ), 'type' => 'textarea', 'default' => __( 'Please send the order total to our Whish Money number and share the receipt with us on WhatsApp, mentioning your order number.', 'pointu' ) ),
		);
	}

	private function instructions_html() {
		return '<div class="pt-whish-box"><p>' . wp_kses_post( wpautop( $this->instructions ) ) . '</p><p><strong>' . esc_html__( 'Whish number:', 'pointu' ) . '</strong> ' . esc_html( $this->whish_number ) . '</p></div>';
	}

	public function thankyou() {
		echo $this->instructions_html(); // phpcs:ignore
	}

	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			echo $plain_text ? esc_html( $this->instructions . ' ' . $this->whish_number ) . PHP_EOL : $this->instructions_html(); // phpcs:ignore
		}
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', __( 'Awaiting Whish Money transfer.', 'pointu' ) );
		wc_reduce_stock_levels( $order_id );
		WC()->cart->empty_cart();
		return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
	}
}
