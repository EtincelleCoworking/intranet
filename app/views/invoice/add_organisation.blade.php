@extends('layouts.master')

@section('meta_title')
    @if ($type == 'F')
        Nouvelle facture
    @elseif ($type == 'D')
        Nouveau devis
    @endif
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>
                @if ($type == 'F')
                    Nouvelle facture
                @elseif ($type == 'D')
                    Nouveau devis
                @endif
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">

                    {{ Form::open(array('route' => array('invoice_add_check', $type))) }}
                    {{ Form::hidden('type', $type) }}
                    {{ Form::hidden('organisation_id', $organisation, array('id' => 'orgaID')) }}
                    {{ Form::label('user_id', 'Client') }}
                    <p>{{ Form::select('user_id', User::SelectInOrganisation($organisation, 'Sélectionnez un client'), null, array('class' => 'form-control')) }}</p>
                    {{ Form::label('address', 'Adresse de facturation') }}
                    <p>{{ Form::textarea('address', null, array('id' => 'addressInvoice', 'class' => 'form-control', 'rows' => '5')) }}</p>
                    {{ Form::label('date_invoice', 'Date de facturation') }}
                    <p>{{ Form::text('date_invoice', date('d/m/Y'), array('class' => 'form-control datePicker')) }}</p>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('invoice_list', 'all') }}" class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>

        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            function getDataOrganisation(id) {
                var url = "{{ URL::route('organisation_json_infos') }}";
                var urlFinale = url.replace("%7Bid%7D", id);

                $.getJSON(urlFinale, function (data) {
                    $.each(data, function (key, val) {
                        $('#addressInvoice').html(val);
                    });
                });
            }

            getDataOrganisation($('#orgaID').val());
            $('.datePicker').datepicker();
        });
    </script>
@stop