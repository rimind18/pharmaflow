<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    /**
     * Get active promotions
     */
    public function index(Request $request)
    {
        try {
            $query = Promotion::with('medicine');
            $status = $request->get('status');

if ($status === 'active') {
    $query->where('is_active', true);
} elseif ($status === 'inactive') {
    $query->where('is_active', false);
}

            // Get only active

            // Filter by medicine
            if ($request->has('medicine_id')) {
                $query->where('medicine_id', $request->get('medicine_id'));
            }

            // Search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            }

            $promotions = $query->orderBy('start_date', 'desc')->paginate(15);

            return response()->json([
                'message' => 'Data promo berhasil diambil',
                'data' => $promotions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create promotion
     */
    public function store(Request $request)
    {
        try {
      $validated = $request->validate([
    'name' => 'required|string',
    'description' => 'nullable|string',
    'type' => 'required|in:percentage,fixed',
    'discount_value' => 'required|numeric|min:0',
    'minimum_purchase' => 'nullable|numeric|min:0',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after:start_date',
    'max_quantity' => 'nullable|integer|min:1',
    'is_active' => 'nullable|boolean',
]);

           $promotion = Promotion::create([
    'name' => $validated['name'],
    'description' => $validated['description'] ?? null,
    'type' => $validated['type'],
    'discount_value' => $validated['discount_value'],
    'minimum_purchase' => $validated['minimum_purchase'] ?? 0,
    'start_date' => $validated['start_date'],
    'end_date' => $validated['end_date'],
    'is_active' => true,
]);

            // Notify all customers
            $this->notifyCustomers($promotion);

            return response()->json([
                'message' => 'Promo berhasil dibuat',
                'data' => $promotion->load('medicine')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get specific promotion
     */
    public function show($id)
    {
        try {
            $promotion = Promotion::with('medicine')->findOrFail($id);

            return response()->json([
                'message' => 'Data promo berhasil diambil',
                'data' => $promotion
            ], 200);

        } catch (\Exception $e) {
    return response()->json([
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
    ], 500);
}
    }

    /**
     * Update promotion
     */
    public function update(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
    'name' => 'required|string',
    'description' => 'nullable|string',
    'type' => 'required|in:percentage,fixed',
    'discount_value' => 'required|numeric|min:0',
    'minimum_purchase' => 'nullable|numeric|min:0',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after:start_date',
    'max_quantity' => 'nullable|integer|min:1',
    'is_active' => 'nullable|boolean',
]);

    $promotion->update([
    'name' => $validated['name'],
    'description' => $validated['description'] ?? null,
    'type' => $validated['type'],
    'discount_value' => $validated['discount_value'],
    'minimum_purchase' => $validated['minimum_purchase'] ?? 0,
    'start_date' => $validated['start_date'],
    'end_date' => $validated['end_date'],
    'is_active' => $validated['is_active'] ?? false,
]);

            return response()->json([
                'message' => 'Promo berhasil diperbarui',
                'data' => $promotion->load('medicine')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
    return response()->json([
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
    ], 500);
}
    }

    /**
     * Delete promotion
     */
    public function destroy($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return response()->json([
                'message' => 'Promo berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
    return response()->json([
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
    ], 500);
}
    }

    /**
     * Notify customers about promotion
     */
    private function notifyCustomers($promotion)
    {
        $customers = User::where('role', 'customer')->get();

        foreach ($customers as $customer) {
            Notification::create([
                'user_id' => $customer->id,
                'type' => 'promo_baru',
                'title' => 'Promo Baru!',
                'message' => "{$promotion->name} - Dapatkan diskon hingga " . ($promotion->type === 'percentage' ? $promotion->discount_value . '%' : 'Rp' . number_format($promotion->discount_value, 0, ',', '.')),
                'data' => json_encode(['promotion_id' => $promotion->id])
            ]);
        }
    }
}