<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/buttons/3.0.1/css/buttons.bootstrap5.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/select/2.0.0/css/select.bootstrap5.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/datetime/1.5.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
        <link href="{{ asset('assets/x-editable/bootstrap-editable.css') }}" rel="stylesheet" type="text/css" />
        <style>
            body {
                margin-top: 50px;
            }

            .form-control-sm, .editable-input {
                width: 100% !important;
            }

            .editable-inline {
                border-left: 1px solid #d9d9d9;
                width: calc(100% + 1px) !important;
                margin-left: -1px;
            }

            .editable-error-block {
                color: red;
            }

            .dt-search {
                display: flex;
                align-items: center;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="container">
            <form id="productsForm">
                @csrf
                <div class="input-group">
                    <input type="text" class="form-control" name="product_name" placeholder="Title" required>
                    <input type="number" class="form-control" name="quantity_in_stock" placeholder="Quantity" required>
                    <input type="number" class="form-control" name="price_per_item" placeholder="Price" required>
                    <div class="input-group-prepend">
                        <button type="submit" class="btn btn-outline-secondary">Submit</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive-xl">
                <table id="productsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Datetime</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>Title</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Datetime</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </body>
    <script src="https://code.jquery.com/jquery-2.0.3.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.0/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.5.2/js/dataTables.dateTime.min.js"></script>
    <script src="{{ asset('assets/x-editable/bootstrap-editable.min.js') }}"></script>

    <script>
		$(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                }
            });

            $('#productsTable').DataTable({
                ajax: '{{ route('products.data') }}',
                columns: [
                    { data: 'product_name', orderable: false },
                    { data: 'quantity_in_stock', orderable: false },
                    { data: 'price_per_item', orderable: false },
                    { data: 'datetime_submitted', orderable: false },
                    { data: 'total_value_number', orderable: false },
                ],
                order: [[3, 'desc']],
                responsive: false,
                stateSave: true,
                bPaginate: true,
                stateDuration: 60 * 60 * 24 * 60 * 60,
                serverSide: false,
                autoWidth: false,
                scrollCollapse: true,
                scrollX: true,
                processing: false,
                pageLength: 100,
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();
                    $(api.column(4).footer()).html(`Total : ${api.column(4).data().reduce(function(a, b) {return parseFloat(a) + parseFloat(b)}, 0)}`);
                },
                createdRow: function (row, data, index) {
                    $('td', row).eq(0).addClass('product_name');
                    $('td', row).eq(0).attr('data-pk', data['id']);
                    $('td', row).eq(0).attr('data-name', data['product_name']);

                    $('td', row).eq(1).addClass('quantity_in_stock');
                    $('td', row).eq(1).attr('data-pk', data['id']);
                    $('td', row).eq(1).attr('data-name', data['quantity_in_stock']);

                    $('td', row).eq(2).addClass('price_per_item');
                    $('td', row).eq(2).attr('data-pk', data['id']);
                    $('td', row).eq(2).attr('data-name', data['price_per_item']);
                },
                drawCallback: function(settings){
                    var api = this.api();

                    $('.product_name', api.table().body()).editable({
                        mode: 'inline',
                        inputclass: "form-control-sm",
                        pk: 1,
                        onblur: "submit",
                        showbuttons: false,
                        validate: function (value) {
                            if($.trim(value) == '') {
                                return 'Please input correct value!';
                            }
                        },
                        url: '{{ route('products.update') }}',
                        success: function(response, newValue) {
                            $('#productsTable').DataTable().ajax.reload();
                        },
                        params: function(params) {
                            var data = {};
                            data['id'] = $(this).editable().data('pk');
                            data['product_name'] = params?.value;
                            return data;
                        }
                    });

                    $('.quantity_in_stock', api.table().body()).editable({
                        mode: 'inline',
                        inputclass: "form-control-sm",
                        pk: 1,
                        onblur: "submit",
                        showbuttons: false,
                        validate: function (value) {
                            var value = Number($.trim(value)) || '';
                            if(typeof value === 'number') {
                                return;
                            }
                            return 'Please enter correct value!';
                        },
                        url: '{{ route('products.update') }}',
                        success: function(response, newValue) {
                            $('#productsTable').DataTable().ajax.reload();
                        },
                        params: function(params) {
                            var data = {};
                            data['id'] = $(this).editable().data('pk');
                            data['quantity_in_stock'] = params?.value;
                            return data;
                        }
                    });

                    $('.price_per_item', api.table().body()).editable({
                        mode: 'inline',
                        inputclass: "form-control-sm",
                        pk: 1,
                        onblur: "submit",
                        showbuttons: false,
                        validate: function (value) {
                            var value = Number($.trim(value)) || '';
                            if(typeof value === 'number') {
                                return;
                            }
                            return 'Please enter correct value!';
                        },
                        url: '{{ route('products.update') }}',
                        success: function(response, newValue) {
                            $('#productsTable').DataTable().ajax.reload();
                        },
                        params: function(params) {
                            var data = {};
                            data['id'] = $(this).editable().data('pk');
                            data['price_per_item'] = params?.value;
                            return data;
                        }
                    });
                },
            });

            $('#productsForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('products.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#productsTable').DataTable().ajax.reload();
                    },
                    error: function(response) {
                        alert('An error occurred');
                    }
                });
            });
        });
    </script>
</html>
