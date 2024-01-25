<?php
require_once('settings.php');

function x_invoice_edit_customers_page()
{
    global $errorMessage;
    global $wpdb;
    $table_name = $wpdb->prefix . 'x_invoice_customers';

    if (isset($_POST['remove_row'])) {
        foreach ($_POST['remove_row'] as $row_id => $value) {
            $wpdb->delete($table_name, array('customer_id' => $row_id));
            break;
        }
    }

    if (isset($_POST['add_row'])) {
        $new_data = array(
            'customer_name'         => sanitize_text_field($_POST['new_customer_name']),
            'customer_national_id'  => sanitize_text_field($_POST['new_customer_national_id']),
            'customer_address'      => sanitize_text_field($_POST['new_customer_address']),
            'customer_shop_name'    => sanitize_text_field($_POST['new_customer_shop_name']),
        );
        $existing_customer_national_id = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE customer_national_id = '{$new_data['customer_national_id']}'");
        if ($existing_customer_national_id > 0) {
            setMessage('قبل مشتری با این کد ملی ثبت شده است.');
        } else {
            $wpdb->insert($table_name, $new_data);
        }
    }

    if (isset($_POST['submit'])) { // Assuming your save button has name="submit"
        foreach ($_POST['customer_name'] as $row_id => $customer_name_value) {
            // Sanitize the input values
            $sanitized_customer_name        = sanitize_text_field($customer_name_value);
            $sanitized_customer_national_id = isset($_POST['customer_national_id'][$row_id]) ? sanitize_text_field($_POST['customer_national_id'][$row_id]) : '';
            $sanitized_customer_address     = isset($_POST['customer_address'][$row_id]) ? sanitize_text_field($_POST['customer_address'][$row_id]) : '';
            $sanitized_customer_shop_name   = isset($_POST['customer_shop_name'][$row_id]) ? sanitize_text_field($_POST['customer_shop_name'][$row_id]) : '';

            // Prepare the data for update
            $data_to_update = array(
                'customer_name'         => $sanitized_customer_name,
                'customer_national_id'  => $sanitized_customer_national_id,
                'customer_address'      => $sanitized_customer_address,
                'customer_shop_name'    => $sanitized_customer_shop_name,
            );

            $where = array('customer_id' => intval($row_id));

            // Update the row in the database
            $wpdb->update($table_name, $data_to_update, $where);
        }
    }


    $existing_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>
    <div>
        <h2>افزودن اطلاعات جدید:</h2>
        <form method="post" action="" id="x-invoice-customers-form">
        <input type="submit" name="submit" value="ذخیره تغییرات" class="button-primary" />
            <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
                <tr valign="top">
                    <th class="firstCol" scope="row">ID</th>
                    <th scope="row">نام مشتری</th>
                    <th scope="row">کد ملی مشتری</th>
                    <th scope="row">نام فروشگاه</th>
                    <th scope="row">آدرس مشتری</th>
                    <th scope="row"></th>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td class="column-column25"><input type="text" class="customer_name" name="new_customer_name" value="" /></td>
                    <td class="column-column25"><input type="text" class="customer_national_id" name="new_customer_national_id" value="" /></td>
                    <td class="column-column25"><input type="text" class="customer_shop_name" name="new_customer_shop_name" value="" /></td>
                    <td class="column-column50"><input type="text" class="customer_address" name="new_customer_address" value="" /></td>
                    <td>
                        <input type="submit" name="add_row" value="افزودن" class="button-primary" />
                    </td>
                </tr>
                <?php
                foreach ($existing_data as $data) {
                    echo '<tr valign="top">' .
                        '<td class="firstCol">' . esc_attr($data['customer_id']) . '</td>' .
                        '<td><input type="text" name="customer_name[' . esc_attr($data['customer_id']) . ']" value="' . esc_attr($data['customer_name']) . '" /></td>' .
                        '<td><input type="text" name="customer_national_id[' . esc_attr($data['customer_id']) . ']" value="' . esc_attr($data['customer_national_id']) . '" /></td>' .
                        '<td><input type="text" name="customer_shop_name[' . esc_attr($data['customer_id']) . ']" value="' . esc_attr($data['customer_shop_name']) . '" /></td>' .
                        '<td><input type="text" name="customer_address[' . esc_attr($data['customer_id']) . ']" value="' . esc_attr($data['customer_address']) . '" /></td>' .
                        '<td>' .
                        '<input type="hidden" name="row_id" value="' . esc_attr($data['customer_id']) . '">' .
                        '<input type="submit" name="remove_row[' . esc_attr($data['customer_id']) . ']" value="حذف" class="button-secondary remove-row" />' .
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
    <script>

    </script>
<?php
}
