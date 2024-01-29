<?php
class Xi_Invoices_Reports {
    private $wpdb;
    private $products_table;
    private $data_lookup_table;
    private $operation_data_table;
    private $customers_table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->products_table       = $wpdb->prefix . 'x_invoice_products';
        $this->data_lookup_table    = $wpdb->prefix . 'x_invoice_data_lookup';
        $this->operation_data_table = $wpdb->prefix . 'x_invoice_operation_data';
        $this->customers_table      = $wpdb->prefix . 'x_invoice_customers';
    }

    public function getReportDataByProduct()
    {
        $sql = "SELECT p.product_name, 
                COUNT(*) as invoice_count, 
                SUM(dl.product_total_price) as total_sales, 
                AVG(dl.product_net_price) as avg_net_price, 
                (SELECT c.customer_name FROM {$this->wpdb->prefix}x_invoice_customers c WHERE c.customer_id = (SELECT customer_id FROM {$this->data_lookup_table} WHERE product_id = p.product_id GROUP BY customer_id ORDER BY COUNT(*) DESC LIMIT 1)) as top_customer
                FROM {$this->products_table} p
                LEFT JOIN {$this->data_lookup_table} dl ON p.product_id = dl.product_id
                LEFT JOIN {$this->operation_data_table} op ON dl.order_id = op.invoice_id
                GROUP BY p.product_id";

        return $this->wpdb->get_results($sql);
    }

    public function getReportDataByVisitor($visitor_id)
    {
        $top_customer_query = $this->wpdb->prepare(
            "SELECT c.customer_name, COUNT(*) as total_orders
            FROM $this->operation_data_table op
            JOIN $this->customers_table c ON op.customer_id = c.customer_id
            WHERE op.visitor_id = %d
            GROUP BY op.customer_id
            ORDER BY total_orders DESC
            LIMIT 1",
            $visitor_id
        );
        $top_customer = $this->wpdb->get_row($top_customer_query);
        $topCustomerName = $top_customer ? $top_customer->customer_name : 'Unknown';

        // Get top product and quantity for this visitor
        $top_product_query = $this->wpdb->prepare(
            "SELECT p.product_name, SUM(dl.product_qty) as total_qty
            FROM $this->data_lookup_table dl
            JOIN $this->products_table p ON dl.product_id = p.product_id
            JOIN $this->operation_data_table op ON dl.order_id = op.invoice_id
            WHERE op.visitor_id = %d
            GROUP BY dl.product_id
            ORDER BY total_qty DESC
            LIMIT 1",
            $visitor_id
        );
        $top_product = $this->wpdb->get_row($top_product_query);
        $topProductName = $top_product ? $top_product->product_name : 'Unknown';
        $topProductQty = $top_product ? $top_product->total_qty : 0;


        // Get top sale (highest order_total_final) for this visitor
        $top_sale_query = $this->wpdb->prepare(
            "SELECT MAX(op.order_total_final) as top_sale, op.invoice_id
            FROM $this->operation_data_table op
            WHERE op.visitor_id = %d
            GROUP BY op.visitor_id",
            $visitor_id
        );
        $top_sale = $this->wpdb->get_row($top_sale_query);
        $topSale = $top_sale ? $top_sale->top_sale : 0;
        $linkToTopSale = $top_sale ? admin_url('admin.php?page=xi-orders-list&invoice_id=' . $top_sale->invoice_id) : '#';

        // Get mostly used payment method for this visitor
        $top_payment_method_query = $this->wpdb->prepare(
            "SELECT op.payment_method, COUNT(*) as total
            FROM $this->operation_data_table op
            WHERE op.visitor_id = %d
            GROUP BY op.payment_method
            ORDER BY total DESC
            LIMIT 1",
            $visitor_id
        );
        $top_payment_method = $this->wpdb->get_row($top_payment_method_query);
        $topPaymentMethod = $top_payment_method ? $top_payment_method->payment_method : 'Unknown';

        // Get total discount given by this visitor
        $total_discount_query = $this->wpdb->prepare(
            "SELECT SUM(op.discount_total_amount) as total_discount
            FROM $this->operation_data_table op
            WHERE op.visitor_id = %d",
            $visitor_id
        );
        $totalDiscount = $this->wpdb->get_var($total_discount_query);

        // Get total pure sales for this visitor
        $total_pure_sales_query = $this->wpdb->prepare(
            "SELECT SUM(op.order_total_pure) as total_pure_sales
            FROM $this->operation_data_table op
            WHERE op.visitor_id = %d",
            $visitor_id
        );
        $totalPureSales = $this->wpdb->get_var($total_pure_sales_query);

        // Get total sales (order_total_final) for this visitor
        $total_sales_query = $this->wpdb->prepare(
            "SELECT SUM(op.order_total_final) as total_sales
            FROM $this->operation_data_table op
            WHERE op.visitor_id = %d",
            $visitor_id
        );
        $totalSales = $this->wpdb->get_var($total_sales_query);

        return [
            'topCustomerName'   => $topCustomerName,
            'topProductName'    => $topProductName,
            'topProductQty'     => $topProductQty,
            'topSale'           => $topSale,
            'linkToTopSale'     => $linkToTopSale,
            'topPaymentMethod'  => $topPaymentMethod,
            'totalDiscount'     => $totalDiscount,
            'totalPureSales'    => $totalPureSales,
            'totalSales'        => $totalSales
        ];
    }

    public function getReportDataByCustomer($customer_id) {
        // Get biggest buy by price and link to invoice
        $biggest_buy_query = $this->wpdb->prepare(
            "SELECT op.invoice_id, MAX(op.order_total_final) as biggest_buy
            FROM $this->operation_data_table op
            WHERE op.customer_id = %d
            GROUP BY op.customer_id",
            $customer_id
        );
        $biggest_buy = $this->wpdb->get_row($biggest_buy_query);
        $biggestBuyPrice = $biggest_buy ? $biggest_buy->biggest_buy : 0;
        $linkToInvoice = $biggest_buy ? admin_url('admin.php?page=xi-orders-list&invoice_id=' . $biggest_buy->invoice_id) : '#';

        // Get most product bought
        $top_product_query = $this->wpdb->prepare(
            "SELECT p.product_name, SUM(dl.product_qty) as total_qty
            FROM $this->data_lookup_table dl
            JOIN $this->products_table p ON dl.product_id = p.product_id
            JOIN $this->operation_data_table op ON dl.order_id = op.invoice_id
            WHERE op.customer_id = %d
            GROUP BY dl.product_id
            ORDER BY total_qty DESC
            LIMIT 1",
            $customer_id
        );
        $top_product = $this->wpdb->get_row($top_product_query);
        $mostProductBought = $top_product ? $top_product->product_name : 'Unknown';
        $topProductQty = $top_product ? $top_product->total_qty : 0;

        // Get most used payment method
        $most_payment_method_query = $this->wpdb->prepare(
            "SELECT op.payment_method, COUNT(*) as total
            FROM $this->operation_data_table op
            WHERE op.customer_id = %d
            GROUP BY op.payment_method
            ORDER BY total DESC
            LIMIT 1",
            $customer_id
        );
        $most_payment_method = $this->wpdb->get_row($most_payment_method_query);
        $mostPaymentMethod = $most_payment_method ? $most_payment_method->payment_method : 'Unknown';

        // Get total discount given to this customer
        $total_discount_query = $this->wpdb->prepare(
            "SELECT SUM(op.discount_total_amount) as total_discount
            FROM $this->operation_data_table op
            WHERE op.customer_id = %d",
            $customer_id
        );
        $totalDiscount = $this->wpdb->get_var($total_discount_query);

        // Get which visitor sold him the most by price
        $top_visitor_by_price_query = $this->wpdb->prepare(
            "SELECT op.visitor_id, SUM(op.order_total_final) as total_sales
            FROM $this->operation_data_table op
            WHERE op.customer_id = %d
            GROUP BY op.visitor_id
            ORDER BY total_sales DESC
            LIMIT 1",
            $customer_id
        );
        $top_visitor_by_price = $this->wpdb->get_row($top_visitor_by_price_query);
        $topVisitorByPrice = $top_visitor_by_price ? get_user_by('id', $top_visitor_by_price->visitor_id)->display_name . ' - مبلغ: ' . number_format($top_visitor_by_price->total_sales) : 'Unknown';


        // Get which visitor sold him the most by items
        $top_visitor_by_items_query = $this->wpdb->prepare(
            "SELECT op.visitor_id, SUM(dl.product_qty) as total_items
            FROM $this->operation_data_table op
            JOIN $this->data_lookup_table dl ON op.invoice_id = dl.order_id
            WHERE op.customer_id = %d
            GROUP BY op.visitor_id
            ORDER BY total_items DESC
            LIMIT 1",
            $customer_id
        );
        $top_visitor_by_items = $this->wpdb->get_row($top_visitor_by_items_query);
        $topVisitorByItems = $top_visitor_by_items ? get_user_by('id', $top_visitor_by_items->visitor_id)->display_name . ' (' . $top_visitor_by_items->total_items . ' قلم)' : 'Unknown';

        // Get total Sales by this customer
        $total_sale_query = $this->wpdb->prepare(
            "SELECT SUM(op.order_total_final) as total_sale
            FROM $this->operation_data_table op
            WHERE op.customer_id = %d",
            $customer_id
        );
        $totalSale = $this->wpdb->get_var($total_sale_query);

        return [
            'biggestBuyPrice'   =>$biggestBuyPrice,
            'linkToInvoice'     =>$linkToInvoice,
            'mostProductBought' =>$mostProductBought,
            'topProductQty'     =>$topProductQty,
            'mostPaymentMethod' =>$mostPaymentMethod,
            'totalDiscount'     =>$totalDiscount,
            'topVisitorByPrice' =>$topVisitorByPrice,
            'topVisitorByItems' =>$topVisitorByItems,
            'totalSale'         =>$totalSale
        ];
    }

    
}

