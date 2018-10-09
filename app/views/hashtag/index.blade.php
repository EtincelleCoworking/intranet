@extends('layouts.master')

@section('meta_title')
    Tags
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>Tags</h2>
        </div>
        <div class="col-sm-4">

        </div>
    </div>
@stop



@section('content')

    @if(count($items)>0)
        <div class="ibox ">
            <div class="ibox-content">
                <div class="row">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Mis en avant</th>
                        </tr>
                        </thead>
                        @foreach($items as $item)
                            <tr>
                                <td>{{$item->name}}</td>
                                <td>
                                    @if($item->is_highlighted)
                                        <i class="fa fa-check"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="ibox ">
        <div class="ibox-content">
            <div class="row">

                {{ Form::open(array('url' => URL::route('hashtags_add'))) }}
                {{ Form::label('content', 'Ajout rapide de tags') }}
                {{ Form::textarea('content', null, array('class' => 'form-control')) }}
                <div class="col-lg-12">
                    {{ Form::checkbox('is_highlighted', true) }}
                    {{ Form::label('is_highlighted', 'Mis en avant') }}
                </div>
                {{ Form::submit('Ajouter', array('class' => 'btn btn-primary')) }}
                {{ Form::close() }}
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
