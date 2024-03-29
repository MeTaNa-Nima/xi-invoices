<?php
require_once('add-customers-data.php');

function x_reports_page()
{
?>
    <div class="reports-btns">
        <a href="?page=xi-reports&report-type=products" class="button-secondary">گزارش فروش محصولات</a>
        <a href="?page=xi-reports&report-type=visitors" class="button-secondary">گزارش فروش ویزیتور ها</a>
        <a href="?page=xi-reports&report-type=customers" class="button-secondary">گزارش فروش مشتری ها</a>
    </div>
    <div class="reports-section">
        <?php
        if (isset($_GET['report-type'])) {
            $reports_type = $_GET['report-type'];
        } else {
            $reports_type = '';
        }
        switch ($reports_type) {

            case 'products';
                report_by_products();
                break;

            case 'visitors';
                report_by_visitors();
                break;

            case 'customers';
                report_by_customers();
                break;

            default:
                default_reports_page();
                break;
        }
        ?>
    </div>
<?php
}


// Products Report Start
function report_by_products()
{
    $reports = new Xi_Invoices_Reports();



    $report = $reports->getReportDataByProduct();
?>
    <h2>گزارش فروش محصولات</h2>
    <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
        <tr valign="top">
            <th scope="row">نام محصول</th>
            <th scope="row">تعداد فاکتور ها</th>
            <th scope="row">مشتری پایه ثابت</th>
            <th scope="row">میانگین قیمت واحد</th>
            <th scope="row">جمع مقدار فروش رفته</th>
        </tr>
        <?php
        foreach ($report as $data) {
        ?>
            <tr valign="top">
                <td scope="row" class=""><?php echo esc_html($data->product_name) ?></td>
                <td scope="row" class=""><?php echo esc_html($data->invoice_count) ?></td>
                <td scope="row" class=""><?php echo esc_html($data->top_customer) ?></td>
                <td scope="row" class=""><?php echo number_format($data->avg_net_price) ?></td>
                <td scope="row" class=""><?php echo number_format($data->total_sales) ?></td>
            </tr>
        <?php
        }
        ?>
    </table>
<?php
}
// Products Report End

// Visitors Report Start
function report_by_visitors()
{

    // Get all users with the role of 'marketer'
    $user_query = new WP_User_Query(array('role' => 'marketer'));
    $visitors = $user_query->get_results();
?>
    <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
        <tr valign="top">
            <th scope="row">نام ویزیتور</th>
            <th scope="row">بیشترین فروش به مشتری</th>
            <th scope="row">محصول پر فروش</th>
            <th scope="row">بزرگترین فروش</th>
            <th scope="row">بیشترین روش پرداخت</th>
            <th scope="row">کل تخفیفات</th>
            <th scope="row">کل فروش خالص</th>
            <th scope="row">کل فروش نهایی (با تخفیف و مالیت)</th>
        </tr>
        <?php
        if (!empty($visitors)) {
            foreach ($visitors as $visitor) {

                $visitor_id         = $visitor->ID;
                $reports            = new Xi_Invoices_Reports();
                $report             = $reports->getReportDataByVisitor($visitor_id);
                $totalDiscount      = $reports->getTotalDiscount($visitor_id, 'visitor');
                $mostPaymentMethod  = $reports->getMostPaymentMethod($visitor_id, 'visitor');
                $totalSaleFinal     = $reports->getTotalSaleFinal($visitor_id, 'visitor');
                $totalSalePure      = $reports->getTotalSalePure($visitor_id, 'visitor');
                $topSaleDetails     = $reports->getTopSaleDetails($visitor_id, 'visitor');
                $topProductSold     = $reports->getTopProductSold($visitor_id, 'visitor');


        ?>
                <tr valign="top">
                    <td scope="row" class=""><?php echo esc_html($visitor->display_name); ?></td>
                    <td scope="row" class=""><?php echo esc_html($report['topCustomerName']); ?></td>
                    <td scope="row" class=""><?php echo esc_html($topProductSold['name']); ?>: (<?php echo esc_html($topProductSold['quantity']); ?>)</td>
                    <td scope="row" class=""><a href="<?php echo esc_url($topSaleDetails['url']); ?>"><?php echo number_format($topSaleDetails['amount']); ?></a></td>
                    <td scope="row" class=""><?php echo esc_html($mostPaymentMethod); ?></td>
                    <td scope="row" class=""><?php echo number_format($totalDiscount); ?></td>
                    <td scope="row" class=""><?php echo number_format($totalSalePure); ?></td>
                    <td scope="row" class=""><?php echo number_format($totalSaleFinal); ?></td>
                </tr>
            <?php
            }
            ?>
    </table>
<?php
        } else {
            echo 'No marketers found.';
        }
    }
    // Visitors Report End


    // Customer Report Start
    function report_by_customers()
    {
        // Get all customers
        $customers = new Xi_Invoices_Customers();
        $allCustomers = $customers->get_all_customers();

?>
<table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
    <tr valign="top">
        <th scope="row">نام مشتری</th>
        <th scope="row">بزرگترین خرید</th>
        <th scope="row">محصول محبوب</th>
        <th scope="row">بیشترین روش پرداخت</th>
        <th scope="row">کل تخفیفات</th>
        <th scope="row">ویزیتور با گران ترین فاکتور</th>
        <th scope="row">ویزیتور با بیشترین تعداد محصول فروخته شده</th>
        <th scope="row">جمع کل خرید ها</th>
    </tr>
    <?php
        foreach ($allCustomers as $customer) {
            $customer_id        = $customer->customer_id;
            $reports            = new Xi_Invoices_Reports();
            $report             = $reports->getReportDataByCustomer($customer_id);
            $totalDiscount      = $reports->getTotalDiscount($customer_id, 'customer');
            $mostPaymentMethod  = $reports->getMostPaymentMethod($customer_id, 'customer');
            $totalFinalSale     = $reports->getTotalSaleFinal($customer_id, 'customer');
            $topSaleDetails     = $reports->getTopSaleDetails($customer_id, 'customer');
            $topProductSold     = $reports->getTopProductSold($customer_id, 'customer');



            // Display the data
    ?>
        <tr valign="top">
            <td scope="row" class=""><?php echo esc_html($customer->customer_name); ?></td>
            <td scope="row" class=""><a href="<?php echo esc_url($topSaleDetails['url']); ?>"><?php echo number_format($topSaleDetails['amount']); ?></a></td>
            <td scope="row" class=""><?php echo esc_html($topProductSold['name']); ?></td>
            <td scope="row" class=""><?php echo esc_html($mostPaymentMethod); ?></td>
            <td scope="row" class=""><?php echo esc_html(number_format($totalDiscount)); ?></td>
            <td scope="row" class=""><?php echo esc_html($report['topVisitorByPrice']); ?></td>
            <td scope="row" class=""><?php echo esc_html($report['topVisitorByItems']); ?></td>
            <td scope="row" class=""><?php echo esc_html(number_format($totalFinalSale)); ?></td>
        </tr>
    <?php
        }
    ?>
</table>
<?php
    }

    function default_reports_page()
    {
        echo 'لطفا یک گزینه جهت نمایش گزارش را انتخاب نمایید.';
    }
