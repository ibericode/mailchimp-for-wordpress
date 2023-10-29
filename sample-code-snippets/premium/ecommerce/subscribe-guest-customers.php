<?php
// Register command when running cli
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	if ( class_exists( 'WP_CLI' ) && class_exists( 'WP_CLI_Command' ) ) {
		class MC4WP_Ecommerce_Subscribe_Guest_Customers_Command extends WP_CLI_Command {
			/**
			 * Subscribes email address found in guest orders to a MailChimp list
			 *
			 * @param $args
			 * @param $assoc_args
			 *
			 * ## OPTIONS
			 *
			 * <list_id>
			 * : The MailChimp list ID to subscribe guest customers to
			 *
			 * ## EXAMPLES
			 *
			 *     wp mc4wp-ecommerce-subscribe-guest-customers run
			 *
			 * @subcommand run
			 */
			public function add_order( $args, $assoc_args = array() ) {
				if ( empty( $assoc_args['list_id'] ) ) {
					WP_CLI::error( "Please provide a list_id argument." );
					return;
				}
				
				$query = new WC_Order_Query( array(
					'type'     => array( 'shop_order' ),
					'customer' => 0,
					'limit'    => isset( $assoc_args['limit'] ) ? $assoc_args['limit'] : '-1',
					'status'   => 'completed',
					'orderby'  => isset( $assoc_args['orderby'] ) ? $assoc_args['orderby'] : 'date',
					'order'    => isset( $assoc_args['order'] ) ? $assoc_args['order'] : 'ASC',
					'page'     => isset( $assoc_args['page'] ) ? $assoc_args['page'] : '1',
					'offset'   => isset( $assoc_args['offset'] ) ? $assoc_args['offset'] : null,
					'return'   => 'ids',
				) );
				
				$guest_orders = $query->get_orders();
				
				$total_guest_orders = count( $guest_orders );
				WP_CLI::log( sprintf( '%d guest orders found.', $total_guest_orders ) );

				$mailchimp = new MC4WP_MailChimp();
				$mailchimp_list_id = $assoc_args['list_id'];
				$args = array(
					'status' 		=> 'pending', // default: "pending", set to "subscribed" to skip double opt-in (not recommended)
				);
				
				$progress = \WP_CLI\Utils\make_progress_bar( 'Progress Bar', $total_guest_orders );
				
				foreach ( $guest_orders as $order_id ) {
					
					try {
						$order = wc_get_order( $order_id );
						$email_address = $order->get_billing_email();
						
						if ( empty( $email_address ) ) {
							$this->get_log()->info( sprintf( 'WP CLI Guest Orders: Skipping guest order #%d because it has no billing_email property.', $order->get_order_number() ) );
							continue;
						}
						
						// query MailChimp to see if this guest is subscribed
						if ( $mailchimp->list_has_subscriber( $mailchimp_list_id, $email_address ) ) {
							$this->get_log()->info( sprintf( 'WP CLI Guest Orders: %s is already subscribed.', $email_address ) );
							$progress->tick();
							
							usleep(300); // 300ms delay to prevent being rate-limited by MailChimp
							continue;
						}
						
						// if not already on the list, subscribe this guest customer as a new subscriber
						$args['merge_fields'] = array(
							'FNAME' => $order->get_billing_first_name(),
							'LNAME' => $order->get_billing_last_name(),
						);
						$mailchimp->list_subscribe( $mailchimp_list_id, $email_address, $args );
						$this->get_log()->info( sprintf( 'WP CLI Guest Orders: Subscribed %s', $email_address ) );
						
						// increment progress bar
						$progress->tick();
					} catch ( Exception $e ) {
						$this->get_log()->info( sprintf( 'WP CLI Guest Orders: Exception: %s Order ID: %s', $e->getMessage() ) );
					}
					
				}
				
				$progress->finish();
				
				WP_CLI::success( 'Done.' );
			}
			
			/**
			 * @return MC4WP_Debug_Log
			 */
			private function get_log() {
				return mc4wp( 'log' );
			}
			
		}
	}
	WP_CLI::add_command( 'mc4wp-ecommerce-subscribe-guest-customers', 'MC4WP_Ecommerce_Subscribe_Guest_Customers_Command' );
}
