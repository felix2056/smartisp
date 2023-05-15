<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
    </button>
    <h4 class="modal-title" id="myModalLabel"><i
            class="fa fa-globe"></i>
        {{ __('app.Onu Detail') }}</h4>
</div>
<div class="modal-body" id="winnew">
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert bg-info alert-icon-right alert-arrow-right alert-dismissible mb-2"
                         role="alert" style="color: #fff; background: #ff4961;">
                        <span class="alert-icon"><i class="la la-info-circle"></i></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true" style="color: #000">×</span>
                        </button>
                        {{ $error  }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

