<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\FiltersRequest;
use App\Http\Requests\Product\SearchRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * GET /api/products
     * Lấy danh sách sản phẩm với pagination, filter, search
     */
    public function index(Request $request)
    {
        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 9);
        $result = $this->productService->getAllProducts($page, $pageSize);

        return response()->json($result);
    }

    public function search(SearchRequest $request)
    {
        $data = $request->validated();

        $keyword = $data['keyword'];
        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 15);

        $result = $this->productService->searchProducts($keyword, $page, $pageSize);

        return response()->json($result);
    }

    public function filters(FiltersRequest $request)
    {
        $requestData = $request->validated();
        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 15);
        
        $result = $this->productService->filterProducts($requestData, $page, $pageSize);
        
        return response()->json($result);
    }

    public function getByCategory(string $slug, Request $request)
    {
        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 15);
        
        $result = $this->productService->getProductsByCategory($slug, $page, $pageSize);
        
        return response()->json($result);
    }

    public function show(string $slug)
    {
        $result = $this->productService->getProductDetail($slug);
        
        $status = $result['success'] ? 200 : 404;
        return response()->json($result, $status);
    }

    public function featured(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $result = $this->productService->getFeaturedProducts($limit);
        
        return response()->json($result);
    }

    public function newProducts(Request $request)
    {
        $limit = $request->input('limit', 4);
        
        $result = $this->productService->getNewProducts($limit);
        
        return response()->json($result);
    }


    public function bestseller(Request $request)
    {
        $limit = $request->input('limit', 4);
        
        $result = $this->productService->getBestsellerProducts($limit);
        
        return response()->json($result);
    }

    public function findBySlug($slug)
    {
        $product = $this->productService->getProductBySlug($slug);
        return new ProductResource($product);
    }

    public function findById ($id)
    {
        $product = $this->productService->getProductById($id);
        return new ProductResource($product);
    }
    public function store(CreateProductRequest $request)
    {
        $result = $this->productService->createProduct($request->validated());
        
        $status = $result['success'] ? 201 : 400;
        return response()->json($result, $status);
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $result = $this->productService->updateProduct($id, $request->validated());
        
        $status = $result['success'] ? 200 : 400;
        return response()->json($result, $status);
    }

    public function destroy(int $id)
    {
        $result = $this->productService->deleteProduct($id);
        
        $status = $result['success'] ? 200 : 400;
        return response()->json($result, $status);
    }

    public function checkStock(int $id, Request $request)
    {
        $quantity = $request->input('quantity', 1);
        
        $result = $this->productService->checkStock($id, $quantity);
        
        return response()->json($result);
    }

    public function categories()
    {
        $result = $this->productService->getAllCategories();
        
        return response()->json($result);
    }

    public function dressStyles()
    {
        $result = $this->productService->getAllDressStyles();
        
        return response()->json($result);
    }

    public function brands()
    {
        $result = $this->productService->getAllBrands();
        
        return response()->json($result);
    }

    public function getByPriceRange(Request $request)
    {
        $request->validate([
            'min_price' => 'required|integer|min:0',
            'max_price' => 'required|integer|min:0',
            'pageSize' => 'nullable|integer|min:1|max:100'
        ]);

        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 15);

        $result = $this->productService->filterProducts([
            'min_price' => $request->min_price,
            'max_price' => $request->max_price
        ], $page, $pageSize);
        
        return response()->json($result);
    }
}