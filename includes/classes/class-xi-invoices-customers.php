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
        $data['visitor_id'] = get_current_user_id();
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
    public function get_customer_by_mobile_no($mobile_no) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE customer_mobile_no = %s", $mobile_no));
    }

    // Retrieve all customers
    public function get_all_customers() {
        $visitor_id = get_current_user_id();
        return $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE visitor_id = %d", $visitor_id));
    }
    

    // Delete a customer
    public function delete_customer($customer_id) {
        return $this->wpdb->delete($this->table_name, array('customer_id' => $customer_id));
    }
}
