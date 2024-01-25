<?php
/*
Plugin Name: X-Invoice
Description: This is a Invoice WordPress plugin.
Version: 0.7
Author: Nima Amani <metananima@gmail.com>
*/
define('X_INVOICE_VERSION', '0.7');



require_once('includes/add-customers-data.php');
require_once('includes/edit-customers-data.php');
require_once('includes/edit-products-data.php');
require_once('includes/orders_list.php');
require_once('includes/reports.php');
require_once('includes/settings.php');


function x_invoice_activation() {
    x_invoice_create_or_update_tables();
}

function x_invoice_create_or_update_tables() {
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
    // Table for invoice_products
    // $products_table = $wpdb->prefix . 'x_invoice_products';
    $sql_products = "CREATE TABLE $products_table (
        product_id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_name varchar(255) NOT NULL,
        PRIMARY KEY  (product_id)
    ) $charset_collate;";

    // Table for invoice_customers
    // $customers_table = $wpdb->prefix . 'x_invoice_customers';
    $sql_customers = "CREATE TABLE $customers_table (
        customer_id mediumint(9) NOT NULL AUTO_INCREMENT,
        customer_name varchar(255) NOT NULL,
        customer_national_id varchar(255) NOT NULL,
        customer_address text NOT NULL,
        customer_shop_name varchar(255) NOT NULL,
        PRIMARY KEY  (customer_id)
    ) $charset_collate;";

    // Table for invoice_operation_data
    // $operation_data_table = $wpdb->prefix . 'x_invoice_operation_data';
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
        PRIMARY KEY  (invoice_id),
        FOREIGN KEY (customer_id) REFERENCES $customers_table(customer_id)
    ) $charset_collate;";

    // Table for invoice_data_lookup
    // $data_lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    $sql_data_lookup = "CREATE TABLE $data_lookup_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id mediumint(9) NOT NULL,
        product_id mediumint(9) NOT NULL,
        product_qty mediumint(9) NOT NULL,
        product_net_price decimal(10,2) NOT NULL,
        product_total_price decimal(10,2) NOT NULL,
        customer_id mediumint(9) NOT NULL,
        date_submit datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
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

// Database Creations on Plugin Activation
register_activation_hook(__FILE__, 'x_invoice_activation');
function x_invoice_activationxxx()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for invoice_products
    // $products_table = $wpdb->prefix . 'x_invoice_products';
    // $sql_products = "CREATE TABLE $products_table (
    //     product_id mediumint(9) NOT NULL AUTO_INCREMENT,
    //     product_name varchar(255) NOT NULL,
    //     PRIMARY KEY  (product_id)
    // ) $charset_collate;";

    // Table for invoice_customers
    // $customers_table = $wpdb->prefix . 'x_invoice_customers';
    // $sql_customers = "CREATE TABLE $customers_table (
    //     customer_id mediumint(9) NOT NULL AUTO_INCREMENT,
    //     customer_name varchar(255) NOT NULL,
    //     customer_national_id varchar(255) NOT NULL,
    //     customer_address text NOT NULL,
    //     PRIMARY KEY  (customer_id)
    // ) $charset_collate;";

    // Table for invoice_operation_data
    // $operation_data_table = $wpdb->prefix . 'x_invoice_operation_data';
    // $sql_operation_data = "CREATE TABLE $operation_data_table (
    //     invoice_id mediumint(9) NOT NULL AUTO_INCREMENT,
    //     order_id mediumint(9) NOT NULL,
    //     order_include_tax varchar(3) NOT NULL,
    //     order_total_tax decimal(10,2),
    //     order_include_discount varchar(3) NOT NULL,
    //     discount_method varchar(50),
    //     date_submit_gmt datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    //     discount_total_amount decimal(10,2),
    //     discount_total_percentage decimal(5,2),
    //     payment_method varchar(50) NOT NULL,
    //     customer_id mediumint(9) NOT NULL,
    //     order_total_pure decimal(10,2) NOT NULL,
    //     order_total_final decimal(10,2) NOT NULL,
    //     visitor_id mediumint(9) NOT NULL,
    //     PRIMARY KEY  (invoice_id),
    //     FOREIGN KEY (customer_id) REFERENCES $customers_table(customer_id)
    // ) $charset_collate;";

    // Table for invoice_data_lookup
    // $data_lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    // $sql_data_lookup = "CREATE TABLE $data_lookup_table (
    //     id mediumint(9) NOT NULL AUTO_INCREMENT,
    //     order_id mediumint(9) NOT NULL,
    //     product_id mediumint(9) NOT NULL,
    //     product_qty mediumint(9) NOT NULL,
    //     product_net_price decimal(10,2) NOT NULL,
    //     product_total_price decimal(10,2) NOT NULL,
    //     customer_id mediumint(9) NOT NULL,
    //     date_submit datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    //     PRIMARY KEY  (id),
    //     FOREIGN KEY (product_id) REFERENCES $products_table(product_id),
    //     FOREIGN KEY (customer_id) REFERENCES $customers_table(customer_id)
    // ) $charset_collate;";

    // require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    // dbDelta($sql_products);
    // dbDelta($sql_customers);
    // dbDelta($sql_operation_data);
    // dbDelta($sql_data_lookup);

    if (!get_option('x_invoice_version')) {
        add_option('x_invoice_version', X_INVOICE_VERSION);
    }
}

// Updating Function:
function x_invoice_check_version() {
    global $wpdb;
    
    if (get_option('x_invoice_version') != X_INVOICE_VERSION) {
        // The plugin has been updated
        
        // Add your update logic here
        $customers_table = $wpdb->prefix . 'x_invoice_customers';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_update_customers = "ALTER TABLE $customers_table ADD customer_shop_name varchar(255) NOT NULL;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_update_customers);
        
        // Update the version in the database
        update_option('x_invoice_version', X_INVOICE_VERSION);
    }
}
// Hook into 'init' or another appropriate action
add_action('init', 'x_invoice_check_version');


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

// Menus and Submenus
function x_invoice_plugin_admin_menu()
{
    $xi_main_page = add_menu_page(
        'فاکتور ایکس',           // Page title
        'فاکتور ایکس',           // Menu title
        'manage_options',           // Capability
        'x-invoice',                // Menu slug
        'invoice_main_function',    // Function that outputs the page content
        'dashicons-admin-generic',  // Icon URL (optional)
        6                           // Position (optional)
    );
    $xi_orders_list = add_submenu_page(
        'x-invoice',                // Parent slug: must match the top-level menu slug
        'فاکتور ها',                // Page title
        'فاکتور ها',                // Menu title
        'manage_options',           // Capability
        'xi-orders-list',           // Menu slug
        'invoice_orders_list'       // Function to display the submenu page content
    );
    $xi_reports = add_submenu_page(
        'x-invoice',                // Parent slug
        'گزارش',            // Page title
        'گزارش',            // Menu title
        'manage_options',           // Capability
        'xi-reports',               // Menu slug
        'x_reports'   // Function to display the content
    );
    $xi_products_data = add_submenu_page(
        'x-invoice',                // Parent slug: must match the top-level menu slug
        'افزودن/ویرایش محصول',     // Page title
        'افزودن/ویرایش محصول',     // Menu title
        'manage_options',           // Capability
        'xi-products-data',         // Menu slug
        'products_data_page'        // Function to display the submenu page content
    );
    $xi_add_customer_data = add_submenu_page(
        'x-invoice',
        'افزودن اطلاعات مشتری',
        'افزودن اطلاعات مشتری',
        'manage_options',
        'xi-add-customer-data',
        'add_customer_data_page'
    );
    $xi_edit_customer_data = add_submenu_page(
        'x-invoice',
        'ویرایش اطلاعات مشتری',
        'ویرایش اطلاعات مشتری',
        'manage_options',
        'xi-edit-customer-data',
        'edit_customer_data_page'
    );
}
add_action('admin_menu', 'x_invoice_plugin_admin_menu');



function x_invoice_enqueue_admin_styles($hook_suffix)
{
    wp_enqueue_style('x-invoice-admin-styles', plugin_dir_url(__FILE__) . 'styles/x-invoice-admin-styles.css');
}
add_action('admin_enqueue_scripts', 'x_invoice_enqueue_admin_styles');



function invoice_main_function()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید</h1>';
    settings();
    echo '</div>';
}

function invoice_orders_list()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید</h1>';
    x_invoice_orders_page();
    echo '</div>';
}
function x_reports()
{
    echo '<div class="wrap">';
    echo '<h1>گزارش ها:</h1>';
    x_reports_page();
    echo '</div>';
}

function products_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>افزودن یا ویرایش اطلاعات محصولات</h1>';
    showMessage();
    x_invoice_edit_products_page();
    echo '</div>';
}
function add_customer_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>افزودن اطلاعات</h1>';
    showMessage();
    if (isset($_POST['some_other_action'])) {
        x_invoice_add_customers_page();
    }
    x_invoice_add_customers_page();
    echo '</div>';
}
function edit_customer_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>ویرایش اطلاعات</h1>';
    showMessage();
    if (isset($_POST['some_other_action'])) {
        x_invoice_edit_customers_page();
    }
    x_invoice_edit_customers_page();
    echo '</div>';
}

// Front Invoice Shortcode
function x_invoice_shortcode()
{
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_display_name = $current_user->display_name;

    ob_start();
    $today = date('Y-m-d');
    $current_date_time = current_time('Y-m-d H:i:s');

    $customers_table_name   = $wpdb->prefix . 'x_invoice_customers';
    $products_table_name    = $wpdb->prefix . 'x_invoice_products';
    $operations_table_name  = $wpdb->prefix . 'x_invoice_operation_data';
    $data_lookup_table_name = $wpdb->prefix . 'x_invoice_data_lookup';
    $customers_data         = $wpdb->get_results("SELECT * FROM $customers_table_name", ARRAY_A);
    $products_data          = $wpdb->get_results("SELECT * FROM $products_table_name", ARRAY_A);
    $operations_data        = $wpdb->get_results("SELECT * FROM $operations_table_name", ARRAY_A);
    $data_lookup_data       = $wpdb->get_results("SELECT * FROM $data_lookup_table_name", ARRAY_A);

    $taxAmount              = get_option('taxAmount', 'applied-tax');
?>
    <?php echo current_time('Y-m-d', false); ?>
    <form id="x-invoice" class="x-invoice" action="" method="post">
        <h2 class="x-invoice-title"></h2>
        <div class="x-invoice-form-inputs">
            <table class="clientDataTable">
                <thead>
                    <tr>
                        <th>کاربر</th>
                        <th>تاریخ</th>
                        <th>نام مشتری</th>
                        <th>کد ملی مشتری</th>
                        <th>آدرس</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="x_invoice_top_td">
                            <input class="current_user" readonly type="" value="<?php echo $user_display_name; ?>">
                            <input type="hidden" name="" value="<?php echo $user_id; ?>">
                        </td>
                        <td class="x_invoice_top_td">
                            <input type="text" readonly class="today-date" name="today-date" value="<?php echo $today; ?>">
                            <input type="hidden" readonly class="current-date-time" name="current-date-time" value="<?php echo $current_date_time; ?>">
                        </td>
                        <td class="x_invoice_top_td">
                            <select name="customer_name" class="customer_name" id="customer_name" onChange="fetchCustomerDetails(this.value)">
                                <option value="-1">— انتخاب کنید —</option>
                                <?php
                                foreach ($customers_data as $data) {
                                ?>
                                    <option value="<?php echo esc_attr($data['customer_id']); ?>"><?php echo esc_attr($data['customer_name']); ?></option>
                                <?php
                                };
                                ?>
                            </select>
                            <input type="hidden" class="this_customer_id" name="this_customer_id" value="">
                        </td>
                        <td class="x_invoice_top_td"><input class="customer_national_id" readonly type="" value=""></td>
                        <td class="x_invoice_top_td"><input class="customer_national_address" readonly type="" value=""></td>
                    </tr>
                </tbody>
            </table>
            <hr>
            <table class="productsList" id="productsList">
                <tbody>
                    <th>نام محصول</th>
                    <th>مقدار (mL)</th>
                    <th>قیمت واحد</th>
                    <th>جمع</th>
                    <th>افزودن/حذف
                        <br>ردیف
                    </th>
                    <tr>
                        <td class="x_invoice_table_td">
                            <select name="custom_product_name" class="custom_product_name" id="custom_product_name">
                                <option value="-1">-- انتخاب کنید --</option>
                                <?php
                                foreach ($products_data as $data) {
                                ?>
                                    <option value="<?php echo esc_attr($data['product_id']); ?>"><?php echo esc_attr($data['product_name']); ?></option>
                                <?php
                                };
                                ?>
                            </select>
                        </td>
                        <td class="x_invoice_table_td"><input class="custom_product_amount" type="text"></td>
                        <td class="x_invoice_table_td"><input class="custom_product_price" type="text"></td>
                        <td class="x_invoice_table_td">
                            <span class="custom_product_show_only"></span>
                            <input readonly type="hidden" class="custom_product_total" value="">
                        </td>
                        <td class="x_invoice_table_td x_invoice_table_td_btn">
                            <button class="add_new_row">+</button>
                            <button class="remove_this_row" style="display: none;">—</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr>
            <div class="invoice_total">
                <div class="invoice_total_title">
                    جمع کل قبل از تخفیف:
                </div>
                <div class="invoice_total_output">
                    <span class="invoice_total_pure_show_only"></span>
                    <input readonly type="hidden" name="invoice_total_pure" class="invoice_total_pure" value="">
                </div>
            </div>
            <hr>
            <div class="invoice_options">
                <div class="payment_method_section">
                    <input type="radio" id="payment_cash" class="payment_method payment_cash" name="payment_method" value="نقدی">
                    <label for="payment_cash">نقدی</label>
                    <input type="radio" id="payment_cheq" class="payment_method payment_cheq" name="payment_method" value="چک">
                    <label for="payment_cheq">چک</label>
                </div>
                <div class="payment_discount">
                    <div class="has_discount">
                        <input type="checkbox" name="invoice_discount" id="invoice_discount" class="invoice_discount">
                        <label for="invoice_discount">تخفیف دارد؟</label>
                    </div>
                    <div class="payment_discount_method" style="display: none;">
                        <input type="radio" id="payment_percents" class="payment_discount_methods payment_percents" name="payment_discount_methods" value="درصد">
                        <label for="payment_percents">درصد</label>
                        <input type="radio" id="payment_constant" class="payment_discount_methods payment_constant" name="payment_discount_methods" value="مبلغ ثابت">
                        <label for="payment_constant">مبلغ ثابت</label>
                        <input type="number" disabled name="discount_percents" id="discount_percents" class="discounts discount_percents" value="" placeholder="درصد تخفیف را وارد نمایید.">
                        <input type="number" disabled name="discount_constant" id="discount_constant" class="discounts discount_constant" value="" placeholder="مبلغ تخفیف را وارد نمایید.">
                    </div>
                </div>
                <div class="payment_tax">
                    <div class="includes_tax">
                        <input type="checkbox" name="invoice_includes_tax" id="invoice_includes_tax" class="invoice_includes_tax">
                        <label for="invoice_includes_tax">مشمول مالیت شود؟</label>
                    </div>
                    <div class="tax_amounts" style="display: none;">
                        <label for="tax_amount">درصد مالیات معادل:</label>
                        <input readonly name="tax_amount_value" id="tax_amount_value" class="tax tax_amount_value" value="<?php echo $taxAmount; ?>">
                    </div>
                </div>
            </div>
            <hr>
            <div class="invoice_total">
                <div class="invoice_total_title">
                    جمع کل:
                </div>
                <div class="invoice_total_output">
                    <span class="invoice_total_prices_show_only"></span>
                    <input readonly type="hidden" name="invoice_total_prices" class="invoice_total_prices" value="">
                </div>

            </div>
            <hr>
            <div class="submit-section">
                <input type="submit" id="submit_invoice" name="submit" class="button-primary" value="Register Invoice" style="display: none;">
            </div>
        </div>
    </form>
    <div id="results_container"></div>
    <script>
        function fetchCustomerDetails(customerId) {
            if (customerId == -1) {
                // Reset the fields if no customer is selected
                jQuery('.customer_national_id').val('');
                jQuery('.customer_national_address').val('');
                return;
            }
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_customer_details',
                    customer_id: customerId
                },
                success: function(response) {
                    if (response) {
                        var data = JSON.parse(response);
                        jQuery('.customer_national_id').val(data.national_id);
                        jQuery('.customer_national_address').val(data.address);
                    }
                }
            });
        };
    </script>
<?php
    return ob_get_clean();
}

/* Start Data Sending via Ajax */
function x_invoice_ajax_submit_invoice()
{
    global $wpdb;
    check_ajax_referer('x_invoice_nonce', 'security');

    // Extract and sanitize data from $_POST
    $customer_id                = sanitize_text_field($_POST['customer_id']);
    $order_include_tax          = sanitize_text_field($_POST['order_include_tax']);
    $order_total_tax            = sanitize_text_field($_POST['order_total_tax']);
    $order_include_discount     = sanitize_text_field($_POST['order_include_discount']);
    $discount_method            = sanitize_text_field($_POST['discount_method']);
    $discount_total_amount      = sanitize_text_field($_POST['discount_total_amount']);
    $discount_total_percentage  = sanitize_text_field($_POST['discount_total_percentage']);
    $payment_method             = sanitize_text_field($_POST['payment_method']);
    $order_total_pure           = sanitize_text_field($_POST['order_total_pure']);
    $order_total_final          = sanitize_text_field($_POST['order_total_final']);
    $visitor_id                 = get_current_user_id(); // Current logged-in user ID

    // Insert data into invoice_operation_data
    $operation_data = array(
        'order_include_tax'         => $order_include_tax,
        'order_total_tax'           => $order_total_tax,
        'order_include_discount'    => $order_include_discount,
        'discount_method'           => $discount_method,
        'date_submit_gmt'           => current_time('mysql', 1), // GMT time
        'discount_total_amount'     => $discount_total_amount,
        'discount_total_percentage' => $discount_total_percentage,
        'payment_method'            => $payment_method,
        'customer_id'               => $customer_id,
        'order_total_pure'          => $order_total_pure,
        'order_total_final'         => $order_total_final,
        'visitor_id'                => $visitor_id
    );
    $wpdb->insert(
        $wpdb->prefix . 'x_invoice_operation_data',
        $operation_data
    );
    $invoice_id = $wpdb->insert_id;

    // Handle product data
    $products = $_POST['products']; // Array of products
    foreach ($products as $product) {
        $product_id     = sanitize_text_field($product['product_id']);
        $quantity       = sanitize_text_field($product['quantity']);
        $net_price      = sanitize_text_field($product['net_price']);
        $total_price    = sanitize_text_field($product['total_price']);

        $wpdb->insert(
            $wpdb->prefix . 'x_invoice_data_lookup',
            array(
                'order_id'              => $invoice_id,
                'product_id'            => $product_id,
                'product_qty'           => $quantity,
                'product_net_price'     => $net_price,
                'product_total_price'   => $total_price,
                'customer_id'           => $customer_id,
                'date_submit'           => current_time('mysql')
            )
        );
    }

    // Update the order_id with the invoice_id
    $wpdb->update(
        $wpdb->prefix . 'x_invoice_operation_data',
        array('order_id'    => $invoice_id),
        array('invoice_id'  => $invoice_id)
    );

    wp_send_json_success(array(
        'message' => 'Invoice created successfully',
        'invoice_id' => $invoice_id,
        'redirect_url' => home_url('/view-invoice/') // Add the redirect URL to the response
    ));
}
/* End Data Sending via Ajax */

// Getting and Updating Customers Data
function get_customer_details()
{
    global $wpdb;
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
    if ($customer_id > 0) {
        $table_name = $wpdb->prefix . 'x_invoice_customers';
        $customer = $wpdb->get_row("SELECT * FROM $table_name WHERE customer_id = $customer_id", ARRAY_A);
        if ($customer) {
            echo json_encode(array('national_id' => $customer['customer_national_id'], 'address' => $customer['customer_address']));
        } else {
            echo json_encode(array('national_id' => '', 'address' => ''));
        }
    }
    wp_die();
}
add_action('wp_ajax_get_customer_details', 'get_customer_details');
add_action('wp_ajax_nopriv_get_customer_details', 'get_customer_details');


function x_invoice_files()
{
    wp_enqueue_style('x-invoice', plugin_dir_url(__FILE__) . 'styles/x-invoice-styles.css');
    wp_enqueue_script('x-invoice', plugin_dir_url(__FILE__) . 'js/x-invoice-scripts.js', array('jquery'), null, true);
    wp_localize_script('x-invoice', 'myAjax', array(
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('x_invoice_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'x_invoice_files');
add_action('wp_ajax_x_invoice_submit_invoice', 'x_invoice_ajax_submit_invoice');
add_action('wp_ajax_nopriv_x_invoice_submit_invoice', 'x_invoice_ajax_submit_invoice');

// Create Shortcode
add_shortcode('x-invoice', 'x_invoice_shortcode');


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




function x_invoice_display_invoice_details($atts)
{
    global $wpdb;
    $atts = shortcode_atts(array(
        'invoice_id' => '',
    ), $atts, 'x_invoice_details');

    $invoice_id = intval($atts['invoice_id']);
    if (!$invoice_id) {
        return 'Invalid Invoice ID.';
    }

    // Fetch invoice details
    $invoice_op_table = $wpdb->prefix . 'x_invoice_operation_data';
    $invoice_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM $invoice_op_table WHERE invoice_id = %d", $invoice_id));

    if (empty($invoice_details)) {
        return 'No details found for this invoice.';
    }

    // Display the invoice details
    ob_start();
    echo '<div class="x-invoice-details">';
    // Display details here, similar to how you did in the admin page
    foreach ($invoice_details as $detail) {
        echo '<p>Detail: ' . esc_html($detail->some_column) . '</p>';
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('x_invoice_details', 'x_invoice_display_invoice_details');



// ob_start();
// return ob_get_clean();
function x_invoice_view_order_shortcode()
{
    global $wpdb;
    $xi_invoice_view_output = '<div class="xi-invoice-view">';

    // Define table names
    $operation_table = $wpdb->prefix . 'x_invoice_operation_data';
    $lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    $customers_table = $wpdb->prefix . 'x_invoice_customers';
    $products_table = $wpdb->prefix . 'x_invoice_products';

    // Check if an invoice number is submitted or fetch the last invoice number
    if (isset($_POST['invoice_number']) && !empty($_POST['invoice_number'])) {
        $invoice_number = intval($_POST['invoice_number']); // Sanitize the input
    } else {
        // Fetch the last registered invoice ID
        $invoice_number = $wpdb->get_var("SELECT MAX(invoice_id) FROM $operation_table");
    }

    // Fetch invoice details
    if ($invoice_number) {
        $invoice = $wpdb->get_row($wpdb->prepare(
            "SELECT op.*, c.customer_name, c.customer_national_id, c.customer_address, c.customer_shop_name, u.display_name as visitor_name
            FROM $operation_table op
            JOIN $customers_table c ON op.customer_id = c.customer_id
            LEFT JOIN {$wpdb->users} u ON op.visitor_id = u.ID
            WHERE op.invoice_id = %d",
            $invoice_number
        ));

        // Fetch product details for the invoice
        $products = $wpdb->get_results($wpdb->prepare(
            "SELECT dl.*, p.product_name
            FROM $lookup_table dl
            JOIN $products_table p ON dl.product_id = p.product_id
            WHERE dl.order_id = %d",
            $invoice_number
        ));
    }
    // Check if the invoice exists
    if ($invoice) {
        $xi_invoice_view_output .= '<div class="xi-invoice-result">';
        // Header Start
        $xi_invoice_view_output .= '<div class="xi-invoice-header">';
        // $xi_invoice_view_output .= '<div class="logo">لوگو اینجا میباشد</div>';
        $xi_invoice_view_output .= '<div class="logo"><img src="' . plugin_dir_url(__FILE__). '/assets/july-logo.jpg" alt=""></div>';
        $xi_invoice_view_output .= '<div class="company-name">شرکت نیک عطرآگین پارس</div>';
        $xi_invoice_view_output .= '<div class="company-registration-number">شماره ثبت ۱۷۲۰۵</div>';
        $xi_invoice_view_output .= '</div>';
        $xi_invoice_view_output .= '<hr>';
        // Header End
        $xi_invoice_view_output .= '<p class="xi-invoice-number"><b>' . esc_html($invoice->invoice_id) . '</b></p>';
        $xi_invoice_view_output .= '<p><b>تاریخ ثبت:</b> ' . esc_html($invoice->date_submit_gmt) . '</p>';
        $xi_invoice_view_output .= '<p><b>نام ویزیتور:</b> ' . esc_html($invoice->visitor_name) . '</p>';
        $xi_invoice_view_output .= '<p><b>نام مشتری:</b> ' . esc_html($invoice->customer_name) . '</p>';
        $xi_invoice_view_output .= '<p><b>نام فروشگاه:</b> ' . esc_html($invoice->customer_shop_name) . '</p>';
        $xi_invoice_view_output .= '<p><b>آدرس مشتری:</b> ' . esc_html($invoice->customer_address) . '</p>';
        $xi_invoice_view_output .= '<p><b>نحوه پرداخت:</b> ' . esc_html($invoice->payment_method) . '</p>';

        $xi_invoice_view_output .= '<table class="xi-items-list"><tr><th>نام</th><th>تعداد</th><th>قیمت</th><th>جمع</th></tr>';
        foreach ($products as $product) {
            $xi_invoice_view_output .= '<tr><td>' . esc_html($product->product_name) . '</td><td>' . esc_html($product->product_qty) . '</td><td>' . esc_html(number_format($product->product_net_price)) . '</td><td>' . esc_html(number_format($product->product_total_price)) . '</td></tr>';
        }
        $xi_invoice_view_output .= '</table>';
        $xi_invoice_view_output .= '<table class="xi-pricing">';
        $xi_invoice_view_output .= '<tr><td><b>جمع کل:</b></td><td class="xi-table-prices">' . esc_html(number_format($invoice->order_total_pure)) . ' تومان</td></tr>';
        if ($invoice->order_include_tax === 'yes') {
            $xi_invoice_view_output .= '<tr><td><b>مقدار مالیات:</b></td><td class="xi-table-prices"> ' . esc_html(number_format($invoice->order_total_tax)) . ' %</td></tr>';
        }
        if ($invoice->order_include_discount === 'yes') {
            if ($invoice->discount_method === 'درصد') {
                $xi_invoice_view_output .= '<tr><td><b>مقدار تخفیف:</b></td><td class="xi-table-prices"> ' . esc_html(number_format($invoice->discount_total_percentage)) . ' %</td></tr>';
            } elseif ($invoice->discount_method === 'مبلغ ثابت') {
                $xi_invoice_view_output .= '<tr><td><b>مقدار تخفیف:</b></td><td class="xi-table-prices"> ' . esc_html(number_format($invoice->discount_total_amount)) . ' تومان </td></tr>';
            }
        }
        $xi_invoice_view_output .= '<tr><td><b>مبلغ نهایی:</b></td><td class="xi-table-prices">' . esc_html(number_format($invoice->order_total_final)) . ' تومان</td></tr>';
        $xi_invoice_view_output .= '</table>';
        // Footer End
        $xi_invoice_view_output .= '<div class="xi-invoice-footer">';
        $xi_invoice_view_output .= '<div class="thanks-message"><b>تشکر از خرید شما، به امید دیدار مجدد.</b></div>';
        $xi_invoice_view_output .= '<table><tbody>';
        $xi_invoice_view_output .= '<tr><td>مدیر فروش (استادی)</td><td>۰۹۳۰۴۹۴۶۶۹۴</td></tr>';
        $xi_invoice_view_output .= '<tr><td>مدیر تولید (قنبری)</td><td>۰۹۱۹۸۵۲۱۸۵۶</td></tr>';
        $xi_invoice_view_output .= '<tr class="links"><td class="site">NikPerfume.com</td><td class="instagram">Instagram: NiikPerfume</td></tr>';
        $xi_invoice_view_output .= '</tbody></table>';
        $xi_invoice_view_output .= '</div>';
        // Footer End
        $xi_invoice_view_output .= '</div>';
    } else {
        $xi_invoice_view_output .= '<div class="xi-invoice-result">Invoice not found.</div>';
    }


    // Form HTML
    $xi_invoice_view_output .= '
    <div class="xi-invoice-view-form-controls">
        <div class="xi-search-invoice">
            <form id="x-invoice-view" class="x-invoice-view" action="" method="post">
                <div class="xi-invoice-search">
                    <input type="number" name="invoice_number" id="invoice_number" placeholder="Enter Invoice Number">
                    <input type="submit" id="search_invoice" value="جستجو">
                </div>
            </form>
        </div>
        <div class="xi-print-invoice">
            <button onClick="window.print()">چاپ فاکتور</button>
            <button class="xi-invoice-save-pdf">ذخیره pdf در سایت</button>
        </div>
    </div>';

    // echo $pdf_dir_path = wp_upload_dir()['basedir'] . '/xi-invoice';
    return $xi_invoice_view_output;
}
add_shortcode('x-invoice_view_order', 'x_invoice_view_order_shortcode');


// PDF Creation
add_action('wp_ajax_generate_invoice_pdf', 'generate_invoice_pdf');
function generate_invoice_pdf() {
    global $wpdb;
    $invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
    
    // Fetch the invoice data just like in your shortcode
    // ...

    // Initialize DOMPDF
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    $dompdf = new Dompdf\Dompdf();

    // Generate the HTML for the invoice
    $html = 'TEST TEST TEST'; // Your HTML content goes here. You can use ob_start() and ob_get_clean() to capture output from includes

    // Load the HTML and render the PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save the PDF to the server
    $pdf_output = $dompdf->output();
    $pdf_dir_path = wp_upload_dir()['basedir'] . '/invoices/';
    $pdf_file_path = $pdf_dir_path . 'xi-invoice-' . $invoice_id . '.pdf';

    if (!file_exists($pdf_dir_path)) {
        mkdir($pdf_dir_path, 0777, true);
    }

    file_put_contents($pdf_file_path, $pdf_output);

    // Send a response
    wp_send_json_success(array('message' => 'PDF generated successfully', 'pdf_url' => $pdf_file_path));
}

