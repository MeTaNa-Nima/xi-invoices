<?php
require_once('settings.php');

function xi_invoice_show_single()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();
    $invoices       = $all_invoices->get_all_invoices();

    if (isset($_GET['invoice_id']) && is_numeric($_GET['invoice_id'])) {
        $invoice_id             = intval($_GET['invoice_id']);
        $invoices               = new Xi_Invoices_Invoice();
        $products               = $invoices->get_product_details($invoice_id, 'sold');
        $returned_products      = $invoices->get_product_details($invoice_id, 'returned');
        $invoice_general_data   = $invoices->get_invoice($invoice_id);

        $customer_details       = $customers->get_customer($invoice_general_data->customer_id);
        $user_info              = get_userdata($invoice_general_data->visitor_id);
        $user_name              = $user_info ? $user_info->user_login : 'Unknown User';
        $user_link              = $user_info ? admin_url('user-edit.php?user_id=' . $invoice_general_data->visitor_id) : '#';
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
                                    if ($invoice_general_data->order_include_tax === 'yes') {
                                        echo '<p><b>مقدار مالیات:</b> ' . esc_html(number_format($invoice_general_data->order_total_tax)) . '</p>';
                                    }
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
                                    echo '<p><b>نام مشتری:</b> ' . esc_html($customer_details->customer_name) . '</p>';
                                    echo '<p><b>کد ملی:</b> ' . esc_html($customer_details->customer_national_id) . '</p>';
                                    echo '<p><b>نام فروشگاه:</b> ' . esc_html($customer_details->customer_shop_name) . '</p>';
                                    echo '<p><b>آدرس مشتری:</b> ' . esc_html($customer_details->customer_address) . '</p>';
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="postbox">
                            <table class="form-table striped table-view-list widefat wp-list-table" id="sold_products">
                                <thead>
                                    <tr>
                                        <td colspan="4"><b>محصولات فروخته شده</b></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>محصول</th>
                                        <th>تعداد</th>
                                        <th>قیمت واحد</th>
                                        <th>قیمت کل</th>
                                    </tr>
                                    <?php
                                    foreach ($products as $product) {
                                    ?>
                                        <tr>
                                            <td><?php echo esc_html($product->product_name); ?></td>
                                            <td><?php echo esc_html($product->product_qty); ?></td>
                                            <td><?php echo esc_html(number_format($product->product_net_price)); ?></td>
                                            <td><?php echo esc_html(number_format($product->product_total_price)); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        if (!empty($returned_products)) {
                        ?>
                            <table class="form-table striped table-view-list widefat wp-list-table" id="returned_products">
                                <thead>
                                    <tr>
                                        <td colspan="4"><b>محصولات مرجوعی</b></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>محصول</th>
                                        <th>تعداد</th>
                                        <th>قیمت واحد</th>
                                        <th>قیمت کل</th>
                                    </tr>
                                    <?php
                                    foreach ($returned_products as $product) {
                                    ?>
                                        <tr>
                                            <td><?php echo esc_html($product->product_name); ?></td>
                                            <td><?php echo esc_html($product->product_qty); ?></td>
                                            <td><?php echo esc_html(number_format($product->product_net_price)); ?></td>
                                            <td><?php echo esc_html(number_format($product->product_total_price)); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        <?php
                        }
                        ?>
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
                            <?php
                            if (in_array('administrator', wp_get_current_user()->roles)) {
                            ?>
                                <a class="button button-secondary" href="admin.php?page=xi-invoices&edit_mode=1&invoice_id=<?php echo $invoice_id; ?>">ویرایش</a>
                            <?php
                            }
                            ?>
                            <a class="button button-secondary" href="admin.php?page=xi-invoices">بازگشت</a>
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