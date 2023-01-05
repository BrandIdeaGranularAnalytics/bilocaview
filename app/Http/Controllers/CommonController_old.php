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
use App\Http\Controllers\ProfileController;

use App\User;
use DB;

class CommonController extends Controller
{
    public function index()
    {

        $stat = auth()->user()->status;
        $user = auth()->user();
        $userid=$user->id;
        $packageid=$user->package_id;
        $contents = Storage::get('client_menu/client_menu_'.$userid.'.json');

        if($stat == 'Active') {
        	return view('pages.dashboard',['menulist'=>$contents,'package_id'=>$packageid]);
        } else {
        	return Redirect::to('/auth/login')->with('message',"Get approval from Admin Team from BrandIdea !!! Thank You");
        }
        
    }
    public function show()
    {
    	
    }

    public function commonactivity($maparray,$maplabel,$type,$main_location,$sub_location,$input_obj,$so_id) // 1- universe 2- chemist 3- status $type param

    {
       
       
        $message=array(); 
      

        $geo_level = DB::table('Geo_Hrchy_master')->where('refid', $sub_location)->select(['geo_level','name1','name2','master_table'])->first();
        $geo_level_main = DB::table('Geo_Hrchy_master')->where('refid', $main_location)->select(['geo_level','name1','name2','master_table'])->first();        
        $location_level_master= $geo_level->master_table;      
        $controllername='CombineController';
        $namespace = "App\Http\Controllers\\";
        $controllerName = $namespace . $controllername;          
        $obj = new $controllerName();
        if($type==3)
           $result_json=$obj->showlayerinfo($maparray,$type,$main_location,$sub_location,$so_id);
        else if($type==4)
           $result_json=$obj->show_added_outletlist($maparray,$type,$main_location,$sub_location,$so_id);
        else if(in_array($type,[5,6,7,8]))
           $result_json=$obj->show_outletlist_bycategory($maparray,$type,$main_location,$sub_location,$so_id);

        else
           $result_json=$obj->combine($maparray,$type,$main_location,$sub_location,$input_obj,$so_id);

       $message['mapdata']=$result_json['mapdata'];         
       $message['griddata']=$result_json['griddata'];
       $message['head']=$result_json['head'];
       $message['maplegend']=$result_json['legend'];

        return $message;


    }
    public static function Gradient($HexFrom, $HexTo, $ColorSteps,$per) {

        $from =str_replace("rgb(","",$HexFrom);
        $fromstr=str_replace(")","",$from);
        $fromspace=str_replace(" ","",$fromstr);
        $hexfrom=explode(",",$fromspace);


        $to =str_replace("rgb(","",$HexTo);
        $tostr=str_replace(")","",$to);
        $tospace=str_replace(" ","",$tostr);
        $hexto=explode(",",$tospace);
       

        $stepred=(float)(($hexfrom[0]-$hexto[0])/($ColorSteps-1));
        $stepgreen=(float)(($hexfrom[1]-$hexto[1])/($ColorSteps-1));
        $stepblue=(float)(($hexfrom[2]-$hexto[2])/($ColorSteps-1));
        
        $red=round($hexfrom[0]-($stepred * $per));
        $green=round($hexfrom[1]-($stepgreen * $per));
        $blue=round($hexfrom[2]-($stepblue * $per));
        
        $redappr=($red < 0) ? ($red * -1) : $red;
        $greenappr=($green < 0) ? ($green * -1) : $green;
        $blueappr=($blue < 0) ? ($blue * -1) : $blue;

        $GradientColors = sprintf("#%02x%02x%02x", $redappr, $greenappr, $blueappr);
        return $GradientColors;
  }

  public static function getcity($cityidarr)
  {
     $data=[];
     $geo_level = DB::table('city_master')->whereIn('refid', $cityidarr)->select(['refid','location_name'])->get();

     for($i=0;$i<count($geo_level);$i++)
     {
        $data[$geo_level[$i]->refid]=$geo_level[$i]->location_name;
     }

    return $data;



  }
   public static function getward($wardid_arr)
  {
     $data=[];
     $geo_level = DB::table('ward_master')->whereIn('refid', $wardid_arr)->select(['refid','location_name'])->get();

     for($i=0;$i<count($geo_level);$i++)
     {
        $data[$geo_level[$i]->refid]=$geo_level[$i]->location_name;
     }

    return $data;



  }
   public static function headline($city)
  {
     $data=[];
     $geo_level = DB::table('city_master')->whereIn('refid', $city)->select(['refid','location_name'])->get();

     for($i=0;$i<count($geo_level);$i++)
     {
        array_push($data,$geo_level[$i]->location_name);
     }

    return join(",",$data).' Localities';

  }
  public static function getreportee($userid,$wardid)
  {


     $data=[];
      
      $pc_user = DB::table('users')->where('pc_uid', $userid)->select(['id','reports_to','firstname','lastname'])->first();

      $so_user = DB::table('users')->where('id', $pc_user->reports_to)->select(['id','reports_to','firstname','lastname'])->first();
      $asmuser = DB::table('users')->where('id', $so_user->reports_to)->select(['id','reports_to','firstname','lastname'])->first();
      $bsmuser = DB::table('users')->where('id', $asmuser->reports_to)->select(['id','reports_to','firstname','lastname'])->first();

      $distributor = DB::table('loclty_pc_link')->leftJoin('mdlz_distbr_master', 'loclty_pc_link.fld1744', '=', 'mdlz_distbr_master.refid')->where([['loclty_pc_link.loc16','=', $wardid],['loclty_pc_link.pc_uid','=',$userid]])->select(['loclty_pc_link.fld1744','mdlz_distbr_master.name'])->first();

      $data['pc_name']=$pc_user->firstname.' '.$pc_user->lastname;
      $data['so_name']=$so_user->firstname.' '.$so_user->lastname;
      $data['asm_name']=$asmuser->firstname.' '.$asmuser->lastname;
      $data['bsm_name']=$bsmuser->firstname.' '.$bsmuser->lastname;
      $data['distributor']=isset($distributor->name) ? $distributor->name :'-';

      $data['pc_uid']=$pc_user->id;
      $data['so_id']=$so_user->id;
      $data['asm_id']=$asmuser->id;
      $data['bsm_id']=$bsmuser->id;
      //$data['distributor_id']=$distributor->fld1744;


      return $data;

  }

}
