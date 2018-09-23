@extends('layouts.master')

@section('meta_title')
    Gérer le casier {{$locker->name}}
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Gérer le casier {{$locker->name}}
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                    <form action="{{URL::route('locker_assign_check', $locker->id)}}" method="post">
                        {{ Form::label('user_id', 'Utilisateur') }}
                        <p>{{ Form::select('user_id', User::select(), null, array('id' => 'selectUserId', 'class' => 'form-control')) }}</p>
                        <input type="submit" value="Enregistrer" class="btn btn-primary"/>
                    </form>
                </div>
            </div>

        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

            $('#selectUserId').select2();

        });
    </script>
@stop