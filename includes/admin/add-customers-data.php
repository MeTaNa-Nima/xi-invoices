<?php
require_once('settings.php');


function x_invoice_add_customers_page() {
    $customers = new Xi_Invoices_Customers();
    if (isset($_POST['add_row'])) {
        $customer_mobile_no = sanitize_text_field($_POST['new_customer_mobile_no']);
        
        // Check if customer with the same national ID already exists
        $mobile_no = sanitize_text_field($_POST['new_customer_mobile_no']);
        $existing_customer = $customers->get_customer_by_mobile_no($mobile_no);

        if ($existing_customer > 0) {
            setMessage('قبل مشتری با این کد ملی ثبت شده است.');
        } else {
            // Insert new customer data
            $new_data = array(
                'customer_name'         => sanitize_text_field($_POST['new_customer_name']),
                'customer_mobile_no'    => $customer_mobile_no,
                'customer_address'      => sanitize_text_field($_POST['new_customer_address']),
                'customer_shop_name'    => sanitize_text_field($_POST['new_customer_shop_name']),
            );
            $customers->add_customer($new_data);
        }
    }
    $all_customers = $customers->get_all_customers();
?>
    <div>
        
        <h2>افزودن اطلاعات جدید:</h2>
        <form method="post" action="" id="x-invoice-customers-form">
            <table class="form-table striped table-view-list widefat wp-list-table" cellspacing="0" id="x-invoice-customers-table">
                <tr valign="top">
                    <th class="firstCol" scope="row">ID</th>
                    <th scope="row">نام مشتری</th>
                    <th scope="row">شماره موبایل</th>
                    <th scope="row">نام فروشگاه</th>
                    <th scope="row">آدرس مشتری</th>
                    <th scope="row"></th>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td class="column"><input type="text" class="customer_name" name="new_customer_name" value="" placeholder="نام مشتری"/></td>
                    <td class="column"><input type="text" class="customer_mobile_no" name="new_customer_mobile_no" value="" placeholder="شماره موبایل"/></td>
                    <td class="column"><input type="text" class="customer_shop_name" name="new_customer_shop_name" value="" placeholder="نام فروشگاه"/></td>
                    <td class="column"><input type="text" class="customer_address" name="new_customer_address" value="" placeholder="آدرس مشتری"/></td>
                    <td>
                        <input type="submit" name="add_row" value="افزودن" class="button-primary" />
                    </td>
                </tr>
                <?php
                foreach ($all_customers as $data) {
                    echo '<tr valign="top">' .
                        '<td class="firstCol">' . esc_attr($data->customer_id) . '</td>' .
                        '<td class="column">' . esc_attr($data->customer_name) . '</td>' .
                        '<td class="column">' . esc_attr($data->customer_mobile_no) . '</td>' .
                        '<td class="column">' . esc_attr($data->customer_shop_name) . '</td>' .
                        '<td class="column">' . esc_attr($data->customer_address) . '</td>' .
                        '<td></td>' .
                        '</tr>';
                }
                ?>
            </table>
        </form>
    </div>
    <br>
    <?php showMessage(); ?>
    <script>
    </script>
<?php
}
