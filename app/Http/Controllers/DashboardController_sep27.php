<?php

namespace App\Http\Controllers;

use App\Models\MasterKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\OutletController;



use App\User;
use DB;

class DashboardController extends Controller
{
    public function index()
    {

        $stat = auth()->user()->status;
        $user = auth()->user();
        $userid=$user->id;
        $packageid=$user->package_id;



       
        $outletlist = DB::table('outlet_list')->where("user_id",$userid)
            ->join('mdlz_main_channel_master', 'outlet_list.channel_name', '=', 'mdlz_main_channel_master.refid')
            ->join('mdlz_channel_master', 'outlet_list.sub_channel_name', '=', 'mdlz_channel_master.refid')
            ->select('outlet_list.*', 'mdlz_main_channel_master.name as channel', 'mdlz_channel_master.name as subchannel')
            ->get();

        $reports_to= DB::table('users')->where([["reports_to","=",$userid],["status","=","Active"]])            
            ->select('users.*')
            ->orderBy('users.firstname', 'ASC')
            ->get();
            $channel_list =[];
        if($user->client_id==100)
        {
          $channel_list =  DB::table('uncovered_outlets')->whereIn('salesman_id',[$userid])->distinct()->get(['maintype_id','main_type']);
        }
        

       

        if($stat == 'Active') {
          $channel = DB::table('mdlz_main_channel_master')->where('stat', 'A')->select(['refid','name'])->get();
        
          return view('pages.dashboard',['channel'=>$channel,'list_outlet'=>$outletlist,'usertype'=>$user->user_type,'subordinate'=>$reports_to,'channel_list'=>$channel_list]);
        } else {
          return Redirect::to('/auth/login')->with('message',"Get approval from Admin Team from BrandIdea !!! Thank You");
        }
        
    }
    public function show()
    {
       //var_dump($this->changejson());
       //var_dump($this->comparejson());
    }
     public function getsubchannel($id) 
    {        
            $subchannel = DB::table("mdlz_channel_master")->where("fld1751",$id)->pluck("name","refid");
            return json_encode($subchannel);
    }
    public function deleteoutlet($id)
    {
         $result=DB::table('outlet_list')->where('refid', '=', $id)->delete();
         if($result)
         {
                $message['status']='success';
                $message['msg']='Outlet deleted successfully';
          }
          else
          {
                $message['status']='failure';
                $message['msg']='Outlet not deleted.';
          }

            return json_encode($message);

    }
    public function updatestatus(Request $request)
    {
       $input=$request->all();
       $user = auth()->user();
       $userid=$user->id;
       $message=array();

       //$sql="update uncovered_outlets set status='".$input['status']."' where id='".$input['outlet_id']."'";
       
       $sql = DB::table('uncovered_outlets')->where('fld580',$input['outlet_id'])->update(['status' => $input['status']]);
       if($sql)
       {
            $message['status']='success';
            $message['msg']='Outlet status updated successfully';

       }
       else
       {
                $message['status']='failure';
                $message['msg']='Outlet status not updates.';
       }

            return json_encode($message);
       



    }
    public function updateoutlet(Request $request)
   {
      $input = $request->all();
      $result=DB::table('ref_24sep2021')
        ->where('refid', $input['outlet_id'])       
        ->update(array('status' =>$input['status']));
        $message=[];      
        $message['status']='success';
        $message['msg']='Outlet status updated.';
        return json_encode($message);


   }
    public function shownearoutlet(Request $request)
    {

       $input=$request->all();
       $user = auth()->user();
       $userid=$user->id;
       if(isset($request['center_coordinates']))
       {
          $lat=$request['center_coordinates'][0];
          $lon=$request['center_coordinates'][1];

          $query = "select refid,name,channel,address,latitude,longitude,distance,name,icon,shop_image from (SELECT fld580 as refid,ccpname as name,name as channel,address,latitude,longitude,icon,shop_image, (((acos(sin((".$lat."*pi()/180)) * sin((`latitude`*pi()/180)) + cos((".$lat."*pi()/180)) * cos((`latitude`*pi()/180)) * cos(((".$lon."- `longitude`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance FROM uncovered_outlets) as a where a.distance < 0.2";
        
          $uncovered_outlets = DB::select(DB::raw($query));

          $uncovered_outlet=[];

          for($k=0;$k<count($uncovered_outlets);$k++)
          {
                array_push($uncovered_outlet,array('refid'=>$uncovered_outlets[$k]->refid,'outlet_name'=>$uncovered_outlets[$k]->name,'channel_name'=>$uncovered_outlets[$k]->channel,'sub_channel_name'=>'','address'=>$uncovered_outlets[$k]->address,'lat'=>$uncovered_outlets[$k]->latitude,'lon'=>$uncovered_outlets[$k]->longitude,'icon'=>$uncovered_outlets[$k]->icon,'shop_image'=>$uncovered_outlets[$k]->shop_image));
          }

           
          return response()->json($uncovered_outlet);
          


       }



    }
    
    public function addoutlet(Request $request) 
    {        
            $input=$request->all();
            $user = auth()->user();
            $userid=$user->id;
            $message=[];
          //  $request->validate([
          //   'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
          // ]);

           $imagePath = $request->file('img');
           $imageName = date('d-m-y').'_'.$imagePath->getClientOriginalName();
           $path = $request->file('img')->storeAs('shop_image', $imageName, 'shop_snap');
       
           $outlet = new OutletController;
           $outlet->outlet_name = $request['outlet_name'];
           $outlet->owner_name = $request['owner_name'];
           $outlet->channel_name = 1;
           $outlet->sub_channel_name = $request['sub_channel_name'];
           $outlet->address = $request['address'];
           $outlet->shop_image = $path;
           $outlet->user_id=$userid;
           $outlet->pan_no = $request['pan_no'];
           $outlet->tan_no = $request['tan_no'];
           $outlet->mobile_no = $request['mobile_no'];
           $outlet->shop_establish_no = $request['shop_establish_no'];
           $outlet->gst_no = $request['gst_no'];
           $geo=explode(",",$request['gio_point']);
           $outlet->lat=(count($geo) >1) ? $geo[0] : '';
           $outlet->lon=(count($geo) >1) ? $geo[1] : '';
           $result=DB::table('outlet_list')->insert([
              ['outlet_name' => $request['outlet_name'],'owner_name'=> $request['owner_name'],
           //'channel_name'=> $request['channel_name'],
              'channel_name'=> 1,
           'sub_channel_name'=> $request['sub_channel_name'],
           'address'=> $request['address'],
           'shop_image' =>$path,
           'user_id'=>$userid,
           'pan_no'=> $request['pan_no'],
           'tan_no' =>$request['tan_no'],
           'mobile_no' => $request['mobile_no'],
           'shop_establish_no' => $request['shop_establish_no'],
           'gst_no' => $request['gst_no'],
           'lat' => $outlet->lat,
           'lon' => $outlet->lon]
          ]);
           
           if($result)
             {

               $outletlist = DB::table('outlet_list')->where([["user_id","=",$userid]])
            ->join('mdlz_main_channel_master', 'outlet_list.channel_name', '=', 'mdlz_main_channel_master.refid')
            ->join('mdlz_channel_master', 'outlet_list.sub_channel_name', '=', 'mdlz_channel_master.refid')
            ->select('outlet_list.*', 'mdlz_main_channel_master.name as channel', 'mdlz_channel_master.name as subchannel')
            ->get();

            $outletlist_data=[];
            for($i=0;$i<count($outletlist);$i++)
            {
               $temp=[];
               $temp['refid']=$outletlist[$i]->refid;
               $temp['outlet_name']=$outletlist[$i]->outlet_name;
               $temp['channel']=$outletlist[$i]->channel;
               $temp['subchannel']=$outletlist[$i]->subchannel;
               array_push($outletlist_data,$temp);
            }
            


                $message['status']='success';
                $message['msg']='Outlet added successfully';
                $message['outletlist']=$outletlist_data;


             }
            else
            {
                $message['status']='failure';
                $message['msg']='Outlet not added.';
            }

            return json_encode($message);
          
    }
    public function add()
    {
          $channel = DB::table('mdlz_main_channel_master')->where('stat', 'A')->select(['refid','name'])->get();
          return view('outlet/add',['channel'=>$channel]);
    }

    public function changejson()
    {
        $dir='D:\biappserver\htdocs\bimondlz_app\storage\app\map_shape\1\1\15_16\1';

       $files = scandir($dir);$path='';
      

       for($i=0;$i<count($files);$i++)
       {

          if($files[$i] != '.' && $files[$i] != '..')
          {
             $loadmap= 'map_shape/1/1/15_16/1/'.$files[$i];
             $tempcontent = Storage::get($loadmap);
             $name=explode(".",$files[$i]);

             $tempmap='1/'.$name[0].'.js';

              Storage::delete('map_uploads/'.$tempmap);

              if(!Storage::disk('map_uploads')->put($tempmap, 'var rs='.$tempcontent)) {
                    return false;
              }

              $path .=   url('/').'/mapshape/1/'.$tempmap;

            

          }

       }
       return $path;
    }
     public function comparejson()
    {

       $dir='D:\biappserver\htdocs\converter_locality\Json\Agra';

       $files = scandir($dir);$path='';
       $filearray=[];

       for($i=0;$i<count($files);$i++)
       {

          if($files[$i] != '.' && $files[$i] != '..')
          {

               $exe=explode("_",$files[$i]);
               $filearray[$exe[0]]=$exe[0].' exists';


          }

       }

//13941 - Ahmadabad -- gj_d_15_ct_2
//13346 - mumbai --mh_d_34_ct_1
       //1074 - Coimbatore -tn_12_6_ct_23
       //14216 - Gurugram -hr_d_19_ct_3
       //18590 - Haora -wb_d_16_ct_1
       //18322 - Hyderabad - ap_d_23_ct_1
       //786 -Jaipur - rj_d_15_ct_1
       //15196- Kochi -kl_8_3_ct_14
       //13731-Ludhiana-pb_d_11_ct_1
       //13509-Pimpri Chinchwad-mh_d_22_ct_1
       //13577-Thane-mh_d_20_ct_2
       //18886-Delhi - dl_d_1_ct_22

         $sql="select refid as loc_id,location_name as name,refid,1872 as pc_uid,map_id from ward_master where city_id=1 and stat='A' ";
         $res = DB::select(DB::raw($sql));   
         $str=''; 
        for ($i=0; $i <count($res) ; $i++) {

           if(isset($filearray[$res[$i]->refid]))
                $str .='<b>'. $res[$i]->refid.' | '. $res[$i]->name.' | '.$res[$i]->map_id.' | Exist</b><br>';
            else
                $str .= '<b>'. $res[$i]->refid.' | '. $res[$i]->name.' | '.$res[$i]->map_id.' | Not Exist</b><br>';




        }

       
       return $str;
    }
   
    
     public function loadmapPost(Request $request)
    {
        $input = $request->all();
        $load_data=array();
         $user = auth()->user();
         $user_id=$user->id;
         $subname='';


      if(!empty($input) && isset($input['initialmap']))
      {
        $load_file_list=[];
        $map_level = DB::table('map_level')->where('refid', 26)->select(['refid','map_label','main_location','sub_location','sub_location_temp','suffix','child'])->first();
        $geo_level = DB::table('Geo_Hrchy_master')->where('refid', $map_level->sub_location)->select(['geo_level','name1','name2','master_table'])->first();
        $geo_table = DB::table('Geo_Hrchy_master')->where('refid',  $map_level->main_location)->select(['geo_level','name1','name2','master_table','table_name'])->first();
        $subname_table = DB::table('map_level')->where([['main_location',  $map_level->main_location],['sub_location',$map_level->sub_location]])->select(['map_label'])->first();

       

        // echo  $sql="SELECT distinct(a.loc15) as loc_id,loc12,b.refid,b.name FROM `mdlz_retailer_master` as a, mdlz_distbr_master as b,city_master as c  where a.sheet_ref like '%18%' and a.fld1744=b.refid and loc12=c.refid and a.stat='A' and b.stat='A' and loc15 !=0  and a.salesman_id='".$user_id."' order by b.refid  asc ";die;

        // $sql="SELECT distinct(a.loc15) as loc_id,GROUP_CONCAT(a.loc16) as loc16,loc12 FROM `mdlz_retailer_master` as a, mdlz_distbr_master as b,city_master as c where a.sheet_ref like '%18%' and a.fld1744=b.refid and loc12=c.refid and a.stat='A' and b.stat='A' and loc15 !=0 and a.salesman_id='".$user_id."' group by a.loc15,a.loc12 order by b.refid asc";
         $getfilter=json_decode($input['input']);


        if($user->user_type == 'SO' || $user->user_type=='SUPPORT')
           $so_id=$user->id;
        else if($user->user_type =='ASM' && isset($getfilter->filter_byso) && ($getfilter->filter_byso != ''))
           $so_id=$getfilter->filter_byso;
        else if($user->user_type =='ASM' && isset($getfilter->filter_so) && (count($getfilter->filter_so) > 0))
           $so_id=implode(",",$getfilter->filter_so);
            
          $condn=[];
      
         if(isset($getfilter->filter_pc) && (count($getfilter->filter_pc) > 0))
          {

             $pc_user=implode(",",$getfilter->filter_pc);

             if($pc_user != '')
                  array_push($condn, "and b.pc_uid in (".$pc_user.")");
            

          }
           if(isset($getfilter->filter_byso))
          {

            // $so_id=$getfilter->filter_byso;

             // $subordinate="select group_concat(pc_uid) as pc_uid from users where reports_to in ('".$selected_so_id."') and status='Active' group by reports_to";
             // $res_subordinate = DB::select(DB::raw($subordinate));
             // $selected_pc_user=$res_subordinate[0]->pc_uid;
             // if($selected_pc_user != '')
             //   array_push($condn, "and b.pc_uid in (".$selected_pc_user.")");
            

          }
          
          if(isset($getfilter->filter_distributor) && (count($getfilter->filter_distributor) > 0))
          {
              $distributor_list=implode(",",$getfilter->filter_distributor);
              array_push($condn, "and b.fld1744 in (".$distributor_list.")");
          }
          $criteria=join(" ",$condn);

         $sql="SELECT distinct(d.refid) as loc_id,group_concat(loc16) as loc16,c.loc12,a.pc_uid  FROM `users` as a,loclty_pc_link as b,colony_master as c, ward_master as d  where a.pc_uid=b.pc_uid and b.loc16=c.refid and c.loc15=d.refid and a.reports_to in (".$so_id.") $criteria group by d.refid,c.loc12,a.pc_uid ";

        

    //$sql="select refid as loc_id,city_id as loc12,location_name as name,refid,1872 as pc_uid from ward_master where city_id=13346 and stat='A' ";

        $res = DB::select(DB::raw($sql));
        $result=array(); $message=array(); $message['maplist']=array();
        $nextlevelarray=array();

        for ($i=0; $i <count($res) ; $i++) {  
            $colony_arr = explode(',', $res[$i]->loc16);                   
           
           $next_maptable = DB::table($geo_level->master_table)->where([['loc12','=',$res[$i]->loc12],['loc15','=',$res[$i]->loc_id]])->whereIn('refid',$colony_arr)->select(['refid','location_name','nxt_mp_level','loc_id','latitude','longitude','loc12','loc15'])->get();

            // $next_maptable = DB::table($geo_level->master_table)->where([['loc12','=',$res[$i]->loc12],['loc15','=',$res[$i]->loc_id]])->select(['refid','location_name','nxt_mp_level','loc_id','latitude','longitude','loc12','loc15'])->get();
           $subname=$subname_table->map_label;
           

          for($i_=0;$i_<count($next_maptable);$i_++)
          {
              
               
                  $nextlevelarray[$next_maptable[$i_]->refid]=array('nxt_mp_level'=>$next_maptable[$i_]->nxt_mp_level,'loc_id'=>$next_maptable[$i_]->loc_id,'current_level'=>26,'main_location'=>$map_level->main_location,'sub_location'=>$map_level->sub_location,'location_name'=>$next_maptable[$i_]->location_name,'latitude'=>$next_maptable[$i_]->latitude,'longitude'=>$next_maptable[$i_]->longitude,'loc12'=>$next_maptable[$i_]->loc12,'loc15'=>$next_maptable[$i_]->loc15,'pc_uid'=>$res[$i]->pc_uid);
              
          }
          $country_id=1;


          $loadmap= 'mapshape/'.$res[$i]->loc12.'/'.$res[$i]->loc_id.'_'.$map_level->main_location.'_'.$map_level->sub_location.'.txt';
          //echo $loadmap . "</br>";
          if(!in_array($loadmap, $load_file_list))
          {
            array_push($load_file_list,$loadmap);
            $location_level_id=$res[$i]->loc_id;

            if (file_exists( public_path().'/'.$loadmap)) {
             $path =   url('/').'/'.$loadmap;
             array_push($message['maplist'],$path);
         
            }
          }
          
       
        } 

        $message['map_nextlevel_info']=$nextlevelarray;
        $message['label']='';
        $namespace = "App\Http\Controllers\\";
        $controllerName = $namespace . 'CommonController';
        $combine_obj = new $controllerName();
        $change=json_decode($input['input'],true);
         $value = array_values($nextlevelarray);
        $loc12 = array_unique(array_column($value, 'loc12'));
        $head = CommonController::headline($loc12);
        $data['head'] = $head;
        $message['head']=$data['head'];

        if(count($change)> 0 && isset($input['type']) && $input['type'] != '' )
        {

           $inputtype=isset($input['type']) ? $input['type'] : $input['input']['type'];
           $data=$combine_obj->commonactivity($nextlevelarray,$subname, $inputtype,$map_level->main_location,$map_level->sub_location,$input['input'],$so_id);  
           $message['map_nextlevel_info']=$data['mapdata'];       
           $message['griddata']=$data['griddata'];
           $message['head']=$data['head'];
           $message['maplegend']=$data['maplegend'];     
           if(isset($data['channel_list']))
             $message['channel_list']=$data['channel_list'];    

        }        
        

         return response()->json($message);

        }
        if(isset($input['statuschange']))
        {
            $status=$input['status'];
            $colony=$input['layer'];
            $user_id=$user->id;
            $msg=[];
            $msg['statuschange']='failure';



            if (DB::table('salesman_covered_ward')->where([['colony_id','=',$colony ],['user_id','=',$user_id]])->exists()) 
            {

              if(DB::table('salesman_covered_ward')->where([['colony_id','=',$colony ],['user_id','=',$user_id]])->update(['status' => $status,'modified_date'=>date('Y-m-d H:i:s')])){
                 $msg['statuschange']='success';
                  $msg['msg']='Details updated';
               }
              
                 

            }
            else
            {
                if(DB::table('salesman_covered_ward')->insert([
                  'colony_id' => $colony,
                  'status' => $status,'user_id'=>$user_id
              ]))
                 $msg['statuschange']='success';
                 $msg['msg']='Details added';
            }

              return response()->json($msg);



        }
        if(isset($input['showlist']))
        {
            $type_of_view=$input['showtype'];
            $data=[];
            
               if($type_of_view=='PC')
            {

               $sql="SELECT a.pc_uid,concat(a.firstname,' ',a.lastname) as pc_name  FROM `users` as a where  a.reports_to=".$user_id." order by pc_name asc";
               $res = DB::select(DB::raw($sql));
               $str='';
               $data['msg']='failure';
               if(count($res) > 0)
               {

                   $str='<table id="showlist" class="display" cellspacing="0" style="width:100%">';
                   $str .=' <thead><tr><th class="no-sort"><input type="checkbox" class="checkbox_all"/></th><th>Pc Name</th></tr></thead><tbody>';

                   for($i=0;$i<count($res);$i++)
                   {
                       $str .=' <tr id="'.$res[$i]->pc_uid.'"><td><input type="checkbox" class="checking_box" value="'.$res[$i]->pc_uid.'"/> </td><td>'.$res[$i]->pc_name.'</td></tr>';

                   }
                   $str .= '</tbody></table>';
                   $data['msg']='success';
                   $data['type']='pc';

               }

               

            }
            if($type_of_view=='Distributor')
            {

             $sql="SELECT distinct c.refid,concat(c.distributor_code,'-',c.name) as distributor_name FROM users as a ,loclty_pc_link as b,mdlz_distbr_master as c where a.pc_uid=b.pc_uid and b.fld1744=c.refid and a.reports_to=".$user_id." order by distributor_name asc";
               $res = DB::select(DB::raw($sql));
               $str='';
               $data['msg']='failure';
               if(count($res) > 0)
               {

                   $str='<table id="showlist" class="display" cellspacing="0" style="width:100%">';
                   $str .=' <thead><tr><th class="no-sort"><input type="checkbox" class="checkbox_all" /></th><th>Distributor Name</th></tr></thead><tbody>';

                   for($i=0;$i<count($res);$i++)
                   {
                       $str .=' <tr id="'.$res[$i]->refid.'"><td><input type="checkbox" class="checking_box" value="'.$res[$i]->refid.'"/></td><td>'.$res[$i]->distributor_name.'</td></tr>';

                   }
                   $str .= '</tbody></table>';
                   $data['msg']='success';
                   $data['type']='distributor';

               }

               

            }
            $data['list_of_user']=$str;

            return response()->json($data);
    }
}
    
     


}
