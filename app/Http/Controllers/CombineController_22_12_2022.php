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
use App\Http\Controllers\CommonController;
use App\User;
use DB;

class CombineController extends Controller
{
    public function index()
    {

        $stat = auth()->user()->status;
        $user = auth()->user();
        $userid = $user->id;
        $packageid = $user->package_id;
        $contents = Storage::get('client_menu/client_menu_' . $userid . '.json');

        if ($stat == 'Active')
        {
            return view('pages.dashboard', ['menulist' => $contents, 'package_id' => $packageid]);
        }
        else
        {
            return Redirect::to('/auth/login')->with('message', "Get approval from Admin Team from BrandIdea !!! Thank You");
        }

    }
    public function show()
    {

    }
    public function showlayerinfo($maparray,$type,$main_location,$sub_location,$so_id)
    {
        $data = [];
        $data['result'] = array();
        $data['mapdata'] = array();
        $user = auth()->user();
        $userid = $user->id;
        $key = array_keys($maparray);
        $value = array_values($maparray);

        $loc15 = array_unique(array_column($value, 'loc15'));
        $loc12 = array_unique(array_column($value, 'loc12'));


        $condition = [];
        $total_data_array = [];
        $covered_condition = [];
        $total_uncovered_data_array = [];
             
        $sql_covered="SELECT colony_id,user_id,status,modified_date FROM `salesman_covered_ward` where user_id in (".$userid.")";
        $covered_result = DB::select(DB::raw($sql_covered));

        $covered_details_array=[];

        for($i=0;$i<count($covered_result);$i++)
        {
             $temp=[];
             $temp['colony_id']=$covered_result[$i]->colony_id;
             $temp['user_id']=$covered_result[$i]->user_id;
             $temp['status']=$covered_result[$i]->status;
             $temp['modified_date']=$covered_result[$i]->modified_date;     
             $covered_details_array[$temp['colony_id']]=$temp;     

        }
        $getdetail=[];
        foreach ($maparray as $key => $value) {

            $pc_uid = DB::table('loclty_pc_link')->where('loc16', $value['loc_id'])->select(['pc_uid'])->first();
            $pc_uid=$pc_uid->pc_uid;
            if (!array_key_exists($pc_uid,$getdetail))
                $getdetail[$pc_uid]=CommonController::getreportee($pc_uid,$value['loc_id']);

            if(isset($covered_details_array[$key]))
            {
                $temp=[];$temp['color']='#ffffff';

               

                $temp['location_id']=$value['loc_id'];
                $temp['location_name']=$value['location_name'];
                $temp['status']=($covered_details_array[$key]['status'] ==1 ) ? 'Visited' : 'Not Visited';
                $temp['action_date']=$covered_details_array[$key]['modified_date'];
                $temp['color']=($covered_details_array[$key]['status'] ==1 ) ? '#DADD21' : '#DD7921';
                $temp['covered_stat']=($covered_details_array[$key]['status'] ==1 ) ? 'Visited' : 'Not Visited';
                $temp['loc12']=$value['loc12'];
                $temp['loc15']=$value['loc15'];
                $temp['pc_uid']=$pc_uid;
                $temp['so_id']=$getdetail[$pc_uid]['so_id'];
                $temp['asm_id']=$getdetail[$pc_uid]['asm_id'];
                $temp['bsm_id']=$getdetail[$pc_uid]['bsm_id'];
                $temp['pc_name']=$getdetail[$pc_uid]['pc_name'];
                $temp['so_name']=$getdetail[$pc_uid]['so_name'];
                $temp['asm_name']=$getdetail[$pc_uid]['asm_name'];
                $temp['bsm_name']=$getdetail[$pc_uid]['bsm_name'];
                

                //$info='<b>'.$value['location_name'].'</b><br>Status:'.$temp['covered_stat'].'<br>'.'Action Date: '.date('d-m-Y H:i a',strtotime($temp['action_date']));
                $info = "<div class='tooltip-data visited-tooltip'><div class='card'><div class='card-header'>". $value['location_name'] ."</div><ul class='list-group list-group-flush'><li class='list-group-item'>Status <span>" . $temp['covered_stat'] . "</span></li><li class='list-group-item'>Action Date <span> ".date('d M Y H:i A',strtotime($temp['action_date'])) . "</span></li></ul></div></div>";

                $temp['info']=$info;

               $maparray[$value['loc_id']] = array_merge($maparray[$value['loc_id']], $temp);

               array_push($data['result'], $temp);
               
            }
            else
            {
                $temp=[];$temp['color']='#ffffff';
                $temp['location_id']=$value['loc_id'];
                $temp['location_name']=$value['location_name'];
                $temp['status']= 'Not Visited';
                $temp['action_date']='';
                $temp['covered_stat']='Not Visited';
                $temp['color']= '#DD7921';
              //  $info='<b>'.$value['location_name'].'</b><br>Status:'.$temp['covered_stat'];
                 $info = "<div class='tooltip-data notvisited-tooltip'><div class='card'><div class='card-header'>". $value['location_name'] ."</div><ul class='list-group list-group-flush'><li class='list-group-item'>Status <span>" . $temp['covered_stat'] . "</span></li></ul></div></div>";
                $temp['info']=$info;
                $temp['loc12']=$value['loc12'];
                $temp['loc15']=$value['loc15'];
                $temp['pc_uid']=$pc_uid;
                $temp['so_id']=$getdetail[$pc_uid]['so_id'];
                $temp['asm_id']=$getdetail[$pc_uid]['asm_id'];
                $temp['bsm_id']=$getdetail[$pc_uid]['bsm_id'];
                $temp['pc_name']=$getdetail[$pc_uid]['pc_name'];
                $temp['so_name']=$getdetail[$pc_uid]['so_name'];
                $temp['asm_name']=$getdetail[$pc_uid]['asm_name'];
                $temp['bsm_name']=$getdetail[$pc_uid]['bsm_name'];
                
                $maparray[$value['loc_id']] = array_merge($maparray[$value['loc_id']], $temp);
                array_push($data['result'], $temp);
            }
        }

        $data['legend'] = [];
        //array_push($data['legend'],array('Covered'=>'#DADD21','Uncovered'=>'#DD7921'));
         array_push($data['legend'],array('Covered'=>'#DADD21','Uncovered'=>'#DD7921'));
        
        
        $data['mapdata'] = $maparray;
        $data['griddata'] = array();

        $data['griddata'] = $this->gridcolumn_bystatus($data['result'], $loc15, $loc12);

        $head = CommonController::headline($loc12);
        $data['head'] = $head;

        return $data;
    }
    public function show_added_outletlist($maparray,$type,$main_location,$sub_location,$so_id)
    {
        $data = [];
        $data['result'] = array();       
        $user = auth()->user();
        $userid = $user->id;   
        $key = array_keys($maparray);
        $value = array_values($maparray);
        $loc15 = array_unique(array_column($value, 'loc15'));
        $loc12 = array_unique(array_column($value, 'loc12'));   
       if($user->client_id==120)
        $data_outlet_list =  DB::table('outlet_list')
            ->join('users', 'users.id', '=', 'outlet_list.user_id')          
            ->join('mdlz_main_channel_master', 'mdlz_main_channel_master.refid', '=', 'outlet_list.channel_name')
            ->join('mdlz_channel_master', 'mdlz_channel_master.refid', '=', 'outlet_list.sub_channel_name')
             ->where('outlet_list.user_id',$user->id)
            ->select('outlet_list.*', 'users.firstname', 'users.lastname','mdlz_main_channel_master.name as channel','mdlz_channel_master.name as subchannel','mdlz_channel_master.icon as icon')
            ->get();
        if($user->client_id==100)
             $data_outlet_list =  DB::table('outlet_list')
            ->join('users', 'users.id', '=', 'outlet_list.user_id')          
            ->join('j_and_j_main_channel_master', 'j_and_j_main_channel_master.refid', '=', 'outlet_list.channel_name')
            ->join('j_and_j_channel_master', 'j_and_j_channel_master.refid', '=', 'outlet_list.sub_channel_name')
             ->where('outlet_list.user_id',$user->id)
            ->select('outlet_list.*', 'users.firstname', 'users.lastname','j_and_j_main_channel_master.name as channel','j_and_j_channel_master.name as subchannel','j_and_j_channel_master.icon as icon')
            ->get();
             if($user->client_id==86)
             $data_outlet_list =  DB::table('outlet_list')
            ->join('users', 'users.id', '=', 'outlet_list.user_id')          
            ->join('nestle_channel_master', 'nestle_channel_master.refid', '=', 'outlet_list.sub_channel_name')
           // ->join('nestle_channel_master', 'nestle_channel_master.refid', '=', 'outlet_list.sub_channel_name')
             ->where('outlet_list.user_id',$user->id)
            ->select('outlet_list.*', 'users.firstname', 'users.lastname','nestle_channel_master.name as channel','nestle_channel_master.name as subchannel','nestle_channel_master.icon as icon')
            ->get();

        for($i=0;$i<count($data_outlet_list);$i++)        
        {
             $temp=[];
             $temp['refid']=$data_outlet_list[$i]->refid;
             $temp['outlet_name']=$data_outlet_list[$i]->outlet_name;
             $temp['owner_name']=$data_outlet_list[$i]->owner_name;
             $temp['channel_name']=$data_outlet_list[$i]->channel;
             $temp['sub_channel_name']=$data_outlet_list[$i]->subchannel;
             $temp['address']=ucwords(strtolower($data_outlet_list[$i]->address));               
             $temp['shop_image']=$data_outlet_list[$i]->shop_image;
             $temp['created_at']=date('d M Y H:i:s A',strtotime($data_outlet_list[$i]->created_at));
             $temp['updated_at']= date('d M Y H:i:s A',strtotime($data_outlet_list[$i]->updated_at));
             $temp['user_id']=$data_outlet_list[$i]->user_id;
             $temp['pan_no']=$data_outlet_list[$i]->pan_no;
             $temp['mobile_no']=$data_outlet_list[$i]->mobile_no;     
             $temp['tan_no']=$data_outlet_list[$i]->tan_no; 
             $temp['shop_establish_no']=$data_outlet_list[$i]->shop_establish_no; 
             $temp['gst_no']=$data_outlet_list[$i]->gst_no; 
             $temp['icon']=$data_outlet_list[$i]->icon; 
             $temp['lat']=(isset($data_outlet_list[$i]->lat)) ? $data_outlet_list[$i]->lat : ''; 
             $temp['lon']=(isset($data_outlet_list[$i]->lon)) ? $data_outlet_list[$i]->lon : ''; 

             array_push($data['result'],$temp);

        }
        

        $data['legend'] = [];
          
        $data['mapdata'] =$data['result'];
        $data['griddata'] = array();

        $data['griddata'] = $this->gridcolumn_byoutletlist($data['result']);
        $user = auth()->user();
        if($user->client_id==86)
          $head='Added Outlets';
        else
          $head = CommonController::headline($loc12);
        $data['head'] = $head;

        return $data;

    }
     public function Combine_subrd($maparray, $type, $main_location, $sub_location,$input_obj,$current_location)
    {
         $data = [];$getdetail=[];
        $user = auth()->user();
        $userid = $user->id;

        $data['result'] = array();
        $data['mapdata'] = array();
        $key = array_keys($maparray);
        $value = array_values($maparray);
        $getfilter=json_decode($input_obj);
       
        $orwhere=[];
        if(isset($getfilter->filter_district) && count($getfilter->filter_district)>0)
            array_push($orwhere,"  a.loc9 in (".implode(",",$getfilter->filter_district).")");
        if(isset($getfilter->filter_taluk) && count($getfilter->filter_taluk)>0)
            array_push($orwhere,"  a.taluk_census in (".implode(",",$getfilter->filter_taluk).")");

        $sql="SELECT  a.`refid`, a.`cluster_id`, a.`cluster_name`, a.`state_name`, a.`district_name`, a.`taluk_name`, a.`village_name`, a.`sector`, a.`loc7`, a.`loc9`, a.`loc10`, a.`loc13`, a.`loc12`, a.`market_id`, a.`bi_id`, a.`distance_subrd`, a.`subrd_loaction`, a.`outlet_potential`, a.`population`, a.`taluk_census`, a.`village_census`, a.`village_choc_consmptn`, a.`cluster_tag`, a.`stat`, a.`subrd_type`, a.`is_hub`, a.`hub_id`, a.`subrd_priority`, a.`tsm_id`, a.`village_2011_census`, a.`company_service_id`,if(a.company_service_id=1,'Active',if(a.company_service_id=2,'Initd',if(a.company_service_id=3,'Inactive',if(a.company_service_id=4,'Activtd',if(a.company_service_id=5,'Deactivtd',''))))) company_servcng,b.latitude,b.longitude FROM `subrd_data` as a,town_village_polygon as b  where a.village_census=b.town_village_code and a.taluk_census=b.taluk_code and a.tsm_id=".$userid." and (".join(" or ",$orwhere).") and  a.stat='A' and b.stat='A' ";


         $result = DB::select(DB::raw($sql));
         $result=CommonController::getarray($result);
          
          $final_result=[];
          $inc=0;
          $taluk_name=array_column($result,'taluk_name');
          $taluk_name=array_unique($taluk_name);
          $district_name=array_column($result,'district_name');
          $district_name=array_unique($district_name);
          $table_data=[];



         
         for($i=0;$i<count($result);$i++)
         {
             if($result[$i]['is_hub']==1)
             {
                  
                  
                if(isset($maparray[$result[$i]['village_census']]))
                {
                    $final_result[$inc]=$result[$i];
                  $final_result[$inc]['child']=[];
                  $filter_id=$result[$i]['cluster_id'];
                                 
                  $final_result[$inc]['subrd_marker']=($result[$i]['subrd_type']==1) ?  'rural_icon/efficient-subrd.png' :  (($result[$i]['subrd_type']==2) ? 'rural_icon/recommendation.png' : (($result[$i]['subrd_type']==3) ? 'svg/Wholesaler.png' :'NA'));
                  $final_result[$inc]['subrd_tooltip']='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$result[$i]['village_census']]['location_name'].'</h3></div><ul class="list-group list-group-flush"><li class="list-group-item">'.$result[$i]['cluster_name'].'</li></ul> </div></div></div>';
                    $hub_child_list = array_filter($result, function ($var) use ($filter_id) {
                         return ($var['cluster_id'] == $filter_id && $var['is_hub'] != 1);
                   });

                              
                  $final_result[$inc]['child']=$hub_child_list; 
                  $res_arr=$result[$i];
                  $res_arr['child']=htmlspecialchars(json_encode([$hub_child_list]), ENT_QUOTES, 'UTF-8');
                  
                 $inc++;
                 array_push($table_data,$res_arr);
                }
                  
             }
         }

         $result_count=count($final_result);

         $temp=[];
         for($k=0;$k<$result_count;$k++)
         {
            $temp=$final_result[$k];
            if($temp['subrd_type']==1)
                $split_color=CommonController::split_color_variation(0);
            else
                $split_color=CommonController::split_color_variation($k+1);

            unset($temp['child']);
            
             $hub='';$child='';
             $from50=$split_color['from_1'];
             $to50=$split_color['to_1'];
             $from100=$split_color['from_2'];
             $to100=$split_color['to_2']; 
             $hub= CommonController::Gradient($from100,$to100,100,100); 
             $label='';
             $legend="";
             $temp['color']=$hub; 
             $cluster_type=$final_result[$k]['subrd_loaction'];
             $final_result[$k]['activate_status']=$final_result[$k]['company_service_id'];
             $cluster_tag=($final_result[$k]['subrd_type']==1) ? 'SubRD Existing' :(($final_result[$k]['subrd_type']==2) ? 'Subrd Reco' :(($final_result[$k]['subrd_type']==3) ?'Wholesaler' : ''));
             $temp['activate_marker']=($final_result[$k]['company_service_id']==1) ? 'rural_icon/active.png' : (($final_result[$k]['company_service_id']==2) ? 'rural_icon/initiated.png' : (($final_result[$k]['company_service_id']==3) ? 'rural_icon/deactivated.png' :(($final_result[$k]['company_service_id']==4) ? 'rural_icon/activated.png' :(($final_result[$k]['company_service_id']==5) ? 'rural_icon/deactivated.png'  : 'NA'))));
            
             $temp['subrd_status']=$final_result[$k]['subrd_type'];
             $temp['subrd_marker']=(($final_result[$k]['subrd_type']==2) ? 'rural_icon/recommendation.png' : (($final_result[$k]['subrd_type']==1) ? 'rural_icon/efficient-subrd.png' : (($final_result[$k]['subrd_type']==3) ? 'app_icon/Wholesale.png' : 'NA')));
             $temp['subrd_tooltip']='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$final_result[$k]['village_census']]['location_name'].'</h3></div><ul class="list-group list-group-flush"><li class="list-group-item">'.$cluster_tag.'</li></ul> </div></div></div>';

            $temp['info']='<div class="container-fluid pb-2" style="height:fit-content;color:white !important;"><span class="d-flex flex-row  justify-content-between pt-2"><h5>'.$maparray[$final_result[$k]['village_census']]['location_name'].' Villg.&nbsp;</h5><span class="" style="height:max-content;width: 1.3rem;background-color: #00CCCC;border-radius: 50%;text-align: center;" geocode="'.$final_result[$k]['latitude'].','.$final_result[$k]['longitude'].'" onclick="location_navigate(this)"><i class="fa fa-location-arrow" aria-hidden="true" style="font-size:17px;color:black;"></i></span></span><hr style="border-top: 1px solid white;"><p><span style="color:#00CCCC">Recommendation: </span>'.$cluster_type.'</p><p><span style="color:rgb(242, 101, 34)">Distance from Recommd Hub (km): </span>0 kms</p><p><span style="color:rgb(242, 101, 34)">Population (2021): </span>'.number_format($final_result[$k]['population'],0).' </p><p><span style="color:rgb(242, 101, 34)">Outlet Potential: </span>'.$final_result[$k]['outlet_potential'].' Nos.</p><p><span style="color:rgb(242, 101, 34)">Village Beverage Consumptn (Rs.): </span>'.$final_result[$k]['village_choc_consmptn'].' </p><p><span style="color:rgb(242, 101, 34)">Cluster Tag: </span>'.$cluster_tag.' </p>';
             if(in_array($final_result[$k]['subrd_type'],[2,3]))
             $temp['info'] .='<p><span style="color:rgb(242, 101, 34)">SubD Priority: </span> '.$final_result[$k]['subrd_priority'].'</p><p><span style="color:rgb(242, 101, 34)">SubD Cluster Priority: </span>'.$final_result[$k]['subrd_priority'].'</p>';
         //<p><span style="color:rgb(242, 101, 34)">Market UID: </span><span style="background-color:white;color:black;">'.$final_result[$k]['market_id'].'</span></p>
             $temp['info'] .='<p><span style="color:rgb(242, 101, 34)">BI Location ID: </span><span style="background-color:white;color:black;" >'.$final_result[$k]['bi_id'].' </span></p></div>';
             $temp['size']=10;
             $temp['activate_status_icon']=$temp['activate_marker'];
             $temp['activate_status']=$final_result[$k]['activate_status'];
            
            $maparray[$final_result[$k]['village_census']]=array_merge($maparray[$final_result[$k]['village_census']],$temp);

            $temp2=[];
            foreach($final_result[$k]['child'] as $key=>$value)
            {
                 $temp2=$value;
                 $temp2['color']= CommonController::Gradient($from50,$to50,50,30);
                 $cluster_type=$value['subrd_loaction'];
                 $cluster_tag=($value['subrd_type']==1) ? 'SubRD Existing' :(($value['subrd_type']==2) ? 'Subrd Reco' :(($value['subrd_type']==3) ?'Wholesaler' : ''));
                  if(isset($maparray[$value['village_census']]))
                {
                       $temp2['info']='<div class="container-fluid pb-1" style="height:fit-content;color:white !important;"><span class="d-flex flex-row  justify-content-between pt-2"><h5>'.$maparray[$value['village_census']]['location_name'].' Villg.&nbsp;</h5><span class="" style="height:max-content;width: 1.3rem; background-color: #00CCCC;border-radius: 50%;text-align: center;" geocode="'.$value['latitude'].','.$value['longitude'].'" onclick="location_navigate(this)"><i class="fa fa-location-arrow" aria-hidden="true" style="font-size:17px;color:black;"></i></span></span><hr style="border-top: 1px solid white;"><p><span style="color:#00CCCC">Recommendation: </span>'.$cluster_type.'</p><p><span style="color:rgb(242, 101, 34)">Distance from Recommd Hub (km): </span>'.$value['distance_subrd'].' kms</p><p><span style="color:rgb(242, 101, 34)">Population (2021): </span>'.number_format($value['population'],0).'</p><p><span style="color:rgb(242, 101, 34)">Outlet Potential: </span>'.$value['outlet_potential'].' Nos.</p><p><span style="color:rgb(242, 101, 34)">Village Beverage Consumptn (Rs.): </span>'.$value['village_choc_consmptn'].' </p><p><span style="color:rgb(242, 101, 34)">Cluster Tag: </span>'.$cluster_tag.' </p>';
             if(in_array($value['subrd_type'],[2,3]))                
             $temp2['info'] .='<p><span style="color:rgb(242, 101, 34)">SubD Priority: </span> '.$value['subrd_priority'].'</p><p><span style="color:rgb(242, 101, 34)">SubD Cluster Priority: </span>'.$value['subrd_priority'].'</p>';
         //<p><span style="color:rgb(242, 101, 34)">Market UID: </span><span style="background-color:white;color:black;">'.$value['market_id'].'</span></p>
             $temp2['info'] .='<p><span style="color:rgb(242, 101, 34)">BI Location ID: </span><span style="background-color:white;color:black;" >'.$value['bi_id'].' </span></p></div>';
             $value['activate_status']=$value['company_service_id'];
             $cluster_tag=($value['subrd_type']==1) ? 'SubRD Existing' :(($value['subrd_type']==2) ? 'Subrd Reco' :(($value['subrd_type']==3) ?'Wholesaler' : ''));
             $value['activate_marker']=($value['company_service_id']==1) ? 'rural_icon/active.png' : (($value['company_service_id']==2) ? 'rural_icon/initiated.png' : (($value['company_service_id']==3) ? 'rural_icon/deactivated.png' :(($value['company_service_id']==4) ? 'rural_icon/activated.png' :(($value['company_service_id']==5) ? 'rural_icon/deactivated.png'  : 'NA'))));
            
             $temp2['size']=5;
             $temp2['activate_status_icon']=$value['activate_marker'];
             $temp2['activate_status']=$value['activate_status'];
             $temp2['subrd_status']=0;
             $temp2['subrd_marker']='NA';             
             $temp2['subrd_tooltip']='';
           
            $maparray[$value['village_census']]=array_merge($maparray[$value['village_census']],$temp2);
                }

             

            }
         
         }

        
        $data['legend'] = [];  
        $data['griddata'] = $this->getsubrd($table_data);
        $data['mapdata'] = $maparray;
        if(isset($getfilter->filter_taluk) && count($getfilter->filter_taluk)>0)
            $data['head']=implode(", ", $taluk_name). ' Sub-distt';
        else if(isset($getfilter->filter_district) && count($getfilter->filter_district)>0)
             $data['head']=implode(", ", $district_name). ' Distt';


        

        return $data;

    }
    public function getsubrd($data)
    {
        $column=[];
        $value=[];
         array_push($column, array(
             'title' => '#', 'className' => 'dt-control'
         ));

          array_push($column, array(
             'title' => 'SubRD Cluster ID', 'className' => 'text-left'
         ));
          array_push($column, array(
             'title' => 'Distt. Name', 'className' => 'text-left'
         ));
           array_push($column, array(
             'title' => 'Sub-Distt. Name', 'className' => 'text-left'
         ));
            array_push($column, array(
             'title' => 'Town / Village Name', 'className' => 'text-left'
         ));
            array_push($column, array(
             'title' => 'Market UID', 'className' => 'text-right'
         ));
            array_push($column, array(
             'title' => 'Distance from Recmmd SubRD Locatn (Km)', 'className' => 'text-right'
         ));
             array_push($column, array(
             'title' => 'Outlet Potential (Nos.)', 'className' => 'text-right'
         ));
             array_push($column, array(
             'title' => 'Population (Nos.)', 'className' => 'text-right'
         ));
              array_push($column, array(
             'title' => 'Village Choc Consmptn', 'className' => 'text-right'
         ));
            
              array_push($column, array(
             'title' => 'Cluster Type', 'className' => 'text-right'
         ));

        for($i=0;$i<count($data);$i++)
        {
             $detail=htmlspecialchars(json_encode([$data[$i]]), ENT_QUOTES, 'UTF-8');
            $temp=array(
                ($i+1),
                'Cluster '.$data[$i]['cluster_id'],

                '<a href="#"  id="'.$data[$i]['child'].'" class="getchild_'.($i+1).'">'. $data[$i]['district_name'] .'</a>',
                 $data[$i]['taluk_name'],
                 '<a href="#" style="text-decoration:underline;" onClick="view_village_detail('.$detail.')">'.$data[$i]['village_name'].'</a>',
                  $data[$i]['market_id'],
                  $data[$i]['distance_subrd'],
                  $data[$i]['outlet_potential'],
                  $data[$i]['population'],
                  $data[$i]['village_choc_consmptn'],
                  $data[$i]['cluster_tag'].' Subrd Hub'


            );
            array_push($value,$temp);

        }
            
            return array(
            'column' => $column,
            'value' => $value
        );



    }
     public function rpi_action(Request $request)
    {
         $input=$request->all();
         $user = auth()->user();
         $userid=$user->id;
         $msg=[];

         $village_id=$input['village_id'];
         $action_id=$input['action_id'];

         if (DB::table('subrd_data')->where([['village_census','=',$village_id ]])->exists()) 
        {

          if(DB::table('subrd_data')->where([['village_census','=',$village_id ]])->update(['company_service_id' => $action_id])){
               $msg['statuschange']='success';
               $msg['msg']='Details updated';
           }
          
        }
        else
        {
             $msg['statuschange']='failure';
             $msg['msg']='Not Available';

        }

        return response()->json($msg);
    }
    public function Combine($maparray, $type, $main_location, $sub_location,$input_obj,$so_id)
    {
        $data = [];$getdetail=[];
        $user = auth()->user();
        $userid = $user->id;

        $data['result'] = array();
        $data['mapdata'] = array();



        $key = array_keys($maparray);
        $value = array_values($maparray);



        $loc15 = array_unique(array_column($value, 'loc15'));
        $loc12 = array_unique(array_column($value, 'loc12'));
        $loc16 = array_unique($key);
        $pc_uid = array_unique(array_column($value, 'pc_uid'));


         $getfilter=json_decode($input_obj);
         $condn=[];
      
         if(isset($getfilter->filter_pc) && (count($getfilter->filter_pc) > 0))
          {
             $pc_user=implode(",",$getfilter->filter_pc);
             if($pc_user != '')
              array_push($condn, "and pc_uid in (".$pc_user.")");
            

          }
          if(isset($getfilter->filter_distributor) && (count($getfilter->filter_distributor) > 0))
          {
              $distributor_list=implode(",",$getfilter->filter_distributor);
              array_push($condn, "and fld1744 in (".$distributor_list.")");
          }
          $criteria=join(" ",$condn);

        $condition = [];
        $total_data_array = [];
        $covered_condition = [];
        $total_uncovered_data_array = [];

        if (count($loc15) > 0)
        {
            array_push($condition, "ward_id in (" . implode(',', $loc15) . ")");
            array_push($covered_condition, " loc15 in (" . implode(',', $loc15) . ")");


        }

        if (count($loc12) > 0)
        {
            array_push($condition, "city_id in (" . implode(',', $loc12) . ")");
            array_push($covered_condition, "loc12 in (" . implode(',', $loc12) . ")");

        }
         if (count($loc16) > 0)
        {
            array_push($condition, "colony_id in (" . implode(',', $loc16) . ")");
            array_push($covered_condition, "loc16 in (" . implode(',', $loc16) . ")");

        }
        if(count($pc_uid) > 0)
        {
           array_push($covered_condition,"pc_uid in (" . implode(',', $pc_uid) . ")");
        }
        

         $condn="";$covered_condn="";

         if(count($condition) > 0 )
         {
                $condn = ltrim(join(" and ", $condition),"and");

                $condn = $condn.' and';

         }

          if(count($covered_condition) > 0 )
         {
                $covered_condn = ltrim(join(" and ", $covered_condition),"and");

                $covered_condn = $covered_condn.' and';

         }



         
     //    $covered_condn = join(" and ", $covered_condition);


         if ($type == 1) $total_data_sql = "select (s.fld_21+s.fld_3+s.fld_22) as total_shop,s.locid,s.ward_id,s.city_id from (SELECT colony_id locid,round((sum(prvsn)+sum(gens)),0) fld_21,round(sum(fb_mdlz),0) fld_3,round(sum(chemist_lsi),0) fld_22,ward_id,city_id FROM all_retailer_colony WHERE  " . $condn . "  ( fld189 = '2') and  ( colony_id != '0') and ( colony_id != '0') and ( stat != 'R') GROUP BY city_id,ward_id,locid) as s";
        
        if ($type == 2) $total_data_sql = "select (s.fld_22) as total_shop,s.locid,s.ward_id,s.city_id from (SELECT colony_id locid,round(sum(chemist_lsi),0) fld_22,ward_id,city_id FROM all_retailer_colony WHERE   " . $condn . " ( fld189 = '2') and  ( colony_id != '0') and ( colony_id != '0') and ( stat != 'R') GROUP BY city_id,ward_id,locid) as s";




        $result = DB::select(DB::raw($total_data_sql));


        for ($i = 0;$i < count($result);$i++)
        {
            $total_data_array[$result[$i]->locid]['total'] = array(
                'total_shop' => $result[$i]->total_shop,
                'colony_id' => $result[$i]->locid,
                'ward_id' => $result[$i]->ward_id,
                'city_id' => $result[$i]->city_id
            );
        }

        if ($type == 1) $total_covered_sql = "SELECT count(*) as covered_shop,a.loc16 as locid,a.loc12 as city_id,a.loc15 as ward_id,a.pc_uid FROM `mdlz_retailer_master` as a  WHERE " . $covered_condn . "  a.`sheet_ref` LIKE '18 Town Data'  and a.stat='A' and a.loc16 != 0    $criteria group by a.pc_uid,a.loc12,a.loc15,locid";
        if ($type == 2) $total_covered_sql = "SELECT count(*) as covered_shop,a.loc16 as locid,a.loc12 as city_id,a.loc15 as ward_id,a.pc_uid FROM `mdlz_retailer_master` as a  WHERE " . $covered_condn . "  a.`fld1746` in(2) and a.`sheet_ref` LIKE '18 Town Data'  and a.stat='A' and a.loc16 != 0    $criteria group by a.pc_uid,a.loc12,a.loc15,locid";

        //echo $total_covered_sql;die;

      
        $covered_result = DB::select(DB::raw($total_covered_sql));

    
        for ($i = 0;$i < count($covered_result);$i++)
        {
          // $covered_result[$i]->pc_uid= 1872;

          if (!array_key_exists($covered_result[$i]->pc_uid,$getdetail))
               $getdetail[$covered_result[$i]->pc_uid]=CommonController::getreportee($covered_result[$i]->pc_uid,$covered_result[$i]->locid);

            $total_data_array[$covered_result[$i]->locid]['retailer'] = array(
                'covered_shop' => $covered_result[$i]->covered_shop,
                'colony_id' => $covered_result[$i]->locid,
                'ward_id' => $covered_result[$i]->ward_id,
                'city_id' => $covered_result[$i]->city_id,
                'pc_uid'=>$covered_result[$i]->pc_uid,                 
                'so_id'=>$getdetail[$covered_result[$i]->pc_uid]['so_id'],
                'asm_id'=>$getdetail[$covered_result[$i]->pc_uid]['asm_id'],
                'bsm_id'=>$getdetail[$covered_result[$i]->pc_uid]['bsm_id'],
                'pc_name'=>$getdetail[$covered_result[$i]->pc_uid]['pc_name'],
                'so_name'=>$getdetail[$covered_result[$i]->pc_uid]['so_name'],
                'asm_name'=>$getdetail[$covered_result[$i]->pc_uid]['asm_name'],
                'bsm_name'=>$getdetail[$covered_result[$i]->pc_uid]['bsm_name'],
                'distributor'=>$getdetail[$covered_result[$i]->pc_uid]['distributor']
                //'distributor'=>$covered_result[$i]->name
            );

           
        }
        //var_dump($total_data_array);die;

        $detail_array = []; $non_potential_array=[];

        foreach ($maparray as $key => $value)
        {

            if (isset($total_data_array[$key]))
            {
                 if(!(isset($total_data_array[$key]['retailer']['pc_uid'])))
                {
                    $pc_uid = DB::table('loclty_pc_link')->where('loc16', $value['loc_id'])->select(['pc_uid'])->first();
                    $pc_uid=$pc_uid->pc_uid;
                    if (!array_key_exists($pc_uid,$getdetail))
                        $getdetail[$pc_uid]=CommonController::getreportee($pc_uid,$value['loc_id']);
                    $total_data_array[$key]['retailer']['pc_uid']=$pc_uid;
                    $total_data_array[$key]['retailer']['so_id']=$getdetail[$pc_uid]['so_id'];
                    $total_data_array[$key]['retailer']['asm_id']=$getdetail[$pc_uid]['asm_id'];
                    $total_data_array[$key]['retailer']['bsm_id']=$getdetail[$pc_uid]['bsm_id'];
                    $total_data_array[$key]['retailer']['pc_name']=$getdetail[$pc_uid]['pc_name'];
                    $total_data_array[$key]['retailer']['so_name']=$getdetail[$pc_uid]['so_name'];
                    $total_data_array[$key]['retailer']['asm_name']=$getdetail[$pc_uid]['asm_name'];
                    $total_data_array[$key]['retailer']['bsm_name']=$getdetail[$pc_uid]['bsm_name'];
                    $total_data_array[$key]['retailer']['distributor']=$getdetail[$pc_uid]['distributor'];

                   
                }
                $total_shop = 0;
                $retailer_shop = 0;
                $uncovered_shop = 0;
                if (isset($total_data_array[$key]['total'])) $total_shop = $total_data_array[$key]['total']['total_shop'];
                if (isset($total_data_array[$key]['retailer']['covered_shop'])) $retailer_shop = $total_data_array[$key]['retailer']['covered_shop'];

               // if($total_shop !=0 && $retailer_shop !=0 )
              //  {
                    if ($total_shop > $retailer_shop) $uncovered_shop = $total_shop - $retailer_shop;
                    if ($total_shop < $retailer_shop)
                    {
                        $total_shop = $retailer_shop;
                        $uncovered_shop = $total_shop - $retailer_shop;
                    }

                   if($uncovered_shop > 0)
                    array_push($detail_array, array(
                        'total_shop' => $total_shop,
                        'covered_shop' => $retailer_shop,
                        'uncovered_shop' => $uncovered_shop,
                        'city_id' => $value['loc12'],
                        'ward_id' => $value['loc15'],
                        'colony_id' => $value['loc_id'],
                       // 'distributor' => $value['distributor'],
                        'pc_uid'=> $total_data_array[$key]['retailer']['pc_uid'],
                        'so_id'=> $total_data_array[$key]['retailer']['so_id'],
                        'asm_id'=> $total_data_array[$key]['retailer']['asm_id'],
                        'bsm_id'=> $total_data_array[$key]['retailer']['bsm_id'],
                        'pc_name'=>$total_data_array[$key]['retailer']['pc_name'],
                        'so_name'=>$total_data_array[$key]['retailer']['so_name'],
                        'asm_name'=>$total_data_array[$key]['retailer']['asm_name'],
                        'bsm_name'=>$total_data_array[$key]['retailer']['bsm_name'],
                        'distributor'=>$total_data_array[$key]['retailer']['distributor']
                       // 'distributor'=>$getdetail[$covered_result[$i]->pc_uid]['distributor']
                    ));
                 else if($uncovered_shop <=0)
                    array_push($non_potential_array, array(
                        'total_shop' => $total_shop,
                        'covered_shop' => $retailer_shop,
                        'uncovered_shop' => $uncovered_shop,
                        'city_id' => $value['loc12'],
                        'ward_id' => $value['loc15'],
                        'colony_id' => $value['loc_id'],
                       //  'distributor' => $value['distributor'],
                        'pc_uid'=> $total_data_array[$key]['retailer']['pc_uid'],
                        'so_id'=> $total_data_array[$key]['retailer']['so_id'],
                        'asm_id'=> $total_data_array[$key]['retailer']['asm_id'],
                        'bsm_id'=> $total_data_array[$key]['retailer']['bsm_id'],
                        'pc_name'=>$total_data_array[$key]['retailer']['pc_name'],
                        'so_name'=>$total_data_array[$key]['retailer']['so_name'],
                        'asm_name'=>$total_data_array[$key]['retailer']['asm_name'],
                        'bsm_name'=>$total_data_array[$key]['retailer']['bsm_name'],
                        'distributor'=>$total_data_array[$key]['retailer']['distributor']
                        //'distributor'=>$getdetail[$covered_result[$i]->pc_uid]['name']
                    ));


             //   }

                

            }

        }

        $uncoverval_arr = array_column($detail_array, 'uncovered_shop');

        array_multisort($uncoverval_arr, SORT_DESC, $detail_array);

        $totaldata=count($detail_array);
        $clr_code = array("G"=>"#01875B","Y"=>"#e0d006","R"=>"#eb3136");
        $clr_split_cnt = array("G"=>round(($totaldata*33)/100),"Y"=>round(($totaldata*33)/100),"R"=>round(($totaldata*34)/100));

        $lolctyColrSplit = array();
         if($totaldata == 1)   {    
             $lolctyColrSplit['G'] = array_slice($detail_array,0,1,TRUE);}
         if($totaldata == 2){
             $lolctyColrSplit['G'] = array_slice($detail_array,0,1,TRUE);
             $lolctyColrSplit['Y'] = array_slice($detail_array,1,1,TRUE);
         }
         if($totaldata == 3){
             $lolctyColrSplit['G'] = array_slice($detail_array,0,1,TRUE);
             $lolctyColrSplit['Y'] = array_slice($detail_array,1,1,TRUE);
             $lolctyColrSplit['R'] = array_slice($detail_array,2,1,TRUE);
         }
          if($totaldata == 4){
             $lolctyColrSplit['G'] = array_slice($detail_array,0,1,TRUE);
             $lolctyColrSplit['Y'] = array_slice($detail_array,1,1,TRUE);
             $lolctyColrSplit['R'] = array_slice($detail_array,2,1,TRUE);
             $lolctyColrSplit['R'] = array_slice($detail_array,3,2,TRUE);
         }
         if($totaldata > 4)
         {
            $lolctyColrSplit['G'] = array_slice($detail_array,0,round(($totaldata*33)/100),TRUE);
            $lolctyColrSplit['Y'] = array_slice($detail_array,round(($totaldata*33)/100),round(($totaldata*33)/100),TRUE);
            $lolctyColrSplit['R'] = array_slice($detail_array,round(($totaldata*33)/100)*2,round(($totaldata*34)/100),TRUE);
         }
         $rank=1;
         $total_count=count($detail_array)+count($non_potential_array);

        foreach($lolctyColrSplit as $clr => $lolctyVal)
        {
             $clr_tot = array_sum(array_column($lolctyVal,'uncovered_shop'));
             $add_shr = 0;
             foreach($lolctyVal as $key => $val)
            {
                $shr = ($val['uncovered_shop']/$clr_tot)*100;
                $add_shr = $add_shr + $shr;
                $inc = (ceil($add_shr/10)*10)-10; // To get Color Percent               
                $inc = $inc > 90 ? 90 : $inc;  
                if(count($lolctyColrSplit[$clr]) <= 3)
                    $final_color = $clr_code[$clr];                
                else
                    $final_color = $this->getColorCodeByPercent($clr_code[$clr], $inc);
                $type=($clr=='G') ? 'High' : (($clr=='Y') ? 'Medium' : 'Low');

                

                // $info_text = '<b>' . $maparray[$val['colony_id']]['location_name'] . '<br>Total Retailers : ' . $val['total_shop'] . '<br>Covered Retailers :  ' . $val['covered_shop'] . '<br>Uncovered Retailers: ' . $val['uncovered_shop'];
                // $info_text = "<div class='tooltip-data'><div class='card'><div class='card-header'>". $maparray[$val['colony_id']]['location_name'] ." <span class='".strtolower($type)."' style='background-color:".$final_color."'>".$type."</span></div><ul class='list-group list-group-flush'><li class='list-group-item'>Total Retailers (Nos.) <span>" . $val['total_shop'] . "</span></li><li class='list-group-item'>Mondelez Retailers (Nos.) <span> ". $val['covered_shop'] . "</span></li><li class='list-group-item' style='background-color:".$final_color."'>Uncovered Retailers (Nos.) <span>" . $val['uncovered_shop']."</span></li></ul></div></div>";

                 $info_text='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$val['colony_id']]['location_name'].' <small>Rank   '.$rank.'/'.$total_count.'</small></h3> <span class="'.strtolower($type).'" style="background-color:'.$final_color.'">'.$type.'</span></div><ul class="list-group list-group-flush"><li class="list-group-item">Total Retailers (Nos.) <span>'. $val['total_shop'] .'</span></li><li class="list-group-item">Mondelez Retailers (Nos.) <span>'. $val['covered_shop'] .'</span></li><li class="list-group-item" style="background-color:'.$final_color.';color:#fff !important;">Uncovered Retailers (Nos.) <span>'. $val['uncovered_shop'].'</span></li></ul><div class="adtnl-details"><ul class="list-group list-group-flush"><li class="list-group-item">'.$val['asm_name'].' <span>ASM</span></li><li class="list-group-item">'.$val['so_name'].' <span> SO</span></li><li class="list-group-item" >'.$val['pc_name'].' <span>PC</span></li><li class="list-group-item" >'.$val['distributor'].' <span>Distrbtr</span></li></ul></div></div></div>';

            if ($val['total_shop'] == 0) $val['total_shop'] = 1;

            $temp = array(
                'location_id' => $val['colony_id'],
                'location_name' => $maparray[$val['colony_id']]['location_name'],
                'total_shop' => $val['total_shop'],
                'covered_shop' => $val['covered_shop'],
                'uncovered_shop' => $val['uncovered_shop'],
                'percentage' => round(((int)$val['covered_shop'] / (int)$val['total_shop']) * 100) ,
                'color' => $final_color ,
                'info' => $info_text,
                'loc15' => $val['ward_id'],
                'pc_uid' => $val['pc_uid'],
                'so_id' => $val['so_id'],
                'asm_id' => $val['asm_id'],
                'bsm_id' => $val['bsm_id'],
                'loc12' => $val['city_id'],
                'pc_name'=>$val['pc_name'],
                'so_name'=>$val['so_name'],
                'asm_name'=>$val['asm_name'],
                'bsm_name'=>$val['bsm_name'],
            );

            $maparray[$val['colony_id']] = array_merge($maparray[$val['colony_id']], $temp);

            array_push($data['result'], $temp);


            $rank++;

                
             
            }
        }

        for($k=0;$k<count($non_potential_array);$k++)
        {
              $final_color='#fff';$type='No data';
              $val=$non_potential_array[$k];
              // $info_text = "<div class='tooltip-data'><div class='card'><div class='card-header'>". $maparray[$val['colony_id']]['location_name'] ." <span class='".strtolower($type)."' style='background-color:".$final_color."'>".$type."</span></div><ul class='list-group list-group-flush'><li class='list-group-item'>Total Retailers (Nos.) <span>" . $val['total_shop'] . "</span></li><li class='list-group-item'>Mondelez Retailers (Nos.) <span> ". $val['covered_shop'] . "</span></li><li class='list-group-item' style='background-color:".$final_color."'>Uncovered Retailers (Nos.) <span>" . $val['uncovered_shop']."</span></li></ul></div></div>";


               //  $info_text='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$val['colony_id']]['location_name'].' <small>Rank:  1/1000</small></h3> <span class="'.strtolower($type).'" style="background-color:'.$final_color.'">'.$type.'</span></div><ul class="list-group list-group-flush"><li class="list-group-item">Total Retailers (Nos.) <span>'. $val['total_shop'] .'</span></li><li class="list-group-item">Mondelez Retailers (Nos.) <span>'. $val['covered_shop'] .'</span></li><li class="list-group-item" style="background-color:'.$final_color.'">Uncovered Retailers (Nos.) <span>'. $val['uncovered_shop'].'</span></li></ul><div class="adtnl-details"><ul class="list-group list-group-flush"><li class="list-group-item">Lalit Mohan <span>ASM</span></li><li class="list-group-item">Gupta Saurabh <span> SO</span></li><li class="list-group-item" >Yakub Sayed <span>PC</span></li><li class="list-group-item" >Mehta Marketing <span>Distrbtr</span></li></ul></div></div></div>';

                 $info_text='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$val['colony_id']]['location_name'].' <small>Rank   '.$rank.'/'.$total_count.'</small></h3> <span class="'.strtolower($type).'" style="background-color:'.$final_color.'">'.$type.'</span></div><ul class="list-group list-group-flush"><li class="list-group-item">Total Retailers (Nos.) <span>'. $val['total_shop'] .'</span></li><li class="list-group-item">Mondelez Retailers (Nos.) <span>'. $val['covered_shop'] .'</span></li><li class="list-group-item" style="background-color:'.$final_color.';color:#fff !important;">Uncovered Retailers (Nos.) <span>'. $val['uncovered_shop'].'</span></li><li></li></ul><div class="adtnl-details"><ul class="list-group list-group-flush"><li class="list-group-item">'.$val['asm_name'].' <span>ASM</span></li><li class="list-group-item">'.$val['so_name'].' <span> SO</span></li><li class="list-group-item" >'.$val['pc_name'].' <span>PC</span></li></li><li class="list-group-item" >'.$val['distributor'].' <span>Distrbtr</span></li></ul></div></div></div>';

               

                $temp = array(
                    'location_id' => $val['colony_id'],
                    'location_name' => $maparray[$val['colony_id']]['location_name'],
                    'total_shop' => $val['total_shop'],
                    'covered_shop' => $val['covered_shop'],
                    'uncovered_shop' => $val['uncovered_shop'],
                    'percentage' =>0,
                    'color' => $final_color ,
                    'info' => $info_text,
                    'loc15' => $val['ward_id'],
                    'loc12' => $val['city_id'],
                    'pc_uid' => $val['pc_uid'],
                    'so_id' => $val['so_id'],
                    'asm_id' => $val['asm_id'],
                    'bsm_id' => $val['bsm_id'],
                    'pc_name'=>$val['pc_name'],
                    'so_name'=>$val['so_name'],
                    'asm_name'=>$val['asm_name'],
                    'bsm_name'=>$val['bsm_name'],
                );

                $maparray[$val['colony_id']] = array_merge($maparray[$val['colony_id']], $temp);

                array_push($data['result'], $temp);

                $rank++;


        }

        $data['legend'] = [];
        array_push($data['legend'],array('High'=>'#01875B','Medium'=>'#e0d006','Low'=>'#eb3136'));

        $data['mapdata'] = $maparray;
        $data['griddata'] = array();

        $data['griddata'] = $this->gridcolumn($data['result'], $loc15, $loc12);

        $head = CommonController::headline($loc12);
        $data['head'] = $head;

        return $data;

    }
    // public function gridcolumn_byoutletlist_bycategory($data)
    // {
    //    $column = array();
    //      $value = array();



    //      array_push($column, array(
    //         'title' => '#', 'className' => 'text-right'
    //     ));
    //        array_push($column, array(
    //         'title' => ucwords('Outlet Name')
    //     ));
    //        array_push($column, array(
    //         'title' => ucwords('Channel')
    //     ));
          
    //        array_push($column, array(
    //         'title' => ucwords('Sub-channel')
    //     ));
       
    //         array_push($column, array(
    //         'title' => ucwords('Address')
    //     ));
    //         array_push($column, array(
    //         'title' => ucwords('Status')
    //     ));
            

    //     for ($i = 0;$i < count($data);$i++)
    //     {

    //       if(!isset($data[0]['status']) )
    //          $temp = array(
    //             ($i + 1) ,   
                
    //              '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
                 
    //             $data[$i]['channel_name'],
    //                $data[$i]['sub_channel_name'],
               
                 
    //               // $data[$i]['beat_name'],
              
    //             $data[$i]['address'],
                
    //             //  $data[$i]['lat'],
    //             // $data[$i]['lon'],
                 
                 

    //         );

    //        else if(isset($data[0]['beat_id']))
    //          $temp = array(
    //             ($i + 1) ,   
                
    //              '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
    //              ((!isset($data[$i]['maintype_id'])) ? $data[$i]['channel_name'] :
    //               '<a href="#" style="text-decoration:underline" onClick="showuncovered('.$data[$i]['maintype_id'].')">'.$data[$i]['channel_name'].'</a>'),
    //            // $data[$i]['channel_name'],
    //                $data[$i]['sub_channel_name'],
    //               '<a href="#" style="text-decoration:underline" onClick="showbeat('.$data[$i]['beat_id'].')">'.$data[$i]['beat_name'].'</a>',
                 
    //               // $data[$i]['beat_name'],
              
    //             $data[$i]['address'],
                
    //             //  $data[$i]['lat'],
    //             // $data[$i]['lon'],
    //              ((isset($data[$i]['maintype_id'])) ?  (($data[$i]['status']=='R') ? 'Not Relevent' : (($data[$i]['status']=='A') ? 'Activated' : 'Uncovered')) : 
    //               (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
    //             )

                 

    //         );

    //        else
    //         $temp = array(
    //             ($i + 1) ,   
    //             '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',

    //             $data[$i]['channel_name'],
               
    //             $data[$i]['sub_channel_name'],
    //             $data[$i]['address'],
              
    //             (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
    //             //  $data[$i]['lat'],
    //             // $data[$i]['lon'],
                 

    //         );
    //      //    var_dump($data);continue;

    //         array_push($value, $temp);

    //     }

    //     return array(
    //         'column' => $column,
    //         'value' => $value
    //     );

    // }
    public function gridcolumn_byoutletlist_bycategory($data)
    {
       $column = array();
         $value = array();
          $user = auth()->user();
      $userid=$user->id;
      



         array_push($column, array(
            'title' => '#', 'className' => 'text-right'
        ));
           array_push($column, array(
            'title' => ucwords('Outlet Name')
        ));
           array_push($column, array(
            'title' => ucwords('Channel')
        ));
          if($user->client_id !=86 && $user->client_id !=120)
           {
             array_push($column, array(
              'title' => ucwords('Sub-channel')
          ));
         }
         else
          array_push($column, array(
              'title' => ucwords('Cluster')
          ));

            if(isset($data[0]['beat_id']))
               array_push($column, array(
            'title' => ucwords('Beat')
        ));
           
            array_push($column, array(
            'title' => ucwords('Address')
        ));
            if(isset($data[0]['potential_status']))
                array_push($column, array(
            'title' => ucwords('Estimated Potential')
        ));

        //      array_push($column, array(
        //     'title' => ucwords('Latitude')
        // ));
        //       array_push($column, array(
        //     'title' => ucwords('Longitude')
        // ));
             if(isset($data[0]['stock_confectionary']) && $user->client_id!=86 && $user->client_id !=120)
                array_push($column, array(
            'title' => ucwords('Stock Confectionary')
        ));
              if(isset($data[0]['stock_chocolate']) && $user->client_id!=86 && $user->client_id !=120)
                array_push($column, array(
            'title' => ucwords('Stock Chocolate')
        ));
              if(isset($data[0]['status']))
                array_push($column, array(
            'title' => ucwords('Status')
        ));

             

        for ($i = 0;$i < count($data);$i++)
        {

          if(!isset($data[0]['status']) )
             $temp = array(
                ($i + 1) ,   
                
                 '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
                 ((!isset($data[$i]['maintype_id'])) ? $data[$i]['channel_name'] :
                  '<a href="#" style="text-decoration:underline" onClick="showuncovered('.$data[$i]['maintype_id'].')">'.$data[$i]['channel_name'].'</a>'),
               // $data[$i]['channel_name'],
                   $data[$i]['sub_channel_name'],
               
                $data[$i]['address'],
                
                //  $data[$i]['lat'],
                // $data[$i]['lon'],
                 ((isset($data[$i]['maintype_id'])) ?  (($data[$i]['status']=='R') ? 'Not Relevent' : (($data[$i]['status']=='A') ? 'Activated' : (($data[$i]['status']=='NF') ? 'Not Found' : (($data[$i]['status']=='E') ? 'Existing':'Uncovered')))) : 
                  (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                )

                 

            );
             else if(isset($data[0]['beat_id']) && isset($data[0]['potential_status']))

             $temp = array(
                ($i + 1) ,   
                
                 '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
                 ((!isset($data[$i]['maintype_id'])) ? $data[$i]['channel_name'] :
                  '<a href="#" style="text-decoration:underline" onClick="showuncovered('.$data[$i]['maintype_id'].')">'.$data[$i]['channel_name'].'</a>'),   
                   $data[$i]['sub_channel_name'],
                  '<a href="#" style="text-decoration:underline" onClick="showbeat('.$data[$i]['beat_id'].')">'.$data[$i]['beat_name'].'</a>', 
                $data[$i]['address'],
                $data[$i]['potential_status_name'],

                 ((isset($data[$i]['maintype_id'])) ?  (($data[$i]['status']=='R') ? '<span id="tbl_'.$data[$i]['refid'].'">Not Relevent</span>' : (($data[$i]['status']=='A') ? '<span id="tbl_'.$data[$i]['refid'].'">Visited</span>' : (($data[$i]['status']=='NF') ? '<span id="tbl_'.$data[$i]['refid'].'">Not Found</span>': (($data[$i]['status']=='E') ? '<span id="tbl_'.$data[$i]['refid'].'">Existing</span>' :'<span id="tbl_'.$data[$i]['refid'].'">Uncovered</span>')))) : 
                  (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                )

                 

            );

           else if(isset($data[0]['beat_id']))

             $temp = array(
                ($i + 1) ,   
                
                 '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
                 ((!isset($data[$i]['maintype_id'])) ? $data[$i]['channel_name'] :
                  '<a href="#" style="text-decoration:underline" onClick="showuncovered('.$data[$i]['maintype_id'].')">'.$data[$i]['channel_name'].'</a>'),   
                   $data[$i]['sub_channel_name'],
                  '<a href="#" style="text-decoration:underline" onClick="showbeat('.$data[$i]['beat_id'].')">'.$data[$i]['beat_name'].'</a>', 
                $data[$i]['address'],
                 ((isset($data[$i]['maintype_id'])) ?  (($data[$i]['status']=='R') ? '<span id="tbl_'.$data[$i]['refid'].'">Not Relevent</span>' : (($data[$i]['status']=='A') ? '<span id="tbl_'.$data[$i]['refid'].'">Visited</span>' : (($data[$i]['status']=='NF') ? '<span id="tbl_'.$data[$i]['refid'].'">Not Found</span>': (($data[$i]['status']=='E') ? '<span id="tbl_'.$data[$i]['refid'].'">Existing</span>' :'<span id="tbl_'.$data[$i]['refid'].'">Uncovered</span>')))) : 
                  (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                )

                 

            );
           else if(isset($data[0]['potential_status']) && $user->client_id!=86 &&  $user->client_id!=120)
             $temp = array(
                ($i + 1) ,   
                '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',

                $data[$i]['channel_name'],
               
                $data[$i]['sub_channel_name'],
                $data[$i]['address'],
                $data[$i]['potential_status_name'],
                $data[$i]['perimium_name'],

                 (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                

            );
           else if(isset($data[0]['stock_confectionary']) && $user->client_id!=86  && $user->client_id!=120)
             $temp = array(
                ($i + 1) ,   
                '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',

                $data[$i]['channel_name'],
               
                $data[$i]['sub_channel_name'],
                $data[$i]['address'],
                $data[$i]['stock_confectionary_name'],
                $data[$i]['stock_chocolate_name'],

                 (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                

            );
           else if($user->client_id==86 || $user->client_id==120)
           {
            $res=explode('/', $data[$i]['channel_name']);
             $temp = array(
                ($i + 1) ,   
                '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',

                
                 '<a href="#" style="text-decoration:underline" onClick="showuncovered(\''.$data[$i]['channel_name'].'\','.$data[$i]['cluster_id'].')">'.$res[1].'</a>',
               
               // $explode[1],
                 $data[$i]['cluster_name'],
                $data[$i]['address'],
                 '<a href="#" style="text-decoration:underline" onClick="showpotential(\''.$data[$i]['potential_status_name'].'\','.$data[$i]['cluster_id'].')">'.$data[$i]['potential_status_name'].'</a>',
                //$data[$i]['potential_status_name'],

                 (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                

            );
           }
            

           else
            $temp = array(
                ($i + 1) ,   
                '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',

                $data[$i]['channel_name'],
               
                $data[$i]['sub_channel_name'],
                $data[$i]['address'],

                 (($data[$i]['status']=='R') ? 'Not Found' : (($data[$i]['status']=='A') ? 'Found' : (($data[$i]['status']=='V') ?  'Visited' : 'New' )))
                

            );
         //    var_dump($data);continue;

            array_push($value, $temp);

        }

        return array(
            'column' => $column,
            'value' => $value
        );

    }
    public function gridcolumn_byoutletlist($data)
    {
         $column = array();
         $value = array();



         array_push($column, array(
            'title' => '#', 'className' => 'text-right'
        ));
           array_push($column, array(
            'title' => ucwords('Establishment Name')
        ));
           array_push($column, array(
            'title' => ucwords('Channel')
        ));
          
           array_push($column, array(
            'title' => ucwords('Sub-channel')
        ));
          
            array_push($column, array(
            'title' => ucwords('proprietor')
        ));
            array_push($column, array(
            'title' => ucwords('Address')
        ));
             array_push($column, array(
            'title' => ucwords('PAN')
        ));
              array_push($column, array(
            'title' => ucwords('TAN')
        ));
               array_push($column, array(
            'title' => ucwords('Mobile No.')
        ));

      array_push($column, array(
             'title' => ucwords('Shop Establish No.')
        ));
      array_push($column, array(
            'title' => ucwords('GST No.')
        ));
       array_push($column, array(
            'title' => ucwords('Outlet Snap')
        ));

        for ($i = 0;$i < count($data);$i++)
        {



           
            $temp = array(
                ($i + 1) ,   
                 $data[$i]['outlet_name'],
                $data[$i]['channel_name'],
                $data[$i]['sub_channel_name'],                
                $data[$i]['owner_name'],
                $data[$i]['address'],
                //$data[$i]['pan_no'],'',
                'XXXXXXXXXX',
                //$data[$i]['tan_no'],
                'XXXXXXXXXX',
                $data[$i]['mobile_no'],
                'XXXXXX',
                //$data[$i]['shop_establish_no'],
                //$data[$i]['gst_no'],
                'XXXXXXXXXXXXXXX',
                '<img alt="'.($i + 1).'" src="'.$data[$i]['shop_image'].'" class="showimage" onClick="showimage(this)"/>'
                
            );
         //    var_dump($data);continue;

            array_push($value, $temp);

        }

        return array(
            'column' => $column,
            'value' => $value
        );




    }
     public function gridcolumn_bycluster($data)
    {
         $column = array();
         $value = array();



         array_push($column, array(
            'title' => '#', 'className' => 'text-right'
        ));
           array_push($column, array(
            'title' => ucwords('Cluster Name')
        ));
           array_push($column, array(
            'title' => 'No. of Total outlets'
        ));
          
           array_push($column, array(
            'title' => ucwords('No. of High Potntl Stores')
        ));
          
            array_push($column, array(
            'title' => ucwords('No. of Medium Potntl Stores')
        ));
        //     array_push($column, array(
        //     'title' => ucwords('No. of Low Potntl Stores')
        // ));
            

        for ($i = 0;$i < count($data);$i++)
        {



           
            $temp = array(
                ($i + 1) ,   
                  '<a href="#" style="text-decoration:underline" onClick="getmaker('.$data[$i]['refid'].')">'.$data[$i]['name'].'</a>',
                $data[$i]['total'],
                $data[$i]['High'],                
                $data[$i]['Medium'],
                //$data[$i]['Low'],
              
                
            );
         //    var_dump($data);continue;

            array_push($value, $temp);

        }

        return array(
            'column' => $column,
            'value' => $value
        );




    }
    public function gridcolumn($data, $loc15, $loc12)
    {
        $column = array();
        $value = array();

        $citydata = CommonController::getcity($loc12);
        $warddata = CommonController::getward($loc15);

        array_push($column, array(
            'title' => '#', 'className' => 'text-right'
        ));
        // array_push($column, array(
        //     'title' => ucwords('city')
        // ));
         array_push($column, array(
            'title' => ucwords('Locality Name')
        ));
        array_push($column, array(
            'title' => ucwords('N\'Bhrhd Name')
        ));       
         array_push($column, array(
            'title' => ucwords('PC')
        ));
        array_push($column, array(
            'title' => ucwords('SO')
        ));
        array_push($column, array(
            'title' => ucwords('ASM')
        ));
        array_push($column, array(
            'title' => ucwords('BSM')
        ));
        array_push($column, array(
            'title' => ucwords('Retlr Univrs (Nos.)') ,
            'className' => 'text-right'
        ));
        array_push($column, array(
            'title' => ucwords('Mdlz coverage (Nos.)') ,
            'className' => 'text-right'
        ));
        array_push($column, array(
            'title' => ucwords('Mdlz Opportunity (Nos.)') ,
            'className' => 'text-right'
        ));
        // array_push($column, array(
        //     'title' => ucwords('ND %') ,
        //     'className' => 'text-right'
        // ));
        $user = auth()->user();




        for ($i = 0;$i < count($data);$i++)
        {

            $temp = array(
                ($i + 1) ,
                $data[$i]['location_name'], 
               // $citydata[$data[$i]['loc12']],
                // '<a href="#" id="' . $data[$i]['location_id'] . '" style="text-decoration:underline" onClick="showbound(this)">' . $data[$i]['location_name'] . '</a>',

                $warddata[$data[$i]['loc15']],
                
              '<a href="#" id="' . $data[$i]['pc_uid'] . '" style="text-decoration:underline" onClick="showboundbyusertype(this)">' . $data[$i]['pc_name'] . '</a>',
              ($user->user_type == 'SO') ?  '<a href="#" id="" style="text-decoration:underline" onClick="showboundbyusertype(this)">' . $data[$i]['so_name'] . '</a>' :  '<a href="#" so_id="' . $data[$i]['so_id'] . '" style="text-decoration:underline" onClick="showboundbyusertype(this)">' . $data[$i]['so_name'] . '</a>',

               //$data[$i]['pc_name'],
               // $data[$i]['so_name'],
                $data[$i]['asm_name'],
                $data[$i]['bsm_name'],
                $data[$i]['total_shop'],
                $data[$i]['covered_shop'],
                $data[$i]['uncovered_shop']
               // $data[$i]['percentage'] . '%'
            );
         //    var_dump($data);continue;

            array_push($value, $temp);

        }

        return array(
            'column' => $column,
            'value' => $value
        );

    }
    public function gridcolumn_bystatus($data, $loc15, $loc12)
    {
        $column = array();
        $value = array();

        $citydata = CommonController::getcity($loc12);
        $warddata = CommonController::getward($loc15);
        $user = auth()->user();

        array_push($column, array(
            'title' => '#', 'className' => 'text-right'
        ));
        // array_push($column, array(
        //     'title' => ucwords('city')
        // ));
         array_push($column, array(
            'title' => ucwords('Locality Name')
        ));
        array_push($column, array(
            'title' => ucwords('N\'Bhrhd Name')
        ));
          array_push($column, array(
            'title' => ucwords('PC')
        ));
        array_push($column, array(
            'title' => ucwords('SO')
        ));
        array_push($column, array(
            'title' => ucwords('ASM')
        ));
        array_push($column, array(
            'title' => ucwords('BSM')
        ));
       
        array_push($column, array(
            'title' => ucwords('Status') ,
            'className' => 'text-right'
        ));
        array_push($column, array(
            'title' => ucwords('Action Date') ,
            'className' => 'text-right'
        ));
        

        for ($i = 0;$i < count($data);$i++)
        {

            $temp = array(
                ($i + 1) ,
                //$citydata[$data[$i]['loc12']],
                  '<a href="#" id="' . $data[$i]['location_id'] . '" style="text-decoration:underline" onClick="showbound(this)">' . $data[$i]['location_name'] . '</a>',
                $warddata[$data[$i]['loc15']],
                '<a href="#" id="' . $data[$i]['pc_uid'] . '" style="text-decoration:underline" onClick="showboundbyusertype(this)">' . $data[$i]['pc_name'] . '</a>',
              ($user->user_type == 'SO') ?  '<a href="#" id="" style="text-decoration:underline" onClick="showboundbyusertype(this)">' . $data[$i]['so_name'] . '</a>' :  '<a href="#" id="' . $data[$i]['so_id'] . '" style="text-decoration:underline" onClick="showboundbyusertype(this)">' . $data[$i]['so_name'] . '</a>',

                $data[$i]['asm_name'],
                $data[$i]['bsm_name'],

              
                $data[$i]['covered_stat'],
                ($data[$i]['action_date'] != '') ? date('d M Y H:i A',strtotime($data[$i]['action_date'])) : ''
            );

            array_push($value, $temp);

        }

        return array(
            'column' => $column,
            'value' => $value
        );

    }
    public function getColorCodeByPercent($hexCode, $adjustPercent) 
    {
       
        $hexCode = ltrim($hexCode, '#');
        
        $hexCode = array_map('hexdec', str_split($hexCode, 2));
        
        foreach ($hexCode as &$color) {
            $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $adjustPercent / 100);
            
            $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }
        
        return '#'.implode($hexCode);
    }
    public function show_outletlist_bycategory($maparray,$type,$main_location,$sub_location,$so_id,$input_obj,$current_location)
    {

        $data = [];
        $data['result'] = array();      
        $data['channel_list'] = array();    
        $user = auth()->user();
        $userid = $user->id;   
        $key = array_keys($maparray);
        $value = array_values($maparray);
        $loc15 = array_unique(array_column($value, 'loc15'));
        $loc12 = array_unique(array_column($value, 'loc12')); 
        $condionarr=array('key'=>array(),'value'=>array(),'icon'=>array());
        $condionarr['key'][5]=[29,30,224,228];         
        $condionarr['key'][6]=[43,181];
        $condionarr['key'][7]=[203,16,17];


        $user = auth()->user();
        $userid=$user->id;
        $obj=json_decode($input_obj,true);


        if($type==9)
        {
           
            $lat=$current_location[0];
            $lon=$current_location[1];
            $data_outlet_list=[];
            $data_uncovered_outlet_list=[];
             $user = auth()->user();
          $client_id=$user->client_id;
          $feedback_question=[];

          $get_headline= DB::table('question_type')->where([['client_id','=', $client_id],['stat','=', 'A']])->get();
          $get_headline_count=count($get_headline);
          for($i=0;$i<$get_headline_count;$i++)
          {
             $feedback_question[$get_headline[$i]->refid]=['title'=>[$get_headline[$i]->question_type],'question'=>[]];
             $feedback_question_sl=DB::table('feedback_question')->where([['question_type','=', $get_headline[$i]->refid],['client_id','=', $client_id],['stat','=', 'A']])->get();
            $feed_question_count=count($feedback_question_sl);
             for($j=0;$j<$feed_question_count;$j++)
             {
                $temp=[];
                $temp['refid']=$feedback_question_sl[$j]->refid;
                $temp['question']=$feedback_question_sl[$j]->question;
                $temp['option_1']=$feedback_question_sl[$j]->option_1;
                $temp['option_2']=$feedback_question_sl[$j]->option_2;
                $temp['option_3']=$feedback_question_sl[$j]->option_3;
                $temp['option_4']=$feedback_question_sl[$j]->option_4;
                $temp['parent']=$feedback_question_sl[$j]->parent;
                $temp['type']=$feedback_question_sl[$j]->type;

                array_push($feedback_question[$get_headline[$i]->refid]['question'],$temp);
             }
          }


            $data_outlet_list =  DB::table('covered_outlets')
            ->join('beat_master', 'covered_outlets.beat_id', '=', 'beat_master.id')
            ->whereIn('covered_outlets.salesman_id',[$userid]);
            if(isset($obj['filter_beat']) && count($obj['filter_beat'])>0)
              $data_outlet_list->whereIn('covered_outlets.beat_id',$obj['filter_beat']);
             if(isset($obj['show_beat']) && $obj['show_beat']!='')
              $data_outlet_list->whereIn('covered_outlets.beat_id',[$obj['show_beat']]);


            $data_outlet_list->select('covered_outlets.id as refid'  , 'covered_outlets.channel as channel', 'covered_outlets.secondary_channel_type as subchannel', 'covered_outlets.name as outlet_name', 'covered_outlets.address as address', 'covered_outlets.latitude as lat', 'covered_outlets.longitude as lon','beat_master.beat_name','beat_master.id as beat_id');

            $data_outlet_list=$data_outlet_list->get();


            $filterchannel='';$filterpotential='';$filter_beat='';$show_beat=''; $status_outlet='';
            if(isset($obj['filter_bychannel']) && $obj['filter_bychannel']!='' && $obj['filter_bychannel']!=0)
              $filterchannel=' and maintype_id in ('.implode(",",$obj['filter_bychannel']).')';
           if(isset($obj['filter_bypotential']) && $obj['filter_bypotential']!='' && $obj['filter_bypotential']!=0)
              $filterpotential=' and fld1923 in ('.implode(",",$obj['filter_bypotential']).')'; //fld1923
            if(isset($obj['filter_beat'])  && count($obj['filter_beat']) > 0)
              $filter_beat=' and beat_id in ('.implode(",",$obj['filter_beat']).')';
             if(isset($obj['show_beat']) && $obj['show_beat']!='')
              $show_beat=' and beat_id in ('.$obj['show_beat'].')';

             if(isset($obj['filter_bystatus']) && (count($obj['filter_bystatus']) > 0))
             {
                $temp='(';
                foreach ($obj['filter_bystatus'] as $key => $value) {
                  $temp .= '"'.$value.'",';
                }
                $temp=trim($temp,",");
                $temp=$temp.")";
                $status_outlet=" and a.status in $temp ";
             }

            


         // $data_uncovered_outlet_list = "SELECT a.refid,rtlr_id,main_type as channel,SubType as subchannel,ccp as outlet_name,address as address,latitude as lat,longitude as lon,status,maintype_icon,maintype_id, c.shop_image,(((acos(sin((".$lat."*pi()/180)) * sin((`latitude`*pi()/180)) + cos((".$lat."*pi()/180)) * cos((`latitude`*pi()/180)) * cos(((".$lon."- `longitude`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance,b.beat_name,b.id as beat_id  FROM uncovered_outlets as a,beat_master as b, hul_alsi_maintype_master as c   where a.beat_id=b.id and a.maintype_id=c.refid and salesman_id='".$userid."' and latitude!='' and latitude!=0 ".$filterchannel." ".$filterpotential." ".$filter_beat." ".$show_beat." order by distance asc";

            // $data_uncovered_outlet_list = "SELECT a.refid,rtlr_id,main_type as channel,SubType as subchannel,ccp as outlet_name,address as address,latitude as lat,longitude as lon,status,if(a.fld1923=1,c.high,if(a.fld1923=2,c.medium,if(a.fld1923=3,c.low,c.icon))) as maintype_icon,maintype_id,if(a.fld1923=1,'High',if(a.fld1923=2,'Medium',if(a.fld1923=3,'Low',''))) as potential_status, c.shop_image,(((acos(sin((".$lat."*pi()/180)) * sin((`latitude`*pi()/180)) + cos((".$lat."*pi()/180)) * cos((`latitude`*pi()/180)) * cos(((".$lon."- `longitude`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance,b.beat_name,b.id as beat_id  FROM uncovered_outlets as a,beat_master as b, hul_alsi_maintype_master as c   where a.beat_id=b.id and a.maintype_id=c.refid and salesman_id='".$userid."' and latitude!='' and latitude!=0 ".$filterchannel." ".$filterpotential." ".$filter_beat." ".$show_beat." ".$status_outlet."order by distance asc";

            $data_uncovered_outlet_list = "SELECT a.refid,rtlr_id,main_type as channel,SubType as subchannel,ccp as outlet_name,address as address,latitude as lat,longitude as lon,status,if(a.fld1923=3,c.high,if(a.fld1923=2,c.medium,if(a.fld1923=1 ,c.low,c.icon))) as maintype_icon,maintype_id,if(a.potential_store=1,'Low',if(a.potential_store=2,'Medium',if((a.potential_store=3 || a.potential_store=4),'High',''))) as feed_potential_status,if(a.fld1923=3,'High',if(a.fld1923=2,'Medium',if(a.fld1923=1,'Low',''))) as potential_status, c.shop_image,(((acos(sin((".$lat."*pi()/180)) * sin((`latitude`*pi()/180)) + cos((".$lat."*pi()/180)) * cos((`latitude`*pi()/180)) * cos(((".$lon."- `longitude`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance,b.beat_name,b.id as beat_id  FROM uncovered_outlets as a,uncovered_user as bb,beat_master as b, hul_alsi_maintype_master as c   where a.stat='A' and a.rtlr_id=bb.uncovered_id  and a.beat_id=b.id and a.maintype_id=c.refid and bb.user_id='".$userid."' and latitude!='' and latitude!=0 ".$filterchannel." ".$filterpotential." ".$filter_beat." ".$show_beat." ".$status_outlet."order by distance asc";
        
          $data_uncovered_outlet_list = DB::select(DB::raw($data_uncovered_outlet_list));


           $uncovered_outlet_details="SELECT a.* FROM `uncovered_outlet_feedback` as a,uncovered_outlets as b  where a.outlet_id=b.refid and b.rtlr_id in (select uncovered_id from uncovered_user where user_id='".$userid."')";
          //$uncovered_outlet_details="select a.*,b.potention from (SELECT * FROM `uncovered_outlet_feedback` where outlet_id in (select uncovered_id from uncovered_user where user_id='".$userid."')) as a ,(SELECT sum(ifnull(ans,0)) as potention,outlet_id,user_id FROM `uncovered_outlet_feedback` where outlet_id in (select uncovered_id from uncovered_user where user_id='".$userid."') and question=5 group by question) as b where a.outlet_id=b.outlet_id and a.user_id=b.user_id";
          $uncovered_outlet_details_list = DB::select(DB::raw($uncovered_outlet_details));    
          $uncovered_info=[];
          for($i=0;$i<count($uncovered_outlet_details_list);$i++)
          {
            if(!array_key_exists($uncovered_outlet_details_list[$i]->outlet_id, $uncovered_info))
              $uncovered_info[$uncovered_outlet_details_list[$i]->outlet_id]=[];

            $uncovered_info[$uncovered_outlet_details_list[$i]->outlet_id][$uncovered_outlet_details_list[$i]->question]=$uncovered_outlet_details_list[$i];
          


             // array_push($uncovered_info[$uncovered_outlet_details_list[$i]->outlet_id],$uncovered_outlet_details_list[$i]);
          }

          $data_outlet_imagelist =  DB::table('jj_outlet_image')      
               ->where([['user_id','=',$userid],['status','=','A']])               
               ->select('outlet_id', DB::raw('count(*) as total,outlet_image'))
               ->groupBy('outlet_id')
               ->get();

           $imagelist=[];$imagename=[];
           $c=count($data_outlet_imagelist);
          for($i=0;$i<$c;$i++)
          {
             $imagelist[$data_outlet_imagelist[$i]->outlet_id]=$data_outlet_imagelist[$i]->total;
             $imagename[$data_outlet_imagelist[$i]->outlet_id]=$data_outlet_imagelist[$i]->outlet_image;
          }
          
           if(isset($obj['outlet_type']))
          {
             if(!in_array(1, $obj['outlet_type']))
                  $data_outlet_list=[];
             if(!in_array(2, $obj['outlet_type']))
                 $data_uncovered_outlet_list=[];

          }
         



           //   $data_uncovered_outlet_list =  DB::table('uncovered_outlets')->whereIn('salesman_id',[$userid])             
           // ->select('refid'  , 'rtlr_id', 'main_type as channel','maintype_id', 'SubType as subchannel', 'ccp as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','status','maintype_icon','maintype_id');
           // if(isset($obj['filter_bychannel']) && $obj['filter_bychannel']!='' && $obj['filter_bychannel']!=0)
           //   $data_uncovered_outlet_list->whereIn('maintype_id',[$obj['filter_bychannel']]);
           // if(isset($obj['filter_bypotential']) && $obj['filter_bypotential']!=''  && $obj['filter_bypotential']!=0)
           //   $data_uncovered_outlet_list->whereIn('Estimtd_potntl',[$obj['filter_bypotential']]);
           

           //  $data_uncovered_outlet_list = $data_uncovered_outlet_list->get();

        }
        else if($type==10)
        {
            

           //  $data_outlet_list =  DB::table('uncovered_outlets')            
           // ->select('fld580 as refid'  , 'fld1054', 'name as channel', 'name as subchannel', 'name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image')
           //  ->get();
               $data_outlet_list =  DB::table('uncovered_outlets')->whereIn('salesman_id',[$userid])         
           ->select('refid'  , 'rtlr_id', 'main_type as channel','main_type_id',  'subtype as subchannel', 'ccp as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','status','maintype_icon','maintype_id')
            ->get();
        }
      
       else if($type ==8)
       {
          $data_outlet_list =  DB::table('ref_nungambakkam')  
           ->whereIn('loc15',[1300105,1300106]) 
           ->select('fld580 as refid'  , 'fld1054', 'type as channel', 'subtype as subchannel', 'loc16', 'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image')
            ->get();
       }
        else if($type==11)
       {

             $user = auth()->user();
             $userid=$user->id;
             // $data_outlet_list =  DB::table('ref_24sep2021')      
             // // ->where([['status_1','=','A'],['status','=','N']])     
             //  ->select('refid'  ,  'type as channel', 'subtype as subchannel', 'loc16', 'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status')
             //  ->get();
        if($user->client_id==1)
        {
          if(isset($obj['filter_beat'])  && count($obj['filter_beat']) > 0)
              $filter_beat=$obj['filter_beat'];
            else
              $filter_beat=[];
           // $data_outlet_list =  DB::table('ref_08oct2021')   
           //  ->where([['user_id','=',$userid]]) 
           //   ->whereIn('beat_id',$filter_beat)  
           //   //  ->where([['status_1','=','C'],['status','=','N']])  
           //   // ->whereIn('fld1054',$condionarr['key'][$type])
           //    ->select('refid'  ,  'type as channel', 'sub_type as subchannel',  'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status','potential_status','perimium')
           //    ->get();
               $data_outlet_list =  DB::table('whole')   
            ->where([['user_id','=',$userid]]) 
             ->whereIn('beat_id',$filter_beat)  
             //  ->where([['status_1','=','C'],['status','=','N']])  
             // ->whereIn('fld1054',$condionarr['key'][$type])
              ->select('refid'  ,'type as channel' ,  'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status','stock_confectionary','stock_chocolate')
              ->get();
              //var_dump($data_outlet_list);
              $data_outlet_imagelist =  DB::table('jj_outlet_image')      
               ->where([['user_id','=',$userid],['status','=','A']])               
               ->select('outlet_id', DB::raw('count(*) as total,outlet_image'))
               ->groupBy('outlet_id')
               ->get();

               $imagelist=[];$imagename=[];
               $c=count($data_outlet_imagelist);
              for($i=0;$i<$c;$i++)
              {
                 $imagelist[$data_outlet_imagelist[$i]->outlet_id]=$data_outlet_imagelist[$i]->total;
                 $imagename[$data_outlet_imagelist[$i]->outlet_id]=$data_outlet_imagelist[$i]->outlet_image;
              }

        }
         if($user->client_id==86 || $user->client_id==120)
        {
           
          if(isset($obj['filter_beat'])  && count($obj['filter_beat']) > 0)
              $filter_beat=$obj['filter_beat'];
            else
              $filter_beat=[];
          if(isset($obj['filter_bychannel'])  && count($obj['filter_bychannel']) > 0)
              $filter_bychannel=$obj['filter_bychannel'];
            else
              $filter_bychannel=[];
          if(isset($obj['filter_bypotential'])  && count($obj['filter_bypotential']) > 0)
              $filter_bypotential=$obj['filter_bypotential'];
            else
              $filter_bypotential=[];
           if(isset($obj['filter_bystatus'])  && count($obj['filter_bystatus']) > 0)
              $filter_bystatus=$obj['filter_bystatus'];
            else
              $filter_bystatus=[];
           if(isset($obj['filter_bycluster'])  && count($obj['filter_bycluster']) > 0)
              $filter_bycluster=$obj['filter_bycluster'];
            else
              $filter_bycluster=[];
           // $data_outlet_list =  DB::table('ref_08oct2021')   
           //  ->where([['user_id','=',$userid]]) 
           //   ->whereIn('beat_id',$filter_beat)  
           //   //  ->where([['status_1','=','C'],['status','=','N']])  
           //   // ->whereIn('fld1054',$condionarr['key'][$type])
           //    ->select('refid'  ,  'type as channel', 'sub_type as subchannel',  'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status','potential_status','perimium')
           //    ->get();

               $data_outlet_list =  DB::table('nestle')   
            ->where([['user_id','=',$userid]]) 
             ->whereIn('beat_id',$filter_beat);
             if(count($filter_bypotential) > 0)
              $data_outlet_list=$data_outlet_list->whereIn('fld1923',$filter_bypotential);
             
             if(count($filter_bychannel) > 0)
              $data_outlet_list=$data_outlet_list->whereIn('type',$filter_bychannel);
            if(count($filter_bystatus) > 0)
              $data_outlet_list=$data_outlet_list->whereIn('status',$filter_bystatus);
            if(count($filter_bycluster) > 0)
              $data_outlet_list=$data_outlet_list->whereIn('cluster_id',$filter_bycluster);

             //  ->where([['status_1','=','C'],['status','=','N']])  
             // ->whereIn('fld1054',$condionarr['key'][$type])
              $data_outlet_list=$data_outlet_list->select('refid'  ,'type as channel' ,  'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status','stock_confectionary','stock_chocolate','fld1923 
                as potential_status','potential_status as predict_potential','cluster_id')
              ->where([['status','!=','D']])  
              ->get();
              
              $data_outlet_imagelist =  DB::table('jj_outlet_image')      
               ->where([['user_id','=',$userid],['status','=','A']])               
               ->select('outlet_id', DB::raw('count(*) as total,outlet_image'))
               ->groupBy('outlet_id')
               ->get();

               $imagelist=[];$imagename=[];
               $c=count($data_outlet_imagelist);
              for($i=0;$i<$c;$i++)
              {
                 $imagelist[$data_outlet_imagelist[$i]->outlet_id]=$data_outlet_imagelist[$i]->total;
                 $imagename[$data_outlet_imagelist[$i]->outlet_id]=$data_outlet_imagelist[$i]->outlet_image;
              }

        }
        else if($user->client_id!=2)
          {
            

             $data_outlet_list =  DB::table('alwarpet_uncvrd');     
              // ->where([['user_id','=',$userid]]) 
            
              $data_outlet_list->select('refid','refid as outlet_id'  ,  'type as channel', 'sub_type as subchannel',  'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status')->get();
          }          
          else
          {
            if(isset($obj['filter_beat'])  && count($obj['filter_beat']) > 0)
              $filter_beat=$obj['filter_beat'];
            else
              $filter_beat=[];

            $data_outlet_list =  DB::table('pg_mumbai_uncvrd_3ward')     
              // ->where([['user_id','=',$userid]])  
             // ->whereIn('fld1054',$condionarr['key'][$type])
            
              ->whereIn('beat_id',$filter_beat)

              ->select('refid','refid as outlet_id'  ,  'type as channel', 'sub_type as subchannel',  'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status')->get();
          }
                
        
       }
       
       else
       {


           
        
            $data_outlet_list =  DB::table('ref_08oct2021')   
            ->where([['user_id','=',$userid]])   
             //  ->where([['status_1','=','C'],['status','=','N']])  
             // ->whereIn('fld1054',$condionarr['key'][$type])
              ->select('refid'  ,  'type as channel', 'sub_type as subchannel',  'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image','status')
              ->get();
       }
       
       $cluster=[];
        for($i=0;$i<count($data_outlet_list);$i++)        
        {
             $potential=[0=>'',1=>'Low',2=>'Medium',3=>'High'];
             $perimium=[0=>'',1=>'Yes',2=>'No'];
            
             if(isset($data_outlet_list[$i]->cluster_id))
             {
                if(array_key_exists($data_outlet_list[$i]->cluster_id, $cluster))
               {
                   $cluster[$data_outlet_list[$i]->cluster_id][$potential[$data_outlet_list[$i]->potential_status]]++;
                   $cluster[$data_outlet_list[$i]->cluster_id]['total']++;
               }
               else
               {
                    $cluster[$data_outlet_list[$i]->cluster_id]=[];
                    if($data_outlet_list[$i]->cluster_id==0)
                        $cluster[$data_outlet_list[$i]->cluster_id]['name']='High Potential - Non-Cluster Outlets';
                    else
                         $cluster[$data_outlet_list[$i]->cluster_id]['name']='Cluster '.$data_outlet_list[$i]->cluster_id;
                    $cluster[$data_outlet_list[$i]->cluster_id]['refid']=$data_outlet_list[$i]->cluster_id;
                    $cluster[$data_outlet_list[$i]->cluster_id]['High']=0;
                    $cluster[$data_outlet_list[$i]->cluster_id]['Low']=0;
                    $cluster[$data_outlet_list[$i]->cluster_id]['Medium']=0;
                    $cluster[$data_outlet_list[$i]->cluster_id]['total']=0;
                    $cluster[$data_outlet_list[$i]->cluster_id][$potential[$data_outlet_list[$i]->potential_status]]++;
                    $cluster[$data_outlet_list[$i]->cluster_id]['total']++;


               }
             }
              $temp=[];
             
             $temp['refid']=$data_outlet_list[$i]->refid;
             $temp['outlet_name']=$data_outlet_list[$i]->outlet_name;            
             $temp['channel_name']= (isset($data_outlet_list[$i]->channel)) ? $data_outlet_list[$i]->channel : '';
             $temp['sub_channel_name']= (isset($data_outlet_list[$i]->subchannel)) ? $data_outlet_list[$i]->subchannel : '';
             $temp['address']=ucwords(strtolower($data_outlet_list[$i]->address));
             $temp['status']=(isset($data_outlet_list[$i]->status)) ? $data_outlet_list[$i]->status : '';
             if(isset($data_outlet_list[$i]->potential_status))
             {
              $temp['potential_status']=(isset($data_outlet_list[$i]->potential_status)) ? $data_outlet_list[$i]->potential_status : '';   
              $temp['predict_potential']=(isset($data_outlet_list[$i]->predict_potential)) ? $data_outlet_list[$i]->predict_potential : '';  

               $temp['potential_status_name']=(isset($data_outlet_list[$i]->potential_status)) ? $potential[$data_outlet_list[$i]->potential_status] : '';
               $temp['perimium']=(isset($data_outlet_list[$i]->perimium)) ? $data_outlet_list[$i]->perimium : '';
               $temp['perimium_name']=(isset($data_outlet_list[$i]->perimium)) ? $perimium[$data_outlet_list[$i]->perimium] : '';
            
             }
              if(isset($obj['filter_bycluster'])  && count($obj['filter_bycluster']) >0)
                $temp['cluster']=false;
              else
                $temp['cluster']=true;


               $temp['cluster_id']=(isset($data_outlet_list[$i]->cluster_id)) ? $data_outlet_list[$i]->cluster_id : '';
               if(isset($data_outlet_list[$i]->cluster_id) && $data_outlet_list[$i]->cluster_id==0)
                $temp['cluster_name']='High Potential - Non-Cluster Outlets';
               else
               $temp['cluster_name']=(isset($data_outlet_list[$i]->cluster_id)) ? 'Cluster '.$data_outlet_list[$i]->cluster_id : '';
               $temp['stock_confectionary']=(isset($data_outlet_list[$i]->stock_confectionary)) ? $data_outlet_list[$i]->stock_confectionary : '';
              $temp['stock_chocolate']=(isset($data_outlet_list[$i]->stock_chocolate)) ? $data_outlet_list[$i]->stock_chocolate : '';
              $temp['stock_confectionary_name']=(isset($data_outlet_list[$i]->stock_confectionary)) ? $perimium[$data_outlet_list[$i]->stock_confectionary] : '';
              $temp['stock_chocolate_name']=(isset($data_outlet_list[$i]->stock_chocolate)) ? $perimium[$data_outlet_list[$i]->stock_chocolate] : '';
              $temp['image_count']=(isset($imagelist[$data_outlet_list[$i]->refid])) ? $imagelist[$data_outlet_list[$i]->refid]  : 0;



             


             if($type==9)
             {
                $temp['beat_name']=ucfirst(strtolower($data_outlet_list[$i]->beat_name));
                $temp['icon']= 'images/covered.png';
                $temp['type']='covered';
             }
             else if($type==10)
             {
                $temp['icon']= ($data_outlet_list[$i]->status=='N') ? $data_outlet_list[$i]->maintype_icon : (($data_outlet_list[$i]->status=='A') ? 'images/coveredblue.png' : 'images/nr.png');                
                $temp['type']='uncovered';
             }
             else
             {

             	 if($data_outlet_list[$i]->status=='A')
                      $temp['icon']= 'images/uncovered.png';
               else if($data_outlet_list[$i]->status=='R' || $data_outlet_list[$i]->status=='V')
                      $temp['icon']= 'images/nr.png';
               else
                $temp['icon']=$data_outlet_list[$i]->icon;
             }
             


             $temp['shop_image']=  ($type==9 || $type==10) ? '' : $data_outlet_list[$i]->shop_image; 
              $temp['shop_image']=(isset($imagename[$data_outlet_list[$i]->refid])) ?  $imagename[$data_outlet_list[$i]->refid] : (isset($data_uncovered_outlet_list[$i]->shop_image) ? $data_outlet_list[$i]->shop_image : $temp['shop_image']);          
             
             $temp['lat']=(isset($data_outlet_list[$i]->lat)) ? $data_outlet_list[$i]->lat : ''; 
             $temp['lon']=(isset($data_outlet_list[$i]->lon)) ? $data_outlet_list[$i]->lon : ''; 

             array_push($data['result'],$temp);

        }
       
          if($type==9)
        {
           $data['uncovered_result']=[];$data['channel_list']=[]; $channel_list='';
           for($i=0;$i<count($data_uncovered_outlet_list);$i++)        
          {
               if(!in_array($data_uncovered_outlet_list[$i]->maintype_id,$data['channel_list']) && $data_uncovered_outlet_list[$i]->maintype_id !=0 && $data_uncovered_outlet_list[$i]->maintype_id!='')
               {
                  array_push($data['channel_list'],$data_uncovered_outlet_list[$i]->maintype_id);
                  $channel_list .='<option value="'.$data_uncovered_outlet_list[$i]->maintype_id.'">'.$data_uncovered_outlet_list[$i]->channel.'</option>';
               }
               $temp=[];

               $temp['refid']=$data_uncovered_outlet_list[$i]->refid;
               $temp['outlet_name']=$data_uncovered_outlet_list[$i]->outlet_name;            
               $temp['channel_name']=$data_uncovered_outlet_list[$i]->channel;
               $temp['maintype_id']=$data_uncovered_outlet_list[$i]->maintype_id;
             //  $temp['potential_status']=$data_uncovered_outlet_list[$i]->potential_status;
               $temp['sub_channel_name']=$data_uncovered_outlet_list[$i]->subchannel;
               $temp['address']=ucwords(strtolower($data_uncovered_outlet_list[$i]->address));
               $temp['beat_name']=ucfirst(strtolower($data_uncovered_outlet_list[$i]->beat_name));
                 if(isset($data_uncovered_outlet_list[$i]->feed_potential_status) && $data_uncovered_outlet_list[$i]->feed_potential_status  != "")
               {
                    $temp['potential_status']=$data_uncovered_outlet_list[$i]->feed_potential_status;             
                    $temp['potential_status_name']=$data_uncovered_outlet_list[$i]->feed_potential_status;
               }
               else
               {
                 $temp['potential_status']=(isset($data_uncovered_outlet_list[$i]->potential_status)) ? $data_uncovered_outlet_list[$i]->potential_status : '';             
                  $temp['potential_status_name']=(isset($data_uncovered_outlet_list[$i]->potential_status)) ? $data_uncovered_outlet_list[$i]->potential_status: '';
               }

               
               
              
                 $temp['beat_id']=ucfirst(strtolower($data_uncovered_outlet_list[$i]->beat_id));
                 $temp['image_count']=(isset($imagelist[$data_uncovered_outlet_list[$i]->refid])) ? $imagelist[$data_uncovered_outlet_list[$i]->refid]  : 0;
                $temp['icon']= ($data_uncovered_outlet_list[$i]->status=='N') ? $data_uncovered_outlet_list[$i]->maintype_icon : (($data_uncovered_outlet_list[$i]->status=='A') ? 'images/coveredblue.png' : (($data_uncovered_outlet_list[$i]->status=='E') ? 'images/existing.png' : 'images/nr.png')); 

              
               $temp['lat']=(isset($data_uncovered_outlet_list[$i]->lat)) ? $data_uncovered_outlet_list[$i]->lat : ''; 
               $temp['lon']=(isset($data_uncovered_outlet_list[$i]->lon)) ? $data_uncovered_outlet_list[$i]->lon : ''; 
               $temp['type']='uncovered';
               $temp['status']=$data_uncovered_outlet_list[$i]->status;
               $temp['jj_stock']='';$temp['jj_baby']='';$temp['competition_baby']='';$temp['potential_store']='';$temp['jj_female']='';$temp['jj_otc']='';$temp['competition_female']='';$temp['competition_facewash']='';$temp['competition_stock']='';$temp['potential_baby']='';$temp['potential_female']='';
               $temp['potential_otc']=''; $temp['potential_skincare']='';

              
                $temp['shop_image']=(isset($imagename[$data_uncovered_outlet_list[$i]->refid])) ?  $imagename[$data_uncovered_outlet_list[$i]->refid] : (isset($data_uncovered_outlet_list[$i]->shop_image) ? $data_uncovered_outlet_list[$i]->shop_image : '');

               if(isset($uncovered_info[$data_uncovered_outlet_list[$i]->refid]))
               {
                  $temp['feedback_result']=$uncovered_info[$data_uncovered_outlet_list[$i]->refid];
                  $res=reset($uncovered_info[$data_uncovered_outlet_list[$i]->refid]);
                 
                 
                  $temp['channel_id']=$res->channel_id;
                   $temp['freezer']=$res->freezer;


               }
 
 
               


               array_push($data['uncovered_result'],$temp);

          }
        }


         $data['channel_list']=[];

        $data['legend'] = [];
        if($type==9)
        {
           $result=array_merge($data['result'],$data['uncovered_result']);
           
           $data['mapdata'] =$result;
           $data['channel_list']=$channel_list; 
            $data['feedback_question']=$feedback_question;
           
         
        }
        else
        {
          $data['mapdata'] =$data['result'];

        }

        
        $data['griddata'] = array();

        $head='';
        if($type==9)
        $data['griddata'] = $this->gridcolumn_byoutletlist_bycategory($data['uncovered_result']);
   
      else if($user->client_id==86 || $user->client_id==120)
        if(isset($obj['filter_bycluster'])  && count($obj['filter_bycluster']) >0)
        {
           

           $data['griddata'] = $this->gridcolumn_byoutletlist_bycategory($data['result']);
          
           $head='Cluster '.implode(",",array_unique(array_column($data['result'],'cluster_id')));
        }
        else{

          $data['griddata'] = $this->gridcolumn_bycluster(array_values($cluster));  
        }
                
      else{
        echo 'tertre';die;
        $data['griddata'] = $this->gridcolumn_byoutletlist_bycategory($data['result']);
      }
        
//      var_dump($data['griddata']);die;
        if($user->client_id!=86 && $user->client_id!=120 )
           $head = CommonController::headline($loc12);
        $data['head'] = $head;
 
        return $data;


    }

}

