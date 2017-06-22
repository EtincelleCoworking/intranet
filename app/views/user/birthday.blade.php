@extends('layouts.master')

@section('meta_title')
    Anniversaires
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Anniversaires</h2>
        </div>
    </div>
@stop

@section('content')
    @foreach ($months as $month)
        <h2>{{ $month }}</h2>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="col-md-1">Membre</th>
            <th class="col-md-5">Nom</th>
            <th class="col-md-4">Email</th>
            <th class="col-md-2">Date de naissance</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users[$month] as $user)
            <tr>
                <td>
                    <?php
                    if ($user->is_member) {
                        echo '<i class="fa fa-check"></i>';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    switch ($user->gender) {
                        case 'F':
                            echo '<i class="fa fa-female"></i>';
                            break;
                        case 'M':
                            echo '<i class="fa fa-male"></i>';
                            break;
                        default:
                            echo '<i class="fa fa-question"></i>';
                    }
                    ?>
                    <a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a>
                </td>
                <td>
                    {{ $user->email }}
                </td>
                <td>
                    <?php
                    if ($user->birthday && $user->birthday != '0000-00-00') {
                        echo date('d/m/Y', strtotime($user->birthday));
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
        @endforeach
@stop

@section('javascript')

    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop