<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Session\Session;

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
         $message = '<strong>'.$product->title.'</strong> added in your Cart successfully!';
         session()->flash('success', $message);

        } else {
            $status = false;
            $message = $product->title.' already added in Cart';
        }
       } else {
        Cart::add($product->id, $product->title, 1, $product->price,
         ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
         $status = true;
         $message = '<strong>'.$product->title.'</strong> added in your Cart successfully!';
         session()->flash('success', $message);
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

    public function updateCart(Request $request) {

        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);
        // Check qty available in stock

        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Requested qty('.$qty.') not available in stock';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);
        }
        
        return response()->json([
            'status' => $status,
            'message' => $message

        ]);

    }

    public function deleteItem(Request $request) {
        $itemInfo = Cart::get($request->rowId);

        if ($itemInfo == null) {
            $errorMessage = 'Item not found in Cart';
            session()->flash('error', $errorMessage);

            return response()->json([
                'status' => false,
                'message' => $errorMessage
    
            ]);
        }

        Cart::remove($request->rowId);

        $message = 'Item removed from Cart successfully.';
        session()->flash('success', $message);

            return response()->json([
                'status' => true,
                'message' => $message
    
            ]);
        

    }

}
