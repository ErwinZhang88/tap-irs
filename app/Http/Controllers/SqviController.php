<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\Sqvi; //MODEL
use Carbon\Carbon;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class SqviController extends Controller
{	
    public function index()
	{
	/* 	$k = 'KG';
		$data = array();
		
		$sql = "
						SELECT werks AS plnt,
							   sub_block_code AS block_code,
							   tgl_mill AS created_on,
							   kg_produksi AS quantity
						  FROM tap_dw.tr_hv_production_daily
						 WHERE tgl_mill BETWEEN CASE
												   WHEN TO_CHAR (sysdate, 'dd') BETWEEN '01' AND '05' THEN TRUNC (ADD_MONTHS (sysdate, -1), 'MON')
												   ELSE TRUNC (sysdate, 'mon')
												END
											AND  TRUNC (sysdate) ";

		$datax = DB::connection('irs')->select($sql);
		
		if( $datax )
		{
			foreach($datax as $k => $v)
			{
				//echo "<pre>"; print_r($v);
				
				$data[] = array(
					'Plnt' => $v['plnt'],
					'Block Code' => $v['block_code'],
					'Created on' => $v['created_on'],
					'quantity' => $v['quantity'],
					'Bun' => 'KG'
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
		$date = date("dmY_Hi");
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=SQVI.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		//$l = '';
		if($datax)
		{
			$output = fopen("php://output", "wb");
			$data2 = array("PLNT","BLOCK CODE","CREATED ON","QUANTITY","BUN");
			
			fputcsv($output, $data2);
			
			foreach( $datax as $k => $v )
			{
				//echo "<pre>"; print_r($v);
				
				$data3 = array(
					'plnt' => $v['plnt'],
					'block_code' => $v['block_code'],
					'created_on' => $v['created_on'],
					'quantity' => $v['quantity'],
					'bun' => 'KG'
				);
				 
				fputcsv($output, $data3);
				
			}
			
			fclose($output);
		}
	
		die;
		
		//return Response::json($result,200); */
	}
	
	public function cron()
	{	
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();		
		$sheet->setCellValue('A1', 'PLNT');
		$sheet->setCellValue('B1', 'BLOCK CODE');
		$sheet->setCellValue('C1', 'CREATED ON');
		$sheet->setCellValue('D1', 'QUANTITY');
		$sheet->setCellValue('E1', 'BUN');
		
		 $sql = "
			SELECT werks AS plnt,
				   sub_block_code AS block_code,
				   tgl_mill AS created_on,
				   kg_produksi AS quantity
			  FROM tap_dw.tr_hv_production_daily
			 WHERE tgl_mill BETWEEN CASE
									   WHEN TO_CHAR (sysdate, 'dd') BETWEEN '01' AND '05' THEN TRUNC (ADD_MONTHS (sysdate, -1), 'MON')
									   ELSE TRUNC (sysdate, 'mon')
									END
									AND CASE
									   WHEN TO_CHAR (sysdate, 'dd') BETWEEN '01' AND '05' 
									   THEN TRUNC (LAST_DAY (ADD_MONTHS (sysdate, -1)))
									   ELSE TRUNC (sysdate)
									END
									/*TRUNC (ADD_MONTHS (sysdate, -1), 'MON') and TRUNC (LAST_DAY (ADD_MONTHS (sysdate, -1)))*/
									";
		
		$datax = DB::connection('irs')->select($sql);
			
		if($datax)
		{
			$i = 2;
			foreach( $datax as $k )
			{
				$sheet->setCellValue('A'.$i, $k->plnt);
				$sheet->setCellValue('B'.$i, $k->block_code);
				$sheet->setCellValue('C'.$i, $k->created_on);
				$sheet->setCellValue('D'.$i, $k->quantity);
				$sheet->setCellValue('E'.$i, 'KG');
				$i++;
			}
		}	
		$pathfile = "/etc/csv_share/SQVI.csv";
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		$writer->save($pathfile); 
	}
	
}

?>