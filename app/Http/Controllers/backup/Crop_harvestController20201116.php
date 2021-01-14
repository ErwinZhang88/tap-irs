<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\GetTokenController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use URL;

//use DB;
use App\Crop_harvest; //MODEL
use Carbon\Carbon;
use App\Providers\Master;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class Crop_harvestController extends Controller
{	
	
	public function index($comp_ba)
	{	
		//$spreadsheet = new Spreadsheet();
		//$sheet = $spreadsheet->getActiveSheet();
		//$sheet->setCellValue('A1', 'Hello World !');
        //$filepath = "/etc/csv_share/TEST_".date("Ymd_Gis").".csv";
		//$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		//$writer->save($filepath);
	}
	
	public function cron($comp_ba)
	{	
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Company Code');
		$sheet->setCellValue('B1', 'Oil Palm Harvesting Number');
		$sheet->setCellValue('C1', 'Date');
		$sheet->setCellValue('D1', 'Employee Code');
		$sheet->setCellValue('E1', 'Estate');
		$sheet->setCellValue('F1', 'Afdeling');
		$sheet->setCellValue('G1', 'Block Code');
		$sheet->setCellValue('H1', 'Old Block');
		$sheet->setCellValue('I1', 'Plant');
		$sheet->setCellValue('J1', 'Total Bunch');
		$sheet->setCellValue('K1', 'Brondolan Quantity');
        $sheet->setCellValue('L1', 'NIK Gandeng');
        // Updated: 31-9-2020
		// $sheet->setCellValue('A1', 'Profile Name');
		// $sheet->setCellValue('B1', 'Company Code');
		// $sheet->setCellValue('C1', 'Oil Palm Harvesting Number');
		// $sheet->setCellValue('D1', 'Platform');
		// $sheet->setCellValue('E1', 'Date');
		// $sheet->setCellValue('F1', 'Employee Code');
		// $sheet->setCellValue('G1', 'Employee Name');
		// $sheet->setCellValue('H1', 'Job Code');
		// $sheet->setCellValue('I1', 'Job Type');
		// $sheet->setCellValue('J1', 'Estate');
		// $sheet->setCellValue('K1', 'Afdeling');
		// $sheet->setCellValue('L1', 'Block Code');
		// $sheet->setCellValue('M1', 'Old Block');
		// $sheet->setCellValue('N1', 'Year of Planting');
		// $sheet->setCellValue('O1', 'Plant');
		// $sheet->setCellValue('P1', 'Mandor Code');
		// $sheet->setCellValue('Q1', 'Mandor Name');
		// $sheet->setCellValue('R1', 'Harvested Bunch');
		// $sheet->setCellValue('S1', 'Total Bunch');
		// $sheet->setCellValue('T1', 'Brondolan Quantity');
		// $sheet->setCellValue('U1', 'Quantity');
		// $sheet->setCellValue('V1', 'Customer');
		// $sheet->setCellValue('W1', 'Created by');
		// $sheet->setCellValue('X1', 'Created on');
		// $sheet->setCellValue('Y1', 'Time');
		// $sheet->setCellValue('Z1', 'Changed by');
		// $sheet->setCellValue('AA1', 'Changed on');
		// $sheet->setCellValue('AB1', 'Time of change');
		// $sheet->setCellValue('AC1', 'NIK Gandeng');
		
        //CONTENT DATA		
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300);
        $sql1 = "SELECT WERKS FROM TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
        $data_ba = DB::connection('dev_tap_dw')->select($sql1);
        if($data_ba)
        {
            foreach( $data_ba as $c ){
                $ba = $c->werks;
                $sql = "  
                    SELECT bcc.prfnr profile_name,
                        bcc.bukrs company_code,
                        '''' || oph oil_palm_harvesting_number,
                        tph platform,
                        TO_CHAR (TO_DATE (bcc.budat, 'yyyymmdd'), 'mm/dd/yyyy') dt,
                        empnr employee_code,
                        ename employee_name,
                        bcc.jbcde job_code,
                        bcc.jbtyp job_type,
                        bcc.estnr estate,
                        bcc.divnr afdeling,
                        bcc.block block_code,
                        blk.block_name old_block,
                        bcc.yplanp year_of_planting,
                        bcc.werks plant,
                        bcc.empnr_m mandor_code,
                        bcc.ename_m mandor_name,
                        bunch_pay harvested_bunch,
                        bunch_total total_bunch,
                        zbrondolan brondolan_quantity,
                        nweig quantity,
                        kunnr customer,
                        ernam created_by,
                        CASE
                            WHEN bcc.erdat != '00000000' AND bcc.aedat IS NOT NULL
                            THEN
                            TO_CHAR (TRUNC (TO_DATE (bcc.erdat, 'yyyymmdd')), 'mm/dd/yyyy')
                        END
                            created_on,
                        CASE
                            WHEN bcc.erdat != '00000000' AND bcc.aedat IS NOT NULL THEN TO_CHAR (TO_DATE (bcc.erdat, 'yyyymmdd'), 'hh:mi:ss AM')
                        END
                            time,
                        aenam changed_by,
                        CASE
                            WHEN bcc.aedat != '00000000' AND bcc.aedat IS NOT NULL
                            THEN
                            TO_CHAR (TRUNC (TO_DATE (bcc.aedat, 'yyyymmdd')), 'mm/dd/yyyy')
                        END
                            changed_on,
                        CASE
                            WHEN bcc.aedat != '00000000' AND bcc.aedat IS NOT NULL THEN TO_CHAR (TO_DATE (bcc.aedat, 'yyyymmdd'), 'hh:mi:ss AM')
                        END
                            time_of_change,
                        bcc.paire nik_gandeng
                    FROM staging.zpay_bcc_sap@stg_link bcc
                        LEFT JOIN tap_dw.tm_block blk
                            ON     bcc.werks = blk.werks
                            AND bcc.divnr = blk.afd_code
                            AND bcc.block = blk.block_code
                            AND TRUNC (TO_DATE (bcc.budat, 'yyyymmdd')) BETWEEN blk.start_valid AND end_valid
                        LEFT JOIN staging.zpay_profile@stg_link prof
                            ON prof.prof_name = bcc.prfnr
                WHERE TRUNC (TO_DATE (bcc.budat, 'yyyymmdd')) BETWEEN CASE
                                                                        WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05'
                                                                        THEN
                                                                            TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
                                                                        ELSE
                                                                            TRUNC (SYSDATE, 'mon')
                                                                    END
                                                                    AND  CASE                                                                      WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05'
                                                                    THEN LAST_DAY (TRUNC (ADD_MONTHS (SYSDATE,-1)))
                                                                        ELSE
                                                                        sysdate
                                                                    END
                                                                    /*TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
                                                                    and LAST_DAY (TRUNC (ADD_MONTHS (SYSDATE,-1)))*/
                        AND prof.plant_code = '".$ba."'";
                
                $datax = DB::connection('irs')->select($sql);
                
                if($datax)
                {   
                    $i = 2;
                    foreach( $datax as $k )
                    {
                        //echo "<pre>"; print_r($v);
                        $sheet->setCellValue('A'.$i,$k-> company_code);
                        $sheet->setCellValue('B'.$i,$k-> oil_palm_harvesting_number);
                        $sheet->setCellValue('C'.$i,$k-> dt);
                        $sheet->setCellValue('D'.$i,$k-> employee_code);
                        $sheet->setCellValue('E'.$i,$k-> estate);
                        $sheet->setCellValue('F'.$i,$k-> afdeling);
                        $sheet->setCellValue('G'.$i,$k-> block_code);
                        $sheet->setCellValue('H'.$i,$k-> old_block);
                        $sheet->setCellValue('I'.$i,$k-> plant);
                        $sheet->setCellValue('J'.$i,$k-> total_bunch);
                        $sheet->setCellValue('K'.$i,$k-> brondolan_quantity);
                        $sheet->setCellValue('L'.$i,$k-> nik_gandeng);
                        //Updated : 30-9-2020
                        // $sheet->setCellValue('A'.$i,$k->profile_name);
                        // $sheet->setCellValue('B'.$i,$k-> company_code);
                        // $sheet->setCellValue('C'.$i,$k-> oil_palm_harvesting_number);
                        // $sheet->setCellValue('D'.$i,$k-> platform);
                        // $sheet->setCellValue('E'.$i,$k-> dt);
                        // $sheet->setCellValue('F'.$i,$k-> employee_code);
                        // $sheet->setCellValue('G'.$i,$k-> employee_name);
                        // $sheet->setCellValue('H'.$i,$k-> job_code);
                        // $sheet->setCellValue('I'.$i,$k-> job_type);
                        // $sheet->setCellValue('J'.$i,$k-> estate);
                        // $sheet->setCellValue('K'.$i,$k-> afdeling);
                        // $sheet->setCellValue('L'.$i,$k-> block_code);
                        // $sheet->setCellValue('M'.$i,$k-> old_block);
                        // $sheet->setCellValue('N'.$i,$k-> year_of_planting);
                        // $sheet->setCellValue('O'.$i,$k-> plant);
                        // $sheet->setCellValue('P'.$i,$k-> mandor_code);
                        // $sheet->setCellValue('Q'.$i,$k-> mandor_name);
                        // $sheet->setCellValue('R'.$i,$k-> harvested_bunch);
                        // $sheet->setCellValue('S'.$i,$k-> total_bunch);
                        // $sheet->setCellValue('T'.$i,$k-> brondolan_quantity);
                        // $sheet->setCellValue('U'.$i,$k-> quantity);
                        // $sheet->setCellValue('V'.$i,$k-> customer);
                        // $sheet->setCellValue('W'.$i,$k-> created_by);
                        // $sheet->setCellValue('X'.$i,$k-> created_on);
                        // $sheet->setCellValue('Y'.$i,$k-> time);
                        // $sheet->setCellValue('Z'.$i,$k-> changed_by);
                        // $sheet->setCellValue('AA'.$i,$k-> changed_on);
                        // $sheet->setCellValue('AB'.$i,$k-> time_of_change);
                        // $sheet->setCellValue('AC'.$i,$k-> nik_gandeng);

                        $i++;
                    }
                }
                
                $pathfile = "/etc/csv_share/Crop_harvest_".$ba.".csv";
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $writer->save($pathfile);
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
                            ->setEndpoint('getCropharvest/'.$param)
                            ->setHeaders([
                                'Authorization' => 'Bearer '.$token
                            ])
                            ->get();
                if($RestAPI['http_status_code'] == 200){
                    $results = array('message' => $RestAPI['message'],
										'data' => $RestAPI['data']['results']);
                    return $results;
				}
				else{
                    return response()->json('Success', "Terjadi error get data Crop Harvest {$RestAPI['http_status_code']} ");
                }
            }
			catch(\Exception $e)
			{
				return response()->json('Error', "Terjadi error get data Crop Harvest ".$e);
            }
        }
        else{
            return response()->json('Success', "Token Invalid!");
        }	

    }

    public function getCropharvest($comp_ba){
        ini_set('memory_limit', '-1');
        // ini_set('max_execution_time', 300);
        ini_set('max_execution_time', -1);
        $sql1 = "SELECT WERKS FROM TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
        $data_ba = DB::connection('dev_tap_dw')->select($sql1);
        if($data_ba)
        {
            foreach( $data_ba as $c ){
                $ba = $c->werks;
                $sql = "  
                    SELECT bcc.bukrs company_code,
                         '''' || oph oil_palm_harvesting_number,
                         TO_CHAR (TO_DATE (bcc.budat, 'yyyymmdd'), 'mm/dd/yyyy') dt,
                         empnr employee_code,
                         bcc.estnr estate,
                         bcc.divnr afdeling,
                         bcc.block block_code,
                         blk.block_name old_block,
                         bcc.werks plant,
                         bunch_total total_bunch,
                         zbrondolan brondolan_quantity,
                         bcc.paire nik_gandeng
                    FROM staging.zpay_bcc_sap@stg_link bcc
                        LEFT JOIN tap_dw.tm_block blk
                            ON     bcc.werks = blk.werks
                            AND bcc.divnr = blk.afd_code
                            AND bcc.block = blk.block_code
                            AND TRUNC (TO_DATE (bcc.budat, 'yyyymmdd')) BETWEEN blk.start_valid AND end_valid
                        LEFT JOIN staging.zpay_profile@stg_link prof
                            ON prof.prof_name = bcc.prfnr
                WHERE TRUNC (TO_DATE (bcc.budat, 'yyyymmdd')) BETWEEN CASE
                                                                        WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05'
                                                                        THEN
                                                                            TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
                                                                        ELSE
                                                                            TRUNC (SYSDATE, 'mon')
                                                                    END
                                                                    AND  CASE                                                                      WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05'
                                                                    THEN LAST_DAY (TRUNC (ADD_MONTHS (SYSDATE,-1)))
                                                                        ELSE
                                                                        sysdate
                                                                    END
                                                                    /*TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
                                                                    and LAST_DAY (TRUNC (ADD_MONTHS (SYSDATE,-1)))*/
                        AND prof.plant_code = '".$ba."'";
                
                $datax = DB::connection('irs')->select($sql);
                    $response['http_status_code'] = 200;
                    $response['message'] = "Crop Harvest";
                    $response['data']['results'] = $datax;
                    //return response()->json( $response );

                if ( $datax != '' ) {
                    $response['http_status_code'] = 200;
                    $response['message'] = "Crop Harvest";
                    $response['data']['results'] = $datax;
                }else{
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
	
}

?>