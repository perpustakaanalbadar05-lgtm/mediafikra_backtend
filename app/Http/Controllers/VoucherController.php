<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        return response()->json(Voucher::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:vouchers,code',
            'type' => 'required|in:discount,free_shipping',
            'value' => 'required|integer|min:0',
            'min_purchase' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_until' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        return response()->json(Voucher::create($data), 201);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'code' => 'sometimes|string|unique:vouchers,code,' . $voucher->id,
            'type' => 'sometimes|in:discount,free_shipping',
            'value' => 'sometimes|integer|min:0',
            'min_purchase' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_until' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $voucher->update($data);
        return response()->json($voucher);
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return response()->json(['message' => 'Voucher berhasil dihapus.']);
    }
}
