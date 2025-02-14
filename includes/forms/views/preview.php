<?php
defined('ABSPATH') or exit;

// fake post to prevent notices in wp_enqueue_scripts call
$GLOBALS['post'] = new \WP_Post((object) [ 'filter' => 'raw' ]);
$GLOBALS['wp_query'] = new \WP_Query();

// render simple page with form in it.
?><!DOCTYPE html>
<html>
<head>
    <title>Mailchimp for WordPress Form Preview</title>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
    <?php
    wp_head();
    ?>
    <style>
        html,
        body{
            background: white;
            width: 100%;
            text-align: left;
        }
        <?php // hide all other elements except the form preview ?>
        html::before,
        html::after,
        body::before,
        body::after,
        body > *:not(#form-preview) {
            display:none !important;
        }
        #form-preview {
            display: block !important;
            width: 100%;
            height: 100%;
            padding: 20px;
            border: 0;
            margin: 0;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="page-template-default page">
    <div id="form-preview" class="page type-page status-publish hentry post post-content">
        <?php mc4wp_show_form($form_id); ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
