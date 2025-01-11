<?php

/*
With the form setting "Enable autocomplete for email domains?" is set to Yes, domain name automcompletions are suggested when typing an email address into the email field of the form.

With the filter 'mc4wp_forms_email_domain_suggestions' you can overwrite what domains are suggested. The expected return for the filter is an array of domain names.

Note that the order of the domains in the array of domains is the order in which they are suggested.
*/


//To add 1 domain to the suggestions without overwriting the others
add_filter(
    'mc4wp_forms_email_domain_suggestions',
    function ($domains) {
        $domains[] = 'thisdomainisaddedtothesuggestions.com';
        return $domains;
    }
);


//To repleace the suggestions with your own
add_filter(
    'mc4wp_forms_email_domain_suggestions',
    function ($domains) {
        $domains = [
            "gmail.com",
            "yahoo.com",
            "hotmail.com",
            "aol.com"
        ];
        return $domains;
    }
);
