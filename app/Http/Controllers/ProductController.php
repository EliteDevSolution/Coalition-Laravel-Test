<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index()
    {
        return view('product.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_name' => 'required',
            'quantity_in_stock' => 'required|integer',
            'price_per_item' => 'required|numeric'
        ]);
        $data['id'] = sha1(time());
        // $data['id'] = sha1(time());
        $data['datetime_submitted'] = Carbon::now()->toDateTimeString();
        $data['total_value_number'] = $data['quantity_in_stock'] * $data['price_per_item'];

        $filename = storage_path('app/public/products.json');
        $jsonData = File::exists($filename) ? json_decode(File::get($filename), true) : [];

        array_push($jsonData, $data);
        File::put($filename, json_encode($jsonData, JSON_PRETTY_PRINT));

        return response()->json(['message' => 'Product added successfully']);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $filename = storage_path('app/public/products.json');
        $jsonData = File::exists($filename) ? json_decode(File::get($filename), true) : [];
        $searchId = $data['id'];

        $updatedArray = array_map(function ($item) use ($searchId, $data) {
            if ($item['id'] === $searchId) {
                $item = array_merge($item, $data);
                $item['total_value_number'] = (int) $item['quantity_in_stock'] * (int) $item['price_per_item'];
                // $item['datetime_submitted'] = Carbon::now()->toDateTimeString();
            }
            return $item;
        }, $jsonData);

        File::put($filename, json_encode($updatedArray, JSON_PRETTY_PRINT));

        return response()->json(['message' => 'Product updated successfully']);
    }

    public function getData()
    {
        $filename = storage_path('app/public/products.json');
        $jsonData = File::exists($filename) ? json_decode(File::get($filename), true) : [];

        return response()->json([ "data" => $jsonData ]);
    }
}
