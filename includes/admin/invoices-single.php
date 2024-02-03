<?php
require_once('settings.php');

function xi_invoice_show_single()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();
    $invoices       = $all_invoices->get_all_invoices();
    // print_r($_GET);

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
        if (!isset($_GET['print_view'])) {

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
                                            echo '<p><b>درصد مالیات:</b> ' . esc_html(number_format($invoice_general_data->order_total_tax)) . '</p>';
                                        }
                                        if ($invoice_general_data->order_include_discount === 'yes') {
                                            if ($invoice_general_data->discount_method === 'percent') {
                                                echo '<p><b>مقدار تخفیف:</b> ' . esc_html(number_format($invoice_general_data->discount_total_percentage)) . ' % معادل ' . esc_html(number_format($invoice_general_data->discount_total_amount)) . ' ریال </p>';
                                            } elseif ($invoice_general_data->discount_method === 'constant') {
                                                echo '<p><b>مقدار تخفیف:</b> ' . esc_html(number_format($invoice_general_data->discount_total_amount)) . ' ریال </p>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div>
                                        <?php
                                        echo '<p><b>نام مشتری:</b> ' . esc_html($customer_details->customer_name) . '</p>';
                                        echo '<p><b>شماره موبایل:</b> ' . esc_html($customer_details->customer_mobile_no) . '</p>';
                                        echo '<p><b>نام فروشگاه:</b> ' . esc_html($customer_details->customer_shop_name) . '</p>';
                                        echo '<p><b>آدرس مشتری:</b> ' . esc_html($customer_details->customer_address) . '</p>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="postbox">
                                <table class="form-table striped table-view-list widefat wp-list-table productsList" id="productsList">
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
                                <table class="form-table striped table-view-list widefat wp-list-table returned_productsList" id="returned_productsList">
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
                                            <td class="total"><?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?></td>
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
        } elseif (isset($_GET['print_view'])) {
            $invoiceLogoOption  = get_option('invoiceLogo', '');

            $logoURL = '';
            if ($invoiceLogoOption === 'default_site_logo') {
                $logoURL = get_site_logo_url();
            } else if (!empty($invoiceLogoOption)) {
                $logoURL = $invoiceLogoOption;
            }
            if ($invoice_general_data->payment_method === 'cash') {
                $paymentMethod = 'نقد';
            } elseif ($invoice_general_data->payment_method === 'cheque') {
                $paymentMethod = 'چک';
            }
            ?>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="postbox-container-2" class="postbox-container">
                        <div class="postbox">
                            <div class="xi-invoice-result">
                                <!-- Header Start -->
                                <div class="xi-invoice-header">
                                    <div class="logo"><img src="<?php echo $logoURL; ?>" alt=""></div>
                                    <div class="company-name">شرکت نیک عطرآگین پارس</div>
                                    <div class="company-registration-number">شماره ثبت ۱۷۲۰۵</div>
                                </div>
                                <hr>
                                <!-- Header End -->
                                <p class="xi-invoice-number"><b><?php echo esc_html($invoice_general_data->invoice_id); ?></b></p>
                                <p><b>تاریخ ثبت:</b> <?php echo esc_html($invoice_general_data->date_submit_gmt); ?></p>
                                <p><b>نام ویزیتور:</b> <?php echo esc_html($invoice_general_data->visitor_name); ?></p>
                                <p><b>نام مشتری:</b> <?php echo esc_html($customer_details->customer_name); ?></p>
                                <p><b>نام فروشگاه:</b> <?php echo esc_html($customer_details->customer_shop_name); ?></p>
                                <p><b>آدرس مشتری:</b> <?php echo esc_html($customer_details->customer_address); ?></p>
                                <p><b>نحوه پرداخت:</b> <?php echo esc_html($paymentMethod); ?></p>

                                <p class="xi-header"><b>اقلام فروخته شده:</b></p>
                                <table class="xi-items-list">
                                    <tr>
                                        <th>نام</th>
                                        <th>تعداد</th>
                                        <th>قیمت</th>
                                        <th>جمع</th>
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
                                </table>
                                <?php
                                if (!empty($returned_products)) {
                                ?>
                                    <p class="xi-header"><b>اقلام مرجوعی:</b></p>
                                    <table class="xi-returned-items-list">
                                        <tr>
                                            <th>نام</th>
                                            <th>تعداد</th>
                                            <th>قیمت</th>
                                            <th>جمع</th>
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
                                    </table>
                                <?php
                                }
                                ?>
                                <table class="xi-pricing">
                                    <tr>
                                        <td><b>جمع کل:</b></td>
                                        <td class="xi-table-prices"><?php echo esc_html(number_format($invoice_general_data->order_total_pure)); ?> ریال</td>
                                    </tr>
                                    <?php
                                    if ($invoice_general_data->order_include_tax === 'yes') {
                                    ?>
                                        <tr>
                                            <td><b>مقدار مالیات:</b></td>
                                            <td class="xi-table-prices"> %<?php echo esc_html(number_format($invoice_general_data->order_total_tax)); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    if ($invoice_general_data->order_include_discount === 'yes') {
                                        if ($invoice_general_data->discount_method === 'percent') {
                                        ?>
                                            <tr>
                                                <td><b>مقدار تخفیف:</b></td>
                                                <td class="xi-table-prices"> %<?php echo esc_html(number_format($invoice_general_data->discount_total_percentage)); ?> معادل <?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?> ریال</td>
                                            </tr>
                                        <?php
                                        } elseif ($invoice_general_data->discount_method === 'constant') {
                                        ?>
                                            <tr>
                                                <td><b>مقدار تخفیف:</b></td>
                                                <td class="xi-table-prices"> <?php echo esc_html(number_format($invoice_general_data->discount_total_amount)); ?> ریال</td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><b>مبلغ نهایی:</b></td>
                                        <td class="xi-table-prices"><?php echo esc_html(number_format($invoice_general_data->order_total_final)); ?> ریال</td>
                                    </tr>
                                </table>
                                <!-- Footer End -->
                                <div class="xi-invoice-footer">
                                    <div class="thanks-message"><b>تشکر از خرید شما، به امید دیدار مجدد.</b></div>
                                    <table class="xi-footer-table">
                                        <tbody>
                                            <tr>
                                                <td>مدیر فروش (استادی)</td>
                                                <td>۰۹۳۰۴۹۴۶۶۹۴</td>
                                            </tr>
                                            <tr>
                                                <td>مدیر تولید (قنبری)</td>
                                                <td>۰۹۱۹۸۵۲۱۸۵۶</td>
                                            </tr>
                                            <tr class="links">
                                                <td class="site">www.NikPerfume.com</td>
                                                <td class="instagram">Instagram: NiikPerfume</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Footer End -->
                            </div>
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