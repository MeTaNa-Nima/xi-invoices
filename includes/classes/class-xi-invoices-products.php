<?php

class Xi_Invoices_Products {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'x_invoice_products';
    }

    // Add a new product
    public function add_product($data) {
        return $this->wpdb->insert($this->table_name, $data);
    }

    // Update an existing product
    public function update_product($product_id, $data) {
        return $this->wpdb->update($this->table_name, $data, array('product_id' => $product_id));
    }

    // Retrieve a product by ID
    public function get_product($product_id) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE product_id = %d", $product_id));
    }
    public function get_product_by_name($product_name) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE product_name = %s", $product_name));
    }

    // Retrieve all products
    public function get_all_products() {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name");
    }
    

    // Delete a product
    public function delete_product($product_id) {
        return $this->wpdb->delete($this->table_name, array('product_id' => $product_id));
    }
}
