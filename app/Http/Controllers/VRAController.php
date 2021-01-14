<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\GetTokenController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
// use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use URL;


//use DB;
use App\VRA; //MODEL
use Carbon\Carbon;
use App\Providers\Master;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class VRAController extends Controller
{	
    
	public function getVRA($comp_ba)
	{	
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 300);
		$sql1 = "SELECT WERKS FROM tap_dw.TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
		$data_ba = DB::connection('irs')->select($sql1);
		if($data_ba)
		{
			foreach( $data_ba as $c ){
				$ba = $c->werks;
					$sql = "  
                    SELECT aufnr vehicle_license_number,
						   comp_code cocd,
						   est_code estate_code,
						   start_mileage,
						   end_mileage,
						   mileage_uom,
						   posting_date,
						   actvt_id,
						   kunnr customer,
						   anln1,
						   block_code,
						   mileage vehicle_mileage,
						   wrealize,
						   wrealize_uom
					  FROM tap_dw.zvra_mv
					 WHERE posting_date BETWEEN CASE WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05' THEN TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon') ELSE TRUNC (SYSDATE, 'mon') END
											AND  CASE WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05' THEN TRUNC (LAST_DAY (ADD_MONTHS (SYSDATE, -1))) ELSE SYSDATE END
                                                                    /*TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
                                                                    and LAST_DAY (TRUNC (ADD_MONTHS (SYSDATE,-1)))*/
                        AND comp_code||est_code = '".$ba."'";
				$datax = collect(DB::connection('irs')->select($sql));
                if ( count($datax) > 0) {
                    $response['http_status_code'] = 200;
                    $response['message'] = "VRA";
                    $response['data']['results'] = $datax;
				}
				else{
					
					$response = array(
						"http_status_code" => 404,
						"message" => "Not found",
						"data" => array(
							"results" => array(),
							"error_message" => array()
						)
					);

				}
				
                return response()->json( $response );
			}
				
		}
	}
	
	public function json($comp_ba){
                  
        $param = $comp_ba;
		$Master = new Master;
        // $token = $req->bearerToken();
        $controller = new GetTokenController;
        $token =  $controller->readToken();
        $valid =  $controller->readValid();

        if($valid > date("Y-m-d H:i:s")){
			try{
				$RestAPI = $Master
							->setEndpoint('getVRA/'.$param)
							->setHeaders([
								'Authorization' => 'Bearer '.$token
							])
							->get();
				if($RestAPI['http_status_code'] == 200){
						$results = array('message' => $RestAPI['message'],
										'data' => $RestAPI['data']['results']);
						return $results;
				}else{
					return response()->json('Success', "Terjadi error get data VRA ");
				}
			}
			catch(\Exception $e)
			{
				return response()->json('Error', "Terjadi error get data VRA ".$e);
            }
        }
        else{
            return response()->json('Success', "Token Invalid!");
        }	

    }
	
}
?>