<!DOCTYPE html>
<html>
<head>
    <style>
        /* CSS untuk gaya tabel */
        *{
            color: black;
        }
        table {
            border-collapse: collapse;
            width: 75%;
            /* margin: 0 auto; */
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        /* tr:nth-child(even) {
            background-color: #f2f2f2;
        } */
        /* CSS untuk gaya kontainer email */
        .container {
            font-family: Arial, sans-serif;
            font-size: 14px;
            /* padding: 20px; */
            width:100%;
            margin: 0 auto;
        }
    </style>
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
                <th>kodetrado</th>
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
        <p>Thx & Regards</p>
        <p>IT Pusat</p>
        
        {{ config('app.name') }}
        
    </div>
</body>
</html>
