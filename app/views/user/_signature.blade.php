<table border="0" cellspacing="0" cellpadding="0" width="470" style="width:470px">
    <tbody>
    <tr valign="top">
        <td style="width:10px;padding-right:10px">
            <img src="{{URL::asset($user->getAvatarUrl(100))}}"
                 width="65" alt="photo"
                 style="border-radius:3px;width:100px;max-width:120px">
        </td>
        <td style="border-right:5px solid #4F2067">
        </td>
        <td style="text-align:initial;font:12px Arial;color:rgb(100,100,100);padding:0px 10px">
            <div style="margin-bottom:5px;margin-top:0px">
                <b>{{$user->firstname}} {{strtoupper($user->lastname)}}</b>, {{$user->bio_short}}<br/>
            </div>
            <table style="width:470px;margin-top:5px" width="470" border="0" cellspacing="0"
                   cellpadding="0">
                <tbody>
                <tr>
                    <td style="color:rgb(141,141,141);font-size:12px">
                        <p style="margin:0px">
 <span style="display:inline-block">
T&eacute;l. : <span style="color:#4F2067;font-family:sans-serif">{{$_ENV['organisation_phone']}}</span>
     @if($user->phone)
         Mobile : <span style="color:#4F2067;font-family:sans-serif">{{$user->phoneFmt}}</span>
     @endif
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
                        <td width="80%" align="left" style="font:12px Arial;color:rgb(100,100,100);padding-right:5px;text-align:left;padding-top:0px">
                            <span><b>{{$_ENV['organisation_name']}}</b></span><br/>
                            <span>
                    {{$user->location->address}}
                                <br/>{{$user->location->zipcode}} {{$user->location->city->name}}
                    </span>
                            <p style="margin:0px">
 <span style="white-space:nowrap;display:inline-block">
<a href="{{$url}}" style="color:#4F2067;text-decoration:none;font-family:sans-serif"
   target="_blank">{{$url}}</a>
</span>
                            </p>

                        </td>
                        <td width="20%" align="right" style="padding-right:5px;text-align:right;padding-top:0px">
                            @if($twitter)
                                <a href="https://twitter.com/{{$twitter}}" target="_blank">
                                    <img style="border-radius:0px;border:0px"
                                         src="{{URL::asset('/img/twitter-logo-button-2.png')}}">
                                </a>
                            @endif
                            @if($facebook)
                                <a href="{{$facebook}}" target="_blank">
                                    <img style="border-radius:0px;border:0px"
                                         src="{{URL::asset('/img/facebook-logo-button-2.png')}}">
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
