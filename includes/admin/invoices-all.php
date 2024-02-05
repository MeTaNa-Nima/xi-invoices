<?php
require_once('settings.php');

function xi_invoice_show_all()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();

    $current_user   = wp_get_current_user();
    $subHeader = '';


    // Determine the current page and set the number of items per page
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    // Fetch paginated invoices
    $invoices = $all_invoices->get_paginated_invoices($current_page, $per_page);
    $total_invoices = $all_invoices->get_total_invoices_count();
    $total_pages = ceil($total_invoices / $per_page);



    // Check if current user is an administrator
    if (in_array('administrator', $current_user->roles)) {
        // Admins can see all invoices
        $invoices = $all_invoices->get_paginated_invoices($current_page);
        $total_invoices = $all_invoices->get_total_invoices_count();
        $subHeader = 'همه ویزیتور ها';
    } else {
        // Other users see only their invoices
        $invoices = $all_invoices->get_invoices_by_user_id($current_user->ID, $current_page, $per_page);
        $total_invoices = $all_invoices->get_total_invoices_count_by_user($current_user->ID);
        $subHeader = 'شما';
    }


?>
    <h2>فاکتور های ثبت شده توسط <?php echo esc_html($subHeader); ?>:</h2>
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
                $datetime = new DateTime($invoice->date_submit_gmt);
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
                    <td><?php echo esc_html($datetime->format('Y/n/j')); ?></td>
                    <td>
                        <?php
                        if (in_array('administrator', wp_get_current_user()->roles)) {
                        ?>
                            <!-- <a href="<?php echo admin_url('admin.php?page=xi-invoices&edit_mode=1&invoice_id=' . esc_attr($invoice->invoice_id)); ?>">ویرایش فاکتور</a> /  -->
                            ویرایش فاکتور
                        <?php
                        }
                        ?>
                        <a href="<?php echo admin_url('admin.php?page=xi-invoices&invoice_id=' . esc_attr($invoice->invoice_id)) . '&print_view'; ?>">نسخه نهایی</a>
                    </td>
                </tr>
        <?php

            }
        } else {
            echo 'No invoice found.';
        }
        ?>
    </table>

    <?php
    // Pagination links
    $page_links = paginate_links(array(
        'base' => add_query_arg('paged', '%#%'),
        'format' => '?paged=%#%',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => $total_pages,
        'current' => $current_page
    ));

    // Display pagination if more than one page is needed
    if ($page_links) {
        echo '<div class="tablenav"><div class="tablenav-pages xi_invoices_all">' . $page_links . '</div></div>';
    }
    ?>
    <?php showMessage(); ?>
    </div>
<?php
}
?>