
<div class="col-lg-6 col-md-9">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.currentMonth')</font></font></font></font></strong>
        </div>

        @php
            $status = [1 => 'Paid', 2 => 'Paid (Account balance)', 3 => 'Unpaid', 4 => 'Late', 5 => 'Remove']
        @endphp

        <div class="panel-body">
            <table class="display supertable table table-striped table-bordered">
                <tbody>
                <tr>
                    <td>@lang('app.debitTransactions')</td>
                    <td>{{ $currentMonth->where('category', 'service')->count() }} ({{ $currentMonth->where('category', 'service')->sum('amount') }} {{ $global->nmoney }})</td>
                </tr>
                <tr>
                    <td>@lang('app.payments')</td>
                    <td>{{ $currentMonth->where('category', 'payment')->count() }} ({{ $currentMonth->where('category', 'payment')->sum('amount') }} {{ $global->nmoney }})</td>
                </tr>
                <tr>
                    <td>@lang('app.billsPaid')</td>
                    <td>{{ $currentMonthInvoices->where('status', '!=', 3)->count() }} ({{ $currentMonthInvoices->where('status', '!=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                </tr>
                <tr>
                    <td>@lang('app.unpaidBills')</td>
                    <td>{{ $currentMonthInvoices->where('status', '=', 3)->count() }} ({{ $currentMonthInvoices->where('status', '=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="col-lg-6 col-md-9">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.lastMonth')</font></font></font></font></strong>
        </div>
        <div class="panel-body">
            <table class="display supertable table table-striped table-bordered">
                <tbody>
                <tr>
                    <td>@lang('app.debitTransactions')</td>
                    <td>{{ $lastMonth->where('category', 'service')->count() }} ({{ $lastMonth->where('category', 'service')->sum('amount') }} {{ $global->nmoney }})</td>
                </tr>
                <tr>
                    <td>@lang('app.payments')</td>
                    <td>{{ $lastMonth->where('category', 'payment')->count() }} ({{ $lastMonth->where('category', 'payment')->sum('total') }} {{ $global->nmoney }})</td>
                </tr>
                <tr>
                    <td>@lang('app.billsPaid')</td>
                    <td>{{ $lastMonthInvoices->where('status', '!=', 3)->count() }} ({{ $lastMonthInvoices->where('status', '!=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                </tr>
                <tr>
                    <td>@lang('app.unpaidBills')</td>
                    <td>{{ $lastMonthInvoices->where('status', '=', 3)->count() }} ({{ $lastMonthInvoices->where('status', '=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>