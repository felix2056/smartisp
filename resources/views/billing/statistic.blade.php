<style type="text/css">
    #idrefre{
        top: 85px !important;
    }

    .form-control {
        width: inherit !important;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 widget-container-col">
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-flat widget-header-small">
                        <h5 class="widget-title">
                            Statistics
                            <select name="service" class="form-control pull-right" id="service" onchange="changeService()">
                                @foreach($clients->service as $key => $service)
                                    <option value="{{ $service->ip }}" @if($key == 0) selected @endif> {{ $service->ip }}</option>
                                @endforeach
                            </select>

                            <select id="statistics_chart_type" class="form-control pull-right" style="margin-right: 10px" onchange="changeService()">
                                <option value="hour" selected="selected">Hourly graph</option>
                                <option value="day">Daily graph</option>
                                <option value="week">Weekly graph</option>
                                <option value="month">Monthly graph</option>
                                <option value="year">Yearly graph</option>
                            </select>
                        </h5>

                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <!--Contenido widget-->
                            @foreach($clients->service as $key => $service)

                            <iframe id="statisticChart" src="http://{{$_SERVER['SERVER_NAME']}}:3000/d-solo/lZjJB-NGk/smartisp_graph?orgId=1&refresh=5s&from=now-1h&to=now&var-client_ip={{ $service->ip }}&theme=light&panelId=2" width="100%" height="300" frameborder="0"></iframe>                            @endforeach


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>

<script>
    $(document).ready(function() {
        changeService();
    });

    function changeService() {
        var service = $('#service').val();
        var chartType = $('#statistics_chart_type').val();
        var intval = '';
        switch (chartType) {
            case 'hour':
                intval = '1h';
                break;
            case 'day':
                intval = '1d';
                break;
            case 'week':
                intval = '1w';
                break;
            case 'month':
                intval = '1M';
                break;
            case 'year':
                intval = '1y';
                break;
        }
        console.log('Hello from console');
        $('#statisticChart').prop('src', '//{{$_SERVER['SERVER_NAME']}}:3000/d-solo/lZjJB-NGk/smartisp_graph?orgId=1&refresh=5s&from=now-' + intval + '&to=now&var-client_ip=' + service + '&theme=light&panelId=2')

    }
</script>