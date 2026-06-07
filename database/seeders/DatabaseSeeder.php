<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\Testimonial;
use App\Models\Promo;
use App\Models\Portfolio;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── SETTINGS ────────────────────────────────────────────────────────
        Setting::set('hero_title', 'Wujudkan Karya Tulis Menjadi Buku Profesional');
        Setting::set('hero_subtitle', 'Media Fikra hadir sebagai mitra penerbitan terpercaya.');
        Setting::set('contact_wa', '6282332975294');
        Setting::set('contact_email', 'info@mediafikra.com');
        // ─── SUPERADMIN ──────────────────────────────────────────────────────
        User::create([
            'name' => 'Superadmin Media Fikra',
            'email' => 'admin@mediafikra.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'status_aktif' => true,
        ]);

        // ─── BOOKS ───────────────────────────────────────────────────────────
        $books = [
            [
                'judul' => 'Menulis Buku Pertamamu',
                'deskripsi' => 'Panduan lengkap bagi penulis pemula yang ingin menerbitkan buku pertama mereka. Dari ide hingga cetakan.',
                'sinopsis' => "Buku ini adalah panduan komprehensif bagi Anda yang selama ini memendam impian menjadi seorang penulis namun bingung harus mulai dari mana. Dengan bahasa yang ringan dan aplikatif, penulis menguraikan tahapan-tahapan krusial mulai dari penggalian ide, penyusunan kerangka karangan, hingga proses teknis menulis draft pertama.\n\nTidak hanya sekadar teknik menulis, buku ini juga mengupas tuntas seluk-beluk dunia penerbitan di Indonesia. Anda akan diajak memahami perbedaan antara penerbit mayor dan indie, cara mengirimkan naskah yang baik, hingga proses penyuntingan dan layout yang profesional.",
                'harga' => 85000,
                'stok' => 50,
                'cover_image' => null,
                'kategori' => 'Penulisan',
                'featured' => true,
                'status_publish' => true,
            ],
            [
                'judul' => 'Metodologi Penelitian Kuantitatif',
                'deskripsi' => 'Buku referensi komprehensif untuk mahasiswa dan peneliti yang menggunakan pendekatan kuantitatif.',
                'harga' => 120000,
                'stok' => 30,
                'cover_image' => null,
                'kategori' => 'Akademik',
                'featured' => true,
                'status_publish' => true,
            ],
            [
                'judul' => 'Transformasi Digital UMKM',
                'deskripsi' => 'Strategi praktis bagi pelaku usaha kecil menengah untuk bertransformasi di era digital.',
                'harga' => 95000,
                'stok' => 40,
                'cover_image' => null,
                'kategori' => 'Bisnis',
                'featured' => false,
                'status_publish' => true,
            ],
            [
                'judul' => 'Psikologi Pendidikan Modern',
                'deskripsi' => 'Mengupas teori dan praktik psikologi dalam konteks pendidikan kontemporer.',
                'harga' => 110000,
                'stok' => 25,
                'cover_image' => null,
                'kategori' => 'Pendidikan',
                'featured' => false,
                'status_publish' => true,
            ],
            [
                'judul' => 'Hukum Kontrak dalam Era Digital',
                'deskripsi' => 'Analisis mendalam tentang perkembangan hukum kontrak di tengah transformasi digital.',
                'harga' => 135000,
                'stok' => 20,
                'cover_image' => null,
                'kategori' => 'Hukum',
                'featured' => true,
                'status_publish' => true,
            ],
            [
                'judul' => 'Sastra Nusantara Kontemporer',
                'deskripsi' => 'Kumpulan esai dan kajian sastra Indonesia kontemporer dari berbagai perspektif.',
                'harga' => 75000,
                'stok' => 60,
                'cover_image' => null,
                'kategori' => 'Sastra',
                'featured' => false,
                'status_publish' => true,
            ],
        ];

        foreach ($books as $book) {
            $book['slug'] = Str::slug($book['judul']) . '-' . Str::random(6);
            Book::create($book);
        }

        // ─── TESTIMONIALS ────────────────────────────────────────────────────
        $testimonials = [
            [
                'nama' => 'Dr. Ahmad Fauzi',
                'jabatan' => 'Dosen Universitas Gadjah Mada',
                'rating' => 5,
                'isi_review' => 'Media Fikra sangat profesional dalam membantu penerbitan buku saya. Prosesnya cepat, hasil cetakannya berkualitas tinggi. Sangat direkomendasikan!',
                'status_publish' => true,
            ],
            [
                'nama' => 'Siti Rahayu, M.Pd',
                'jabatan' => 'Guru SMA & Penulis',
                'rating' => 5,
                'isi_review' => 'Saya sangat puas dengan layanan ghostwriting dari Media Fikra. Tim mereka memahami visi saya dan menuangkannya dengan sangat baik.',
                'status_publish' => true,
            ],
            [
                'nama' => 'Budi Santoso',
                'jabatan' => 'Pengusaha UMKM',
                'rating' => 5,
                'isi_review' => 'Berhasil menerbitkan buku bisnis pertama saya berkat Media Fikra. Pelayanannya ramah dan penuh dedikasi dari awal hingga akhir.',
                'status_publish' => true,
            ],
            [
                'nama' => 'Prof. Dr. Lestari Widodo',
                'jabatan' => 'Peneliti BRIN',
                'rating' => 5,
                'isi_review' => 'Konversi disertasi saya menjadi buku berjalan lancar. Media Fikra tahu betul cara menyajikan karya ilmiah agar menarik untuk khalayak luas.',
                'status_publish' => true,
            ],
            [
                'nama' => 'Anisa Permata',
                'jabatan' => 'Mahasiswa S2 Hukum',
                'rating' => 4,
                'isi_review' => 'Prosesnya mudah dan harganya terjangkau. Tim editorial sangat membantu dalam perbaikan naskah saya.',
                'status_publish' => true,
            ],
        ];

        foreach ($testimonials as $t) {
            Testimonial::create($t);
        }

        // ─── PROMOS & BERITA ─────────────────────────────────────────────────
        $promos = [
            [
                'judul' => 'Promo Spesial: Diskon 20% Penerbitan Buku Perdana',
                'isi' => 'Dapatkan diskon 20% untuk layanan penerbitan buku pertama Anda. Berlaku hingga akhir bulan ini. Hubungi CS kami sekarang!',
                'type' => 'promo',
                'status_publish' => true,
            ],
            [
                'judul' => 'Media Fikra Raih Penghargaan Penerbit Terbaik 2025',
                'isi' => 'Dengan bangga kami mengumumkan bahwa Media Fikra telah meraih penghargaan sebagai Penerbit Independen Terbaik 2025 dari Asosiasi Penerbit Indonesia.',
                'type' => 'berita',
                'status_publish' => true,
            ],
            [
                'judul' => 'Paket Konversi Tugas Akhir — Harga Spesial!',
                'isi' => 'Ubah skripsi, tesis, atau disertasi Anda menjadi buku berkualitas dengan harga paket spesial. Termasuk editing, layout, dan ISBN.',
                'type' => 'promo',
                'status_publish' => true,
            ],
        ];

        foreach ($promos as $p) {
            $p['slug'] = Str::slug($p['judul']) . '-' . Str::random(6);
            Promo::create($p);
        }

        // ─── PORTFOLIO ───────────────────────────────────────────────────────
        $portfolios = [
            ['judul' => 'Pendidikan Karakter Berbasis Kearifan Lokal', 'penulis' => 'Dr. Hendra Kusuma', 'kategori' => 'Pendidikan', 'tahun' => 2024],
            ['judul' => 'Inovasi Sistem Pertanian Modern', 'penulis' => 'Tim Peneliti IPB', 'kategori' => 'Pertanian', 'tahun' => 2024],
            ['judul' => 'Manajemen Risiko Keuangan Syariah', 'penulis' => 'Dr. Fatimah Azzahra', 'kategori' => 'Ekonomi', 'tahun' => 2023],
            ['judul' => 'Rekayasa Perangkat Lunak Berbasis AI', 'penulis' => 'Muhammad Ilham, S.Kom', 'kategori' => 'Teknologi', 'tahun' => 2023],
            ['judul' => 'Sejarah Perkembangan Islam Nusantara', 'penulis' => 'Prof. Abdul Karim', 'kategori' => 'Sejarah', 'tahun' => 2024],
            ['judul' => 'Strategi Marketing Digital untuk Pemula', 'penulis' => 'Reni Anggraini', 'kategori' => 'Bisnis', 'tahun' => 2023],
        ];

        foreach ($portfolios as $p) {
            Portfolio::create($p);
        }
    }
}
