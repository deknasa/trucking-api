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
            width: 50%;
            margin: 0 auto;
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
            padding: 20px;
            width:60%;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <span>
            <strong>{{json_decode($data)[0]->judul}} </strong>
            (Report generated on : {{date('d-m-Y H:i:s')}})
        </span>
        <br><br>
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

        <br>
        Email ini dikirimkan secara otomatis melalui system.
        <br>
        Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]
        <br><br>
        Thx & Regards
        <br>
        IT Pusat
        {{ config('app.name') }}
        
    </div>
</body>
</html>
