<?php
require_once('settings.php');

function xi_invoice_edit_single()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();
    $all_products   = (new Xi_Invoices_Products())->get_all_products();

    $allCustomers   = $customers->get_all_customers();
    $invoices       = $all_invoices->get_all_invoices();

    if (isset($_GET['invoice_id']) && is_numeric($_GET['invoice_id'])) {
        $invoice_id             = intval($_GET['invoice_id']);
        $invoices               = new Xi_Invoices_Invoice();
        $products               = $invoices->get_product_details($invoice_id, 'sold');
        $returned_products      = $invoices->get_product_details($invoice_id, 'returned');
        $invoice_general_data   = $invoices->get_invoice($invoice_id);
        $customer_details       = $customers->get_customer($invoice_general_data->customer_id);

        $user_role_slugs = array(
            'role__in' => array('marketer', 'administrator') // Array of roles
        );
        $marketers = new WP_User_Query($user_role_slugs);
        $marketer_users = $marketers->get_results();


        // Update invoice Start
        if (isset($_POST) && !empty($_POST) && isset($_POST['update_invoice'])) {
            $invoice_id;
            $invoice_data = array(
                'visitor_id'                => $_POST['visitor'],
                'customer_id'               => $_POST['customer_name'],
                'order_total_pure'          => $_POST['order_total_pure'],
                'order_total_tax'           => $_POST['order_total_tax'],
                'discount_total_amount'     => $_POST['discount_constant'],
                'order_total_final'         => $_POST['order_total_final'],
                'include_returned_products' => $_POST['include_returned_products'],
            );

            $update_invoice = $invoices->update_invoice($invoice_id, $invoice_data);

            if ($update_invoice === false) {
                // Update failed
                printf('<pre>%s</pre>', print_r($invoice_data, true));
                echo '<br>';
                global $wpdb;
                echo $wpdb->last_error;
                echo '<br>';
                echo "Update failed";
            } else {
                // Update successful or no rows affected
                echo "Update successful or no rows affected";
                echo "<script type='text/javascript'>window.location.href = 'admin.php?page=xi-invoices&edit_mode=1&invoice_id=" . $invoice_id . ";</script>";
            }
        }
        // Update invoice End


?>
        <?php
        if ($invoice_general_data) {
        ?>
            <form class="x-invoice-edit" id="x-invoice-edit" action="" method="post">
                <input style="display: none; visibility: hidden; opacity: 0;" type="checkbox" name="invoice_discount" id="invoice_discount" class="invoice_discount" checked>
                <input style="display: none; visibility: hidden; opacity: 0;" type="radio" id="payment_constant" class="payment_discount_methods payment_constant" name="payment_discount_methods" value="constant" checked>
                <input style="display: none; visibility: hidden; opacity: 0;" type="checkbox" name="invoice_includes_tax" id="invoice_includes_tax" class="invoice_includes_tax" checked>

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
                                                    <option value="-1">— انتخاب ویزیتور —</option>
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
                                                <input readonly type="text" name="tax_amount_value" id="tax_amount_value" class="tax tax_amount_value" value="<?php echo esc_html(number_format($invoice_general_data->order_total_tax)); ?>" pattern="^([0-9]|[1-9][0-9]|100)$" title="Please enter a number between 0 and 100">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="payment_constant">مقدار تخفیف (ریال)</label></th>
                                            <td>
                                                <input readonly type="text" id="payment_constant" class="payment_discount_methods payment_constant" name="payment_discount_methods" value="<?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?>" inputmode="numeric">
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
                                                    <option value="-1">— انتخاب مشتری —</option>
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
                                            <th>شماره موبایل</th>
                                            <td class="customer_mobile_no"><?php echo esc_html($customer_details->customer_mobile_no); ?></td>
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
                                <table class="form-table striped table-view-list widefat wp-list-table productsList" id="productsList">
                                    <thead>
                                        <tr>
                                            <td colspan="5"><b>محصولات فروخته شده</b></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>محصول</th>
                                            <th>تعداد</th>
                                            <th>قیمت واحد</th>
                                            <th>قیمت کل</th>
                                            <th>افزودن/حذف
                                                <br>ردیف
                                            </th>
                                        </tr>
                                        <?php
                                        foreach ($products as $product) {
                                        ?>
                                            <tr>
                                                <td class="x_invoice_table_td products_col">
                                                    <select name="custom_product_name[]" class="custom_product_name">
                                                        <option value="-1">— انتخاب محصول —</option>
                                                        <?php foreach ($all_products as $data) : ?>
                                                            <option value="<?php echo esc_attr($data->product_id); ?>" <?php selected($data->product_id, $product->product_id); ?>>
                                                                <?php echo esc_html($data->product_name); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td class="x_invoice_table_td qty_col"><input class="custom_product_amount" type="text" inputmode="numeric" value="<?php echo esc_html($product->product_qty); ?>"></td>
                                                <td class="x_invoice_table_td price_col"><input class="custom_product_price" type="text" inputmode="numeric" value="<?php echo esc_html(number_format($product->product_net_price)); ?>"></td>
                                                <td class="x_invoice_table_td total_price_col">
                                                    <span class="custom_product_show_only"><?php echo esc_html(number_format($product->product_total_price)); ?></span>
                                                    <input readonly type="hidden" class="custom_product_total" value="<?php echo esc_html(number_format($product->product_total_price)); ?>">
                                                </td>
                                                <td class="x_invoice_table_td x_invoice_table_td_btn">
                                                    <button class="add_new_row add_new_product_row">+</button>
                                                    <button class="remove_this_row remove_product_row" style="display: none;">—</button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td>
                                                <div class="invoice_total_title">
                                                    جمع کل قبل از تخفیف:
                                                </div>
                                            </td>
                                            <td colspan="2">
                                                <div class="invoice_total_output">
                                                    <span class="invoice_total_pure_show_only"></span>
                                                    <input readonly type="hidden" name="invoice_total_pure" class="invoice_total_pure" value=""> ریال
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php
                            if (!empty($returned_products)) {
                            ?>
                                <div class="postbox">
                                    <table class="form-table striped table-view-list widefat wp-list-table returned_productsList" id="returned_productsList">
                                        <thead>
                                            <tr>
                                                <td colspan="5"><b>محصولات مرجوعی</b></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th>محصول</th>
                                                <th>تعداد</th>
                                                <th>قیمت واحد</th>
                                                <th>قیمت کل</th>
                                                <th>افزودن/حذف
                                                    <br>ردیف
                                                </th>
                                            </tr>
                                            <?php
                                            foreach ($returned_products as $product) {
                                            ?>
                                                <tr>
                                                    <td class="x_invoice_table_td products_col">
                                                        <select name="custom_product_name[]" class="custom_product_name">
                                                            <option value="-1">— انتخاب محصول —</option>
                                                            <?php foreach ($all_products as $data) : ?>
                                                                <option value="<?php echo esc_attr($data->product_id); ?>" <?php selected($data->product_id, $product->product_id); ?>>
                                                                    <?php echo esc_html($data->product_name); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td class="x_invoice_table_td qty_col"><input class="custom_product_amount" type="text" inputmode="numeric" value="<?php echo esc_html($product->product_qty); ?>"></td>
                                                    <td class="x_invoice_table_td price_col"><input class="custom_product_price" type="text" inputmode="numeric" value="<?php echo esc_html(number_format($product->product_net_price)); ?>"></td>
                                                    <td class="x_invoice_table_td total_price_col">
                                                        <span class="custom_product_show_only"><?php echo esc_html(number_format($product->product_total_price)); ?></span>
                                                        <input readonly type="hidden" class="custom_product_total" value="<?php echo esc_html(number_format($product->product_total_price)); ?>">
                                                    </td>
                                                    <td class="x_invoice_table_td x_invoice_table_td_btn">
                                                        <button class="add_new_row add_new_returned_row">+</button>
                                                        <button class="remove_this_row remove_returned_row" style="display: none;">—</button>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2"></td>
                                                <td>
                                                    <div class="invoice_total_title">
                                                        جمع کل مرجوعی ها:
                                                    </div>
                                                </td>
                                                <td colspan="2">
                                                    <div class="invoice_total_returned_output">
                                                        <span class="invoice_total_returned_pure_show_only"></span>
                                                        <input readonly type="hidden" name="invoice_total_returned_pure" class="invoice_total_returned_pure" value=""> ریال
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php
                            }
                            ?>
                            <div class="postbox" style="float: left;">
                                <table class="xi-order-totals  table-view-list wp-list-table">
                                    <tbody>
                                        <tr>
                                            <td class="label">
                                                <div class="invoice_total_title">
                                                    جمع موارد:
                                                </div>
                                            </td>
                                            <td width="1%"></td>
                                            <td class="total">
                                                <div class="invoice_total_output">
                                                    <span class="invoice_total_prices_show_only"></span>
                                                    <input readonly type="hidden" name="invoice_total_prices" class="invoice_total_prices" value="<?php echo esc_html(number_format($invoice_general_data->order_total_pure)); ?>"> ریال
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label"><label for="tax_amount">درصد مالیات معادل:</label></td>
                                            <td width="1%"></td>
                                            <td class="total">
                                                %<?php echo esc_html(number_format($invoice_general_data->order_total_tax)); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label">تخفیف:</td>
                                            <td width="1%"></td>
                                            <td class="total">
                                                <input type="number" name="discount_constant" id="discount_constant" class="discounts discount_constant" value="<?php echo esc_html($invoice_general_data->discount_total_amount); ?>" placeholder="مقدار فعلی: <?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label">جمع کل نهایی:</td>
                                            <td width="1%"></td>
                                            <td class="total">
                                                <span class="invoice_total_prices_show_only"><?php echo esc_html(number_format($invoice_general_data->order_total_final)); ?> ریال</span>
                                                <input readonly type="hidden" name="invoice_total_prices" class="invoice_total_prices" value=""> ریال
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="postbox-container-1" class="postbox-container">
                            <div class="postbox">
                                <a class="button button-secondary" href="admin.php?page=xi-invoices&invoice_id=<?php echo $invoice_id; ?>">بازگشت</a>
                                <input class="button button-primary" name="update_invoice" type="submit" value="ذخیره">
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
