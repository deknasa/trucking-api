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
        <table style="border-collapse: collapse; width:100% color:black; font-family: Arial, sans-serif;">
            <tr>
                <th class="colNum" style="width:50px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">No</th>
                <th style="text-align:center; width:100px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">No Pol</th>
                <th style="text-align:center; min-width:100px; max-width:200px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Tanggal Ganti Terakhir</th>
                <th style="text-align:center; max-width:250px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Batas Ganti (KM)</th>
                <th style="text-align:center; max-width:250px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">KM Berjalan</th>
                <th style="text-align:center; min-width: 80px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Keterangan</th>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td class="colNum" style="border: 1px solid black; color:black; padding: 8px;" >{{$loop->iteration}}</td>
                <td style="width:100px; border: 1px solid black; color:black; padding: 8px;" >{{$sim->kodetrado}}</td>
                <td style="min-width:100px; max-width:200px; border: 1px solid black; color:black; padding: 8px;" >{{$sim->tanggal}}</td>
                <td style="max-width:250px; border: 1px solid black; color:black; padding: 8px; text-align:right">{{$sim->batasganti}}</td>
                <td style="max-width:250px; border: 1px solid black; color:black; padding: 8px; text-align:right">{{$sim->kberjalan}}</td>
                <td style="min-width: 80px; border: 1px solid black; color:black; padding: 8px;" >{{$sim->Keterangan}}</td>
            </tr>
            @endforeach
        </table>
        <div class="text" style="line-height: 2em;  color:black; font-family: Arial, sans-serif; font-size: 14px;">
            
        <p>Kepada Pengurus mohon Ingatkan & Arahkan team mekanik untuk ikutin alur Prosedur setiap Penggantian Oli Persneling & Gardan sbb :</p>

        <ol>
            <li>Pemeriksaan oli Perneling yang diganti apakah ada mengandung Gram besi dalam oli bekas tersebut atau tidak, jika ada <b>Wajib</b> dibongkar untuk pemeriksaan lebih lanjut.</li>
            <li>Terhadap point 1, jika Persneling sudah turun, <b>Wajib</b> lakukan pengecekan Bearing/Lakher <b>As klos</b>, lakukan grease ulang dengan grease bagus atau ganti saja jika ragu dengan kondisinya.</li>
            <li>Pemeriksaan bekas oli Gardan yang diganti apakah ada mengandung <b>Gram besi</b> dalam oli tersebut atau tidak, jika ada <b>Wajib</b> dibongkar untuk pemeriksaan lebih lanjut.</li>
            <li>Pembukaan <b>Dinamo Ampere </b>untuk pengecekan Bearing/Lakher, lakukan grease ulang dengan grease bagus atau ganti saja jika ragu dengan kondisinya.</li>
            <li>Pembukaan <b>Water pump</b> pengecekan kondisi Bearing/Lakher, lakukan grease ulang dengan grease bagus atau ganti saja jika ragu dengan kondisinya.</li>
        </ol>
        
        <p>Email ini dikirimkan secara otomatis melalui system.</p>
        <p>Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
        <br>
        <p>Thx & Regards</p>
        <p>IT Pusat</p>
        
        </div>
    </div>
</body>
</html>
