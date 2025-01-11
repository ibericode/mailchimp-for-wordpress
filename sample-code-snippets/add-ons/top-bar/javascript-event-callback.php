<?php

add_action('wp_footer', function () {
    ?>
    <script>
        document.querySelector('#mailchimp-top-bar form').addEventListener('submit', function() {
            // your code goes here
        });
    </script>
    <?php
}, 20);
