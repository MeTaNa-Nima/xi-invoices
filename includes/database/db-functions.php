<?php



function x_invoice_create_or_update_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Define table names
    $products_table = $wpdb->prefix . 'x_invoice_products';
    $customers_table = $wpdb->prefix . 'x_invoice_customers';
    $operation_data_table = $wpdb->prefix . 'x_invoice_operation_data';
    $data_lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';


    // Use dbDelta for creating or updating tables
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Table creation or modification queries
    $sql_products = "CREATE TABLE $products_table (
        product_id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_name varchar(255) NOT NULL,
        PRIMARY KEY  (product_id)
    ) $charset_collate;";

    // Table for invoice_customers
    $sql_customers = "CREATE TABLE $customers_table (
        customer_id mediumint(9) NOT NULL AUTO_INCREMENT,
        customer_name varchar(255) NOT NULL,
        customer_national_id varchar(255) NOT NULL,
        customer_address text NOT NULL,
        customer_shop_name varchar(255) NOT NULL,
        PRIMARY KEY  (customer_id)
    ) $charset_collate;";

    // Table for invoice_operation_data
    $sql_operation_data = "CREATE TABLE $operation_data_table (
        invoice_id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id mediumint(9) NOT NULL,
        order_include_tax varchar(3) NOT NULL,
        order_total_tax decimal(10,2),
        order_include_discount varchar(3) NOT NULL,
        discount_method varchar(50),
        date_submit_gmt datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        discount_total_amount decimal(10,2),
        discount_total_percentage decimal(5,2),
        payment_method varchar(50) NOT NULL,
        customer_id mediumint(9) NOT NULL,
        order_total_pure decimal(10,2) NOT NULL,
        order_total_final decimal(10,2) NOT NULL,
        visitor_id mediumint(9) NOT NULL,
        include_returned_products varchar(50) NOT NULL,
        PRIMARY KEY  (invoice_id),
        FOREIGN KEY (customer_id) REFERENCES $customers_table(customer_id)
    ) $charset_collate;";

    // Table for invoice_data_lookup
    $sql_data_lookup = "CREATE TABLE $data_lookup_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id mediumint(9) NOT NULL,
        product_id mediumint(9) NOT NULL,
        product_qty mediumint(9) NOT NULL,
        product_net_price decimal(10,2) NOT NULL,
        product_total_price decimal(10,2) NOT NULL,
        customer_id mediumint(9) NOT NULL,
        date_submit datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        product_sale_return varchar(50) NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (product_id) REFERENCES $products_table(product_id),
        FOREIGN KEY (customer_id) REFERENCES $customers_table(customer_id)
    ) $charset_collate;";

    // Execute the queries
    dbDelta($sql_products);
    dbDelta($sql_customers);
    dbDelta($sql_operation_data);
    dbDelta($sql_data_lookup);

    // Optionally, handle post-update actions or version updates
}