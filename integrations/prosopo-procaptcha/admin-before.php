<?php

// translators: %1$s is opening anchor tag, %2$s is closing anchor tag
echo sprintf(
    esc_html__(
        '%1$s Procaptcha (by Prosopo) %2$s is offering seamless bot protection without compromising user data. You can customize settings and algorithms, ensuring optimal defense against all types of malicious bots.',
        'mailchimp-for-wp'
    ),
    '<a href="https://prosopo.io/" target="_blank">',
    '</a>'
);
