@extends('layouts.master')

@section('meta_title')
    Mise à jour de la trésorerie
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Mise à jour de la trésorerie</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">
                    <div class="alert alert-info">
                        {{$message}}
                    </div>

                    <table class="table">
                        <tr>
                            <th>Date</th>
                            <th>Opération</th>
                            <th>Montant</th>
                            <th>Module</th>
                            <th>Commentaire</th>
                        </tr>
                        @foreach($report as $line)
                            <tr class="bg-{{$line['status']}}">
                                <td>{{$line['occurs_at']}}</td>
                                <td>{{$line['text']}}</td>
                                <td>
                                    @if ($line['amount'] < 0)
                                        <span style="color: red">{{ number_format( $line['amount'], 2, ',', '.') }}
                                            €</span>
                                    @else
                                        <span style="color: green">{{ number_format( $line['amount'], 2, ',', '.') }}
                                            €</span>
                                    @endif
                                </td>
                                <td>{{$line['module']}}</td>
                                <td>{{$line['comment']}}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <a href="{{ URL::route('cashflow') }}" class="btn btn-white">Retour</a>
                    </div>
                </div>
            </div>

        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
        });
    </script>
@stop