<?php
require_once('settings.php');

function fetch_invoice_details($invoice_id)
{
    global $wpdb;
    $lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM $lookup_table WHERE order_id = %d", $invoice_id));
}

function get_customer_details_by_id($customer_id)
{
    global $wpdb;
    $customers_table = $wpdb->prefix . 'x_invoice_customers';
    $customer = $wpdb->get_row($wpdb->prepare("SELECT customer_name, customer_national_id, customer_address, customer_shop_name FROM $customers_table WHERE customer_id = %d", $customer_id));

    if ($customer) {
        return array(
            'name' => $customer->customer_name,
            'national_id' => $customer->customer_national_id,
            'address' => $customer->customer_address,
            'shop_name' => $customer->customer_shop_name
        );
    } else {
        return array(
            'name' => 'Unknown Customer',
            'national_id' => 'N/A',
            'address' => 'N/A',
            'shop_name' => 'N/A'
        );
    }
}

function get_product_name_by_id($product_id)
{
    global $wpdb;
    $products_table = $wpdb->prefix . 'x_invoice_products';
    $product = $wpdb->get_row($wpdb->prepare("SELECT product_name FROM $products_table WHERE product_id = %d", $product_id));

    return $product ? $product->product_name : 'Unknown Product';
}

function x_invoice_orders_page()
{
    global $wpdb;
    $invoice_op_table = $wpdb->prefix . 'x_invoice_operation_data';
    $invoices = $wpdb->get_results("SELECT * FROM $invoice_op_table");

    if (!isset($_GET['invoice_id'])) {
?>
        <h2>فاکتور های ثبت شده:</h2>
        <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
            <tr>
                <th>شناسه</th>
                <th>ویزیتور</th>
                <th>مبلغ خالص</th>
                <th>مبلغ نهایی</th>
                <th>مشتری</th>
                <th>تاریخ</th>
                <th>جزییات</th>
            </tr>
            <?php
            if (!empty($invoices)) {
                foreach ($invoices as $invoice) {
                    $customer_details = get_customer_details_by_id($invoice->customer_id);
                    $user_info = get_userdata($invoice->visitor_id);
                    $user_name = $user_info ? $user_info->user_login : 'Unknown User';
                    $user_link = $user_info ? admin_url('user-edit.php?user_id=' . $invoice->visitor_id) : '#';
            ?>
                    <tr>
                        <td><?php echo esc_html($invoice->invoice_id); ?></td>
                        <td><a href="<?php echo esc_url($user_link); ?>"><?php echo esc_html($user_name); ?></a></td>
                        <td><?php echo esc_html(number_format($invoice->order_total_pure)); ?></td>
                        <td><?php echo esc_html(number_format($invoice->order_total_final)); ?></td>
                        <td><?php echo esc_html($customer_details['name']); ?></td>
                        <td><?php echo esc_html($invoice->date_submit_gmt); ?></td>
                        <td><a href="<?php echo admin_url('admin.php?page=xi-orders-list&invoice_id=' . esc_attr($invoice->invoice_id)); ?>">مشاهده فاکتور</a></td>
                    </tr>
            <?php
                }
            }
            ?>
        </table>
    <?php
    } elseif (isset($_GET['invoice_id']) && is_numeric($_GET['invoice_id'])) {
        $invoice_id = intval($_GET['invoice_id']);
        $invoice_details = fetch_invoice_details($invoice_id);
        $invoice_general_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $invoice_op_table WHERE invoice_id = %d", $invoice_id));
        $customer_details = get_customer_details_by_id($invoice_general_data->customer_id);
        $user_info = get_userdata($invoice_general_data->visitor_id);
        $user_name = $user_info ? $user_info->user_login : 'Unknown User';
        $user_link = $user_info ? admin_url('user-edit.php?user_id=' . $invoice_general_data->visitor_id) : '#';
    ?>
        <?php
        if ($invoice_general_data) {
        ?>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="postbox-container-2" class="postbox-container">
                        <div class="postbox">
                            <div class="xi-invoice-info invoice-info_general">
                                <h2>فاکتور شماره <?php echo $invoice_id; ?></h2>
                                <p>تاریخ: <?php echo esc_html($invoice_general_data->date_submit_gmt); ?></p>
                                <a href="<?php echo esc_url($user_link); ?>">ویزیتور: <?php echo esc_html($user_name); ?></a>
                            </div>
                        </div>
                        <div class="postbox">
                            <div class="xi-invoice-info invoice-info_customer">
                                <div>
                                    <?php
                                    // echo '<p>مشموال مالیات: ' . esc_html($invoice_general_data->order_include_tax) . '</p>';
                                    if ($invoice_general_data->order_include_tax === 'yes') {
                                        echo '<p><b>مقدار مالیات:</b> ' . esc_html(number_format($invoice_general_data->order_total_tax)) . '</p>';
                                    }
                                    // echo '<p>مشموال تخفیف: ' . esc_html($invoice_general_data->order_include_discount) . '</p>';
                                    if ($invoice_general_data->order_include_discount === 'yes') {
                                        echo '<p><b>نوع تخفیف:</b> ' . esc_html($invoice_general_data->discount_method) . '</p>';
                                        if ($invoice_general_data->discount_method === 'درصد') {
                                            echo '<p><b>مقدار تخفیف:</b> ' . esc_html(number_format($invoice_general_data->discount_total_percentage)) . '%</p>';
                                        } elseif ($invoice_general_data->discount_method === 'مبلغ ثابت') {
                                            echo '<p><b>مقدار تخفیف:</b> ' . esc_html(number_format($invoice_general_data->discount_total_amount)) . ' تومان </p>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div>
                                    <?php
                                    echo '<p><b>نام مشتری:</b> ' . esc_html($customer_details['name']) . '</p>';
                                    echo '<p><b>کد ملی:</b> ' . esc_html($customer_details['national_id']) . '</p>';
                                    echo '<p><b>نام فروشگاه:</b> ' . esc_html($customer_details['shop_name']) . '</p>';
                                    echo '<p><b>آدرس مشتری:</b> ' . esc_html($customer_details['address']) . '</p>';
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="postbox">
                            <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
                                <tr>
                                    <th>محصول</th>
                                    <th>تعداد/مقدار</th>
                                    <th>قیمت واحد</th>
                                    <th>قیمت کل</th>
                                </tr>
                                <?php
                                foreach ($invoice_details as $detail) {
                                    $product_name = get_product_name_by_id($detail->product_id);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($product_name); ?></td>
                                        <td><?php echo esc_html($detail->product_qty); ?></td>
                                        <td><?php echo esc_html(number_format($detail->product_net_price)); ?></td>
                                        <td><?php echo esc_html(number_format($detail->product_total_price)); ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </table>
                        </div>
                        <div class="postbox" style="float: left;">
                            <table class="xi-order-totals  table-view-list wp-list-table">
                                <tbody>
                                    <tr>
                                        <td class="label">جمع موارد:</td>
                                        <td width="1%"></td>
                                        <td class="total"><?php echo esc_html(number_format($invoice_general_data->order_total_pure)); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label">مالیات:</td>
                                        <td width="1%"></td>
                                        <td class="total"><?php echo esc_html(number_format($invoice_general_data->order_total_tax)); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label">تخفیف:</td>
                                        <td width="1%"></td>
                                        <td class="total"><?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?> / <?php echo esc_html($invoice_general_data->discount_total_percentage); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label">جمع کل نهایی:</td>
                                        <td width="1%"></td>
                                        <td class="total"><?php echo esc_html(number_format($invoice_general_data->order_total_final)); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="postbox">
                            <a class="button button-secondary" href="admin.php?page=xi-orders-list">بازگشت</a>
                            <a class="button delet-invoice" href="#">حذف فاکتور</a>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        } else {
            echo 'No details found for this invoice.';
        }
    } else {
        echo 'Invalid Invoice ID.';
    }
    ?>
    <?php showMessage(); ?>
    </div>
<?php
}
?>