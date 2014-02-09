<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>
<div id="mc4wp-fw" class="mc4wp-well">

	<h4 class="mc4wp-title">Add a new field</h4>

	
	<p class="mc4wp-notice no-lists-selected" <?php if(!empty($opts['lists'])) { ?>style="display: none;" <?php } ?>>Select at least one list first.</p>

	<p>Use the tool below to generate the HTML for your form fields.</p>
	<p>
		<select class="widefat" id="mc4wp-fw-mailchimp-fields">
			<option class="default" value="" disabled selected>Select MailChimp field..</option>
			<optgroup label="MailChimp merge fields" class="merge-fields"></optgroup>
			<optgroup label="Interest groupings" class="groupings"></optgroup>
			<optgroup label="Other" class="other">
				<option class="default" value="submit">Submit button</option>
				<option class="default" disabled>(PRO ONLY) Lists Choice</option>				
			</optgroup>
		</select>
	</p>

	<div id="mc4wp-fw-fields">

		<p class="row label">
			<label for="mc4wp-fw-label">Label <small>(optional)</small></label>
			<input class="widefat" type="text" id="mc4wp-fw-label" />
		</p>

		<p class="row placeholder">
			<label for="mc4wp-fw-placeholder">Placeholder <small>(optional)</small></label>
			<input class="widefat" type="text" id="mc4wp-fw-placeholder" />
		</p>

		<p class="row value">
			<label for="mc4wp-fw-value"><span id="mc4wp-fw-value-label">Initial value <small>(optional)</small></span></label>
			<input class="widefat" type="text" id="mc4wp-fw-value" />
		</p>

		<p class="row values" id="mc4wp-fw-values">
			<label for="mc4wp-fw-values">Value labels <small>(leave empty to hide)</small></label>
		</p>

		<p class="row wrap-p">
			<input type="checkbox" id="mc4wp-fw-wrap-p" value="1" checked /> 
			<label for="mc4wp-fw-wrap-p">Wrap in paragraph (<code>&lt;p&gt;</code>) tags?</label>
		</p>

		<p class="row required">
			<input type="checkbox" id="mc4wp-fw-required" value="1" /> 
			<label for="mc4wp-fw-required">Required field?</label>
		</p>

		<p>
			<input class="button button-large" type="button" id="mc4wp-fw-add-to-form" value="&laquo; add to form" />
		</p>

		<p>
			<label for="mc4wp-fw-preview">Generated HTML</label>
			<textarea class="widefat" id="mc4wp-fw-preview" rows="5"></textarea>
		</p>

	</div>
</div>