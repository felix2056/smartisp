<table class="display supertable table table-striped table-bordered">
    <thead>
    <tr>
        <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.type')</font></font></th>
        <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.quantity')</font></font></th>
        <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.total')</font></font></th>
    </tr>
    </thead>
    <tbody>
    @php
        $classes = [
            'Unpaid' => 'warning',
            'Paid Out' => 'success',
            'Paid (account balance)' => 'success',
            'Late' => 'danger',
            'Total' => 'primary',
            'Removed' => 'default'
        ];
    @endphp
    @foreach($data as $key => $total)
        <tr>
            <td>
                <label class="label label-{{ $classes[$key] }}">
                    <font style="vertical-align: inherit;">
                        <font style="vertical-align: inherit;">
                            {{ ucFirst($key) }}
                        </font>
                    </font>
                </label>
            </td>
            <td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $total['quantity'] }}</td>
            <td id="admin_customers_view_billing_transactions_totals_debit_total">{{ $total['total'] }}</td>
        </tr>
    @endforeach

    </tbody>
</table>
