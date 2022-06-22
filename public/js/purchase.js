$(document).ready(function () {
    if ($('input#iraqi_selling_price_adjustment').length > 0) {
        iraqi_selling_price_adjustment = true;
    } else {
        iraqi_selling_price_adjustment = false;
    }

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    //get suppliers
    $('#supplier_id')
        .select2({
            ajax: {
                url: '/purchases/get_suppliers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data,
                    };
                },
            },
            minimumInputLength: 1,
            escapeMarkup: function (m) {
                return m;
            },
            templateResult: function (data) {
                if (!data.id) {
                    return data.text;
                }
                var html = data.text + ' - ' + data.business_name + ' (' + data.contact_id + ')';
                return html;
            },
            language: {
                noResults: function () {
                    var name = $('#supplier_id').data('select2').dropdown.$search.val();
                    return (
                        '<button type="button" data-name="' +
                        name +
                        '" class="btn btn-link add_new_supplier"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                        __translate('add_name_as_new_supplier', { name: name }) +
                        '</button>'
                    );
                },
            },
        })
        .on('select2:select', function (e) {
            var data = e.params.data;
            $('#pay_term_number').val(data.pay_term_number);
            $('#pay_term_type').val(data.pay_term_type);
        });

    //Quick add supplier
    $(document).on('click', '.add_new_supplier', function () {
        $('#supplier_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal').find('input#name').val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('supplier')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });

    $('form#quick_add_contact')
        .submit(function (e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contact-id',
                        type: 'post',
                        data: {
                            contact_id: function () {
                                return $('#contact_id').val();
                            },
                            hidden_id: function () {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function (form) {
                $(form).find('button[type="submit"]').attr('disabled', true);
                var data = $(form).serialize();
                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            $('select#supplier_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            );
                            $('select#supplier_id').val(result.data.id).trigger('change');
                            $('div.contact_modal').modal('hide');
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function () {
        $('form#quick_add_contact').find('button[type="submit"]').removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });

    //Add products
    if ($('#search_product').length > 0) {
        $('#search_product')
            .autocomplete({
                source: function (request, response) {
                    $.getJSON(
                        '/purchases/get_products',
                        { location_id: $('#location_id').val(), term: request.term },
                        response
                    );
                },
                minLength: 2,
                response: function (event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        $(this)
                            .data('ui-autocomplete')
                            ._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                    } else if (ui.content.length == 0) {
                        var term = $(this).data('ui-autocomplete').term;
                        swal({
                            title: LANG.no_products_found,
                            text: __translate('add_name_as_new_product', { term: term }),
                            buttons: [LANG.cancel, LANG.ok],
                        }).then((value) => {
                            if (value) {
                                var container = $('.quick_add_product_modal');
                                $.ajax({
                                    url: '/products/quick_add?product_name=' + term,
                                    dataType: 'html',
                                    success: function (result) {
                                        $(container).html(result).modal('show');
                                    },
                                });
                            }
                        });
                    }
                },
                select: function (event, ui) {
                    $(this).val(null);
                    get_purchase_entry_row(ui.item.product_id, ui.item.variation_id);
                },
            })
            .autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>')
                .append('<div>' + item.text + '</div>')
                .appendTo(ul);
        };
    }

    $(document).on('click', '.remove_purchase_entry_row', function () {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((value) => {
            if (value) {
                let row_count = $(this).data('row_count');
                $('.unload_tank')
                    .find('#tank_row' + row_count)
                    .remove();
                unloadTankQtyMatch();
                $(this).closest('tr').remove();
                update_table_total();
                update_grand_total();
                update_table_sr_number();
            }
        });
    });

    //On Change of quantity
    $(document).on('change', '.purchase_quantity', function () {
        var row = $(this).closest('tr');
        var quantity = __read_number($(this), true);
        var purchase_before_tax = __read_number(row.find('input.purchase_unit_cost'), true);
        var purchase_after_tax = __read_number(
            row.find('input.purchase_unit_cost_after_tax'),
            true
        );

        //Calculate sub totals
        var sub_total_before_tax = quantity * purchase_before_tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        update_table_total();
        update_grand_total();
    });

    $(document).on('change', '.purchase_unit_cost_without_discount', function () {
        var purchase_before_discount = __read_number($(this), true);

        var row = $(this).closest('tr');
        var discount_percent = __read_number(row.find('input.inline_discounts'), true);
        var quantity = __read_number(row.find('input.purchase_quantity'), true);

        //Calculations.
        var purchase_before_tax =
            parseFloat(purchase_before_discount) -
            __calculate_amount('percentage', discount_percent, purchase_before_discount);

        __write_number(row.find('input.purchase_unit_cost'), purchase_before_tax, true);

        var sub_total_before_tax = quantity * purchase_before_tax;

        //Tax
        var tax_rate = parseFloat(
            row.find('select.purchase_line_tax_id').find(':selected').data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        update_inline_profit_percentage(row);
        update_table_total();
        update_grand_total();
    });

    $(document).on('change', '.inline_discounts', function () {
        var row = $(this).closest('tr');

        var discount_percent = __read_number($(this), true);

        var quantity = __read_number(row.find('input.purchase_quantity'), true);
        var purchase_before_discount = __read_number(
            row.find('input.purchase_unit_cost_without_discount'),
            true
        );

        //Calculations.
        var purchase_before_tax =
            parseFloat(purchase_before_discount) -
            __calculate_amount('percentage', discount_percent, purchase_before_discount);

        __write_number(row.find('input.purchase_unit_cost'), purchase_before_tax, true);

        var sub_total_before_tax = quantity * purchase_before_tax;

        //Tax
        var tax_rate = parseFloat(
            row.find('select.purchase_line_tax_id').find(':selected').data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        update_inline_profit_percentage(row);
        update_table_total();
        update_grand_total();
    });

    $(document).on('change', '.purchase_unit_cost', function () {
        var row = $(this).closest('tr');
        var quantity = __read_number(row.find('input.purchase_quantity'), true);
        var purchase_before_tax = __read_number($(this), true);

        var sub_total_before_tax = quantity * purchase_before_tax;

        //Update unit cost price before discount
        var discount_percent = __read_number(row.find('input.inline_discounts'), true);
        var purchase_before_discount = __get_principle(purchase_before_tax, discount_percent, true);
        __write_number(
            row.find('input.purchase_unit_cost_without_discount'),
            purchase_before_discount,
            true
        );

        //Tax
        var tax_rate = parseFloat(
            row.find('select.purchase_line_tax_id').find(':selected').data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        //row.find('.purchase_product_unit_tax_text').text( tax );
        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        update_inline_profit_percentage(row);
        update_table_total();
        update_grand_total();
    });

    $(document).on('change', 'select.purchase_line_tax_id', function () {
        var row = $(this).closest('tr');
        var purchase_before_tax = __read_number(row.find('.purchase_unit_cost'), true);
        var quantity = __read_number(row.find('input.purchase_quantity'), true);

        //Tax
        var tax_rate = parseFloat($(this).find(':selected').data('tax_amount'));
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        //Purchase price
        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        update_table_total();
        update_grand_total();
    });

    $(document).on('change', '.purchase_unit_cost_after_tax', function () {
        var row = $(this).closest('tr');
        var purchase_after_tax = __read_number($(this), true);
        var quantity = __read_number(row.find('input.purchase_quantity'), true);

        var sub_total_after_tax = purchase_after_tax * quantity;

        //Tax
        var tax_rate = parseFloat(
            row.find('select.purchase_line_tax_id').find(':selected').data('tax_amount')
        );
        var purchase_before_tax = __get_principle(purchase_after_tax, tax_rate);
        var sub_total_before_tax = quantity * purchase_before_tax;
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        //Update unit cost price before discount
        var discount_percent = __read_number(row.find('input.inline_discounts'), true);
        var purchase_before_discount = __get_principle(purchase_before_tax, discount_percent, true);
        __write_number(
            row.find('input.purchase_unit_cost_without_discount'),
            purchase_before_discount,
            true
        );

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        __write_number(row.find('.purchase_unit_cost'), purchase_before_tax, true);

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, true, true));
        __write_number(row.find('input.purchase_product_unit_tax'), tax);

        update_table_total();
        update_grand_total();
    });

    $('#tax_id, #discount_type, #discount_amount, input#shipping_charges').change(function () {
        update_grand_total();
    });

    //Purchase table
    //Purchase table
    purchase_table = $('#purchase_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/purchases',
            data: function (d) {
                if ($('#purchase_list_filter_location_id').length) {
                    d.location_id = $('#purchase_list_filter_location_id').val();
                }
                if ($('#purchase_list_filter_supplier_id').length) {
                    d.supplier_id = $('#purchase_list_filter_supplier_id').val();
                }
                if ($('#purchase_list_filter_payment_status').length) {
                    d.payment_status = $('#purchase_list_filter_payment_status').val();
                }
                if ($('#purchase_list_filter_status').length) {
                    d.status = $('#purchase_list_filter_status').val();
                }

                var start = '';
                var end = '';
                if ($('#purchase_list_filter_date_range').val()) {
                    start = $('input#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;

                // d = __datatable_ajax_callback(d);
            },
        },
        aaSorting: [[1, 'desc']],
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'location_name', name: 'BS.name' },
            { data: 'name', name: 'contacts.name' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'final_total', name: 'final_total' },
            { data: 'payment_due', name: 'payment_due', orderable: false, searchable: false },
            { data: 'payment_method', name: 'payment_method', orderable: false, searchable: false },
            { data: 'added_by', name: 'u.first_name' },
        ],
        fnDrawCallback: function (oSettings) {
            var total_purchase = sum_table_col($('#purchase_table'), 'final_total');
            $('#footer_purchase_total').text(total_purchase);

            var total_due = sum_table_col($('#purchase_table'), 'payment_due');
            $('#footer_total_due').text(total_due);

            var total_purchase_return_due = sum_table_col($('#purchase_table'), 'purchase_return');
            $('#footer_total_purchase_return_due').text(total_purchase_return_due);

            $('#footer_status_count').html(__sum_status_html($('#purchase_table'), 'status-label'));

            $('#footer_payment_status_count').html(
                __sum_status_html($('#purchase_table'), 'payment-status-label')
            );

            __currency_convert_recursively($('#purchase_table'));
        },
        createdRow: function (row, data, dataIndex) {
            $(row).find('td:eq(5)').attr('class', 'clickable_td');
        },
    });

    $(document).on(
        'change',
        '#purchase_list_filter_location_id, \
                    #purchase_list_filter_supplier_id, #purchase_list_filter_payment_status,\
                     #purchase_list_filter_status',
        function () {
            purchase_table.ajax.reload();
        }
    );

    update_table_sr_number();

    $(document).on('change', '.mfg_date', function () {
        var this_date = $(this).val();
        var this_moment = moment(this_date, moment_date_format);
        var expiry_period = parseFloat($(this).closest('td').find('.row_product_expiry').val());
        var expiry_period_type = $(this).closest('td').find('.row_product_expiry_type').val();
        if (this_date) {
            if (expiry_period && expiry_period_type) {
                exp_date = this_moment
                    .add(expiry_period, expiry_period_type)
                    .format(moment_date_format);
                $(this).closest('td').find('.exp_date').datepicker('update', exp_date);
            } else {
                $(this).closest('td').find('.exp_date').datepicker('update', '');
            }
        } else {
            $(this).closest('td').find('.exp_date').datepicker('update', '');
        }
    });

    $('#purchase_entry_table tbody')
        .find('.expiry_datepicker')
        .each(function () {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
        });

    $(document).on('change', '.profit_percent', function () {
        var row = $(this).closest('tr');
        var profit_percent = __read_number($(this), true);

        var purchase_unit_cost = __read_number(
            row.find('input.purchase_unit_cost_after_tax'),
            true
        );
        var default_sell_price =
            parseFloat(purchase_unit_cost) +
            __calculate_amount('percentage', profit_percent, purchase_unit_cost);
        var exchange_rate = $('input#exchange_rate').val();
        __write_number(
            row.find('input.default_sell_price'),
            default_sell_price * exchange_rate,
            true
        );
    });

    $(document).on('change', '.default_sell_price', function () {
        var row = $(this).closest('tr');
        update_inline_profit_percentage(row);
    });

    $('table#purchase_table tbody').on('click', 'a.delete-purchase', function (e) {
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            purchase_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

    $('table#purchase_entry_table').on('change', 'select.sub_unit', function () {
        var tr = $(this).closest('tr');
        var base_unit_cost = tr.find('input.base_unit_cost').val();
        var base_unit_selling_price = tr.find('input.base_unit_selling_price').val();

        var multiplier = parseFloat($(this).find(':selected').data('multiplier'));

        var unit_sp = base_unit_selling_price * multiplier;
        var unit_cost = base_unit_cost * multiplier;

        var sp_element = tr.find('input.default_sell_price');
        __write_number(sp_element, unit_sp);

        var cp_element = tr.find('input.purchase_unit_cost_without_discount');
        __write_number(cp_element, unit_cost);
        cp_element.change();

        let cs_element = tr.find('input.current_stock');
        var orignalstock = tr.find('input.current_stock').data('orignalstock');

        unit_stock = orignalstock / multiplier;
        cs_element.val(__number_f(unit_stock, false));
    });
    toggle_search();
});

function get_purchase_entry_row(product_id, variation_id) {
    if (product_id) {
        let duplicate_found = false;
        $('#purchase_entry_table tbody')
            .find('tr')
            .each(function () {
                if (
                    parseInt($(this).find('.hidden_variation_id').val()) === parseInt(variation_id)
                ) {
                    duplicate_found = true;
                }
            });
        if (duplicate_found) {
            return;
        }
        var row_count = $('#row_count').val();
        var purchase_pos = 0;
        if ($('#purchase_pos').length) {
            purchase_pos = $('#purchase_pos').val();
        }
        $.ajax({
            method: 'POST',
            url: '/purchases/get_purchase_entry_row',
            dataType: 'html',
            data: {
                product_id: product_id,
                row_count: row_count,
                variation_id: variation_id,
                purchase_pos: purchase_pos,
            },
            success: function (result) {
                $(result)
                    .find('.purchase_quantity')
                    .each(function () {
                        row = $(this).closest('tr');

                        $('#purchase_entry_table tbody').append(
                            update_purchase_entry_row_values(row)
                        );
                        update_row_price_for_exchange_rate(row);

                        update_inline_profit_percentage(row);

                        update_table_total();
                        update_grand_total();
                        update_table_sr_number();

                        //Check if multipler is present then multiply it when a new row is added.
                        if (__getUnitMultiplier(row) > 1) {
                            row.find('select.sub_unit').trigger('change');
                        }
                    });
                if ($(result).find('.purchase_quantity').length) {
                    $('#row_count').val(
                        $(result).find('.purchase_quantity').length + parseInt(row_count)
                    );
                }

                //if fuel category product then show unload tanks
                if ($(result).find('.is_fuel_category').val() == 1) {
                    $.ajax({
                        method: 'get',
                        url: '/purchases/get_unload_tank_row',
                        data: {
                            product_id: $(result).find('.product_id').val(),
                            location_id: $('#location_id').val(),
                            row_count: row_count,
                        },
                        success: function (data) {
                            if (data) {
                                $('.unload_div').removeClass('hide');
                                $('.unload_tank').append(data);
                                $('#submit_purchase_form')
                                    .attr('disabled', 'disabled')
                                    .off('click');
                            }
                            unloadTankQtyMatch();
                        },
                    });
                }
            },
        });
    }
}

function unloadTankQtyMatch() {
    checked = false;
    row = 0;
    $('.check_tank_row').each(function (index) {
        if (row === 0) {
            $(this).find('.tank_qty ').prop('disabled', false);
        }
        row_index = $(this).data('row_id');
        stock_match = $('#stock_match' + row_index).val();
        if (stock_match == '1') {
            checked = true;
        } else {
            $('#submit_purchase_form').attr('disabled', 'disabled').off('click');
            if (checked) {
                $(this).find('.tank_qty').prop('disabled', false);
            }
            checked = false;
            return false;
        }
        row++;
    });
    return checked;
}

function update_purchase_entry_row_values(row) {
    if (typeof row != 'undefined') {
        var quantity = __read_number(row.find('.purchase_quantity'), true);
        var unit_cost_price = __read_number(row.find('.purchase_unit_cost'), true);
        var row_subtotal_before_tax = quantity * unit_cost_price;
        var tax_rate = parseFloat(
            $('option:selected', row.find('.purchase_line_tax_id')).attr('data-tax_amount')
        );

        var unit_product_tax = __calculate_amount('percentage', tax_rate, unit_cost_price);

        var unit_cost_price_after_tax = unit_cost_price + unit_product_tax;
        var row_subtotal_after_tax = quantity * unit_cost_price_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(row_subtotal_before_tax, false, true)
        );
        __write_number(row.find('.row_subtotal_before_tax_hidden'), row_subtotal_before_tax, true);
        __write_number(row.find('.purchase_product_unit_tax'), unit_product_tax, true);
        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(unit_product_tax, false, true)
        );
        row.find('.purchase_unit_cost_after_tax').text(
            __currency_trans_from_en(unit_cost_price_after_tax, true)
        );
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(row_subtotal_after_tax, false, true)
        );
        __write_number(row.find('.row_subtotal_after_tax_hidden'), row_subtotal_after_tax, true);

        row.find('.expiry_datepicker').each(function () {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
        });
        return row;
    }
}

function update_row_price_for_exchange_rate(row) {
    var exchange_rate = $('input#exchange_rate').val();

    if (exchange_rate == 1) {
        return true;
    }

    var purchase_unit_cost_without_discount =
        __read_number(row.find('.purchase_unit_cost_without_discount'), true) / exchange_rate;
    __write_number(
        row.find('.purchase_unit_cost_without_discount'),
        purchase_unit_cost_without_discount,
        true
    );

    var purchase_unit_cost = __read_number(row.find('.purchase_unit_cost'), true) / exchange_rate;
    __write_number(row.find('.purchase_unit_cost'), purchase_unit_cost, true);

    var row_subtotal_before_tax_hidden =
        __read_number(row.find('.row_subtotal_before_tax_hidden'), true) / exchange_rate;
    row.find('.row_subtotal_before_tax').text(
        __currency_trans_from_en(row_subtotal_before_tax_hidden, false, true)
    );
    __write_number(
        row.find('input.row_subtotal_before_tax_hidden'),
        row_subtotal_before_tax_hidden,
        true
    );

    var purchase_product_unit_tax =
        __read_number(row.find('.purchase_product_unit_tax'), true) / exchange_rate;
    __write_number(row.find('input.purchase_product_unit_tax'), purchase_product_unit_tax, true);
    row.find('.purchase_product_unit_tax_text').text(
        __currency_trans_from_en(purchase_product_unit_tax, false, true)
    );

    var purchase_unit_cost_after_tax =
        __read_number(row.find('.purchase_unit_cost_after_tax'), true) / exchange_rate;
    __write_number(
        row.find('input.purchase_unit_cost_after_tax'),
        purchase_unit_cost_after_tax,
        true
    );

    var row_subtotal_after_tax_hidden =
        __read_number(row.find('.row_subtotal_after_tax_hidden'), true) / exchange_rate;
    __write_number(
        row.find('input.row_subtotal_after_tax_hidden'),
        row_subtotal_after_tax_hidden,
        true
    );
    row.find('.row_subtotal_after_tax').text(
        __currency_trans_from_en(row_subtotal_after_tax_hidden, false, true)
    );
}

function iraqi_dinnar_selling_price_adjustment(row) {
    var default_sell_price = __read_number(row.find('input.default_sell_price'), true);

    //Adjsustment
    var remaining = default_sell_price % 250;
    if (remaining >= 125) {
        default_sell_price += 250 - remaining;
    } else {
        default_sell_price -= remaining;
    }

    __write_number(row.find('input.default_sell_price'), default_sell_price, true);

    update_inline_profit_percentage(row);
}

function update_inline_profit_percentage(row) {
    //Update Profit percentage
    var default_sell_price = __read_number(row.find('input.default_sell_price'), true);
    var exchange_rate = $('input#exchange_rate').val();
    default_sell_price_in_base_currency = default_sell_price / parseFloat(exchange_rate);

    var purchase_after_tax = __read_number(row.find('input.purchase_unit_cost_after_tax'), true);
    var profit_percent = __get_rate(purchase_after_tax, default_sell_price_in_base_currency);
    __write_number(row.find('input.profit_percent'), profit_percent, true);
}

function update_table_total() {
    var total_quantity = 0;
    var total_st_before_tax = 0;
    var total_subtotal = 0;

    $('#purchase_entry_table tbody')
        .find('tr')
        .each(function () {
            total_quantity += __read_number($(this).find('.purchase_quantity'), true);
            total_st_before_tax += __read_number(
                $(this).find('.row_subtotal_before_tax_hidden'),
                true
            );
            total_subtotal += __read_number($(this).find('.row_subtotal_after_tax_hidden'), true);
        });

    $('.total_quantity').text(__number_f(total_quantity, true));
    $('#total_st_before_tax').text(__currency_trans_from_en(total_st_before_tax, true, true));
    __write_number($('input#st_before_tax_input'), total_st_before_tax, true);

    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
    __write_number($('input#total_subtotal_input'), total_subtotal, true);
    __write_number($('input#final_total_input'), total_subtotal, true);

    $('#total_payable').text(__currency_trans_from_en(total_subtotal, true, true));
    $('.price_total').text(__currency_trans_from_en(total_subtotal, true, true));
    __write_number($('input#total_subtotal_input'), total_subtotal, true);
}

function update_grand_total() {
    var st_before_tax = __read_number($('input#st_before_tax_input'), true);
    var total_subtotal = __read_number($('input#total_subtotal_input'), true);

    //Calculate Discount
    var discount_type = $('select#discount_type').val();
    var discount_amount = __read_number($('input#discount_amount'), true);
    var discount = __calculate_amount(discount_type, discount_amount, total_subtotal);
    $('#discount_calculated_amount').text(__currency_trans_from_en(discount, true, true));

    //Calculate Tax
    var tax_rate = parseFloat($('option:selected', $('#tax_id')).data('tax_amount'));
    var tax = __calculate_amount('percentage', tax_rate, total_subtotal - discount);
    __write_number($('input#tax_amount'), tax);
    $('#tax_calculated_amount').text(__currency_trans_from_en(tax, true, true));

    //Calculate shipping
    var shipping_charges = __read_number($('input#shipping_charges'), true);

    //Calculate Final total
    grand_total = total_subtotal - discount + tax + shipping_charges;
    __write_number($('input#grand_total_hidden'), grand_total, true);

    var payment = __read_number($('input.payment-amount'), true);
    $('#total_paying_input').val(payment);
    var due = grand_total - payment;

    $('#grand_total').text(__currency_trans_from_en(grand_total, true, true));

    $('#payment_due').text(__currency_trans_from_en(due, true, true));

    if ($('#is_edit').length === 0) {
        $('input#amount_0').val(grand_total);
        $('.payment-amount').trigger('change');
    }
    //__currency_convert_recursively($(document));
}
$(document).on('change', 'input.payment-amount', function () {
    var payment = 0;

    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function () {
            if (parseFloat($(this).val())) {
                payment += __read_number($(this));
            }
        });
    var grand_total = __read_number($('input#grand_total_hidden'), true);
    var bal = grand_total - payment;
    $('#payment_due').text(__currency_trans_from_en(bal, true, true));

    if (
        $('.payment_types_dropdown').val() === 'cash' ||
        $('.payment_types_dropdown').val() === 'cheque'
    ) {
        if (parseFloat($(this).val()) > grand_total) {
            toastr.error('Amount Should not be more then total amount');
            $(this).val('');
        }
    }

    var row_id = parseInt($(this).closest('.payment_row').data('row_id'));
    if (row_id >= 0) {
        check_insufficient_balance_row(row_id);
    }
});

function update_table_sr_number() {
    var sr_number = 1;
    $('table#purchase_entry_table tbody')
        .find('.sr_number')
        .each(function () {
            $(this).text(sr_number);
            sr_number++;
        });

    $('#purchaseBody').each(function (elem, index) {
        var arr = $.makeArray($('tr', this).detach());
        arr.reverse();
        $(this).append(arr);
    });
}

$(document).on('click', 'button#submit_purchase_form', function (e) {
    e.preventDefault();
    //check if internet available or not
    var offline = Offline.state;

    if (offline == 'down') {
        toastr.error('No internet connection available');
        return offline;
    }
    //Check if product is present or not.
    if ($('table#purchase_entry_table tbody tr').length <= 0) {
        toastr.warning(LANG.no_products_added);
        $('input#search_product').select();
        return false;
    }

    jQuery.validator.addMethod('greaterThanZero', function (value, element) {
        return parseFloat(value) > 0;
    });
    $.validator.messages.greaterThanZero = 'Zero Values not accepted. Please correct';
    jQuery.validator.addClassRules('payment-amount', {
        required: true,
        // greaterThanZero: true,
    });

    $('form#add_purchase_form').validate({
        rules: {},
        messages: {},
    });

    if ($('form#add_purchase_form').valid()) {
        $('form#add_purchase_form').submit();
    }
});

function toggle_search() {
    if ($('#location_id').val()) {
        $('#search_product').removeAttr('disabled');
        $('#search_product').focus();
    } else {
        $('#search_product').attr('disabled', true);
    }
}

$(document).on('change', '#location_id', function () {
    toggle_search();
    $('#purchase_entry_table tbody').html('');
    update_table_total();
    update_grand_total();
    update_table_sr_number();
});

$(document).on('shown.bs.modal', '.quick_add_product_modal', function () {
    var selected_location = $('#location_id').val();
    if (selected_location) {
        $('.quick_add_product_modal')
            .find('#product_locations')
            .val([selected_location])
            .trigger('change');
    }
});

$('button#add-payment-row').click(function () {
    var row_index = parseInt($('.payment_row_index').val()) + 1;
    var location_id = $('#location_id').val();

    $.ajax({
        method: 'POST',
        url: '/purchases/get_payment_row',
        data: { row_index: row_index, location_id: location_id },
        dataType: 'html',
        success: function (result) {
            if (result) {
                var total_payable = __read_number($('input#grand_total_hidden'));
                var total_paying = 0;
                $('#payment_rows_div')
                    .find('.payment-amount')
                    .each(function () {
                        if (parseFloat($(this).val())) {
                            total_paying += __read_number($(this));
                        }
                    });
                var b_due = total_payable - total_paying;
                var appended = $('#payment_rows_div').append(result);
                $(appended).find('input.payment-amount').focus();
                $(appended).find('input.payment-amount').last().val(b_due).change().select();
                __select2($(appended).find('.select2'));
                $('#amount_' + row_index).trigger('change');
                $('#cheque_date_' + row_index).datepicker('setDate', new Date());
                $('.payment_row_index').val(parseInt(row_index));
                let cash_account_id = $('#cash_account_id').val();
                $(appended)
                    .find('select.payment_types_dropdown ')
                    .last()
                    .val(cash_account_id)
                    .change()
                    .select();
            }
        },
    });
});

$(document).on('click', '.remove_payment_row', function () {
    swal({
        title: LANG.sure,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $(this).closest('.payment_row').remove();
            calculate_balance_due();
        }
    });
});

$(document).on('change', '.row_subtotal_after_tax_hidden', function () {
    let line_total = __read_number($(this));
    var row = $(this).closest('tr');

    let qty = __read_number(row.find('.purchase_quantity '));
    let purchase_pirce_before_discount = line_total / qty;
    row.find('.purchase_unit_cost_without_discount').val(purchase_pirce_before_discount).change();
});

function get_product_suggestion_list(category_id, brand_id, location_id, url = null) {
    if ($('div#product_list_body').length == 0) {
        return false;
    }

    if (url == null) {
        url = '/sells/pos/get-product-suggestion';
    }
    $('#suggestion_page_loader').fadeIn(700);
    var page = $('input#suggestion_page').val();
    if (page == 1) {
        $('div#product_list_body').html('');
    }
    if ($('div#product_list_body').find('input#no_products_found').length > 0) {
        $('#suggestion_page_loader').fadeOut(700);
        return false;
    }
    $.ajax({
        method: 'GET',
        url: url,
        data: {
            category_id: category_id,
            brand_id: brand_id,
            location_id: location_id,
            page: page,
        },
        dataType: 'html',
        success: function (result) {
            $('div#product_list_body').append(result);
            $('#suggestion_page_loader').fadeOut(700);
        },
    });
}
$(document).ready(function () {
    get_product_suggestion_list(
        $('select#product_category').val(),
        $('select#product_brand').val(),
        $('input#location_id').val(),
        null
    );
});

$(document).on('click', 'div.product_box', function () {
    //Check if location is not set then show error message.
    if ($('input#location_id').val() == '') {
        toastr.warning(LANG.select_location);
    } else {
        get_purchase_entry_row($(this).data('product_id'), $(this).data('variation_id'));
    }
});

//Finalize invoice, open payment modal
$('button#pos-finalize').click(function () {
    //Check if product is present or not.
    if ($('table#purchase_entry_table tbody').find('.product_row').length <= 0) {
        toastr.warning(LANG.no_products_added);
        return false;
    }

    if ($('#reward_point_enabled').length) {
        var validate_rp = isValidatRewardPoint();
        if (!validate_rp['is_valid']) {
            toastr.error(validate_rp['msg']);
            return false;
        }
    }

    $('#modal_payment').modal('show');
});

$('#modal_payment').one('shown.bs.modal', function () {
    $('#modal_payment').find('input').filter(':visible:first').focus().select();
    if ($('form#edit_pos_purchase_form').length == 0) {
        $(this).find('#method_0').change();
    }
});

//For edit pos form
if ($('form#edit_pos_purchase_form').length > 0) {
    pos_form_obj = $('form#edit_pos_purchase_form');
} else {
    pos_form_obj = $('form#add_pos_purchase_form');
}

//Finalize without showing payment options
$('button.pos-express-finalize').click(function () {
    //Check if product is present or not.
    if ($('table#purchase_entry_table tbody').find('.product_row').length <= 0) {
        toastr.warning(LANG.no_products_added);
        return false;
    }

    var pay_method = $(this).data('pay_method');

    //Change payment method.
    var payment_method_dropdown = $('#payment_rows_div').find('.payment_types_dropdown').first();

    //If pay method is credit sale submit form
    if (pay_method == 'credit_purchase') {
        $('#is_credit_purchase').val(1);
        payment_method_dropdown.val(pay_method);
        payment_method_dropdown.change();
        pos_form_obj.submit();
        return true;
    } else {
        if ($('#is_credit_purchase').length) {
            $('#is_credit_purchase').val(0);
        }
    }

    //Check for remaining balance & add it in 1st payment row
    var total_payable = __read_number($('input#final_total_input'));
    var total_paying = __read_number($('input#total_paying_input'));
    if (total_payable > total_paying) {
        var bal_due = total_payable - total_paying;
        var first_row = $('#payment_rows_div').find('.payment-amount').first();
        var first_row_val = __read_number(first_row);
        first_row_val = first_row_val + bal_due;
        __write_number(first_row, first_row_val);
        first_row.trigger('change');
    }

    payment_method_dropdown.val(pay_method);
    payment_method_dropdown.change();
    if (pay_method == 'card') {
        $('div#card_details_modal').modal('show');
    } else if (pay_method == 'suspend') {
        $('div#confirmSuspendModal').modal('show');
    } else {
        pos_form_obj.submit();
    }
});

$('div#card_details_modal').on('shown.bs.modal', function (e) {
    $('input#card_number').focus();
});

$('div#confirmSuspendModal').on('shown.bs.modal', function (e) {
    $(this).find('textarea').focus();
});

//on save card details
$('button#pos-save-card').click(function () {
    $('input#card_number_0').val($('#card_number').val());
    $('input#card_holder_name_0').val($('#card_holder_name').val());
    $('input#card_transaction_number_0').val($('#card_transaction_number').val());
    $('select#card_type_0').val($('#card_type').val());
    $('input#card_month_0').val($('#card_month').val());
    $('input#card_year_0').val($('#card_year').val());
    $('input#card_security_0').val($('#card_security').val());

    $('div#card_details_modal').modal('hide');
    pos_form_obj.submit();
});

$('button#pos-suspend').click(function () {
    $('input#is_suspend').val(1);
    $('div#confirmSuspendModal').modal('hide');
    pos_form_obj.submit();
    $('input#is_suspend').val(0);
});

//fix select2 input issue on modal
$('#modal_payment')
    .find('.select2')
    .each(function () {
        $(this).select2({
            dropdownParent: $('#modal_payment'),
        });
    });


$(document).on('click', '.remove_payment_row', function () {
    swal({
        title: LANG.sure,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $(this).closest('.payment_row').remove();
            calculate_balance_due();
        }
    });
});

pos_form_validator = pos_form_obj.validate({
    submitHandler: function (form) {
        var cnf = true;

        //Ignore if the difference is less than 0.5
        if ($('input#in_balance_due').val() >= 0.5) {
            cnf = confirm(LANG.paid_amount_is_less_than_payable);
        }

        if (cnf) {
            $('div.pos-processing').show();
            $('#pos-save').attr('disabled', 'true');
            var data = $(form).serialize();
            var url = $(form).attr('action');
            var method = $(form).attr('method');
            console.log();
            $.ajax({
                method: method,
                url: url,
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (result.success == 1) {
                        $('#modal_payment').modal('hide');
                        toastr.success(result.msg);
                        if ($(form).attr('id') !== 'edit_pos_purchase_form') {
                            reset_pos_form();
                        }
                        //Check if enabled or not
                        // if (result.receipt.is_enabled) {
                        //     pos_print(result.receipt);
                        // }
                    } else {
                        toastr.error(result.msg);
                    }

                    $('div.pos-processing').hide();
                    $('#pos-save').removeAttr('disabled');
                },
            });
        }
        return false;
    },
});

$(document).on('change', '.payment-amount', function () {
    calculate_balance_due();
});

function calculate_balance_due() {
    var total_payable = __read_number($('#final_total_input'));
    var total_paying = 0;
    if ($('input#was_customer_wallet').length) {
        total_paying = __read_number($('input#was_customer_wallet')); // if any excess amount previous cusotmer wallet
        if (parseFloat($('input#was_customer_wallet').val()) > 0) {
            $('span.total_paying').css('color', 'brown');
        } else {
            $('span.total_paying').css('color', 'white');
        }
    }
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function () {
            if (parseFloat($(this).val())) {
                total_paying += __read_number($(this));
            }
        });
    var bal_due = total_payable - total_paying;
    var change_return = 0;

    //change_return
    if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
        __write_number($('input#change_return'), bal_due * -1);
        $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
        change_return = bal_due * -1;
        bal_due = 0;
    } else {
        __write_number($('input#change_return'), 0);
        $('span.change_return_span').text(__currency_trans_from_en(0, true));

        change_return = 0;
    }

    __write_number($('input#total_paying_input'), total_paying);
    $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    __write_number($('input#in_balance_due'), bal_due);
    $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    __highlight(bal_due * -1, $('span.balance_due'));
    __highlight(change_return * -1, $('span.change_return_span'));
}

$(document).on('click', '.add_new_supplier', function () {
    $('#supplier_id').select2('close');
    var name = $(this).data('name');
    $('.contact_modal').find('input#name').val(name);
    $('.contact_modal')
        .find('select#contact_type')
        .val('supplier')
        .closest('div.contact_type_div')
        .addClass('hide');
    $('.contact_modal').modal('show');
});

//Update line total and check for quantity not greater than max quantity
$('table#pos_table tbody').on('change', 'input.purchase_quantity', function () {
    if (sell_form_validator) {
        sell_form_validator.element($(this));
    }
    if (pos_form_validator) {
        pos_form_validator.element($(this));
    }
    // var max_qty = parseFloat($(this).data('rule-max'));
    var entered_qty = __read_number($(this));

    var tr = $(this).parents('tr');

    var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
    var line_total = entered_qty * unit_price_inc_tax;

    __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
    tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
    tr.find('input.row_discount_amount').change();
    pos_total_row();

    adjustComboQty(tr);
});

//Update values for each row
function pos_each_row(row_obj) {
    var unit_price = __read_number(row_obj.find('input.pos_unit_price'));

    var discounted_unit_price = calculate_discounted_unit_price(row_obj);
    var tax_rate = row_obj.find('select.tax_id').find(':selected').data('rate');

    var unit_price_inc_tax =
        discounted_unit_price + __calculate_amount('percentage', tax_rate, discounted_unit_price);
    __write_number(row_obj.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);

    var discount = __read_number(row_obj.find('input.row_discount_amount'));

    if (discount > 0) {
        var qty = __read_number(row_obj.find('input.purchase_quantity'));
        var line_total = qty * unit_price_inc_tax;
        if (row_obj.find('select.row_discount_type').val() === 'fixed') {
            line_total = line_total - __read_number(row_obj.find('input.row_discount_amount'));
        }
        __write_number(row_obj.find('input.pos_line_total'), line_total);
    }

    //var unit_price_inc_tax = __read_number(row_obj.find('input.pos_unit_price_inc_tax'));

    __write_number(row_obj.find('input.item_tax'), unit_price_inc_tax - discounted_unit_price);
}

function pos_total_row() {
    var total_quantity = 0;
    var price_total = 0;

    $('table#pos_table tbody tr').each(function () {
        total_quantity = total_quantity + __read_number($(this).find('input.purchase_quantity'));
        price_total = price_total + __read_number($(this).find('input.pos_line_total'));
    });

    //Go through the modifier prices.
    $('input.modifiers_price').each(function () {
        price_total = price_total + __read_number($(this));
    });

    //updating shipping charges
    $('span#shipping_charges_amount').text(
        __currency_trans_from_en(__read_number($('input#shipping_charges_modal')), false)
    );

    $('span.total_quantity').each(function () {
        $(this).html(__number_f(total_quantity));
    });

    $('span.price_total').html(__currency_trans_from_en(price_total, false));
    calculate_billing_details(price_total);
}

function calculate_billing_details(price_total) {
    var discount = pos_discount(price_total);
    if ($('#reward_point_enabled').length) {
        total_customer_reward = $('#rp_redeemed_amount').val();
        discount = parseFloat(discount) + parseFloat(total_customer_reward);

        if ($('input[name="is_direct_sale"]').length <= 0) {
            $('span#total_discount').text(__currency_trans_from_en(discount, false));
        }
    }

    var order_tax = pos_order_tax(price_total, discount);

    //Add shipping charges.
    var shipping_charges = __read_number($('input#shipping_charges'));

    //Add packaging charge
    var packing_charge = 0;
    if ($('#types_of_service_id').length > 0 && $('#types_of_service_id').val()) {
        packing_charge = __calculate_amount(
            $('#packing_charge_type').val(),
            __read_number($('input#packing_charge')),
            price_total
        );

        $('#packing_charge_text').text(__currency_trans_from_en(packing_charge, false));
    }

    var total_payable = price_total + order_tax - discount + shipping_charges + packing_charge;

    __write_number($('input#final_total_input'), total_payable);
    var curr_exchange_rate = 1;
    if ($('#exchange_rate').length > 0 && $('#exchange_rate').val()) {
        curr_exchange_rate = __read_number($('#exchange_rate'));
    }
    var shown_total = total_payable * curr_exchange_rate;
    $('span#total_payable').text(__currency_trans_from_en(shown_total, false));

    $('span.total_payable_span').text(__currency_trans_from_en(total_payable, true));

    //Check if edit form then don't update price.
    if ($('form#edit_pos_sell_form').length == 0) {
        __write_number($('.payment-amount').first(), total_payable);
    }

    $(document).trigger('invoice_total_calculated');

    calculate_balance_due();
}

function reset_pos_form() {
    //If on edit page then redirect to Add POS page
    if ($('form#edit_pos_sell_form').length > 0) {
        setTimeout(function () {
            window.location = $('input#pos_redirect_url').val();
        }, 4000);
        return true;
    }
    if (pos_form_obj[0]) {
        pos_form_obj[0].reset();
    }
    $('#supplier_id').val('').change();
    $('#status').val('').change();
    $('#store_id').val('').change();

    $('#in_customer_wallet').val('0');
    $('tr.product_row').remove();
    $(
        'span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount'
    ).text(0);
    $('span.total_payable_span', 'span.total_paying', 'span.balance_due').text(0);

    $('#modal_payment')
        .find('.remove_payment_row')
        .each(function () {
            $(this).closest('.payment_row').remove();
        });

    //Reset discount
    __write_number($('input#discount_amount'), $('input#discount_amount').data('default'));
    $('input#discount_type').val($('input#discount_type').data('default'));

    //Reset tax rate
    $('input#tax_rate_id').val($('input#tax_rate_id').data('default'));
    __write_number(
        $('input#tax_calculation_amount'),
        $('input#tax_calculation_amount').data('default')
    );

    $('select.payment_types_dropdown').val('').trigger('change');
    $('#price_group').trigger('change');

    //Reset shipping
    __write_number($('input#shipping_charges'), $('input#shipping_charges').data('default'));
    $('input#shipping_details').val($('input#shipping_details').data('default'));

    if ($('input#is_recurring').length > 0) {
        $('input#is_recurring').iCheck('update');
    }
    getInvoice();
    $(document).trigger('sell_form_reset');
}

function set_default_supplier() {
    var default_supplier_id = $('#default_supplier_id').val();
    var default_supplier_name = $('#default_supplier_name').val();
    var exists = $("select#supplier_id option[value='" + default_supplier_id + "']").length;
    if (exists == 0) {
        $('select#supplier_id').append(
            $('<option>', { value: default_supplier_id, text: default_supplier_name })
        );
    }

    $('select#supplier_id').val(default_supplier_id).trigger('change');

    supplier_set = true;
}
