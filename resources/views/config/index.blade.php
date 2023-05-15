@extends('layouts.master')

@section('title',__('app.configuration'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
   integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
   crossorigin=""/>
    <style>
        .pac-container {
            z-index: 99999;
        }
        .form-horizontal .form-group {
            margin-right: 15px;
            margin-left: 15px;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="{{URL::to('admin')}}">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="#">@lang('app.system')</a>
                    </li>
                    <li class="active">@lang('app.configuration')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.configuration')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.configureImportantAspects')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="tabbable">
                            <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
                                <li class="active">
                                    <a data-toggle="tab" href="#faq-tab-1">
                                        <i class="blue ace-icon icon-briefcase bigger-120"></i>
                                        General
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#faq-tab-2">
                                        <i class="menu-icon ace-icon la la-cog bigger-120"></i>
                                        {{-- <i class="green ace-icon fa fa-rocket bigger-120"></i> --}}
                                        @lang('app.system')
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#faq-tab-4">
                                        <i class="ace-icon la la-user bigger-120"></i>
                                        @lang('app.clientPortal')
                                    </a>
                                </li>

                                <li>
                                    <a data-toggle="tab" href="#faq-tab-5" id="lsapis">
                                        <i class="ace-icon icon-feed bigger-120"></i>
                                        APIS
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#faq-tab-6" id="tabsms">
                                        <i class="ace-icon la la-commenting bigger-120"></i>
                                        SMS
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#faq-tab-7" id="tabpayment">
                                        <i class="ace-icon la la-money bigger-120"></i>
                                        @lang('app.payment')
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#faq-tab-8" id="tabfacturaiconelectronica">
                                        <i class="green ace-icon fa fa-rocket bigger-120"></i>
                                        @lang('app.electronicBilling')
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#faq-tab-3" id="tablanguagessettings">
                                        <i class="green ace-icon fa fa-language bigger-120"></i>
                                        @lang('app.languageSettings')
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content no-border padding-24">
                                <div id="faq-tab-1" class="tab-pane fade in active">

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <button class="btn btn-success" type="button" id="savebtnGeneral">
                                                <i class="icon-plus"></i> @lang('app.save')
                                            </button>
                                        </div>
                                    </div>

                                    <h4 class="blue">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        General
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-1" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-1" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-building-o bigger-130"></i>
                                                    &nbsp; @lang('app.companyOrganization')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-1-1">
                                                <div class="panel-body">

                                                    <form class="form-horizontal">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class="form-group">
                                                                    <label class="label-control"
                                                                           for="name">@lang('app.company')
                                                                        ( <span id="remain">19 </span> <span class="badge badge-danger">{{ __('app.charactersRemaining') }}</span>)</label>
                                                                    <input type="text" class="form-control" id="name" name="name" maxlength="19" placeholder="Company Name">
                                                                </div>
                                                            </div>
                                                            {{--<div class="col-sm-12">--}}
                                                                {{--<div class="form-group">--}}
                                                                    {{--<label class="label-control"--}}
                                                                           {{--for="name">Or Upload Logo</label>--}}
                                                                    {{--<figure class="d-none" id="">--}}
                                                                        {{--<img alt="Logo" />--}}
                                                                    {{--</figure>--}}
                                                                    {{--<input onchange="loadFile(event)" type="file" class="form-control" name="file" id="file">--}}
                                                                {{--</div>--}}
                                                            {{--</div>--}}
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="label-control"
                                                                   for="company_email">@lang('app.companyEmail')</label>
                                                            <input type="text" class="form-control" id="company_email" name="company_email" maxlength="50"  placeholder="Company Email">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="label-control"
                                                                   for="dni">@lang('app.companyDni')</label>
                                                            <input type="text" class="form-control" id="dni" name="dni" maxlength="50" placeholder="Company DNI">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="label-control"
                                                                   for="phone">@lang('app.companyPhone')</label>
                                                            <input type="text" class="form-control" id="phone" name="phone" maxlength="50"  placeholder="Company Phone">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="label-control"
                                                                   for="phone">@lang('app.companyAddress')  <span class="red">( @lang('app.companyAddressNote') )</span></label>
                                                            <div class="row">
                                                                <div class="col-sm-12 col-lg-4">
                                                                    <input type="text" class="form-control" id="street" name="street" maxlength="255"  placeholder="Street" value="{{ $street ?? '' }}">
                                                                </div>

                                                                <div class="col-sm-12 col-lg-4">
                                                                    <input type="text" class="form-control" id="state" name="state" maxlength="255"  placeholder="State" value="{{ $state ?? '' }}">
                                                                </div>

                                                                <div class="col-sm-12 col-lg-4">
                                                                    <input type="text" class="form-control" id="country" name="country" maxlength="255"  placeholder="Country" value="{{ $country ?? '' }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-2" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-usd"></i>
                                                    &nbsp; @lang('app.currency')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-1-2">
                                                <div class="panel-body">
                                                    <form class="form-inline">
                                                        <div class="form-group">
                                                            <label class="sr-only"
                                                                   for="exampleInputEmail3">@lang('app.symbol')</label>
                                                            <input type="text" class="form-control" id="smoney"
                                                                   placeholder="Símbolo" maxlength="12">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="sr-only"
                                                                   for="exampleInputPassword3">@lang('app.currency')</label>
                                                            <select class="form-control" id="money">
                                                                <option value="USD">USD - Dólar estadounidense</option>
                                                                <option value="COP">COP - Peso colombiano</option>
                                                                <option value="AED">AED - Dirham de los Emiratos Árabes
                                                                    Unidos
                                                                </option>
                                                                <option value="AFN">AFN - Afgani afgano</option>
                                                                <option value="ALL">ALL - Lek albanés</option>
                                                                <option value="AMD">AMD - Dram armenio</option>
                                                                <option value="ANG">ANG - Florín antillano neerlandés
                                                                </option>
                                                                <option value="AOA">AOA - Kwanza angoleño</option>
                                                                <option value="ARS">ARS - Peso argentino</option>
                                                                <option value="AUD">AUD - Dólar australiano</option>
                                                                <option value="AWG">AWG - Florín arubeño</option>
                                                                <option value="AZM">AZM - Manat azerbaiyano</option>
                                                                <option value="BAM">BAM - Marco convertible de
                                                                    Bosnia-Herzegovina
                                                                </option>
                                                                <option value="BBD">BBD - Dólar de Barbados</option>
                                                                <option value="BDT">BDT - Taka de Bangladesh</option>
                                                                <option value="BGN">BGN - Lev búlgaro</option>
                                                                <option value="BHD">BHD - Dinar bahreiní</option>
                                                                <option value="BIF">BIF - Franco burundés</option>
                                                                <option value="BMD">BMD - Dólar de Bermuda</option>
                                                                <option value="BND">BND - Dólar de Brunéi</option>
                                                                <option value="BOB">BOB - Boliviano</option>
                                                                <option value="BOV">BOV - Mvdol boliviano (código de
                                                                    fondos)
                                                                </option>
                                                                <option value="BRL">BRL - Real brasileño</option>
                                                                <option value="BSD">BSD - Dólar bahameño</option>
                                                                <option value="BTN">BTN - Ngultrum de Bután</option>
                                                                <option value="BWP">BWP - Pula de Botsuana</option>
                                                                <option value="BYR">BYR - Rublo bielorruso</option>
                                                                <option value="BZD">BZD - Dólar de Belice</option>
                                                                <option value="CAD">CAD - Dólar canadiense</option>
                                                                <option value="CDF">CDF - Franco congoleño</option>
                                                                <option value="CHF">CHF - Franco suizo</option>
                                                                <option value="CLF">CLF - Unidades de fomento chilenas
                                                                    (código de fondos)
                                                                </option>
                                                                <option value="CLP">CLP - Peso chileno</option>
                                                                <option value="CNY">CNY - Yuan Renminbi de China
                                                                </option>
                                                                <option value="COU">COU - Unidad de valor real
                                                                    colombiana (añadida al COP)
                                                                </option>
                                                                <option value="CRC">CRC - Colón costarricense</option>
                                                                <option value="CSD">CSD - Dinar serbio (Reemplazado por
                                                                    RSD el 25 de Octubre de 2006)
                                                                </option>
                                                                <option value="CUP">CUP - Peso cubano</option>
                                                                <option value="CUC">CUC - Peso cubano convertible
                                                                </option>
                                                                <option value="CVE">CVE - Escudo caboverdiano</option>
                                                                <option value="CYP">CYP - Libra chipriota</option>
                                                                <option value="CZK">CZK - Corona checa</option>
                                                                <option value="DJF">DJF - Franco yibutiano</option>
                                                                <option value="DKK">DKK - Corona danesa</option>
                                                                <option value="DOP">DOP - Peso dominicano</option>
                                                                <option value="DZD">DZD - Dinar argelino</option>
                                                                <option value="EEK">EEK - Corona estonia</option>
                                                                <option value="EGP">EGP - Libra egipcia</option>
                                                                <option value="ERN">ERN - Nakfa eritreo</option>
                                                                <option value="ETB">ETB - Birr etíope</option>
                                                                <option value="EUR">EUR - Euro</option>
                                                                <option value="FJD">FJD - Dólar fiyiano</option>
                                                                <option value="FKP">FKP - Libra malvinense</option>
                                                                <option value="GBP">GBP - Libra esterlina (libra de Gran
                                                                    Bretaña)
                                                                </option>
                                                                <option value="GEL">GEL - Lari georgiano</option>
                                                                <option value="GHS">GHS - Cedi ghanés</option>
                                                                <option value="GIP">GIP - Libra de Gibraltar</option>
                                                                <option value="GMD">GMD - Dalasi gambiano</option>
                                                                <option value="GNF">GNF - Franco guineano</option>
                                                                <option value="GTQ">GTQ - Quetzal guatemalteco</option>
                                                                <option value="GYD">GYD - Dólar guyanés</option>
                                                                <option value="HKD">HKD - Dólar de Hong Kong</option>
                                                                <option value="HNL">HNL - Lempira hondureño</option>
                                                                <option value="HRK">HRK - Kuna croata</option>
                                                                <option value="HTG">HTG - Gourde haitiano</option>
                                                                <option value="HUF">HUF - Forint húngaro</option>
                                                                <option value="IDR">IDR - Rupiah indonesia</option>
                                                                <option value="ILS">ILS - Nuevo shéquel israelí</option>
                                                                <option value="INR">INR - Rupia india</option>
                                                                <option value="IQD">IQD - Dinar iraquí</option>
                                                                <option value="IRR">IRR - Rial iraní</option>
                                                                <option value="ISK">ISK - Corona islandesa</option>
                                                                <option value="JMD">JMD - Dólar jamaicano</option>
                                                                <option value="JOD">JOD - Dinar jordano</option>
                                                                <option value="JPY">JPY - Yen japonés</option>
                                                                <option value="KES">KES - Chelín keniano</option>
                                                                <option value="KGS">KGS - Som kirguís (de Kirguistán)
                                                                </option>
                                                                <option value="KHR">KHR - Riel camboyano</option>
                                                                <option value="KMF">KMF - Franco comoriano (de
                                                                    Comoras)
                                                                </option>
                                                                <option value="KPW">KPW - Won norcoreano</option>
                                                                <option value="KRW">KRW - Won surcoreano</option>
                                                                <option value="KWD">KWD - Dinar kuwaití</option>
                                                                <option value="KYD">KYD - Dólar caimano (de Islas
                                                                    Caimán)
                                                                </option>
                                                                <option value="KZT">KZT - Tenge kazajo</option>
                                                                <option value="LAK">LAK - Kip laosiano</option>
                                                                <option value="LBP">LBP - Libra libanesa</option>
                                                                <option value="LKR">LKR - Rupia de Sri Lanka</option>
                                                                <option value="LRD">LRD - Dólar liberiano</option>
                                                                <option value="LSL">LSL - Loti lesotense</option>
                                                                <option value="LTL">LTL - Litas lituana</option>
                                                                <option value="LVL">LVL - Lats letón</option>
                                                                <option value="LYD">LYD - Dinar libio</option>
                                                                <option value="MAD">MAD - Dirham marroquí</option>
                                                                <option value="MDL">MDL - Leu moldavo</option>
                                                                <option value="MGA">MGA - Ariary malgache</option>
                                                                <option value="MKD">MKD - Denar macedonio</option>
                                                                <option value="MMK">MMK - Kyat birmano</option>
                                                                <option value="MNT">MNT - Tugrik mongol</option>
                                                                <option value="MOP">MOP - Pataca de Macao</option>
                                                                <option value="MRO">MRO - Ouguiya mauritana</option>
                                                                <option value="MTL">MTL - Lira maltesa</option>
                                                                <option value="MUR">MUR - Rupia mauricia</option>
                                                                <option value="MVR">MVR - Rupia de las Maldivas</option>
                                                                <option value="MWK">MWK - Kwacha malauí</option>
                                                                <option value="MXN">MXN - Peso mexicano</option>
                                                                <option value="MXV">MXV - Unidad de Inversión (UDI)
                                                                    mexicana (código de fondos)
                                                                </option>
                                                                <option value="MYR">MYR - Ringgit malayo</option>
                                                                <option value="MZN">MZN - Metical mozambiqueño</option>
                                                                <option value="NAD">NAD - Dólar namibio</option>
                                                                <option value="NGN">NGN - Naira nigeriano</option>
                                                                <option value="NIO">NIO - Córdoba nicaragüense</option>
                                                                <option value="NOK">NOK - Corona noruega</option>
                                                                <option value="NPR">NPR - Rupia nepalesa</option>
                                                                <option value="NZD">NZD - Dólar neozelandés</option>
                                                                <option value="OMR">OMR - Rial omaní</option>
                                                                <option value="PAB">PAB - Balboa panameño</option>
                                                                <option value="PEN">PEN - Nuevo sol peruano</option>
                                                                <option value="PGK">PGK - Kina de Papúa Nueva Guinea
                                                                </option>
                                                                <option value="PHP">PHP - Peso filipino</option>
                                                                <option value="PKR">PKR - Rupia pakistaní</option>
                                                                <option value="PLN">PLN - zloty polaco</option>
                                                                <option value="PYG">PYG - Guaraní paraguayo</option>
                                                                <option value="QAR">QAR - Riyal qatarí</option>
                                                                <option value="RON">RON - Leu rumano</option>
                                                                <option value="RUB">RUB - Rublo ruso</option>
                                                                <option value="RWF">RWF - Franco ruandés</option>
                                                                <option value="SAR">SAR - Riyal saudí</option>
                                                                <option value="SBD">SBD - Dólar de las Islas Salomón
                                                                </option>
                                                                <option value="SCR">SCR - Rupia de Seychelles</option>
                                                                <option value="SDG">SDG - Dinar sudanés</option>
                                                                <option value="SEK">SEK - Corona sueca</option>
                                                                <option value="SGD">SGD - Dólar de Singapur</option>
                                                                <option value="SHP">SHP - Libra de Santa Helena</option>
                                                                <option value="SKK">SKK - Corona eslovaca</option>
                                                                <option value="SLL">SLL - Leone de Sierra Leona</option>
                                                                <option value="SOS">SOS - Chelín somalí</option>
                                                                <option value="SRD">SRD - Dólar surinamés</option>
                                                                <option value="STD">STD - Dobra de Santo Tomé y
                                                                    Príncipe
                                                                </option>
                                                                <option value="SYP">SYP - Libra siria</option>
                                                                <option value="SZL">SZL - Lilangeni suazi</option>
                                                                <option value="THB">THB - Baht tailandés</option>
                                                                <option value="TJS">TJS - Somoni tayik (de Tayikistán)
                                                                </option>
                                                                <option value="TMT">TMT - Manat turcomano</option>
                                                                <option value="TND">TND - Dinar tunecino</option>
                                                                <option value="TOP">TOP - Pa’anga tongano</option>
                                                                <option value="TRY">TRY - Lira turca</option>
                                                                <option value="TTD">TTD - Dólar de Trinidad y Tobago
                                                                </option>
                                                                <option value="TWD">TWD - Dólar taiwanés</option>
                                                                <option value="TZS">TZS - Chelín tanzano</option>
                                                                <option value="UAH">UAH - Grivna ucraniana</option>
                                                                <option value="UGX">UGX - Chelín ugandés</option>
                                                                <option value="USN">USN - Dólar estadounidense
                                                                    (Siguiente día) (código de fondos)
                                                                </option>
                                                                <option value="USS">USS - United States dollar (Mismo
                                                                    día) (código de fondos)
                                                                </option>
                                                                <option value="UYU">UYU - Peso uruguayo</option>
                                                                <option value="UZS">UZS - Som uzbeko</option>
                                                                <option value="VEF">VEF - Bolívar fuerte venezolano
                                                                </option>
                                                                <option value="VND">VND - Dong vietnamita</option>
                                                                <option value="VUV">VUV - Vatu vanuatense</option>
                                                                <option value="WST">WST - Tala samoana</option>
                                                                <option value="XAF">XAF - Franco CFA de África Central
                                                                </option>
                                                                <option value="XAG">XAG - Onza de plata</option>
                                                                <option value="XAU">XAU - Onza de oro</option>
                                                                <option value="XBA">XBA - European Composite Unit
                                                                    (EURCO) (unidad del mercado de bonos)
                                                                </option>
                                                                <option value="XBB">XBB - European Monetary Unit
                                                                    (E.M.U.-6) (unidad del mercado de bonos)
                                                                </option>
                                                                <option value="XBC">XBC - European Unit of Account 9
                                                                    (E.U.A.-9) (unidad del mercado de bonos)
                                                                </option>
                                                                <option value="XBD">XBD - European Unit of Account 17
                                                                    (E.U.A.-17) (unidad del mercado de bonos)
                                                                </option>
                                                                <option value="XCD">XCD - Dólar del Caribe Oriental
                                                                </option>
                                                                <option value="XDR">XDR - Derechos Especiales de Giro
                                                                    (FMI)
                                                                </option>
                                                                <option value="XFO">XFO - Franco de oro (Special
                                                                    settlement currency)
                                                                </option>
                                                                <option value="XFU">XFU - Franco UIC (Special settlement
                                                                    currency)
                                                                </option>
                                                                <option value="XOF">XOF - Franco CFA de África
                                                                    Occidental
                                                                </option>
                                                                <option value="XPD">XPD - Onza de paladio</option>
                                                                <option value="XPF">XPF - Franco CFP</option>
                                                                <option value="XPT">XPT - Onza de platino</option>
                                                                <option value="XTS">XTS - Reservado para pruebas
                                                                </option>
                                                                <option value="XXX">XXX - Sin divisa</option>
                                                                <option value="YER">YER - Rial yemení (de Yemen)
                                                                </option>
                                                                <option value="ZAR">ZAR - Rand sudafricano</option>
                                                                <option value="ZMK">ZMK - Kwacha zambiano</option>
                                                                <option value="ZWL">ZWL - Dólar zimbabuense</option>
                                                            </select>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-3" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-sort-numeric-asc"></i>
                                                    &nbsp;@lang('app.InvoiceNumbering')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-1-3">
                                                <div class="panel-body">
                                                    <form>
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail3">@lang('app.billNumber')</label>
                                                            <input type="number" class="form-control" id="nbill"
                                                                   maxlength="4">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="exampleInputEmail3">@lang('app.invoiceTemplate')</label>
                                                            <select class="form-control" name="invoice_template_id"
                                                                    id="invoice_template_id">
                                                                @foreach($facturaTemplates as $template)
                                                                    <option value="{{ $template->id }}"
                                                                            @if($template->id == $global->invoice_template_id) selected @endif>{{ $template->name  }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-6" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-calendar-o"></i>
                                                    &nbsp; @lang('app.autoCutTolerance')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-1-6">
                                                <div class="panel-body">
                                                    <form>
                                                        <div class="form-group">
                                                            <label for="tolerance">@lang('app.daysAfterExpiration')</label>
                                                            <input type="number" min="0" value="0" name="tolerance"
                                                                   class="form-control" id="tolerance" maxlength="2">
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-5" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-bell-o bigger-130 "></i>
                                                    &nbsp; @lang('app.clientNotifications')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-5">
                                                <div class="panel-body">


                                                    <form>
                                                        <div class="form-group">
                                                            <label for="inputdays"> @lang('app.notifyDaysBefore')</label>
                                                            <input type="number" name="daysnotify" class="form-control"
                                                                   id="inputdays"
                                                                   placeholder="@lang('app.enterNumberOfDays')">
                                                        </div>
                                                        <!-- time Picker -->
                                                        <div class="bootstrap-timepicker">
                                                            <div class="form-group">
                                                                <label>@lang('app.shippingTime')</label>

                                                                <div class="input-group">
                                                                    <input type="text" name="timeemail" id="hrsemail"
                                                                           readonly class="form-control timepicker">

                                                                    <div class="input-group-addon">
                                                                        <i class="fa fa-clock-o"></i>
                                                                    </div>
                                                                </div>
                                                                <!-- /.input group -->
                                                            </div>
                                                            <!-- /.form group -->
                                                        </div>

														<div class="col-xs-12">
															<div class="col-md-6">
																<div class="form-group">
																	<label for="preadv"
																		   class="col-sm-9 control-label">@lang('app.sendEmailBeforeCourt')</label>
																	<div class="col-sm-6">
																		<label><input id="preadv"
																					  class="ace ace-switch ace-switch-6"
																					  type="checkbox"/>
																			<span class="lbl"></span>
																		</label>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label for="presms"
																		   class="col-sm-9 control-label">@lang('app.sendSMSPriorNotice')</label>
																	<div class="col-sm-6">
																		<label><input id="presms"
																					  class="ace ace-switch ace-switch-6"
																					  type="checkbox"/>
																			<span class="lbl"></span>
																		</label>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label for="prewhatsapp" class="col-sm-9 control-label">Enviar
																		whatsapp sms pre aviso de Corte</label>
																	<div class="col-sm-6">
																		<label>
																			<input id="prewhatsapp"
																				   class="ace ace-switch ace-switch-6"
																				   type="checkbox"/>
																			<span class="lbl"></span>
																		</label>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label for="prewaboxapp" class="col-sm-9 control-label">Enviar
																		waboxapp sms pre aviso de Corte</label>
																	<div class="col-sm-6">
																		<label>
																			<input id="prewaboxapp"
																				   class="ace ace-switch ace-switch-6"
																				   type="checkbox"/>
																			<span class="lbl"></span>
																		</label>
																	</div>
																</div>
															</div>
                                                            <div class="col-md-6">
																<div class="form-group">
																	<label for="prewhatsappcloudapi" class="col-sm-9 control-label">Enviar
																		whatsapp cloud sms pre aviso de Corte</label>
																	<div class="col-sm-6">
																		<label>
																			<input id="prewhatsappcloudapi"
																				   class="ace ace-switch ace-switch-6"
																				   type="checkbox"/>
																			<span class="lbl"></span>
																		</label>
																	</div>
																</div>
															</div>
														</div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-7" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-database bigger-130"></i>
                                                    &nbsp; @lang('app.backups')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-7">
                                                <div class="panel-body">


                                                    <!-- time Picker -->
                                                    <div class="bootstrap-timepicker">
                                                        <div class="form-group">
                                                            <label>@lang('app.dailyCreationTime')</label>

                                                            <div class="input-group">
                                                                <input type="text" name="timebackup" id="hrsbackup"
                                                                       readonly class="form-control timepicker2">

                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </div>
                                                            </div>
                                                            <!-- /.input group -->
                                                        </div>
                                                        <!-- /.form group -->
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="backups"
                                                               class="col-sm-9 control-label">@lang('app.backups') @lang('app.automatic')</label>
                                                        <div class="col-sm-6">
                                                            <label><input id="backups"
                                                                          class="ace ace-switch ace-switch-6"
                                                                          type="checkbox"/>
                                                                <span class="lbl"></span>
                                                            </label>
                                                        </div>
                                                    </div>


                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-8" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-refresh bigger-130"></i>
                                                    &nbsp; @lang('app.routerSync')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-8">
                                                <div class="panel-body">
                                                    <!-- time Picker -->
                                                    <div class="form-group">
                                                        <label for="phonecode"
                                                               class="col-sm-3 control-label">@lang('app.selectIntervel')</label>
                                                        <div class="col-sm-8">
                                                            <select class="form-control" name="router-interval"
                                                                    style="width: 100%" id="router-interval">
                                                                <option value="1"
                                                                        @if($global->router_interval == 1) selected @endif>@lang('app.every')
                                                                    1 Hour
                                                                </option>
                                                                <option value="2"
                                                                        @if($global->router_interval == 2) selected @endif>@lang('app.every')
                                                                    2 Hour
                                                                </option>
                                                                <option value="3"
                                                                        @if($global->router_interval == 3) selected @endif>@lang('app.every')
                                                                    3 Hour
                                                                </option>
                                                                <option value="4"
                                                                        @if($global->router_interval == 4) selected @endif>@lang('app.every')
                                                                    4 Hour
                                                                </option>
                                                                <option value="5"
                                                                        @if($global->router_interval == 5) selected @endif>@lang('app.every')
                                                                    5 Hour
                                                                </option>
                                                                <option value="6"
                                                                        @if($global->router_interval == 6) selected @endif>@lang('app.every')
                                                                    6 Hour
                                                                </option>
                                                                <option value="7"
                                                                        @if($global->router_interval == 7) selected @endif>@lang('app.every')
                                                                    7 Hour
                                                                </option>

                                                            </select>
                                                        </div>

                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="faq-tab-2" class="tab-pane fade">
                                    <h4 class="title_con">
                                        <i class="ace-icon la la-cog bigger-120"></i>
                                        @lang('app.system')
                                    </h4>
                                    <div class="space-8"></div>
                                    <div id="faq-list-2" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-2-1" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-info-circle bigger-130"></i>
                                                    &nbsp; @lang('app.systemInformation')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-2-1">
                                                <div class="panel-body">

                                                    <ul class="list-unstyled spaced">
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.platform'):<b
                                                                    class="green"> <?php echo php_uname(); ?> </b>
                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.database'): <b
                                                                    class="green"> <?php echo mysqli_get_client_info(); ?> </b>
                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.version') PHP: <b
                                                                    class="green"> <?php echo phpversion(); ?> </b>
                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            Web Server: <b class="green">

                                                            </b>

                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.coreDirectory'): <b
                                                                    class="green"> <?php echo base_path(); ?> </b>

                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.homeDirectory'): <b
                                                                    class="green"> <?php echo public_path(); ?> </b>

                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.browser'): <b
                                                                    class="green"> <?php echo $_SERVER ['HTTP_USER_AGENT']; ?></b>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.author'): <b class="green">Smartisp</b>
                                                        </li>
                                                        <li>
                                                            <i class="ace-icon fa fa-caret-right blue"></i>
                                                            @lang('app.authorWebsite'): <a href="http://Smartisp.us"
                                                                                           target="_blank">http://Smartisp.us</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-4" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-envelope-o bigger-130"></i>
                                                    &nbsp; Email SMTP principal
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-1-4">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-horizontal" id="smtpform">
                                                            <div class="form-group">
                                                                <label for="smtpserver"
                                                                       class="col-sm-2 control-label">@lang('app.server')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control"
                                                                           name="server" id="smtpserver" maxlength="30"
                                                                           placeholder="smtp.gmail.com">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="email"
                                                                       class="col-sm-2 control-label">@lang('app.email')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="email" class="form-control"
                                                                           name="email" id="email"
                                                                           placeholder="tuempresa@gmail.com"
                                                                           maxlength="60">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="password"
                                                                       class="col-sm-2 control-label">@lang('app.password')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="password" class="form-control"
                                                                           name="pass" id="password" maxlength="50">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="protocol"
                                                                       class="col-sm-2 control-label">@lang('app.protocol')</label>
                                                                <div class="col-sm-10">
                                                                    <select class="form-control" name="protocol"
                                                                            id="protocol">
                                                                        <option value="tls" selected>TLS</option>
                                                                        <option value="ssl">SSL</option>
                                                                        <option value="">@lang('app.none')</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="port"
                                                                       class="col-sm-2 control-label">@lang('app.port')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="port"
                                                                           id="port" placeholder="587" maxlength="5">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <button type="button" class="btn btn-primary btn-sm"
                                                                            id="btnsavesmtp">@lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-14" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-envelope bigger-130"></i>
                                                    &nbsp; @lang('app.emailNotificationsTickets')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-1-14">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-horizontal">
                                                            <div class="form-group">

                                                                <div class="col-sm-10">
                                                                    <p>@lang('app.emailThatReceivesNotifications')</p>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="emailtickets"
                                                                       class="col-sm-2 control-label">@lang('app.email')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="email" class="form-control"
                                                                           id="emailtickets"
                                                                           placeholder="soporte@gmail.com"
                                                                           maxlength="60">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <button type="button" class="btn btn-primary btn-sm"
                                                                            id="btnsavesemailticket">@lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        {{-- <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-11" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-compass bigger-130"></i>
                                                    &nbsp; Zona horaria
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-11">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <label class="sr-only" for="exampleInputPassword3">Password</label>
                                                                <select class="form-control" name="time-zone" id="zone">
                                                                    <option value="America/Caracas">America/Caracas</option>
                                                                    <option value="America/Buenos_Aires">America/Buenos_Aires</option>
                                                                    <option value="America/Los_Angeles">America/Los_Angeles</option>
                                                                    <option value="America/Sao_Paulo">America/Sao_Paulo</option>
                                                                    <option value="America/Toronto">America/Toronto</option>
                                                                    <option value="America/Santa_Isabel">America/Santa_Isabel</option>
                                                                    <option value="America/Dominica">America/Dominica</option>
                                                                    <option value="America/Monterrey">America/Monterrey</option>
                                                                    <option value="America/New_York">America/New_York</option>
                                                                    <option value="America/Costa_Rica">America/Costa_Rica</option>
                                                                    <option value="America/La_Paz">America/La_Paz</option>
                                                                    <option value="America/Phoenix">America/Phoenix</option>
                                                                    <option value="America/Santiago">America/Santiago</option>
                                                                    <option value="America/Mexico_City">America/Mexico_City</option>
                                                                    <option value="America/Lima">America/Lima</option>
                                                                    <option value="America/Guatemala">America/Guatemala</option>
                                                                    <option value="America/Panama">America/Panama</option>
                                                                    <option value="America/Managua">America/Managua</option>
                                                                    <option value="America/Guayaquil">America/Guayaquil</option>
                                                                    <option value="America/Porto_Velho">America/Porto_Velho</option>
                                                                    <option value="America/Bogota">America/Bogota</option>
                                                                    <option value="Europe/Madrid">Europe/Madrid</option>
                                                                    <option value="Europe/Moscow">Europe/Moscow</option>
                                                                    <option value="Europe/Paris">Europe/Paris</option>
                                                                    <option value="Indian/Chagos">Indian/Chagos</option>
                                                                    <option value="Indian/Maldives">Indian/Maldives</option>
                                                                    <option value="Indian/Antananarivo">Indian/Antananarivo</option>
                                                                    <option value="Asia/Singapore">Asia/Singapore</option>
                                                                    <option value="Asia/Taipei">Asia/Taipei</option>
                                                                    <option value="Asia/Tokyo">Asia/Tokyo</option>
                                                                    <option value="Africa/Niamey">Africa/Niamey</option>
                                                                    <option value="Africa/Dakar">Africa/Dakar</option>
                                                                    <option value="Africa/Cairo">Africa/Cairo</option>
                                                                    <option value="Africa/Luanda">Africa/Luanda</option>
                                                                    <option value="UTC">UTC</option>
                                                                </select>
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm" id="btnsavezone"> Guardar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-12" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-map bigger-130"></i>
                                                    &nbsp;@lang('app.googleMapsDefaulLocation')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-12">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-horizontal">


                                                            <div class="form-group">

                                                                <div class="col-sm-5">
                                                                    <input type="text" class="form-control"
                                                                           id="locationdefault"
                                                                           placeholder="-0.1806532,-78.46783820000002">
                                                                </div>
                                                                @if($map!='0')
                                                                    <div class="col-sm-1">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-danger"
                                                                                id="openmap" data-toggle="modal"
                                                                                data-target="#modalmapedit"
                                                                                title="@lang('app.open') Mapa"><i
                                                                                    class="fa fa-map"></i></button>
                                                                    </div>
                                                                @endif

                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-5">
                                                                    <button type="button" class="btn btn-primary btn-sm"
                                                                            id="btnsavedefaultmap">@lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-8" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-bug bigger-130"></i>
                                                    &nbsp; @lang('app.debugMode')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-8">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <label for="debug"
                                                                       class="col-sm-7 control-label">@lang('app.debugMode')</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="debug"
                                                                                  class="ace ace-switch ace-switch-6"
                                                                                  type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-10" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-eraser bigger-130"></i>
                                                    &nbsp; @lang('app.systemCache')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-10">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <label for="cache"
                                                                       class="col-sm-7 control-label">@lang('app.clearCache')</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="cache"
                                                                                  class="ace ace-switch ace-switch-6"
                                                                                  type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-9" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-file-image-o bigger-130"></i>
                                                    &nbsp; @lang('app.loginLogo')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-9">
                                                <div class="panel-body">
                                                    <div class="col-xs-12">
                                                        <form id="logoform" method="post" enctype="multipart/form-data">
                                                            <div class="form-group">
                                                                <label for="cache"
                                                                       class="col-xs-1 control-label">@lang('app.uploadImage')</label>
                                                                <div class="col-xs-5">
                                                                    <input type="file" class="form-control" name="file"
                                                                           id="file">
                                                                    <p>@lang('app.imageWithExtension').</p>
                                                                    <button type="submit" class="btn btn-primary btn-sm"
                                                                            id="btnsaveimg"> @lang('app.save')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-2-3" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-exclamation-triangle bigger-130"></i>
                                                    &nbsp; @lang('app.resetValues')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-2-3">
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <b class="red"><i
                                                                    class="ace-icon fa fa-exclamation-triangle bigger-120"></i> @lang('app.cautionRestoring')
                                                            .</b>
                                                    </div>
                                                    <div class="col-xs-6"><br>
                                                        <form class="form-inline">
                                                            {{-- <div class="form-group">
                                                                <label for="ressys" class="col-sm-8 control-label">@lang('app.resetSystem')</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="ressys" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="respay" class="col-sm-8 control-label">@lang('app.ResetPayments')</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="respay" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div> --}}
                                                            <div class="form-group">
                                                                <label for="reslog"
                                                                       class="col-sm-8 control-label">@lang('app.ResetLogs')</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="reslog"
                                                                                  class="ace ace-switch ace-switch-6"
                                                                                  type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="faq-tab-6" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon la la-commenting bigger-120"></i>
                                        SMS
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-6" class="panel-group accordion-style1 accordion-style2">

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-6-1" data-parent="#faq-list-6" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                                    &nbsp; Twilio SMS
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-6-1">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formsmsgateway">


                                                        <div class="form-group">
                                                            <label for="smsemail" class="col-sm-1 control-label">Account
                                                                Sid</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" name="token" class="form-control"
                                                                       value="@if($twsms['options']['t']){{$twsms['options']['t']}}@endif"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="deviceid" class="col-sm-1 control-label">Auth
                                                                Token</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control" name="deviceid"
                                                                       value="@if($twsms['options']['d']){{$twsms['options']['d']}}@endif"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="deviceid" class="col-sm-1 control-label">From
                                                                Number</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control" name="twinumber"
                                                                       value="@if($twsms['options']['n']){{$twsms['options']['n']}}@endif"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">

                                                            <label for="smsg"
                                                                   class="col-sm-1 control-label">Activar</label>
                                                            <div class="col-sm-11">

                                                                <label>
                                                                    <input name="enabledsmsg"
                                                                           class="ace ace-switch ace-switch-6"
                                                                           type="checkbox"
                                                                           @if(($twsms['options']['e'])=='1')checked @endif />
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">

                                                            <div class="col-sm-8 control-label"></div>

                                                            <div class="col-sm-4">
                                                                <a target="_blanck"
                                                                   href="https://www.twilio.com/try-twilio?promo=qqU4Zd">Crear
                                                                    AQUI... Una Cuenta en Twilio</a>
                                                            </div>

                                                        </div>

                                                        <div class="form-group">

                                                            <div class="col-sm-12">
                                                                <label for="btnsmsmgateway"
                                                                       class="col-sm-1 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnsmsmgateway"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-6-2" data-parent="#faq-list-6" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                                    &nbsp; Twilio Whatsapp SMS
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-6-2">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formwhatsapp">


                                                        <div class="form-group">
                                                            <label for="smsemail" class="col-sm-1 control-label">Account
                                                                Sid</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" name="wappsid" class="form-control"
                                                                       value="@if($twsmsarr['options']['t']){{$twsmsarr['options']['t']}}@endif"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="deviceid" class="col-sm-1 control-label">Auth
                                                                Token</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control" name="wapptoken"
                                                                       value="@if($twsmsarr['options']['d']){{$twsmsarr['options']['d']}}@endif"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="deviceid" class="col-sm-1 control-label">From
                                                                Number</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control"
                                                                       name="wappnumber"
                                                                       value="@if($twsmsarr['options']['n']){{$twsmsarr['options']['n']}}@endif"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="smsg"
                                                                   class="col-sm-1 control-label">Activar</label>
                                                            <div class="col-sm-11">
                                                                <label>

                                                                    <input name="enabledsmsg"
                                                                           class="ace ace-switch ace-switch-6"
                                                                           type="checkbox"
                                                                           @if(($twsmsarr['options']['e'])=='1')checked @endif />
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>


                                                        <div class="form-group">

                                                            <div class="col-sm-8 control-label"></div>

                                                            <div class="col-sm-4">
                                                                <a target="_blanck"
                                                                   href="https://www.twilio.com/try-twilio?promo=qqU4Zd">Crear
                                                                    AQUI... Una Cuenta en Twilio</a>
                                                            </div>

                                                        </div>
                                                        <div class="form-group">

                                                            <div class="col-sm-12">
                                                                <label for="btnformwhatsapp"
                                                                       class="col-sm-1 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnformwhatsapp"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-6-3" data-parent="#faq-list-6" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                                    &nbsp; @lang('app.configuration') @lang('app.additional') SMS
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-6-3">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formsmsgeneral">
                                                        <div class="form-group">
                                                            <label for="phonecode"
                                                                   class="col-sm-1 control-label">@lang('app.countryCode')</label>
                                                            <div class="col-sm-10">
                                                                <select class="form-control" name="phonecode"
                                                                        style="width: 100%" id="phonecode">
                                                                    <option value="213">Algeria (+213)</option>
                                                                    <option value="49">Alemania (+49)</option>
                                                                    <option value="376">Andorra (+376)</option>
                                                                    <option value="244">Angola (+244)</option>
                                                                    <option value="1264">Anguilla (+1264)</option>
                                                                    <option value="1268">Antigua &amp; Barbuda (+1268)
                                                                    </option>
                                                                    <option value="54" selected>Argentina (+54)</option>
                                                                    <option value="374">Armenia (+374)</option>
                                                                    <option value="297">Aruba (+297)</option>
                                                                    <option value="61">Australia (+61)</option>
                                                                    <option value="43">Austria (+43)</option>
                                                                    <option value="994">Azerbaijan (+994)</option>
                                                                    <option value="1242">Bahamas (+1242)</option>
                                                                    <option value="591">Bolivia (+591)</option>
                                                                    <option value="55">Brazil (+55)</option>
                                                                    <option value="1">Canada (+1)</option>
                                                                    <option value="238">Islas de Cabo Verde (+238)
                                                                    </option>
                                                                    <option value="1345">Islas Caimán (+1345)</option>
                                                                    <option value="236">República Centroafricana
                                                                        (+236)
                                                                    </option>
                                                                    <option value="56">Chile (+56)</option>
                                                                    <option value="57">Colombia (+57)</option>
                                                                    <option value="269">Comoros (+269)</option>
                                                                    <option value="242">Congo (+242)</option>
                                                                    <option value="506">Costa Rica (+506)</option>
                                                                    <option value="53">Cuba (+53)</option>
                                                                    <option value="42">República Checa (+42)</option>
                                                                    <option value="45">Dinamarca (+45)</option>
                                                                    <option value="1809">Dominica (+1809)</option>
                                                                    <option value="1809">República Dominicana (+1809)
                                                                    </option>
                                                                    <option value="593">Ecuador (+593)</option>
                                                                    <option value="503">El Salvador (+503)</option>
                                                                    <option value="240">Guinea Ecuatorial (+240)
                                                                    </option>
                                                                    <option value="500">Islas Malvinas (+500)</option>
                                                                    <option value="33">Francia (+33)</option>
                                                                    <option value="594">Guayana Francesa (+594)</option>
                                                                    <option value="233">Ghana (+233)</option>
                                                                    <option value="30">Grecia (+30)</option>
                                                                    <option value="1473">Granada (+1473)</option>
                                                                    <option value="590">Guadalupe (+590)</option>
                                                                    <option value="502">Guatemala (+502)</option>
                                                                    <option value="592">Guyana (+592)</option>
                                                                    <option value="509">Haiti (+509)</option>
                                                                    <option value="504">Honduras (+504)</option>
                                                                    <option value="852">Hong Kong (+852)</option>
                                                                    <option value="354">Islandia (+354)</option>
                                                                    <option value="91">India (+91)</option>
                                                                    <option value="62">Indonesia (+62)</option>
                                                                    <option value="98">Iran (+98)</option>
                                                                    <option value="964">Iraq (+964)</option>
                                                                    <option value="972">Israel (+972)</option>
                                                                    <option value="39">Italia (+39)</option>
                                                                    <option value="1876">Jamaica (+1876)</option>
                                                                    <option value="81">Japón (+81)</option>
                                                                    <option value="962">Jordan (+962)</option>
                                                                    <option value="7">Kazakhstan (+7)</option>
                                                                    <option value="254">Kenya (+254)</option>
                                                                    <option value="850">Corea del Norte (+850)</option>
                                                                    <option value="82">Corea del Sur (+82)</option>
                                                                    <option value="965">Kuwait (+965)</option>
                                                                    <option value="996">Kyrgyzstan (+996)</option>
                                                                    <option value="856">Laos (+856)</option>
                                                                    <option value="371">Latvia (+371)</option>
                                                                    <option value="352">Luxembourgo (+352)</option>
                                                                    <option value="853">Macao (+853)</option>
                                                                    <option value="389">Macedonia (+389)</option>
                                                                    <option value="261">Madagascar (+261)</option>
                                                                    <option value="265">Malawi (+265)</option>
                                                                    <option value="60">Malasia (+60)</option>
                                                                    <option value="223">Mali (+223)</option>
                                                                    <option value="356">Malta (+356)</option>
                                                                    <option value="52">Mexico (+52)</option>
                                                                    <option value="691">Micronesia (+691)</option>
                                                                    <option value="377">Monaco (+377)</option>
                                                                    <option value="976">Mongolia (+976)</option>
                                                                    <option value="258">Mozambique (+258)</option>
                                                                    <option value="977">Nepal (+977)</option>
                                                                    <option value="31">Países Bajos (+31)</option>
                                                                    <option value="64">Nueva Zelanda (+64)</option>
                                                                    <option value="505">Nicaragua (+505)</option>
                                                                    <option value="507">Panama (+507)</option>
                                                                    <option value="675">Papúa Nueva Guinea (+675)
                                                                    </option>
                                                                    <option value="595">Paraguay (+595)</option>
                                                                    <option value="51">Peru (+51)</option>
                                                                    <option value="63">Filipinas (+63)</option>
                                                                    <option value="48">Polonia (+48)</option>
                                                                    <option value="351">Portugal (+351)</option>
                                                                    <option value="1787">Puerto Rico (+1787)</option>
                                                                    <option value="974">Qatar (+974)</option>
                                                                    <option value="40">Rumania (+40)</option>
                                                                    <option value="7">Rusia (+7)</option>
                                                                    <option value="378">San Marino (+378)</option>
                                                                    <option value="239">Sao Tome &amp; Principe (+239)
                                                                    </option>
                                                                    <option value="221">Senegal (+221)</option>
                                                                    <option value="232">Sierra Leona (+232)</option>
                                                                    <option value="65">Singapur (+65)</option>
                                                                    <option value="27">Sud Africa (+27)</option>
                                                                    <option value="34">España (+34)</option>
                                                                    <option value="1868">Trinidad &amp; Tobago (+1868)
                                                                    </option>
                                                                    <option value="598">Uruguay (+598)</option>
                                                                    <option value="379">Ciudad del Vaticano (+379)
                                                                    </option>
                                                                    <option value="58">Venezuela (+58)</option>

                                                                </select>
                                                            </div>

                                                        </div>

                                                        <div class="form-group">
                                                            <label for="delaysend"
                                                                   class="col-sm-1 control-label">@lang('app.Pausebetweenmessages')</label>
                                                            <div class="col-sm-10">
                                                                <input type="number" class="form-control"
                                                                       name="delaysend" id="delaysend">
                                                                <span id="helpBlock" class="help-block">@lang('app.Valueexpressedinseconds').</span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">

                                                            <div class="col-sm-12">
                                                                <label for="btnsmsgeneral"
                                                                       class="col-sm-1 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnsmsgeneral">@lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
<!--  Telegram Section Starts here -->
										<div class="panel panel-default">
											<div class="panel-heading">
												<a href="#faq-6-4" data-parent="#faq-list-6" data-toggle="collapse" class="accordion-toggle collapsed">
													<i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i><i class="fa fa-telegram" aria-hidden="true"></i>&nbsp; Waboxapp Sms</a>
											</div>
											<div class="panel-collapse collapse" id="faq-6-4">
												<div class="panel-body">
													<form class="form-horizontal" id="formweboxapp">
														<div class="form-group">
															<label for="smsemail" class="col-sm-1 control-label">Token</label>
															<div class="col-sm-10">
																<input type="text" name="smsweboxtokenid" class="form-control" value="@if($weboxapp['options']['t']){{$weboxapp['options']['t']}}@endif"/>
															</div>
														</div>
														<div class="form-group">
															<label for="smsuid" class="col-sm-1 control-label">Uid</label>
															<div class="col-sm-10">
																<input type="text" name="weboxuid" class="form-control" value="@if($weboxapp['options']['d']){{$weboxapp['options']['d']}}@endif"/>
															</div>
														</div>

														<div class="form-group">
															<label for="smsg" class="col-sm-1 control-label">Activar</label>
															<div class="col-sm-11">
																<label>
																	<input name="enabledsmsg" class="ace ace-switch ace-switch-6" type="checkbox" @if(($weboxapp['options']['e'])=='1')checked @endif /><span class="lbl"></span>
																</label>
															</div>
														</div>
														<div class="form-group">
															<div class="col-sm-8 control-label"></div>
																<div class="col-sm-4">
																	<a target="_blanck" href="https://www.waboxapp.com/">Crear AQUI... Una Cuenta en Waboxapp</a>
																</div>
														</div>
														<div class="form-group">
															<div class="col-sm-12">
																<label for="btnformtelegram" class="col-sm-1 control-label"></label>
																<button type="button" class="btn btn-primary btn-sm" id="btnformweboxapp"> @lang('app.save')</button>
															</div>
														</div>
													</form>
												</div>
											</div>
										</div>
<!-- Telegram section ends here  -->
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-6-5" data-parent="#faq-list-6" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                                    &nbsp;Whatsapp Cloud API
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-6-5">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formwhatsappcloudapi">
                                                        <div class="form-group">
                                                            <label for="phonenumberid" class="col-sm-1 control-label">Phone Number ID</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" name="phonenumberid" class="form-control"
                                                                       value="{{ $whatsappcloudapi->phonenumberid ?? '' }}"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="deviceid" class="col-sm-1 control-label">Access
                                                                Token</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control" name="access_token"
                                                                       value="{{ $whatsappcloudapi->access_token ?? '' }}"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="deviceid" class="col-sm-1 control-label">Business Account ID</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control" name="business_account_id"
                                                                       value="{{ $whatsappcloudapi->business_account_id ?? '' }}"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="smsg"
                                                                   class="col-sm-1 control-label">Activar</label>
                                                            <div class="col-sm-11">
                                                                <label>

                                                                    <input name="enabledsmsg"
                                                                           class="ace ace-switch ace-switch-6"
                                                                           type="checkbox"
                                                                           {{ !empty($whatsappcloudapi->status) ? 'checked' : '' }} />
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>


                                                        <!-- <div class="form-group">

                                                            <div class="col-sm-8 control-label"></div>

                                                            <div class="col-sm-4">
                                                                <a target="_blanck"
                                                                   href="https://www.twilio.com/try-twilio?promo=qqU4Zd">Crear
                                                                    AQUI... Una Cuenta en Twilio</a>
                                                            </div>

                                                        </div> -->
                                                        <div class="form-group">

                                                            <div class="col-sm-12">
                                                                <label for="btnformwhatsappcloudapi"
                                                                       class="col-sm-1 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnformwhatsappcloudapi"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="faq-tab-7" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon la la-money bigger-120"></i>
                                        PAGO
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-7" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-7-1" data-parent="#faq-list-7" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>


                                                    <i class="ace-icon fa fa-cc-paypal bigger-130"></i>
                                                    &nbsp; PayPal
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-7-1">
                                                <div class="panel-body">

                                                    <form class="form-horizontal" id="form-paypal">
                                                        <div class="form-group">
                                                            <label for="paypal_client_id"
                                                                   class="col-sm-2 control-label">Client ID</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="paypal_client_id" id="paypal_client_id"
                                                                       value="{{ $paypal_client_id ? $paypal_client_id : '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="paypal_secret" class="col-sm-2 control-label">Secret</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="paypal_secret" id="paypal_secret"
                                                                       value="{{ $paypal_secret ? $paypal_secret : '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="paypal_mode" class="col-sm-2 control-label">Paypal
                                                                Mode</label>
                                                            <div class="col-sm-4">
                                                                <select class="form-control" name="paypal_mode"
                                                                        id="paypal_mode">
                                                                    <option value="sandbox" {{ $paypal_mode === 'sandbox' ? 'selected' : '' }}>
                                                                        Sandbox
                                                                    </option>
                                                                    <option value="live" {{ $paypal_mode === 'live' ? 'selected' : '' }}>
                                                                        Live
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="mkdeug"
                                                                   class="col-sm-2 control-label">@lang('app.enableDisable')</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="paypal_status" name="paypal_status" value="1"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              @if($paypal_status == "1") checked @endif
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnpaypal"
                                                                       class="col-sm-2 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnpaypal"> @lang('app.save')</button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-7-2" data-parent="#faq-list-7" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-cc-stripe bigger-130"></i>
                                                    &nbsp; Stripe
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-7-2">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="form-stripe">


                                                        <div class="form-group">
                                                            <label for="stripe_key" class="col-sm-2 control-label">Stripe
                                                                Key</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="stripe_key" id="stripe_key"
                                                                       value="{{ $stripe_key ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="stripe_secret" class="col-sm-2 control-label">Stripe
                                                                Secret</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="stripe_secret" id="stripe_secret"
                                                                       value="{{ $stripe_secret ?? '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="mkdeug"
                                                                   class="col-sm-2 control-label">@lang('app.enableDisable')</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="stripe_status" name="stripe_status" value="1"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              @if($stripe_status == "1") checked @endif
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnstripe"
                                                                       class="col-sm-2 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnstripe"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-7-3" data-parent="#faq-list-7" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-cc-visa bigger-130"></i>
                                                    &nbsp; PayU
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-7-3">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="form-payu">

                                                        <div class="form-group">
                                                            <label for="stripe_key" class="col-sm-2 control-label">Merchant
                                                                ID</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="payu_merchant_id" id="payu_merchant_id"
                                                                       value="{{ $payu_merchant_id ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="stripe_secret" class="col-sm-2 control-label">Account
                                                                ID</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="payu_account_id" id="payu_account_id"
                                                                       value="{{ $payu_account_id ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="stripe_secret" class="col-sm-2 control-label">Api
                                                                Key</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="payu_api_key" id="payu_api_key"
                                                                       value="{{ $payu_api_key ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="paypal_mode" class="col-sm-2 control-label">Payu
                                                                Mode</label>
                                                            <div class="col-sm-4">
                                                                <select class="form-control" name="payu_mode"
                                                                        id="payu_mode">
                                                                    <option value="sandbox" {{ $payu_mode === 'sandbox' ? 'selected' : '' }}>
                                                                        Sandbox
                                                                    </option>
                                                                    <option value="live" {{ $payu_mode === 'live' ? 'selected' : '' }}>
                                                                        Live
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="mkdeug"
                                                                   class="col-sm-2 control-label">@lang('app.enableDisable')</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="payu_status" name="payu_status" value="1"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              @if($payu_status == "1") checked @endif
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnpayu"
                                                                       class="col-sm-2 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnpayu"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-7-4" data-parent="#faq-list-7" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-cc-visa bigger-130"></i>
                                                    &nbsp; DLocal
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-7-4">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="form-directo-pago">

                                                        <div class="form-group">
                                                            <label for="stripe_key" class="col-sm-2 control-label">API Key</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="directo_pago_api_key" id="directo_pago_api_key"
                                                                       value="{{ $directo_pago_api_key ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="stripe_secret" class="col-sm-2 control-label">Secret Key</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="directo_pago_secret_key" id="directo_pago_secret_key"
                                                                       value="{{ $directo_pago_secret_key ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="paypal_mode" class="col-sm-2 control-label">DLocal
                                                                Mode</label>
                                                            <div class="col-sm-4">
                                                                <select class="form-control" name="directo_pago_mode"
                                                                        id="directo_pago_mode">
                                                                    <option value="sandbox" {{ $directo_pago_mode === 'sandbox' ? 'selected' : '' }}>
                                                                        Sandbox
                                                                    </option>
                                                                    <option value="live" {{ $directo_pago_mode === 'live' ? 'selected' : '' }}>
                                                                        Live
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="mkdeug"
                                                                   class="col-sm-2 control-label">@lang('app.enableDisable')</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="directo_pago_status" name="directo_pago_status" value="1"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              @if($directo_pago_status == "1") checked @endif
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnpayu"
                                                                       class="col-sm-2 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnDirectoPago"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-7-5" data-parent="#faq-list-7" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-cc-visa bigger-130"></i>
                                                    &nbsp; Payvalida
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-7-5">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="form-pay-valida">

                                                        <div class="form-group">
                                                            <label for="pay_valida_merchant_id" class="col-sm-2 control-label">Merchant ID</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="pay_valida_merchant_id" id="pay_valida_merchant_id"
                                                                       value="{{ $pay_valida_merchant_id ?? '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="pay_valida_fixed_hash" class="col-sm-2 control-label">Fixed Hash</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="pay_valida_fixed_hash" id="pay_valida_fixed_hash"
                                                                       value="{{ $pay_valida_fixed_hash ?? '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="pay_valida_fixed_hash_notification" class="col-sm-2 control-label">Fixed Hash Notification</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text"
                                                                       name="pay_valida_fixed_hash_notification" id="pay_valida_fixed_hash_notification"
                                                                       value="{{ $pay_valida_fixed_hash_notification ?? '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="pay_valida_mode" class="col-sm-2 control-label">Payvalida
                                                                Mode</label>
                                                            <div class="col-sm-4">
                                                                <select class="form-control" name="pay_valida_mode"
                                                                        id="pay_valida_mode">
                                                                    <option value="sandbox" {{ $pay_valida_mode === 'sandbox' ? 'selected' : '' }}>
                                                                        Sandbox
                                                                    </option>
                                                                    <option value="live" {{ $pay_valida_mode === 'live' ? 'selected' : '' }}>
                                                                        Live
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="pay_valida_status"
                                                                   class="col-sm-2 control-label">@lang('app.enableDisable')</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="pay_valida_status" name="pay_valida_status" value="1"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              @if($pay_valida_status == "1") checked @endif
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnPayValida"
                                                                       class="col-sm-2 control-label"></label>
                                                                <button type="submit" class="btn btn-primary btn-sm"
                                                                        id="btnPayValida"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                {{-- SECTION INVOICING --}}
                                <div id="faq-tab-8" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon la la-money bigger-120"></i>
                                        @lang('app.electronicBilling')
                                    </h4>
                                    <div class="space-8"></div>
                                    {{-- TABS HEADERS --}}
                                    <div class="tabs-invoice-headers">
                                        <ul class="nav nav-tabs padding-18 tab-size-bigger" role="tablist" id="tabinvoices">

                                            <li class="active"><a href="#invoice-ec" class="text-center" data-toggle="tab" aria-selected="true"><img src="{{asset('assets/flags/ec.png')}}"><br /> @lang('app.invoiceEc')</a></li>
                                            <li><a href="#invoice-co" class="text-center" data-toggle="tab" aria-selected="false"><img src="{{asset('assets/flags/co.png')}}"><br /> @lang('app.invoiceCo')</a></li>
                                            <li><a href="#invoice-mx" class="text-center" data-toggle="tab" aria-selected="false"><img src="{{asset('assets/flags/mx.png')}}"><br /> @lang('app.invoiceMx')</a></li>
                                            <li><a href="#invoice-ven" class="text-center" data-toggle="tab" aria-selected="false"><img src="{{asset('assets/flags/ven.jpg')}}"><br /> @lang('app.invoiceVenezuala')</a></li>
                                        </ul>

                                    </div>
                                    {{-- END TAB HEADER --}}

                                    {{-- TABS CONTENT --}}
                                    <div class="tabs-invoice-content tab-content">
                                        <div id="invoice-ec" class="tab-pane fade in active" role="tabpanel">
                                            <div class="accordion">

                                                <p>
                                                    <a class="btn btn-outline-primary" style="background: #fff !important;color: #000 !important;border-width: 1px !important;" data-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"><i class="blue ace-icon fa fa-file-image-o bigger-120"></i>
                                                        &nbsp; @lang('app.electronicBillingLogo')</a>
                                                    <button class="btn btn-outline-primary" style="background: #fff !important;color: #000 !important;border-width: 1px !important;" type="button" data-toggle="collapse" data-target="#multiCollapseExample2" aria-expanded="false" aria-controls="multiCollapseExample2"><i class="menu-icon ace-icon la la-cog bigger-120"></i>
                                                        &nbsp; @lang('app.DIGITALSIGNATURE')</button>
                                                  </p>
                                                  <div class="row">
                                                    <div class="col">
                                                      <div class="collapse multi-collapse" id="multiCollapseExample1">
                                                        <div class="card card-body" style="overflow: auto;">
                                                            <div class="col-xs-12">
                                                                <form id="logoform_f" method="post"
                                                                        enctype="multipart/form-data">
                                                                    <div class="form-group">
                                                                        <label for="cache"
                                                                                class="col-xs-1 control-label">@lang('app.uploadImage')</label>
                                                                        <div class="col-xs-5">
                                                                            <input type="file" class="form-control" name="file"
                                                                                    id="file">
                                                                            <p>@lang('app.maximumMustBeExtended').</p>
                                                                            <button type="submit" class="btn btn-primary btn-sm"
                                                                                    id="btnsaveimg"> @lang('app.save')</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <div class="col">
                                                      <div class="collapse multi-collapse" id="multiCollapseExample2">
                                                        <div class="card card-body" style="overflow: auto;">
                                                            <div class="col-xs-12">
                                                                <form id="logoform2" method="post"
                                                                      enctype="multipart/form-data">
                                                                    <div class="form-group">
                                                                        <label for="cache"
                                                                               class="col-xs-12 control-label">@lang('app.uploadDigitalCertificate')</label>

                                                                        <input type="file" class="form-control"
                                                                               name="certificado_digital"
                                                                               id="certificado_digital">
                                                                        <p>@lang('app.FileWithExtensionp')</p>

                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="cache"
                                                                               class="col-xs-12 control-label">@lang('app.DigitalCertificatePassword')</label>

                                                                        <input type="text" class="form-control"
                                                                               name="pass_certificado" id="pass_certificado">

                                                                    </div>
                                                                    <div class="form-group">
                                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                                                id="logoform2"> @lang('app.save')</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>
                                            </div>






                                            <form class="form_emisor" method="post">
                                                <div id="divfeEcuador">
                                                    <div class="form-group">
                                                        <label for="cache" class="col-xs-12 control-label">Configuracion de Establecimientos</label>
                                                        <a href="{{route('establecimientos.index')}}" class="btn btn-primary">Ir Modulo de Establecimientos</a>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cache" class="col-xs-12 control-label">Configuracion de puntos de Emision</label>
                                                        <a href="{{route('ptoEmision.index')}}" class="btn btn-primary">Ir Modulo de Puntos de Emision</a>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cache" class="col-xs-12 control-label">RUC</label>
                                                        <input type="text" class="form-control" name="ruc"
                                                            id="ruc" value="{{$emisor_rut}}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="cache"
                                                            class="col-xs-12 control-label">@lang('app.businessName')</label>
                                                        <input type="text" class="form-control"
                                                            name="razonSocial" id="razonSocial"
                                                            value="{{$emisor_razonSocial}}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cache"
                                                            class="col-xs-12 control-label">@lang('app.RegimenRimpe')</label>
                                                        <select  name="regimenMicroempresas" id="regimenMicroempresas">
                                                            <option value="1" {{ $regimenMicroempresas === '1' ? 'selected' : '' }}>SI</option>
                                                            <option value="0" {{ $regimenMicroempresas === '0' ? 'selected' : '' }}>NO</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cache"
                                                            class="col-xs-12 control-label">@lang('app.agenteRetencion')</label>
                                                        <input type="number" class="form-control"
                                                            name="agenteRetencion" id="agenteRetencion" max="99999999"
                                                            value="{{$agenteRetencion}}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cache"
                                                            class="col-xs-12 control-label">@lang('app.Tradename')</label>
                                                        <input type="text" class="form-control"
                                                            name="nombreComercial" id="nombreComercial"
                                                            value="{{$emisor_nombreComercial}}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="cache"
                                                            class="col-xs-12 control-label">@lang('app.direction')</label>
                                                        <input type="text" class="form-control"
                                                            name="direccion" id="Direccion"
                                                            value="{{$emisor_direccion}}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="paypal_client_id"
                                                            class="col-sm-2 control-label">@lang('app.ForcedToKeepAccounting')</label>
                                                        <div>
                                                            <input type="radio" name="status_cont"
                                                                id="status_cont"
                                                                value="SI" {{ $status_cont === 'SI' ? 'checked' : '' }}> @lang('app.yes')
                                                            <br>
                                                            <input type="radio" name="status_cont"
                                                                id="status_cont"
                                                                value="NO" {{ $status_cont === 'NO' ? 'checked' : '' }}>
                                                            NO<br>
                                                        </div>
                                                    </div>
                                                    <div class="form-group w100">
                                                        <label for="activate_mx" class="col-lg-2 control-label">Activar Integración</label>
                                                        <label class="switch">
                                                            {{-- $status_factel 0 => Ecuador, 1 => Desactivado , 2 => Colombia, 3 => Mexico--}}
                                                            <input type="checkbox" name="activate_ec" value="activate_ec" {{ $status_factel == '0' ? 'checked': '' }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                    <div class="form-group" style="text-align: left;">
                                                        <input type="hidden" name="status_factel" value="0">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                                id="btnsaveemisor"> @lang('app.save')</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="invoice-co" class="tab-pane fade" role="tabpanel">
                                            <div class="accordion">

                                                <p>
                                                    <a class="btn btn-outline-primary" style="background: #fff !important;color: #000 !important;border-width: 1px !important;" data-toggle="collapse" href="#multiCollapseExample12" role="button" aria-expanded="false" aria-controls="multiCollapseExample12"><i class="blue ace-icon fa fa-file-image-o bigger-120"></i>
                                                        &nbsp; @lang('app.electronicBillingLogo')</a>
                                                    <button class="btn btn-outline-primary" style="background: #fff !important;color: #000 !important;border-width: 1px !important;" type="button" data-toggle="collapse" data-target="#multiCollapseExample22" aria-expanded="false" aria-controls="multiCollapseExample22"><i class="menu-icon ace-icon la la-cog bigger-120"></i>
                                                        &nbsp; @lang('app.DIGITALSIGNATURE')</button>
                                                  </p>
                                                  <div class="row">
                                                    <div class="col">
                                                      <div class="collapse multi-collapse" id="multiCollapseExample12">
                                                        <div class="card card-body" style="overflow: auto;">
                                                            <div class="col-xs-12">
                                                                <form id="logoform_f" method="post"
                                                                        enctype="multipart/form-data">
                                                                    <div class="form-group">
                                                                        <label for="cache"
                                                                                class="col-xs-1 control-label">@lang('app.uploadImage')</label>
                                                                        <div class="col-xs-5">
                                                                            <input type="file" class="form-control" name="file"
                                                                                    id="file">
                                                                            <p>@lang('app.maximumMustBeExtended').</p>
                                                                            <button type="submit" class="btn btn-primary btn-sm"
                                                                                    id="btnsaveimg"> @lang('app.save')</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <div class="col">
                                                      <div class="collapse multi-collapse" id="multiCollapseExample22">
                                                        <div class="card card-body" style="overflow: auto;">
                                                            <div class="col-xs-12">
                                                                <form id="logoform2" method="post"
                                                                      enctype="multipart/form-data">
                                                                    <div class="form-group">
                                                                        <label for="cache"
                                                                               class="col-xs-12 control-label">@lang('app.uploadDigitalCertificate')</label>

                                                                        <input type="file" class="form-control"
                                                                               name="certificado_digital"
                                                                               id="certificado_digital">
                                                                        <p>@lang('app.FileWithExtensionp')</p>

                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="cache"
                                                                               class="col-xs-12 control-label">@lang('app.DigitalCertificatePassword')</label>

                                                                        <input type="text" class="form-control"
                                                                               name="pass_certificado" id="pass_certificado">

                                                                    </div>
                                                                    <div class="form-group">
                                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                                                id="logoform2"> @lang('app.save')</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>
                                            </div>
                                            <form class="form_emisor" method="post">
                                                <div id="divfeColombia">
                                                    <div class="form-group">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                            <th colspan="3">Setting</th>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>
                                                                    <label>Ambiente de destino del
                                                                        documento</label>
                                                                    <select name="dian_settings_typeoperation_cod"
                                                                            id="dian_settings_typeoperation_cod"
                                                                            class="form-control">
                                                                        @if ($dian_settings_typeoperation_cod == '2')
                                                                            <option value="2">Pruebas
                                                                            </option>
                                                                        @else
                                                                            <option value="2" {{ $dian_settings_typeoperation_cod == '2' ? 'selected' : '' }}>
                                                                                Pruebas
                                                                            </option>
                                                                            <option value="1" {{ $dian_settings_typeoperation_cod == '1' ? 'selected' : '' }}>
                                                                                Producción
                                                                            </option>
                                                                        @endif
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label>TestSetId</label>
                                                                    <input class="form-control"
                                                                           name="dian_settings_testsetid"
                                                                           id="dian_settings_testsetid"
                                                                           value="{{$dian_settings_testsetid}}"
                                                                           maxlength="255" required>
                                                                </td>
                                                                <td>
                                                                    <label>LLave técnica</label>
                                                                    <input class="form-control"
                                                                           name="dian_settings_tecnicalkey"
                                                                           id="dian_settings_tecnicalkey"
                                                                           value="{{$dian_settings_tecnicalkey}}"
                                                                           maxlength="255" required>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="form-group">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                            <th colspan="3">Software</th>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>
                                                                    <label>Nombre</label>
                                                                    <input class="form-control"
                                                                           name="dian_settings_softwarename"
                                                                           id="dian_settings_softwarename"
                                                                           value="{{$dian_settings_softwarename}}" required>
                                                                </td>
                                                                <td>
                                                                    <label>Id</label>
                                                                    <input class="form-control"
                                                                           name="dian_settings_softwareid"
                                                                           id="dian_settings_softwareid"
                                                                           value="{{$dian_settings_softwareid}}"
                                                                           maxlength="255" required>
                                                                </td>
                                                                <td>
                                                                    <label>Pin</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_softwarepin"
                                                                           id="dian_settings_softwarepin"
                                                                           value="{{$dian_settings_softwarepin}}" required>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="form-group">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                            <th colspan="4">Resolución</th>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>
                                                                    <label>Fecha Resolución</label>
                                                                    <input type="date" class="form-control"
                                                                           name="dian_settings_resolutiondate"
                                                                           id="dian_settings_resolutiondate"
                                                                           value="{{$dian_settings_resolutiondate}}">
                                                                </td>
                                                                <td>
                                                                    <label>Fecha Inicio</label>
                                                                    <input type="date" class="form-control"
                                                                           name="dian_settings_resolutiondatestar"
                                                                           id="dian_settings_resolutiondatestar"
                                                                           value="{{$dian_settings_resolutiondatestar}}">
                                                                </td>
                                                                <td>
                                                                    <label>Fecha Fin</label>
                                                                    <input type="date" class="form-control"
                                                                           name="dian_settings_resolutiondateend"
                                                                           id="dian_settings_resolutiondateend"
                                                                           value="{{$dian_settings_resolutiondateend}}">
                                                                </td>
                                                                <td>
                                                                    <label>N° Resolución</label>
                                                                    <input class="form-control"
                                                                           name="dian_settings_resolutionnumber"
                                                                           id="dian_settings_resolutionnumber"
                                                                           value="{{$dian_settings_resolutionnumber}}"
                                                                           maxlength="255"required>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <label>Prefijo Factura</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_prefix"
                                                                           id="dian_settings_prefix"
                                                                           value="{{$dian_settings_prefix}}"
                                                                           maxlength="10">
                                                                </td>
                                                                <td>
                                                                    <label>Inicio de Numeración</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_numberstart"
                                                                           id="dian_settings_numberstart"
                                                                           value="{{$dian_settings_numberstart}}"
                                                                           min="0" max="999999999999999" required>
                                                                </td>
                                                                <td>
                                                                    <label>Fin de Numeración</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_numberend"
                                                                           id="dian_settings_numberend"
                                                                           value="{{$dian_settings_numberend}}"
                                                                           min="0" max="999999999999999" required>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <label>Prefijo Nota Crédito</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_prefixnc"
                                                                           id="dian_settings_prefixnc"
                                                                           value="{{$dian_settings_prefixnc}}"
                                                                           maxlength="10">
                                                                </td>
                                                                <td>
                                                                    <label>Inicio de Numeración</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_numberstartnc"
                                                                           id="dian_settings_numberstartnc"
                                                                           value="{{$dian_settings_numberstartnc}}"
                                                                           min="0" max="999999999999999" required>
                                                                </td>
                                                                <td>
                                                                    <label>Fin de Numeración</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_numberendnc"
                                                                           id="dian_settings_numberendnc"
                                                                           value="{{$dian_settings_numberendnc}}"
                                                                           min="0" max="999999999999999" required>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <label>Prefijo Nota Débito</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_prefixnd"
                                                                           id="dian_settings_prefixnd"
                                                                           value="{{$dian_settings_prefixnd}}"
                                                                           maxlength="10">
                                                                </td>
                                                                <td>
                                                                    <label>Inicio de Numeración</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_numberstartnd"
                                                                           id="dian_settings_numberstartnd"
                                                                           value="{{$dian_settings_numberstartnd}}"
                                                                           min="0" max="999999999999999" required>
                                                                </td>
                                                                <td>
                                                                    <label>Fin de Numeración</label>
                                                                    <input type="number"
                                                                           class="form-control"
                                                                           name="dian_settings_numberendnd"
                                                                           id="dian_settings_numberendnd"
                                                                           value="{{$dian_settings_numberendnd}}"
                                                                           min="0" max="999999999999999" required>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="form-group">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                            <th colspan="4">Datos tributarios del Emisor
                                                            </th>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td style="width:25%;">
                                                                    <label>Tipo de documento</label>
                                                                    <select name="dian_settings_typedoc_cod"
                                                                            id="dian_settings_typedoc_cod"
                                                                            class="form-control">
                                                                        @foreach ($cmbtypedoc as $typedoc_cod)
                                                                            @if ($typedoc_cod->cod==$dian_settings_typedoc_cod)
                                                                                <option value="{{$typedoc_cod->cod}}"
                                                                                        selected='selected'>{{$typedoc_cod->Description}}</option>
                                                                            @else
                                                                                <option value="{{$typedoc_cod->cod}}">{{$typedoc_cod->Description}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td style="width:25%;">
                                                                    <label>N° Identificación</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_identificacion"
                                                                           id="dian_settings_identificacion"
                                                                           value="{{$dian_settings_identificacion}}" required>
                                                                </td>
                                                                <td style="width:25%;">
                                                                    <label>Razón social</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_businessname"
                                                                           id="dian_settings_businessname"
                                                                           value="{{$dian_settings_businessname}}">
                                                                </td>
                                                                <td style="width:25%;">
                                                                    <label>Nombre Comercial</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_tradename"
                                                                           id="dian_settings_tradename"
                                                                           value="{{$dian_settings_tradename}}" required>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label>Tipo de Contribuyente</label>
                                                                    <select name="dian_settings_typetaxpayer_cod"
                                                                            id="dian_settings_typetaxpayer_cod"
                                                                            class="form-control">
                                                                        @foreach ($cmbtypetaxpayer as $typetaxpayer)
                                                                            @if ($typetaxpayer->cod==$dian_settings_typetaxpayer_cod)
                                                                                <option value="{{$typetaxpayer->cod}}"
                                                                                        selected='selected'>{{$typetaxpayer->Description}}</option>
                                                                            @else
                                                                                <option value="{{$typetaxpayer->cod}}">{{$typetaxpayer->Description}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label>Regimen Contable</label>
                                                                    <select name="dian_settings_accountingregime_cod"
                                                                            id="dian_settings_accountingregime_cod"
                                                                            class="form-control">
                                                                        @foreach ($cmbaccountingregime as $accountingregime)
                                                                            @if ($accountingregime->cod==$dian_settings_accountingregime_cod)
                                                                                <option value="{{$accountingregime->cod}}"
                                                                                        selected='selected'>{{$accountingregime->Description}}</option>
                                                                            @else
                                                                                <option value="{{$accountingregime->cod}}">{{$accountingregime->Description}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label>Tipo de Responsabilidad</label>
                                                                    <select name="dian_settings_typeresponsibility_cod"
                                                                            id="dian_settings_typeresponsibility_cod"
                                                                            class="form-control">
                                                                        @foreach ($cmbtyperesponsibility as $typeresponsibility)
                                                                            @if ($typeresponsibility->cod==$dian_settings_typeresponsibility_cod)
                                                                                <option value="{{$typeresponsibility->cod}}"
                                                                                        selected='selected'>{{$typeresponsibility->Description}}</option>
                                                                            @else
                                                                                <option value="{{$typeresponsibility->cod}}">{{$typeresponsibility->Description}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label>Actividad Económica</label>
                                                                    <select style='width:100%;'
                                                                            name="dian_settings_economicactivity_cod"
                                                                            id="dian_settings_economicactivity_cod"
                                                                            class="select2">
                                                                        @foreach ($cmbeconomicactivity as $economicactivity)
                                                                            @if ($economicactivity->cod==$dian_settings_economicactivity_cod)
                                                                                <option value="{{$economicactivity->cod}}"
                                                                                        selected='selected'>{{$economicactivity->cod.' - '.$economicactivity->Description}}</option>
                                                                            @else
                                                                                <option value="{{$economicactivity->cod}}">{{$economicactivity->cod.' - '.$economicactivity->Description}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <label>Departamento\Municipio</label><br>
                                                                    <select style='width:100%;'
                                                                            name="dian_settings_municipio_cod"
                                                                            id="dian_settings_municipio_cod"
                                                                            class="select2">
                                                                        @foreach ($cmbmunicipio as $municipio)
                                                                            @if ($municipio->cod==$dian_settings_municipio_cod)
                                                                                <option value="{{$municipio->cod}}"
                                                                                        selected='selected'>{{$municipio->Departamento.'/'.$municipio->Municipio}}</option>
                                                                            @else
                                                                                <option value="{{$municipio->cod}}">{{$municipio->Departamento.'/'.$municipio->Municipio}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td colspan="2">
                                                                    <label>@lang('app.direction')</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_direction"
                                                                           id="dian_settings_direction"
                                                                           value="{{$dian_settings_direction}}" required>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <label>@lang('app.email')</label><br>
                                                                    <input type="email" class="form-control"
                                                                           name="dian_settings_email"
                                                                           id="dian_settings_email"
                                                                           value="{{$dian_settings_email}}">
                                                                </td>
                                                                <td colspan="2">
                                                                    <label>Télefono</label>
                                                                    <input type="text" class="form-control"
                                                                           name="dian_settings_phone"
                                                                           id="dian_settings_phone"
                                                                           value="{{$dian_settings_phone}}">
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="form-group w100">
                                                        <label for="activate_mx" class="col-lg-2 control-label">Activar Integración</label>
                                                        <label class="switch">
                                                            {{-- $status_factel 0 => Ecuador, 1 => Desactivado , 2 => Colombia, 3 => Mexico--}}
                                                            <input type="checkbox" name="activate_co" value="activate_co" {{ $status_factel == '2' ? 'checked': '' }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                    <div class="form-group" style="text-align: left;">
                                                        <input type="hidden" name="status_factel" value="2">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                                id="btnsaveemisor"> @lang('app.save')</button>
                                                        @if ($status_factel == 2 && $dian_settings_typeoperation_cod == '2' && $dian_settings_tecnicalkey!='')
                                                            <a class="btn btn-default btn-sm" id="btnpruebas"
                                                               onclick="enviarxmldeprueba();"> Enviar los XML de
                                                                prueba</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="invoice-mx" class="tab-pane fade" role="tabpanel">
                                            @include('config.invoice_mx')
                                        </div>
                                        <div id="invoice-ven" class="tab-pane fade" role="tabpanel">
                                            @include('config.invoice_ven')
                                        </div>
                                    </div>
                                    {{-- END TABS CONTENT --}}
                                </div>
                                {{-- END SECTION INVOICING --}}

                                <div id="faq-tab-9" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon la la-money bigger-120"></i>
                                        @lang('app.transmitter')
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-9" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-9-1" data-parent="#faq-list-9" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-cc-stripe bigger-130"></i>
                                                    &nbsp; @lang('app.issuerData')
                                                </a>

                                            </div>

                                            <div class="panel-collapse collapse" id="faq-9-1">
                                                <div class="panel-body">

                                                </div>
                                            </div>

                                        </div>

                                        <div class="panel panel-default">
                                            {{-- <div class="panel-heading">
                                                <a href="#faq-9-2" data-parent="#faq-list-7" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-envelope-o bigger-130"></i>
                                                    &nbsp; Email smtp @lang('app.electronicBilling')
                                                </a>
                                            </div> --}}

                                            <div class="panel-collapse collapse" id="faq-9-2">
                                                <div class="panel-body">
                                                    <form id="form_email_f" method="post">
                                                        <div class="form-group">
                                                            <label for="cache"
                                                                   class="col-xs-12 control-label">@lang('app.CorreoElectrònico')</label>
                                                            <input type="text" value="{{$email_f}}" class="form-control"
                                                                   name="email_f" id="email_f">
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="form-group" style="text-align: left;">
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnsaveemail_f"> @lang('app.save')</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel-collapse collapse" id="faq-8-2">
                                            <div class="panel-body">
                                                <form class="form-horizontal" id="form-factel-active"
                                                      enctype="multipart/form-data">

                                                    <div class="form-group">
                                                        <label for="paypal_client_id"
                                                               class="col-sm-2 control-label text-lowercase">@lang('app.electronicBilling')</label>
                                                        <div class="col-sm-10">
                                                            <input type="radio" name="status_factel" id="status_factel"
                                                                   value="0" {{ $status_factel === 0 ? 'checked' : '' }}> @lang('app.activate')
                                                            <br>
                                                            <input type="radio" name="status_factel" id="status_factel"
                                                                   value="1" {{ $status_factel === 1 ? 'checked' : '' }}> @lang('app.deactivate')
                                                            <br>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-sm-12">
                                                            <label for="btnstripe"
                                                                   class="col-sm-2 control-label"></label>
                                                            <button type="button" class="btn btn-primary btn-sm"
                                                                    id="btnsavefactel"> @lang('app.save')</button>
                                                        </div>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="faq-tab-5" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon icon-feed bigger-120"></i>
                                        APIS
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-5" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-5-1" data-parent="#faq-list-5" data-toggle="collapse"
                                                   class="accordion-toggle collapsed" id="">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-server bigger-130"></i>
                                                    &nbsp; Mikrotik API
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-5-1">
                                                <div class="panel-body">
                                                    <p>@lang('app.HereYoucanconfigurethe').</p>

                                                    <form class="form-horizontal" id="formapimk">
                                                        <div class="form-group">
                                                            <label for="attempts" class="col-sm-2 control-label">Attempts</label>
                                                            <div class="col-sm-6">
                                                                <input type="text" class="form-control" maxlength="2"
                                                                       name="attempts" id="attempts">
                                                                <span class="help-block">@lang('app.Numberofconnection').</span>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="Timeout"
                                                                   class="col-sm-2 control-label">Timeout</label>
                                                            <div class="col-sm-6">
                                                                <input type="text" class="form-control" maxlength="2"
                                                                       name="timeout" id="Timeout">
                                                                <span class="help-block">@lang('app.Connectionattempttimeout').</span>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="mkdeug"
                                                                   class="col-sm-2 control-label">@lang('app.debug')</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="mkdebug" name="mkdebug" value="true"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                                <span class="help-block">@lang('app.showDebugInformation').</span>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="mkssl"
                                                                   class="col-sm-2 control-label">SSL</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="mkssl" name="mkssl" value="true"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                                <span class="help-block">@lang('app.ConnectUsingSSL').</span>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-sm-offset-2 col-sm-10">
                                                                <button type="button" id="btnsavemkapi"
                                                                        class="btn btn-primary">@lang('app.save')</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!--start tab-->

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-5-2" data-parent="#faq-list-5" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="fa fa-map" aria-hidden="true"></i>
                                                    &nbsp; Google Maps API
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-5-2">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formapimaps">


                                                        <div class="form-group">
                                                            <label for="maps"
                                                                   class="col-sm-1 control-label">@lang('app.Key')
                                                                API</label>
                                                            <div class="col-sm-10">
                                                                <input type="text" class="form-control"
                                                                       name="googlemapsapi" id="maps">
                                                                <span class="help-block">@lang('app.GetYourGoogleMapsAPI'), <a
                                                                            href="https://developers.google.com/maps/documentation/javascript/get-api-key"
                                                                            target="_blank">@lang('app.getmyAPIAPIkeyfor')</a>.</span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                                <label for="choose_map"
                                                                       class="col-sm-1 control-label">@lang('app.mapSettings')</label>
                                                                <div class="col-sm-10">
                                                                    <select class="form-control" name="map_type"
                                                                            id="choose_map">
                                                                            <option value="google_map" {{ $global->map_type == 'google_map' ? 'selected' : '' }}>Google Map</option>
                                                                            <option value="open_street_map" {{ $global->map_type == 'open_street_map' ? 'selected' : '' }}>OpenStreetMap</option>
                                                                    </select>
                                                                </div>
                                                        </div>
                                                        <div class="form-group">

                                                            <div class="col-sm-12">
                                                                <label for="btnsmsmgateway"
                                                                       class="col-sm-1 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        id="btnsaveapimaps"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-5-3" data-parent="#faq-list-5" data-toggle="collapse"
                                                   class="accordion-toggle collapsed" id="">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-server bigger-130"></i>
                                                    &nbsp; Smart OLT API
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-5-3">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formapiolt">
                                                        <div class="form-group">
                                                            <label for="attempts" class="col-sm-2 control-label">URL</label>
                                                            <div class="col-sm-6">
                                                                <input type="text" class="form-control"
                                                                       name="url_smartolt" id="url_smartolt">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="Timeout"
                                                                   class="col-sm-2 control-label">API Key</label>
                                                            <div class="col-sm-6">
                                                                <input type="text" class="form-control"
                                                                       name="apikey_smartolt" id="apikey_smartolt">
                                                            </div>
                                                        </div>


                                                        <div class="form-group">
                                                            <label for="mkdeug"
                                                                   class="col-sm-2 control-label">Activar API</label>
                                                            <div class="col-sm-3">
                                                                <label><input id="check_smartolt" name="check_smartolt" value="true"
                                                                              class="ace ace-switch ace-switch-6"
                                                                              type="checkbox"/>
                                                                    <span class="lbl"></span>
                                                                </label>
                                                                <span class="help-block">@lang('app.activar api para trabajar con smart olt').</span>
                                                            </div>
                                                        </div>


                                                        <div class="form-group">
                                                            <div class="col-sm-offset-2 col-sm-10">
                                                                <button type="button" id="btnsavesmartoltapi"
                                                                        class="btn btn-primary">@lang('app.save')</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!--end tab-->


                                    </div>
                                </div>
                                <div id="faq-tab-4" class="tab-pane fade">
                                    <h4 class="title_con">
                                        <i class="ace-icon la la-user bigger-120"></i>
                                        @lang('app.clientPortal')
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-4" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-4-1" data-parent="#faq-list-4" data-toggle="collapse"
                                                   class="accordion-toggle collapsed" id="adv">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-paper-plane bigger-130"></i>
                                                    &nbsp; @lang('app.PortalAddress')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-4-1">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="formaddadv">
                                                        <div class="form-group">
                                                            <label for="url"
                                                                   class="col-sm-2 control-label">IP/URL @lang('app.server')</label>
                                                            <div class="col-sm-3">
                                                                <input type="text" class="form-control" name="ip"
                                                                       id="url" maxlength="50">
                                                            </div>


                                                        </div>

                                                        <div class="form-group">
                                                            <label for="url"
                                                                   class="col-sm-2 control-label">@lang('app.directory') </label>
                                                            <div class="col-sm-3">
                                                                <input type="text" class="form-control" name="path"
                                                                       placeholder="aviso" id="path" maxlength="20">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-sm-offset-2 col-sm-10">
                                                                <input type="hidden" name="idadv" id="idadv">
                                                                <button type="button" class="btn btn-primary"
                                                                        id="savebtnadv">@lang('app.save')</button>
                                                                <button type="reset"
                                                                        class="btn btn-success">@lang('app.clear')</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="faq-tab-3" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon fa fa-language bigger-120"></i>
                                        @lang('app.languageSettings')
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-3" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-3-1" data-parent="#faq-list-3" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-language bigger-130"></i>
                                                    &nbsp; @lang('app.languageSetting')
                                                </a>

                                            </div>

                                            <div class="panel-collapse collapse" id="faq-3-1">
                                                <div class="panel-body">
                                                    <form id="form_language_settings" method="post">
                                                        <div class="col-xs-12">
                                                            @foreach($allLanguages as $language)
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label for="backups"
                                                                               class="col-sm-9 control-label">{{$language->language_name}}</label>
                                                                        <div class="col-sm-6">
                                                                            <label><input
                                                                                        id="backups[{{$language->id}}]"
                                                                                        name="language_code[{{$language->language_code}}]"
                                                                                        value="true"
                                                                                        class="ace ace-switch ace-switch-6"
                                                                                        @if($language->status == 'enabled') checked
                                                                                        @endif type="checkbox"/>
                                                                                <span class="lbl"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="col-xs-12">
                                                            <div class="form-group text-center">
                                                                <button type="submit" class="btn btn-primary btn-sm"
                                                                        id="btnsavelanguagesettings"> @lang('app.save')</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-3-2" data-parent="#faq-list-3" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-language bigger-130"></i>
                                                    @lang('app.SelectLanguage')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-3-2">
                                                <div class="panel-body">
                                                    <div class="col-xs-12">
                                                        <form id="form_select_language" method="post">
                                                            <div class="form-group">
                                                                <label for="paypal_mode"
                                                                       class="col-sm-2 control-label">@lang('app.language')</label>
                                                                <div class="col-sm-4">
                                                                    <select class="form-control" name="language"
                                                                            id="language">
                                                                        @foreach($languages as $language)
                                                                            <option value="{{$language->language_code}}" {{ $global->locale === $language->language_code ? 'selected' : '' }}>{{ $language->language_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="form-group" style="text-align: left;">
                                                                    <button type="button" class="btn btn-primary btn-sm"
                                                                            id="btnsavelanguage"> @lang('app.save')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="{{ url('/translations') }}" target="_blank"
                                                   class="accordion-toggle collapsed">
                                                    <i class="green ace-icon fa fa-language"></i> @lang('app.changeTranslations')
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmapedit" role="dialog"
                     aria-labelledby="myLargeModalLabel" data-map-type="{{ $global->map_type }}">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">@lang('app.close')</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i> @lang('app.map_types.' . $global->map_type ?? '')</h4>
                            </div>

                            <div class="modal-body">
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-1 control-label">@lang('app.lookFor'):</label>

                                        <div class="col-sm-11">
                                            <input type="text" class="form-control" id="us4-address"/>
                                        </div>
                                    </div>

                                    <div id="us4" style="width: 100%; height: 400px;"></div>
                                    <div class="clearfix">&nbsp;</div>


                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal"><i
                                            class="fa fa-crosshairs"></i> @lang('app.toAccept')</button>

                            </div>

                        </div>
                    </div>
                </div>

                @include('layouts.modals')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($map!='0')
        <script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
        <script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
    @endif

    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/typeahead.jquery.min.js')}}"></script>
	<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
   integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
   crossorigin=""></script>
   <script src="{{asset('assets/js/rocket/config-core.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('#status_factel').trigger('change');
            $('.select2').select2();

            var maxchars = 19;

            setTimeout(()=> {
                $('#name').keyup();
            }, 500)

            $('#name').keyup(function () {
                var tlength = $(this).val().length;
                $(this).val($(this).val().substring(0, maxchars));
                var tlength = $(this).val().length;
                remain = maxchars - parseInt(tlength);
                $('#remain').text(remain);
            });
        });


        function enviarxmldeprueba() {
            bootbox.confirm("Realmente desea ejecutar el test de prueba", function (result) {
                if (result) {
                    $('html, body').animate({scrollTop: 0}, 'slow');
                    var url = '{{ route('invoice.sendtestxml_dian') }}';
                    $.easyAjax({
                        type: 'POST',
                        blockUI: true,
                        url: url,
                        success: function (response) {
                            if (response.status == "success") {
                                $('#dian_settings_typeoperation_cod').val('1');
                                setTimeout('document.location.reload()', 4000);
                            }
                        }
                    });
                }
            });
        }


    </script>	<script>		</script>
@endsection
