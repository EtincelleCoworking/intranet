<table border="0" cellspacing="0" cellpadding="0" width="470" style="width:470px">
    <tbody>
    <tr valign="top">
        <td style="width:10px;padding-right:10px">
            <img src="{{URL::asset($user->getAvatarUrl(100))}}"
                 width="65" alt="photo"
                 style="border-radius:3px;width:100px;max-width:120px">
        </td>
        <td style="border-right:3px solid #4F2067">
        </td>
        <td style="text-align:initial;font:12px Arial;color:rgb(100,100,100);padding:0px 10px">
            <div style="margin-bottom:5px;margin-top:0px">
                <b>
                    {{$user->firstname}} {{strtoupper($user->lastname)}}</b>
                <br>
                <span>{{$user->bio_short}}, {{$_ENV['organisation_name']}}</span>
            </div>
            <table style="width:470px;margin-top:5px" width="470" border="0" cellspacing="0"
                   cellpadding="0">
                <tbody>
                <tr>
                    <td style="color:rgb(141,141,141);font-size:12px">
                        <p style="margin:0px">
 <span style="display:inline-block">
Tél. : <a href="tel:{{urlencode($_ENV['organisation_phone'])}}" style="color:#4F2067;text-decoration:none;font-family:sans-serif" target="_blank">{{$_ENV['organisation_phone']}}</a>
Mobile : <a href="tel:{{urlencode($user->phoneFmt)}}" style="color:#4F2067;text-decoration:none;font-family:sans-serif" target="_blank">{{$user->phoneFmt}}</a>
</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="color:#4F2067;font-size:12px">
                        <p style="margin:0px">
 <span style="white-space:nowrap;display:inline-block">
<a href="{{$user->website}}" style="color:#4F2067;text-decoration:none;font-family:sans-serif"
   target="_blank">{{$user->website}}</a>
</span>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            <div style="margin-top:10px">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr style="padding-top:10px">
                        <td align="left" style="padding-right:5px;text-align:center;padding-top:0px">
                            @if($user->twitter)
                            <a href="http://twitter.com/{{$user->twitter}}" target="_blank">
                                <img style="border-radius:0px;border:0px"
                                     src="https://dn3tzca2xtljm.cloudfront.net/social_icons/16px/twitter.png">
                            </a>
                                @endif
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>
