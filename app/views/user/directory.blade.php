@extends('layouts.master')

@section('meta_title')
    Liste des utilisateurs
@stop

@section('content')
    <h1>Liste des utilisateurs</h1>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>Organisations</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr>
                <td><a href="{{ URL::route('user_profile', $user->id) }}">{{ $user->fullname }}</a></td>
                <td>
                    @foreach ($user->organisations as $k => $organisation)
                        @if ($k > 0)
                            ,
                        @endif
                        {{ $organisation->name }}
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">{{ $users->links() }}</td>
            </tr>
        </tfoot>
    </table>
@stop