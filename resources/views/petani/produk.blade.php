@extends('layouts.petani')

@section('content')
<style>
  [x-cloak] {
    display: none !important;
  }
  .tl-wizard {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #fafaf5;
    display: flex;
    justify-content: center;
  }
  .tl-panel {
    width: 100%;
    max-width: 576px;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #fafaf5;
    color: #172c23;
  }
  .tl-head {
    background: white;
    padding: 20px 16px;
    border-bottom: 1px solid #dfe8dc;
    flex-shrink: 0;
  }
  .tl-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 20px;
    font-weight: 700;
  }
  .tl-steps {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 4px;
    margin-top: 28px;
  }
  .tl-step {
    text-align: center;
    font-size: 11px;
    line-height: 1.35;
    color: #63816c;
  }
  .tl-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    margin: 0 auto 10px;
    border-radius: 50%;
    background: #e9f0e5;
    font-weight: 700;
    font-size: 14px;
  }
  .tl-circle.active {
    background: #ffbd00;
    box-shadow: 0 0 0 5px #fff0be;
    color: #25352a;
  }
  .tl-circle.done {
    background: #286441;
    color: white;
  }
  .tl-main {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 28px 16px;
  }
  .tl-main h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
  }
  .tl-muted {
    color: #718575;
    font-size: 14px;
  }
  .tl-field {
    display: block;
    margin-top: 24px;
    font-size: 14px;
    font-weight: 600;
  }
  .tl-input {
    display: block;
    width: 100%;
    margin-top: 9px;
    padding: 13px 14px;
    background: white;
    border: 1px solid #dce6d7;
    border-radius: 12px;
    color: #172c23;
    font-weight: 400;
  }
  .tl-footer {
    background: white;
    border-top: 1px solid #dce6d7;
    padding: 16px;
    padding-bottom: max(16px, env(safe-area-inset-bottom));
    display: flex;
    gap: 12px;
    flex-shrink: 0;
  }
  .tl-btn {
    border: 1px solid #dce6d7;
    border-radius: 12px;
    padding: 13px 20px;
    font-weight: 600;
    background: white;
  }
  .tl-next {
    background: #ffbd00;
    border-color: #ffbd00;
    flex: 1;
  }
  .tl-next:disabled {
    background: #fff0b5;
    border-color: #fff0b5;
    color: #a5a89d;
    cursor: not-allowed;
  }
  .tl-counter {
    display: flex;
    align-items: center;
    padding: 7px;
    background: #edf1e9;
    border-radius: 12px;
    margin-top: 9px;
    gap: 8px;
  }
  .tl-counter button {
    width: 42px;
    height: 42px;
    background: white;
    border-radius: 8px;
    font-size: 24px;
  }
  .tl-counter input {
    width: 100%;
    min-width: 0;
    text-align: center;
    background: transparent;
    border: 0;
  }
  .tl-shortcuts {
    display: flex;
    gap: 10px;
    margin: 24px 0;
  }
  .tl-shortcuts button {
    flex: 1;
    border: 1px solid #dce6d7;
    border-radius: 10px;
    padding: 12px 3px;
    background: white;
    font-size: 14px;
  }
  .tl-shortcuts button.selected {
    background: #285f41;
    color: white;
    border-color: #285f41;
  }
  .tl-review {
    background: white;
    border: 1px solid #dce6d7;
    border-radius: 16px;
    overflow: hidden;
    margin-top: 24px;
  }
  .tl-review img {
    width: 100%;
    height: 160px;
    object-fit: cover;
  }
  .tl-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 11px 16px;
    font-size: 14px;
  }
  .tl-row span:first-child {
    color: #718575;
  }
  .tl-row span:last-child {
    font-weight: 600;
    text-align: right;
    overflow-wrap: anywhere;
    max-width: 64%;
  }
  .tl-error {
    color: #b42318;
    font-size: 14px;
    margin-top: 12px;
  }
  .tl-preview {
    height: 160px;
    width: 100%;
    object-fit: cover;
    border-radius: 12px;
    margin-top: 16px;
  }
  .tl-expiring {
    color: #92400e;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 8px 10px;
  }
  .tl-expired {
    color: #b42318;
    background: #fff1f0;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 8px 10px;
  }
</style>
<script>
  function produkPage() {
    return {
      // Data dari Petani\ProdukController@index (database).
      produk: @js($produk),
      kategori: @js($kategori),
      kabupatenPetani: @js($kabupatenPetani),
      terverifikasi: @js($terverifikasi),
      url: {
        simpan: @js(route('petani.produk.store')),
        ubah: @js(route('petani.produk.update', ['produk' => '__ID__'])),
        tersedia: @js(route('petani.produk.tersedia', ['produk' => '__ID__'])),
        hapus: @js(route('petani.produk.destroy', ['produk' => '__ID__'])),
      },
      csrf: @js(csrf_token()),
      // TODO(tersedia_sampai): tabel produk belum punya kolom tersedia_sampai.
      // Langkah "Tersedia sampai" dan label kedaluwarsa disembunyikan selama nilai ini false.
      // Kalau Bayu menambah kolom date nullable `tersedia_sampai`, ubah ke true lalu simpan nilainya di ProdukController.
      fiturTersediaSampai: false,
      menyimpan: false,
      formTambah: false,
      formUbah: false,
      editId: null,
      edit: null,
      baru: null,
      step: 1,
      pesan: @js(session('sukses') ?? session('gagal') ?? ''),
      error: "",
      fotoError: "",
      fotoLoading: false,
      editFotoLoading: false,
      editFotoError: "",
      reader: null,
      editReader: null,
      session: 0,
      editSession: 0,
      awal: "",
      overflowAwal: null,
      todayKey: "",
      dayTimer: null,
      editDateError: "",
      editDateBadInput: false,
      get langkah() {
        return this.fiturTersediaSampai
          ? ["Foto & Nama", "Harga & Stok", "Lokasi & Panen", "Tersedia sampai", "Tinjau"]
          : ["Foto & Nama", "Harga & Stok", "Lokasi & Panen", "Tinjau"];
      },
      get stepTinjau() {
        return this.langkah.length;
      },
      init() {
        if (this.pesan) this.notify(this.pesan, 3500);
        this.refreshToday();
        this.dayTimer = setInterval(() => this.refreshToday(), 60000);
        this.$watch("formTambah", (v) => {
          if (v) {
            this.overflowAwal = document.body.style.overflow;
            document.body.style.overflow = "hidden";
          } else if (this.overflowAwal !== null) {
            document.body.style.overflow = this.overflowAwal;
            this.overflowAwal = null;
          }
        });
      },
      destroy() {
        if (this.dayTimer !== null) {
          clearInterval(this.dayTimer);
          this.dayTimer = null;
        }
        if (this.overflowAwal !== null)
          document.body.style.overflow = this.overflowAwal;
        if (this.reader) this.reader.abort();
        if (this.editReader) this.editReader.abort();
      },
      kosong() {
        return {
          nama: "",
          kategori_id: this.kategori.length ? this.kategori[0].id : "",
          harga: 10000,
          stok: 50,
          min: 5,
          grade: "",
          kabupaten: this.kabupatenPetani,
          periode: "",
          tanggalPanen: "",
          tersedia: true,
          gambar: "",
          file: null,
          tersediaSampai: "",
        };
      },
      bukaTambah() {
        this.session++;
        this.baru = this.kosong();
        this.awal = JSON.stringify(this.baru);
        this.step = 1;
        this.error = "";
        this.fotoError = "";
        this.fotoLoading = false;
        this.formTambah = true;
      },
      batal() {
        if (
          (JSON.stringify(this.baru) !== this.awal || this.fotoLoading) &&
          !confirm("Batalkan dan buang perubahan produk?")
        )
          return;
        this.session++;
        if (this.reader) this.reader.abort();
        this.reader = null;
        this.fotoLoading = false;
        this.formTambah = false;
        this.baru = null;
        this.error = "";
      },
      today() {
        return new Intl.DateTimeFormat("en-CA", {
          timeZone: "Asia/Makassar",
          year: "numeric",
          month: "2-digit",
          day: "2-digit",
        })
          .formatToParts(new Date())
          .reduce((a, p) => {
            a[p.type] = p.value;
            return a;
          }, {});
      },
      // The reactive key makes every date-dependent binding refresh after midnight.
      refreshToday() {
        const p = this.today();
        this.todayKey = `${p.year}-${p.month}-${p.day}`;
      },
      hariIni() {
        if (this.todayKey) return this.todayKey;
        const p = this.today();
        return `${p.year}-${p.month}-${p.day}`;
      },
      hariKedaluwarsa(s) {
        return Math.round(
          (this.epoch(s) - this.epoch(this.hariIni())) / 86400000,
        );
      },
      labelKedaluwarsa(s) {
        return Number.isFinite(this.epoch(s))
          ? this.tanggal(s)
          : "Belum diatur";
      },
      peringatanKedaluwarsa(s) {
        const n = this.hariKedaluwarsa(s);
        if (!Number.isFinite(n) || n > 3) return "";
        if (n < 0) return "Sudah kedaluwarsa";
        if (n === 0) return "Kedaluwarsa hari ini";
        return `Mendekati kedaluwarsa: ${n} hari lagi`;
      },
      epoch(s) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s || "")) return NaN;
        const [y, m, d] = s.split("-").map(Number);
        const t = Date.UTC(y, m - 1, d);
        return new Date(t).toISOString().slice(0, 10) === s ? t : NaN;
      },
      shortcut(n) {
        this.baru.tersediaSampai = new Date(
          this.epoch(this.hariIni()) + n * 86400000,
        )
          .toISOString()
          .slice(0, 10);
      },
      sisa() {
        return Math.round(
          (this.epoch(this.baru.tersediaSampai) - this.epoch(this.hariIni())) /
            86400000,
        );
      },
      tanggal(s) {
        const t = this.epoch(s);
        return Number.isFinite(t)
          ? new Intl.DateTimeFormat("id-ID", {
              dateStyle: "long",
              timeZone: "UTC",
            }).format(new Date(t))
          : "—";
      },
      positif(v) {
        return (
          String(v).trim() !== "" &&
          Number.isSafeInteger(Number(v)) &&
          Number(v) > 0
        );
      },
      valid(s) {
        const b = this.baru;
        if (!b) return false;
        if (s === 1)
          return !!b.nama.trim() && !this.fotoLoading && !this.fotoError;
        if (s === 2)
          return (
            this.kategori.some((k) => String(k.id) === String(b.kategori_id)) &&
            ["harga", "stok", "min"].every((k) => this.positif(b[k])) &&
            Number(b.min) <= Number(b.stok)
          );
        if (s === 3)
          return (
            !!b.periode.trim() &&
            (b.tanggalPanen === "" || Number.isFinite(this.epoch(b.tanggalPanen)))
          );
        if (s === 4 && !this.fiturTersediaSampai) return true;
        if (s === 4)
          return (
            Number.isFinite(this.epoch(b.tersediaSampai)) &&
            b.tersediaSampai >= this.hariIni()
          );
        return (
          !this.fotoLoading &&
          !this.fotoError &&
          [1, 2, 3, 4].every((k) => this.valid(k))
        );
      },
      lanjut() {
        if (this.valid(this.step) && this.step < this.stepTinjau) {
          this.step++;
          this.error = "";
        }
      },
      counter(k, d) {
        const v = Number(this.baru[k]);
        this.baru[k] = Math.max(1, (Number.isSafeInteger(v) ? v : 0) + d);
      },
      foto(e, mode) {
        const input = e.target,
          f = input.files && input.files[0],
          isEdit = mode === "edit",
          target = isEdit ? this.edit : this.baru;
        if (!f || !target) return;
        const loading = isEdit ? "editFotoLoading" : "fotoLoading",
          err = isEdit ? "editFotoError" : "fotoError",
          readerKey = isEdit ? "editReader" : "reader";
        if (this[readerKey]) this[readerKey].abort();
        this[loading] = false;
        this[err] = "";
        const types = ["image/jpeg", "image/png", "image/webp", "image/gif"];
        if (!types.includes(f.type) || f.size > 5 * 1024 * 1024) {
          this[err] = "Pilih JPG, PNG, WebP, atau GIF maksimal 5 MB.";
          input.value = "";
          return;
        }
        const token = isEdit ? this.editSession : this.session,
          r = new FileReader();
        this[readerKey] = r;
        this[loading] = true;
        const current = () =>
          this[readerKey] === r &&
          (isEdit ? this.editSession : this.session) === token &&
          (isEdit ? this.edit : this.baru) === target;
        r.onload = () => {
          if (!current()) return;
          if (
            typeof r.result === "string" &&
            /^data:image\/(jpeg|png|webp|gif);base64,/.test(r.result)
          ) {
            target.gambar = r.result;
            target.file = f;
          } else this[err] = "Foto tidak dapat dibaca.";
          this[loading] = false;
        };
        r.onerror = () => {
          if (current()) {
            this[err] = "Gagal membaca foto. Silakan coba lagi.";
            this[loading] = false;
          }
        };
        r.onabort = () => {
          if (current()) this[loading] = false;
        };
        r.readAsDataURL(f);
      },
      onFotoBaru(e) {
        this.foto(e, "baru");
      },
      onFotoUbah(e) {
        this.foto(e, "edit");
      },
      namaKategori(id) {
        const k = this.kategori.find((x) => String(x.id) === String(id));
        return k ? k.nama : "-";
      },
      formData(d, metode) {
        const fd = new FormData();
        if (metode) fd.append("_method", metode);
        fd.append("nama", d.nama.trim());
        fd.append("kategori_id", d.kategori_id);
        fd.append("harga", d.harga);
        fd.append("stok", d.stok);
        fd.append("min", d.min);
        fd.append("periode", (d.periode || "").trim());
        fd.append("grade", d.grade || "");
        fd.append("tanggal_panen", d.tanggalPanen || "");
        if (d.file) fd.append("foto", d.file);
        return fd;
      },
      async kirim(url, body, metode = "POST") {
        const res = await fetch(url, {
          method: metode,
          headers: { Accept: "application/json", "X-CSRF-TOKEN": this.csrf, "X-Requested-With": "XMLHttpRequest" },
          body,
          credentials: "same-origin",
        });
        let data = {};
        try {
          data = await res.json();
        } catch (e) {}
        if (!res.ok) {
          const errs = data.errors ? Object.values(data.errors).flat() : [];
          throw new Error(
            errs[0] || data.message || (res.status === 419 ? "Sesi habis. Muat ulang halaman lalu coba lagi." : "Gagal menyimpan. Silakan coba lagi."),
          );
        }
        return data;
      },
      async simpanTambah() {
        if (this.step !== this.stepTinjau || this.menyimpan) return false;
        for (let s = 1; s < this.stepTinjau; s++) {
          if (!this.valid(s)) {
            this.step = s;
            this.error = "Lengkapi isian yang valid sebelum menyimpan.";
            return false;
          }
        }
        this.menyimpan = true;
        this.error = "";
        try {
          await this.kirim(this.url.simpan, this.formData(this.baru));
          this.session++;
          window.location.reload(); // pesan sukses tampil dari session setelah dimuat ulang
          return true;
        } catch (e) {
          this.error = e.message;
          return false;
        } finally {
          this.menyimpan = false;
        }
      },
      notify(s, ms = 2000) {
        this.pesan = s;
        setTimeout(() => {
          if (this.pesan === s) this.pesan = "";
        }, ms);
      },
      async setTersedia(p, on) {
        if (!this.terverifikasi) {
          this.notify("Akun belum terverifikasi. Status produk belum bisa diubah.", 3500);
          return;
        }
        const lama = p.tersedia;
        p.tersedia = on; // ubah dulu di layar, kembalikan kalau server menolak
        const fd = new FormData();
        fd.append("_method", "PATCH");
        fd.append("aktif", on ? "1" : "0");
        try {
          const data = await this.kirim(this.url.tersedia.replace("__ID__", p.id), fd);
          this.notify(data.message || "Status produk diperbarui");
        } catch (e) {
          p.tersedia = lama;
          this.notify(e.message, 3500);
        }
      },
      bukaUbah(p) {
        this.editSession++;
        this.editId = p.id;
        this.edit = {
          ...p,
          file: null,
          tersediaSampai: p.tersediaSampai || "",
        };
        this.editDateError = "";
        this.editDateBadInput = false;
        this.editFotoLoading = false;
        this.editFotoError = "";
        this.formUbah = true;
      },
      tutupUbah() {
        this.editSession++;
        if (this.editReader) this.editReader.abort();
        this.editFotoLoading = false;
        this.formUbah = false;
      },
      async simpanUbah() {
        if (this.editFotoLoading || this.menyimpan) return;
        // TODO(tersedia_sampai): validasi tanggal kedaluwarsa hanya berlaku kalau fiturnya aktif.
        if (
          this.fiturTersediaSampai &&
          (this.editDateBadInput ||
            (this.edit.tersediaSampai !== "" &&
              !Number.isFinite(this.epoch(this.edit.tersediaSampai))))
        ) {
          this.editDateError =
            "Masukkan tanggal kedaluwarsa yang valid atau kosongkan.";
          return false;
        }
        this.editDateError = "";
        const fd = this.formData(this.edit, "PUT");
        fd.append("tersedia", this.edit.tersedia ? "1" : "0");
        this.menyimpan = true;
        this.editFotoError = "";
        try {
          await this.kirim(this.url.ubah.replace("__ID__", this.editId), fd);
          window.location.reload();
        } catch (e) {
          this.editFotoError = e.message;
        } finally {
          this.menyimpan = false;
        }
      },
      async hapus(id) {
        const p = this.produk.find((x) => x.id === id);
        if (!p || !confirm("Hapus produk " + p.nama + "?")) return;
        const fd = new FormData();
        fd.append("_method", "DELETE");
        try {
          await this.kirim(this.url.hapus.replace("__ID__", id), fd);
          this.produk = this.produk.filter((x) => x.id !== id);
          this.notify("Produk dihapus");
        } catch (e) {
          this.notify(e.message, 3500);
        }
      },
      jumlah() {
        return this.produk.length;
      },
      aktif() {
        return this.produk.filter((p) => p.tersedia && Number(p.stok) > 0).length;
      },
      habis() {
        return this.produk.filter((p) => !p.tersedia || Number(p.stok) <= 0).length;
      },
      rupiah(v) {
        return "Rp " + Number(v).toLocaleString("id-ID") + " / kg";
      },
    };
  }
</script>
<div class="max-w-5xl mx-auto px-4 py-6 space-y-5" x-data="produkPage()">
  <div x-show="!formTambah">
    <header class="mb-4">
        <h1 class="text-2xl font-extrabold">Kelola Produk</h1>
    </header>
    <p class="text-sm text-text-muted mb-5">Atur produk yang Anda jual</p>
    @unless ($terverifikasi)
      <div class="mb-5 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
        <p class="font-semibold">Akun Anda belum terverifikasi</p>
        <p class="mt-1">
          Produk baru bisa ditambah atau diubah setelah admin menyetujui verifikasi akun
          (status saat ini: {{ str_replace('_', ' ', $statusVerifikasi) }}).
        </p>
      </div>
    @endunless
    <div class="grid grid-cols-3 gap-3 mb-5">
      <div class="bg-white border border-border rounded-2xl p-4 text-center">
        <p class="text-2xl font-semibold text-gray-900" x-text="jumlah()"></p>
        <p class="text-sm text-text-muted">Jumlah produk</p>
      </div>
      <div class="bg-white border border-border rounded-2xl p-4 text-center">
        <p class="text-2xl font-semibold text-gray-900" x-text="aktif()"></p>
        <p class="text-sm text-text-muted">Aktif</p>
      </div>
      <div class="bg-white border border-border rounded-2xl p-4 text-center">
        <p class="text-2xl font-semibold text-gray-900" x-text="habis()"></p>
        <p class="text-sm text-text-muted">Stok habis</p>
      </div>
    </div>
    <button
      type="button"
      @click="bukaTambah()"
      :disabled="!terverifikasi"
      class="w-full bg-yellow-400 text-gray-900 font-semibold rounded-xl py-3 mb-5 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      + Tambah Produk Baru
    </button>
    <p x-show="produk.length === 0" class="rounded-2xl border border-border bg-white p-6 text-center text-sm text-text-muted">
      Belum ada produk. Tekan "Tambah Produk Baru" untuk mulai berjualan.
    </p>
    <div class="grid md:grid-cols-2 gap-4">
      <template x-for="p in produk" :key="p.id"
        ><div class="bg-white border border-border rounded-2xl overflow-hidden">
          <img
            x-show="p.gambar"
            :src="p.gambar || null"
            :alt="p.nama"
            class="h-40 w-full object-cover"
          />
          <div class="p-4 space-y-2 text-sm">
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Nama Produk</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="p.nama"></span
                ></span
              >
            </div>
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Kategori</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="p.kategori"></span
                ></span
              >
            </div>
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Harga (per kg)</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="rupiah(p.harga)"></span
                ></span
              >
            </div>
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Stok (kg)</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="p.stok + ' kg'"></span
                ></span
              >
            </div>
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Minimal pesan (kg)</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="p.min + ' kg'"></span
                ></span
              >
            </div>
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Kabupaten</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="p.kabupaten || 'Belum diatur di Akun'"></span
                ></span
              >
            </div>
            <div class="flex justify-between gap-2">
              <span class="text-text-muted">Periode Panen</span
              ><span class="font-medium text-gray-900 text-right max-w-[60%]"
                ><span x-text="p.periode || '-'"></span
                ></span
              >
            </div>
            <div x-show="p.tanggalPanen" class="flex justify-between gap-2">
              <span class="text-text-muted">Tanggal Panen</span>
              <span class="font-medium text-gray-900 text-right max-w-[60%]" x-text="tanggal(p.tanggalPanen)"></span>
            </div>
            <div x-show="p.grade" class="flex justify-between gap-2">
              <span class="text-text-muted">Grade</span>
              <span class="font-medium text-gray-900 text-right max-w-[60%]" x-text="p.grade"></span>
            </div>
            {{-- TODO(tersedia_sampai): tampil lagi kalau kolom tersedia_sampai sudah ada --}}
            <div x-show="fiturTersediaSampai" class="flex justify-between gap-2">
              <span class="text-text-muted">Kedaluwarsa</span>
              <span
                class="font-medium text-gray-900 text-right max-w-[60%]"
                x-text="labelKedaluwarsa(p.tersediaSampai)"
              ></span>
            </div>
            <p
              x-show="fiturTersediaSampai && peringatanKedaluwarsa(p.tersediaSampai)"
              x-text="peringatanKedaluwarsa(p.tersediaSampai)"
              :class="hariKedaluwarsa(p.tersediaSampai) < 0 ? 'tl-expired' : 'tl-expiring'"
              role="status"
              aria-live="polite"
            ></p>
            <div class="flex items-center justify-between pt-2">
              <span
                :class="p.tersedia ? 'text-primary' : 'text-text-muted'"
                x-text="p.tersedia ? 'Tersedia' : 'Tidak tersedia'"
              ></span
              ><button
                type="button"
                @click="setTersedia(p,!p.tersedia)"
                :class="p.tersedia ? 'bg-primary' : 'bg-gray-300'"
                class="relative w-11 h-6 rounded-full"
                role="switch"
                :aria-checked="p.tersedia"
                aria-label="Ketersediaan produk"
              >
                <span
                  :class="p.tersedia ? 'translate-x-5' : 'translate-x-0'"
                  class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow"
                ></span>
              </button>
            </div>
            <button
              type="button"
              @click="bukaUbah(p)"
              :disabled="!terverifikasi"
              class="w-full mt-2 disabled:opacity-50 disabled:cursor-not-allowed border border-green-700 text-green-700 rounded-xl py-2 font-medium hover:bg-green-50"
            >
              Ubah Produk
            </button>
            <button
              type="button"
              @click="hapus(p.id)"
              class="w-full mt-2 text-red-600 font-medium py-2"
            >
              Hapus Produk
            </button>
          </div>
        </div></template
      >
    </div>
  </div>
  <template x-if="formTambah && baru"
    ><section
      class="tl-wizard"
      role="dialog"
      aria-modal="true"
      aria-labelledby="tl-heading"
      @keydown.escape.prevent.stop="batal()"
    >
      <div class="tl-panel">
        <header class="tl-head">
          <div class="tl-title">
            <h2 id="tl-heading">Tambah Produk Baru</h2>
            <button type="button" @click="batal()" class="text-red-600 text-sm">
              Batal
            </button>
          </div>
          <ol class="tl-steps" :style="'grid-template-columns: repeat(' + langkah.length + ', minmax(0, 1fr))'">
            <template x-for="(label,i) in langkah" :key="i"
              ><li class="tl-step" :aria-current="step===i+1 ? 'step' : null">
                <span
                  class="tl-circle"
                  :class="{'active':step===i+1,'done':step>i+1}"
                  x-text="step>i+1?'✓':i+1"
                ></span
                ><span
                  x-text="label"
                  class="block mx-auto max-w-[65px]"
                ></span></li
            ></template>
          </ol>
        </header>
        <main class="tl-main">
          <p x-show="error" x-text="error" class="tl-error" role="alert"></p>
          <div x-show="step===1">
            <label class="tl-field" for="tl-foto"
              >Foto Produk
              <span class="tl-muted">(boleh dikosongkan)</span></label
            ><input
              id="tl-foto"
              x-ref="fotoInput"
              class="sr-only"
              tabindex="-1"
              type="file"
              accept="image/jpeg,image/png,image/webp,image/gif"
              @change="onFotoBaru($event)"
            /><button
              type="button"
              class="tl-btn w-full mt-3"
              @click="$refs.fotoInput.click()"
              aria-controls="tl-foto"
            >
              Pilih Foto dari Perangkat
            </button>
            <p class="tl-muted mt-2">
              JPG, PNG, WebP, atau GIF. Maksimal 5 MB.
            </p>
            <p x-show="fotoLoading" role="status" class="tl-muted">
              Memuat foto…
            </p>
            <p
              x-show="fotoError"
              x-text="fotoError"
              class="tl-error"
              role="alert"
            ></p>
            <img
              x-show="baru.gambar"
              :src="baru.gambar || null"
              alt="Pratinjau foto produk"
              class="tl-preview"
            /><label class="tl-field" for="tl-nama">Nama Produk *</label
            ><input
              id="tl-nama"
              class="tl-input"
              x-model="baru.nama"
              placeholder="Contoh: Tomat Cherry Segar"
              required
            />
          </div>
          <div x-show="step===2">
            <label class="tl-field" for="tl-kategori">Kategori *</label
            ><select id="tl-kategori" class="tl-input" x-model="baru.kategori_id">
              <template x-for="k in kategori" :key="k.id"
                ><option :value="k.id" x-text="k.nama" :selected="String(k.id) === String(baru.kategori_id)"></option
              ></template></select
            ><label class="tl-field" for="tl-grade">Grade <span class="tl-muted">(opsional)</span></label
            ><select id="tl-grade" class="tl-input" x-model="baru.grade">
              <option value="">Tanpa grade</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
            </select
            ><label class="tl-field" for="tl-harga">Harga per kg (Rp) *</label>
            <div class="tl-counter">
              <button
                type="button"
                @click="counter('harga',-1000)"
                aria-label="Kurangi Harga per kg (Rp)"
              >
                −</button
              ><input
                id="tl-harga"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                x-model="baru.harga"
                required
              /><button
                type="button"
                @click="counter('harga',1000)"
                aria-label="Tambah Harga per kg (Rp)"
              >
                +
              </button>
            </div>
            <label class="tl-field" for="tl-stok">Stok (kg) *</label>
            <div class="tl-counter">
              <button
                type="button"
                @click="counter('stok',-1)"
                aria-label="Kurangi Stok (kg)"
              >
                −</button
              ><input
                id="tl-stok"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                x-model="baru.stok"
                required
              /><button
                type="button"
                @click="counter('stok',1)"
                aria-label="Tambah Stok (kg)"
              >
                +
              </button>
            </div>
            <label class="tl-field" for="tl-min">Minimal pesan (kg) *</label>
            <div class="tl-counter">
              <button
                type="button"
                @click="counter('min',-1)"
                aria-label="Kurangi Minimal pesan (kg)"
              >
                −</button
              ><input
                id="tl-min"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                x-model="baru.min"
                required
              /><button
                type="button"
                @click="counter('min',1)"
                aria-label="Tambah Minimal pesan (kg)"
              >
                +
              </button>
            </div>
            <p class="tl-error" x-show="Number(baru.min)>Number(baru.stok)">
              Minimal pesan tidak boleh melebihi stok.
            </p>
          </div>
          <div x-show="step===3">
            {{-- Tabel produk tidak punya kolom kabupaten: lokasi diambil dari profil_petani (diubah di menu Akun). --}}
            <p class="tl-field">Kabupaten / Kota</p>
            <p class="tl-input" x-text="kabupatenPetani || 'Belum diatur'"></p>
            <p class="tl-muted mt-2">
              Lokasi mengikuti profil usaha tani Anda.
              <a href="{{ url('/petani/akun') }}" class="font-semibold text-green-700 underline">Ubah di Akun</a>
            </p
            ><label class="tl-field" for="tl-periode">Periode Panen *</label
            ><input
              id="tl-periode"
              type="text"
              class="tl-input"
              x-model="baru.periode"
              placeholder="Contoh: Panen tiap 2 minggu"
              required
            /><label class="tl-field" for="tl-panen">Tanggal Panen <span class="tl-muted">(opsional)</span></label
            ><input
              id="tl-panen"
              type="date"
              class="tl-input"
              x-model="baru.tanggalPanen"
            />
          </div>
          {{-- TODO(tersedia_sampai): langkah ini disembunyikan sampai kolom tersedia_sampai ada di tabel produk. --}}
          <div x-show="fiturTersediaSampai && step===4">
            <p class="tl-field" id="tl-date-heading">
              Produk tersedia sampai kapan? *
            </p>
            <div class="tl-shortcuts">
              <template x-for="n in [3,7,14]" :key="n"
                ><button
                  type="button"
                  @click="shortcut(n)"
                  :class="{'selected':sisa()===n}"
                  :aria-pressed="sisa()===n"
                  x-text="n===3 ? '3 hari' : (n===7 ? '1 minggu' : '2 minggu')"
                ></button
              ></template>
            </div>
            <label class="tl-muted block" for="tl-tanggal"
              >Atau pilih tanggal di kalender</label
            ><input
              id="tl-tanggal"
              type="date"
              class="tl-input"
              x-model="baru.tersediaSampai"
              :min="hariIni()"
              required
            />
            <div x-show="valid(4)" class="mt-5">
              <p
                class="tl-muted"
                x-text="'Tersedia sampai '+tanggal(baru.tersediaSampai)+' (Sisa '+sisa()+' hari)'"
              ></p>
            </div>
          </div>
          <div x-show="step===stepTinjau">
            <div class="tl-review">
              <img
                x-show="baru.gambar"
                :src="baru.gambar || null"
                alt="Foto produk"
              />
              <div class="tl-row">
                <span>Nama Produk</span><span x-text="baru.nama.trim()"></span>
              </div>
              <div class="tl-row">
                <span>Kategori</span><span x-text="namaKategori(baru.kategori_id)"></span>
              </div>
              <div class="tl-row" x-show="baru.grade">
                <span>Grade</span><span x-text="baru.grade"></span>
              </div>
              <div class="tl-row">
                <span>Harga per kg</span
                ><span x-text="rupiah(baru.harga)"></span>
              </div>
              <div class="tl-row">
                <span>Stok</span><span x-text="baru.stok+' kg'"></span>
              </div>
              <div class="tl-row">
                <span>Minimal pesan</span><span x-text="baru.min+' kg'"></span>
              </div>
              <div class="tl-row">
                <span>Kabupaten / Kota</span
                ><span x-text="kabupatenPetani || 'Belum diatur'"></span>
              </div>
              <div class="tl-row">
                <span>Periode Panen</span
                ><span x-text="baru.periode.trim()"></span>
              </div>
              <div class="tl-row" x-show="baru.tanggalPanen">
                <span>Tanggal Panen</span><span x-text="tanggal(baru.tanggalPanen)"></span>
              </div>
              <div class="tl-row" x-show="fiturTersediaSampai">
                <span>Tersedia sampai</span
                ><span
                  x-text="tanggal(baru.tersediaSampai)+' (Sisa '+sisa()+' hari)'"
                ></span>
              </div>
              <div class="tl-row">
                <span>Status</span><span x-text="'Tersedia'"></span>
              </div>
            </div>
          </div>
        </main>
        <footer class="tl-footer">
          <button
            type="button"
            class="tl-btn flex-1"
            @click="step===1 ? batal() : (step--,error='')"
            x-text="step===1?'Batal':'Kembali'"
          ></button
          ><button
            x-show="step<stepTinjau"
            type="button"
            class="tl-btn tl-next"
            :disabled="!valid(step)"
            @click="lanjut()"
          >
            Lanjut</button
          ><button
            x-show="step===stepTinjau"
            type="button"
            class="tl-btn tl-next"
            :disabled="!valid(5) || menyimpan"
            @click="simpanTambah()"
          >
            <span x-text="menyimpan ? 'Menyimpan…' : 'Simpan Produk'"></span>
          </button>
        </footer>
      </div>
    </section></template
  >
  <div
    x-show="formUbah"
    x-cloak
    class="fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-4"
    @keydown.escape.prevent.stop="tutupUbah()"
  >
    <div
      class="bg-white w-full sm:max-w-md rounded-2xl p-5 space-y-3 max-h-[90vh] overflow-y-auto"
      @click.outside="tutupUbah()"
    >
      <div class="grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2">
        <button type="button" @click="tutupUbah()" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
        <h2 class="text-center text-lg font-semibold">Ubah Produk</h2>
        <span aria-hidden="true"></span>
      </div>
      <template x-if="edit"
        ><div class="space-y-3">
          <label class="block text-sm font-medium" for="edit-foto"
            >Foto Produk</label
          ><input
            id="edit-foto"
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
            @change="onFotoUbah($event)"
            class="w-full text-sm"
          /><img
            x-show="edit.gambar"
            :src="edit.gambar || null"
            alt="Foto produk"
            class="mt-2 h-32 w-full object-cover rounded-xl"
          />
          <p
            x-show="editFotoError"
            x-text="editFotoError"
            class="tl-error"
            role="alert"
          ></p>
          <p x-show="editFotoLoading" role="status">Memuat foto…</p>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-nama"
              >Nama Produk</label
            ><input
              id="edit-nama"
              type="text"
              x-model="edit.nama"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-kategori"
              >Kategori</label
            ><select
              id="edit-kategori"
              x-model="edit.kategori_id"
              class="w-full border border-border rounded-xl px-4 py-2"
            >
              <template x-for="k in kategori" :key="k.id"
                ><option :value="k.id" x-text="k.nama" :selected="String(k.id) === String(edit.kategori_id)"></option
              ></template>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-grade">Grade (opsional)</label
            ><select id="edit-grade" x-model="edit.grade" class="w-full border border-border rounded-xl px-4 py-2">
              <option value="">Tanpa grade</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-harga"
              >Harga per kg (Rp)</label
            ><input
              id="edit-harga"
              type="number"
              x-model="edit.harga"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-stok"
              >Stok (kg)</label
            ><input
              id="edit-stok"
              type="number"
              x-model="edit.stok"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-min"
              >Minimal pesan (kg)</label
            ><input
              id="edit-min"
              type="number"
              x-model="edit.min"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-kabupaten"
              >Kabupaten</label
            ><p
              id="edit-kabupaten"
              class="w-full border border-border rounded-xl px-4 py-2 bg-gray-50 text-text-muted"
              x-text="(kabupatenPetani || 'Belum diatur') + ' · ubah di menu Akun'"
            ></p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-periode"
              >Periode Panen</label
            ><input
              id="edit-periode"
              type="text"
              x-model="edit.periode"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="edit-panen">Tanggal Panen (opsional)</label
            ><input
              id="edit-panen"
              type="date"
              x-model="edit.tanggalPanen"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
          </div>
          {{-- TODO(tersedia_sampai): field ini disembunyikan sampai kolom tersedia_sampai ada. --}}
          <div x-show="fiturTersediaSampai">
            <label
              class="block text-sm font-medium mb-1"
              for="edit-kedaluwarsa"
            >
              Kedaluwarsa (opsional)
            </label>
            <input
              id="edit-kedaluwarsa"
              type="date"
              x-model="edit.tersediaSampai"
              @input="editDateBadInput = $event.target.validity.badInput; editDateError = ''"
              :aria-invalid="!!editDateError || editDateBadInput"
              aria-describedby="edit-date-help edit-date-error"
              class="w-full border border-border rounded-xl px-4 py-2"
            />
            <p id="edit-date-help" class="tl-muted mt-2">
              Kosongkan jika belum diatur. Tanggal lampau tetap dapat disimpan.
            </p>
            <p
              id="edit-date-error"
              x-show="editDateError"
              x-text="editDateError"
              class="tl-error"
              role="alert"
            ></p>
          </div>
          <div class="flex items-center justify-between">
            <span x-text="edit.tersedia?'Tersedia':'Tidak tersedia'"></span
            ><button
              type="button"
              @click="edit.tersedia=!edit.tersedia"
              :class="edit.tersedia?'bg-primary':'bg-gray-300'"
              class="relative w-11 h-6 rounded-full"
              role="switch"
              :aria-checked="edit.tersedia"
              aria-label="Ketersediaan produk"
            >
              <span
                :class="edit.tersedia?'translate-x-5':'translate-x-0'"
                class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow"
              ></span>
            </button>
          </div>
          <div class="flex gap-2 pt-2">
            <button
              type="button"
              @click="tutupUbah()"
              class="flex-1 border border-border rounded-xl py-2"
            >
              Batal</button
            ><button
              type="button"
              @click="simpanUbah()"
              :disabled="editFotoLoading || menyimpan"
              class="flex-1 bg-primary text-white rounded-xl py-2 disabled:opacity-60"
            >
              <span x-text="menyimpan ? 'Menyimpan…' : 'Simpan'"></span>
            </button>
          </div>
        </div></template
      >
    </div>
  </div>
  <div
    x-show="pesan"
    x-cloak
    x-transition
    class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl z-50"
    role="status"
    x-text="pesan"
  ></div>
</div>
@endsection
