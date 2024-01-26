<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}




// Front Invoice Shortcode
function x_invoice_shortcode()
{
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_display_name = $current_user->display_name;

    ob_start();
    $today = jdate('Y/n/j');
    $current_date_time = jdate('Y/n/j');

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
                            <button class="add_new_row add_new_product_row">+</button>
                            <button class="remove_this_row remove_product_row" style="display: none;">—</button>
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
            <div class="returned_products_section" style="display: none;">
                <h3>لیست کالا های مرجوعی</h3>
                <table class="returned_productsList" id="returned_productsList">
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
                                <button class="add_new_row add_new_returned_row">+</button>
                                <button class="remove_this_row remove_returned_row" style="display: none;">—</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <div class="invoice_total_returned">
                    <div class="invoice_total_title">
                        جمع کل مرجوعی ها:
                    </div>
                    <div class="invoice_total_returned_output">
                        <span class="invoice_total_returned_pure_show_only"></span>
                        <input readonly type="hidden" name="invoice_total_returned_pure" class="invoice_total_returned_pure" value="">
                    </div>
                </div>
                <hr>
            </div>
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
                        <div class="form-controls">
                            <input type="radio" id="payment_percents" class="payment_discount_methods payment_percents" name="payment_discount_methods" value="درصد">
                            <label for="payment_percents">درصد</label>
                            <input type="radio" id="payment_constant" class="payment_discount_methods payment_constant" name="payment_discount_methods" value="مبلغ ثابت">
                            <label for="payment_constant">مبلغ ثابت</label>
                        </div>
                        <div class="form-controls">
                            <input type="number" disabled name="discount_percents" id="discount_percents" class="discounts discount_percents" value="" placeholder="درصد تخفیف را وارد نمایید.">
                            <input type="number" disabled name="discount_constant" id="discount_constant" class="discounts discount_constant" value="" placeholder="مبلغ تخفیف را وارد نمایید.">
                        </div>
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
                <div class="returned_products">
                    <input type="checkbox" name="include_returned_products" id="include_returned_products" class="include_returned_products">
                    <label for="include_returned_products">کالای مرجوعی دارید؟</label>
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
                <div class="xi-form-error-msg"></div>
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
    $include_returned_products  = sanitize_text_field($_POST['include_returned_products']);

    // Insert data into invoice_operation_data
    $operation_data = array(
        'order_include_tax'         => $order_include_tax,
        'order_total_tax'           => $order_total_tax,
        'order_include_discount'    => $order_include_discount,
        'discount_method'           => $discount_method,
        'date_submit_gmt'           => jdate('Y/n/j'), // GMT time
        'discount_total_amount'     => $discount_total_amount,
        'discount_total_percentage' => $discount_total_percentage,
        'payment_method'            => $payment_method,
        'customer_id'               => $customer_id,
        'order_total_pure'          => $order_total_pure,
        'order_total_final'         => $order_total_final,
        'visitor_id'                => $visitor_id,
        'include_returned_products' => $include_returned_products,
    );
    $wpdb->insert(
        $wpdb->prefix . 'x_invoice_operation_data',
        $operation_data
    );
    $invoice_id = $wpdb->insert_id;

    // Handle product data for sold products
    $products = $_POST['products']; // Array of products
    foreach ($products as $product) {
        $product_id         = sanitize_text_field($product['product_id']);
        $quantity           = sanitize_text_field($product['quantity']);
        $net_price          = sanitize_text_field($product['net_price']);
        $total_price        = sanitize_text_field($product['total_price']);
        $sale_return_flag   = sanitize_text_field($product['sale_return_flag']); // Get the sale/return flag

        $wpdb->insert(
            $wpdb->prefix . 'x_invoice_data_lookup',
            array(
                'order_id'              => $invoice_id,
                'product_id'            => $product_id,
                'product_qty'           => $quantity,
                'product_net_price'     => $net_price,
                'product_total_price'   => $total_price,
                'customer_id'           => $customer_id,
                'product_sale_return'   => $sale_return_flag, // Save the sale/return flag
                'date_submit'           => jdate('Y/n/j')
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
    wp_enqueue_style('x-invoice', X_INVOICE_PLUGIN_URL . 'assets/css/public.css');
    wp_enqueue_script('x-invoice', X_INVOICE_PLUGIN_URL . 'assets/js/public.js', array('jquery'), null, true);
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