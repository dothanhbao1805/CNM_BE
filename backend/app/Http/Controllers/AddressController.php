<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class AddressController extends Controller
{
public function getProvinces()
{
    $response = Http::withoutVerifying()->get('https://production.cas.so/address-kit/2025-07-01/provinces');

    $data = $response->json();

    // Trả về đúng mảng provinces
    return $data['provinces'] ?? [];
}



    public function getWards($code)
    {
        $response = Http::get("https://production.cas.so/address-kit/2025-07-01/provinces/{$code}/communes");

        $data = $response->json();

        return $data['communes'] ?? [];
    }

}
