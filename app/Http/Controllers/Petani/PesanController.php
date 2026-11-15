<?php

namespace App\Http\Controllers\Petani;

use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Percakapan petani dengan pembeli (pengusaha).
 * Hanya menampilkan dan membalas percakapan milik petani yang login.
 * Membuat percakapan baru tidak dilakukan di sini (biasanya dimulai dari sisi pembeli).
 */
class PesanController extends PetaniController
{
    public function index(Request $request): View
    {
        $user = $this->petani($request);

        return view('petani.pesan', [
            'percakapan' => $this->daftarPercakapan($user),
            'percakapanAktif' => null,
            'pesan' => [],
        ]);
    }

    public function show(Request $request, int $percakapan): View
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        $this->tandaiDibaca($model, $user);

        $model->load([
            'pengusaha.profilPengusaha',
            'pesanan.item',
            'pesan.pengirim',
        ]);

        return view('petani.pesan', [
            'percakapan' => $this->daftarPercakapan($user),
            'percakapanAktif' => $this->ringkasPercakapan($model, $user),
            'pesan' => $model->pesan->map(fn (Pesan $m) => $this->ringkasPesan($m, $user, $model))->values(),
        ]);
    }

    public function kirim(Request $request, int $percakapan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        $data = $request->validate([
            'isi' => ['required', 'string', 'max:2000'],
        ], $this->pesanValidasi(), [
            'isi' => 'Pesan',
        ]);

        $isi = trim($data['isi']);
        if ($isi === '') {
            return back()->withErrors(['isi' => 'Pesan wajib diisi.'])->withInput();
        }

        Pesan::query()->create([
            'percakapan_id' => $model->id,
            'pengirim_id' => $user->id,
            'isi' => $isi,
        ]);

        $model->update(['pesan_terakhir_pada' => now()]);

        return redirect()
            ->route('petani.pesan.show', $model->id)
            ->with('sukses', 'Pesan terkirim.');
    }

    /** 404 kalau tidak ada, 403 kalau percakapan milik petani lain. */
    private function percakapanMilik(int $id, User $user): Percakapan
    {
        $percakapan = Percakapan::query()->find($id);

        return $this->pastikanMilik($percakapan, $user, 'petani_id');
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function daftarPercakapan(User $user)
    {
        $daftar = Percakapan::query()
            ->with(['pengusaha.profilPengusaha', 'pesanan.item'])
            ->withCount([
                'pesan as belum_dibaca' => fn ($q) => $q->whereNull('dibaca_pada')->where('pengirim_id', '!=', $user->id),
            ])
            ->where('petani_id', $user->id)
            ->orderByDesc('pesan_terakhir_pada')
            ->orderByDesc('id')
            ->get();

        $terakhir = $this->pesanTerakhir($daftar->pluck('id'));
        $daftar->each(function (Percakapan $p) use ($terakhir) {
            $p->setRelation('pesan', collect($terakhir->get($p->id) ? [$terakhir->get($p->id)] : []));
        });

        return $daftar->map(fn (Percakapan $p) => $this->ringkasPercakapan($p, $user))->values();
    }

    /** @param  \Illuminate\Support\Collection<int, mixed>  $ids */
    private function pesanTerakhir($ids)
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        $idTerakhir = Pesan::query()
            ->selectRaw('MAX(id) as id')
            ->whereIn('percakapan_id', $ids)
            ->groupBy('percakapan_id')
            ->pluck('id');

        return Pesan::query()
            ->whereIn('id', $idTerakhir)
            ->get()
            ->keyBy('percakapan_id');
    }

    /** @return array<string, mixed> */
    private function ringkasPercakapan(Percakapan $p, User $user): array
    {
        $terakhir = $p->relationLoaded('pesan')
            ? $p->pesan->sortByDesc('id')->first()
            : $p->pesan()->orderByDesc('id')->first();

        $waktuSumber = $p->pesan_terakhir_pada ?: $terakhir?->created_at;
        $baru = (int) ($p->belum_dibaca ?? 0) > 0;

        return [
            'id' => $p->id,
            'nama' => $this->namaPengusaha($p->pengusaha),
            'foto' => $this->urlFile($p->pengusaha?->foto),
            'produk' => $p->pesanan?->item?->first()?->nama_produk ?: '',
            'cuplikan' => $terakhir?->isi ? $this->potong($terakhir->isi, 80) : 'Belum ada pesan.',
            'waktu' => $this->waktuDaftar($waktuSumber),
            'baru' => $baru,
            'status' => $this->labelStatus($p->pesanan?->status),
        ];
    }

    /** @return array<string, mixed> */
    private function ringkasPesan(Pesan $m, User $user, Percakapan $percakapan): array
    {
        $milikSaya = (int) $m->pengirim_id === (int) $user->id;
        $namaPengirim = $milikSaya
            ? 'Anda'
            : $this->namaPengusaha($m->pengirim ?: $percakapan->pengusaha);

        return [
            'id' => $m->id,
            'isi' => $m->isi,
            'milikSaya' => $milikSaya,
            'pengirim' => $namaPengirim,
            'waktu' => $this->waktuWita($m->created_at, 'H:i'),
        ];
    }

    private function tandaiDibaca(Percakapan $percakapan, User $user): void
    {
        Pesan::query()
            ->where('percakapan_id', $percakapan->id)
            ->whereNull('dibaca_pada')
            ->where('pengirim_id', '!=', $user->id)
            ->update(['dibaca_pada' => now()]);
    }

    private function namaPengusaha($user): string
    {
        if (! $user) {
            return 'Pembeli';
        }

        $namaUsaha = trim((string) ($user->profilPengusaha?->nama_usaha ?? ''));

        return $namaUsaha !== '' ? $namaUsaha : (trim((string) $user->name) ?: 'Pembeli');
    }

    private function labelStatus(?string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => 'Belum dibayar',
            'menunggu_konfirmasi' => 'Menunggu konfirmasi',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'ditolak' => 'Ditolak',
            default => 'Terbuka',
        };
    }

    private function waktuDaftar($waktu): string
    {
        if (! $waktu) {
            return '';
        }

        $wita = \Illuminate\Support\Carbon::parse($waktu)->timezone(self::ZONA_WAKTU);
        $hariIni = now()->timezone(self::ZONA_WAKTU)->isSameDay($wita);

        return $this->waktuWita($waktu, $hariIni ? 'H:i' : 'j M');
    }

    private function potong(string $teks, int $batas): string
    {
        $teks = trim(preg_replace('/\s+/u', ' ', $teks) ?? $teks);

        return mb_strlen($teks) > $batas ? mb_substr($teks, 0, $batas - 1).'…' : $teks;
    }
}
