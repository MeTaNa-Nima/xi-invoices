<?php
require_once('settings.php');

function x_invoice_orders_page()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();
    $invoices       = $all_invoices->get_all_invoices();

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
                    $customerName = $customers->get_customer($invoice->customer_id)->customer_name;

                    $user_info = get_userdata($invoice->visitor_id);
                    $user_name = $user_info ? $user_info->user_login : 'Unknown User';
                    $user_link = $user_info ? admin_url('user-edit.php?user_id=' . $invoice->visitor_id) : '#';
            ?>
                    <tr>
                        <td><?php echo esc_html($invoice->invoice_id); ?></td>
                        <td><a href="<?php echo esc_url($user_link); ?>"><?php echo esc_html($user_name); ?></a></td>
                        <td><?php echo esc_html(number_format($invoice->order_total_pure)); ?></td>
                        <td><?php echo esc_html(number_format($invoice->order_total_final)); ?></td>
                        <td><?php echo esc_html($customerName); ?></td>
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