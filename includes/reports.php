<?php
require_once('add-customers-data.php');

function x_reports_page()
{
?>
    <div class="reports-btns">
        <a href="?page=xi-reports&report-type=products" class="button-secondary">گزارش فروش محصولات</a>
        <a href="?page=xi-reports&report-type=visitors" class="button-secondary">گزارش فروش ویزیتور ها</a>
        <a href="?page=xi-reports&report-type=customers" class="button-secondary">گزارش فروش مشتری ها</a>
        <!-- <a href="?page=xi-reports&report-type=city" class="button-secondary">گزارش فروش شهر</a> -->
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

                // case 'city';
                //     report_by_city();
                //     break;

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
    global $wpdb;
    $products_table = $wpdb->prefix . 'x_invoice_products';
    $data_lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    $operation_data_table = $wpdb->prefix . 'x_invoice_operation_data';
    $sql = "SELECT p.product_name, 
                   COUNT(*) as invoice_count, 
                   SUM(dl.product_total_price) as total_sales, 
                   AVG(dl.product_net_price) as avg_net_price, 
                   (SELECT c.customer_name FROM {$wpdb->prefix}x_invoice_customers c WHERE c.customer_id = (SELECT customer_id FROM $data_lookup_table WHERE product_id = p.product_id GROUP BY customer_id ORDER BY COUNT(*) DESC LIMIT 1)) as top_customer
            FROM $products_table p
            LEFT JOIN $data_lookup_table dl ON p.product_id = dl.product_id
            LEFT JOIN $operation_data_table op ON dl.order_id = op.invoice_id
            GROUP BY p.product_id";

    $products_data = $wpdb->get_results($sql, ARRAY_A);
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
        foreach ($products_data as $data) {
        ?>
            <tr valign="top">
                <td scope="row" class=""><?php echo esc_html($data['product_name']) ?></td>
                <td scope="row" class=""><?php echo esc_html($data['invoice_count']) ?></td>
                <td scope="row" class=""><?php echo esc_html($data['top_customer']) ?></td>
                <td scope="row" class=""><?php echo number_format($data['avg_net_price']) ?></td>
                <td scope="row" class=""><?php echo number_format($data['total_sales']) ?></td>
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
    global $wpdb;
    $operation_table = $wpdb->prefix . 'x_invoice_operation_data';
    $lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
    $customers_table = $wpdb->prefix . 'x_invoice_customers';
    $products_table = $wpdb->prefix . 'x_invoice_products';

    // Get all users with the role of 'marketer'
    $user_query = new WP_User_Query(array('role' => 'marketer'));
    $visitors = $user_query->get_results();
?>
    <table class="form-table striped table-view-list widefat wp-list-table" id="form-table striped widefat fixed">
        <tr valign="top">
            <th scope="row">نام ویزیتور</th>
            <th scope="row">بیشترین فروش به مشتری</th>
            <th scope="row">محصول پر فروش</th>
            <!-- <th scope="row">شهر پر فروش</th> -->
            <th scope="row">بزرگترین فروش</th>
            <th scope="row">بیشترین روش پرداخت</th>
            <th scope="row">کل تخفیفات</th>
            <th scope="row">کل فروش خالص</th>
            <th scope="row">کل فروش نهایی (با تخفیف و مالیت)</th>
        </tr>
        <?php
        if (!empty($visitors)) {
            foreach ($visitors as $visitor) {

                $visitor_id = $visitor->ID;

                // Get top customer for this visitor
                $top_customer_query = $wpdb->prepare(
                    "SELECT c.customer_name, COUNT(*) as total_orders
                    FROM $operation_table op
                    JOIN $customers_table c ON op.customer_id = c.customer_id
                    WHERE op.visitor_id = %d
                    GROUP BY op.customer_id
                    ORDER BY total_orders DESC
                    LIMIT 1",
                    $visitor_id
                );
                $top_customer = $wpdb->get_row($top_customer_query);
                $topCustomerName = $top_customer ? $top_customer->customer_name : 'Unknown';

                // Get top product and quantity for this visitor
                $top_product_query = $wpdb->prepare(
                    "SELECT p.product_name, SUM(dl.product_qty) as total_qty
                    FROM $lookup_table dl
                    JOIN $products_table p ON dl.product_id = p.product_id
                    JOIN $operation_table op ON dl.order_id = op.invoice_id
                    WHERE op.visitor_id = %d
                    GROUP BY dl.product_id
                    ORDER BY total_qty DESC
                    LIMIT 1",
                    $visitor_id
                );
                $top_product = $wpdb->get_row($top_product_query);
                $topProductName = $top_product ? $top_product->product_name : 'Unknown';
                $topProductQty = $top_product ? $top_product->total_qty : 0;

                // Get top city for this visitor
                // Get top city for this visitor
                $top_city_query = $wpdb->prepare(
                    "SELECT c.customer_city, COUNT(*) as total_orders
                    FROM $operation_table op
                    JOIN $customers_table c ON op.customer_id = c.customer_id
                    WHERE op.visitor_id = %d
                    GROUP BY c.customer_city
                    ORDER BY total_orders DESC
                    LIMIT 1",
                    $visitor_id
                );
                $top_city = $wpdb->get_row($top_city_query);
                $topCity = $top_city ? $top_city->customer_city : 'Unknown';


                // Get top sale (highest order_total_final) for this visitor
                $top_sale_query = $wpdb->prepare(
                    "SELECT MAX(op.order_total_final) as top_sale, op.invoice_id
                    FROM $operation_table op
                    WHERE op.visitor_id = %d
                    GROUP BY op.visitor_id",
                    $visitor_id
                );
                $top_sale = $wpdb->get_row($top_sale_query);
                $topSale = $top_sale ? $top_sale->top_sale : 0;
                $linkToTopSale = $top_sale ? admin_url('admin.php?page=xi-orders-list&invoice_id=' . $top_sale->invoice_id) : '#';

                // Get mostly used payment method for this visitor
                $top_payment_method_query = $wpdb->prepare(
                    "SELECT op.payment_method, COUNT(*) as total
                    FROM $operation_table op
                    WHERE op.visitor_id = %d
                    GROUP BY op.payment_method
                    ORDER BY total DESC
                    LIMIT 1",
                    $visitor_id
                );
                $top_payment_method = $wpdb->get_row($top_payment_method_query);
                $topPaymentMethod = $top_payment_method ? $top_payment_method->payment_method : 'Unknown';

                // Get total discount given by this visitor
                $total_discount_query = $wpdb->prepare(
                    "SELECT SUM(op.discount_total_amount) as total_discount
                    FROM $operation_table op
                    WHERE op.visitor_id = %d",
                    $visitor_id
                );
                $totalDiscount = $wpdb->get_var($total_discount_query);

                // Get total pure sales for this visitor
                $total_pure_sales_query = $wpdb->prepare(
                    "SELECT SUM(op.order_total_pure) as total_pure_sales
                    FROM $operation_table op
                    WHERE op.visitor_id = %d",
                    $visitor_id
                );
                $totalPureSales = $wpdb->get_var($total_pure_sales_query);

                // Get total sales (order_total_final) for this visitor
                $total_sales_query = $wpdb->prepare(
                    "SELECT SUM(op.order_total_final) as total_sales
                    FROM $operation_table op
                    WHERE op.visitor_id = %d",
                    $visitor_id
                );
                $totalSales = $wpdb->get_var($total_sales_query);
        ?>
                <tr valign="top">
                    <td scope="row" class=""><?php echo esc_html($visitor->display_name); ?></td>
                    <td scope="row" class=""><?php echo esc_html($topCustomerName); ?></td>
                    <td scope="row" class=""><?php echo esc_html($topProductName); ?>: (<?php echo esc_html($topProductQty); ?>)</td>
                    <!-- <td scope="row" class=""><?php echo esc_html($topCity); ?></td> -->
                    <td scope="row" class=""><a href="<?php echo esc_url($linkToTopSale); ?>"><?php echo number_format($topSale); ?></a></td>
                    <td scope="row" class=""><?php echo esc_html($topPaymentMethod); ?></td>
                    <td scope="row" class=""><?php echo number_format($totalDiscount); ?></td>
                    <td scope="row" class=""><?php echo number_format($totalPureSales); ?></td>
                    <td scope="row" class=""><?php echo number_format($totalSales); ?></td>
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
        global $wpdb;
        $operation_table = $wpdb->prefix . 'x_invoice_operation_data';
        $lookup_table = $wpdb->prefix . 'x_invoice_data_lookup';
        $customers_table = $wpdb->prefix . 'x_invoice_customers';
        $products_table = $wpdb->prefix . 'x_invoice_products';

        // Get all customers
        $customers = $wpdb->get_results("SELECT * FROM $customers_table", ARRAY_A);

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
    </tr>
    <?php
        foreach ($customers as $customer) {
            $customer_id = $customer['customer_id'];

            // Check if the customer has any registered invoices
            $invoice_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $operation_table WHERE customer_id = %d",
                $customer_id
            ));

            // If no invoices are found for the customer, skip to the next customer
            if ($invoice_count == 0) {
                continue;
            }

            // Get biggest buy by price and link to invoice
            $biggest_buy_query = $wpdb->prepare(
                "SELECT op.invoice_id, MAX(op.order_total_final) as biggest_buy
                FROM $operation_table op
                WHERE op.customer_id = %d
                GROUP BY op.customer_id",
                $customer_id
            );
            $biggest_buy = $wpdb->get_row($biggest_buy_query);
            $biggestBuyPrice = $biggest_buy ? $biggest_buy->biggest_buy : 0;
            $linkToInvoice = $biggest_buy ? admin_url('admin.php?page=xi-orders-list&invoice_id=' . $biggest_buy->invoice_id) : '#';

            // Get most product bought
            $top_product_query = $wpdb->prepare(
                "SELECT p.product_name, SUM(dl.product_qty) as total_qty
                FROM $lookup_table dl
                JOIN $products_table p ON dl.product_id = p.product_id
                JOIN $operation_table op ON dl.order_id = op.invoice_id
                WHERE op.customer_id = %d
                GROUP BY dl.product_id
                ORDER BY total_qty DESC
                LIMIT 1",
                $customer_id
            );
            $top_product = $wpdb->get_row($top_product_query);
            $mostProductBought = $top_product ? $top_product->product_name : 'Unknown';
            $topProductQty = $top_product ? $top_product->total_qty : 0;

            // Get most used payment method
            $most_payment_method_query = $wpdb->prepare(
                "SELECT op.payment_method, COUNT(*) as total
                FROM $operation_table op
                WHERE op.customer_id = %d
                GROUP BY op.payment_method
                ORDER BY total DESC
                LIMIT 1",
                $customer_id
            );
            $most_payment_method = $wpdb->get_row($most_payment_method_query);
            $mostPaymentMethod = $most_payment_method ? $most_payment_method->payment_method : 'Unknown';

            // Get total discount given to this customer
            $total_discount_query = $wpdb->prepare(
                "SELECT SUM(op.discount_total_amount) as total_discount
                FROM $operation_table op
                WHERE op.customer_id = %d",
                $customer_id
            );
            $totalDiscount = $wpdb->get_var($total_discount_query);

            // Get which visitor sold him the most by price
            $top_visitor_by_price_query = $wpdb->prepare(
                "SELECT op.visitor_id, SUM(op.order_total_final) as total_sales
                FROM $operation_table op
                WHERE op.customer_id = %d
                GROUP BY op.visitor_id
                ORDER BY total_sales DESC
                LIMIT 1",
                $customer_id
            );
            $top_visitor_by_price = $wpdb->get_row($top_visitor_by_price_query);
            $topVisitorByPrice = $top_visitor_by_price ? get_user_by('id', $top_visitor_by_price->visitor_id)->display_name . ' - مبلغ: ' . number_format($top_visitor_by_price->total_sales) : 'Unknown';


            // Get which visitor sold him the most by items
            $top_visitor_by_items_query = $wpdb->prepare(
                "SELECT op.visitor_id, SUM(dl.product_qty) as total_items
                FROM $operation_table op
                JOIN $lookup_table dl ON op.invoice_id = dl.order_id
                WHERE op.customer_id = %d
                GROUP BY op.visitor_id
                ORDER BY total_items DESC
                LIMIT 1",
                $customer_id
            );
            $top_visitor_by_items = $wpdb->get_row($top_visitor_by_items_query);
            $topVisitorByItems = $top_visitor_by_items ? get_user_by('id', $top_visitor_by_items->visitor_id)->display_name . ' (' . $top_visitor_by_items->total_items . ' قلم)' : 'Unknown';

            // Display the data
    ?>
        <tr valign="top">
            <td scope="row" class=""><?php echo esc_html($customer['customer_name']); ?></td>
            <td scope="row" class=""><a href="<?php echo esc_url($linkToInvoice); ?>"><?php echo number_format($biggestBuyPrice); ?></a></td>
            <td scope="row" class=""><?php echo esc_html($mostProductBought); ?></td>
            <td scope="row" class=""><?php echo esc_html($mostPaymentMethod); ?></td>
            <td scope="row" class=""><?php echo number_format($totalDiscount); ?></td>
            <td scope="row" class=""><?php echo esc_html($topVisitorByPrice); ?></td>
            <td scope="row" class=""><?php echo esc_html($topVisitorByItems); ?></td>
        </tr>
    <?php
        }
    ?>
</table>
<?php
    }

    // Customer Report End

    // function report_by_city()
    // {
    //     global $errorMessage;
    //     global $wpdb;
    //     $table_name = $wpdb->prefix . 'x_invoice_data_lookup';
    //     $existing_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    // echo '<h2>گزارش فروش شهر ها</h2>';
    // }

    function default_reports_page()
    {
        echo 'لطفا یک گزینه جهت نمایش گزارش را انتخاب نمایید.';
    }
