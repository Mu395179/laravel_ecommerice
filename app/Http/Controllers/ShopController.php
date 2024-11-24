<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->query('size') ? $request->query('size') : 12;
        $o_column = ""; // 修正拼寫
        $o_order = "";
        $order = $request->query('order') ? $request->query('order') : -1;
        $f_brands = $request->query('brands');
        $f_categories = $request->query('categories');
        $min_price = $request->query('min') ? $request->query('min'):1;
        $max_price = $request->query('max') ? $request->query('max'):500;

        switch ($order) {
            case 1:
                $o_column = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_column = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_column = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_column = 'sale_price';
                $o_order = 'DESC';
                break;
            default:
                $o_column = 'id';
                $o_order = 'DESC';
        }

        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();

        $products = Product::when(!empty($f_brands), function ($query) use ($f_brands) {
            $query->whereIn('brand_id', explode(',', $f_brands));
        })
        ->when(!empty($f_categories), function ($query) use ($f_categories) {
            $query->whereIn('category_id', explode(',', $f_categories));
        })
        ->when($request->query('check_both'), function ($query) use ($min_price, $max_price) {
            // 檢查兩個條件都要符合
            $query->whereBetween('regular_price', [$min_price, $max_price])
                  ->whereBetween('sale_price', [$min_price, $max_price]);
        }, function ($query) use ($min_price, $max_price) {
            // 檢查任意條件符合即可
            $query->whereBetween('regular_price', [$min_price, $max_price])
                  ->orWhereBetween('sale_price', [$min_price, $max_price]);
        })
        ->orderBy($o_column, $o_order)
        ->paginate($size);

        return view('shop', compact('products', 'size', 'order', 'brands', 'f_brands','categories','f_categories','min_price','max_price'));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();
        $rproducts = Product::where('slug', '<>', $product_slug)
            ->limit(8)
            ->get();

        return view('details', compact('product', 'rproducts'));
    }
}
