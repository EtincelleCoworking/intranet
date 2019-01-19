@extends('layouts.master')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered">
                <tr>
                    <td>Salle</td>
                    @foreach($rooms as $room)
                        <td>{{$room['name']}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Actuellement</td>
                    @foreach($rooms as $room)
                        <td>-</td>
                    @endforeach
                </tr>
                <tr>
                    <td>A venir</td>
                    @foreach($rooms as $room)
                        <td>-</td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>

@stop

@section('javascript')
@stop




