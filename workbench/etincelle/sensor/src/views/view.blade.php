<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
    </title>

</head>
<body>
<table border="1">
    <tr>
        <th>Occured At</th>
        <th>Value</th>
    </tr>
    @foreach($items as $item)
        <tr>
            <td>{{$item->occured_at}}</td>
            <td>{{$item->value}}</td>
        </tr>
    @endforeach
</table>
</body>
</html>