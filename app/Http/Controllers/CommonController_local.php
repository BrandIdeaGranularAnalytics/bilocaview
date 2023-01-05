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

    public function commonactivity($maparray,$maplabel,$type,$main_location,$sub_location,$input_obj,$so_id,$current_location) // 1- universe 2- chemist 3- status $type param

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
        else if(in_array($type,[5,6,7,8,9,10,11]))
           $result_json=$obj->show_outletlist_bycategory($maparray,$type,$main_location,$sub_location,$so_id,$input_obj,$current_location); 
        else if(in_array($type,[12]))
           $result_json=$obj->combine_subrd($maparray,$type,$main_location,$sub_location,$input_obj,$current_location);
                      
        else
           $result_json=$obj->combine($maparray,$type,$main_location,$sub_location,$input_obj,$so_id);

       $message['mapdata']=$result_json['mapdata'];
        if(isset($result_json['griddata']))
            $message['griddata']=$result_json['griddata'];         
       
       $message['head']=$result_json['head'];
       $message['maplegend']=$result_json['legend'];
       if(isset($result_json['channel_list']))
            $message['channel_list']=$result_json['channel_list'];
           if(isset($result_json['feedback_question']))
            $message['feedback_question']=$result_json['feedback_question'];


        return $message;


    }
    public static function getColor($maxvalue, $minvalue, $delta,$low,$high) {
    $color=[];
    for($i=0;$i<3;$i++)
    {
       array_push($color,(($high[$i]-$low[$i])*$delta+$low[$i]));

    }

    $color="hsl(".$color[0]. ",".$color[1]."%," .$color[2]."%)";

    return $color;

  }

    public static function getarray($arrayofobj)
    {
       $arrayofobj = array_map(function ($arrayofobj) {
                return (array)$arrayofobj;
            }, $arrayofobj);
       return $arrayofobj;
    }
    public static function split_color_variation($range)
  {
 
      $splitarray=[];
$org_range=$range;
      if($range >= 71)
        $range=$range-60;
      if($range >= 51)
        $range=$range-40;
      else if($range > 31)
         $range=$range-20;
       
        $splitarray[0]=array('hex'=>'#908D8E','from_1'=>'rgb(211, 211, 211)','to_1'=>'rgb(172, 172, 172)','from_2'=>'rgb(172, 172, 172)','to_2'=>'rgb(144, 141, 142)');//gray
      $splitarray[1]=array('hex'=>'#01875B','from_1'=>'rgb(228, 242, 231)','to_1'=>'rgb(0, 242, 43)','from_2'=>'rgb(0, 242, 43)','to_2'=>'rgb(1, 135, 91)');//green

       $splitarray[2]=array('hex'=>'#ac0f13','from_1'=>'rgb(254, 231, 220)','to_1'=>'rgb(255, 0, 0)','from_2'=>'rgb(255, 0, 0)','to_2'=>'rgb(172, 15, 19)');//red
        $splitarray[3]=array('hex'=>'#982DC5','from_1'=>'rgb(133, 34, 176)','to_1'=>'rgb(87, 132, 180)','from_2'=>'rgb(87, 132, 180)','to_2'=>'rgb(152, 45, 197)');//lavendar
$splitarray[4]=array('hex'=>'#712664','from_1'=>'rgb(229, 218, 230)','to_1'=>'rgb(204, 51, 255)','from_2'=>'rgb(204, 51, 255)','to_2'=>'rgb(113, 38, 100)');//violet
     // $splitarray[4]=array('hex'=>'#ac0f13','from_1'=>'rgb(254, 231, 220)','to_1'=>'rgb(255, 0, 0)','from_2'=>'rgb(255, 0, 0)','to_2'=>'rgb(172, 15, 19)');//red_2

      $splitarray[5]=array('hex'=>'#373784','from_1'=>'rgb(224, 222, 240)','to_1'=>'rgb(0, 0, 255)','from_2'=>'rgb(0, 0, 255)','to_2'=>'rgb(55, 55, 132)');//blue

      $splitarray[6]=array('hex'=>'#65601F','from_1'=>'rgb(238, 236, 218)','to_1'=>'rgb(198, 221, 32)','from_2'=>'rgb(198, 221, 32)','to_2'=>'rgb(101, 96, 31');//golden

      // $splitarray[7]=array('hex'=>'#2F2D2D','from_1'=>'rgb(230, 231, 232)','to_1'=>'rgb(189, 190, 196)','from_2'=>'rgb(189, 190, 196)','to_2'=>'rgb(47, 45, 45)');//black
      $splitarray[7]=array('hex'=>'#ac0f13','from_1'=>'rgb(254, 231, 220)','to_1'=>'rgb(255, 0, 0)','from_2'=>'rgb(255, 0, 0)','to_2'=>'rgb(172, 15, 19)');//red_2


      $splitarray[8]=array('hex'=>'#d01176','from_1'=>'rgb(253, 233, 241)','to_1'=>'rgb(255, 0, 255)','from_2'=>'rgb(255, 0, 255)','to_2'=>'rgb(208, 17, 118)');//pink

      $splitarray[9]= array('hex'=>'#0096CE','from_1'=>'rgb(225, 244, 253)','to_1'=>'rgb(2, 238, 251)','from_2'=>'rgb(2, 238, 251)','to_2'=>'rgb(0, 150, 206)');//lblue
       
      // $splitarray[9]=array('hex'=>'#712664','from_1'=>'rgb(229, 218, 230)','to_1'=>'rgb(204, 51, 255)','from_2'=>'rgb(204, 51, 255)','to_2'=>'rgb(113, 38, 100)');//violet
      
      $splitarray[10]=array('hex'=>'#713620','from_1'=>'rgb(241, 223, 212)','to_1'=>'rgb(218, 136, 112)','from_2'=>'rgb(218, 136, 112)','to_2'=>'rgb(113, 54, 32)');//brown
       $splitarray[11]=array('hex'=>'#e0d006','from_1'=>'rgb(252, 252, 153)','to_1'=>'rgb(255, 255, 0)','from_2'=>'rgb(255, 255, 0)','to_2'=>'rgb(224, 208, 6)');//brown
        $splitarray[12]=array('hex'=>'#63be7b','from_1'=>'rgb(254, 228, 130)','to_1'=>'rgb(205, 221, 130)','from_2'=>'rgb(205, 221, 130)','to_2'=>'rgb(99, 190, 123)');//grid

          $splitarray[13]=array('hex'=>'#FF8000','from_1'=>'rgb(250, 204, 154)','to_1'=>'rgb(251, 182, 109)','from_2'=>'rgb(251, 182, 109)','to_2'=>'rgb(255, 128, 0)');//orange
       

         $splitarray[14]=array('hex'=>'#2E7ACB','from_1'=>'rgb(41, 95, 153)','to_1'=>'rgb(171, 81, 209)','from_2'=>'rgb(171, 81, 209)','to_2'=>'rgb(46, 122, 203)');//orange
        $splitarray[15]=array('hex'=>'#982DC5','from_1'=>'rgb(133, 34, 176)','to_1'=>'rgb(87, 132, 180)','from_2'=>'rgb(87, 132, 180)','to_2'=>'rgb(152, 45, 197)');//lavendar



         $splitarray[16]=array('hex'=>'#01875B','from_1'=>'rgb(228, 242, 231)','to_1'=>'rgb(0, 242, 43)','from_2'=>'rgb(0, 242, 43)','to_2'=>'rgb(1, 135, 91)');//green

      // $splitarray[2]=array('hex'=>'#ac0f13','from_1'=>'rgb(254, 231, 220)','to_1'=>'rgb(255, 0, 0)','from_2'=>'rgb(255, 0, 0)','to_2'=>'rgb(172, 15, 19)');//red
        $splitarray[17]=array('hex'=>'#982DC5','from_1'=>'rgb(133, 34, 176)','to_1'=>'rgb(87, 132, 180)','from_2'=>'rgb(87, 132, 180)','to_2'=>'rgb(152, 45, 197)');//lavendar

      $splitarray[18]=array('hex'=>'#ac0f13','from_1'=>'rgb(254, 231, 220)','to_1'=>'rgb(255, 0, 0)','from_2'=>'rgb(255, 0, 0)','to_2'=>'rgb(172, 15, 19)');//red_2

      $splitarray[19]=array('hex'=>'#373784','from_1'=>'rgb(224, 222, 240)','to_1'=>'rgb(0, 0, 255)','from_2'=>'rgb(0, 0, 255)','to_2'=>'rgb(55, 55, 132)');//blue

      $splitarray[20]=array('hex'=>'#65601F','from_1'=>'rgb(238, 236, 218)','to_1'=>'rgb(198, 221, 32)','from_2'=>'rgb(198, 221, 32)','to_2'=>'rgb(101, 96, 31');//golden

      // $splitarray[21]=array('hex'=>'#2F2D2D','from_1'=>'rgb(230, 231, 232)','to_1'=>'rgb(189, 190, 196)','from_2'=>'rgb(189, 190, 196)','to_2'=>'rgb(47, 45, 45)');//black
      $splitarray[21]=array('hex'=>'#ac0f13','from_1'=>'rgb(254, 231, 220)','to_1'=>'rgb(255, 0, 0)','from_2'=>'rgb(255, 0, 0)','to_2'=>'rgb(172, 15, 19)');//red

      $splitarray[22]=array('hex'=>'#d01176','from_1'=>'rgb(253, 233, 241)','to_1'=>'rgb(255, 0, 255)','from_2'=>'rgb(255, 0, 255)','to_2'=>'rgb(208, 17, 118)');//pink

      $splitarray[23]= array('hex'=>'#0096CE','from_1'=>'rgb(225, 244, 253)','to_1'=>'rgb(2, 238, 251)','from_2'=>'rgb(2, 238, 251)','to_2'=>'rgb(0, 150, 206)');//lblue
       
      $splitarray[24]=array('hex'=>'#712664','from_1'=>'rgb(229, 218, 230)','to_1'=>'rgb(204, 51, 255)','from_2'=>'rgb(204, 51, 255)','to_2'=>'rgb(113, 38, 100)');//violet
      
      $splitarray[25]=array('hex'=>'#713620','from_1'=>'rgb(241, 223, 212)','to_1'=>'rgb(218, 136, 112)','from_2'=>'rgb(218, 136, 112)','to_2'=>'rgb(113, 54, 32)');//brown
       $splitarray[26]=array('hex'=>'#e0d006','from_1'=>'rgb(252, 252, 153)','to_1'=>'rgb(255, 255, 0)','from_2'=>'rgb(255, 255, 0)','to_2'=>'rgb(224, 208, 6)');//brown
        $splitarray[27]=array('hex'=>'#63be7b','from_1'=>'rgb(254, 228, 130)','to_1'=>'rgb(205, 221, 130)','from_2'=>'rgb(205, 221, 130)','to_2'=>'rgb(99, 190, 123)');//grid

          $splitarray[28]=array('hex'=>'#FF8000','from_1'=>'rgb(250, 204, 154)','to_1'=>'rgb(251, 182, 109)','from_2'=>'rgb(251, 182, 109)','to_2'=>'rgb(255, 128, 0)');//orange
        $splitarray[29]=array('hex'=>'#908D8E','from_1'=>'rgb(211, 211, 211)','to_1'=>'rgb(172, 172, 172)','from_2'=>'rgb(172, 172, 172)','to_2'=>'rgb(144, 141, 142)');//gray

         $splitarray[30]=array('hex'=>'#2E7ACB','from_1'=>'rgb(41, 95, 153)','to_1'=>'rgb(171, 81, 209)','from_2'=>'rgb(171, 81, 209)','to_2'=>'rgb(46, 122, 203)');//orange
        $splitarray[31]=array('hex'=>'#982DC5','from_1'=>'rgb(133, 34, 176)','to_1'=>'rgb(87, 132, 180)','from_2'=>'rgb(87, 132, 180)','to_2'=>'rgb(152, 45, 197)');//lavendar


if(!isset( $splitarray[$range]))
{
   echo $range.'   '.$org_range;


   die;
}

      return $splitarray[$range];


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
