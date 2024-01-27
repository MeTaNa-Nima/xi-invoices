<?php
// File: includes/invoices/class-xi-invoices-invoices.php

class Xi_Invoices_Invoices {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'x_invoice_invoices';
    }
    
    
    /* */
    
    
    // Add a new invoice
    public function add_invoice($data) {
        return $this->wpdb->insert($this->table_name, $data);
    }

    // Update an existing invoice
    public function update_invoice($invoice_id, $data) {
        return $this->wpdb->update($this->table_name, $data, array('invoice_id' => $invoice_id));
    }

    // Retrieve a invoice by ID
    public function get_invoice($invoice_id) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE invoice_id = %d", $invoice_id));
    }
    public function get_invoice_by_national_id($national_id) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE invoice_national_id = %s", $national_id));
    }

    // Retrieve all invoices
    public function get_all_invoices() {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name");
    }
    

    // Delete a invoice
    public function delete_invoice($invoice_id) {
        return $this->wpdb->delete($this->table_name, array('invoice_id' => $invoice_id));
    }
}
