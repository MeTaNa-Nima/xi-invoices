<?php
require_once('add-customers-data.php');



function settings()
{

    if (isset($_POST['save'])) {
        // Sanitize and save the settings
        update_option('taxAmount',          sanitize_text_field($_POST['applied-tax']));
        update_option('dateFormat',         sanitize_text_field($_POST['date_format']));
        // update_option('colHeader2',         sanitize_text_field($_POST['setting2']));
        // update_option('colHeader3',         sanitize_text_field($_POST['setting3']));
        // update_option('colHeader4',         sanitize_text_field($_POST['setting4']));
        // update_option('colHeader5',         sanitize_text_field($_POST['setting5']));
        // update_option('colHeader6',         sanitize_text_field($_POST['setting6']));

        // Add an admin notice on successful save
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
        });
    }

    // Retrieve current settings
    $taxAmount        = get_option('taxAmount',     'applied-tax');
    $dateFormat       = get_option('dateFormat',    'date_format');
    // $colHeader2         = get_option('colHeader2',      'شماره پاکت');
    // $colHeader3         = get_option('colHeader3',      'عیار');
    // $colHeader4         = get_option('colHeader4',      'شماره آزمایشگاه');
    // $colHeader5         = get_option('colHeader5',      'تلفن گویا');
    // $colHeader6         = get_option('colHeader6',      'تلفن گویا');

?>
    <form action="" method="post">
        <h2>تنظیمات ثبت فاکتور ویزیتور</h2>
        <table class="form-table">
            <tr>
                <th><label for="applied-tax">مقدار مالیت</label></th>
                <td><input type="number" name="applied-tax" value="<?php echo $taxAmount ?>"></td>
            </tr>
            <tr>
                <th><label for="date_format">فرمت تاریخ</label></th>
                <td>
                    <input type="radio" id="date_format_jalali" class="date_format date_format_jalali" name="date_format" value="jalali" <?php echo ($dateFormat == 'jalali') ? 'checked' : ''; ?>>
                    <label for="date_format_jalali">شمسی</label>
                    <input type="radio" id="date_format_georgian" class="date_format date_format_georgian" name="date_format" value="georgian"<?php echo ($dateFormat == 'georgian') ? 'checked' : ''; ?>>
                    <label for="date_format_georgian">میلادی</label>
                </td>
            </tr>
            <tr>
                <th><label for="options3">تنظیمات سوم</label></th>
                <td><input disabled type="text" name="options3" value=""></td>
            </tr>
            <tr>
                <th><label for="options4">تنظیمات چهارم</label></th>
                <td><input disabled type="text" name="options4" value=""></td>
            </tr>
            <tr>
                <th><label for="options5">تنظیمات پنجم</label></th>
                <td><input disabled type="text" name="options5" value=""></td>
            </tr>
            <tr>
                <th><label for="options6">تنظیمات ششم</label></th>
                <td><input disabled type="text" name="options6" value=""></td>
            </tr>
        </table>
        <input type="submit" value="ذخیره تنظیمات" name="save" class="button-primary">

    </form>


<?php


}
