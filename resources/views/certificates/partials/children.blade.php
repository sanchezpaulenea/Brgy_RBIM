<table class="children" cellspacing="0" cellpadding="0">
    <tr>
        <th>NAME</th>
        <th>DATE OF BIRTH</th>
        <th>AGE</th>
    </tr>
    @forelse ($children as $child)
        <tr>
            <td>{{ $child['name'] }}</td>
            <td>{{ $child['date_of_birth'] }}</td>
            <td>{{ $child['age'] }}</td>
        </tr>
    @empty
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    @endforelse
</table>
