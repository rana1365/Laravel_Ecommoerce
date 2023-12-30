<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class ProductImageController extends Controller
{
   public function update(Request $request)
   {
       //Image Extension Generation
       $image = $request->image;
       $ext = $image->getClientOriginalExtension();
       $sourcePath = $image->getPathName();

       $productImage = new ProductImage();
       $productImage->product_id = $request->product_id;
       $productImage->image = 'NULL';
       $productImage->save();

       //Image Name Generation
       $imageName = $request->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
       $productImage->image = $imageName;
       $productImage->save();

       //Image Thumbnail Generation
       // Large Image
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

       return response()->json(
           [
               'status' => true,
               'image-path' => $productImage->id,
               'imagePath' => asset('uploads/product/small/'.$productImage->image),
               'message' => 'Image Saved Successfully.!',
           ]);
   }

    public function destroy(Request $request)
    {

        $productImage = ProductImage::find($request->id);
        if (empty($productImage)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Image not found.!'
                ]);
        }

        File::delete(public_path().'uploads/product/large/'.$productImage->image);
        File::delete(public_path().'uploads/product/small/'.$productImage->image);

        $productImage->delete();

        //$request->session()->flash('success', 'Image deleted successfully.!');

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully.!'
        ]);
    }
}
