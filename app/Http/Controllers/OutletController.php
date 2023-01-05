<?php

namespace App\Http\Controllers;

use App\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

use App\User;
use DB;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function index()
    {
        $outlet = Outlet::all();
        $sql="SELECT a. `refid`, `outlet_name`, `owner_name`, b.name as `channel_name`, c.name as `sub_channel_name`, `address`, `shop_image`, `created_at`, `updated_at`, `user_id`, `pan_no`, `mobile_no`, `tan_no`, `shop_establish_no`, `gst_no`, `lat`, `lon` FROM `outlet_list` as a ,mdlz_main_channel_master as b ,mdlz_channel_master as c where a.channel_name=b.refid and a.sub_channel_name=c.refid ";

        $outlet = DB::select(DB::raw($sql));

        return view('outlet.list', compact('outlet'));
    }
    public function getsubchannel($id) 
    {        
            $subchannel = DB::table("mdlz_channel_master")->where("fld1751",$id)->pluck("name","refid");
            return json_encode($subchannel);
    }
     public function add()
    {
          $channel = DB::table('mdlz_main_channel_master')->where('stat', 'A')->select(['refid','name'])->get();
          return view('outlet/add',['channel'=>$channel]);
    }
     
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'outlet_name' => 'required',          
            'address' => 'required',
            'channel_name' => 'required',
            'sub_channel_name' => 'required',
        ]);
         // if ($request->hasFile('file')) {

         //    $request->validate([
         //        'image' => 'mimes:jpeg,bmp,png' // Only allow .jpg, .bmp and .png file types.
         //    ]);

         //    // Save the file locally in the storage/public/ folder under a new folder named /product
         //    $request->file->store('product', 'public');

         //    // Store the record, using the new file hashname which will be it's new filename identity.
         //    $product = new Product([
         //        "name" => $request->get('name'),
         //        "file_path" => $request->file->hashName()
         //    ]);
        if($validatedData)
        {
        

              $user = auth()->user();
              $userid=$user->id;
              var_dump($request->all());die;
              $outlet = new Outlet([
                "outlet_name" => $request->get('outlet_name'),
                "owner_name" => $request->get('owner_name'),
                "address" => $request->get('address'),
                "channel_name" => $request->get('channel_name'),
                "sub_channel_name" => $request->get('sub_channel_name'),
                "shop_image" => '',
                "user_id"=>$userid,
                "pan_no" => $request->get('pan_no'),
                "mobile_no" => $request->get('mobile_no'),
                "tan_no" => $request->get('tan_no'),
                "shop_establish_no" => $request->get('shop_establish_no'),
                "gst_no" => $request->get('gst_no'),
                "lat" => '',
                "lon" => '',
            ]);
            $outlet->save();
            return redirect('outlet/index');
        }
       

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function show(Outlet $outlet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function edit(Outlet $outlet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Outlet $outlet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Outlet $outlet)
    {
        //
    }
}
