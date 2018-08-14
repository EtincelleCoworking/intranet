@extends('layouts.master')

@section('meta_title')
    Notification Domiciliation - {{$organisation->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Notification Domiciliation - {{$organisation->name}}</h2>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    {{ Form::open(array('route' => array('postbox_notify_handle', $organisation->id))) }}
                    <div class="row">
                        <div class="col-md-3">
                            {{ Form::label('occurs_at', 'Courrier reçus le') }}
                            <p>{{ Form::text('occurs_at', date('d/m/Y'), array('class' => 'form-control datePicker', 'autocomplete' => 'new-password')) }}</p>
                        </div>
                    </div>
                    <table class="table table-striped" id="PostboxContent">
                        <tr>
                            <td>
                                <div class="row">
                                    <div class="col-md-1">
                                        {{ Form::label('quantity', 'Quantité') }}
                                        <p>{{ Form::text('quantity[0]', 1, array('class' => 'form-control')) }}</p>
                                    </div>
                                    <div class="col-md-2">
                                        {{ Form::label('kind', 'Type') }}
                                        @foreach($kinds as $kind_id => $kind_value)
                                            <p>
                                                {{Form::radio('kind[0]', $kind_id, $default_kind_id == $kind_id)}} {{$kind_value}}
                                            </p>
                                        @endforeach

                                    </div>
                                    <div class="col-md-4">
                                        {{ Form::label('from_name', 'Expéditeur') }}
                                        <p>{{ Form::text('from_name[0]', null, array('class' => 'form-control')) }}</p>
                                    </div>
                                    <div class="col-md-5">
                                        {{ Form::label('details', 'Informations complémentaires') }}
                                        <p>{{ Form::text('details[0]', null, array('class' => 'form-control')) }}</p>
                                    </div>
                                    <div class="col-md-12">
                                        <p>{{Form::checkbox('is_important[0]', true, false)}} {{ Form::label('is_important', 'Recommandé') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="btn btn-danger btn-xs action-delete">Supprimer</a>
                            </td>
                        </tr>

                    </table>
                    <a href="javascript:void()" class="btn btn-primary" id="postboxAdd">Ajouter</a>

                    <div class="row">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('postbox') }}" class="btn btn-white">Annuler</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



@section('javascript')
    <script type="text/javascript">
        var RowCounter = 1;
        var newRowTemplate = '<tr>\n' +
            '                            <td>\n' +
            '                                <div class="row">\n' +
            '                                    <div class="col-md-1">\n' +
            '                                        {{ Form::label('quantity', 'Quantité') }}\n' +
            '                                        <p>{{ Form::text('quantity[ROWID]', 1, array('class' => 'form-control')) }}</p>\n' +
            '                                    </div>\n' +
            '                                    <div class="col-md-2">\n' +
            '                                        {{ Form::label('kind', 'Type') }}\n' +
                @foreach($kinds as $kind_id => $kind_value)
                    '                                            <p>\n' +
            '                                                {{Form::radio('kind[ROWID]', $kind_id, $default_kind_id == $kind_id)}} {{$kind_value}}\n' +
            '                                            </p>\n' +
                @endforeach
                    '                                    </div>\n' +
            '                                    <div class="col-md-4">\n' +
            '                                        {{ Form::label('from_name', 'Expéditeur') }}\n' +
            '                                        <p>{{ Form::text('from_name[ROWID]', null, array('class' => 'form-control')) }}</p>\n' +
            '                                    </div>\n' +
            '                                    <div class="col-md-5">\n' +
            '                                        {{ Form::label('details', 'Informations complémentaires') }}\n' +
            '                                        <p>{{ Form::text('details[ROWID]', null, array('class' => 'form-control')) }}</p>\n' +
            '                                    </div>\n' +
            '                                    <div class="col-md-12">\n' +
            '                                        <p>{{Form::checkbox('is_important[ROWID]', true, false)}} {{ Form::label('is_important', 'Recommandé') }}</p>\n' +
            '                                    </div>\n' +
            '                                </div>\n' +
            '                            </td>\n' +
            '                            <td>' +
            '                                <a href="javascript:void(0);" class="btn btn-danger btn-xs action-delete">Supprimer</a>' +
            '                            </td> ' +
            '                        </tr>';

        $().ready(function () {
            $('.datePicker').datepicker();
            $('body').on('click', '.action-delete', function () {
                $(this).parent().parent().remove();
            });

            $('#postboxAdd').click(function () {
                $('#PostboxContent').append(newRowTemplate
                    .replace(new RegExp('ROWID', 'g'), RowCounter++)
                );
            });
        });


    </script>
@stop
