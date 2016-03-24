<script>
    ! function ($) {
        $(function () {
            // -------------------
            // Order Builder
            // -------------------
            $(document).on('click', '.btn-add-order', function(e) {
                e.preventDefault();
                var $target_table = $(this).attr('data-target-table');
                var $target_select = $(this).attr('data-target-select');
                var $target_input = $(this).attr('data-target-input');
                var $target_name = $(this).attr('data-target-name');
                
                $($target_select + ' option:selected').each(function() {
                    var $select_id = $(this).val();
                    var $select_name = $(this).text();
                    var $select_quantity = $($target_input).val();
                    var $select_row_id = $target_name + '_' + $select_id;
                    
                    // Add new product to hidden field
                    $obj = {
                        id: $select_id,
                        name: $select_name,
                        quantity: $select_quantity
                    };
                    $hidden_input = "<input type='hidden' name='" + $target_name + "[]' value='" + JSON.stringify($obj) + "'>";
                    // Populate row
                    $product_cells = '<td>' + $select_id + '</td>' +
                        '<td>' + $select_name + '</td>' +
                        '<td>' + $select_quantity + '</td>' +
                        '<td class="text-right">' +
                        '<a data-id="' + $select_row_id +
                        '" class="btn btn-flat btn-danger btn-xs btn-remove-order">x</a>' +
                        $hidden_input
                        '</td>';
                    
                    if ($('#' + $select_row_id).length == 0) {
                        $($target_table + ' tbody').append(
                            '<tr id="' + $select_row_id + '">' + $product_cells + '</tr>'
                        );
                    } else {
                        $('#' + $select_row_id).empty().html($product_cells);
                    }
                    // Flash the newly added row
                    $('#' + $select_row_id).addClass('warning', 500, 'easeInOutQuint', function() {
                        $('#' + $select_row_id).delay(2000).removeClass('warning', 500, 'easeInOutQuint');
                    });
                });
            });
            $(document).on('click', '.btn-remove-order', function(e) {
                e.preventDefault();
                var $remove_id = $(this).attr('data-id');
                if ($('#' + $remove_id).length != 0) {
                    $('#' + $remove_id).empty().remove();
                }
            });
        })
    }(window.jQuery);
</script>