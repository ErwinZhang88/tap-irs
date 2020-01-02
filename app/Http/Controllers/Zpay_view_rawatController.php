<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\Zpay_view_rawat; //MODEL
use Carbon\Carbon;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class Zpay_view_rawatController extends Controller
{	
    public function index( $comp_ba )
	{
		
	}
	
	public function cron($comp_ba)
	{	
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1','Profile Name');
		$sheet->setCellValue('B1','Company Code');
		$sheet->setCellValue('C1','Date');
		$sheet->setCellValue('D1','Employee Code');
		$sheet->setCellValue('E1','Employee Name');
		$sheet->setCellValue('F1','Job Code');
		$sheet->setCellValue('G1','Job Type');
		$sheet->setCellValue('H1','Estate');
		$sheet->setCellValue('I1','Afdeling');
		$sheet->setCellValue('J1','Phase');
		$sheet->setCellValue('K1','Item');
		$sheet->setCellValue('L1','Total Item');
		$sheet->setCellValue('M1','Activity');
		$sheet->setCellValue('N1','Activity Name');
		$sheet->setCellValue('O1','Block Code');
		$sheet->setCellValue('P1','Old Block');
		$sheet->setCellValue('Q1','Mandor Code');
		$sheet->setCellValue('R1','Helper1 Code');
		$sheet->setCellValue('S1','Helper1 Name');
		$sheet->setCellValue('T1','Helper2 Code');
		$sheet->setCellValue('U1','Helper2 Name');
		$sheet->setCellValue('V1','Helper3 Code');
		$sheet->setCellValue('W1','Helper3 Name');
		$sheet->setCellValue('X1','PER');
		$sheet->setCellValue('Y1','Customer');
		$sheet->setCellValue('Z1','Activity UoM');
		$sheet->setCellValue('AA1','Vehicle License Number');
		$sheet->setCellValue('AB1','Start Point of Vehicle Mileage');
		$sheet->setCellValue('AC1','End Point of Vehicle Mileage');
		$sheet->setCellValue('AD1','Vehicle Mileage');
		$sheet->setCellValue('AE1','UOM');
		$sheet->setCellValue('AF1','Asset');
		$sheet->setCellValue('AG1','Asset main no. text');
		$sheet->setCellValue('AH1','Cost Center');
		$sheet->setCellValue('AI1','Document Currency');
		$sheet->setCellValue('AJ1','Premi Rate');
		$sheet->setCellValue('AK1','Over Time');
		$sheet->setCellValue('AL1','Material 1');
		$sheet->setCellValue('AM1','Material Description 1');
		$sheet->setCellValue('AN1','Quantity Material 1');
		$sheet->setCellValue('AO1','UOM Material 1');
		$sheet->setCellValue('AP1','Material 2');
		$sheet->setCellValue('AQ1','Material Description 2');
		$sheet->setCellValue('AR1','Quantity Material 2');
		$sheet->setCellValue('AS1','UOM Material 2');
		$sheet->setCellValue('AT1','Material 3');
		$sheet->setCellValue('AU1','Material Description 3');
		$sheet->setCellValue('AV1','Quantity Material 3');
		$sheet->setCellValue('AW1','UOM Material 3');
		$sheet->setCellValue('AX1','Plant');
		$sheet->setCellValue('AY1','Reason');
		$sheet->setCellValue('AZ1','Created by');
		$sheet->setCellValue('BA1','Created on');
		$sheet->setCellValue('BB1','Time');
		$sheet->setCellValue('BC1','Changed by');
		$sheet->setCellValue('BD1','Changed on');
		$sheet->setCellValue('BE1','Time of change');

		//CONTENT DATA		
		$sql = "SELECT prfnr profile_name,
					   bukrs company_code,
					   TO_CHAR (TO_DATE (budat, 'yyyymmdd'), 'mm/dd/yyyy') dt,
					   empnr employee_code,
					   zemp.employee_name employee_name,
					   jbcde job_code,
					   jbtyp job_type,
					   estnr estate,
					   divnr afdeling,
					   zwork.phase phase,
					   NULL item,
					   NULL total_item,
					   actvt_no activity,
					   actvt_name activity_name,
					   CASE WHEN TRIM (anln1) IS NOT NULL THEN SUBSTR (anln1, -3, 3) ELSE block END block,
					   blk.block_name old_block,
					   empnr_m mandor_code,
					   empnr_k1 helper1_code,
					   ename_k1 helper1_name,
					   empnr_k2 helper2_code,
					   ename_k2 helper2_name,
					   empnr_k3 helper3_code,
					   ename_k3 helper3_name,
					   per per,
					   zwork.kunnr customer,
					   amein activity_uom,
					   aufnr vehicle_license_no,
					   start_mileage start_mileage,
					   end_mileage end_mileage,
					   mileage mileage,
					   mileage_uom uom,
					   anln1 asset,
					   anlhtxt asset_maint_no,
					   kostl cost_center,
					   zwork.waerk doc_currency,
					   prate premi_rate,
					   otime over_time,
					   matnr_1 mat_1,
					   maktx_1 mat_desc1,
					   mat_per_1 mat_qty1,
					   mat_meins_1 mat_uom1,
					   matnr_2 mat_2,
					   maktx_2 mat_desc2,
					   mat_per_2 mat_qty2,
					   mat_meins_2 mat_uom2,
					   matnr_3 mat_3,
					   maktx_3 mat_desc3,
					   mat_per_3 mat_qty3,
					   mat_meins_3 mat_uom3,
					   zwork.werks plant,
					   reasn reason,
					   ernam created_by,
					   TO_CHAR (TO_DATE (erdat, 'YYYYMMDD'), 'mm/dd/yyyy') created_on,
					   TO_DATE (erzet, 'HH24MISS') time,
					   aenam changed_by,
					   CASE WHEN aedat != '00000000' THEN TO_CHAR (TO_DATE (aedat, 'YYYYMMDD'), 'mm/dd/yyyy') END changed_on,
					   CASE WHEN aedat != '00000000' THEN TO_CHAR (TO_DATE (aezet, 'HH24MISS'), 'HH24MISS') END time_of_changed
				  FROM rizki.zpay_work_sap_mv zwork
					   LEFT JOIN staging.zpay_employee@proddb_link zemp
						  ON zwork.empnr = zemp.nik AND TO_DATE (budat, 'yyyymmdd') BETWEEN start_valid AND end_valid
					   LEFT JOIN tap_dw.tm_block blk
						  ON     zwork.werks = blk.werks
							 AND CASE WHEN TRIM (zwork.anln1) IS NOT NULL THEN SUBSTR (zwork.anln1, -3, 3) ELSE zwork.block END = blk.block_code
							 AND TO_DATE (budat, 'yyyymmdd') BETWEEN blk.start_valid AND blk.end_valid
					   LEFT JOIN staging.zpay_profile@proddb_link prof
						  ON zwork.prfnr = prof.prof_name
				 WHERE TRUNC (TO_DATE (budat, 'yyyymmdd')) BETWEEN CASE
																	  WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05'
																	  THEN
																		 TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
																	  ELSE
																		 TRUNC (SYSDATE, 'mon')
																   END
																  AND CASE
																	WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05'
																	THEN
																	   TRUNC (last_day(ADD_MONTHS (SYSDATE, -1)))
																	ELSE
																	   sysdate
																 END
																/*TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon') and  
																TRUNC (last_day(ADD_MONTHS (SYSDATE, -1)))*/
					   AND prof.plant_code = '".$comp_ba."'";
		
		$datax = DB::connection('irs')->select($sql);
		
		if($datax)
		{	
			$i = 2;
			foreach( $datax as $k )
			{
				$sheet->setCellValue('A'.$i, $k->profile_name);
				$sheet->setCellValue('B'.$i, $k->company_code);
				$sheet->setCellValue('C'.$i, $k->dt);
				$sheet->setCellValue('D'.$i, $k->employee_code);
				$sheet->setCellValue('E'.$i, $k->employee_name);
				$sheet->setCellValue('F'.$i, $k->job_code);
				$sheet->setCellValue('G'.$i, $k->job_type);
				$sheet->setCellValue('H'.$i, $k->estate);
				$sheet->setCellValue('I'.$i, $k->afdeling);
				$sheet->setCellValue('J'.$i, $k->phase);
				$sheet->setCellValue('K'.$i, $k->item);
				$sheet->setCellValue('L'.$i, $k->total_item);
				$sheet->setCellValue('M'.$i, $k->activity);
				$sheet->setCellValue('N'.$i, $k->activity_name);
				$sheet->setCellValue('O'.$i, $k->block);
				$sheet->setCellValue('P'.$i, $k->old_block);
				$sheet->setCellValue('Q'.$i, $k->mandor_code);
				$sheet->setCellValue('R'.$i, $k->helper1_code);
				$sheet->setCellValue('S'.$i, $k->helper1_name);
				$sheet->setCellValue('T'.$i, $k->helper2_code);
				$sheet->setCellValue('U'.$i, $k->helper2_name);
				$sheet->setCellValue('V'.$i, $k->helper3_code);
				$sheet->setCellValue('W'.$i, $k->helper3_name);
				$sheet->setCellValue('X'.$i, $k->per);
				$sheet->setCellValue('Y'.$i, $k->customer);
				$sheet->setCellValue('Z'.$i, $k->activity_uom);
				$sheet->setCellValue('AA'.$i, $k->vehicle_license_no);
				$sheet->setCellValue('AB'.$i, $k->start_mileage);
				$sheet->setCellValue('AC'.$i, $k->end_mileage);
				$sheet->setCellValue('AD'.$i, $k->mileage);
				$sheet->setCellValue('AE'.$i, $k->uom);
				$sheet->setCellValue('AF'.$i, $k->asset);
				$sheet->setCellValue('AG'.$i, $k->asset_maint_no);
				$sheet->setCellValue('AH'.$i, $k->cost_center);
				$sheet->setCellValue('AI'.$i, $k->doc_currency);
				$sheet->setCellValue('AJ'.$i, $k->premi_rate);
				$sheet->setCellValue('AK'.$i, $k->over_time);
				$sheet->setCellValue('AL'.$i, $k->mat_1);
				$sheet->setCellValue('AM'.$i, $k->mat_desc1);
				$sheet->setCellValue('AN'.$i, $k->mat_qty1);
				$sheet->setCellValue('AO'.$i, $k->mat_uom1);
				$sheet->setCellValue('AP'.$i, $k->mat_2);
				$sheet->setCellValue('AQ'.$i, $k->mat_desc2);
				$sheet->setCellValue('AR'.$i, $k->mat_qty2);
				$sheet->setCellValue('AS'.$i, $k->mat_uom2);
				$sheet->setCellValue('AT'.$i, $k->mat_3);
				$sheet->setCellValue('AU'.$i, $k->mat_desc3);
				$sheet->setCellValue('AV'.$i, $k->mat_qty3);
				$sheet->setCellValue('AW'.$i, $k->mat_uom3);
				$sheet->setCellValue('AX'.$i, $k->plant);
				$sheet->setCellValue('AY'.$i, $k->reason);
				$sheet->setCellValue('AZ'.$i, $k->created_by);
				$sheet->setCellValue('BA'.$i, $k->created_on);
				$sheet->setCellValue('BB'.$i, $k->time);
				$sheet->setCellValue('BC'.$i, $k->changed_by);
				$sheet->setCellValue('BD'.$i, $k->changed_on);
				$sheet->setCellValue('BE'.$i, $k->time_of_changed);
				$i++;
			}
		}
		$pathfile = "/etc/csv_share/Zpay_view_rawat_".$comp_ba.".csv";
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		$writer->save($pathfile);
	}
	
}

?>