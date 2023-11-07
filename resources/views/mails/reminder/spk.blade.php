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
                <th style="text-align:center; min-width:100px; max-width:200px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Gudang</th>
                <th style="text-align:center; min-width:100px; max-width:150px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Tanggal</th>
                <th style="text-align:center; width:150px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">No PG</th>
                <th style="text-align:center; min-width: 80px; border: 1px solid black; padding: 8px; background-color: #f2f2f2;">Kode Ban</th>
            </tr>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td class="colNum" style="border: 1px solid black; color:black; padding: 8px;" >{{$loop->iteration}}</td>
                <td style="min-width:100px; max-width:200px; border: 1px solid black; color:black; padding: 8px;">{{$sim->gudang}}</td>
                <td style="min-width:100px; max-width:1500px; border: 1px solid black; color:black; padding: 8px;">{{$sim->tanggal}}</td>
                <td style="width:150px; border: 1px solid black; color:black; padding: 8px;">{{$sim->nopg}}</td>
                <td style="min-width: 80px; border: 1px solid black; color:black; padding: 8px;" >{{$sim->kodeban}}</td>
            </tr>
            @endforeach
        </table>
        
        <div class="text" style="line-height: 2em;  color:black; font-family: Arial, sans-serif; font-size: 14px;">
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
            <br>
            <p>Thx & Regards</p>
            <p>IT Pusat</p>
        </div>
        
    </div>
</body>
</html>
