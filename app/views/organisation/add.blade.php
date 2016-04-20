@extends('layouts.master')

@section('meta_title')
    Ajouter une société
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Ajouter une société</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">

                        {{ Form::open(array('route' => 'organisation_add')) }}
                        {{ Form::label('name', 'Nom') }}
                        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                        {{ Form::label('address', 'Adresse') }}
                        <p>{{ Form::textarea('address', null, array('class' => 'form-control', 'rows' => 3)) }}</p>

                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::label('zipcode', 'Code postal') }}
                                <p>{{ Form::text('zipcode', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('city', 'Ville') }}
                                <p>{{ Form::text('city', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('country_id', 'Pays') }}
                                <p>{{ Form::select('country_id', Country::Select(), 73, array('class' => 'form-control')) }}</p>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::label('code_purchase', 'Code achat') }}
                                <p>{{ Form::text('code_purchase', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('code_sale', 'Code vente') }}
                                <p>{{ Form::text('code_sale', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('tva_number', 'TVA') }}
                                <p>{{ Form::text('tva_number', null, array('class' => 'form-control')) }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::label('is_domiciliation', 'Domiciliation') }}
                                <p>{{ Form::checkbox('is_domiciliation', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                            </div>
                            <div class="col-md-4">
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('country_list') }}" class="btn btn-white">Annuler</a>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop