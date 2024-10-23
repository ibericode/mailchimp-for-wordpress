<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Procaptcha
 *
 * @private
 */
class MC4WP_Procaptcha
{
	/**
	 * @var MC4WP_Procaptcha
	 */
	private static $instance;

	/**
	 * @var bool
	 */
	private $is_in_use;

	private function __construct()
	{
		$this->is_in_use = false;
	}

	/**
	 * @return MC4WP_Procaptcha
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return void
	 */
	public function render_captcha_element()
	{
		$this->is_in_use = true;

		echo '<mc4wp-procaptcha><div class="prosopo-procaptcha"></div></mc4wp-procaptcha>';
	}

	/**
	 * @param array<string,mixed> $attributes
	 * @return void
	 */
	public function render_captcha_js($attributes)
	{
		echo '<script type="module" async defer src="https://js.prosopo.io/js/procaptcha.bundle.js"></script>';
		?>
		<script data-name="prosopo-procaptcha-element" type="module">
			let attributes = JSON.parse('<?php echo wp_json_encode($attributes); ?>');

			class MC4WPProcaptcha extends HTMLElement{
				connectedCallback() {
					// wait window.load to make sure 'window.procaptcha' is available.
					"complete" !== document.readyState ?
						window.addEventListener("load", this.setup.bind(this)) :
						this.setup();
				}

				validatedCallback(output){
					// todo validation element
				}

				setup() {
					attributes.callback = this.validatedCallback.bind(this);

					window.procaptcha.render(this.querySelector('.prosopo-procaptcha'),attributes);
				}
			}

			customElements.define("mc4wp-procaptcha", MC4WPProcaptcha);
		</script>
		<?php
	}
}
