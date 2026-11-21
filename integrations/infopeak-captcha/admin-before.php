<?php

defined('ABSPATH') || exit;

printf(
    // translators: %1$s is opening anchor tag, %2$s is closing anchor tag.
    esc_html__(
        '%1$s InfoPeak Captcha %2$s is an EU-hosted captcha without cookies or image puzzles. The visitor\'s browser solves a small proof-of-work challenge in the background while the form is being filled in.',
        'mailchimp-for-wp'
    ),
    '<a href="https://infopeak.io/captcha" target="_blank">',
    '</a>'
);
