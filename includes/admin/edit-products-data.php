<?php
require_once('settings.php');

function x_invoice_edit_products_page()
{
    $products = new Xi_Invoices_Products();

    if (isset($_POST['remove_row'])) {
        foreach ($_POST['remove_row'] as $row_id => $value) {
            $products->delete_product($row_id);
            break;
        }
    }

    if (isset($_POST['add_row'])) {
        $product_name = sanitize_text_field($_POST['new_product_name']);
        $existing_product_name = $products->get_product_by_name($product_name);
        
        if ($existing_product_name > 0) {
            setMessage('قبلا محصولی با این نام ثبت شده است.');
        } else {
            $new_data = array(
                'product_name'         => sanitize_text_field($_POST['new_product_name']),
            );
            $products->add_product($new_data);
        }
    }

    if (isset($_POST['submit'])) {
        foreach ($_POST['product_name'] as $row_id => $product_name_value) {
            $sanitized_product_name        = sanitize_text_field($product_name_value);
            $data_to_update = array(
                'product_name'         => $sanitized_product_name,
            );
            $products->update_product($row_id, $data_to_update);
        }
    }


    $all_products = $products->get_all_products();
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
                foreach ($all_products as $data) {
                    echo '<tr valign="top">' .
                        '<td class="firstCol">' . esc_attr($data->product_id) . '</td>' .
                        '<td><input type="text" name="product_name[' . esc_attr($data->product_id) . ']" value="' . esc_attr($data->product_name) . '" /></td>' .
                        '<td>' .
                        '<input type="hidden" name="row_id" value="' . esc_attr($data->product_id) . '">' .
                        '<input type="submit" name="remove_row[' . esc_attr($data->product_id) . ']" value="حذف" class="button-secondary remove-row" />' .
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
