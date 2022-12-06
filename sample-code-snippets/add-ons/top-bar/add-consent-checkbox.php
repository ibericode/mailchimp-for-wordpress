<?php 

add_action( 'mctb_before_submit_button', function() {
	echo '<input name="AGREE_TO_TERMS" type="checkbox" value="1" required=""> I have read and agree to the terms &amp; conditions';
});
