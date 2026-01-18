<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingFee\ShippingFeeRequest;
use App\Models\ShippingFee;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ShippingFeeController extends Controller
{
    // GET /api/shipping-fees
    public function index(Request $request)
    {
        $query = ShippingFee::query();

        if ($request->filled('province_code')) {
            $query->where('province_code', $request->province_code);
        }

        if ($request->filled('ward_code')) {
            $query->where('ward_code', $request->ward_code);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qb) use ($q) {
                $qb->where('province_name', 'like', "%{$q}%")
                   ->orWhere('ward_name', 'like', "%{$q}%");
            });
        }

        $perPage = (int) $request->get('per_page', 20);

        $data = $query
            ->orderBy('province_name')
            ->paginate($perPage);

        return response()->json($data);
    }

    // POST /api/shipping-fees
    public function store(ShippingFeeRequest $request)
    {
        try {
            $fee = ShippingFee::create($request->validated());

            return response()->json([
                'message' => 'Created',
                'data' => $fee
            ], 201);

        } catch (QueryException $e) {
            // Duplicate key (province_code + ward_code)
            if ($e->getCode() == 23000) {
                return response()->json([
                    'message' => 'Shipping fee already exists for this province/ward'
                ], 409);
            }

            throw $e;
        }
    }

    // GET /api/shipping-fees/{id}
    public function show($id)
    {
        $fee = ShippingFee::find($id);

        if (!$fee) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($fee);
    }

    // PUT /api/shipping-fees/{id}
    public function update(ShippingFeeRequest $request, $id)
    {
        $fee = ShippingFee::find($id);

        if (!$fee) {
            return response()->json(['message' => 'Not found'], 404);
        }

        try {
            $fee->update($request->validated());

            return response()->json([
                'message' => 'Updated',
                'data' => $fee
            ]);

        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'message' => 'Shipping fee already exists for this province/ward'
                ], 409);
            }

            throw $e;
        }
    }

    // DELETE /api/shipping-fees/{id}
    public function destroy($id)
    {
        $fee = ShippingFee::find($id);

        if (!$fee) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $fee->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // GET /api/shipping-fees/lookup
    public function lookup(Request $request)
    {
        $province = $request->province_code;
        $ward = $request->ward_code;

        if (!$province) {
            return response()->json(['message' => 'province_code is required'], 400);
        }

        $query = ShippingFee::where('province_code', $province);

        if ($ward) {
            $byWard = (clone $query)->where('ward_code', $ward)->first();
            if ($byWard) {
                return response()->json(['data' => $byWard]);
            }
        }

        $byProvince = (clone $query)->whereNull('ward_code')->first()
            ?? (clone $query)->orderBy('fee')->first();

        if (!$byProvince) {
            return response()->json(['message' => 'No shipping fee found'], 404);
        }

        return response()->json(['data' => $byProvince]);
    }

    // GET /api/shipping-fees/all
    public function getAll()
    {
        $data = ShippingFee::orderBy('province_name')->get();

        return response()->json([
            'message' => 'OK',
            'data' => $data
        ]);
    }
}
