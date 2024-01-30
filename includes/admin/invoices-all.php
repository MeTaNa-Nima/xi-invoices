<?php
require_once('settings.php');

function xi_invoice_show_all()
{
    $all_invoices   = new Xi_Invoices_Invoice();
    $customers      = new Xi_Invoices_Customers();
    $invoices       = $all_invoices->get_all_invoices();
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
                        <td><a href="<?php echo admin_url('admin.php?page=xi-invoices&invoice_id=' . esc_attr($invoice->invoice_id)); ?>">مشاهده فاکتور</a></td>
                    </tr>
            <?php
                }
            } else {
                echo 'No invoice found.';
            }
            ?>
        </table>
    <?php
    
    ?>
    <?php showMessage(); ?>
    </div>
<?php
}
?>