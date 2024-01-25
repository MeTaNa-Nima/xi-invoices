<?php
require_once('settings.php');

function x_invoice_add_customers_page()
{
    global $errorMessage;
    global $wpdb;
    $table_name = $wpdb->prefix . 'x_invoice_customers';
    if (isset($_POST['remove_row'])) {
        foreach ($_POST['remove_row'] as $row_id => $value) {
            $wpdb->delete($table_name, array('id' => $row_id));
            break;
        }
    }

    if (isset($_POST['add_row'])) {
        $new_data = array(
            'customer_name'         => sanitize_text_field($_POST['new_customer_name']),
            'customer_national_id'  => sanitize_text_field($_POST['new_customer_national_id']),
            'customer_address'      => sanitize_text_field($_POST['new_customer_address']),
        );
        $existing_customer_national_id = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE customer_national_id = '{$new_data['customer_national_id']}'");
        if ($existing_customer_national_id > 0) {
            setMessage('قبل مشتری با این کد ملی ثبت شده است.');
        } else {
            $wpdb->insert($table_name, $new_data);
        }
    }
    $existing_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>
    <div>
        <h2>افزودن اطلاعات جدید:</h2>
        <form method="post" action="" id="x-invoice-customers-form">
            <table class="form-table striped table-view-list widefat wp-list-table" cellspacing="0" id="x-invoice-customers-table">
                <tr valign="top">
                    <th class="firstCol" scope="row">ID</th>
                    <th scope="row">نام مشتری</th>
                    <th scope="row">کد ملی مشتری</th>
                    <th scope="row">آدرس مشتری</th>
                    <th scope="row"></th>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td class="column-column25"><input type="text" class="customer_name" name="new_customer_name" value="" /></td>
                    <td class="column-column25"><input type="text" class="customer_national_id" name="new_customer_national_id" value="" /></td>
                    <td class="column-column50"><input type="text" class="customer_address" name="new_customer_address" value="" /></td>
                    <td>
                        <input type="submit" name="add_row" value="افزودن" class="button-primary" />
                    </td>
                </tr>
                <?php
                foreach ($existing_data as $data) {
                    echo '<tr valign="top">' .
                        '<td class="firstCol">' . esc_attr($data['customer_id']) . '</td>' .
                        '<td class="column-column25">' . esc_attr($data['customer_name']) . '</td>' .
                        '<td class="column-column25">' . esc_attr($data['customer_national_id']) . '</td>' .
                        '<td class="column-column50">' . esc_attr($data['customer_address']) . '</td>' .
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
