{{-- Hitung mundur tenggat pesanan ("20 jam 55 menit"), diperbarui tiap 30 detik. Dipakai pesanan & pesanan-detail. --}}
<script>
    window.hitungMundur = window.hitungMundur || function (batasIso, awal) {
        return {
            sisa: awal,
            batas: new Date(batasIso).getTime(),
            init() {
                this.hitung();
                this._t = setInterval(() => this.hitung(), 30000);
            },
            destroy() { clearInterval(this._t); },
            hitung() {
                const detik = Math.floor((this.batas - Date.now()) / 1000);
                if (detik <= 0) { this.sisa = 'Lewat tenggat'; clearInterval(this._t); return; }
                const menitTotal = Math.ceil(detik / 60);
                const jam = Math.floor(menitTotal / 60);
                const menit = menitTotal % 60;
                this.sisa = jam > 0 ? jam + ' jam ' + menit + ' menit' : menit + ' menit';
            }
        };
    };
</script>
