<div class="ibox">
    <div class="ibox-title">
        <h5>Accès automatiques</h5>
    </div>
    <div class="ibox-content">
        <table class="table">
            @foreach($_ENV['intercoms'] as $key => $data)
                <tr>
                    <td width="40">
                        <div id="intercom-{{$key}}" class="label label-danger">KO</div>
                    </td>
                    <td>
                        {{$data['name']}}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>


<script type="application/javascript">
    function updateIntercomStatus(widget, uri) {
        $.get(uri, function (data) {
            if ('Yes' == data) {
                $(widget)
                    .removeClass('label-danger')
                    .addClass('label-success')
                    .text('OK');
            } else {
                $(widget)
                    .removeClass('label-success')
                    .addClass('label-danger')
                    .text('KO');
            }
        });
    }

    $(function () {
        @foreach($_ENV['intercoms'] as $key => $data)
        setInterval(function () {
            updateIntercomStatus('#intercom-{{$key}}', '{{$data['uri']}}')
        }, 60000);
        @endforeach
    });
</script>