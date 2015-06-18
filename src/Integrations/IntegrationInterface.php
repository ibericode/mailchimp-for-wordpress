<?php

interface MC4WP_Integration_Interface {

	public function __construct( array $options );

	public function add_hooks();
}