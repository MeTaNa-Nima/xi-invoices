<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}


function x_invoice_display_invoice_details($atts)
{
    $atts = shortcode_atts(array(
        'invoice_id' => '',
    ), $atts, 'x_invoice_details');

    $invoice_id = intval($atts['invoice_id']);
    if (!$invoice_id) {
        return 'Invalid Invoice ID.';
    }

    $invoices = new Xi_Invoices_Invoice();
    $invoice_details = $invoices->get_invoice($invoice_id);

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
    $invoices = new Xi_Invoices_Invoice();
    $invoiceLogoOption  = get_option('invoiceLogo', '');
    $regPageSlug        = get_option('regPageSlug');
    $paymentMethod = '';
    $logoURL = '';
    if ($invoiceLogoOption === 'default_site_logo') {
        $logoURL = get_site_logo_url();
    } else if (!empty($invoiceLogoOption)) {
        $logoURL = $invoiceLogoOption;
    }

    // Check if an invoice number is submitted or fetch the last invoice number
    if (isset($_POST['invoice_number']) && !empty($_POST['invoice_number'])) {
        $invoice_number = intval($_POST['invoice_number']); // Sanitize the input
    } else {
        $invoice_number = $invoices->get_last_inserted_invoice_id();   
    }

    ob_start();
    $xi_invoice_view_output = '<div class="xi-invoice-view">';
    if ($invoice_number) {
        $invoice = $invoices->get_invoice_details($invoice_number);
        $products = $invoices->get_product_details($invoice_number, 'sold');
        $returned_products = $invoices->get_product_details($invoice_number, 'returned');
        $datetime = new DateTime($invoice->date_submit_gmt);
        if ($invoice) {
            
            if ($invoice->payment_method === 'cash') {
                $paymentMethod = 'نقد';
            } elseif ($invoice->payment_method === 'cheque')  {
                $paymentMethod = 'چک';
            }
            $xi_invoice_view_output .= '<div class="xi-invoice-result">';
            // Header Start
            $xi_invoice_view_output .= '<div class="xi-invoice-header">';
            $xi_invoice_view_output .= '<div class="logo"><img src="' . $logoURL . '" alt=""></div>';
            $xi_invoice_view_output .= '<div class="company-name">شرکت نیک عطرآگین پارس</div>';
            $xi_invoice_view_output .= '<div class="company-registration-number">شماره ثبت ۱۷۲۰۵</div>';
            $xi_invoice_view_output .= '</div>';
            $xi_invoice_view_output .= '<hr>';
            // Header End
            $xi_invoice_view_output .= '<p class="xi-invoice-number"><b>' . esc_html($invoice->invoice_id) . '</b></p>';
            $xi_invoice_view_output .= '<p><b>تاریخ ثبت:</b> ' . esc_html($datetime->format('Y/n/j')) . '</p>';
            $xi_invoice_view_output .= '<p><b>نام ویزیتور:</b> ' . esc_html($invoice->visitor_name) . '</p>';
            $xi_invoice_view_output .= '<p><b>نام مشتری:</b> ' . esc_html($invoice->customer_name) . '</p>';
            $xi_invoice_view_output .= '<p><b>شماره مشتری:</b> ' . esc_html($invoice->customer_mobile_no) . '</p>';
            $xi_invoice_view_output .= '<p><b>نام فروشگاه:</b> ' . esc_html($invoice->customer_shop_name) . '</p>';
            $xi_invoice_view_output .= '<p><b>آدرس مشتری:</b> ' . esc_html($invoice->customer_address) . '</p>';
            $xi_invoice_view_output .= '<p><b>نحوه پرداخت:</b> ' . esc_html($paymentMethod) . '</p>';
    
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
            $xi_invoice_view_output .= '<tr><td><b>جمع کل:</b></td><td class="xi-table-prices">' . esc_html(number_format($invoice->order_total_pure)) . ' ریال</td></tr>';
            if ($invoice->order_include_tax === 'yes') {
                $xi_invoice_view_output .= '<tr><td><b>مقدار مالیات:</b></td><td class="xi-table-prices"> %' . esc_html(number_format($invoice->order_total_tax)) . '</td></tr>';
            }
            if ($invoice->order_include_discount === 'yes') {
                if ($invoice->discount_method === 'percent') {
                    $xi_invoice_view_output .= '<tr><td><b>مقدار تخفیف:</b></td><td class="xi-table-prices"> %' . esc_html(number_format($invoice->discount_total_percentage)) . ' معادل ' . esc_html(number_format($invoice->discount_total_amount)) .' ریال</td></tr>';
                } elseif ($invoice->discount_method === 'constant') {
                    $xi_invoice_view_output .= '<tr><td><b>مقدار تخفیف:</b></td><td class="xi-table-prices"> ' . esc_html(number_format($invoice->discount_total_amount)) . ' ریال</td></tr>';
                }
            }
            $xi_invoice_view_output .= '<tr><td><b>مبلغ نهایی:</b></td><td class="xi-table-prices">' . esc_html(number_format($invoice->order_total_final)) . ' ریال</td></tr>';
            $xi_invoice_view_output .= '</table>';
            // Footer End
            $xi_invoice_view_output .= '<div class="xi-invoice-footer">';
            $xi_invoice_view_output .= '<div class="thanks-message"><b>تشکر از خرید شما، به امید دیدار مجدد.</b></div>';
            $xi_invoice_view_output .= '<table class="xi-footer-table"><tbody>';
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
    } else {
        $xi_invoice_view_output .= '<div class="xi-invoice-result">No invoice number provided.</div>';
    }


    // Form HTML
    $xi_invoice_view_output .= '
    <div class="xi-invoice-view-form-controls">
        <div class="go-back-btn xi_btn_group">
            <a class="xi_btn" href="' . get_site_url() . '/' . $regPageSlug . '">ثبت فاکتور جدید</a>
            <a class="xi_btn" href="' . get_site_url() . '/' . 'wp-admin/admin.php?page=xi-invoices' . '" target="_blank">فاکتور های من</a>
        </div>
        <div class="xi-search-invoice">
            <form id="x-invoice-view" class="x-invoice-view" action="" method="post">
                <div class="xi-invoice-search">
                    <input type="number" name="invoice_number" id="invoice_number" placeholder="جستجو بر اساس شماره فاکتور">
                    <input type="submit" id="search_invoice" value="جستجو">
                </div>
            </form>
        </div>
        <div class="xi-print-invoice xi_btn_group">
            <a class="xi-invoice-print xi_btn" onClick="window.print()">چاپ فاکتور</a>
            <a href="#" class="xi-invoice-save-pdf xi_btn" data-invoice-id="' . $invoice_number . '">ذخیره pdf در سایت</a>
        </div>
        <input class="xi-pdf-url" readonly type="text" value="' . $invoice->invoice_pdf_link . '" placeholder="برای دریافت لینک، ابتدا pdf را تولید کنید.">
        </div>';
    $xi_invoice_view_output .= ob_get_clean();

    return $xi_invoice_view_output;
}
add_shortcode('x-invoice_view_order', 'x_invoice_view_order_shortcode');


?>

