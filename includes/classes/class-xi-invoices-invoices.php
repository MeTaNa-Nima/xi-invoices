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
        $query = "SELECT invoice_id FROM {$this->operation_data_table} ORDER BY invoice_id DESC LIMIT 1";
        $result = $this->wpdb->get_row($query);
        return $result ? $result->invoice_id : null;
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
}
