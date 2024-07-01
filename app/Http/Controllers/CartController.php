<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function checkout () {

        // If cart is empty, redirect to cart page
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }

        // If user is not loggedin, then redirect to user-login page
        if (Auth::check() == false) {

            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
            
            return redirect()->route('account.login');
        }

        $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();

        session()->forget('url.intended');

        $countries = Country::orderBy('name', 'ASC')->get();

        /* Start Calculating Shipping Charge Here */

        $userCountry = $customerAddress->country_id;
        $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

        //echo '<p>' .$shippingInfo->amount.'</p>';
        $totalQty = 0;
        $totalShippingCharge = 0;
        $grandTotal = 0;
        foreach (Cart::content() as $item) {
            $totalQty += $item->qty;
        }

        $totalShippingCharge = $totalQty*$shippingInfo->amount;

        $grandTotal = Cart::subtotal(2,'.','')+$totalShippingCharge;

        /* End Calculating Shipping Charge Here */

        return view('front.checkout', [
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'grandTotal' => $grandTotal
        ]);
    }

    public function processCheckout (Request $request) {
        
        /* Step-01: Apply Validation  */
        $validator = Validator::make($request->all(), [

            'first_name' => 'required|min:5',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:30',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required'
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the error',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        /* Step-02: Save user address to customer_addresses table  */

        $user = Auth::user();
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],

            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'apartment' => $request->apartment,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                
            ]);

            /*  Step-03: Store data in orders Table */

            if ($request->payment_method == 'cod') {

                $shipping = 0;
                $discount = 0;
                $subTotal = Cart::subtotal(2,'.','');

                /* Calculating Shipping Here */
                $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();

                $totalQty = 0;

                foreach (Cart::content() as $item) {
                    $totalQty += $item->qty;
                }

                if ($shippingInfo != null) {
                    $shippingCharge = $totalQty*$shippingInfo->amount;
                    $grandTotal = $subTotal+$shippingCharge;
                    
    
                } else {
                    $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                    $shippingCharge = $totalQty*$shippingInfo->amount;
                    $grandTotal = $subTotal+$shippingCharge;
                }

                $order = new Order;
                $order->subtotal = $subTotal;
                $order->shipping = $shipping;
                $order->grand_total = $grandTotal;
                $order->user_id = $user->id;
                $order->first_name = $request->first_name;
                $order->last_name = $request->last_name;
                $order->email = $request->email;
                $order->mobile = $request->mobile;
                $order->address = $request->address;
                $order->apartment = $request->apartment;
                $order->state = $request->state;
                $order->city = $request->city;
                $order->zip = $request->zip;
                $order->notes = $request->order_notes;
                $order->country_id = $request->country;

                $order->save();

                /* Step-04: Store oreder items in order_items table  */

                foreach (Cart::content() as $item) {
                    $orderItem = new OrderItem;
                    $orderItem->product_id = $item->id;
                    $orderItem->order_id = $order->id;
                    $orderItem->name = $item->name;
                    $orderItem->qty = $item->qty;
                    $orderItem->price = $item->price;
                    $orderItem->total = $item->price*$item->qty;

                    $orderItem->save();
                }

                session()->flash('success', 'You have successfully placed your order.');

                Cart::destroy();

                return response()->json([
                    'message' => 'Order saved successfully',
                    'orderId' => $order->id,
                    'status' => true
                ]);
                

            } else {
                //
            }


    }

    public function thankyou ($id) {
       return view('front.thanks',[
        'id' => $id
       ]); 
    }

    public function getOrderSummery (Request $request) {

        $subTotal = Cart::subtotal(2,'.','');

        if ($request->country_id > 0) {

            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;

            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {

                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = $subTotal+$shippingCharge;
                
                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'shippingCharge' => number_format($shippingCharge,2),
                ]);

            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = $subTotal+$shippingCharge;
                
                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'shippingCharge' => number_format($shippingCharge,2),
                ]);
            }

        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format($subTotal,2),
                'shippingCharge' => number_format(0,2),
            ]);
        }
    }

}
