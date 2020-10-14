<?php

namespace App\Http\Controllers;

ini_set('memory_limit', 0);
ini_set('max_execution_time', 0);

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\Sqvi; //MODEL
use Carbon\Carbon;


class SqviController extends Controller
{	
    public function json()
	{                  
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', -1);
		
		$sql = "
				SELECT werks AS plnt,
					   sub_block_code AS block_code,
					   tgl_mill AS \"Created On\",
					   kg_produksi AS quantity,
					   'KG' bun
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
		// dd($datax);
		if($datax)
		{	
			return response()->json( [
							"message" => "sqvi",
							"data" => $datax ] );
		}
		
	}
	
	public function xcron()
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
	public function cron()
	{	
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
		
		$dt = [];
		$dt[] = ['PLNT','BLOCK CODE','CREATED ON','QUANTITY','BUN'];
		if($datax)
		{
			foreach( $datax as $k )
			{
				$ar = (array) $k;
				array_push($ar,'KG');
				$dt[] = $ar;
			}
		}	
		
		$list = $dt;

		$fp = fopen('/etc/csv_share/SQVI.csv', 'w');

		foreach ($list as $fields) {
			fputcsv($fp, $fields);
		}

		fclose($fp);
	}
	public function xxcron($comp_ba)
	{	

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', -1);
        $sql1 = "SELECT WERKS FROM TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
        $data_ba = DB::connection('dev_tap_dw')->select($sql1);
        if($data_ba)
        {
            foreach( $data_ba as $c ){
                $ba = $c->werks;
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
                                            AND werks = '".$ba."'
                                            ";
                
                $datax = DB::connection('irs')->select($sql);
                
                $dt = [];
                $dt[] = ['PLNT','BLOCK CODE','CREATED ON','QUANTITY','BUN'];
                if($datax)
                {
                    foreach( $datax as $k )
                    {
                        $ar = (array) $k;
                        array_push($ar,'KG');
                        $dt[] = $ar;
                    }
                }	
                
                $list = $dt;

                $fp = fopen('/etc/csv_share/SQVI.csv', 'w');

                foreach ($list as $fields) {
                    fputcsv($fp, $fields);
                }

                fclose($fp);
            }
        }
	}
}

?>