<div id="mc4wp-fw" class="mc4wp-well">

	<h4>Add a new field</h4>
	<p>Use the tool below to help you add fields to your form mark-up.</p>
	<p>
		<select class="widefat" id="mc4wp-fw-mailchimp-fields">
			<option class="default" value="" disabled selected>Select MailChimp field..</option>
			<optgroup label="MailChimp merge fields" class="merge-fields"></optgroup>
			<optgroup label="Interest groupings" class="groupings"></optgroup>
			<optgroup label="Other" class="other">
				<option class="default" value="submit">Submit button</option>				
			</optgroup>
		</select>
	</p>

	<div id="mc4wp-fw-fields">

		<p class="row label">
			<label for="mc4wp-fw-label">Label <small>(optional)</small></label>
			<input class="widefat" type="text" id="mc4wp-fw-label" />
		</p>

		<p class="row placeholder">
			<label for="mc4wp-fw-placeholder">Placeholder <small>(optional, HTML5)</small></label>
			<input class="widefat" type="text" id="mc4wp-fw-placeholder" />
		</p>

		<p class="row value">
			<label for="mc4wp-fw-value"><span id="mc4wp-fw-value-label">Initial value <small>(optional)</small></span></label>
			<input class="widefat" type="text" id="mc4wp-fw-value" />
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
			<textarea class="widefat" id="mc4wp-fw-preview" rows="5"></textarea>
		</p>

		<p>
			<input class="button button-large" type="button" id="mc4wp-fw-add-to-form" value="&laquo; add to form" />
		</p>

	</div>
</div>