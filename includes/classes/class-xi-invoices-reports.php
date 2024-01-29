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
        // Top Customer Detail
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

        return [
            'topCustomerName'   => $topCustomerName,
        ];
    }

    public function getReportDataByCustomer($customer_id) {
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


        return [
            'topVisitorByPrice' =>$topVisitorByPrice,
            'topVisitorByItems' =>$topVisitorByItems,
        ];
    }

    public function getTotalDiscount($entity_id, $by = 'visitor') {
        $column = $by === 'visitor' ? 'visitor_id' : 'customer_id';
        $total_discount_query = $this->wpdb->prepare(
            "SELECT SUM(op.discount_total_amount) as total_discount
            FROM $this->operation_data_table op
            WHERE op.$column = %d",
            $entity_id
        );
        return $this->wpdb->get_var($total_discount_query);
    }

    public function getMostPaymentMethod($entity_id, $by = 'visitor') {
        $column = $by === 'visitor' ? 'visitor_id' : 'customer_id';
        $top_payment_method_query = $this->wpdb->prepare(
            "SELECT op.payment_method, COUNT(*) as total
            FROM $this->operation_data_table op
            WHERE op.$column = %d
            GROUP BY op.payment_method
            ORDER BY total DESC
            LIMIT 1",
            $entity_id
        );
        $top_payment_method = $this->wpdb->get_row($top_payment_method_query);
        return $top_payment_method ? $top_payment_method->payment_method : 'Unknown';
    }

    public function getTotalSaleFinal($entity_id, $by = 'visitor') {
        $column = $by === 'visitor' ? 'visitor_id' : 'customer_id';
        $total_sales_query = $this->wpdb->prepare(
            "SELECT SUM(op.order_total_final) as total_sales
            FROM $this->operation_data_table op
            WHERE op.$column = %d",
            $entity_id
        );
        return $this->wpdb->get_var($total_sales_query);
    }

    public function getTotalSalePure($entity_id, $by = 'visitor') {
        $column = $by === 'visitor' ? 'visitor_id' : 'customer_id';
        $total_pure_sales_query = $this->wpdb->prepare(
            "SELECT SUM(op.order_total_pure) as total_pure_sales
            FROM $this->operation_data_table op
            WHERE op.$column = %d",
            $entity_id
        );
        return $this->wpdb->get_var($total_pure_sales_query);
    }

    public function getTopSaleDetails($entity_id, $by = 'visitor') {
        $column = $by === 'visitor' ? 'visitor_id' : 'customer_id';
        $biggest_buy_query = $this->wpdb->prepare(
            "SELECT op.invoice_id, MAX(op.order_total_final) as biggest_sale
            FROM $this->operation_data_table op
            WHERE op.$column = %d
            GROUP BY op.$column",
            $entity_id
        );
        $biggest_sale = $this->wpdb->get_row($biggest_buy_query);
        if ($biggest_sale) {
            $linkToInvoice = admin_url('admin.php?page=xi-orders-list&invoice_id=' . $biggest_sale->invoice_id);
            return [
                'amount'    => $biggest_sale->biggest_sale,
                'url'       => $linkToInvoice,
                'id'        => $biggest_sale->invoice_id
            ];
        }
        return null;
    }


    public function getTopProductSold($entity_id, $by = 'visitor') {
        $column = $by === 'visitor' ? 'visitor_id' : 'customer_id';
        $top_product_query = $this->wpdb->prepare(
            "SELECT p.product_name, SUM(dl.product_qty) as total_qty
            FROM $this->data_lookup_table dl
            JOIN $this->products_table p ON dl.product_id = p.product_id
            JOIN $this->operation_data_table op ON dl.order_id = op.invoice_id
            WHERE op.$column = %d
            GROUP BY dl.product_id
            ORDER BY total_qty DESC
            LIMIT 1",
            $entity_id
        );
        $top_product = $this->wpdb->get_row($top_product_query);
        if ($top_product) {
            return [
                'name' => $top_product->product_name,
                'quantity' => $top_product->total_qty
            ];
        }
    
        return ['name' => 'Unknown',
                'quantity' => 0
        ];
    }

    




    
}

