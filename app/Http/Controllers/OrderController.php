<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::with('book')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_pembeli' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'buku_id' => 'required|exists:books,id',
            'qty' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
            'kurir' => 'nullable|string',
            'ongkir' => 'nullable|numeric',
        ]);

        $book = Book::findOrFail($data['buku_id']);
        
        if ($book->stok < $data['qty']) {
            return response()->json([
                'message' => 'Stok buku tidak mencukupi.',
                'stok_tersedia' => $book->stok
            ], 422);
        }

        $data['total'] = $book->harga * $data['qty'];
        $data['status'] = 'pending';

        $order = Order::create($data);
        $book->decrement('stok', $data['qty']);

        return response()->json([
            'message' => 'Order berhasil dibuat.',
            'order' => $order->load('book'),
            'whatsapp_url' => $this->buildWhatsAppUrl($order, $book),
        ], 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load('book'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,diproses,selesai,dibatalkan']);
        $order->update(['status' => $request->status]);
        return response()->json($order);
    }

    private function buildWhatsAppUrl(Order $order, Book $book): string
    {
        $csNumber = config('app.cs_whatsapp', '6282332975294');
        $message = "Halo Media Fikra, saya ingin memesan buku:\n\n";
        $message .= "📚 *Buku*: {$book->judul}\n";
        $message .= "👤 *Nama*: {$order->nama_pembeli}\n";
        $message .= "📱 *WhatsApp*: {$order->whatsapp}\n";
        $message .= "📦 *Jumlah*: {$order->qty} eks\n";
        if ($order->kurir) {
            $message .= "🚚 *Kurir*: " . strtoupper($order->kurir) . "\n";
            $message .= "💸 *Ongkir*: Rp " . number_format($order->ongkir, 0, ',', '.') . "\n";
            $message .= "💰 *Subtotal*: Rp " . number_format($order->total, 0, ',', '.') . "\n";
            $message .= "💳 *Total Bayar*: Rp " . number_format($order->total + $order->ongkir, 0, ',', '.') . "\n";
        } else {
            $message .= "💰 *Total*: Rp " . number_format($order->total, 0, ',', '.') . "\n";
        }
        $message .= "📍 *Alamat*: {$order->alamat}\n";
        if ($order->catatan) {
            $message .= "📝 *Catatan*: {$order->catatan}\n";
        }
        $message .= "\nTerima kasih! 🙏";

        return "https://wa.me/{$csNumber}?text=" . urlencode($message);
    }
}
