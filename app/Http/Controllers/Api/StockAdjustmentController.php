<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input dari Vue
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $medicine = Medicine::findOrFail($validated['medicine_id']);

            // Cek jika stok keluar tapi barang tidak cukup
            if ($validated['type'] === 'out') {
                if ($medicine->total_stock < $validated['quantity']) {
                    return response()->json([
                        'message' => 'Stok tidak cukup! Sisa stok saat ini: ' . $medicine->total_stock
                    ], 422);
                }
                $medicine->total_stock -= $validated['quantity']; // Kurangi stok
            } else {
                $medicine->total_stock += $validated['quantity']; // Tambah stok
            }

            // Simpan ke database
            $medicine->save();

            DB::commit();

            return response()->json([
                'message' => 'Stok berhasil diperbarui!',
                'data' => $medicine
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat memproses stok.'
            ], 500);
        }
    }
}