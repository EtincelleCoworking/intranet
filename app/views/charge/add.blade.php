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
        {{ Form::label('tags', 'Tags (séparés par ", ")') }}
        <p>{{ Form::text('tags', null, array('class' => 'form-control autoGetTags')) }}</p>
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

    $( ".autoGetTags" )
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
            $( this ).autocomplete( "instance" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        source: function( request, response ) {
          $.getJSON( urlJsonGetTags, {
            term: extractLast( request.term )
          }, response );
        },
        search: function() {
          // custom minLength
          var term = extractLast( this.value );
          if ( term.length < 2 ) {
            return false;
          }
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
      });
});
</script>
@stop