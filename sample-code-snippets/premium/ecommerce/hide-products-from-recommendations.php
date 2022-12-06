<?php

add_filter( 'mc4wp_ecommerce_product_data', function( $product_data ) {
   $disabled_products = array( 
      500, 
      505, 
      510 
   );

   // don't change anything if data is for a product not in the above array
   if( ! in_array( $product_data['id'], $disabled_products ) ) {
      return $product_data;
   }

   // set inventory to 0 for disabled products
   foreach( $product_data['variants'] as $key => $variant_data ) {
      $product_data['variants'][$key]['inventory_quantity'] = 0;
   }

   return $product_data;
});
