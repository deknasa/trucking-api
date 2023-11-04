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
        <table id="box-table" >
            <tr>
                <th class="tbl-no">No</th>
                <th>No Pol</th>
                <th>Tanggal Ganti Terakhir</th>
                <th>Batas Ganti (KM)</th>
                <th>KM Berjalan	</th>
                <th>Keterangan</th>
            </tr>
            @foreach (json_decode($data) as $sim)
            <tr  style="background-color: {{$sim->warna}}; ">
                <td  class="tbl-no">{{$loop->iteration}}</td>
                <td style="width:150px;">{{$sim->kodetrado}}</td>
                <td>{{$sim->tanggal}}</td>
                <td style="text-align:right">{{$sim->batasganti}}</td>
                <td style="text-align:right">{{$sim->kberjalan}}</td>
                <td>{{$sim->Keterangan}}</td>
            </tr>
            @endforeach
        </table>
        <div class="text">

            <p style="margin-top:0; margin-bottom:0; line-height:.5"><br/></p>
            --
            <p><font color="Black" font Bold="false"><b>ALUR PROSEDUR OPNAME SERVICE</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false"><b>I. DOORSMER/STEAM</b> adalah tahap awal sebelum masuk point service lebih lanjut , dimana tujuannya</p>
                <p><font color="Black" font Bold="true">&nbsp;&nbsp;&nbsp;&nbsp;mobil sudah bersih untuk memudahkan mekanik melihat & meneliti</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false"><b>II. BAGIAN MESIN</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">1. Point 1 dilakukan sejalan dengan <b>Cuci Radiator</b> dengan cara buka lubang pembuangan air bagian bawah radiator ,</p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;isi air dengan selang colokan ke mulut radiator , buka baut pembuangan air pada mesin , </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;<b>3 hal</b> tersebut dilakukan sambil mesin dinyalakan selama 15 menit</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">2. Pengecekan Ikatan ring pengikat <b>Selang Radiator bawah</b> serta kondisi selang tersebut</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">3. <b>Introgasi Supir</b> ybs atas keluhan apa yang mau disampaikan</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">4. Pengecekan <b>Kebocoran/Merembes Oli</b> sekecil apapun pada bagian mesin atau bagian yang vital untuk ditindaklanjutin</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">5. Pengecekan fungsi kerja angin pada <b>Kompresor</b> untuk mengetahui apakah ada kebocoran </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;dan pengisian penuh angin ke tangki apakah lancar atau tidak</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">6. <b>Ganti Oli</b> sesuai kapasitas mesin dan lakukan pengukuran untuk diberi tanda pada </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;"Media Pengukur Oli" ( karena kemumgkinan sudah bukan bawaan asli mobil ) , </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;agar pengecekan hariannya lebih akurat atas kecukupan oli mesin</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">7. Ganti <b>Saringan Oli & Saringan Solar</b> , harap perhatikan bekas sisa yang tersaring untuk dianalisa </p>
            <p><font color="Black" font Bold=false">&nbsp;&nbsp;&nbsp;&nbsp;kondisi mesin apakah ada mengandung <b>Gram</b> dan kwalitas solar</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">8. Pembersihan  <b>Saringan Teh</b> pada injection pump</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">9. Pengecekan <b>Belt/Tali Kipas</b> serta Media  <b>Penutup</b> kipas berfungsi baik guna angin/udara yang dihasilkan </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;maksimal mengarah ke <b>Radiator</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">10. Pembukaan <b>Water pump</b> pengecekan kondisi Bearing/Lakher , lakukan grease ulang dengan grease bagus Atau </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;jika kurang bagus kondisi Bearing/Lakher ganti.</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NB : setiap <b>Service Rutin</b> WAJIB di Pispot/ngeFat</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">11. Pembersihan <b>Saringan Hawa</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">12. Pembersihan <b>Saringan Valve Mainbrance</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">13. Pengecekan kecukupan <b>Oli Hidraulik & Minyak Rem</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">14. Pengecekan <b>Master Klos Atas & Bawah</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">15. Pengecekan  & Penyetelan Tinggi / Rendah <b>Pijakan Kopling</b> , jika sisa penyetelan <b>sisa 1/4 atau 25%</b> maka'
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;segera lakukan penggantian <b>Kanvas kopling</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">16. Pengecekan Karet Gantungan Kopling. </p>'
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false"><b>III. PEKERJAAN BAGIAN KOLONG meliputi HEAD & TRAILLER</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">1. Pengurasan & Pencucian <b>Tangki Solar</b> ( supaya  unit tidak terhambat operasional disarankan mempunyai Tangki Solar Serap )</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">2. Pengecekan kondisi <b>KING PEN</b> </p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">3. Pembersihan <b>Bearing /Lakher semua Roda Head & Trailler</b>dengan mencabut Ban komplit (Tanpa buka baut roda ) , </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;Lakukan Grase Ulang dengan Grease khusus atau Ganti jika kondisi sudah dikhawatirkan</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">4. Pengecekan <b>Kanvas Rem , Jember Rem dan Stel Ulang</b> pada Head & Trailler</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NB : <b>KHUSUS</b> unit sistem Rem Minyak WAJIB ganti <b>Karet Rem</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">5. Pengecekan Fungsi kerja <b>Handling Jack </b>pada gandengan dan lakukan tambahan Grease biasa </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;jika ada kurang (buka bagian atas tutup )</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">6. Lakukan <b>Pispot/nge Fat</b> pada semua bagian <b>Media lubang pispot</b> pada Head & Trailler , </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;jika ada media yang gak ada harap dipasangkan kembali</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">7. Pengolesan tambahan Grease biasa pada <b>Tapak Gandengan</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">8. Pengecekan bagian <b>PER</b> atas  Daun Per, Boosing Per , Bohel & Baut As tengah Per serta Stabilizer Gandengan</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">9. Pengecekan & Pengikatan kembali <b>Baut yang kendor</b> dan pengadaan kembali <b>baut yang hilang</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">10. Pengecekan kelengkapan <b>baut roda & Pengencangan</b> kembali <b>baut roda</b> Head & Trailler</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">11. Pengecekan <b>Tekanan Angin</b> semua Ban Head & Trailler termasuk Ban Serap</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false"><b>IV. BAGIAN KELISTRIKAN</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">1. Pengecekan <b>Aki</b> meliputi Penambahan Air Aki , Pengencangan ikatan Kabel +/- , Pembersihan jamur/karat pada kepala Aki</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">2. Pengecekan <b>Sekring</b> lampu Besar , lampu Sen , lampu Atrek mundur , lampu Rotary </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;dan lampu gandengan serta <b>Sekring bagian Vital</b> lainnya</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">3. Pengecekan & Pembersihan <b>Rumah Kunci Start</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">4. Pengecekan kabel <b>Relay Start.</b></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">5. Pengecekan <b>Dinamo Starter</b> , cek kondisi Karbon Brush dan Gigi Bendit</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false">6. Pengecekan Alternator/Dinamo Ampere  sbb:</p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Cek <b>Indikator di Dashboard</b> mobil apabila berkedip maka tidak ada pengisian aki dan bisa jadi </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;terdapat kerusakan pada komponen alternator.</p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Jangan menambah <b>beban listrik</b> yang berlebihan pada mobil , karena dapat memperpendek umur alternator </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dan juga umur aki.</p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Segera perbaiki ( Jangan Menunda) <b>komponen alternator yang rusak</b> supaya tidak menimbulkan </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;erusakan pada komponen lainnya.</p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Mengecek <b>kabel pengisian listrik</b>dari alternator ke aki mobil dikarenakan sangat </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rentan berkarat oleh kotor dan air, akibatnya kabel menjadi getas dan korosi.</p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Pengecekan <b>Bearing/Lakher</b>, lakukan Grease ulang dengan Grease kualitas baik , jika kondisi </p>
            <p><font color="Black" font Bold="false">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;menghawatirkan sebaiknya diganti bearing/lakher baru.</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:0"><br/></p>
            <p><font color="Black" font Bold="false"><b>UNIT SETELAH SELESAI OPNAME SERVICE WAJIB DI TEST DRIVE DILAPANGAN UNTUK PENGECEKAN </b></p>
            <p><font color="Black" font Bold="false"><b>LANJUTAN KHUSUSNYA BAGIAN REM YANG SUDAH DISETEL ULANG GUNA MEMASTIKAN BERFUNGSI DENGAN BAIK.</b></p>
            --		
                    <p><font color="Black" font Bold="False">Email ini dikirimkan secara otomatis melalui system.</p>
                    <p><font color="Black" font Bold="False">Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
                    <p style="margin-top:0; margin-bottom:0; line-height:.5"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:.5"><br/></p>
                    <p style="margin-top:0; margin-bottom:0; line-height:.5"><br/></p>
                    <p><font color="Black">Thx & Regards</p>
                    <p><font color="Black">IT Pusat</p>

        {{ config('app.name') }}
    </div>
        
    </div>
</body>
</html>
