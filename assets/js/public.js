jQuery(document).ready(function ($) {
    // Update the total for a single row
    function updateRowTotal(row) {
        var amount = parseFloat(row.find(".custom_product_amount").val()) || 0;
        var price = parseFloat(row.find(".custom_product_price").val()) || 0;
        var total = amount * price;
        row.find(".custom_product_total").val(total.toFixed(0));
        row.find(".custom_product_show_only").html(total.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, "."));
    }

    // Calculate the total of all rows
    function calculateInvoiceTotal() {
        var invoiceTotalPrice = 0;
        $(".productsList .custom_product_total").each(function () {
            invoiceTotalPrice += parseFloat($(this).val()) || 0;
        });
        return invoiceTotalPrice;
    }

    // Calculate the total of all RETURNED rows
    function calculateInvoiceReturnedTotal() {
        var invoiceTotalReturnedPrice = 0;
        if ($('#include_returned_products').is(':checked')) {
            $(".returned_productsList .custom_product_total").each(function () {
                invoiceTotalReturnedPrice += parseFloat($(this).val()) || 0;
            });
        }
        return invoiceTotalReturnedPrice;
    }

    // Calculate the discount amount
    function calculateDiscount(invoiceTotal, invoiceReturnedTotal) {
        var discountAmount = 0;
        if ($('#invoice_discount').is(':checked')) {
            if ($('#payment_percents').is(':checked')) {
                var discountPercentage = parseFloat($('#discount_percents').val()) || 0;
                discountAmount = (invoiceTotal - invoiceReturnedTotal) * (discountPercentage / 100);
            } else if ($('#payment_constant').is(':checked')) {
                discountAmount = parseFloat($('#discount_constant').val()) || 0;
            }
        }
        return discountAmount;
    }

    // Calculate the tax amount
    function calculateTax(invoiceTotal, invoiceReturnedTotal) {
        var taxAmount = 0;
        if ($('#invoice_includes_tax').is(':checked')) {
            var taxPercentage = parseFloat($('#tax_amount_value').val()) || 0;
            taxAmount = (invoiceTotal - invoiceReturnedTotal) * (taxPercentage / 100);
        }
        return taxAmount;
    }

    // Update the summary of the invoice
    function updateInvoiceSummary() {
        var invoiceTotal = calculateInvoiceTotal();
        var invoiceReturnedTotal = calculateInvoiceReturnedTotal();
        var discountAmount = calculateDiscount(invoiceTotal, invoiceReturnedTotal);
        var taxAmount = calculateTax(invoiceTotal, invoiceReturnedTotal);
        var finalTotal = invoiceTotal - invoiceReturnedTotal - discountAmount + taxAmount;

        $(".invoice_total_pure").val(invoiceTotal.toFixed(0));
        $(".invoice_total_pure_show_only").html(invoiceTotal.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, "."));

        $(".invoice_total_returned_pure").val(invoiceReturnedTotal.toFixed(0));
        $(".invoice_total_returned_pure_show_only").html(invoiceReturnedTotal.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, "."));

        $(".invoice_total_prices").val(finalTotal.toFixed(0));
        $(".invoice_total_prices_show_only").html(finalTotal.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, "."));
    }

    // Event Handlers
    $(".productsList").on("input", ".custom_product_amount, .custom_product_price", function () {
        updateRowTotal($(this).closest("tr"));
        updateInvoiceSummary();
    });
    $(".returned_productsList").on("input", ".custom_product_amount, .custom_product_price", function () {
        updateRowTotal($(this).closest("tr"));
        updateInvoiceSummary();
    });
    $("#include_returned_products").change(function () {
        if ($(this).is(":checked")) {
            $(".returned_products_section").fadeIn();
        } else {
            $(".returned_products_section").fadeOut();
        }
        updateInvoiceSummary(); // Call this function to recalculate the invoice summary when the checkbox is checked/unchecked
    });

    // Add Remove Rows New Products
    $(document).on('click', '.add_new_product_row', function (e) {
        e.preventDefault();
		var lastRow = $('#productsList tbody tr:last');
        var newRow = lastRow.clone();
        newRow.find('input').val('');
        newRow.find('select').val('-1');
        newRow.find('.custom_product_show_only').html(''); // Clear the content of the span
        newRow.appendTo('#productsList tbody');
        lastRow.find('.remove_product_row').show();
        newRow.find('.remove_product_row').show();
        updateInvoiceSummary();
    });
    $(document).on('click', '.remove_product_row', function (e) {
        e.preventDefault();
		if($('#productsList tbody tr').length === 1) {
            $('#productsList .remove_product_row').hide();
        }
		$(this).closest("tr").remove();
        updateInvoiceSummary();
    });

    // Add Remove Rows Returned Products
    $(document).on('click', '.add_new_returned_row', function (e) {
        e.preventDefault();
		var lastRow = $('#returned_productsList tbody tr:last');
        var newRow = lastRow.clone();
        newRow.find('input').val('');
        newRow.find('select').val('-1');
        newRow.find('.custom_product_show_only').html(''); // Clear the content of the span
        newRow.appendTo('#returned_productsList tbody');
        lastRow.find('.remove_returned_row').show();
        newRow.find('.remove_returned_row').show();
        updateInvoiceSummary();
    });
    $(document).on('click', '.remove_returned_row', function (e) {
        e.preventDefault();
		if($('#returned_productsList tbody tr').length === 1) {
            $('#returned_productsList .remove_returned_row').hide();
        }
		$(this).closest("tr").remove();
        updateInvoiceSummary();
    });

	$("#invoice_discount").change(function () {
		if ($(this).is(":checked")) {
			$(".payment_discount_method").prop("disabled", false);
			$(".payment_discount_method").fadeIn();
		} else {
			$(".payment_discount_method").prop("disabled", true);
			$(".payment_discount_method").fadeOut();
		}
	});
	function toggleDiscountFields() {
        var isPercent = $('#payment_percents').is(':checked');
        var isConstant = $('#payment_constant').is(':checked');
        if (isPercent) {
            $('#discount_percents').prop('disabled', false);
            $('#discount_percents').show();
        } else {
            $('#discount_percents').prop('disabled', true);
            $('#discount_percents').hide();
        }
        if (isConstant) {
            $('#discount_constant').prop('disabled', false);
            $('#discount_constant').show();
        } else {
            $('#discount_constant').prop('disabled', true);
            $('#discount_constant').hide();
        }
    };
	toggleDiscountFields();
    $('.payment_discount_methods').change(function() {
        toggleDiscountFields();
    });
    $('#invoice_discount, #invoice_includes_tax, #include_returned_products').change(updateInvoiceSummary);
    $('.payment_discount_methods, #discount_percents, #discount_constant, #include_returned_products').on('input', updateInvoiceSummary);

    $('.payment_method').change(function () {
        $('#submit_invoice').toggle($('.payment_method:checked').length > 0);
    });

	$("#invoice_includes_tax").change(function () {
		if ($(this).is(":checked")) {
			$(".tax_amounts").fadeIn();
		} else {
			$(".tax_amounts").fadeOut();
		}
	});

    $("#include_returned_products").change(function () {
		if ($(this).is(":checked")) {
			$(".returned_products_section").fadeIn();
		} else {
			$(".returned_products_section").fadeOut();
		}
	});

    // Initial Calculations
    updateInvoiceSummary();





    // Start Sending Data via AJAX
    $('#x-invoice').submit(function(e) {
        e.preventDefault();

        // Clear previous error messages and highlights
        $('.xi-form-error-msg').text('');
        $('.has-error').removeClass('has-error');

        // Validate select fields
        var isValid = true;
        $('#x-invoice select').each(function () {
            if ($(this).val() === '-1') {
                isValid = false;
                $(this).addClass('has-error');
                $('.xi-form-error-msg').text('لطفا فیلد ها را به درستی انتخاب کنید.');
            }
        });

        if (!isValid) {
            // If not valid, prevent form submission and highlight the error message
            $('.xi-form-error-msg').css('color', 'red');
            return;
        }

        var formData = {
            'action'                    : 'x_invoice_submit_invoice',
            'security'                  : myAjax.nonce,
            'customer_id'               : $('#customer_name').val(),
            'order_include_tax'         : $('#invoice_includes_tax').is(':checked') ? 'yes' : 'no',
            'order_total_tax'           : $('#tax_amount_value').val(),
            'order_include_discount'    : $('#invoice_discount').is(':checked') ? 'yes' : 'no',
            'discount_method'           : $('.payment_discount_methods:checked').val(),
            'discount_total_amount'     : $('#discount_constant').val(),
            'discount_total_percentage' : $('#discount_percents').val(),
            'payment_method'            : $('.payment_method:checked').val(),
            'order_total_pure'          : $('.invoice_total_pure').val(),
            'order_total_final'         : $('.invoice_total_prices').val(),
            'include_returned_products' : $('#include_returned_products').val(),
            'products'                  : []
        };

        $('#productsList tbody tr').each(function() {
            var productData = {
                'product_id'        : $(this).find('.custom_product_name').val(),
                'quantity'          : $(this).find('.custom_product_amount').val(),
                'net_price'         : $(this).find('.custom_product_price').val(),
                'total_price'       : $(this).find('.custom_product_total').val(),
                'sale_return_flag'  : 'sold' // Flag indicating the product is sold
            };
            formData.products.push(productData);
        });
        
        $('#returned_productsList tbody tr').each(function() {
            var returnedProductData = {
                'product_id'        : $(this).find('.custom_product_name').val(),
                'quantity'          : $(this).find('.custom_product_amount').val(),
                'net_price'         : $(this).find('.custom_product_price').val(),
                'total_price'       : $(this).find('.custom_product_total').val(),
                'sale_return_flag'  : 'returned' // Flag indicating the product is returned
            };
            formData.products.push(returnedProductData);
        });
        

        $.post(myAjax.ajaxurl, formData, function(response) {
            if (response.success) {
                console.log(response.data.message);
                console.log(formData);
                // Redirect or handle success response
                window.location.href = response.data.redirect_url;
            } else {
                console.log('Error: ' + response.data.message);
            }
        });
    });
    // End Sending Data via AJAX


    // PDF Creation Start
    $('.xi-invoice-save-pdf').on('click', function() {
        var invoiceId = $(this).data('invoice-id');
        
        $.ajax({
            url: myAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'generate_invoice_pdf',
                invoice_id: invoiceId
            },
            success: function(response) {
                if (response.success) {
                    alert('PDF saved: ' + response.data.pdf_url);
                } else {
                    alert('Failed to generate PDF.');
                }
            }
        });
    });
    // PDF Creation End
});
