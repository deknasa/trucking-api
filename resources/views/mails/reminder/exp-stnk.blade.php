<!DOCTYPE html>
<html>
<head>
    @include('style')
</head>
<body>
    <div class="container">
        <p>
            <strong>{{json_decode($data)[0]->judul}} </strong>
            (Report generated on : {{date('d-m-Y H:i:s')}})
        </p>
        <table>
            <tr>
                <th>No</th>
                <th>No Pol</th>
                <th>Tgl Jatuh Tempo</th>
                <th>Keterangan</th>
            </tr>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td>{{$loop->iteration}}</td>
                <td>{{$sim->kodetrado}}</td>
                <td>{{$sim->tglstr}}</td>
                <td>{{$sim->jenis}}</td>
            </tr>
            @endforeach
        </table>

        <p>Email ini dikirimkan secara otomatis melalui system.</p>
        <p>Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
        <p>Thx & Regards</p>
        <p>IT Pusat</p>
        
        {{ config('app.name') }}
        
    </div>
</body>
</html>
