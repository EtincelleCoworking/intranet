@foreach($ressources as $ressource)
    <div class="label" style="{{$ressource->labelCss}}; text-wrap: none">
        <input type="checkbox" name="filter_ressource_{{$ressource->id}}"
               id="filter_ressource_{{$ressource->id}}" value="{{$ressource->id}}"
               checked="checked"/>
        <label for="filter_ressource_{{$ressource->id}}"
               style="font-weight: 600;" title="{{$ressource->description}}">{{$ressource->name}}
            ({{ number_format($ressource->amount, 0, ',', '.') }}€ HT/h)
        </label>

        @if($ressource->url)
            <a href="{{$ressource->url}}" target="_blank" class="fa-stack">
                <i class="fa fa-circle fa-stack-2x"></i>
                <i class="fa fa-question fa-stack-1x fa-inverse"></i>
            </a>
        @endif
    </div>

@endforeach