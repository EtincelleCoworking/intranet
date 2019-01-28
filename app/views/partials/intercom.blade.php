<div class="ibox">
    <div class="ibox-title">
        <h5>Accès automatiques</h5>
    </div>
    <div class="ibox-content">
        <table class="table">
            @foreach(Config::get('etincelle.intercoms') as $key => $data)
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

