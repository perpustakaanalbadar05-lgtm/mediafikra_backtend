<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OngkirController extends Controller
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        // Untuk production, masukkan ini di .env: RAJAONGKIR_API_KEY
        $this->apiKey = env('RAJAONGKIR_API_KEY', 'dummy_api_key'); 
        $this->baseUrl = 'https://api.rajaongkir.com/starter';
    }

    public function getProvinces()
    {
        if ($this->apiKey === 'dummy_api_key') {
            return response()->json([
                ['province_id' => '11', 'province' => 'Jawa Timur'],
                ['province_id' => '6', 'province' => 'DKI Jakarta'],
            ]);
        }

        $response = Http::withHeaders(['key' => $this->apiKey])->get("{$this->baseUrl}/province");
        return response()->json($response->json()['rajaongkir']['results'] ?? []);
    }

    public function getCities($provinceId)
    {
        if ($this->apiKey === 'dummy_api_key') {
            return response()->json([
                ['city_id' => '444', 'city_name' => 'Surabaya', 'type' => 'Kota'],
                ['city_id' => '114', 'city_name' => 'Denpasar', 'type' => 'Kota'],
            ]);
        }

        $response = Http::withHeaders(['key' => $this->apiKey])->get("{$this->baseUrl}/city?province={$provinceId}");
        return response()->json($response->json()['rajaongkir']['results'] ?? []);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'destination_city_id' => 'required',
            'weight' => 'required|numeric|min:1',
            'courier' => 'required|in:jne,pos,tiki',
            'voucher_code' => 'nullable|string',
            'total_belanja' => 'required|numeric'
        ]);

        $originCityId = Setting::get('free_shipping_city_id', '444'); // Default Surabaya
        
        $ongkirAsli = 0;
        $ongkirFinal = 0;
        $potongan = 0;
        $pesan = '';

        // Jika origin dan destination sama (Lokasi Lokal Gratis Ongkir)
        if ($request->destination_city_id == $originCityId) {
            $ongkirAsli = 0;
            $ongkirFinal = 0;
            $pesan = 'Gratis ongkir untuk wilayah lokal.';
        } else {
            if ($this->apiKey === 'dummy_api_key') {
                $ongkirAsli = 25000; // Harga simulasi
            } else {
                $response = Http::withHeaders(['key' => $this->apiKey])->post("{$this->baseUrl}/cost", [
                    'origin' => $originCityId,
                    'destination' => $request->destination_city_id,
                    'weight' => $request->weight,
                    'courier' => $request->courier
                ]);
                $results = $response->json()['rajaongkir']['results'][0]['costs'] ?? [];
                if (count($results) > 0) {
                    $ongkirAsli = $results[0]['cost'][0]['value'];
                }
            }
            $ongkirFinal = $ongkirAsli;
        }

        // Cek Mekanisme Otomatis Minimal Belanja
        $minBelanja = Setting::get('free_shipping_min_amount', 0);
        if ($minBelanja > 0 && $request->total_belanja >= $minBelanja) {
            $ongkirFinal = 0;
            $potongan = $ongkirAsli;
            $pesan = 'Gratis ongkir! (Minimal belanja terpenuhi)';
        }

        // Cek Mekanisme Voucher
        if ($request->voucher_code && $ongkirFinal > 0) {
            $voucher = Voucher::where('code', $request->voucher_code)->where('is_active', true)->first();
            if ($voucher && $request->total_belanja >= $voucher->min_purchase) {
                if ($voucher->type === 'free_shipping') {
                    $potongan_voucher = $voucher->value > 0 ? $voucher->value : $ongkirFinal;
                    $potongan = min($potongan_voucher, $ongkirFinal);
                    $ongkirFinal -= $potongan;
                    $pesan = 'Voucher gratis ongkir berhasil digunakan.';
                }
            }
        }

        return response()->json([
            'ongkir_asli' => $ongkirAsli,
            'ongkir_final' => $ongkirFinal,
            'potongan' => $potongan,
            'pesan' => $pesan
        ]);
    }
}
