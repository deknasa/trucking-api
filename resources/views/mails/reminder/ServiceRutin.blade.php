<!DOCTYPE html>
<html>
<head>
    @include('bootstrap')
</head>
<body>
    <div class="container">
        <p>
            <strong>{{json_decode($data)[0]->judul}} </strong>
            (Report generated on : {{date('d-m-Y H:i:s')}})
        </p>
        <table>
            <tr>
                <th class="colNum" style="width:50px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">No</th>
                <th style="text-align:center; width:100px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">No Pol</th>
                <th style="text-align:center; min-width:100px; max-width:200px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Tanggal Service Terakhir</th>
                <th style="text-align:center; min-width:100px; max-width:200px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Tanggal Service Berikutnya</th>
                <th style="text-align:center; min-width: 80px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Keterangan</th>
            </tr>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td class="colNum" style="border: 1px solid black; color:black; padding: 8px;" >{{$loop->iteration}}</td>
                <td style="width:100px; border: 1px solid black; color:black; padding: 8px;">{{$sim->kodetrado}}</td>
                <td style="min-width:100px; max-width:200px; border: 1px solid black; color:black; padding: 8px;" >{{$sim->tanggaldari}}</td>
                <td style="min-width:100px; max-width:200px; border: 1px solid black; color:black; padding: 8px;" >{{$sim->tanggalsampai}}</td>
                <td style="min-width: 80px; border: 1px solid black; color:black; padding: 8px;">{{$sim->keterangan}}</td>
            </tr>
            @endforeach
        </table>
        
        <div class="text" style="line-height: 2em;  color:black; font-family: Arial, sans-serif; font-size: 14px;">

            <p>Email ini dikirimkan secara otomatis melalui system.</p>
            <p>Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
            <ul class="star-list">
                <li class="red-text">* Untuk Service yang telah dilakukan harap dientry di Form Service In.</li>
                <li class="red-text">* Service In dapat dilanjutkan dengan SPK maupun tanpa SPK.</li>
                <li class="red-text">* Untuk Trado yang masih dalam tahap Standarisasi harap diinfokan.</li>
                <li class="red-text">* Untuk Trado yang sudah selesai Standarisasi harap diinfokan juga.</li>
            </ul>
            
            <br>
            <p>Thx & Regards</p>
            <p>IT Pusat</p>

        </div>
        
    </div>
</body>
</html>
