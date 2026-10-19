<main class="max-w-2xl mx-auto px-4 pt-28 pb-16">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-slate-900">Daftar Sebagai Individu</h1>
            <p class="text-sm text-slate-500">Bergabunglah sebagai relawan atau penggerak lingkungan.</p>
        </div>

        <form action="index.php" method="POST" class="space-y-4">
            <input type="hidden" name="aksi_register_individu" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-bold mb-1">Nama Sesuai KTP</label><input type="text" name="nama_lengkap" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
                <div><label class="block text-sm font-bold mb-1">Nama Panggilan</label><input type="text" name="nama_panggilan" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            </div>

            <div><label class="block text-sm font-bold mb-1">Nomor HP / WA</label><input type="tel" name="telepon" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            <div><label class="block text-sm font-bold mb-1">Alamat Lengkap (Jalan/RT/RW)</label><textarea name="alamat" rows="2" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></textarea></div>

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
            <div><label class="block text-sm font-bold mb-1">Email Aktif</label><input type="email" name="email" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-bold mb-1">Password</label><input type="password" name="password" required minlength="6" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
                <div><label class="block text-sm font-bold mb-1">Konfirmasi Password</label><input type="password" name="password_confirm" required minlength="6" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl"></div>
            </div>

            <button type="submit" class="w-full py-4 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-lg mt-4">Daftar Sekarang</button>
        </form>
    </div>
</main>

<script>
    // Script API Wilayah Indonesia
    const apiWilayah = 'https://www.emsifa.com/api-wilayah-indonesia/api';
    
    fetch(`${apiWilayah}/provinces.json`).then(res => res.json()).then(data => {
        let opt = '<option value="">Pilih Provinsi</option>';
        data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
        document.getElementById('provinsi').innerHTML = opt;
    });

    document.getElementById('provinsi').addEventListener('change', function() {
        const id = this.value;
        if(!id) return;
        fetch(`${apiWilayah}/regencies/${id}.json`).then(res => res.json()).then(data => {
            let opt = '<option value="">Pilih Kota</option>';
            data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
            document.getElementById('kota').innerHTML = opt;
        });
    });

    document.getElementById('kota').addEventListener('change', function() {
        const id = this.value;
        if(!id) return;
        fetch(`${apiWilayah}/districts/${id}.json`).then(res => res.json()).then(data => {
            let opt = '<option value="">Pilih Kecamatan</option>';
            data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
            document.getElementById('kecamatan').innerHTML = opt;
        });
    });

    document.getElementById('kecamatan').addEventListener('change', function() {
        const id = this.value;
        if(!id) return;
        fetch(`${apiWilayah}/villages/${id}.json`).then(res => res.json()).then(data => {
            let opt = '<option value="">Pilih Kelurahan</option>';
            data.forEach(p => opt += `<option value="${p.id}" data-nama="${p.name}">${p.name}</option>`);
            document.getElementById('kelurahan').innerHTML = opt;
        });
    });
</script>