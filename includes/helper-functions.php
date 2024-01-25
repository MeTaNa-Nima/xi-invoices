<?php

// Helper Functions
function setMessage($message = "")
{
    global $errorMessage;
    $errorMessage = $message;
}

function showMessage()
{
    global $errorMessage;
    echo $errorMessage;
}

function x_invoice_hide_admin_footer()
{
    // Get the current screen information
    $screen = get_current_screen();

    // Define the slug of your plugin's main admin page
    $plugin_main_page = 'x-invoice';

    if ($screen->parent_file === $plugin_main_page) {
        // If the current page is under your plugin's menu, hide the footer
        echo '<style type="text/css">
            #wpfooter {
                display: none;
            }
        </style>';
    }
}
add_action('admin_footer', 'x_invoice_hide_admin_footer');