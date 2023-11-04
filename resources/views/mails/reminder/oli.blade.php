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
                <td>{{$sim->kodetrado}}</td>
                <td>{{$sim->tanggal}}</td>
                <td style="text-align:right">{{$sim->batasganti}}</td>
                <td style="text-align:right">{{$sim->kberjalan}}</td>
                <td>{{$sim->Keterangan}}</td>
            </tr>
            @endforeach
        </table>
        <div class="text">

        
        <h1>ALUR PROSEDUR OPNAME SERVICE</h1>
        
        <h2>I. DOORSMER/STEAM</h2>
        <p>Adalah tahap awal sebelum masuk point service lebih lanjut, dimana tujuannya mobil sudah bersih untuk memudahkan mekanik melihat & meneliti.</p>
        
        <h2>II. BAGIAN MESIN</h2>
        <ol>
            <li>Point 1 dilakukan sejalan dengan <b>Cuci Radiator</b> dengan cara buka lubang pembuangan air bagian bawah radiator,
                isi air dengan selang colokan ke mulut radiator, buka baut pembuangan air pada mesin,
                <b>3 hal</b> tersebut dilakukan sambil mesin dinyalakan selama 15 menit
            </li>
            <li>Pengecekan Ikatan ring pengikat <b>Selang Radiator bawah</b> serta kondisi selang tersebut</li>
            <li><b>Introgasi</b> Supir ybs atas keluhan apa yang mau disampaikan</li>
            <li>Pengecekan <b>Kebocoran/Merembes</b> Oli sekecil apapun pada bagian mesin atau bagian yang vital untuk ditindaklanjutin</li>
            <li>Pengecekan fungsi kerja angin pada <b>Kompresor</b> untuk mengetahui apakah ada kebocoran
                dan pengisian penuh angin ke tangki apakah lancar atau tidak
            </li>
            <li><b>Ganti Oli</b> sesuai kapasitas mesin dan lakukan pengukuran untuk diberi tanda pada "Media Pengukur Oli" (karena kemungkinan sudah bukan bawaan asli mobil),
                agar pengecekan hariannya lebih akurat atas kecukupan oli mesin
            </li>
            <li>Ganti <b>Saringan Oli & Saringan Solar</b>, harap perhatikan bekas sisa yang tersaring untuk dianalisa
                kondisi mesin apakah ada mengandung <b>Gram</b> dan kwalitas solar
            </li>
            <li>Pembersihan <b>Saringan Teh</b> pada injection pump</li>
            <li>Pengecekan <b>Belt/Tali Kipas</b> serta Media <b>Penutup</b> kipas berfungsi baik guna angin/udara yang dihasilkan
                maksimal mengarah ke <b>Radiator</b>
            </li>
            <li>Pembukaan <b>Water pump</b> pengecekan kondisi Bearing/Lakher, lakukan grease ulang dengan grease bagus Atau jika kurang bagus kondisi Bearing/Lakher ganti.
                <p>NB: setiap <b>Service Rutin</b> WAJIB di Pispot/ngeFat</strong></p>
            </li>
            <li>Pembersihan <b>Saringan Hawa</b></li>
            <li>Pembersihan <b>Saringan Valve Mainbrance</b></li>
            <li>Pengecekan kecukupan <b>Oli Hidraulik & Minyak Rem</b></li>
            <li>Pengecekan <b>Master Klos Atas & Bawah</b></li>
            <li>Pengecekan & Penyetelan Tinggi / <b>Rendah Pijakan Kopling</b>, jika sisa penyetelan sisa <b>1/4</b> atau <b>25%</b> maka segera lakukan penggantian Kanvas kopling</li>
            <li>Pengecekan Karet Gantungan Kopling.</li>
        </ol>
        
        
        <h2>III. PEKERJAAN BAGIAN KOLONG meliputi HEAD & TRAILLER</h2>
        <ol>
            <li>Pengurasan & Pencucian <b>Tangki Solar</b> ( supaya unit tidak terhambat operasional disarankan mempunyai Tangki Solar Serap )</li>
            <li>Pengecekan kondisi KING PEN </li>
            <li>Pembersihan <b>Bearing /Lakher semua Roda Head & Trailler</b> dengan mencabut Ban komplit (Tanpa buka baut roda ), Lakukan Grase Ulang dengan Grease khusus atau Ganti jika kondisi sudah dikhawatirkan</li>
            <li>Pengecekan <b>Kanvas Rem , Jember Rem dan Stel Ulang</b> pada Head & Trailler 
                <p>NB : <b>KHUSUS</b> unit sistem Rem Minyak WAJIB ganti <b>Karet Rem</b></p>
            </li>
            <li>Pengecekan Fungsi kerja <b>Handling Jack</b> pada gandengan dan lakukan tambahan Grease biasa jika ada kurang (buka bagian atas tutup )</li>
            <li>Lakukan <b>Pispot/nge Fat</b> pada semua bagian <b>Media lubang pispot</b> pada Head & Trailler, jika ada media yang gak ada harap dipasangkan kembali</li>
            <li>Pengolesan tambahan Grease biasa pada <b>Tapak Gandengan</b></li>
            <li>Pengecekan bagian <b>PER</b> atas Daun Per, Boosing Per , Bohel & Baut As tengah Per serta Stabilizer Gandengan</li>
            <li>Pengecekan & Pengikatan kembali Baut yang kendor dan pengadaan kembali <b>baut yang hilang</b></li>
            <li>Pengecekan kelengkapan <b>baut roda & Pengencangan</b> kembali <b>baut roda Head</b> & Trailler</li>
            <li>Pengecekan <b>Tekanan Angin</b> semua Ban Head & Trailler termasuk Ban Serap</li>
        </ol>
        
        <h2>IV. BAGIAN KELISTRIKAN</h2>

        <ol>
            <li>Pengecekan <b>Aki</b> meliputi Penambahan Air Aki , Pengencangan ikatan Kabel +/- , Pembersihan jamur/karat pada kepala Aki</li>
            <li>Pengecekan <b>Sekring</b> lampu Besar , lampu Sen , lampu Atrek mundur , lampu Rotary dan lampu gandengan serta <b>Sekring bagian Vital</b> lainnya</li>
            <li>Pengecekan & Pembersihan <b>Rumah Kunci Start</b></li>
            <li>Pengecekan kabel <b>Relay Start</b></li>
            <li>Pengecekan <b>Dinamo Starter</b> , cek kondisi Karbon Brush dan Gigi Bendit</li>
            <li>Pengecekan Alternator/Dinamo Ampere sbb:
                <ul>
                    <li>Cek <b>Indikator di Dashboard</b> mobil apabila berkedip maka tidak ada pengisian aki dan bisa jadi terdapat kerusakan pada komponen alternator.</li>
                    <li>Jangan menambah <b>beban listrik</b> yang berlebihan pada mobil , karena dapat memperpendek umur alternator dan juga umur aki.</li>
                    <li>Segera perbaiki ( Jangan Menunda) <b>komponen alternator yang rusak</b> supaya tidak menimbulkanerusakan pada komponen lainnya.</li>
                    <li>Mengecek <b>kabel pengisian listrikdari</b> alternator ke aki mobil dikarenakan sangat rentan berkarat oleh kotor dan air, akibatnya kabel menjadi getas dan korosi.</li>
                    <li>Pengecekan <b>Bearing/Lakher</b>, lakukan Grease ulang dengan Grease kualitas baik , jika kondisi menghawatirkan sebaiknya diganti bearing/lakher baru.</li>
                </ul>
            </li>
        </ol>


        
        <p>UNIT SETELAH SELESAI OPNAME SERVICE WAJIB DI TEST DRIVE DILAPANGAN UNTUK PENGECEKAN LANJUTAN KHUSUSNYA BAGIAN REM YANG SUDAH DISETEL ULANG GUNA MEMASTIKAN BERFUNGSI DENGAN BAIK.</p>

        <p>Email ini dikirimkan secara otomatis melalui system.</p>
        <p>Harap jangan membalas ke email ini. [TAS_AUTO_GENERATED_EMAIL]</p>
        <p>Thx & Regards</p>
        <p>IT Pusat</p>
        
        {{ config('app.name') }}
    </div>
        
    </div>
</body>
</html>
