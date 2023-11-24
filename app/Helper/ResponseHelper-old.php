<?php
namespace App\Helper;
use DB;
use DateTime;
use DateInterval;
use App\Helper\Constant;
use Illuminate\Support\Str;
use App\Models\FoodtruckMenu;
use App\Models\CuisineCategory;
use App\Models\FoodtruckProfile;
use App\Models\FoodtruckMenuItem;
use App\Models\FoodtruckOperation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use App\Models\FoodtruckMenuItemAddOns;
use Illuminate\Support\Facades\Validator;
// DB::enableQueryLog();
 //dd(DB::getQueryLog());

class ResponseHelper{

    public function api_response($data=[],$code='',$type='',$message='',$headers=[],$getString=false)
    {
        
        $data_array['type'] = $type;
        $data_array['data']    = $data;
        $data_array['message'] = $message;
        
        $data_array['code']    = 200;//$code;

        if($getString===true)
        {
            return json_encode($data_array);
        }
         response()->json($data_array, $code, $headers)->send();
        die();
    }

    public function api_validate_request($input=[], $rule=[], $msg=[])
    {
        $validator = Validator::make($input, $rule, $msg);

        if ($validator->fails())
        {
          return $this->api_response($validator->errors(),422,'error');
        }

        return true;

    }

    public function encrypt_password($password){
            $output = false;
            $encrypt_method = "AES-256-CBC";
            $secret_key = Constant::ENCRYPTION_KEY;
            $secret_iv 	= Constant::CIPHER;
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            $output = openssl_encrypt($password, $encrypt_method, $key, 0, $iv);
            $output = $this->base64url_encode($output);
            return $output;
        }


    public function decrypt_password($encryption){
            $output = false;
            $encrypt_method = "AES-256-CBC";
            $secret_key = Constant::ENCRYPTION_KEY;
            $secret_iv 	= Constant::CIPHER;
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            $string1 = $this->base64url_decode($encryption);
            $output = openssl_decrypt($string1, $encrypt_method, $key, 0, $iv);
            return $output;
    }

    public function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    public function base64url_decode($data) {
      return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    public function dateFormat($data) {
        return date('Y-m-d', strtotime($data));
    }

    public function generateRandomCode()
    {
        $chars = "012345678911121314151617181920";
        $res = "";
        for ($i = 0; $i < 6; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $res;
    }
    function randomString($length = 10) {
        // Set the chars
        $chars='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
        // Count the total chars
        $totalChars = strlen($chars);
    
        // Get the total repeat
        $totalRepeat = ceil($length/$totalChars);
    
        // Repeat the string
        $repeatString = str_repeat($chars, $totalRepeat);
    
        // Shuffle the string result
        $shuffleString = str_shuffle($repeatString);
    
        // get the result random string
        return substr($shuffleString,1,$length);
    }

    function subscriptionExpDays($d){
        $days = '+'.$d.' days';
        $now = new DateTime(date('Y-m-d H:i:s'));
        $duration = (new DateTime($days))->diff($now);
        return $duration->format('%a');
    }
    function subscriptionExpDate($d){
        $days = '+'.$d.' days';
        $expDate = date('Y-m-d H:i:s',strtotime($days,strtotime(date('Y-m-d H:i:s'))));
         return $expDate;
    }


    public function UserProfileImage($file) {
        try {
            
            if(isset($file) && !empty($file)){
                $ext = strtolower($file->getClientOriginalExtension());
                $check = in_array($ext, ['jpeg','jpg','png']);
                if (!$check) {
                    return $this->api_response(null, 200,'error', " Images must be png, jpeg or jpg!");
                }
                if(filesize($file) > 2000000){
                    return $this->api_response(null, 200,'error', " Images exceeds 2MB, Keep it less than 2MB.");
                }
                $image_name = Str::random(10);
                $image_full_name = $image_name.'.'.$ext;
                $original_dir_path = public_path('storage/images/profile/original/');
                if (!File::exists($original_dir_path)) {
                        File::makeDirectory($original_dir_path, 0755, true);
                }
                /* Original Image uploaded here */
                $file->move($original_dir_path,$image_full_name);

                /* Thumbnail Image */
                $thumbnail_dir_path = public_path('storage/images/profile/thumbnail/');
                if (!File::exists($thumbnail_dir_path)) {
                    File::makeDirectory($thumbnail_dir_path, 0755, true);
                }
                $thumbnailPath = $thumbnail_dir_path . $image_full_name;
                $thumbnailImage = Image::make($original_dir_path.$image_full_name);
                $thumbnailImage->resize(150, 150);
                $thumbnailImage->save($thumbnailPath);
                return URL::to('/').'/storage/images/profile/original/'.$image_full_name;
            }else{
                return $this->api_response(null, 200,'error', "Image is not selected.");
            }


        } catch (\Exception $exception) {
                $error = $exception->getMessage();
                dd($error);
        }
    }

    public function deleteUserProfilePreviousImage($file){
        
        $baseurl = URL::to('/').'/';
        $filename = str_replace($baseurl, '', $file);
        if(File::exists(public_path($filename))){
            unlink(public_path($filename));
            $newurl = str_replace('original', 'thumbnail', $filename);
            unlink(public_path($newurl));
        }else{
            return $this->api_response(null, 200,'error', "Unable to process request");
        }

    }

    public function FoodieMenuItemImage($file) {
        try {
            if($file){
            $ext = strtolower($file->getClientOriginalExtension());
            $check = in_array($ext, ['jpeg','jpg','png']);
            if (!$check) {
                return $this->api_response(null, 200,'error', " Images must be png, jpeg or jpg!");
            }
            if(filesize($file) > 2000000){
                return $this->api_response(null, 200,'error', " Images exceeds 2MB, Keep it less than 2MB.");
            }
            $image_name = Str::random(10);
            $image_full_name = $image_name.'.'.$ext;
            $original_dir_path = public_path('storage/images/menuitem/original/');
            
            if (!File::exists($original_dir_path)) {
                    File::makeDirectory($original_dir_path, 0755, true);
            }
            /* Original Image uploaded here */
            $file->move($original_dir_path,$image_full_name);

            /* Thumbnail Image */
            $thumbnail_dir_path = public_path('storage/images/menuitem/thumbnail/');
            if (!File::exists($thumbnail_dir_path)) {
                File::makeDirectory($thumbnail_dir_path, 0755, true);
            }
            $thumbnailPath = $thumbnail_dir_path . $image_full_name;
            $thumbnailImage = Image::make($original_dir_path.$image_full_name);
            $thumbnailImage->resize(150, 150);
            $thumbnailImage->save($thumbnailPath);
            return URL::to('/').'/storage/images/menuitem/original/'.$image_full_name;
        }else{
            return $this->api_response(null, 200,'error', "Image is not selected.");
        }

        } catch (\Exception $exception) {
                $error = $exception->getMessage();
                dd($error);
            }
     }

     public function deleteFoodieMenuItemPreviousImage($file){
        $baseurl = URL::to('/').'/';
        $filename = str_replace($baseurl, '', $file);
        if(File::exists(public_path($filename))){
            unlink(public_path($filename));
            $newurl = str_replace('original', 'thumbnail', $filename);
            unlink(public_path($newurl));
        }else{
            return $this->api_response(null, 200,'error', "Unable to process request");
        }
    }


    public function FoodtruckProfileImage($file) {
        try {
            if($file){
                $ext = strtolower($file->getClientOriginalExtension());
                $check = in_array($ext, ['jpeg','jpg','png']);
                if (!$check) {
                    return $this->api_response(null, 200,'error', " Images must be png, jpeg or jpg!");
                }
                if(filesize($file) > 2000000){
                    return $this->api_response(null, 200,'error', " Images exceeds 2MB, Keep it less than 2MB.");
                }
                $image_name = Str::random(10);
                $image_full_name = $image_name.'.'.$ext;
                $original_dir_path = public_path('storage/images/foodtruckprofile/original/');
                if (!File::exists($original_dir_path)) {
                        File::makeDirectory($original_dir_path, 0755, true);
                }
                /* Original Image uploaded here */
                $file->move($original_dir_path,$image_full_name);

                /* Thumbnail Image */
                $thumbnail_dir_path = public_path('storage/images/foodtruckprofile/thumbnail/');
                if (!File::exists($thumbnail_dir_path)) {
                    File::makeDirectory($thumbnail_dir_path, 0755, true);
                }
                $thumbnailPath = $thumbnail_dir_path . $image_full_name;
                $thumbnailImage = Image::make($original_dir_path.$image_full_name);
                $thumbnailImage->resize(150, 150);
                $thumbnailImage->save($thumbnailPath);
                return  URL::to('/').'/storage/images/foodtruckprofile/original/'.$image_full_name;
            }else{
                return $this->api_response(null, 200,'error', "Image is not selected.");
            }


        } catch (\Exception $exception) {
                $error = $exception->getMessage();
                dd($error);
            }
    }


    public function deleteFoodtruckProfilePreviousImage($file){
        $baseurl = URL::to('/').'/';
        $filename = str_replace($baseurl, '', $file);
        if(File::exists(public_path($filename))){
            unlink(public_path($filename));
            $newurl = str_replace('original', 'thumbnail', $filename);
            unlink(public_path($newurl));
        }else{
            return $this->api_response(null, 200,'error', "Unable to process request");
        }

    }


    public function formatOrderAmoutForPaymentGateway($ordAmt){
       $convertcentInt = (int)($ordAmt*100);
       return $convertcentInt;
     }


     function getLatLong($address)
     {
         // $localIP = getHostByName(getHostName());
       if (!empty($address)) {
         $formattedAddr = str_replace(array('  ', '+'), '', preg_replace('/[^a-zA-Z0-9 s]/', '', trim($address)));
         $geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($formattedAddr) . '&key=AIzaSyDNah9An50_m2HdarBSvAIQWs4dyLSKLTs');
         $output = json_decode($geocodeFromAddr);
         $data['latitude'] = $output->results[0]->geometry->location->lat;
         $data['longitude'] = $output->results[0]->geometry->location->lng;
   
         if (!empty($data)) {
           return $data;
         } else {
           return false;
         }
       }
     }

    /*
        function getLatLong($address)
    {
        
        if (!empty($address)) {
            $geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=AIzaSyDNah9An50_m2HdarBSvAIQWs4dyLSKLTs');
            $output = json_decode($geocodeFromAddr);
            if ($output->status === "OK" && isset($output->results[0]->geometry->location)) {
                $data['latitude'] = $output->results[0]->geometry->location->lat;
                $data['longitude'] = $output->results[0]->geometry->location->lng;
                return $data;
            } else {
                return false;
            }
        }
    }
    
    */


     function getDistancBetweenTwoCoordinates($id,$passed_latitude,$passed_longitude,$foodtruck_latitude,$foodtruck_longitude){
        
            $lat1 = $foodtruck_latitude;
            $lon1 = $foodtruck_longitude;
            $lat2 = $passed_latitude;
            $lon2 = $passed_longitude;
        
            //$earth_radius = 6371; // In meter
            $earth_radius = 3959; // In miles
            // $earth_radius = 3958.756; // In KiloMeter
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
            $c = 2 * asin(sqrt($a));
            $distance = $earth_radius * $c;
        
            // Save the id and distance to an array
            $distances[] = [
                'id' => $id,
                'distance' => $distance
            ];
            return $distances;
     }


     function FilterBYtimeHelper(){
        $openEarlyFoodtruckIds = [];
        $openNowFoodtruckIds = [];
        $openLateFoodtruckIds = [];
        $filteredTruckByTime = FoodtruckOperation::all();
        foreach ($filteredTruckByTime as $value) {
        //dd(date('G:i A', strtotime($value->mon_open_time)), strtotime($value->mon_open_time), strtotime('08:00 pm'));
        
            // SUNDAY
            if(strtolower(date('l')) == 'sunday')
            {  
                if(strtotime($value->sun_open_time) > strtotime('05:00 am') && strtotime($value->sun_open_time) <= strtotime('10:00 am')){
                    $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                }
                if(strtotime($value->sun_open_time) > strtotime('10:00 am') && strtotime($value->sun_open_time) <= strtotime('08:00 pm')){
                    $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                    
                }
                if(strtotime($value->sun_open_time) > strtotime('08:00 pm')){
                    $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                }
            }
            // MONDAY
            if(strtolower(date('l')) == 'monday')
            { 
                
                    if(strtotime($value->mon_open_time) > strtotime('05:00 am') && strtotime($value->mon_open_time) <= strtotime('10:00 am')){
                    $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->mon_open_time) > strtotime('10:00 am') && strtotime($value->mon_open_time) <= strtotime('8:00 pm')){
                        $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                        // dd($openNowFoodtruckIds);
                        
                    }
                    if(strtotime($value->mon_open_time) > strtotime('08:00 pm')){
                        $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
            }
            

            // TUESDAY
            if(strtolower(date('l')) == 'tuesday')
            {
                
                if(strtotime($value->tue_open_time) > strtotime('05:00 am') && strtotime($value->tue_open_time) <= strtotime('10:00 am')){
                    $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->tue_open_time) > strtotime('10:00 am') && strtotime($value->tue_open_time) <= strtotime('08:00 pm')){
                        $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->tue_open_time) > strtotime('08:00 pm')){
                        $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
            }
            

            // WEDNESDAY
            if(strtolower(date('l')) == 'wednesday')
            { 
                    if(strtotime($value->wed_open_time) > strtotime('05:00 am') && strtotime($value->wed_open_time) <= strtotime('10:00 am')){
                    $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->wed_open_time) > strtotime('10:00 am') && strtotime($value->wed_open_time) <= strtotime('08:00 pm')){
                        $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->wed_open_time) > strtotime('08:00 pm')){
                        $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
            }
            

                // THURSDAY
                if(strtolower(date('l')) == 'thursday')
                {
                
                if(strtotime($value->thu_open_time) > strtotime('05:00 am') && strtotime($value->thu_open_time) <= strtotime('10:00 am')){
                    $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->thu_open_time) > strtotime('10:00 am') && strtotime($value->thu_open_time) <= strtotime('08:00 pm')){
                        $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->thu_open_time) > strtotime('08:00 pm')){
                        $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                }
                

                // FRIDAY
                if(strtolower(date('l')) == 'friday')
                {
                    if(strtotime($value->fri_open_time) > strtotime('05:00 am') && strtotime($value->fri_open_time) <= strtotime('10:00 am')){
                    $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->fri_open_time) > strtotime('10:00 am') && strtotime($value->fri_open_time) <= strtotime('08:00 pm')){
                        $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                    if(strtotime($value->fri_open_time) > strtotime('08:00 pm')){
                        $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                    }
                }
                

                // Saturday
                if(strtolower(date('l')) == 'saturday')
                {
                    
                    if(strtotime($value->sat_open_time) > strtotime('05:00 am') && strtotime($value->sat_open_time) <= strtotime('10:00 am')){
                        $openEarlyFoodtruckIds[] = $value->foodtruck_profile_id;
                        }
                        if(strtotime($value->sat_open_time) > strtotime('10:00 am') && strtotime($value->sat_open_time) <= strtotime('08:00 pm')){
                            $openNowFoodtruckIds[] = $value->foodtruck_profile_id;
                        }
                        if(strtotime($value->sat_open_time) > strtotime('08:00 pm')){
                            $openLateFoodtruckIds[] = $value->foodtruck_profile_id;
                            
                        }
                }
                
        }

        return ['openEarlyFoodtruckIds'=>$openEarlyFoodtruckIds,'openNowFoodtruckIds'=>$openNowFoodtruckIds,'openLateFoodtruckIds'=>$openLateFoodtruckIds];
        
     }


     function FilterByDistanceHelper($data){
            $lat = $data['latitude'];//19.076090;
            $lon = $data['longitude'];//72.877426;
            $distance = $data['distance'];
            $foodtrucks = [];
            $foodtrucksIds = [];
            $filteredTruckByDistance = FoodtruckOperation::all();
            foreach ($filteredTruckByDistance as $value) {
                $reData[] = $this->getDistancBetweenTwoCoordinates($value->foodtruck_profile_id,$lat,$lon,$value->latitude,$value->longitude);
                       
            }
           foreach ($reData as $key => $value) {
                if(!empty($distance)){
                 $distance1 = $value[0]['distance'];
                 if($distance1 <= $distance){
                    $foodtrucksIds[]=$value[0]['id'];
                 }
                }else{
                    $foodtrucksIds[]=$value[0]['id'];;
                }
                
           }
           return $foodtrucksIds;
     }
     function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }
     function getcusineByFoodtruckId($fid){
       try {
        $data = FoodtruckProfile::select('category')->where('id',$fid)->first();
        if(!is_null($data)){
            if($this->isJson($data['category'])){
                $filteredTruckCategory = json_decode($data['category'], true);
                $cusineList = CuisineCategory::whereIn('id',$filteredTruckCategory)->get();
            }else{
                $cusineList = CuisineCategory::where('id',$data['category'])->get();
            }
            
            return $cusineList;
        }
       
        } catch (\Exception $e) {
            return $this->api_response(null, 422,"error", $e->getMessage());
        }
     }
     function getAddonByMenuitemId($fid){
       try {
        $fdata = FoodtruckProfile::where('user_id',auth()->user()->id)->first();
        $data = FoodtruckMenuItem::select('addons')->where('id',$fid)->first();
        if(!is_null($data)){
            if($this->isJson($data['addons'])){
                $MenuitemsAddon = json_decode($data['addons'], true);
                $addonList = FoodtruckMenuItemAddOns::whereIn('id',$MenuitemsAddon)->get();
            }else{
                $addonList = FoodtruckMenuItemAddOns::where('id',$data['addons'])->where('foodtruck_id',$fdata['id'])->get();
              }
            return $addonList;
        }       
        } catch (\Exception $e) {
            return $this->api_response(null, 422,"error", $e->getMessage());
        }
     }

     function getMenuitemMenuId($menuid){
        try {
            $menuitemscount = FoodtruckMenuItem::whereJsonContains('menu_category_id',["".$menuid.""])->get();
            if(!is_null($menuitemscount) && !empty($menuitemscount)){
                return $menuitemscount;
            }
         } catch (\Exception $e) {
             return $this->api_response(null, 422,"error", $e->getMessage());
         }
      }

      function getFoodtruckId(){
        $FoodtruckProfile = FoodtruckProfile::where('user_id', auth()->user()->id)->first();
        return $FoodtruckProfile->id;
      }

      function getMenuitemsByFoodtruckId($fid){
        try {
            $nullFoodtruckMenu = FoodtruckMenu::where('foodtruck_id',Null)->get();
            $menus = FoodtruckProfile::with(['menus'])->where('id', $fid)->get();
            $menuitemIds = [];
            foreach ($menus as $key => $value) {
               foreach ($value->menus as $k => $v) {
                if(auth()->user()->type == 'foodtruckowner'){
                    $menuitemIds[] = FoodtruckMenuItem::where('created_by',auth()->user()->id)->whereJsonContains('menu_category_id',["".$v->id.""])->get();
                }else{
                    $menuitemIds[] = FoodtruckMenuItem::where('foodtruck_id',$fid)->whereJsonContains('menu_category_id',["".$v->id.""])->get();
                }
               }
            }
            foreach ($nullFoodtruckMenu as $key => $value) {
                if(auth()->user()->type == 'foodtruckowner'){
                    $menuitemIds[] = FoodtruckMenuItem::where('created_by',auth()->user()->id)->whereJsonContains('menu_category_id',["".$value->id.""])->get();
                }else{
                    $menuitemIds[] = FoodtruckMenuItem::where('foodtruck_id',$fid)->whereJsonContains('menu_category_id',["".$value->id.""])->get();
                }
                }
            $ids = [];
            foreach ($menuitemIds as $key => $item) {
                foreach ($item as $k => $v) {
                    $ids[] = $v->id;
                }
               
            }
            $menuitemdetails = FoodtruckMenuItem::whereIn('id',$ids)->get();
            if(!is_null($menuitemdetails) && !empty($menuitemdetails)){
                return $menuitemdetails;
            }
         } catch (\Exception $e) {
             return $this->api_response(null, 422,"error", $e->getMessage());
         }
      }


      function getMenuitemAveragePriceByMenuId($menuid){
        try {
            $priceArray=[];
            $menuitemscount=[];
            foreach ($menuid as $key => $value) {
                $menuitemscount[$key] = FoodtruckMenuItem::whereJsonContains('menu_category_id',["".$value.""])->count();
                $data = FoodtruckMenuItem::whereJsonContains('menu_category_id',["".$value.""])->get();
                if(!is_null($data)){
                    foreach ($data as $k => $v) {
                        $priceArray[$k] = $v->price;
                     }
                 } 
            }
         $total1 = array_sum($priceArray);
         $menuitemscount1 = array_sum($menuitemscount);
         $averagePricePerPerson1 = $total1/$menuitemscount1;
         $averagePricePerPerson = number_format((float)$averagePricePerPerson1, 2, '.', '');
         return $averagePricePerPerson;    
         } catch (\Exception $e) {
             return $this->api_response(null, 422,"error", $e->getMessage());
         }
      }
      function getAllMenuitemsByMenuId($menuid){
        try {
            $menuitemsId=[];
            foreach ($menuid as $key => $value) {
                $data = FoodtruckMenuItem::whereJsonContains('menu_category_id',["".$value.""])->get();
                if(!is_null($data)){
                    foreach ($data as $k => $v) {
                        $menuitemsId[] = $v->id;
                     }
                 } 
            }
            $newmenuitemsId = array_unique($menuitemsId);
         $mdata = FoodtruckMenuItem::whereIn('id',$newmenuitemsId)->get();
         return $mdata;    
         } catch (\Exception $e) {
             return $this->api_response(null, 422,"error", $e->getMessage());
         }
      }

     function FilterByTimeStringKeyword($Timedata, $data){
       
        $Ids = [];
        if($Timedata  == 'openearly'){
            $filteredTruck = FoodtruckProfile::whereIn('id',$data['openEarlyFoodtruckIds'])->get();
            if($filteredTruck && $filteredTruck->isNotEmpty()){
                foreach ($filteredTruck as $value) {
                    $Ids[] = $value->id;
                }
                return $Ids;
                }
            }
        if($Timedata  == 'opennow'){
            $filteredTruck = FoodtruckProfile::whereIn('id',$data['openNowFoodtruckIds'])->get();
           
            if($filteredTruck && $filteredTruck->isNotEmpty()){
                foreach ($filteredTruck as $value) {
                    $Ids[] = $value->id;
                 }
                    return $Ids;
                }
        }
        if($Timedata  == 'openlate'){
            $filteredTruck = FoodtruckProfile::whereIn('id',$data['openLateFoodtruckIds'])->get();
            if($filteredTruck && $filteredTruck->isNotEmpty()){
                foreach ($filteredTruck as $value) {
                    $Ids[] = $value->id;
                }
                    return $Ids;
                }
         }
     }




}
