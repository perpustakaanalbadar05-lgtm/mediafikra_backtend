<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ===================================================================
// DIAGNOSTIK LENGKAP
// Buka: https://api.mediafikra.id/diagnose
// ===================================================================
Route::get('/diagnose', function () {
    $publicPath  = public_path();
    $storagePath = storage_path('app/public');
    $coversPath  = storage_path('app/public/covers');
    $publicImg   = public_path('img/covers');

    $coversFiles = is_dir($coversPath)
        ? array_values(array_diff(scandir($coversPath), ['.', '..']))
        : [];

    $publicImgFiles = is_dir($publicImg)
        ? array_values(array_diff(scandir($publicImg), ['.', '..']))
        : [];

    try {
        $books = \Illuminate\Support\Facades\DB::table('books')
            ->select('id', 'judul', 'cover_image')
            ->whereNotNull('cover_image')
            ->get();
    } catch (\Throwable $e) {
        $books = ['error' => $e->getMessage()];
    }

    // Test apakah kita bisa tulis ke public/img
    $canWritePublic = false;
    $imgDir = public_path('img');
    if (!is_dir($imgDir)) {
        $canWritePublic = @mkdir($imgDir, 0755, true);
    } else {
        $canWritePublic = is_writable($imgDir);
    }

    return response()->json([
        'paths' => [
            'public_path'           => $publicPath,
            'storage_app_public'    => $storagePath,
            'covers_storage_path'   => $coversPath,
            'public_img_covers'     => $publicImg,
        ],
        'status' => [
            'storage_covers_exists'       => is_dir($coversPath),
            'public_img_covers_exists'    => is_dir($publicImg),
            'public_dir_writable'         => $canWritePublic,
            'symlink_exists'              => file_exists(public_path('storage')),
        ],
        'covers_in_storage' => $coversFiles,
        'covers_in_public_img' => $publicImgFiles,
        'books_in_db' => $books,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

// ===================================================================
// MIGRASI SATU KALI: Salin dari storage ke public/img + Update Database
// Buka: https://api.mediafikra.id/migrate-images
// ===================================================================
Route::get('/migrate-images', function () {
    $allDirs = ['covers', 'thumbnails', 'articles', 'promos', 'portfolios', 'testimonials', 'fotos', 'images'];
    $fileCopied = [];
    $fileErrors = [];
    $totalCopied = 0;

    // Pastikan public/img ada
    $publicImgBase = public_path('img');
    if (!is_dir($publicImgBase)) {
        if (!mkdir($publicImgBase, 0755, true)) {
            return response()->json(['error' => 'Gagal membuat folder public/img. Periksa permission.'], 500);
        }
    }

    foreach ($allDirs as $dir) {
        $src  = storage_path('app/public/' . $dir);
        $dest = public_path('img/' . $dir);

        if (!is_dir($src)) {
            $fileCopied[$dir] = 'tidak ada di storage, dilewati';
            continue;
        }

        if (!is_dir($dest) && !mkdir($dest, 0755, true)) {
            $fileErrors[] = "Gagal buat folder: $dest";
            continue;
        }

        $files  = array_diff(scandir($src), ['.', '..']);
        $copied = 0;
        foreach ($files as $f) {
            $s = $src . '/' . $f;
            $d = $dest . '/' . $f;
            if (is_file($s) && !file_exists($d)) {
                if (copy($s, $d)) {
                    $copied++;
                } else {
                    $fileErrors[] = "Gagal salin: $f";
                }
            }
        }
        $totalCopied += $copied;
        $fileCopied[$dir] = "Disalin $copied dari " . count($files) . " file";
    }

    // ---------------------------------------------------------------
    // UPDATE DATABASE: Ganti path /storage/ → /img/ di semua tabel
    // ---------------------------------------------------------------
    $dbTables = [
        ['table' => 'books',        'column' => 'cover_image'],
        ['table' => 'portfolios',   'column' => 'cover'],
        ['table' => 'testimonials', 'column' => 'foto'],
        ['table' => 'articles',     'column' => 'thumbnail'],
        ['table' => 'promos',       'column' => 'thumbnail'],
    ];

    $dbResults = [];
    foreach ($dbTables as $t) {
        try {
            $updated = \Illuminate\Support\Facades\DB::table($t['table'])
                ->where($t['column'], 'like', '/storage/%')
                ->update([
                    $t['column'] => \Illuminate\Support\Facades\DB::raw(
                        "REPLACE(`{$t['column']}`, '/storage/', '/img/')"
                    )
                ]);
            $dbResults[$t['table'] . '.' . $t['column']] = "Updated: {$updated} baris";
        } catch (\Throwable $e) {
            $dbResults[$t['table'] . '.' . $t['column']] = 'Error: ' . $e->getMessage();
        }
    }

    // Verifikasi: cek sample data dari DB sekarang
    $verify = [];
    try {
        $book = \Illuminate\Support\Facades\DB::table('books')
            ->whereNotNull('cover_image')->select('id', 'cover_image')->first();
        $verify['books.cover_image'] = $book ? $book->cover_image : 'tidak ada data';
    } catch (\Throwable $e) {
        $verify['books'] = 'error: ' . $e->getMessage();
    }

    return response()->json([
        'status'                => count($fileErrors) === 0 ? 'sukses' : 'sukses_dengan_error',
        'files_total_copied'    => $totalCopied,
        'files_detail'          => $fileCopied,
        'files_errors'          => $fileErrors,
        'db_update'             => $dbResults,
        'db_verify_sample'      => $verify,
        'pesan'                 => 'Selesai! File disalin dan database diperbarui. Gambar seharusnya sudah tampil sekarang.',
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

// ===================================================================
// UPDATE DATABASE: Ganti semua path /storage/ → /img/ di semua tabel
// Buka SEKALI: https://api.mediafikra.id/update-db-paths
// ===================================================================
Route::get('/update-db-paths', function () {
    $tables = [
        ['table' => 'books',        'column' => 'cover_image'],
        ['table' => 'portfolios',   'column' => 'cover'],
        ['table' => 'testimonials', 'column' => 'foto'],
        ['table' => 'articles',     'column' => 'thumbnail'],
        ['table' => 'promos',       'column' => 'thumbnail'],
    ];

    $results = [];
    foreach ($tables as $t) {
        try {
            $updated = \Illuminate\Support\Facades\DB::table($t['table'])
                ->where($t['column'], 'like', '/storage/%')
                ->update([
                    $t['column'] => \Illuminate\Support\Facades\DB::raw(
                        "REPLACE(`{$t['column']}`, '/storage/', '/img/')"
                    )
                ]);
            $results[$t['table'] . '.' . $t['column']] = "Updated: {$updated} baris";
        } catch (\Throwable $e) {
            $results[$t['table'] . '.' . $t['column']] = 'Error: ' . $e->getMessage();
        }
    }

    // Verifikasi sesudah update
    $verify = [];
    foreach ($tables as $t) {
        try {
            $sample = \Illuminate\Support\Facades\DB::table($t['table'])
                ->whereNotNull($t['column'])
                ->select('id', $t['column'])
                ->first();
            $verify[$t['table']] = $sample
                ? [$t['column'] => $sample->{$t['column']}]
                : 'tidak ada data';
        } catch (\Throwable $e) {
            $verify[$t['table']] = 'error: ' . $e->getMessage();
        }
    }

    return response()->json([
        'status'  => 'selesai',
        'updated' => $results,
        'verify_sample' => $verify,
        'pesan'   => 'Path database sudah diperbarui ke /img/. Gambar sekarang harus tampil!',
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});
