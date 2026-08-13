@for ($i = 0; $i < max(count($buyers), count($sellers)); $i++)
    <tr>
        @if (isset($buyers[$i]))
            <td class="text-center" style="width:1%;white-space:nowrap;"><x-broker-badge :code="$buyers[$i]['code']" /></td>
            <td class="text-center">{{ $buyers[$i]['valRaw'] }}</td>
            <td class="text-center">{{ $buyers[$i]['volRaw'] }}</td>
            <td class="text-center">{{ $buyers[$i]['avgRaw'] }}</td>
            <td class="text-center text-muted">{{ $buyers[$i]['freq'] }}</td>
        @else
            <td colspan="5"></td>
        @endif
        @if (isset($sellers[$i]))
            <td class="text-center" style="width:1%;white-space:nowrap;"><x-broker-badge :code="$sellers[$i]['code']" /></td>
            <td class="text-center">{{ $sellers[$i]['valRaw'] }}</td>
            <td class="text-center">{{ $sellers[$i]['volRaw'] }}</td>
            <td class="text-center">{{ $sellers[$i]['avgRaw'] }}</td>
            <td class="text-center text-muted">{{ $sellers[$i]['freq'] }}</td>
        @else
            <td colspan="5"></td>
        @endif
    </tr>
@endfor
