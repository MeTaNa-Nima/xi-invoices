<?php
/*
Plugin Name: X-Invoice
Description: This is a Invoice WordPress plugin.
Version: 1.1.0
Author: Nima Amani <metananima@gmail.com>
*/
define('X_INVOICE_VERSION', '1.1.1');
define('X_INVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));
require_once plugin_dir_path(__FILE__) . 'includes/database/db-functions.php';



// OOP STARTED:
require_once plugin_dir_path( __FILE__ ) . 'includes/classes/class-xi-invoices-customers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/classes/class-xi-invoices-products.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/classes/class-xi-invoices-invoices.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/classes/class-xi-invoices-reports.php';



// Starting File reStructuring:
require_once plugin_dir_path( __FILE__ ) . 'includes/jdf.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/helper-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/public/shortcodes/x-invoice-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/public/shortcodes/x-invoice-view-order-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/add-customers-data.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/edit-customers-data.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/edit-products-data.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/reports.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/admin/invoices.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/invoices-all.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/invoices-single.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/invoices-edit.php';


require_once plugin_dir_path( __FILE__ ) . 'includes/admin/orders_list.php';



register_activation_hook(__FILE__, 'x_invoice_activation');

function x_invoice_activation() {
    x_invoice_check_version();
    x_invoice_create_or_update_tables(); // This function is now in db-functions.php
}

function x_invoice_check_version() {
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
    // $xi_edit_customer_data = add_submenu_page(
    //     'x-invoice',
    //     'all invoices',
    //     'all invoices',
    //     'manage_options',
    //     'xi-invoices',
    //     'invoice_actions'
    // );

}
add_action('admin_menu', 'x_invoice_plugin_admin_menu');



function x_invoice_enqueue_admin_styles($hook_suffix)
{
    wp_enqueue_style('x-invoice-admin-styles', X_INVOICE_PLUGIN_URL . 'assets/css/admin.css');
}
add_action('admin_enqueue_scripts', 'x_invoice_enqueue_admin_styles');



function invoice_main_function()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید | نسخه  ' . X_INVOICE_VERSION . '</h1>';
    settings();
    echo '</div>';
}

function invoice_actions()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید</h1>';
    x_invoices_page();
    echo '</div>';
}

function invoice_orders_list()
{
    echo '<div class="wrap">';
    echo '<h1>به افزونه ایکس فاکتور خوش آمدید</h1>';
    x_invoice_orders_page();
    echo '</div>';
}


function x_reports()
{
    echo '<div class="wrap">';
    echo '<h1>گزارش ها:</h1>';
    x_reports_page();
    echo '</div>';
}

function products_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>افزودن یا ویرایش اطلاعات محصولات</h1>';
    showMessage();
    x_invoice_edit_products_page();
    echo '</div>';
}
function add_customer_data_page()
{
    echo '<div class="wrap">';
    echo '<h1>افزودن اطلاعات</h1>';
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
    showMessage();
    if (isset($_POST['some_other_action'])) {
        x_invoice_edit_customers_page();
    }
    x_invoice_edit_customers_page();
    echo '</div>';
}











// PDF Creation
add_action('wp_ajax_generate_invoice_pdf', 'generate_invoice_pdf');
function generate_invoice_pdf()
{
    global $wpdb;
    $invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;

    // Fetch the invoice data just like in your shortcode
    // ...

    // Initialize DOMPDF
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    $dompdf = new Dompdf\Dompdf();

    // Generate the HTML for the invoice
    $html = 'TEST TEST TEST'; // Your HTML content goes here. You can use ob_start() and ob_get_clean() to capture output from includes

    // Load the HTML and render the PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save the PDF to the server
    $pdf_output = $dompdf->output();
    $pdf_dir_path = wp_upload_dir()['basedir'] . '/invoices/';
    $pdf_file_path = $pdf_dir_path . 'xi-invoice-' . $invoice_id . '.pdf';

    if (!file_exists($pdf_dir_path)) {
        mkdir($pdf_dir_path, 0777, true);
    }

    file_put_contents($pdf_file_path, $pdf_output);

    // Send a response
    wp_send_json_success(array('message' => 'PDF generated successfully', 'pdf_url' => $pdf_file_path));
}
