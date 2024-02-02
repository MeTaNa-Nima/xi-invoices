<?php
require_once('add-customers-data.php');

function handle_logo_upload($file)
{
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        return $movefile['url'];
    } else {
        // Handle errors
        return false;
    }
}

function settings()
{

    if (isset($_POST['save'])) {
        update_option('taxAmount',          sanitize_text_field($_POST['applied_tax']));
        update_option('dateFormat',         sanitize_text_field($_POST['date_format']));
        update_option('regPageSlug',        sanitize_text_field($_POST['invoice_registration_page_slug']));

        // Handle the logo settings
        if (isset($_POST['invoice_logo'])) {
            switch ($_POST['invoice_logo']) {
                case 'default_site_logo':
                    update_option('invoiceLogo', 'default_site_logo');
                    break;
                case 'custom_invoice_logo':
                    if (!empty($_POST['custom_invoice_logo_url'])) {
                        update_option('invoiceLogo', sanitize_text_field($_POST['custom_invoice_logo_url']));
                    } else {
                        update_option('invoiceLogo', ''); // Empty string for no logo
                    }
                    break;
                case 'no_logo':
                    update_option('invoiceLogo', ''); // Empty string for no logo
                    break;
            }
        }


        // Add an admin notice on successful save
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
        });
    }

    wp_enqueue_media();

    // Retrieve current settings
    $taxAmount      = get_option('taxAmount', 'applied_tax');
    $dateFormat     = get_option('dateFormat', 'date_format');
    $invoiceLogo    = get_option('invoiceLogo', 'default_site_logo');
    $regPageSlug    = get_option('regPageSlug', 'invoice_registration_page_slug');

?>
    <form action="" method="post" enctype="multipart/form-data">
        <h2>تنظیمات ثبت فاکتور ویزیتور</h2>
        <table class="form-table">
            <tr>
                <th><label for="applied_tax">مقدار مالیت</label></th>
                <td><input type="number" name="applied_tax" value="<?php echo $taxAmount ?>"></td>
            </tr>
            <tr>
                <th><label for="date_format">فرمت تاریخ</label></th>
                <td>
                    <input type="radio" id="date_format_jalali" class="date_format date_format_jalali" name="date_format" value="jalali" <?php echo ($dateFormat == 'jalali') ? 'checked' : ''; ?>>
                    <label for="date_format_jalali">شمسی</label>
                    <input type="radio" id="date_format_georgian" class="date_format date_format_georgian" name="date_format" value="georgian" <?php echo ($dateFormat == 'georgian') ? 'checked' : ''; ?>>
                    <label for="date_format_georgian">میلادی</label>
                </td>
            </tr>
            <tr>
                <th><label for="invoice_logo">تنظیمات سوم</label></th>
                <td>
                    <input type="radio" id="invoice_logo_no" name="invoice_logo" value="no_logo" <?php checked($invoiceLogo, ''); ?>>
                    <label for="invoice_logo_no">بدون لوگو</label>

                    <input type="radio" id="invoice_logo_default" name="invoice_logo" value="default_site_logo" <?php checked($invoiceLogo, 'default_site_logo'); ?>>
                    <label for="invoice_logo_default">لوگو سایت</label>

                    <input type="radio" id="invoice_logo_custom" name="invoice_logo" value="custom_invoice_logo" <?php checked($invoiceLogo, 'custom_invoice_logo'); ?>>
                    <label for="invoice_logo_custom">انتخاب لوگو</label>
                    
                    <div id="invoice_logo_custom_selector" style="<?php echo ($invoiceLogo !== 'default_site_logo' && $invoiceLogo !== '') ? '' : 'display: none;'; ?>">
                        <input type="hidden" name="custom_invoice_logo_url" id="custom_invoice_logo_url" value="<?php echo esc_attr($invoiceLogo); ?>">
                        <button type="button" class="button" id="upload_invoice_logo_button">Select Image</button>
                        <span id="invoice_logo_preview"><?php echo $invoiceLogo !== 'default_site_logo' && $invoiceLogo !== '' ? '<img src="' . esc_url($invoiceLogo) . '" style="max-width: 100px; height: auto;">' : ''; ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label for="invoice_registration_page_slug">لینک برگه ثبت فاکتور</label></th>
                <td><input type="text" name="invoice_registration_page_slug" value="<?php echo $regPageSlug; ?>"></td>
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
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#upload_invoice_logo_button').click(function(e) {
                e.preventDefault();

                var custom_uploader = wp.media({
                        title: 'Select Invoice Logo',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    }).on('select', function() {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        $('#custom_invoice_logo_url').val(attachment.url);
                        $('#invoice_logo_preview').html('<img src="' + attachment.url + '" style="max-width: 100px; height: auto;">');
                    })
                    .open();
            });

            // Toggle the custom logo selector based on the selected option
            $('input[name="invoice_logo"]').change(function() {
                if ($('#invoice_logo_custom').is(':checked')) {
                    $('#invoice_logo_custom_selector').show();
                } else {
                    $('#invoice_logo_custom_selector').hide();
                }
            });
        });
    </script>
<?php
}
