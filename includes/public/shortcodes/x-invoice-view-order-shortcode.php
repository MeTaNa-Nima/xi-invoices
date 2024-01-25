<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}


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
            WHERE dl.order_id = %d AND dl.product_sale_return = 'sold'", // Filter by product_sale_return
            $invoice_number
        ));

        // Additional code for displaying returned products if needed
        $returned_products = $wpdb->get_results($wpdb->prepare(
            "SELECT dl.*, p.product_name
            FROM $lookup_table dl
            JOIN $products_table p ON dl.product_id = p.product_id
            WHERE dl.order_id = %d AND dl.product_sale_return = 'returned'", // Fetch returned products
            $invoice_number
        ));
    }
    // Check if the invoice exists
    if ($invoice) {
        $xi_invoice_view_output .= '<div class="xi-invoice-result">';
        // Header Start
        $xi_invoice_view_output .= '<div class="xi-invoice-header">';
        // $xi_invoice_view_output .= '<div class="logo">لوگو اینجا میباشد</div>';
        $xi_invoice_view_output .= '<div class="logo"><img src="' . X_INVOICE_PLUGIN_URL . '/assets/images/july-logo.jpg" alt=""></div>';
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

        $xi_invoice_view_output .= '<p class="xi-header"><b>اقلام فروخته شده:</b></p>';
        $xi_invoice_view_output .= '<table class="xi-items-list"><tr><th>نام</th><th>تعداد</th><th>قیمت</th><th>جمع</th></tr>';
        foreach ($products as $product) {
            $xi_invoice_view_output .= '<tr><td>' . esc_html($product->product_name) . '</td><td>' . esc_html($product->product_qty) . '</td><td>' . esc_html(number_format($product->product_net_price)) . '</td><td>' . esc_html(number_format($product->product_total_price)) . '</td></tr>';
        }
        $xi_invoice_view_output .= '</table>';
        if (!empty($returned_products)) {
            $xi_invoice_view_output .= '<p class="xi-header"><b>اقلام مرجوعی:</b></p>';
            $xi_invoice_view_output .= '<table class="xi-returned-items-list"><tr><th>نام</th><th>تعداد</th><th>قیمت</th><th>جمع</th></tr>';
            foreach ($returned_products as $product) {
                $xi_invoice_view_output .= '<tr><td>' . esc_html($product->product_name) . '</td><td>' . esc_html($product->product_qty) . '</td><td>' . esc_html(number_format($product->product_net_price)) . '</td><td>' . esc_html(number_format($product->product_total_price)) . '</td></tr>';
            }
            $xi_invoice_view_output .= '</table>';
        }
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
        $xi_invoice_view_output .= '<tr class="links"><td class="site">www.NikPerfume.com</td><td class="instagram">Instagram: NiikPerfume</td></tr>';
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

    return $xi_invoice_view_output;
}
add_shortcode('x-invoice_view_order', 'x_invoice_view_order_shortcode');