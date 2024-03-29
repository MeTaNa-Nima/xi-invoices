<?php
class Xi_Invoices_Invoice {
    private $wpdb;
    private $operation_data_table;
    private $data_lookup_table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->operation_data_table = $wpdb->prefix . 'x_invoice_operation_data';
        $this->data_lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    }

    public function add_invoice($data) {
        if ($this->wpdb->insert($this->operation_data_table, $data)) {
            return $this->wpdb->insert_id;
        }
        return false;
    }

    // Method to get the ID of the last inserted invoice
    public function get_last_inserted_invoice_id() {
        return $this->wpdb->get_var("SELECT invoice_id FROM {$this->operation_data_table} ORDER BY invoice_id DESC LIMIT 1");
    }

    public function add_product_details($invoice_id, $product_details) {
        foreach ($product_details as $product) {
            $product['order_id'] = $invoice_id;
            $this->wpdb->insert($this->data_lookup_table, $product);
        }
    }

    public function add_product_details_debug($product_details) {
        $this->wpdb->insert($this->data_lookup_table, $product_details);
    }

    public function update_order_id($invoice_id) {
        return $this->wpdb->update(
            $this->operation_data_table,
            array('order_id' => $invoice_id),
            array('invoice_id' => $invoice_id)
        );
    }

    public function update_invoice_pdf_url($invoice_id, $pdfUrl) {
        return $this->wpdb->update(
            $this->operation_data_table,
            array('invoice_pdf_link'    => $pdfUrl),
            array('invoice_id'          => $invoice_id)
        );
    }

    // Getting Data
    public function get_invoice($invoice_id) {
        $invoice = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->operation_data_table WHERE invoice_id = %d", $invoice_id));

        if (!$invoice) {
            return null; // No invoice found
        }
        return $invoice;
    }

    public function get_all_invoices() {
        $invoices = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $this->operation_data_table"));
        if (!$invoices) {
            return null; // No invoice found
        }
        return $invoices;
    }

    // Method to get customer details for a given invoice
    public function get_customer_details($invoice_id) {
        $customer_details = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT c.* FROM {$this->wpdb->prefix}x_invoice_customers c
            JOIN {$this->operation_data_table} op ON c.customer_id = op.customer_id
            WHERE op.invoice_id = %d",
            $invoice_id
        ));

        return $customer_details ?: null; // Return the customer details or null if not found
    }

    // Method to get invoice details by ID
    public function get_invoice_details($invoice_id) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT op.*, c.customer_name, c.customer_national_id, c.customer_mobile_no, c.customer_address, c.customer_shop_name, u.display_name as visitor_name
            FROM {$this->operation_data_table} op
            JOIN {$this->wpdb->prefix}x_invoice_customers c ON op.customer_id = c.customer_id
            LEFT JOIN {$this->wpdb->users} u ON op.visitor_id = u.ID
            WHERE op.invoice_id = %d",
            $invoice_id
        ));
    }

    // Method to get product details for an invoice
    public function get_product_details($invoice_id, $sale_return_flag = 'sold') {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT dl.*, p.product_name
            FROM {$this->wpdb->prefix}x_invoice_data_lookup dl
            JOIN {$this->wpdb->prefix}x_invoice_products p ON dl.product_id = p.product_id
            WHERE dl.order_id = %d AND dl.product_sale_return = %s",
            $invoice_id, $sale_return_flag
        ));
    }

    // Method to get invoices by a specific user ID (visitor_id)
    public function get_invoices_by_user_id($user_id, $page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->operation_data_table} WHERE visitor_id = %d LIMIT %d, %d",
            $user_id, $offset, $per_page
        );
        return $this->wpdb->get_results($sql);
    }

    // Method to get the total count of invoices for a specific user
    public function get_total_invoices_count_by_user($user_id) {
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->operation_data_table} WHERE visitor_id = %d",
            $user_id
        );
        return (int) $this->wpdb->get_var($sql);
    }

    // Method to get paginated invoices
    public function get_paginated_invoices($page_number = 1, $per_page = 20) {
        $offset = ($page_number - 1) * $per_page;
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->operation_data_table} ORDER BY invoice_id DESC LIMIT %d, %d",
            $offset, $per_page
        );
        return $this->wpdb->get_results($query);
    }

    // Method to get total number of invoices
    public function get_total_invoices_count() {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->operation_data_table}");
    }

    // Method to get invoices by a specific user ID (visitor_id)
    public function get_paginated_invoices_by_user_id($user_id, $page_number = 1, $per_page = 20) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->operation_data_table} WHERE visitor_id = %d",
            $user_id
        );
        return $this->wpdb->get_results($sql);
    }

    // Method to update invoice
    public function update_invoice($invoice_id, $data) {
        return $this->wpdb->update($this->operation_data_table, $data, array('invoice_id' => $invoice_id));
    }

    // Method to update product details
    public function update_product_details($invoice_id, $product_details) {
        foreach ($product_details as $product) {
            $product['order_id'] = $invoice_id;
            $this->wpdb->insert($this->data_lookup_table, $product);
        }
    }

    // Method to get invoices by a specific customer ID
    public function get_invoices_by_customer_id($customer_id, $page_number = 1, $per_page = 20) {
        $offset = ($page_number - 1) * $per_page;
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->operation_data_table} WHERE customer_id = %d ORDER BY invoice_id DESC LIMIT %d, %d",
            $customer_id, $offset, $per_page
        );
        return $this->wpdb->get_results($sql);
    }

    // Method to get the total count of invoices for a specific customer
    public function get_total_invoices_count_by_customer($customer_id) {
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->operation_data_table} WHERE customer_id = %d",
            $customer_id
        );
        return (int) $this->wpdb->get_var($sql);
    }
}
