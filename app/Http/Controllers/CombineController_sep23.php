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
        $data_outlet_list =  DB::table('outlet_list')
            ->join('users', 'users.id', '=', 'outlet_list.user_id')          
            ->join('mdlz_main_channel_master', 'mdlz_main_channel_master.refid', '=', 'outlet_list.channel_name')
            ->join('mdlz_channel_master', 'mdlz_channel_master.refid', '=', 'outlet_list.sub_channel_name')
        //    ->where('outlet_list.user_id',$so_id)
            ->select('outlet_list.*', 'users.firstname', 'users.lastname','mdlz_main_channel_master.name as channel','mdlz_channel_master.name as subchannel','mdlz_channel_master.icon as icon')
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

        $head = CommonController::headline($loc12);
        $data['head'] = $head;

        return $data;

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

                 $info_text='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$val['colony_id']]['location_name'].' <small>Rank   '.$rank.'/'.$total_count.'</small></h3> <span class="'.strtolower($type).'" style="background-color:'.$final_color.'">'.$type.'</span></div><ul class="list-group list-group-flush"><li class="list-group-item">Total Retailers (Nos.) <span>'. $val['total_shop'] .'</span></li><li class="list-group-item">Mondelez Retailers (Nos.) <span>'. $val['covered_shop'] .'</span></li><li class="list-group-item" style="background-color:'.$final_color.'">Uncovered Retailers (Nos.) <span>'. $val['uncovered_shop'].'</span></li></ul><div class="adtnl-details"><ul class="list-group list-group-flush"><li class="list-group-item">'.$val['asm_name'].' <span>ASM</span></li><li class="list-group-item">'.$val['so_name'].' <span> SO</span></li><li class="list-group-item" >'.$val['pc_name'].' <span>PC</span></li><li class="list-group-item" >'.$val['distributor'].' <span>Distrbtr</span></li></ul></div></div></div>';

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

                 $info_text='<div class="tooltip-data"><div class="card"><div class="card-header"><h3>'.$maparray[$val['colony_id']]['location_name'].' <small>Rank   '.$rank.'/'.$total_count.'</small></h3> <span class="'.strtolower($type).'" style="background-color:'.$final_color.'">'.$type.'</span></div><ul class="list-group list-group-flush"><li class="list-group-item">Total Retailers (Nos.) <span>'. $val['total_shop'] .'</span></li><li class="list-group-item">Mondelez Retailers (Nos.) <span>'. $val['covered_shop'] .'</span></li><li class="list-group-item" style="background-color:'.$final_color.'">Uncovered Retailers (Nos.) <span>'. $val['uncovered_shop'].'</span></li></ul><div class="adtnl-details"><ul class="list-group list-group-flush"><li class="list-group-item">'.$val['asm_name'].' <span>ASM</span></li><li class="list-group-item">'.$val['so_name'].' <span> SO</span></li><li class="list-group-item" >'.$val['pc_name'].' <span>PC</span></li></li><li class="list-group-item" >'.$val['distributor'].' <span>Distrbtr</span></li></ul></div></div></div>';

               

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
    public function gridcolumn_byoutletlist_bycategory($data)
    {
       $column = array();
         $value = array();



         array_push($column, array(
            'title' => '#', 'className' => 'text-right'
        ));
           array_push($column, array(
            'title' => ucwords('Outlet Name')
        ));
           array_push($column, array(
            'title' => ucwords('Channel')
        ));
          
           array_push($column, array(
            'title' => ucwords('Sub-channel')
        ));
          
            array_push($column, array(
            'title' => ucwords('Address')
        ));
             array_push($column, array(
            'title' => ucwords('Latitude')
        ));
              array_push($column, array(
            'title' => ucwords('Longitude')
        ));
              if(isset($data[0]['status']))
                array_push($column, array(
            'title' => ucwords('Status')
        ));

             

        for ($i = 0;$i < count($data);$i++)
        {

          if(isset($data[0]['status']))
             $temp = array(
                ($i + 1) ,   
                
                 '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
                $data[$i]['channel_name'],
                $data[$i]['sub_channel_name'],
                $data[$i]['address'],
                 $data[$i]['lat'],
                $data[$i]['lon'],
                (($data[$i]['status']=='R') ? 'Not Relevent' : (($data[$i]['status']=='A') ? 'Activated' : 'New'))

                 

            );
           else
            $temp = array(
                ($i + 1) ,   
                '<a href="#" style="text-decoration:underline" onClick="highlight('.$data[$i]['refid'].','.$data[$i]['lat'].','.$data[$i]['lon'].')">'.$data[$i]['outlet_name'].'</a>',
                $data[$i]['channel_name'],
                $data[$i]['sub_channel_name'],
                $data[$i]['address'],
                 $data[$i]['lat'],
                $data[$i]['lon'],
                 

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
               // $citydata[$data[$i]['loc12']],
                '<a href="#" id="' . $data[$i]['location_id'] . '" style="text-decoration:underline" onClick="showbound(this)">' . $data[$i]['location_name'] . '</a>',

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
    public function show_outletlist_bycategory($maparray,$type,$main_location,$sub_location,$so_id)
    {
        $data = [];
        $data['result'] = array();       
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


        if($type==9)
        {

            $data_outlet_list =  DB::table('covered_outlets')->whereIn('salesman_id',[$userid])            
           ->select('id as refid'  , 'secondary_channel_type as channel', 'secondary_channel_type as subchannel', 'name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon')
            ->get();
             $data_uncovered_outlet_list =  DB::table('uncovered_outlets')->whereIn('salesman_id',[$userid])             
           ->select('refid'  , 'rtlr_id', 'subtype as channel', 'SubType as subchannel', 'ccp as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','status')
            ->get();

        }
        else if($type==10)
        {
            

           //  $data_outlet_list =  DB::table('uncovered_outlets')            
           // ->select('fld580 as refid'  , 'fld1054', 'name as channel', 'name as subchannel', 'name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image')
           //  ->get();
               $data_outlet_list =  DB::table('uncovered_outlets')->whereIn('salesman_id',[$userid])         
           ->select('refid'  , 'rtlr_id', 'subtype as channel', 'subtype as subchannel', 'ccp as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','status')
            ->get();
        }
      
       else if($type ==8)
       {
          $data_outlet_list =  DB::table('ref_nungambakkam')  
           ->whereIn('loc15',[1300105,1300106]) 
           ->select('fld580 as refid'  , 'fld1054', 'type as channel', 'subtype as subchannel', 'loc16', 'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image')
            ->get();
       }
       else
       {

            $data_outlet_list =  DB::table('ref_02sep2021')      
              ->where([['loc16','=','7951']])
              ->whereIn('fld1054',$condionarr['key'][$type])
              ->select('fld580 as refid'  ,  'type as channel', 'subtype as subchannel', 'loc16', 'fld1054', 'CCP_Name as outlet_name', 'address as address', 'latitude as lat', 'longitude as lon','icon','shop_image')
              ->get();
        
       }
       
       
       

        for($i=0;$i<count($data_outlet_list);$i++)        
        {
             $temp=[];
             $temp['refid']=$data_outlet_list[$i]->refid;
             $temp['outlet_name']=$data_outlet_list[$i]->outlet_name;            
             $temp['channel_name']=$data_outlet_list[$i]->channel;
             $temp['sub_channel_name']=$data_outlet_list[$i]->subchannel;
             $temp['address']=ucwords(strtolower($data_outlet_list[$i]->address));
             $temp['status']=(isset($data_outlet_list[$i]->status)) ? $data_outlet_list[$i]->status : '';


             if($type==9)
             {
                $temp['icon']= 'images/covered.png';
                $temp['type']='covered';
             }
             else if($type==10)
             {
                $temp['icon']= ($data_outlet_list[$i]->status=='N') ? 'images/uncovered.png' : (($data_outlet_list[$i]->status=='A') ? 'images/covered.png' : 'images/nr.png');
                $temp['type']='uncovered';
             }
             else
             {
               $temp['icon']= ($type==9) ? 'images/covered.png' : (($type==10) ? 'images/uncovered.png' : $data_outlet_list[$i]->icon);
             }
             


             $temp['shop_image']=  ($type==9 || $type==10) ? '' : $data_outlet_list[$i]->shop_image;           
             
             $temp['lat']=(isset($data_outlet_list[$i]->lat)) ? $data_outlet_list[$i]->lat : ''; 
             $temp['lon']=(isset($data_outlet_list[$i]->lon)) ? $data_outlet_list[$i]->lon : ''; 

             array_push($data['result'],$temp);

        }
        if($type==9)
        {
           $data['uncovered_result']=[];
           for($i=0;$i<count($data_uncovered_outlet_list);$i++)        
          {
               $temp=[];
               $temp['refid']=$data_uncovered_outlet_list[$i]->refid;
               $temp['outlet_name']=$data_uncovered_outlet_list[$i]->outlet_name;            
               $temp['channel_name']=$data_uncovered_outlet_list[$i]->channel;
               $temp['sub_channel_name']=$data_uncovered_outlet_list[$i]->subchannel;
               $temp['address']=ucwords(strtolower($data_uncovered_outlet_list[$i]->address));
               $temp['icon']= ($data_uncovered_outlet_list[$i]->status=='N') ? 'images/uncovered.png' : (($data_uncovered_outlet_list[$i]->status=='A') ? 'images/covered.png' : 'images/nr.png');
               $temp['lat']=(isset($data_uncovered_outlet_list[$i]->lat)) ? $data_uncovered_outlet_list[$i]->lat : ''; 
               $temp['lon']=(isset($data_uncovered_outlet_list[$i]->lon)) ? $data_uncovered_outlet_list[$i]->lon : ''; 
               $temp['type']='uncovered';
               $temp['status']=$data_uncovered_outlet_list[$i]->status;


               array_push($data['uncovered_result'],$temp);

          }
        }


         

        $data['legend'] = [];
        if($type==9)
        {
           $result=array_merge($data['result'],$data['uncovered_result']);
           $data['mapdata'] =$result;
        }
        else
        {
          $data['mapdata'] =$data['result'];
        }
        
        $data['griddata'] = array();

        $data['griddata'] = $this->gridcolumn_byoutletlist_bycategory($data['result']);

        $head = CommonController::headline($loc12);
        $data['head'] = $head;

        return $data;


    }

}

