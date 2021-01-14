<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException; 
use App\Providers\Master;

//use DB;
use Carbon\Carbon;


class GetTokenController extends Controller
{	
	
	public function index(Request $req) {
		$ps = md5($req->password);
		$password = '08bb6ba96a764b3bf4b8493aebf2dd62';
		if($ps == $password){
			try{
				$cekvalid = File::get(storage_path('app/access_token_irs.txt'));

				$data =  explode("\n", $cekvalid) ;
				$data_token = $data[0];
				$data_valid = $data[1];
				// dd( strtotime($data_valid) > date("Y-m-d H:i:s"));

				if( $data_valid > date("Y-m-d H:i:s") ){
                    return response()->json( [
                        "message" => "Token is still valid"
                    ] );
				}else{
					//replace;
					Storage::delete('access_token_irs.txt');
					$token = Str::random(60);
					$valid_until = Carbon::now()->addDay(1);
					$_token = $token."\n".$valid_until;
					Storage::put('access_token_irs.txt', $_token);
					return response()->json( [
                        "message" => "Successfully generating a token"
                    ] );
				}
			}
			catch (FileNotFoundException  $exception)
			{
				$token = Str::random(60);
				$valid_until = Carbon::now()->addDay(1);
				$_token = array ( 'token' => $token, 'valid_until'=>$valid_until);
				Storage::put('access_token_irs.txt', $_token);
				return response()->json( [
                    "message" => "Successfully generating a token"
                ] );
			}
		}
		else{
			return response()->json( [
                "message" => "Unaouthorized!!"
            ] );
		}
	}

	public function readToken() {
		try{

			$cekvalid = File::get(storage_path('app/access_token_irs.txt'));

			$data =  explode("\n", $cekvalid) ;
			$data_token = $data[0];
			$data_valid = $data[1];
			// dd( strtotime($data_valid) > date("Y-m-d H:i:s"));

			if( $data_valid > date("Y-m-d H:i:s") ){
				return $data_token;
			}else{
				//replace;
				Storage::delete('access_token_irs.txt');
				$token = Str::random(60);
				$valid_until = Carbon::now()->addDay(1);
				$_token = $token."\n".$valid_until;
				Storage::put('access_token_irs.txt', $_token);
				return $token;
			}
		}
		catch (FileNotFoundException  $exception)
		{
			return "File Not Found";
		}
	}
    
    public function readValid() {
		try{

			$cekvalid = File::get(storage_path('app/access_token_irs.txt'));

            $data =  explode("\n", $cekvalid) ;
			$data_valid = $data[1];
				return $data_valid;
		}
		catch (FileNotFoundException  $exception)
		{
			return "File Not Found";
		}
	}
}

?>