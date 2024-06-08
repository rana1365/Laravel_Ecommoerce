<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function addToCart(Request $request) {

       $product = Product::with('product_images')->find($request->id);

       if ($product == null) {
        return response()->json([

            'status' => false,
            'message' => 'Product not found!'

        ]);
       }

       // Initialize variables
       //$status = false;
       //$message = '';

       if (Cart::count() > 0) {
        //echo "Product already in Cart";
        // products found in cart
        // Check this product already in the cart
        // Return a message as "product already added"
        // if product not found add product in cart

        $cartContent = Cart::content();
        $productAlreadyExists = false;

        foreach ($cartContent as $item) {
            if ($item->id == $product->id) {
                $productAlreadyExists = true;
            }
        }

        if ($productAlreadyExists == false) {
            Cart::add($product->id, $product->title, 1, $product->price,
         ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

         $status = true;
         $message = $product->title.' added in Cart';

        } else {
            $status = false;
            $message = $product->title.' already added in Cart';
        }
       } else {
        Cart::add($product->id, $product->title, 1, $product->price,
         ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
         $status = true;
         $message = $product->title.' added in Cart';
       }

       return response()->json([

        'status' => $status,
        'message' => $message

        ]);


    }

    public function cart() {

        //dd(Cart::content());

        $cartContent = Cart::content();
        $subtotal = Cart::subtotal();
        $data['cartContent'] = $cartContent;
        $data['subtotal'] = $subtotal;


        return view('front.cart', $data);
        
    }

}
