<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::latest();
        if (!empty($request->get('keyword'))) {
            $brands = $brands->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $brands = $brands->paginate(10);

        return view('admin.brands.list', compact('brands'));

    }

    public function create()
    {
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['brands'] = $brands;
        return view('admin.brands.create', $data);

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),

            [
                'name' => 'required',
                'slug' => 'required|unique:brands',
                'status' => 'required',
            ]);

        if ($validator->passes()) {

            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;

            $brand->save();

            $request->session()->flash('success', 'Brand Added Successfully.!');

            return response(
                [
                    'status' => true,
                    'message' => 'Brand Added Successfully.!',
                ]);

        } else {
            return response(
                [
                    'status' => false,
                    'errors' => $validator->errors(),
                ]);
        }
    }

    public function edit($brandId, Request $request)
    {
        $brand = Brand::find($brandId);

        if (empty($brand)) {
            return redirect()->route('brands.index');
        }
        return view('admin.brands.edit', compact('brand'));

    }

    public function update($brandId, Request $request)
    {
        $brand = Brand::find($brandId);

        if (empty($brand)) {

            $request->session()->flash('error', 'Brand Not Found.!');

            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Brand not Found.!'
            ]);
        }

        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
            ]);

        if ($validator->passes()) {

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success', 'Brand Updated Successfully.!');

            return response()->json([
                'status' => true,
                'message' => 'Brand Updated Successfully.!',
            ]);

        } else {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }

    public function destroy($brandId, Request $request)
    {
        $brand = Brand::find($brandId);
        if (empty($brand)) {
            //return redirect()->route('categories.index');
            $request->session()->flash('error', 'Brand not found.!');
            return response()->json([
                'status' => true,
                'message' => 'Brand not found.!'
            ]);
        }

        $brand->delete();

        $request->session()->flash('success', 'Brand deleted successfully.!');

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully.!'
        ]);

    }
}
