<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class Breeze_Woocommerce_Product_Cache {
	function __construct() {
		// When a new order is placed.
		add_action( 'woocommerce_checkout_order_processed', array( &$this, 'recreate_cache_for_products' ), 99, 3 );
		add_action( 'woocommerce_order_status_changed', array( &$this, 'recreate_cache_for_products' ), 99, 3 );
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
			$change_stock_hide = false;
			$is_no_stock       = false;
			// yes === Hide out of stock items from the catalog
			if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
				$change_stock_hide = true;
			}

			if ( ! empty( $items ) ) {
				foreach ( $items as $item_id => $item_product ) {
					$product_id = $item_product->get_product_id();
					$product    = wc_get_product( absint( $product_id ) );
					$stock_no   = intval( $product->get_stock_quantity() ) - 1;

					if ( true === $product->managing_stock() && 1 > $stock_no ) { // if stock is 0
						$is_no_stock = true;
					}

					if ( ! empty( $product_id ) ) {
						$url_path = get_permalink( $product_id );
						$product_list_cd[] = $url_path;
						// Clear Varnish server cache for this URL.
						breeze_varnish_purge_cache( $url_path, $do_varnish_purge );
					}
				}

				if ( true === $is_no_stock && true === $change_stock_hide ) {

					$home_url          = trailingslashit( home_url() );
					$shop_page         = trailingslashit( wc_get_page_permalink( 'shop' ) );
					$product_list_cd[] = $home_url;
					$product_list_cd[] = $shop_page;

					breeze_varnish_purge_cache( $home_url, $do_varnish_purge );
					breeze_varnish_purge_cache( $shop_page, $do_varnish_purge );
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


