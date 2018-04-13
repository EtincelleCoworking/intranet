<div class="ibox">
    <div class="ibox-title">
        <h5>Code personnel</h5>
    </div>
    <div class="ibox-content">
        <?php
        $current_user = Auth::user();
        $code = $current_user->personnal_code;
        ?>
        <h1 id="user-personnal-code">
            @if(empty($code))
                -
            @else
                {{$code}}
            @endif
        </h1>

        <?php

        $rooms = Phonebox::where('location_id', '=', $current_user->default_location_id)->with('active_session')->orderBy('order_index', 'ASC')->get();

        ?>

        <p><a href="#" class="btn btn-primary" id="btn-refresh-personnal-code">Nouveau code</a></p>
        <p class="text-muted">Ce code vous permettra d'utiliser les box téléphonique. Si le code ne vous conviens pas, vous pouvez en générer un nouveau.</p>
        <table class="table">
            @foreach($rooms as $room)
                <tr>
                    <td width="40">
                        @if($room->active_session)
                            <div class="label label-danger">KO</div>
                        @else
                            <div class="label label-primary">OK</div>
                        @endif
                    </td>
                    <td>
                        @if($room->active_session)
                            <a href="{{URL::route('user_profile', $room->active_session->user_id)}}" class="pull-right">
                                {{ $room->active_session->user->avatarTag38 }}
                            </a>
                        @endif

                        {{ $room->name }}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>


<script type="application/javascript">
    docReady(function () {

        $('#btn-refresh-personnal-code').click(function () {
            $.ajax({
                dataType: 'json',
                url: '{{ URL::route('user_refresh_personnal_code') }}',
                type: "GET",
                success: function (data) {
                    console.log(data);
                    if (data.status == 'error') {
                        alert(data.message);
                    } else {
                        $('#user-personnal-code').text(data.code);
                    }
                },
                error: function (data) {
                    console.log(data);
                }
            });
            return false;
        })
    });

</script>