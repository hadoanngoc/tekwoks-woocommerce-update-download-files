<?php
	// This code should be added in your child-theme functions.php file

	/**
	 * Add all download files of a product to all existing orders which have that product
	 **/
	function add_downloads_to_orders($product_id){
		$product = wc_get_product($product_id);
		if($product){
			$downloads = $product->get_downloads();
			
			$orders = wc_get_orders(array('numberposts' => -1, 'status' => 'completed'));
			foreach($orders as $order){
				$items = $order->get_items();
				$found = false;
				foreach($items as $item){
					$itemproduct        = $item->get_product();
					if($itemproduct->get_id() == $product_id){
						$found = true;
						break;
					}
				}
				if($found){
					$data_store         = WC_Data_Store::load( 'customer-download' );
					$customer_downloads = $data_store->get_downloads(
						array(
							'user_email' => $order->get_billing_email(),
							'order_id'   => $order->get_id(),
							'product_id' => $product_id,
						)
					);
					
					$downloads = $product->get_downloads();

					// make sure we don't add duplicate download files
					foreach ( array_keys( $downloads ) as $download_id ) {
						$found = false;
						foreach ( $customer_downloads as $customer_download ) {
							
							$id = $customer_download->get_download_id();
							if($id == $download_id){
								$found = true;
								break;
							}
						}
						if(!$found){
							wc_downloadable_file_permission( $download_id, $product, $order, $item->get_quantity() );
						}
					}
				}
			}
		}
	}
	
	add_action('init', 'my_init');
	function my_init(){
		add_action('admin_enqueue_scripts', 'my_admin_scripts');
	
		add_action( 'add_meta_boxes', 'wpdocs_register_meta_boxes' );
		
		add_action( 'wp_ajax_update_downloads', 'my_ajax_update_downloads' );
	}
	
	function my_ajax_update_downloads() {
		// Make your response and echo it.
		$product_id = isset($_POST['id']) ? $_POST['id'] : 0;
		if($product_id){
			add_downloads_to_orders($product_id);
		}
		
		echo esc_html__('Update Successful!', 'tekwoks');

		// Don't forget to stop execution afterward.
		wp_die();
	}
	
	function my_admin_scripts( $hook ){
		if ( 'post.php' != $hook ) {
			return;
		}
		
		// make sure jquery is enqueued
		 wp_enqueue_script( 'jquery');
	}
	
	function wpdocs_register_meta_boxes() {
		add_meta_box( 'update-download-file', esc_html__( 'Update Download Files', 'tekwoks' ), 'wpdocs_my_display_callback', 'product', 'side', 'high' );
	}
	
	function wpdocs_my_display_callback(){
		$product_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
		if($product_id){
		?>
		<input name="update_downloads" type="button" class="button button-primary button-large" id="update_downloads" value="Update Downloads">
		<div><p>
			<?php esc_html_e('Scan through all orders and update download files', 'tekwoks');?>
			</p>
		</div>
		<script>
			jQuery('#update_downloads').on('click', function(){
				jQuery('#update_downloads').attr("disabled", "disabled");
				jQuery.post(
					ajaxurl, 
						{
							'action': 'update_downloads',
							'id':   <?php echo $product_id;?>
						}, 
						function(response) {
							alert(response);
							jQuery('#update_downloads').removeAttr("disabled");
						}
					);
				});
		</script>
		<?php
		}
	}