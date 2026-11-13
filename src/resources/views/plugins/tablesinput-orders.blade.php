<script>
    ! function ($) {
        $(function () {
            // Find a row by its data attribute instead of concatenating it into a selector.
            function findOrderRow($table, rowId) {
                return $table.find('tbody tr').filter(function () {
                    return $(this).attr('data-row-id') === rowId;
                }).first();
            }

            // -------------------
            // Order Builder
            // -------------------
            $(document).on('click', '.btn-add-order', function(e) {
                e.preventDefault();
                var $target_table = $(this).attr('data-target-table');
                var $target_select = $(this).attr('data-target-select');
                var $target_input = $(this).attr('data-target-input');
                var $target_name = $(this).attr('data-target-name');
                var $table = $($target_table);

                $($target_select + ' option:selected').each(function() {
                    var $select_id = $(this).val();
                    var $select_name = $(this).text();
                    var $select_quantity = $($target_input).val();
                    var $select_row_id = $target_name + '_' + $select_id;

                    // Build the hidden input and the row with DOM APIs so names and ids cannot inject markup.
                    var $obj = {
                        id: $select_id,
                        name: $select_name,
                        quantity: $select_quantity
                    };
                    var $hidden_input = $('<input>', {
                        type: 'hidden',
                        name: $target_name + '[]',
                        value: JSON.stringify($obj)
                    });
                    var $row = $('<tr>').attr('data-row-id', $select_row_id);
                    $row.append($('<td>').text($select_id));
                    $row.append($('<td>').text($select_name));
                    $row.append($('<td>').text($select_quantity));
                    var $actions = $('<td>').addClass('text-end');
                    $actions.append($('<a>', {
                        href: '#',
                        'class': 'btn btn-flat btn-danger btn-sm btn-remove-order',
                        'data-id': $select_row_id
                    }).text('x'));
                    $actions.append($hidden_input);
                    $row.append($actions);

                    var $existing = findOrderRow($table, $select_row_id);
                    if ($existing.length == 0) {
                        $table.find('tbody').append($row);
                    } else {
                        $existing.replaceWith($row);
                    }
                    // Flash the newly added row
                    $row.addClass('table-warning', 500, 'easeInOutQuint', function() {
                        $row.delay(2000).removeClass('table-warning', 500, 'easeInOutQuint');
                    });
                });
            });
            $(document).on('click', '.btn-remove-order', function(e) {
                e.preventDefault();
                var $remove_id = $(this).attr('data-id');
                var $row = $(this).closest('tr');
                if ($row.attr('data-row-id') === $remove_id) {
                    $row.remove();
                }
            });
        })
    }(window.jQuery);
</script>
