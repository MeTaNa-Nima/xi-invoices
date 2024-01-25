<?php
require_once('add-customers-data.php');



function settings()
{

    if (isset($_POST['save'])) {
        // Sanitize and save the settings
        update_option('taxAmount',          sanitize_text_field($_POST['applied-tax']));
        // update_option('colHeader1',         sanitize_text_field($_POST['setting1']));
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
    // $colHeader1         = get_option('colHeader1',      'نام آزمایشگاه');
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
                <th><label for="options2">تنظیمات دوم</label></th>
                <td><input disabled type="text" name="options2" value=""></td>
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
