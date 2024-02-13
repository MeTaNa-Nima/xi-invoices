jQuery(document).ready(function ($) {


    // Function to parse input string as a number
	function parseInputValue(value) {
		if (!value) {
			return 0;
		}
		return parseFloat(value.replace(/,/g, "")) || 0;
	}

	// Function to add thousand separator for display
	function formatNumberForDisplay(num) {
		return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	// Event Handler to format number while typing
	$(document).on("input", ".custom_product_amount, .custom_product_price", function () {
		var $this = $(this);
		var inputVal = $this.val();
		var caretPos = this.selectionStart; // Get the cursor position before formatting

		// Remove non-digit characters (except decimal point)
		var cleanInput = inputVal.replace(/[^\d.]/g, "");

		// Handle the edge case where the input may start with a non-numeric character
		if (cleanInput === "") {
			$this.val("");
			return;
		}

		// Formatting the input value
		var formattedInput = parseFloat(cleanInput)
			.toFixed(0)
			.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

		$this.val(formattedInput);

		// Calculate the difference in length between the original and the formatted value
		var diff = formattedInput.length - inputVal.length;

		// Set the cursor back to the correct position after formatting
		this.setSelectionRange(caretPos + diff, caretPos + diff);
	});

	// Update the total for a single row
	function updateRowTotal(row) {
		var amount = parseInputValue(row.find(".custom_product_amount").val());
		var price = parseInputValue(row.find(".custom_product_price").val());
		var total = amount * price;
		row.find(".custom_product_total").val(formatNumberForDisplay(total));
		row.find(".custom_product_show_only").html(formatNumberForDisplay(total));
	}

	// Calculate the total of all rows
	function calculateInvoiceTotal() {
		var invoiceTotalPrice = 0;
		$(".productsList .custom_product_total").each(function () {
			invoiceTotalPrice += parseInputValue($(this).val());
		});
		return invoiceTotalPrice;
	}

	// Calculate the total of all RETURNED rows
	function calculateInvoiceReturnedTotal() {
		var invoiceTotalReturnedPrice = 0;
		if ($(".include_returned_products").is(":checked")) {
			$(".returned_productsList .custom_product_total").each(function () {
				invoiceTotalReturnedPrice += parseInputValue($(this).val());
			});
		}
		return invoiceTotalReturnedPrice;
	}

	// Calculate the discount amount
	function calculateDiscount(invoiceTotal, invoiceReturnedTotal) {
		var discountAmount = 0;
		if ($(".invoice_discount").is(":checked")) {
			if ($(".payment_percents").is(":checked")) {
				var discountPercentage = parseInputValue($(".discount_percents").val());
				discountAmount = (invoiceTotal - invoiceReturnedTotal) * (discountPercentage / 100);
			} else if ($(".payment_constant").is(":checked")) {
				discountAmount = parseInputValue($(".discount_constant").val());
			}
		}
        $('.discount_calculated').val(discountAmount);
		return discountAmount;
	}

	// Calculate the tax amount
	function calculateTax(invoiceTotal, invoiceReturnedTotal) {
		var taxAmount = 0;
		if ($(".invoice_includes_tax").is(":checked")) {
			var taxPercentage = parseInputValue($(".tax_amount_value").val());
			taxAmount = (invoiceTotal - invoiceReturnedTotal) * (taxPercentage / 100);
		}
		return taxAmount;
	}

	// Update the summary of the invoice
	function updateInvoiceSummary() {
		var invoiceTotal			= calculateInvoiceTotal();
		var invoiceReturnedTotal	= calculateInvoiceReturnedTotal();
		var discountAmount			= calculateDiscount(invoiceTotal, invoiceReturnedTotal);

		// Calculate the subtotal after discount
		var subtotalAfterDiscount = invoiceTotal - invoiceReturnedTotal - discountAmount;

		var taxAmount = calculateTax(subtotalAfterDiscount, 0); // No need to subtract invoiceReturnedTotal again

		var finalTotal = subtotalAfterDiscount + taxAmount;

		$(".invoice_total_pure").val(formatNumberForDisplay(invoiceTotal));
		$(".invoice_total_pure_show_only").html(formatNumberForDisplay(invoiceTotal));

		$(".invoice_total_returned_pure").val(formatNumberForDisplay(invoiceReturnedTotal));
		$(".invoice_total_returned_pure_show_only").html(formatNumberForDisplay(invoiceReturnedTotal));

		$(".invoice_total_prices").val(formatNumberForDisplay(finalTotal));
		$(".invoice_total_prices_show_only").html(formatNumberForDisplay(finalTotal));
		console.log('Products:' + invoiceTotal + " - Returns:" + invoiceReturnedTotal + " - Discount:" + discountAmount + " - Subtotal:" + subtotalAfterDiscount + " - Tax:" + taxAmount + " - Total:" + finalTotal);
	}

    
	// Event Handlers
	$(".productsList").on("input", ".custom_product_amount, .custom_product_price", function () {
		updateRowTotal($(this).closest("tr"));
		updateInvoiceSummary();
	});
	$(".returned_productsList").on(
		"input",
		".custom_product_amount, .custom_product_price",
		function () {
			updateRowTotal($(this).closest("tr"));
			updateInvoiceSummary();
		}
	);
	$(".include_returned_products").change(function () {
		if ($(this).is(":checked")) {
			$(".returned_products_section").fadeIn();
		} else {
			$(".returned_products_section").fadeOut();
		}
		updateInvoiceSummary(); // Call this function to recalculate the invoice summary when the checkbox is checked/unchecked
	});

	$('.new_customer').click(function(e) {
		e.preventDefault();
		$('.xi_add_customer_container').toggle();
	});

	// Add Remove Rows New Products
	$(document).on("click", ".add_new_product_row", function (e) {
		e.preventDefault();
		var lastRow = $(".productsList tbody tr:last");
		var newRow = lastRow.clone();
		newRow.find("input").val("");
		newRow.find("select").val("-1");
		newRow.find(".custom_product_show_only").html(""); // Clear the content of the span
		newRow.appendTo(".productsList tbody");
		lastRow.find(".remove_product_row").show();
		newRow.find(".remove_product_row").show();
		updateInvoiceSummary();
	});
	$(document).on("click", ".remove_product_row", function (e) {
		e.preventDefault();
		if ($(".productsList tbody tr").length === 1) {
			$(".productsList .remove_product_row").hide();
		}
		$(this).closest("tr").remove();
		updateInvoiceSummary();
	});

	// Add Remove Rows Returned Products
	$(document).on("click", ".add_new_returned_row", function (e) {
		e.preventDefault();
		var lastRow = $(".returned_productsList tbody tr:last");
		var newRow = lastRow.clone();
		newRow.find("input").val("");
		newRow.find("select").val("-1");
		newRow.find(".custom_product_show_only").html(""); // Clear the content of the span
		newRow.appendTo(".returned_productsList tbody");
		lastRow.find(".remove_returned_row").show();
		newRow.find(".remove_returned_row").show();
		updateInvoiceSummary();
	});
	$(document).on("click", ".remove_returned_row", function (e) {
		e.preventDefault();
		if ($(".returned_productsList tbody tr").length === 1) {
			$(".returned_productsList .remove_returned_row").hide();
		}
		$(this).closest("tr").remove();
		updateInvoiceSummary();
	});

	$(".payment_discount .invoice_discount").change(function () {
		if ($(this).is(":checked")) {
			$(".payment_discount_method").prop("disabled", false);
			$(".payment_discount_method").fadeIn();
		} else {
			$(".payment_discount_method").prop("disabled", true);
			$(".payment_discount_method").fadeOut();
		}
	});
	function toggleDiscountFields() {
		var isPercent = $(".payment_discount .payment_percents").is(":checked");
		var isConstant = $(".payment_discount .payment_constant").is(":checked");
		if (isPercent) {
			$(".payment_discount .discount_percents").prop("disabled", false);
			$(".payment_discount .discount_percents").show();
		} else {
			$(".payment_discount .discount_percents").prop("disabled", true);
			$(".payment_discount .discount_percents").hide();
		}
		if (isConstant) {
			$(".payment_discount .discount_constant").prop("disabled", false);
			$(".payment_discount .discount_constant").show();
		} else {
			$(".payment_discount .discount_constant").prop("disabled", true);
			$(".payment_discount .discount_constant").hide();
		}
	}
	toggleDiscountFields();
	$(".payment_discount_methods").change(function () {
		toggleDiscountFields();
	});
	$(".invoice_discount, .invoice_includes_tax, .include_returned_products").change(
		updateInvoiceSummary
	);
	$(
		".payment_discount_methods, .discount_percents, .discount_constant, .include_returned_products"
	).on("input", updateInvoiceSummary);

	$(".payment_method").change(function () {
		$("#submit_invoice").toggle($(".payment_method:checked").length > 0);
	});

	$(".invoice_includes_tax").change(function () {
		if ($(this).is(":checked")) {
			$(".tax_amounts").fadeIn();
		} else {
			$(".tax_amounts").fadeOut();
		}
	});

	$(".include_returned_products").change(function () {
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
    
        // Validate select fields for customers
        var isValid = true;
        $('select.customer_name').each(function () {
            if ($(this).val() === '-1') {
                isValid = false;
                $(this).addClass('has-error');
                $('.xi-form-error-msg').text('لطفا فیلد مشتری را به درستی انتخاب کنید.');
            }
        });

        // Validate select fields for main products
        $('.productsList select').each(function () {
            if ($(this).val() === '-1') {
                isValid = false;
                $(this).addClass('has-error');
                $('.xi-form-error-msg').text('لطفا فیلد های محصولات را به درستی انتخاب کنید.');
            }
        });
    
        // Validate select fields for returned products only if "include_returned_products" is checked
        if ($('.include_returned_products').is(':checked')) {
            $('.returned_productsList select').each(function () {
                if ($(this).val() === '-1') {
                    isValid = false;
                    $(this).addClass('has-error');
                    $('.xi-form-error-msg').text('لطفا فیلد های محصولات مرجوعی را به درستی انتخاب کنید.');
                }
            });
        }
    
        if (!isValid) {
            // If not valid, prevent form submission and highlight the error message
            $('.xi-form-error-msg').css('color', 'red');
            return;
        }

        var formData = {
            'action'                    : 'x_invoice_submit_invoice',
            'security'                  : myAjax.nonce,
            'customer_id'               : $('select.customer_name').val(),
            'date_time'                 : $('.current_date_time').val(),
            'order_include_tax'         : $('.invoice_includes_tax').is(':checked') ? 'yes' : 'no',
            'order_total_tax'           : $('.tax_amount_value').val(),
            'order_include_discount'    : $('.invoice_discount').is(':checked') ? 'yes' : 'no',
            'discount_method'           : $('.payment_discount_methods:checked').val(),
            'discount_total_amount'     : $('.discount_constant').val(),
            'discount_total_percentage' : $('.discount_percents').val(),
            'discount_calculated'       : $('.discount_calculated').val(),
            'payment_method'            : $('.payment_method:checked').val(),
            'order_total_pure'          : parseInputValue($('.invoice_total_pure').val()).toString(),
            'order_total_final'         : parseInputValue($('.invoice_total_prices').val()).toString(),
            'include_returned_products' : $('.include_returned_products').is(':checked') ? 'yes' : 'no',
			'invoice_notes'             : $('.invoice_notes').val(),
            'products'                  : []
        };

        $('.productsList tbody tr').each(function() {
            var productData = {
                'product_id'        : $(this).find('.custom_product_name').val(),
                'quantity'          : parseInputValue($(this).find('.custom_product_amount').val()),
                'net_price'         : parseInputValue($(this).find('.custom_product_price').val()),
                'total_price'       : parseInputValue($(this).find('.custom_product_total').val()),
                'date_time'         : $('.current_date_time').val(),
                'sale_return_flag'  : 'sold' // Flag indicating the product is sold
            };
            formData.products.push(productData);
        });
        
        if ($('.include_returned_products').is(':checked')) {
            $('.returned_productsList tbody tr').each(function() {
                var returnedProductData = {
                    'product_id'        : $(this).find('.custom_product_name').val(),
                    'quantity'          : parseInputValue($(this).find('.custom_product_amount').val()),
                    'net_price'         : parseInputValue($(this).find('.custom_product_price').val()),
                    'total_price'       : parseInputValue($(this).find('.custom_product_total').val()),
                    'date_time'         : $('.current_date_time').val(),
                    'sale_return_flag'  : 'returned' // Flag indicating the product is returned
                };
                formData.products.push(returnedProductData);
            });
        }

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
					$("div.xi-invoice-view div.xi-invoice-view-form-controls input.xi-pdf-url").val(response.data.pdf_url);

                } else {
                    alert('Failed to generate PDF.');
                }
            }
        });
    });
    // PDF Creation End

	$("div.xi-invoice-view div.xi-invoice-view-form-controls input.xi-pdf-url").on('click', function() {
		$(this).select();
		document.execCommand("copy");
	});
});



function fetchCustomerDetails(customerId) {
	if (!customerId) {
		// Clear the fields if no customer is selected
		jQuery(".customer_mobile_no").text("");
		jQuery(".customer_shop_name").text("");
		jQuery(".customer_address").text("");
		return;
	}

	// AJAX request to get customer details
	jQuery.ajax({
		url: myAjax.ajaxurl, // Replace 'myAjax.ajaxurl' with your AJAX URL
		type: "POST",
		data: {
			action: "get_customer_details", // Your AJAX action to fetch customer details
			customer_id: customerId,
		},
		success: function (response) {
			if (response) {
				var data = JSON.parse(response);
				jQuery(".customer_mobile_no").text(data.mobile_no);
				jQuery(".customer_shop_name").text(data.shop_name);
				jQuery(".customer_address").text(data.address);
			}
		},
	});

}