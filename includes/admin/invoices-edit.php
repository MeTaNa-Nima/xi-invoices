<?php
require_once('settings.php');

function xi_invoice_edit_single()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();

    $allCustomers = $customers->get_all_customers();
    $invoices       = $all_invoices->get_all_invoices();

    if (isset($_GET['invoice_id']) && is_numeric($_GET['invoice_id'])) {
        $invoice_id             = intval($_GET['invoice_id']);
        $invoices               = new Xi_Invoices_Invoice();
        $products               = $invoices->get_product_details($invoice_id, 'sold');
        $returned_products      = $invoices->get_product_details($invoice_id, 'returned');
        $invoice_general_data   = $invoices->get_invoice($invoice_id);
        // $includesReturneds      = ;


        $customer_details       = $customers->get_customer($invoice_general_data->customer_id);
        $user_info              = get_userdata($invoice_general_data->visitor_id);
        // $user_name              = $user_info ? $user_info->user_login : 'Unknown User';
        // $user_link              = $user_info ? admin_url('user-edit.php?user_id=' . $invoice_general_data->visitor_id) : '#';

        $marketers = new WP_User_Query(array('role' => 'marketer'));
        $marketer_users = $marketers->get_results();

?>
        <?php
        if ($invoice_general_data) {
        ?>
            <form action="" method="post">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="postbox-container-2" class="postbox-container">
                            <div class="postbox">
                                <div class="xi-invoice-info invoice-info_general">
                                    <table class="form-table">
                                        <tr>
                                            <th>شماره فاکتور</th>
                                            <th>تاریخ</th>
                                            <th><label for="visitor">ویزیتور ثبت کننده</label></th>
                                        </tr>
                                        <tr>
                                            <td><?php echo $invoice_id; ?></td>
                                            <td><?php echo esc_html($invoice_general_data->date_submit_gmt); ?></td>
                                            <td>
                                                <select name="visitor" id="visitor" class="visitor">
                                                    <option value="">— انتخاب ویزیتور —</option>
                                                    <?php foreach ($marketer_users as $user) : ?>
                                                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($invoice_general_data->visitor_id, $user->ID); ?>>
                                                            <?php echo esc_html($user->display_name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="postbox">
                                <div class="xi-invoice-info invoice-info_customer">
                                    <table class="form-table">
                                        <tr>
                                            <th><label for="tax_amount_value">درصد مالیات معادل</label></th>
                                            <td>
                                                <input type="text" name="tax_amount_value" id="tax_amount_value" class="tax tax_amount_value" value="<?php echo esc_html(number_format($invoice_general_data->order_total_tax)); ?>" pattern="^([0-9]|[1-9][0-9]|100)$" title="Please enter a number between 0 and 100">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="tax_amount_value">مقدار تخفیف (ریال)</label></th>
                                            <td>
                                                <input type="text" id="payment_constant" class="payment_discount_methods payment_constant" name="payment_discount_methods" value="<?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?>" inputmode="numeric">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="include_returned_products">کالای مرجویع</label></th>
                                            <td>
                                                <input type="checkbox" name="include_returned_products" id="include_returned_products" class="include_returned_products" <?php checked(!empty($returned_products)); ?>>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <td></td>
                                        </tr>
                                    </table>

                                    <table class="form-table">
                                        <tr>
                                            <th><label for="customer_name">مشتری</label></th>
                                            <td>
                                                <select name="customer_name" class="customer_name" id="customer_name" onChange="fetchCustomerDetails(this.value)">
                                                    <option value="">— انتخاب مشتری —</option>
                                                    <?php foreach ($allCustomers as $customer) : ?>
                                                        <option value="<?php echo esc_attr($customer->customer_id); ?>" <?php selected($customer_details->customer_id, $customer->customer_id); ?>>
                                                            <?php echo esc_html($customer->customer_name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" class="this_customer_id" name="this_customer_id" value="">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>کد ملی</th>
                                            <td class="customer_national_id"><?php echo esc_html($customer_details->customer_national_id); ?></td>
                                        </tr>
                                        <tr>
                                            <th>نام فروشگاه</th>
                                            <td class="customer_shop_name"><?php echo esc_html($customer_details->customer_shop_name); ?></td>
                                        </tr>
                                        <tr>
                                            <th>آدرس مشتری</th>
                                            <td class="customer_address"><?php echo esc_html($customer_details->customer_address); ?></td>
                                        </tr>
                                    </table>
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
                                <a class="button button-secondary" href="admin.php?page=xi-invoices&invoice_id=<?php echo $invoice_id; ?>">بازگشت</a>
                                <input class="button button-primary" type="submit" value="ذخیره">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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