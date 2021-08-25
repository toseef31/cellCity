<?php

namespace App\Http\Controllers\Admin;

use App\Facade\CityClass;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductCondition;
use App\Models\Pmodel;
use App\Models\ProductColor;
use App\Models\ProductStorage;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\TechMail;
use App\Models\Alert;
use App\Models\Order;
use App\Models\Wishlist;
use Darryldecode\Cart\Cart;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Twilio\Rest\Client;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $products =Product::all();
        return view('admin.products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.products.create');
    }

    public function getModels($id)
    {

         $brand= Brand::find($id);
        //  dd($brand);
         $pmodels = Pmodel::where('brand_Id',$brand->id)->get();

         return view('admin.products.getModel',compact('pmodels','brand'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // dd($request->file('image'));
//         DB::beginTransaction();

// try {
        $product = new Product;
        //  $product->insert($request->only($product->getFillable()));

         $product->category = $request->category;
         $product->memory = $request->memory;
         $product->locked = $request->locked;
         $product->warranty = $request->warranty;
         $product->desc = $request->desc;
         $product->screen_size = $request->screen_size;
         $product->megapixel = $request->megapixel;
         $product->OS = $request->OS;
         $product->resolution = $request->resolution;
         $product->screen_type = $request->screen_type;
         $product->network = $request->network;
         $product->sim_card_format = $request->sim_card_format;
         $product->double_sim = $request->double_sim;
         $product->release_year = $request->release_year;
         $product->model_id = $request->model_id;
        //  dd($product);
        $product->save();



        foreach($request->color_name as $key=> $colors)
        {

            $color = new ProductColor;
            $color->color_name = $colors;
            $color->product_id = $product->id;
            $color->save();

            foreach($request->storage[$key] as $key2=>$storages)
            {

                $storage = new ProductStorage;
                $storage->storage = $storages;
                $storage->color_id = $color->id;
                $storage->save();


            foreach($request->condition[$key2] as $key3=>$conditions)
            {
                //  dd($condition);
                $condition = new ProductCondition;
                $condition->condition =$conditions;
                $condition->price = $request->price[$key2][$key3];
                $condition->quantity = $request->quantity[$key2][$key3];
                $condition->storage_id = $storage->id;
                $condition->save();
            }
        }

         foreach($request->file('image')[$key] as $image)
            {

                $imageName= time().$image->getClientOriginalName();
                $destination ='storage/images/products';
                $image->move(public_path($destination), $imageName);

                // dd($imageName);
                $imagefile = new ProductImage;
                $imagefile->image = $imageName;
                $imagefile->product_id = $product->id;
                $imagefile->color_id = $color->id;
                $imagefile->save();
                // dd($request->condition);

                // dd($storage);


    }

    }
    //     DB::commit();

    // } catch (\Exception $e) {
    //     DB::rollback();
    //     return back()->with('message', Alert::_message('success', 'somthing wrong.'));
    // }

        return back()->with('message', Alert::_message('success', 'Product Created Successfully.'));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
         $images = ProductImage::where('product_id',$product->id)->get();
        //  dd($product);
        return view('admin.products.edit',compact('product','images'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        // dd($request);
        $product = Product::find($id);
        //  $product->insert($request->only($product->getFillable()));

         $product->storage = $request->storage;
         $product->colors = $request->colors;
         $product->ram = $request->ram;
         $product->locked = $request->locked;
         $product->warranty = $request->warranty;
         $product->sell_price = $request->sell_price;
         $product->original_price = $request->original_price;
         $product->desc = $request->desc;
         $product->display = $request->display;
         $product->cameraMp = $request->cameraMp;
         $product->OS = $request->OS;
         $product->resolution = $request->resolution;
         $product->quantity = $request->quantity;

         $product->category = $request->category;
         $product->model_id = $request->model_Id;
         $product->update();
        //  dd($product);


                $q = "DELETE pp FROM `product_images` pp
                    join products pd on pp.product_id = pd.id
                    WHERE pd.id = ?";


                $status = \DB::delete($q, array($id));


        foreach($request->file('image') as $image)
        {
            $imageName= time().$image->getClientOriginalName();
            $image->move('storage/products/images/', $imageName);

            $imagefile = new ProductImage;
            $imagefile->image ='storage/products/images/'. $imageName;
            $imagefile->product_id = $product->id;
            $imagefile->save();

        }

        return back()->with('message', Alert::_message('success', 'Product Updated Successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $product = Product::find($id);
        // dd($product);
        ProductImage::where('product_id',$product->id)->delete();

        $product->delete();
        return back()->with('message', Alert::_message('success', 'Product Deleted Successfully.'));


    }
//Store Product Ajax
  public function storeProduct(Request $request)
    {

          ;
        //         DB::beginTransaction();
        // dd($request->all());



        // try {
            $product = new Product;
            //  $product->insert($request->only($product->getFillable()));

            $product->category = $request->category;
            $product->memory = $request->memory;
            $product->locked = $request->locked;
            $product->warranty = $request->warranty;
            $product->desc = $request->desc;
            $product->screen_size = $request->screen_size;
            $product->megapixel = $request->megapixel;
            $product->OS = $request->OS;
            $product->resolution = $request->resolution;
            $product->screen_type = $request->screen_type;
            $product->network = $request->network;
            $product->sim_card_format = $request->sim_card_format;
            $product->double_sim = $request->double_sim;
            $product->release_year = $request->release_year;
            $product->model_id = $request->model_id;
            //  dd($product);
            $product->save();



            foreach($request->color_name as $key=> $colors)
            {

                $color = new ProductColor;
                $color->color_name = $colors;
                $color->product_id = $product->id;
                $color->save();

                foreach($request->storage[$key] as $key2=>$storages)
                {

                    $storage = new ProductStorage;
                    $storage->storage = $storages;
                    $storage->color_id = $color->id;
                    $storage->save();


                foreach($request->condition[$key2] as $key3=>$condit)
                {
                    //  dd($condition);
                    $condition = new ProductCondition;
                    $condition->condition =$condit;
                    $condition->price = $request->price[$key2][$key3];
                    $condition->quantity = $request->quantity[$key2][$key3];
                    $condition->storage_id = $storage->id;
                    $condition->save();
                }
            }



            foreach($request->file('image')[$key] as $image)
                {

                    $imageName= time().$image->getClientOriginalName();
                    $destination ='storage/products/images/';
                    $image->move(public_path($destination), $imageName);

                    // dd($imageName);
                    $imagefile = new ProductImage;
                    $imagefile->image = $imageName;
                    $imagefile->product_id = $product->id;
                    $imagefile->color_id = $color->id;
                    $imagefile->save();
                    // dd($request->condition);




        }


        }


        //     DB::commit();

        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return back()->with('message', Alert::_message('success', 'somthing wrong.'));
        // }

    return response()->json($product);
  }

    public function storeMoreProduct(Request $request)
    {
        //    dd($request->all());

           $product_id = $request->product_id;

           foreach($request->color_name as $key=> $colors)
           {

               $color = new ProductColor;
               $color->color_name = $colors;
               $color->product_id = $product_id;
               $color->save();

               foreach($request->storage[$key] as $key2=>$storages)
               {

                   $storage = new ProductStorage;
                   $storage->storage = $storages;
                   $storage->color_id = $color->id;
                   $storage->save();


               foreach($request->condition[$key2] as $key3=>$condit)
               {
                   //  dd($condition);
                   $condition = new ProductCondition;
                   $condition->condition =$condit;
                   $condition->price = $request->price[$key2][$key3];
                   $condition->quantity = $request->quantity[$key2][$key3];
                   $condition->storage_id = $storage->id;
                   $condition->save();
               }
           }



           foreach($request->file('image')[$key] as $image)
               {

                   $imageName= time().$image->getClientOriginalName();
                   $destination ='storage/products/images/';
                   $image->move(public_path($destination), $imageName);

                   // dd($imageName);
                   $imagefile = new ProductImage;
                   $imagefile->image = $imageName;
                   $imagefile->product_id = $product_id;
                   $imagefile->color_id = $color->id;
                   $imagefile->save();
                   // dd($request->condition);




        }

        }

    return response()->json($product_id);
   }
    /// Frontend buy phones

    public function getPhones()
    {
        $products = Product::where('category','phone')->paginate(4);
        // $colors  = ProductColor::all();
        if(Auth::check())
        {
            $products = Product::where('category','phone')->paginate(4);
            $wishlist  = Wishlist::where('user_id',Auth::user()->id)->first();
            return view('frontend.buy-phone',compact('products'));
        }

        return view('frontend.buy-phone',compact('products'));
    }

   public function productDetail($id)
   {
       $colors = Product::find($id)->color;
       $product = Product::find($id);
    //    dd($product);
       $color = ProductColor::where('product_id',$id)->first();

       $storage = ProductStorage::where('color_id',$color->id)->first();
       $model = Pmodel::where('id',$product->model_id)->first();
       $images = ProductImage::where('color_id',$color->id)->get();
    //    dd($images);
       $condition = ProductCondition::where('storage_id',$storage->id)->first();
       return view('frontend.single',compact('product','color','model','condition','images','storage',
       'colors'));
   }


   public function getStorage($id)
   {
    //    dd($id);
       $color = ProductColor::find($id);
       $storages = ProductStorage::where('color_id',$color->id)->get();
       $images = ProductImage::where('color_id',$color->id)->get();

       $temp= null;

       foreach($storages as $storage)
       {

            $temp .='<input type="hidden" name="storageId" id="storageId" value="'.$storage->storage.'">
                    <div class="select-color">
                        <input type="radio" name="storage" class="hidden" id="'.$storage->id.'">
                        <label class="color" for="'.$storage->id.'" onclick="geCondition('.$storage->id.')">
                            '.$storage->storage.'
                        </label>
                    </div>';
        }


       $img = null;
        foreach ($images as $image )
        {

             $img .='<div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-360px, 0px, 0px); transition: all 0.75s ease 0s; width: 720px;"><div class="owl-item" style="width: 360px; margin-right: 0px;">
                        <li>
                        <a href="'.asset('storage/products/images/'.$image->image).'" class="lightbox-image" title="Image Caption Here">
                         <img src="'.asset('storage/products/images/'.$image->image).'" alt=""></a>
                     </li></div>';
         }

         $imgg = null;
        foreach($images as $img)
        {
            $imgg .='<li><img src="'.asset('storage/products/images/'.$image->image).'" alt=""></li>';
        }
        return response()->json(['temp'=>$temp ,'img'=> $img,'color'=>$color->color_name,'imgg'=>$imgg]);
        //return view(['frontend.productmanagment.get-storage'=>$storages,'teams'=>teamInfo,'points'=>pointslist]);
    //return view('frontend.productmanagment.get-storage',compact('storages'));

   }

   public function getCondition($id)
   {
     $storage = ProductStorage::find($id);
    //  dd($storage->storage)
     $conditions = ProductCondition::where('storage_id',$storage->id)->get();

     $condit = null;

      foreach($conditions as $condition)
      {
        $condit .=' <div class="select-color">

                    <input type="radio" name="condition" class="hidden condition" id="'.$condition->condition.'">
                    <label class="color" for="'.$condition->condition.'"  onclick="getPrice('.$condition->price.','.$condition->quantity.','.$condition->id.')">
                    '.$condition->condition.' <br> $'.$condition->price.'
                    </label>
                    </div>';
      }

      return response()->json(['condit'=>$condit,'storage'=>$storage->storage]);
   }


///Add To Cart
   public function addToCart(Request $request)
   {
    //    dd($request->all());

       $product = Product::find($request->product);
       $condit = ProductCondition::find($request->condition);
    //    $color = ProductColor::find($request->colorId);
    //    $storage = ProductStorage::find($request->storageId);

       $id = mt_rand(100, 9000);

       if(Auth::guard('web')->check())
       {
         $userID = Auth::user()->id;

         $cart= \Cart::session($userID)->add(array(
        'id' =>  $id,
        'name' =>  $request->brand_name.' '.$request->model_name,
        'price' => $request->getprice,
        'quantity' => $request->quantity,
        'attributes' => array(
                        'userID' => $userID,
                        'storage' => $request->getStorages,
                        'color' => $request->getcolor,
                        'conditition' => $condit->condition

                        ),
        'associatedModel' => $product

    ));


     $items=\Cart::session($userID)->getContent();
    // dd($items);
      return response()->json(['status'=>'Successfully item add into your cart!']);
        }
        else{
            return response()->json(['login' => '']);
        }
   }

   public function cartUpdate(Request $request)
   {
       // dd($request->all());
       $userID = Auth::user()->id;
       if ($request->quantity == 0) {
           \Cart::remove($request->id);
       }
       \Cart::session($userID)->update(
           $request->id,
           array(
               'quantity' => array(
                   'relative' => false,
                   'value' => $request->quantity
               ),
           )
       );
       return response()->json();
   }
   public function remove(Request $request)
   {
    //    dd($request->id);
       $userID = Auth::user()->id;
       \Cart::session($userID)->remove($request->id);
       return response()->json();
   }
   public function viewToCart()
   {
       return view('frontend.viewCart');
   }

    public function payment(Request $request)
    {
        $userID = Auth::user()->id;
        $cartCollection=\Cart::session($userID)->getContent();
        $data = $cartCollection->all();
        //    dd($request->all());
        // dd($cartCollection);

        if($request->payment == "cash")
        {
            foreach ($cartCollection as $cart) {

                $model = Pmodel::where('id',$cart->associatedModel->model_id)->first();
                $color = ProductColor::where('product_id',$cart->associatedModel->id)->first();
                $storage = ProductStorage::where('color_id',$color->id)->first();
                $total = round($cart->quantity*$cart->price);
                // dd($cart->attributes->color);
                $order = new Order;
                $order->user_id = $userID;
                $order->product_id = $cart->associatedModel->id;
                $order->shipAddress_id = $request->address_id;
                $order->brand_name = $model->brand->brand_name;
                $order->model_name  = $model->model_name;
                $order->color       =  $cart->attributes->color;
                $order->condition   = $cart->attributes->conditition;
                $order->storage     = $cart->attributes->storage;
                $order->quantity     = $cart->quantity;
                $order->price     = $cart->price;
                $order->grand_price  =$total;
                $order->payment_method = "Cash";
                $order->status = 1;

                $order->save();

                $condition = ProductCondition::where('storage_id',$storage->id)->first();
             if($cart->quantity <= $condition->quantity)
              {
                 $condition->increment('quantity',$cart->quantity);
              }
              else
              {
                  return redirect()->route('view.cart')->with('status' ,'Enough Quantity of:' . $condition->name);
              }

            }
        //  dd($order);

        $total = \Cart::session($userID)->getTotal();
            $details = [
                'title' => 'Mail from PeekInternational.com',
                'subject' => 'Dear Customer ,',
                'message' => 'Payment completed Successfully through Cash',
                'Total'  => $total
            ];
             $messgae = "Succesfully Transferred";
             \Mail::to(Auth::user()->email)->send(new TechMail($details));
            //  return response()->json($messgae);

            $phone = "+".Auth::user()->phoneno;
            //  dd($phone);
             $message =strip_tags(nl2br("Dear Customer, \n You have Successfully Pay  through Cash . \n Total Amount : $". $total));

             $account_sid = "AC6769d3e36e7a9e9ebbea3839d82a4504";
           $auth_token = "b2229f79769f0b47fa8e7bb685291d0d";
           $twilio_number = +15124027605;
             $client = new Client($account_sid, $auth_token);
             $client->messages->create($phone,
                 ['from' => $twilio_number, 'body' => $message] );


                 \Cart::clear();
                 return redirect()->route('view.cart');
        }
    elseif($request->payment == "paypal")
    {

           $total = \Cart::session($userID)->getTotal();
           $desc= $request->address_id;

        $apiContext = new ApiContext(
          new OAuthTokenCredential(
            'AY9mTzyew4I5bQDY82ZT23Hw6CVvRNN_gxGdFNFD1dBeP_JtMjM2ubFS8NkFqjnieO_nJ-g54ZZEiwB5',
            'EKdd3HTSiu1Rgptb7VZfEY2zON7xdsBpCRjdEVvl36u54DO7_AWmyChF-zpIo7l6LWwlETL4vUnCxN0n'
               )
      );
// dd($apiContext);
      $payer = new Payer();
      $payer->setPaymentMethod("paypal");
      // dd($payer);
      // Set redirect URLs
      $redirectUrls = new RedirectUrls();
      $redirectUrls->setReturnUrl(route('paypal.successProduct'))
          ->setCancelUrl(route('paypal.cancelProduct'));
      // dd($redirectUrls);
      // Set payment amount
      $amount = new Amount();
      $amount->setCurrency("USD")
          ->setTotal($total);


      // Set transaction object
      $transaction = new Transaction();
      $transaction->setAmount($amount)
          ->setDescription($desc);
      //   dd($transaction);
      // Create the full payment object
      $payment = new Payment();
      $payment->setIntent('sale')
          ->setPayer($payer)
          ->setRedirectUrls($redirectUrls)
          ->setTransactions(array($transaction));
      // dd($payment);
      // Create payment with valid API context
      try {

          $payment->create($apiContext);
          // dd($payment);
          // Get PayPal redirect URL and redirect the customer
          // $approvalUrl =
          return redirect($payment->getApprovalLink());
          // dd($approvalUrl);
          // Redirect the customer to $approvalUrl
      } catch (PayPalConnectionException $ex) {
          echo $ex->getCode();
          echo $ex->getData();
          die($ex);
      } catch (Exception $ex) {
          die($ex);
      }
  }



        else
        {
            return back();
        }
    }

    public function success(Request $request)
{
    $apiContext = new ApiContext(
        new OAuthTokenCredential(
            'AY9mTzyew4I5bQDY82ZT23Hw6CVvRNN_gxGdFNFD1dBeP_JtMjM2ubFS8NkFqjnieO_nJ-g54ZZEiwB5',
            'EKdd3HTSiu1Rgptb7VZfEY2zON7xdsBpCRjdEVvl36u54DO7_AWmyChF-zpIo7l6LWwlETL4vUnCxN0n'
                    )
    );

    // Get payment object by passing paymentId
    $paymentId = $_GET['paymentId'];
    $payment = Payment::get($paymentId, $apiContext);
    $payerId = $_GET['PayerID'];

    // Execute payment with payer ID
    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    try {
        // Execute payment
        $result = $payment->execute($execution, $apiContext);
        // dd($result->transactions[0]->amount->total);
        // $str = $result->transactions[0]->description;
        // $id = $str;
        // $total = $result->transactions[0]->amount->total;

        $userID = Auth::user()->id;
        $cartCollection=\Cart::session($userID)->getContent();
        foreach ($cartCollection as $cart) {

            $model = Pmodel::where('id',$cart->associatedModel->model_id)->first();
            $color = ProductColor::where('product_id',$cart->associatedModel->id)->first();
            $storage = ProductStorage::where('color_id',$color->id)->first();
            $total = round($cart->quantity*$cart->price);
            $condition = ProductCondition::where('storage_id',$storage->id)->first();
            if($cart->quantity <= $condition->quantity)
            {
               $condition->increment('quantity',$cart->quantity);
            }
            else
            {
                return redirect()->route('view.cart')->with('status' ,'Enough Quantity of:' . $condition->name);
            }
            // dd($cart->attributes->color);
            $order = new Order;
            $order->user_id = $userID;
            $order->product_id = $cart->associatedModel->id;
            $order->shipAddress_id = $shipAdress_id;
            $order->brand_name = $model->brand->brand_name;
            $order->model_name  = $model->model_name;
            $order->color       =  $cart->attributes->color;
            $order->condition   = $cart->attributes->conditition;
            $order->storage     = $cart->attributes->storage;
            $order->quantity     = $cart->quantity;
            $order->price     = $cart->price;
            $order->grand_price  =$total;
            $order->payment_method = "PayPal";
            $order->status = 1;

            $order->save();
            // dd($order);



        }
        $total = \Cart::session($userID)->getTotal();
            $details = [
                'title' => 'Mail from PeekInternational.com',
                'subject' => 'Dear Customer ,',
                'message' => 'Payment completed Successfully through PayPal',
                'Total'  => $total
            ];
             $messgae = "Succesfully Transferred";
             \Mail::to(Auth::user()->email)->send(new TechMail($details));
            //  return response()->json($messgae);


            $phone = "+".Auth::user()->phoneno;
            //  dd($phone);
             $message =strip_tags(nl2br("Dear Customer, \n You have Successfully Pay  through PayPal . \n Total Amount : $". $total));

             $account_sid = "AC6769d3e36e7a9e9ebbea3839d82a4504";
             $auth_token = "b2229f79769f0b47fa8e7bb685291d0d";
             $twilio_number = +15124027605;
             $client = new Client($account_sid, $auth_token);
             $client->messages->create($phone,
                 ['from' => $twilio_number, 'body' => $message] );

                 \Cart::clear();
                 return redirect()->route('view.cart');

    } catch (PayPalConnectionException $ex) {
        echo $ex->getCode();
        echo $ex->getData();
        die($ex);
    } catch (Exception $ex) {
        die($ex);
    }
}

  public function cancel()
{
        dd('payment cancel');
}



   /// ------------------Filter-----------------////
   public Function getBrandFilter(Request $request)
    {
        //  dd($request->all());
            //  $data = collect([$request->selectedModel]);
            //  dd($data);

         if(isset($request->brand))
         {
            $models = Pmodel::whereIn('brand_Id',explode(',',$request->brand))->get();
            // dd($model);

            $products = DB::table('products')
                          ->join('pmodels','pmodels.id','=','products.model_id')
                          ->join('brands','brands.id','=','pmodels.brand_Id')
                          ->whereIn('brands.id',explode(',',$request->brand))
                          ->select('products.*')
                          ->get();



           $prod = null;
              foreach($products as $product)
              {
                $color = ProductColor::where('product_id',$product->id)->first();
                $storage = ProductStorage::where('color_id',$color->id)->first();
                $model = Pmodel::where('id',$product->model_id)->first();
                $image = ProductImage::where('product_id',$product->id)->first();
                $condition = ProductCondition::where('storage_id',$storage->id)->first();


               $prod .='<div class="shop-item col-md-4 col-sm-6 col-xs-12"><div class="inner-box">';
                      if(Auth::user()){

                            if (CityClass::checkWishlist($product->id) == "1")
                            {
                                $prod.='<a href="#" onclick="undoWishlist()"><i class="fa fa-heart" style="font-size: 30px;color:#ff0707"></i></a>';
                             } else{
                            $prod.='<a href="#" onclick="wishlist('.$product->id.')"><i class="fa fa-heart" style="font-size: 30px;"></i></a>';
                             }
                        }
                        else{
                        $prod.=' <a href="#" onclick="wishlist('.$product->id.')"><i class="fa fa-heart" style="font-size: 30px;"></i></a>';
                    }

                  $prod .=' <figure class="image-box">';
                  $prod .='<a href="'.route('product.details',$product->id) .'"><img src="'.asset('storage/products/images/'.$image->image).'" alt="" /></a>';
                  $prod .='  </figure>';

                    $prod .=' <div class="lower-content">';
                    $prod .='<h3><a href="">'. $model->brand->brand_name .'  '.$model->model_name .' </a></h3>';
                    $prod .='<div> <span>'. $storage->storage .' - '.$color->color_name.' - '. $product->locked .'</span> </div>';
                    $prod .='<span>';
                    $prod .=' Warranty: '. $product->warranty .' ';
                    $prod .='</span>';
                    $prod .='<div class="brand-imgs">';
                    $prod .='<div class="brand">';
                                $prod .='<img src="'.asset('frontend-assets/images/tmobile.svg').'">';
                            $prod .='</div>';
                            $prod .='<div class="brand">';
                                $prod .='<img src=" '.asset('frontend-assets/images/att.svg').'">';
                            $prod .='</div>';
                            $prod .='<div class="brand">';
                               $prod .=' <img src=" '.asset('frontend-assets/images/verizon.svg').'">';
                            $prod .='</div>';
                           $prod .=' </div>';
                        $prod .='<div>Starting from</div>';
                        $prod .='<div class="price">';
                        $prod .='<strong>$ '. $condition->price ?? '' .'.00</strong> <del>$'. $product->original_price ?? '' .'</del></div>';

                    $prod .='</div>';
                    $prod .='</div>';
                    $prod .='</div>';
                    $prod .='</div>';
              }


              $mod = null;

              foreach($models as $model)
              {
                $mod .='<li data-test="facet-item" class="_33pDOgQ80LhcEmJTGXNM3U">

                    <input id="'. $model->id .'" type="checkbox" name="models_name" data-test="facet-'. $model->brand->brand_name ?? '' .'  '. $model->model_name ?? ''.'" class="_3wvnh-Qn  getModelId"  value="'. $model->id .'" onclick="getModels('. $model->id .')">
                    <label for="'. $model->id  .'" class="_33K8eTZu">
                    <div class="_3S4CObWg">
                        <div class="_2OVE0h6V"></div>
                        <div class="_3xAYCg9N">
                            <svg aria-hidden="true" fill="currentColor" height="20" viewBox="0 0 40 40" width="20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.43 25a1 1 0 01-.71-.29l-5.84-5.84a1 1 0 010-1.41 1 1 0 0 1 1.42 0l5.13 5.13 8.23-8.24a1 1 0 011.42 0 1 1 0 0 1 0 1.41l-8.95 9a1 1 0 01-.7.24z"></path> <!----></svg>
                            </div>
                        </div>
                            <div class="TRSMTVTh"><span class="_28IelIKC"><span class="_28IelIKC _1LYyf7lOuywpdBWUdNvl1k">
                                '. ucwords($model->brand->brand_name) ?? '' .'  '. ucwords($model->model_name) ?? ''.'
                            </span>
                            </span>
                        </div>
                    </label>

                </li>';
              }

              return ['product' => $prod , 'modd' => $mod];
                // view('frontend.filterProduct.getBrand',compact('products','model'));
         }

         elseif(isset($request->model))
         {
              $model=$request->model;
              $products = Product::whereIn('model_id',explode(',',$model))->get();
            //  dd($model);
            //    response()->json(['product'=>$products,'model'=>$model]);
             return view('frontend.filterProduct.getBrand',compact('products'));
         }

         elseif(isset($request->getCondition))
         {
            //  dd(explode(',',$request->getCondition));

                if(isset($request->selectedModel))
               {
                $model=$request->selectedModel;
                // $model=$request->model;
                $products = Product::whereIn('model_id',explode(',',$model))->get();
                // dd($products);

                $modelName =  $products->pluck("id");
                $modelID = $modelName->all();
                // dd(implode(",",$modelID));
                $producdID = implode(",",$modelID);


                 $products = DB::table('product_conditions')
                            ->join('product_storages','product_storages.id','=','product_conditions.storage_id')
                            ->join('product_colors','product_colors.id','=','product_storages.color_id')
                            ->join('products','products.id','=','product_colors.product_id')
                            ->whereIn('products.id',explode(',',$producdID))
                            ->whereIn('product_conditions.condition',explode(',',$request->input('getCondition')))
                            ->select('*','products.id')
                            ->get();


                //    dd($products);
                  return view('frontend.filterProduct.getCondition',compact('products'));
               }
         }
         elseif(isset($request->getStorage))
         {
            if(isset($request->selectedModel))
            {
             $model=$request->selectedModel;

             $products = Product::whereIn('model_id',explode(',',$model))->get();
             $modelName =  $products->pluck("id");
             $modelID = $modelName->all();
             $producdID = implode(",",$modelID);

             $products = DB::table('product_storages')
                            ->join('product_colors','product_colors.id','=','product_storages.color_id')
                            ->join('products','products.id','=','product_colors.product_id')
                            ->join('product_conditions','product_conditions.storage_id','=','product_storages.id')
                            ->whereIn('products.id',explode(',',$producdID))
                            ->whereIn('product_storages.storage',explode(',',$request->input('getStorage')))
                            ->select('*','products.id')
                            ->get();


             return view('frontend.filterProduct.getCondition',compact('products'));

            }
            else
            {
                $products = DB::table('product_storages')
                ->join('product_colors','product_colors.id','=','product_storages.color_id')
                ->join('products','products.id','=','product_colors.product_id')
                ->join('product_conditions','product_conditions.storage_id','=','product_storages.id')
                ->whereIn('product_storages.storage',explode(',',$request->input('getStorage')))
                ->select('*','products.id')
                ->get();


            return view('frontend.filterProduct.getCondition',compact('products'));
            }
         }
         elseif(isset($request->getLocked))
         {
            //  dd($request->all());
            if(isset($request->selectedModel))
            {
             $model=$request->selectedModel;

             $products = Product::whereIn('model_id',explode(',',$model))->where('locked',explode(',',$request->input('getLocked')))->get();

            //    dd($products);
             return view('frontend.filterProduct.getLocked',compact('products'));

            }
            else
            {
                $products = Product::whereIn('locked',explode(',',$request->input('getLocked')))->get();
                 return view('frontend.filterProduct.getLocked',compact('products'));
            }
         }

         elseif(isset($request->maxPrice) || isset($request->minPrice))
         {
             $start = $request->minPrice;
             $end   =$request->maxPrice;
            $products = DB::table('product_conditions')
            ->join('product_storages','product_storages.id','=','product_conditions.storage_id')
            ->join('product_colors','product_colors.id','=','product_storages.color_id')
            ->join('products','products.id','=','product_colors.product_id')
            ->where('product_conditions.price','>=',$start)
            ->where('product_conditions.price','<=',$end)
            ->select('*','products.id')
            ->get();
        //    dd($products);
           return view('frontend.filterProduct.getCondition',compact('products'));
         }


    }



}
