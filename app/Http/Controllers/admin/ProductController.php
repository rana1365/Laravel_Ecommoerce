<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest()->with('product_images');

        if (!empty($request->get('keyword'))) {
            $products = $products->where('title', 'like', '%'.$request->keyword.'%');
        }

        $products = $products->paginate(6);
        $data['products'] = $products;

        return view('admin.products.list', $data);

    }

    public function create()
    {
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $subCategories = SubCategory::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['subCategories'] = $subCategories;
        return view('admin.products.create', $data);
    }

    public function store(Request $request)
    {
        $rules =
            [
                'title' => 'required',
                'slug' => 'required|unique:products',
                'price' => 'required|numeric',
                'sku' => 'required|unique:products',
                'track_qty' => 'required|in:Yes,No',
                'is_featured' => 'required|in:Yes,No',
                'category' => 'required|numeric',
            ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes')
        {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes())
        {
//            dd($request->image_id);
//            exit();

            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();

            /*** Saving Product Gallery Images ***/
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temp_image_id) {
                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray); //Like jpg, png, gif ..etc extension

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    // if product_id => 4 and product_image_id => 6, then the image name will be like bellow:
                    //Example:  4-6-26/12/2023.jpg

                    $productImage->image = $imageName;
                    $productImage->save();

                    // Generate Product Thumbnails

                    // Large Image
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destinationPath = public_path().'/uploads/product/large/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $image->save($destinationPath);

                    // Small Image
                    $destinationPath = public_path().'/uploads/product/small/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->fit(300, 300);

                    $image->save($destinationPath);
                }
            }

            $request->session()->flash('success', 'Product Added Successfully.!');

            return response(
                [
                    'status' => true,
                    'message' => 'Product Added Successfully.!',
                ]);

        } else
            {
                return response(
                    [
                        'status' => false,
                        'errors' => $validator->errors(),
                    ]);
            }


    }

    public function edit($id, Request $request)
    {
        $products = Product::find($id);

        if (empty($products)) {

            return redirect()->route('products.index')->with('error', 'Product not Found.!');
        }

        /*** Fetch Product Image ***/
        $productImages = ProductImage::where('product_id', $products->id)->get();

        /*** Fetch Related Products ***/
        $relatedProducts = [];
        if ($products->related_products != '') {
            $productArray = explode(',', $products->related_products);

            $relatedProducts = Product::whereIn('id', $productArray)->with('product_images')->get();
        }

        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $subCategories = SubCategory::where('category_id', $products->category_id)->get();
        $data['products'] = $products;
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;


        return view('admin.products.edit', $data);

    }

    public function update($id, Request $request)
    {
        $products = Product::find($id);
        $rules =
            [
                'title' => 'required',
                'slug' => 'required|unique:products,slug,'.$products->id.',id',
                'price' => 'required|numeric',
                'sku' => 'required|unique:products,sku,'.$products->id.',id',
                'track_qty' => 'required|in:Yes,No',
                'is_featured' => 'required|in:Yes,No',
                'category' => 'required|numeric',
            ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes')
        {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes())
        {
//            dd($request->image_id);
//            exit();
            $products->title = $request->title;
            $products->slug = $request->slug;
            $products->description = $request->description;
            $products->price = $request->price;
            $products->compare_price = $request->compare_price;
            $products->sku = $request->sku;
            $products->barcode = $request->barcode;
            $products->track_qty = $request->track_qty;
            $products->qty = $request->qty;
            $products->status = $request->status;
            $products->category_id = $request->category;
            $products->sub_category_id = $request->sub_category;
            $products->brand_id = $request->brand;
            $products->is_featured = $request->is_featured;
            $products->short_description = $request->short_description;
            $products->shipping_returns = $request->shipping_returns;
            $products->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $products->save();


            $request->session()->flash('success', 'Product Updated Successfully.!');

            return response(
                [
                    'status' => true,
                    'message' => 'Product Updated Successfully.!',
                ]);

        } else
        {
            return response(
                [
                    'status' => false,
                    'errors' => $validator->errors(),
                ]);
        }

    }

    public function destroy($id, Request $request)
    {

        $products = Product::find($id);
        if (empty($products)) {

            $request->session()->flash('error', 'Product not found.!');
            return response()->json([
                'status' => true,
                'message' => 'Product not found.!'
            ]);
        }

        $productImages = ProductImage::where('product_id', $id)->get();

        if (!empty($productImages))
        {
            foreach ($productImages as $productImage) {

                File::delete(public_path('uploads/product/large/'.$productImage->image));
                File::delete(public_path('uploads/product/small/'.$productImage->image));
            }

            ProductImage::where('product_id', $id)->delete();
        }

        $products->delete();

        $request->session()->flash('success', 'Product deleted successfully.!');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully.!'
        ]);

    }

    public function getProducts(Request $request) {
        $tempProduct = [];
        if ($request->term != "") {
            $products = Product::where('title', 'like', '%'.$request->term.'%')->get();
        }

        if ($products != null) {
            foreach ($products as $product) {
                $tempProduct[] = array('id' => $product->id, 'text' => $product->title);
            }
        }

        // print_r($tempProduct);
        return response()->json([
            'tags' => $tempProduct,
            'status' => true
        ]);

    }
}
