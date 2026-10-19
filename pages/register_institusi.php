<main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
    <main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
    
    <!-- TAMBAHKAN KODE NOTIFIKASI INI -->
    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200">
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>
    <!-- SAMPAI SINI -->

    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-slate-900">Daftar Institusi / Lembaga</h1>
            <p class="text-sm text-slate-500">Bergabunglah sebagai organisasi berbadan hukum.</p>
        </div>

        <form action="index.php" method="POST" class="space-y-4">
            <input type="hidden" name="aksi_register_institusi" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-bold mb-1">Nama Lengkap Lembaga</label>
        <input type="text" name="nama_institusi" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl" placeholder="Cth: LSM Kompas (Koordinat Masyarakat...">
    </div>
    <div>
        <label class="block text-sm font-bold mb-1">Nama Singkat Lembaga</label>
        <input type="text" name="nama_pendek" required maxlength="50" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl" placeholder="Cth: LSM KOMPAS">
    </div>
</div>
                    <label class="block text-sm font-bold mb-1">Jenis Institusi</label>
                    <select name="jenis_institusi" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl bg-white">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Lembaga Non Profit">Lembaga Non Profit / LSM / Yayasan</option>
                        <option value="Pemerintah">Pemerintah Pusat / Kementerian / Badan</option>
                        <option value="Pemerintah">Pemerintah Provinsi / Dinas / Badan</option>
                        <option value="Pemerintah">Pemerintah Kab/Kota / Dinas / Badan</option>
                        <option value="Pemerintah">Pemerintah Desa</option>
                        <option value="Pendidikan">Pendidikan (Sekolah/Kampus)</option>
                        <option value="BUMN/BUMD">BUMN</option>
                        <option value="BUMN/BUMD">BUMD</option>
                        <option value="Swasta">Perusahaan Swasta / CSR</option>
                        <option value="Swasta">Koperasi</option>
                        <option value="Pendidikan">TNI</option>
                        <option value="Pendidikan">POLRI</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-slate-100 pt-4 mt-4">
                <div><label class="block text-sm font-bold mb-1">Nama Ketua/Pimpinan</label><input type="text" name="nama_ketua" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
                <div><label class="block text-sm font-bold mb-1">Nama Sekretaris</label><input type="text" name="nama_sekretaris" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
                <div><label class="block text-sm font-bold mb-1">Nama Bendahara</label><input type="text" name="nama_bendahara" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-bold mb-1">Email Lembaga</label><input type="email" name="email_lembaga" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
                <div><label class="block text-sm font-bold mb-1">No. HP / WA PIC (Admin)</label><input type="tel" name="telepon_pic" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            </div>

            <!-- Wilayah Dropdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Provinsi</label>
                    <select name="provinsi" id="provinsi" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl bg-white"><option value="">Pilih Provinsi</option></select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Kota / Kabupaten</label>
                    <select name="kota" id="kota" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl bg-white"><option value="">Pilih Kota</option></select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Kecamatan</label>
                    <select name="kecamatan" id="kecamatan" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl bg-white"><option value="">Pilih Kecamatan</option></select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Kelurahan / Desa</label>
                    <select name="kelurahan" id="kelurahan" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl bg-white"><option value="">Pilih Kelurahan</option></select>
                </div>
            </div>

            <hr class="border-slate-100 my-4">
            <div><label class="block text-sm font-bold mb-1">Email Akses Login (PIC)</label><input type="email" name="email_login" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-bold mb-1">Password</label><input type="password" name="password" required minlength="6" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
                <div><label class="block text-sm font-bold mb-1">Konfirmasi Password</label><input type="password" name="password_confirm" required minlength="6" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            </div>

            <button type="submit" class="w-full py-4 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition shadow-lg mt-4">Daftar Institusi</button>
        </form>
    </div>
</main>

<script>
    // Script API Wilayah Indonesia (Sama seperti form individu)
    const apiWilayah = 'https://www.emsifa.com/api-wilayah-indonesia/api';
    
    fetch(`${apiWilayah}/provinces.json`).then(res => res.json()).then(data => {
        let opt = '<option value="">Pilih Provinsi</option>';
        data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
        document.getElementById('provinsi').innerHTML = opt;
    });

    document.getElementById('provinsi').addEventListener('change', function() {
        const id = this.value; if(!id) return;
        fetch(`${apiWilayah}/regencies/${id}.json`).then(res => res.json()).then(data => {
            let opt = '<option value="">Pilih Kota</option>';
            data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
            document.getElementById('kota').innerHTML = opt;
        });
    });

    document.getElementById('kota').addEventListener('change', function() {
        const id = this.value; if(!id) return;
        fetch(`${apiWilayah}/districts/${id}.json`).then(res => res.json()).then(data => {
            let opt = '<option value="">Pilih Kecamatan</option>';
            data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
            document.getElementById('kecamatan').innerHTML = opt;
        });
    });

    document.getElementById('kecamatan').addEventListener('change', function() {
        const id = this.value; if(!id) return;
        fetch(`${apiWilayah}/villages/${id}.json`).then(res => res.json()).then(data => {
            let opt = '<option value="">Pilih Kelurahan</option>';
            data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
            document.getElementById('kelurahan').innerHTML = opt;
        });
    });
</script>