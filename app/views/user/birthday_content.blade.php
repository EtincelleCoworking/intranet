<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th class="col-md-4">Nom</th>
        <th class="col-md-4">Email</th>
        <th class="col-md-2">Date de naissance</th>
        <th class="col-md-2">Dernière visite</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($users as $u)
        <tr class="<?php
        if (!$u->is_member) {
            echo 'text-muted';
        }
        ?>">
            <td>

                <?php
                switch ($u->gender) {
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
                <a href="{{ URL::route('user_modify', $u->id) }}">{{ $u->fullname }}</a>
            </td>
            <td>
                {{ $u->email }}
            </td>
            <td>
                <?php
                if ($u->birthday && $u->birthday != '0000-00-00' && $u->birthday != '1970-01-01') {
                    echo date('d/m/Y', strtotime($u->birthday));
                } else {
                    echo '-';
                }
                ?>
            </td><td>
                <?php

                if ($u->last_seen_at && $u->last_seen_at != '0000-00-00' && $u->last_seen_at != '1970-01-01') {
                    echo date('d/m/Y', strtotime($u->last_seen_at));
                } else {
                    echo '-';
                }
                ?>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>