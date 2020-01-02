<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\Crop_harvest; //MODEL
use Carbon\Carbon;


class Crop_harvestController extends Controller
{	
    public function index( $comp_ba )
	{
		/* $k = '.';
		$data = array();
		
		$sql = "  SELECT prof_name profile_name,
						   bcc.comp_code company_code,
						   ''''|| to_number(oph) oil_palm_harvesting_number,
						   tph platform,
						   TO_CHAR (posting_date, 'mm/dd/yyyy') dt,
						   nik employee_code,
						   employee_name employee_name,
						   job_code job_code,
						   job_type job_type,
						   bcc.est_code estate,
						   bcc.afd_code afdeling,
						   bcc.block_code block_code,
						   blk.block_name old_block,
						   year_doc year_of_planting,
						   plant_code plant,
						   nik_mandor mandor_code,
						   mandor_name mandor_name,
						   bunch_pay harvested_bunch,
						   bunch_total total_bunch,
						   zbrondolan brondolan_quantity,
						   nweig quantity,
						   cust_numb customer,
						   created_by,
						   TRUNC (created_date) created_on,
						   TO_CHAR (created_date, 'hh:mi:ss AM') time,
						   updated_by changed_by,
						   TRUNC (updated_date) changed_on,
						   TO_CHAR (updated_date, 'hh:mi:ss AM') time_of_change
					  FROM    rizki.zpay_bcc_mv bcc
						   LEFT JOIN
							  tap_dw.tm_block blk
						   ON     bcc.plant_code = blk.werks
							  AND bcc.afd_code = blk.afd_code
							  AND bcc.block_code = blk.block_code
							  AND TRUNC (bcc.posting_date) BETWEEN blk.start_valid AND end_valid
					 WHERE TRUNC (posting_date) BETWEEN CASE
														   WHEN TO_CHAR (SYSDATE, 'dd') BETWEEN '01' AND '05' THEN TRUNC (ADD_MONTHS (SYSDATE, -1), 'mon')
														   ELSE TRUNC (SYSDATE, 'mon')
														END
													AND  TRUNC (SYSDATE)
						   AND bcc.plant_code = '".$comp_ba."'";

		$datax = DB::connection('irs')->select($sql);
		
		if( $datax )
		{
			foreach($datax as $k => $v)
			{
				//echo "<pre>"; print_r($v);
				
				$data[] = array(
					'profile_name' => $v['profile_name'],
					'company_code' => $v['company_code'],
					'oil_palm_harvesting_number' => $v['oil_palm_harvesting_number'],
					'platform' => $v['platform'],
					'date' => $v['dt'],
					'employee_code' => $v['employee_code'],
					'employee_name' => $v['employee_name'],
					'job_code' => $v['job_code'],   
					'job_type' => $v['job_type'],   
					'estate' => $v['estate'],   
					'afdeling' => $v['afdeling'],   
					'block_code' => $v['block_code'],   
					'old_block' => $v['old_block'],   
					'year_of_planting' => $v['year_of_planting'],   
					'plant' => $v['plant'],   
					'mandor_code' => $v['mandor_code'],   
					'mandor_name' => $v['mandor_name'],   
					'harvested_bunch' => $v['harvested_bunch'],   
					'total_bunch' => $v['total_bunch'],   
					'brondolan_quantity' => $v['brondolan_quantity'],
					'customer' => $v['customer'],
					'quantity' => $v['quantity'],
					'created_by' => $v['created_by'],
					'created_on' => $v['created_on'],
					'time' => $v['time'],
					'changed_by' => $v['changed_by'],
					'changed_on' => $v['changed_on'],
					'time_of_change' => $v['time_of_change']
				);
			}
			//die();
		}
		
		if(empty($datax))
		{
		    $result = array(
				'code' => 200,
				'status' => 'failed',
				'message' => 'data not found',
				'data' => $datax
			);
		    
			return Response::json($result,200);
		}
	 
		$result = array(
				'code' => 200,
				'status' => 'success',
				'message' => ''.count($data).' data found',
				'data' => $data
			);
		
		date_default_timezone_set("Asia/Bangkok");
		//$date = date("dmY_Hi");
		$date = date("dmY");
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=CROP_HARVEST_".$comp_ba.".csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		//$l = '';
		if($datax)
		{
			$output = fopen("php://output", "wb");
			$data2 = array("Profile Name","Company Code","Oil Palm Harvesting Number","Platform","Date",
						"Employee Code", "Employee Name", "Job Code", "Job Type", "Estate", "Afdeling",
						"Block Code", "Old Block", "Year of Planting", "Plant", "Mandor Code", "Mandor Name",
						"Harvested Bunch", "Total Bunch", "Brondolan Quantity", "Quantity", "Customer", "Created by", "Created on", "Time", "Changed by", "Changed on", "Time of change");
			
			fputcsv($output, $data2);
			
			foreach( $datax as $k => $v )
			{
				//echo "<pre>"; print_r($v);
				
				$data3 = array(
					'profile_name' => $v['profile_name'],
					'company_code' => $v['company_code'],
					'oil_palm_harvesting_number' => $v['oil_palm_harvesting_number'],
					'platform' => $v['platform'],
					'date' => $v['dt'],
					'employee_code' => $v['employee_code'],
					'employee_name' => $v['employee_name'],
					'job_code' => $v['job_code'],   
					'job_type' => $v['job_type'],   
					'estate' => $v['estate'],   
					'afdeling' => $v['afdeling'],   
					'block_code' => $v['block_code'],   
					'old_block' => $v['old_block'],   
					'year_of_planting' => $v['year_of_planting'],   
					'plant' => $v['plant'],   
					'mandor_code' => $v['mandor_code'],   
					'mandor_name' => $v['mandor_name'],   
					'harvested_bunch' => $v['harvested_bunch'],   
					'total_bunch' => $v['total_bunch'],   
					'brondolan_quantity' => $v['brondolan_quantity'],
					'quantity' => $v['quantity'],
					'customer' => $v['customer'],
					'created_by' => $v['created_by'],
					'created_on' => $v['created_on'],
					'time' => $v['time'],
					'changed_by' => $v['changed_by'],
					'changed_on' => $v['changed_on'],
					'time_of_change' => $v['time_of_change']
				);
				 
				fputcsv($output, $data3);
				
			}
			
			fclose($output);
		}
	
		die; */
		
		//return Response::json($result,200);
	}
	
	public function cron($comp_ba)
	{	
		$pathfile = "/etc/csv_share/Crop_harvest_".$comp_ba.".csv";
		
		
		//HEADER
		//$output = fopen("php://output", "wb");
		$data2 = array("Profile Name","Company Code","Oil Palm Harvesting Number","Platform","Date",
						"Employee Code", "Employee Name", "Job Code", "Job Type", "Estate", "Afdeling",
						"Block Code", "Old Block", "Year of Planting", "Plant", "Mandor Code", "Mandor Name",
						"Harvested Bunch", "Total Bunch", "Brondolan Quantity", "Quantity", "Customer", "Created by", "Created on", "Time", "Changed by", "Changed on", "Time of change", "NIK Gandeng");

		//CONTENT DATA		
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
            FROM staging.zpay_bcc_sap@proddb_link bcc
                 LEFT JOIN tap_dw.tm_block blk
                    ON     bcc.werks = blk.werks
                       AND bcc.divnr = blk.afd_code
                       AND bcc.block = blk.block_code
                       AND TRUNC (TO_DATE (bcc.budat, 'yyyymmdd')) BETWEEN blk.start_valid AND end_valid
                 LEFT JOIN staging.zpay_profile@proddb_link prof
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
                 AND prof.plant_code = '".$comp_ba."'";
		
		$datax = DB::connection('irs')->select($sql);
		
		if($datax)
		{   $file = fopen($pathfile,"w");
			fputcsv($file, $data2);
			foreach( $datax as $k //=> $v 
			)
			{
				//echo "<pre>"; print_r($v);
				
				$data3 = array(
					'profile_name' => $k->profile_name,
                    'company_code' => $k->company_code,
                    'oil_palm_harvesting_number' => $k->oil_palm_harvesting_number,
                    'platform' => $k->platform,
                    'date' => $k->dt,
                    'employee_code' => $k->employee_code,
                    'employee_name' => $k->employee_name,
                    'job_code' => $k->job_code,   
                    'job_type' => $k->job_type,   
                    'estate' => $k->estate,   
                    'afdeling' => $k->afdeling,   
                    'block_code' => $k->block_code,   
                    'old_block' => $k->old_block,   
                    'year_of_planting' => $k->year_of_planting,   
                    'plant' => $k->plant,   
                    'mandor_code' => $k->mandor_code,   
                    'mandor_name' => $k->mandor_name,   
                    'harvested_bunch' => $k->harvested_bunch,   
                    'total_bunch' => $k->total_bunch,   
                    'brondolan_quantity' => $k->brondolan_quantity,
                    'quantity' => $k->quantity,
                    'customer' => $k->customer,
                    'created_by' => $k->created_by,
                    'created_on' => $k->created_on,
                    'time' => $k->time,
                    'changed_by' => $k->changed_by,
                    'changed_on' => $k->changed_on,
                    'time_of_change' => $k->time_of_change,
					'nik_gandeng' => $k->nik_gandeng
				);
				 
				fputcsv($file, $data3);
				
			}
			fclose($file);
		}
	}
	
}

?>