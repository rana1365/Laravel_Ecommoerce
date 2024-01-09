<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest();
        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $categories = $categories->paginate(10);

        return view('admin.category.list', compact('categories'));

    }


    public function create()
    {
        return view('admin.category.create');

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:categories',
                'show_home' => 'required',
            ]);

        if ($validator->passes())
        {
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->show_home = $request->show_home;
            $category->save();

            /*** Save Images Here ***/

            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;

                File::copy($sPath, $dPath);

            /*** Generating Thumbnail ***/

                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                //$img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->flash('success', 'Category Added Successfully.!');

            return response()->json([
                'status' => true,
                'message' => 'Category Added Successfully.!',
            ]);

        } else {
            return response()->json([
               'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }


    public function edit($categoryId, Request $request)
    {
        $category = Category::find($categoryId);

        if (empty($category)) {
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit', compact('category'));

    }


    public function update($categoryId, Request $request)
    {
        $category = Category::find($categoryId);

        if (empty($category)) {

            $request->session()->flash('error', 'Category Not Found.!');

            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not Found.!'
            ]);
        }

        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:categories,slug,'.$category->id.',id',
                'show_home' => 'required',
            ]);

        if ($validator->passes()) {

            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->show_home = $request->show_home;
            $category->save();

            $oldImage = $category->image;
            /*** Save Image Here ***/

            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'-'.time().'.'.$ext;
                $sPath = public_path().'/temp/' . $tempImage->name;
                $dPath = public_path().'/uploads/category/' . $newImageName;

                File::copy($sPath, $dPath);

                /*** Generating Thumbnail ***/

                $dPath = public_path().'/uploads/category/' . $newImageName;
                $img = Image::make($sPath);
                //$img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();

                /*** Delete old image ***/

                $thumbPath = public_path().'/uploads/category/thumb/'.$oldImage;
                $imagePath = public_path().'/uploads/category/'.$oldImage;

                File::delete($thumbPath);
                File::delete($imagePath);
            }

            $request->session()->flash('success', 'Category Updated Successfully.!');

            return response()->json([
                'status' => true,
                'message' => 'Category Updated Successfully.!',
            ]);

        } else {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }


    public function destroy($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            //return redirect()->route('categories.index');
            $request->session()->flash('error', 'Category not found.!');
            return response()->json([
                'status' => true,
                'message' => 'Category not found.!'
            ]);
        }

        File::delete(public_path().'/uploads/category/thumb/'.$category->image);
        File::delete(public_path().'/uploads/category/'.$category->image);

        $category->delete();

        $request->session()->flash('success', 'Category deleted successfully.!');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully.!'
        ]);

    }
}
