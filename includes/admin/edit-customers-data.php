<?php
require_once('settings.php');

function x_invoice_edit_customers_page()
{
    $customers = new Xi_Invoices_Customers();

    if (isset($_POST['remove_row'])) {
        foreach ($_POST['remove_row'] as $row_id => $value) {
            $customers->delete_customer($row_id);
            break;
        }
    }

    if (isset($_POST['add_row'])) {
        $customer_mobile_no = sanitize_text_field($_POST['new_customer_mobile_no']);
        $mobile_no = sanitize_text_field($_POST['new_customer_mobile_no']);
        $existing_customer = $customers->get_customer_by_mobile_no($mobile_no);

        if ($existing_customer > 0) {
            setMessage('قبلا مشتری با این شماره موبایل ثبت شده است.');
        } else {
            $new_data = array(
                'customer_name'         => sanitize_text_field($_POST['new_customer_name']),
                'customer_mobile_no'    => $customer_mobile_no,
                'customer_address'      => sanitize_text_field($_POST['new_customer_address']),
                'customer_shop_name'    => sanitize_text_field($_POST['new_customer_shop_name']),
            );
            $customers->add_customer($new_data);
        }
    }

    if (isset($_POST['submit'])) {
        foreach ($_POST['customer_name'] as $row_id => $customer_name_value) {
            $sanitized_customer_name        = sanitize_text_field($customer_name_value);
            $sanitized_customer_mobile_no = isset($_POST['customer_mobile_no'][$row_id]) ? sanitize_text_field($_POST['customer_mobile_no'][$row_id]) : '';
            $sanitized_customer_address     = isset($_POST['customer_address'][$row_id]) ? sanitize_text_field($_POST['customer_address'][$row_id]) : '';
            $sanitized_customer_shop_name   = isset($_POST['customer_shop_name'][$row_id]) ? sanitize_text_field($_POST['customer_shop_name'][$row_id]) : '';
            $data_to_update = array(
                'customer_name'         => $sanitized_customer_name,
                'customer_mobile_no'    => $sanitized_customer_mobile_no,
                'customer_address'      => $sanitized_customer_address,
                'customer_shop_name'    => $sanitized_customer_shop_name,
            );
            $customers->update_customer($row_id, $data_to_update);
        }
    }

    $all_customers = $customers->get_all_customers();
?>
    <div>
        <h2>افزودن اطلاعات جدید:</h2>
        <form method="post" action="" id="x-invoice-customers-form">
        <input type="submit" name="submit" value="ذخیره تغییرات" class="button-primary" />
            <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
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
                    <td class="column-column25"><input type="text" class="customer_name" name="new_customer_name" value="" /></td>
                    <td class="column-column25"><input type="text" class="customer_mobile_no" name="new_customer_mobile_no" value="" /></td>
                    <td class="column-column25"><input type="text" class="customer_shop_name" name="new_customer_shop_name" value="" /></td>
                    <td class="column-column50"><input type="text" class="customer_address" name="new_customer_address" value="" /></td>
                    <td>
                        <input type="submit" name="add_row" value="افزودن" class="button-primary" />
                    </td>
                </tr>
                <?php
                foreach ($all_customers as $data) {
                    echo '<tr valign="top">' .
                        '<td class="firstCol">' . esc_attr($data->customer_id) . '</td>' .
                        '<td><input type="text" name="customer_name[' . esc_attr($data->customer_id) . ']" value="' . esc_attr($data->customer_name) . '" /></td>' .
                        '<td><input type="text" name="customer_mobile_no[' . esc_attr($data->customer_id) . ']" value="' . esc_attr($data->customer_mobile_no) . '" /></td>' .
                        '<td><input type="text" name="customer_shop_name[' . esc_attr($data->customer_id) . ']" value="' . esc_attr($data->customer_shop_name) . '" /></td>' .
                        '<td><input type="text" name="customer_address[' . esc_attr($data->customer_id) . ']" value="' . esc_attr($data->customer_address) . '" /></td>' .
                        '<td>' .
                        '<input type="hidden" name="row_id" value="' . esc_attr($data->customer_id) . '">' .
                        '<input type="submit" name="remove_row[' . esc_attr($data->customer_id) . ']" value="حذف" class="button-secondary remove-row" />' .
                        '</td>' .
                        '</tr>';
                }
                ?>
                <input type="hidden" name="remove_row_id" id="remove_row_id" value="" />
            </table>
            <input type="submit" name="submit" value="ذخیره تغییرات" class="button-primary" />
        </form>
    </div>
    <br>
    <?php showMessage(); ?>
<?php
}
