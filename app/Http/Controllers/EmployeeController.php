<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\Employee; //MODEL
use Carbon\Carbon;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class EmployeeController extends Controller
{	
	
	public function json( $comp_ba )
	{                  
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', -1);
		$sql1 = "SELECT WERKS FROM tap_dw.TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
		$data_ba = DB::connection('irs')->select($sql1);
		if($data_ba)
		{
			foreach( $data_ba as $c ){
				$ba = $c->werks;
				$sql = " SELECT hd.werks site,
							   hd.afd_code afdeling,
							   hd.nik,
							   hd.employee_name nama,
							   hd.job_code \"Kode Job\",
							   hd.status,
							   hd.sex,
							   hd.emp_stat \"Marital Status\",
							   TO_CHAR (hd.entry_date, 'mm/dd/yyyy') \"Tgl Masuk\",
							   TO_CHAR (hd.res_date, 'mm/dd/yyyy') \"Tgl Resign\",
							   TO_CHAR (hd.start_valid, 'mm/dd/yyyy') \"Start Valid\",
							   TO_CHAR (hd.end_valid, 'mm/dd/yyyy') \"End Valid\"
						  FROM tap_dw.tm_employee_sap hd LEFT JOIN (  SELECT nik, MAX (end_valid) max_end_valid
																 FROM tap_dw.tm_employee_sap
															 GROUP BY nik) maks
								  ON hd.nik = maks.nik
						 WHERE hd.end_valid = maks.max_end_valid 
						 AND hd.werks = '".$ba."'";
				$datax = DB::connection('irs')->select($sql);
				// dd($datax);
				if($datax)
				{	
					return response()->json( [
									"message" => "employee",
									"data" => $datax ] );
				}
			}
		}
	}
	
	public function cron($comp_ba)
	{	
        //CONTENT DATA		
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', -1);
        $sql1 = "SELECT WERKS FROM tap_dw.TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
        $data_ba = DB::connection('irs')->select($sql1);
        if($data_ba)
        {
            foreach( $data_ba as $c ){
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->setCellValue('A1', 'Site');
				$sheet->setCellValue('B1', 'Afdeling');
				$sheet->setCellValue('C1', 'NIK');
				$sheet->setCellValue('D1', 'Nama');
				$sheet->setCellValue('E1', 'Kode Job');
				$sheet->setCellValue('F1', 'Status');
				$sheet->setCellValue('G1', 'Sex');
				$sheet->setCellValue('H1', 'Marital Status');
				$sheet->setCellValue('I1', 'Tgl Masuk');
				$sheet->setCellValue('J1', 'Tgl Resign');
				$sheet->setCellValue('K1', 'Start Valid');
				$sheet->setCellValue('L1', 'End Valid');
				$ba = $c->werks;
                echo $ba.'<br>';
				flush();
				ob_flush();
				$sql = " SELECT hd.werks,	hd.afd_code, hd.nik, hd.employee_name, hd.job_code, hd.status, hd.sex,emp_stat,	
                TO_CHAR (entry_date, 'mm/dd/yyyy') entry_date,	TO_CHAR (res_date, 'mm/dd/yyyy') res_date,	TO_CHAR (start_valid, 'mm/dd/yyyy') start_valid, TO_CHAR (end_valid, 'mm/dd/yyyy') end_valid 
                FROM tap_dw.tm_employee_sap hd LEFT JOIN (  SELECT nik, MAX (end_valid) max_end_valid
																 FROM tap_dw.tm_employee_sap
															 GROUP BY nik) maks
								  ON hd.nik = maks.nik WHERE  hd.end_valid = maks.max_end_valid and hd.werks = '".$ba."'";
                
                $datax = DB::connection('dev_tap_dw')->select($sql);
                
                if($datax)
                {   
                    $i = 2;
                    foreach( $datax as $k )
                    {
                        //echo "<pre>"; print_r($v);
                        $sheet->setCellValue('A'.$i,$k-> werks);
                        $sheet->setCellValue('B'.$i,$k-> afd_code);
                        $sheet->setCellValue('C'.$i,$k-> nik);
                        $sheet->setCellValue('D'.$i,$k-> employee_name);
                        $sheet->setCellValue('E'.$i,$k-> job_code);
                        $sheet->setCellValue('F'.$i,$k-> status);
                        $sheet->setCellValue('G'.$i,$k-> sex);
                        $sheet->setCellValue('H'.$i,$k-> emp_stat);
                        $sheet->setCellValue('I'.$i,$k-> entry_date);
                        $sheet->setCellValue('J'.$i,$k-> res_date);
                        $sheet->setCellValue('K'.$i,$k-> start_valid);
                        $sheet->setCellValue('L'.$i,$k-> end_valid);
                        $i++;
                    }
                }
                
                $pathfile = "/etc/csv_share/Employee_".$ba.".csv";
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $writer->save($pathfile);
            }
        }
		
	}
	
}

?>