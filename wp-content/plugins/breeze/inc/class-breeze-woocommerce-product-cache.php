<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class Breeze_Woocommerce_Product_Cache {
	function __construct() {
		// When a new order is placed.
		add_action( 'woocommerce_checkout_order_processed', array( &$this, 'recreate_cache_for_products' ), 10, 3 );
	}

	/**
	 * When a new order is placed we must re-create the cache for the order
	 * products to refresh the stock value.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @since 1.1.10
	 */
	public function recreate_cache_for_products( $order_id, $posted_data, $order ) {

		if ( ! empty( $order_id ) ) {

			// Checks if the Varnish server is ON.
			$do_varnish_purge = is_varnish_cache_started();

			// fetch the order data.
			$order_id = absint( $order_id );
			$order    = new WC_Order( $order_id );
			// Fetch the order products.
			$items = $order->get_items();

			$product_list_cd = array();

			if ( ! empty( $items ) ) {
				foreach ( $items as $item_id => $item_product ) {
					$product_id = $item_product->get_product_id();

					if ( ! empty( $product_id ) ) {
						$url_path = get_permalink( $product_id );
						$product_list_cd[] = $url_path;
						// Clear Varnish server cache for this URL.
						breeze_varnish_purge_cache( $url_path, $do_varnish_purge );
					}
				}

				if ( ! empty( $product_list_cd ) ) {
					Breeze_CloudFlare_Helper::purge_cloudflare_cache_urls( $product_list_cd );
				}
			}
		}

	}
}


add_action(
	'init',
	function () {
		if ( class_exists( 'WooCommerce' ) ) {
			new Breeze_Woocommerce_Product_Cache();
		}
	}
);


