@extends('layouts.master')

@section('meta_title')
    Ajout d'une charge
@stop

@section('content')
    @if ($errors->has())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif

    <h1>Nouvelle charge</h1>
    {{ Form::open(array('route' => array('charge_add'), 'files' => true)) }}
        {{ Form::label('date_charge', 'Date de la charge') }}
        <p>{{ Form::text('date_charge', date('d/m/Y'), array('class' => 'form-control datePicker')) }}</p>
        {{ Form::label('date_payment', 'Date du paiement') }}
        <p>{{ Form::text('date_payment', null, array('class' => 'form-control datePicker')) }}</p>
        {{ Form::label('deadline', 'Date d\'échéance') }}
        <p>{{ Form::text('deadline', null, array('class' => 'form-control datePicker')) }}</p>
        {{ Form::label('tags', 'Tags (séparés par ", ")') }}
        <p>{{ Form::select('tags[]', array('test' => 'Test 1', '2' => 'Test 2'), null, array('class' => 'form-control tagsGet', 'multiple' => 'multiple', 'data-tags' => true)) }}</p>
        {{ Form::label('document', 'Facture jointe') }}
        <p>{{ Form::file('document', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
$().ready(function(){
    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }

    var urlJsonGetTags = "{{ URL::route('tag_json_list') }}";

    $('.datePicker').datepicker();

    $(".tagsGet").select2({
        ajax: {
        url: urlJsonGetTags,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            term: params.term
          };
        },
        processResults: function (data, page) {
          return {
            results: $.map(data, function (item) {
                    return {
                        text: item.name,
                        id: item.name
                    }
                })
          };
        },
        cache: true
      },
      minimumInputLength: 2
    });
});
</script>
@stop