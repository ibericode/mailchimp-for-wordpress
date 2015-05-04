<?php

interface iMC4WP_Request {
	public function prepare();
	public function validate();
	public function process();
	public function respond();
}