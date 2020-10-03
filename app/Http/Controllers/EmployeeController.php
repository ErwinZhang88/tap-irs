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
use App\Employee; //MODEL
use Carbon\Carbon;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class EmployeeController extends Controller
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
		
        //CONTENT DATA		
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300);
        $sql1 = "SELECT WERKS FROM TM_EST WHERE TO_CHAR(END_VALID, 'YYYYMMDD') = '99991231' AND WERKS LIKE '".$comp_ba."%'";
        $data_ba = DB::connection('dev_tap_dw')->select($sql1);
        if($data_ba)
        {
            foreach( $data_ba as $c ){
				echo $c->werks;
				flush();
				ob_flush();
                $ba = $c->werks;
                $sql = " SELECT werks,	afd_code, nik, employee_name, job_code, status, sex,emp_stat,	
                TO_CHAR (entry_date, 'mm/dd/yyyy') entry_date,	TO_CHAR (res_date, 'mm/dd/yyyy') res_date,	TO_CHAR (start_valid, 'mm/dd/yyyy') start_valid, TO_CHAR (end_valid, 'mm/dd/yyyy') end_valid 
                FROM TM_EMPLOYEE_SAP WHERE werks = '".$ba."'";
                
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