<?php
require_once('settings.php');

function x_invoices_page() {
    if (isset($_GET['page']) && $_GET['page'] === 'xi-invoices') {
        $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;

        if (!$invoice_id) {
            // Call function to show all invoices
            xi_invoice_show_all();

        } elseif ($invoice_id && !isset($_GET['edit_mode'])) {
            // Function to view single selected invoice
            xi_invoice_show_single();

        } elseif ($invoice_id && isset($_GET['edit_mode']) && $_GET['edit_mode'] == 1) {
            // Function to edit single selected invoice
            xi_invoice_edit_single();
        } else {
            echo 'Invalid action or Invoice ID.';
        }
    } else {
        echo 'Invalid page parameter.';
    }
}