<table border="0" cellspacing="0" cellpadding="0" width="470" style="width:355px">
    <tbody>
    <tr valign="top">
        <td style="width:100px;padding-right:10px">
            <img src="{{$picture_url}}"
                 width="100" alt="photo"
                 style="border-radius:3px;width:100px;max-width:120px">
        </td>
        <td style="border-right:5px solid #4F2067"></td>
        <td style="text-align:initial;font:12px Arial;color:rgb(100,100,100);padding:0px 10px">
            <div style="margin-bottom:5px;margin-top:0px">
                <b>{{htmlentities($user->firstname)}} {{htmlentities(strtoupper($user->lastname))}}</b>, {{$user->bio_short}}
                <br/>
            </div>
            <table style="width:355px;margin-top:5px" width="355" border="0" cellspacing="0"
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
                <table border="0" width="355" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr style="padding-top:10px">
                        <td width="70%" align="left"
                            style="font:12px Arial;color:rgb(100,100,100);padding-right:5px;text-align:left;padding-top:0px">
                            <span><b>{{htmlentities($_ENV['organisation_name'])}}</b></span><br/>
                            <span>
                    {{htmlentities($user->location->address)}}
                                <br/>{{$user->location->zipcode}} {{htmlentities($user->location->city->name)}}
                    </span>
                            <p style="margin:0px">
 <span style="white-space:nowrap;display:inline-block">
<a href="{{$url}}" style="color:#4F2067;text-decoration:none;font-family:sans-serif"
   target="_blank">{{$url}}</a>
</span>
                            </p>

                        </td>
                        <td width="30%" align="right" style="padding-right:5px;text-align:right;padding-top:0px">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="33%">
                                        @if($twitter)
                                            <a href="https://twitter.com/{{$twitter}}" target="_blank"><img
                                                        style="border-radius:0px;border:0px" alt="Twitter"
                                                        src="{{url('/img/twitter-logo-button-2.png')}}"></a>
                                        @endif
                                    </td>
                                    <td width="33%">
                                        @if($facebook)
                                            <a href="{{$facebook}}" target="_blank"><img
                                                        style="border-radius:0px;border:0px" alt="Facebook"
                                                        src="{{url('/img/facebook-logo-button-2.png')}}"></a>
                                        @endif
                                    </td>
                                    <td width="34%">
                                        <a href="https://etincelle.rocks/with/{{$user->slug}}" target="_blank"><img
                                                    style="border-radius:0px;border:0px" alt="Etincelle.rocks"
                                                    src="{{url('/img/icon-etincelle-24.png')}}"></a>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<br/><p style="font:12px Arial;color:rgb(100,100,100);font-family:sans-serif">À la recherche de talents ? Trouvez les
    dans notre <a
            href="http://etincelle.rocks/" style="color:#4F2067;text-decoration:none;font-family:sans-serif">réseau de
        coworkers</a> ! </p>
