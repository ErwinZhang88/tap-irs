<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\FingerSite; //MODEL
use Carbon\Carbon;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class FingerSiteController extends Controller
{	
    public function index()
	{
		cron();
	}
	
	public function cron()
	{	
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();		
		
		 $sql = "
			    SELECT nik,
					 TO_CHAR (tanggal, 'dd-mon-yyyy') tanggal,
					 TO_CHAR (in_time, 'HH24:mi') in_time,
					 TO_CHAR (out_time, 'HH24:mi') out_time
				FROM (  SELECT nik,
							   absensi.fingercode,
							   TRUNC (absensi.checktime) tanggal,
							   MIN (CASE WHEN absensi.checktype = 'I' THEN absensi.checktime END) in_time,
							   MIN (CASE WHEN absensi.checktype = 'O' THEN absensi.checktime END) out_time
						  FROM fp_staging.data_fp@proddb_link absensi LEFT JOIN fp_staging.master_karyawan@proddb_link emp
								  ON absensi.fingercode = emp.fingercode
						 WHERE TRUNC (checktime) BETWEEN TRUNC (SYSDATE, 'MON') AND SYSDATE
						   AND nik IS NOT NULL
					  GROUP BY nik, absensi.fingercode, TRUNC (absensi.checktime))
			ORDER BY nik, tanggal";
		
		$datax = DB::connection('irs')->select($sql);
		$array = json_decode(json_encode($datax), True);
		
		//UNTUK HEADER SESUAI DENGAN COLUMN NAME 
		//$sheet->fromArray(array_keys($array[0]), NULL, 'A1');
		
		if($datax)
		{	
			//FETCHING DATA DENGAN ARRAY
			$sheet->fromArray($array, NULL, 'A1');
			
			//FETCHING PER ROW AND PER COLUMN
			//Looping datax		
			//$i = 1;
			//foreach( $datax as $k )
			//{
			//	$sheet->setCellValue('A'.$i, $k->nik);
			//	$sheet->setCellValue('B'.$i, $k->tanggal);
			//	$sheet->setCellValue('C'.$i, $k->in_time);
			//	$sheet->setCellValue('D'.$i, $k->out_time);
			//	$i++;
			//}
			
		}	
		$sourcefile = "/tmp/PSFTabsensiSite".Date("ym").".csv";
		
		//CREATE CSV FILE DI LOKAL
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		$writer->save($sourcefile); 
		
		//FTP SETTING
		$ftp_server = "ftp.tap-agri.com";
		$ftp_username = "psft-1";
		$ftp_userpass = "psft123";
		$ftp_filepath = "/home/psft-1/PSFT/PSFTabsensiSite".Date("ym").".csv";	
		$ftp_path = "ftp://".$ftp_username.":".$ftp_userpass."@".$ftp_server.$ftp_filepath;
		
		//DELETE FTP FILE
		if (is_file($ftp_path)) {
			unlink($ftp_path);
			echo "Delete FTP file berhasil.</br>";
		}
		else {
			echo "FTP file not exist.</br>";
		}
		
		//COPY FILE KE FTP
		if (copy($sourcefile, $ftp_path)){
			echo "Copy file berhasil.</br>";
		}
		else{
			echo "Copy file gagal.</br>";
		}
		
		//DELETE LOKAL FILE SETELAH COPY
		if (is_file($sourcefile)) {
			unlink($sourcefile);
			echo "Delete local file berhasil.</br>";
		}
		else {
			echo "Local file not exist.</br>";
		}
	} 
	
}

?>