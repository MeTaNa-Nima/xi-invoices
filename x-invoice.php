<?php
/*
Plugin Name: X-Invoice
Description: This is a Invoice WordPress plugin.
Version: 1.1.6
Author: Nima Amani <metananima@gmail.com>
*/
define('X_INVOICE_VERSION', '1.1.6');
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





function x_invoice_enqueue_scripts() {
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



function xi_hide_admin_bar_for_marketers($show_admin_bar) {
    $user = wp_get_current_user();
    if (in_array('marketer', $user->roles)) {
        return false; // Hide admin bar
    }
    return $show_admin_bar; // Otherwise, show admin bar as usual
}
add_filter('show_admin_bar', 'xi_hide_admin_bar_for_marketers');




// PDF Creation
function generate_invoice_pdf() {
    // Check for nonce for security here (if you passed it in AJAX call)
    // if ( !wp_verify_nonce( $_POST['nonce'], 'generate_pdf_nonce' ) ) {
    //     wp_send_json_error( array( 'message' => 'Nonce verification failed.' ) );
    //     return;
    // }

    // Ensure the current user has the capability to generate PDFs
    if ( !current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
        return;
    }

    $invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
    if (!$invoice_id) {
        wp_send_json_error(array('message' => 'Invalid Invoice ID.'));
        return;
    }

    // Fetch the invoice details
    $invoices = new Xi_Invoices_Invoice();
    $invoice_details = $invoices->get_invoice_details($invoice_id);
    if ( !$invoice_details ) {
        wp_send_json_error(array('message' => 'Invoice details not found.'));
        return;
    }

    // Assuming you've properly included the DOMPDF library
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    $dompdf = new Dompdf\Dompdf();

    // Construct the HTML for the invoice
    ob_start();
    // Include a separate PHP file here if you prefer to keep the HTML structure apart
    echo '<h1>Invoice Details for ID: ' . esc_html($invoice_id) . '</h1>';
    // Build your HTML content here based on $invoice_details
    $html = ob_get_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Define the PDF file path
    $upload_dir = wp_upload_dir();
    $pdf_dir_path = trailingslashit( $upload_dir['basedir'] ) . 'invoices/';
    if ( ! file_exists( $pdf_dir_path ) ) {
        wp_mkdir_p( $pdf_dir_path );
    }

    // Generate the unique filename for the PDF
    $filename_base = tr_num(jdate('Ymd')) . $invoice_id;
    $counter = 1;
    do {
        $pdf_filename = $filename_base . str_pad($counter, 2, '0', STR_PAD_LEFT) . '.pdf';
        $pdf_file_path = $pdf_dir_path . $pdf_filename;
        $counter++;
    } while (file_exists($pdf_file_path));

    file_put_contents($pdf_file_path, $dompdf->output());

    $pdf_url = trailingslashit( $upload_dir['baseurl'] ) . 'invoices/' . $pdf_filename;
    wp_send_json_success(array('message' => 'PDF generated successfully', 'pdf_url' => $pdf_url));
}
add_action('wp_ajax_generate_invoice_pdf', 'generate_invoice_pdf');
// Uncomment the next line to allow non-logged-in users to access this AJAX action
// add_action('wp_ajax_nopriv_generate_invoice_pdf', 'generate_invoice_pdf');

