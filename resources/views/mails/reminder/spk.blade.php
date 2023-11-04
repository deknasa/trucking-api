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
                <th>Gudang</th>
                <th>Tanggal</th>
                <th>No PG</th>
                <th>Kode Ban</th>
            </tr>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td>{{$loop->iteration}}</td>
                <td>{{$sim->gudang}}</td>
                <td>{{$sim->tanggal}}</td>
                <td>{{$sim->nopg}}</td>
                <td>{{$sim->kodeban}}</td>
            </tr>
            @endforeach
        </table>
        
        <h4>Kepada seluruh Pengurus Cabang</h4>
        <p>Disampaikan perihal menggunakan Jasa Vulkanisir ban ada beberapa hal yang harus diperhatikan sbb :</p>
        <ol>
            <li>
                Menggunakan Jasa Vulkanisir dari Pabrik bukan Agen
            </li>
            <li>
                Meminta Garansi kepada Pabrik atas hasil Jasa Vulkan yang terkelupas
            </li>
            <li>
                Meminta Garansi kepada Pabrik atas hasil Jasa Vulkan yang masa pemakaian tidak wajar karena ada campuran bahan karet oplosan
            </li>
            <li>
                Meminta Tanggung Jawab atas bahan Ban yang sudah dijemput
            </li>
        </ol>
        
        

        NB:
        <ol>
            <li>
                Ban kondisi sisa 2 mm ( meskipun bagian lainnya masih diatas 2 mm ) WAJIB di Ganti & Vulkanisir
            </li>
            <li>
                Pada point 1, kalu diketemukan ketipisan yang tidak merata harap dilakukan pengecekan terhadap As, Bearing/Lakher/Setelan As, Kanvas Rem , Velg, King Pen , Per dan juga Penyetelan Rem harus merata fungsinya
     
            </li>
           
        </ol>
       
        <p>Email ini dikirimkan secara otomatis melalui system.</p>
        <p>Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
        <p>Thx & Regards</p>
        <p>IT Pusat</p>
        
        {{ config('app.name') }}
        
    </div>
</body>
</html>
