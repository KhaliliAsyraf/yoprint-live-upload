<?php

use App\Http\Resources\FileUploadResource;
use App\Http\Resources\ProductResource;
use App\Models\FileUpload;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/uploads', function (Request $request) {
    $uploads = FileUpload::orderByDesc('created_at')->paginate(25);
    return FileUploadResource::collection($uploads);
});

Route::get('/products', function (Request $request) {
    $products = Product::paginate(25);
    return ProductResource::collection($products);
});
