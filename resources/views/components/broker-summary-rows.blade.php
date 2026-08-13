@for ($i = 0; $i < max(count($buyers), count($sellers)); $i++)
    <tr>
        @if (isset($buyers[$i]))
            <td>{{ $buyers[$i]['code'] }}</td>
            <td>{{ $buyers[$i]['type'] }}</td>
            <td class="text-center">{{ $buyers[$i]['volRaw'] }}</td>
            <td class="text-center">{{ $buyers[$i]['valRaw'] }}</td>
            <td class="text-center">{{ $buyers[$i]['avgRaw'] }}</td>
            <td class="text-center">{{ $buyers[$i]['freq'] }}</td>
        @else
            <td colspan="6"></td>
        @endif
        @if (isset($sellers[$i]))
            <td>{{ $sellers[$i]['code'] }}</td>
            <td>{{ $sellers[$i]['type'] }}</td>
            <td class="text-center">{{ $sellers[$i]['volRaw'] }}</td>
            <td class="text-center">{{ $sellers[$i]['valRaw'] }}</td>
            <td class="text-center">{{ $sellers[$i]['avgRaw'] }}</td>
            <td class="text-center">{{ $sellers[$i]['freq'] }}</td>
        @else
            <td colspan="6"></td>
        @endif
    </tr>
@endfor
