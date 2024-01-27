<?php
// File: includes/customers/class-xi-invoices-customers.php

class Xi_Invoices_Customers {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'x_invoice_customers';
    }

    // Add a new customer
    public function add_customer($data) {
        return $this->wpdb->insert($this->table_name, $data);
    }

    // Update an existing customer
    public function update_customer($customer_id, $data) {
        return $this->wpdb->update($this->table_name, $data, array('customer_id' => $customer_id));
    }

    // Retrieve a customer by ID
    public function get_customer($customer_id) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE customer_id = %d", $customer_id));
    }
    public function get_customer_by_national_id($national_id) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE customer_national_id = %s", $national_id));
    }

    // Retrieve all customers
    public function get_all_customers() {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name");
    }
    

    // Delete a customer
    public function delete_customer($customer_id) {
        return $this->wpdb->delete($this->table_name, array('customer_id' => $customer_id));
    }
}
