<?php
require_once('settings.php');

function x_invoice_edit_products_page()
{
    global $errorMessage;
    global $wpdb;
    $table_name = $wpdb->prefix . 'x_invoice_products';

    if (isset($_POST['remove_row'])) {
        foreach ($_POST['remove_row'] as $row_id => $value) {
            $wpdb->delete($table_name, array('product_id' => $row_id));
            break;
        }
    }

    if (isset($_POST['add_row'])) {
        $new_data = array(
            'product_name'         => sanitize_text_field($_POST['new_product_name']),
        );
        $existing_product_name = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE product_name = '{$new_data['product_name']}'");
        if ($existing_product_name > 0) {
            setMessage('قبلا محصولی با این نام ثبت شده است.');
        } else {
            $wpdb->insert($table_name, $new_data);
        }
    }

    if (isset($_POST['submit'])) { // Assuming your save button has name="submit"
        foreach ($_POST['product_name'] as $row_id => $product_name_value) {
            // Sanitize the input values
            $sanitized_product_name        = sanitize_text_field($product_name_value);

            // Prepare the data for update
            $data_to_update = array(
                'product_name'         => $sanitized_product_name,
            );

            $where = array('product_id' => intval($row_id));

            // Update the row in the database
            $wpdb->update($table_name, $data_to_update, $where);
        }
    }


    $existing_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>
    <div>
        <h2>افزودن اطلاعات جدید:</h2>
        <form method="post" action="" id="x-invoice-products-form">
        <input type="submit" name="submit" value="ذخیره تغییرات" class="button-primary" />
            <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
                <tr valign="top">
                    <th class="firstCol" scope="row">ID</th>
                    <th scope="row">نام محصول</th>
                    <th scope="row"></th>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td class=""><input type="text" class="product_name" name="new_product_name" value="" /></td>
                    <td>
                        <input type="submit" name="add_row" value="افزودن" class="button-primary" />
                    </td>
                </tr>
                <?php
                foreach ($existing_data as $data) {
                    echo '<tr valign="top">' .
                        '<td class="firstCol">' . esc_attr($data['product_id']) . '</td>' .
                        '<td><input type="text" name="product_name[' . esc_attr($data['product_id']) . ']" value="' . esc_attr($data['product_name']) . '" /></td>' .
                        '<td>' .
                        '<input type="hidden" name="row_id" value="' . esc_attr($data['product_id']) . '">' .
                        '<input type="submit" name="remove_row[' . esc_attr($data['product_id']) . ']" value="حذف" class="button-secondary remove-row" />' .
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
