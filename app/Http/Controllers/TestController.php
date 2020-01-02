<?php

namespace App\Http\Controllers;

require '/var/www/html/tap-irs/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

//use DB;
use App\Test; //MODEL
use Carbon\Carbon;

//use Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TestController extends Controller
{	
    public function index()
	{
		
	}
	
	public function cron()
	{	
		$header = array( "NIK", "Tanggal", "IN Time", "OUT Time" );
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();		
		//$sheet->fromArray([$header], NULL, 'A1');
		
		$sql = "
				  SELECT   comp_code,
						   est_code,
						   werks,
						   est_name
					  FROM tap_dw.tm_est
					  ORDER BY werks";
		
		$datax = DB::connection('irs')->select($sql);
		$array = json_decode(json_encode($datax), True);
		$sheet->fromArray(array_keys($array[0]), NULL, 'A1');
		if($datax)
		{	
			$sheet->fromArray($array, NULL, 'A2');
		}	
		$sourcefile = "/etc/csv_share/Test".Date("ym").".xlsx";
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save($sourcefile); 
		$ftp_server = "ftp.tap-agri.com";
		$ftp_username = "psft-1";
		$ftp_userpass = "psft123";
		$ftp_filepath = "/home/psft-1/Test".Date("ym").".xlsx";	
		$ftp_path = "ftp://".$ftp_username.":".$ftp_userpass."@".$ftp_server.$ftp_filepath;

		/*if (is_file($ftp_path)) {
			unlink($ftp_path);
			echo "Delete FTP file berhasil.</br>";
		}
		else {
			echo "FTP file not exist.</br>";
		}
		if (copy($sourcefile, $ftp_path)){
			echo "Copy file berhasil.</br>";
		}
		else{
			echo "Copy file gagal.</br>";
		}
		if (is_file($sourcefile)) {
			unlink($sourcefile);
			echo "Delete local file berhasil.</br>";
		}
		else {
			echo "Local file not exist.</br>";
		}*/
	} 
	
}

?>