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
                    @if(isset($messages))
                        <ul>
                            @foreach($messages as $message)
                                <li>{{$message}}</li>
                            @endforeach
                        </ul>
                    @endif

                    {{ Form::open(array('route' => 'cashflow_update_post', 'files' => true)) }}
                    {{ Form::label('file', 'Fichier OFX') }}
                    <p>{{ Form::file('file', array('class' => '')) }}</p>


                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('cashflow') }}" class="btn btn-white">Annuler</a>
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
        });
    </script>
@stop