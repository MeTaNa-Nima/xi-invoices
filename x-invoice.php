<?php
/*
Plugin Name: X-Invoice
Description: This is a Invoice WordPress plugin.
Version: 1.2.2
Author: Nima Amani <metananima@gmail.com>
*/
define('X_INVOICE_VERSION', '1.2.2');
define('X_INVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));
require_once plugin_dir_path(__FILE__) . 'includes/database/db-functions.php';


// OOP STARTED:
require_once plugin_dir_path(__FILE__) . 'includes/classes/class-xi-invoices-customers.php';
require_once plugin_dir_path(__FILE__) . 'includes/classes/class-xi-invoices-products.php';
require_once plugin_dir_path(__FILE__) . 'includes/classes/class-xi-invoices-invoices.php';
require_once plugin_dir_path(__FILE__) . 'includes/classes/class-xi-invoices-reports.php';


// Starting File reStructuring:
require_once plugin_dir_path(__FILE__) . 'includes/jdf.php';
require_once plugin_dir_path(__FILE__) . 'includes/helper-functions.php';

require_once plugin_dir_path(__FILE__) . 'includes/public/shortcodes/x-invoice-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/public/shortcodes/x-invoice-view-order-shortcode.php';


require_once plugin_dir_path(__FILE__) . 'includes/admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/add-customers-data.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/edit-customers-data.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/edit-products-data.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/reports.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/orders_list.php';

require_once plugin_dir_path(__FILE__) . 'includes/admin/invoices.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/invoices-all.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/invoices-single.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/invoices-edit.php';

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';






register_activation_hook(__FILE__, 'x_invoice_activation');

function x_invoice_activation()
{
    x_invoice_check_version();
    x_invoice_create_or_update_tables(); // This function is now in db-functions.php
}

function x_invoice_check_version()
{
    $installed_ver = get_option('x_invoice_version');
    if ($installed_ver != X_INVOICE_VERSION) {
        x_invoice_create_or_update_tables(); // This function is now in db-functions.php
        update_option('x_invoice_version', X_INVOICE_VERSION);
    }
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
        'xi-invoices',           // Menu slug
        'invoice_actions'       // Function to display the submenu page content
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
    wp_enqueue_style('x-invoice-admin-styles', X_INVOICE_PLUGIN_URL . 'assets/css/admin.css');
}
add_action('admin_enqueue_scripts', 'x_invoice_enqueue_admin_styles');


function x_invoice_shortcuts()
{
    $siteUrl            = get_site_url();
    $adminUrl           = admin_url();
    $regPageSlug        = get_option('regPageSlug');
    $regPageUrl         = $siteUrl . '/' . $regPageSlug;
    $reportsPageUrl     = $adminUrl . 'admin.php?page=xi-invoices';
?>
    <div class="xi_shortcuts">
        <a class="xi_btn xi_invoice_shortcuts button-secondary" href="<?php echo $regPageUrl; ?>" target="_blank">ثبت فاکتور جدید</a>
        <a class="xi_btn xi_invoice_shortcuts button-secondary" href="<?php echo $reportsPageUrl; ?>" target="_blank">گزارش فروش من</a>
    </div>
<?php
}

function invoice_main_function()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید | نسخه  ' . X_INVOICE_VERSION . '</h1>';
    x_invoice_shortcuts();
    settings();
    echo '</div>';
}

function invoice_actions()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید</h1>';
    x_invoice_shortcuts();
    x_invoices_page();
    echo '</div>';
}

function invoice_orders_list()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید</h1>';
    x_invoice_shortcuts();
    x_invoice_orders_page();
    echo '</div>';
}


function x_reports()
{
    echo '<div class="wrap">';
    echo '<h1>گزارش ها:</h1>';
    x_invoice_shortcuts();
    x_reports_page();
    echo '</div>';
}

function products_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>افزودن یا ویرایش اطلاعات محصولات</h1>';
    x_invoice_shortcuts();
    showMessage();
    x_invoice_edit_products_page();
    echo '</div>';
}
function add_customer_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>افزودن اطلاعات</h1>';
    x_invoice_shortcuts();
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
    x_invoice_shortcuts();
    showMessage();
    if (isset($_POST['some_other_action'])) {
        x_invoice_edit_customers_page();
    }
    x_invoice_edit_customers_page();
    echo '</div>';
}

function x_invoice_enqueue_scripts()
{
    // Enqueue the common.js script
    wp_enqueue_script(
        'x-invoice-common',
        X_INVOICE_PLUGIN_URL . 'assets/js/common.js',
        array('jquery'), // Dependency on jQuery
        '',             // Version number (optional)
        true            // Load in footer (optional but recommended)
    );


    // Localize the script with new data
    $translation_array = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('x_invoice_nonce')
    );
    wp_localize_script('x-invoice-common', 'myAjax', $translation_array);
}

add_action('wp_enqueue_scripts', 'x_invoice_enqueue_scripts');
add_action('admin_enqueue_scripts', 'x_invoice_enqueue_scripts');



// PDF Creation
function generate_invoice_pdf()
{
    // Ensure the current user has the capability to generate PDFs
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'Insufficient permissions.'));
        return;
    }
    $invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
    if (!$invoice_id) {
        wp_send_json_error(array('message' => 'Invalid Invoice ID.'));
        return;
    }
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

    ////////////////// PDF TEST START //////////////////

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, 'mm', array(80, 3000), true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(5, 5, 5, true); // Adjust these values according to your needs
    $pdf->SetAutoPageBreak(TRUE, 0);



    $fontPath = plugin_dir_path(__FILE__) . 'assets/fonts/IranSansFaNum.ttf';
    $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);


    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Invoice');
    $pdf->SetSubject('Invoice PDF');

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


    $pdf->SetFont($fontname, '', 8);
    $pdf->AddPage();
    $pdf->setRTL(true);

    ////////////////// PDF TEST END //////////////////


    // Fetch the invoice details
    $invoices = new Xi_Invoices_Invoice();
    $invoiceLogoOption  = get_option('invoiceLogo', '');
    $paymentMethod = '';
    $logoURL = '';
    if ($invoiceLogoOption === 'default_site_logo') {
        $logoURL = get_site_logo_url();
    } else if (!empty($invoiceLogoOption)) {
        $logoURL = $invoiceLogoOption;
    }
    $invoice            = $invoices->get_invoice_details($invoice_id);
    $datetime           = new DateTime($invoice->date_submit_gmt);
    $products           = $invoices->get_product_details($invoice_id, 'sold');
    $returned_products  = $invoices->get_product_details($invoice_id, 'returned');
    if (!$invoice) {
        wp_send_json_error(array('message' => 'Invoice details not found.'));
        return;
    }



    // Prepare HTML content
    $html = '<!DOCTYPE html>
                <html dir="rtl" lang="fa-IR">
                <head>
                    <meta charset="UTF-8">
                    <style>
                        @font-face {
                            font-family: "IranSansFaNum";
                            src: url("' . plugin_dir_url(__FILE__) . 'assets/fonts/IranSansFaNum.ttf") format("truetype");
                        }
                        body, p, div, span, table, thead, tbody, tfoot, tr, th, td {
                            font-family: "IranSansFaNum", sans-serif;
                            direction: rtl;
                            text-align: right;
                        }
                        .xi-invoice-view {
                            width: 100% !important;
                            padding: 0 !important;
                            margin: 0 !important;
                            font-size: 0.7em;
                            display: flex;
                            flex-direction: column-reverse;
                            align-items: center;
                        }
                        .xi-invoice-view .xi-invoice-view-form-controls .go-back-btn a,
                        .xi-invoice-view .xi-invoice-view-form-controls .xi-print-invoice a
                         {
                            border: 1px solid;
                            padding: 1em;
                            text-decoration: none;
                            background: #4848ff;
                            color: #fff;
                            cursor: pointer;
                        }
                        .xi-invoice-view .xi-invoice-view-form-controls .go-back-btn,
                        .xi-invoice-view .xi-invoice-view-form-controls .xi-print-invoice {
                            display: flex;
                            flex-direction: row;
                            justify-content: space-between;
                        }
                        .xi-invoice-view .xi-invoice-view-form-controls {
                            display: flex;
                            gap: 1em;
                            flex-direction: column;
                        }
                        .xi-invoice-view table.xi-items-list,
                        .xi-invoice-view table.xi-returned-items-list {
                            width: 100%;
                            margin: 0 0 2em 0;
                        }
                        .xi-invoice-view table.xi-items-list,
                        .xi-invoice-view table.xi-items-list th,
                        .xi-invoice-view table.xi-items-list td,
                        .xi-invoice-view table.xi-returned-items-list,
                        .xi-invoice-view table.xi-returned-items-list th,
                        .xi-invoice-view table.xi-returned-items-list td {
                            border: 1px dashed gray;
                            border-collapse: collapse;
                            text-align: center;
                        }
                        .xi-invoice-view p {
                            margin: 0.3em 0;
                        }
                        .xi-invoice-view hr {
                            border-style: dashed;
                            max-width: 100%;
                            width: 100%;
                        }
                        .xi-invoice-view .xi-invoice-result .xi-final-total {
                            border: 5px solid black;
                            padding: 5px;
                        }
                        .xi-invoice-header .company-name, .xi-invoice-header .company-registration-number {
                            font-weight: 700;
                        }
                        .xi-invoice-view .xi-invoice-footer {
                            font-size: 11px;
                        }
                        .xi-invoice-view .xi-invoice-footer table, .xi-invoice-view .xi-pricing {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 1em;
                        }
                        .xi-invoice-view .xi-invoice-view-form-controls .go-back-btn,
                        .xi-invoice-view .xi-invoice-view-form-controls .xi-print-invoice,
                        .xi-invoice-view .xi-current-date,
                        .xi-invoice-view .xi-invoice-header,
                        .xi-invoice-view .xi-invoice-footer,
                        .xi-invoice-view table.xi-footer-table,
                        .xi-invoice-view table.xi-footer-table th,
                        .xi-invoice-view table.xi-footer-table td {
                            text-align: center;
                        }
                        .xi-invoice-view tr.links td.site {
                            border-left: 1px dashed;
                        }
                        .xi-invoice-view .xi-pricing td.xi-table-prices {
                            text-align: left;
                        }
                        .xi-invoice-view .xi-items-list td,
                        .xi-invoice-view .xi-items-list th,
                        .xi-invoice-view .xi-returned-items-list td,
                        .xi-invoice-view .xi-returned-items-list th,
                        .xi-invoice-view .xi-invoice-footer td {
                            padding: 0.5em 0;
                        }
                        .xi-invoice-view .xi-pricing td {
                            padding: 1em 0;
                        }
                        .xi-invoice-view .xi-pricing tr {
                            border-bottom: 1px dashed;
                            border-top: 1px dashed;
                        }
                        .xi-invoice-view .logo {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-wrap: nowrap;
                        }
                        .xi-invoice-view .xi-invoice-header .logo img {
                            max-width: 100px;
                        }
                        .xi-invoice-view .xi-invoice-header .logo {
                            margin: 1em 0;
                        }
                        .xi-invoice-view .xi-invoice-number {
                            text-align: left;
                        }
                    </style>
                </head>
                <body>';
    // Include a separate PHP file here if you prefer to keep the HTML structure apart
    if ($invoice->payment_method === 'cash') {
        $paymentMethod = 'نقد';
    } elseif ($invoice->payment_method === 'cheque') {
        $paymentMethod = 'چک';
    }
    $html .= '<div class="xi-invoice-result">';
    // Header Start
    $html .= '<div class="xi-invoice-header">';
    $html .= '<div class="logo"><img src="' . $logoURL . '" alt=""></div>';
    $html .= '<div class="company-name">شرکت نیک عطرآگین پارس</div>';
    $html .= '<div class="company-registration-number">شماره ثبت ۱۷۲۰۵</div>';
    $html .= '</div>';
    $html .= '<hr>';
    $html .= esc_html($invoice->visitor_name);
    // Header End
    $html .= '<p class="xi-invoice-number">' . esc_html($invoice->invoice_id) . '</p>';
    $html .= '<p>تاریخ ثبت: ' . esc_html($datetime->format('Y/n/j')) . '</p>';
    $html .= '<p>نام ویزیتور: ' . esc_html($invoice->visitor_name) . '</p>';
    $html .= '<p>نام مشتری: ' . esc_html($invoice->customer_name) . '</p>';
    $html .= '<p>شماره مشتری: ' . esc_html($invoice->customer_mobile_no) . '</p>';
    $html .= '<p>نام فروشگاه: ' . esc_html($invoice->customer_shop_name) . '</p>';
    $html .= '<p>آدرس مشتری: ' . esc_html($invoice->customer_address) . '</p>';
    $html .= '<p>نحوه پرداخت: ' . esc_html($paymentMethod) . '</p>';

    $html .= '<p class="xi-header">اقلام فروخته شده:</p>';
    $html .= '<table class="xi-items-list"><tr><th>نام</th><th>تعداد</th><th>قیمت</th><th>جمع</th></tr>';
    foreach ($products as $product) {
        $html .= '<tr><td>' . esc_html($product->product_name) . '</td><td>' . esc_html($product->product_qty) . '</td><td>' . esc_html(number_format($product->product_net_price)) . '</td><td>' . esc_html(number_format($product->product_total_price)) . '</td></tr>';
    }
    $html .= '</table>';
    if (!empty($returned_products)) {
        $html .= '<p class="xi-header">اقلام مرجوعی:</p>';
        $html .= '<table class="xi-returned-items-list"><tr><th>نام</th><th>تعداد</th><th>قیمت</th><th>جمع</th></tr>';
        foreach ($returned_products as $product) {
            $html .= '<tr><td>' . esc_html($product->product_name) . '</td><td>' . esc_html($product->product_qty) . '</td><td>' . esc_html(number_format($product->product_net_price)) . '</td><td>' . esc_html(number_format($product->product_total_price)) . '</td></tr>';
        }
        $html .= '</table>';
    }
    $html .= '<table class="xi-pricing">';
    $html .= '<tr><td>جمع کل:</td><td class="xi-table-prices">' . esc_html(number_format($invoice->order_total_pure)) . ' ریال</td></tr>';
    if ($invoice->order_include_tax === 'yes') {
        $html .= '<tr><td>مقدار مالیات:</td><td class="xi-table-prices"> %' . esc_html(number_format($invoice->order_total_tax)) . '</td></tr>';
    }
    if ($invoice->order_include_discount === 'yes') {
        if ($invoice->discount_method === 'percent') {
            $html .= '<tr><td>مقدار تخفیف:</td><td class="xi-table-prices"> %' . esc_html(number_format($invoice->discount_total_percentage)) . ' معادل ' . esc_html(number_format($invoice->discount_total_amount)) . ' ریال</td></tr>';
        } elseif ($invoice->discount_method === 'constant') {
            $html .= '<tr><td>مقدار تخفیف:</td><td class="xi-table-prices"> ' . esc_html(number_format($invoice->discount_total_amount)) . ' ریال</td></tr>';
        }
    }
    $html .= '<tr><td>مبلغ نهایی:</td><td class="xi-table-prices">' . esc_html(number_format($invoice->order_total_final)) . ' ریال</td></tr>';
    $html .= '</table>';
    // Footer End
    $html .= '<div class="xi-invoice-footer">';
    $html .= '<div class="thanks-message">تشکر از خرید شما، به امید دیدار مجدد.</div>';
    $html .= '<table class="xi-footer-table"><tbody>';
    $html .= '<tr><td>مدیر فروش (استادی)</td><td>۰۹۳۰۴۹۴۶۶۹۴</td></tr>';
    $html .= '<tr><td>مدیر تولید (قنبری)</td><td>۰۹۱۹۸۵۲۱۸۵۶</td></tr>';
    $html .= '<tr class="links"><td class="site">www.NikPerfume.com</td><td class="instagram">Instagram: NiikPerfume</td></tr>';
    $html .= '</tbody></table>';
    $html .= '</div>';
    // Footer End
    $html .= '</div>';
    $html .= '</body></html>';

    // Define the PDF file path
    $upload_dir = wp_upload_dir();
    $pdf_dir_path = trailingslashit($upload_dir['basedir']) . 'invoices/';
    if (!file_exists($pdf_dir_path)) {
        wp_mkdir_p($pdf_dir_path);
    }

    // Generate the unique filename for the PDF
    $filename_base = tr_num(jdate('Ymd')) . $invoice_id;
    $counter = 1;
    do {
        $pdf_filename = $filename_base . str_pad($counter, 2, '0', STR_PAD_LEFT) . '.pdf';
        $pdf_file_path = $pdf_dir_path . $pdf_filename;
        $counter++;
    } while (file_exists($pdf_file_path));

    $pdf->WriteHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    // Close and output PDF document
    $pdf->Output($pdf_file_path, 'F'); // Output the PDF to the browser (I: Inline, D: Download)

    $pdf_url = trailingslashit($upload_dir['baseurl']) . 'invoices/' . $pdf_filename;
    $invoices->update_invoice_pdf_url($invoice_id, $pdf_url);
    wp_send_json_success(array('message' => 'PDF generated successfully', 'pdf_url' => $pdf_url));
}
add_action('wp_ajax_generate_invoice_pdf', 'generate_invoice_pdf');
// Uncomment the next line to allow non-logged-in users to access this AJAX action
// add_action('wp_ajax_nopriv_generate_invoice_pdf', 'generate_invoice_pdf');
