<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
    </button>
    <h4 class="modal-title" id="myModalLabel"><i
            class="fa fa-user-plus"></i>
        {{ __('app.banHistory') }}</h4>
</div>
<div class="modal-body" id="winnew">
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default"
            data-dismiss="modal">{{ __('app.close') }}
    </button>
</div>
{!! $dataTable->scripts() !!}
