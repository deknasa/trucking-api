<!DOCTYPE html>
<html>
<head>
    @include('style')
</head>
<body>
    <div class="container">
        <p class="text">
            <strong>{{json_decode($data)[0]->judul}} </strong>
            (Report generated on : {{date('d-m-Y H:i:s')}})
        </p>
        <table>
            <tr>
                <th>No</th>
                <th>No Pol</th>
                <th>Tanggal Ganti Terakhir</th>
                <th>Batas Ganti (KM)</th>
                <th>KM Berjalan	</th>
                <th>Keterangan</th>
            </tr>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td>{{$loop->iteration}}</td>
                <td>{{$sim->kodetrado}}</td>
                <td>{{$sim->tanggal}}</td>
                <td style="text-align:right">{{$sim->batasganti}}</td>
                <td style="text-align:right">{{$sim->kberjalan}}</td>
                <td>{{$sim->Keterangan}}</td>
            </tr>
            @endforeach
        </table>
        <div class="text">
        
        
        <p>Email ini dikirimkan secara otomatis melalui system.</p>
        <p>Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
        <p>Thx & Regards</p>
        <p>IT Pusat</p>
        
        {{ config('app.name') }}
    </div>
    </div>
</body>
</html>
