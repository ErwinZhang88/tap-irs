<?php
namespace App\Http\Controllers;
set_time_limit(0);
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Session;
use Config;
use View;
use URL;

use \Firebase\JWT\JWT;


class MobileInspectionController extends Controller {

	public function __construct() {
		$this->mobile_inspection = DB::connection( 'mobile_ins' );
	}

	private function verify_token( $token = '' ) {
		$status = array();
		$status['expired'] = true;
		$status['message'] = '';
		$status['missmatch'] = true;
		$status['data'] = array();

		$token = ( $token != '' ? explode( ' ', $token ) : array( $token ) );

		if ( count( $token ) > 0 ) {
			$access_token = $token[1];

			try {
				$key = "T4pagri123#";
				$decoded = JWT::decode( $access_token, $key, array( 'HS256' ) );
				$decoded = json_decode( json_encode( $decoded ), true );

				if ( self::check_token( $decoded ) == true ) {
					$decoded['iat'] = date( 'Y-m-d', intval( $decoded['iat'] ) );
					$decoded['exp'] = date( 'Y-m-d', intval( $decoded['exp'] ) );
					$status['data'] = $decoded;
					$status['expired'] = false;
					$status['message'] = 'OK';
					$status['missmatch'] = false;
				}
				else {
					$status['message'] = 'Invalid Token 2.';
					$status['missmatch'] = true;
				}
			}
			catch( \Firebase\JWT\ExpiredException $e ){
				$status['expired'] = true;
				$status['message'] = $e->getMessage();
			}
			catch( \Firebase\JWT\SignatureInvalidException $e ){
				$response['missmatch'] = true;
				$response['message'] = $e->getMessage();
				
			}
		}
		else {
			$status['message'] = 'Invalid Token 1.';
			$status['missmatch'] = true;
		}

		return $status;
	}

	private function check_token( $array ) {
		if( 
			isset( $array['USERNAME'] ) 
			&& isset( $array['USER_AUTH_CODE'] ) 
			&& isset( $array['USER_ROLE'] ) 
			&& isset( $array['LOCATION_CODE'] ) 
			&& isset( $array['REFFERENCE_ROLE'] ) 
			&& isset( $array['EMPLOYEE_NIK'] ) 
			&& isset( $array['exp'] ) 
			&& isset( $array['iat'] ) 
		) {
			return true;
		}
		else {
			return false;
		}
	}

	public function data_suggestion_daily( Request $req ) {


		# Set Default Response
		$response = array();
		$response['status'] = true;
		$response['message'] = 'OK';
		$response['data'] = array();

		# Authorization
		$authdata = self::verify_token( $req->header( 'Authorization' ) );
		$REFFERENCE_ROLE = $authdata['data']['REFFERENCE_ROLE'];
		$LOCATION_CODE = $authdata['data']['LOCATION_CODE'];
		$LOCATION_CODE = explode( ',', $LOCATION_CODE );
		$LOCATION_CODE = '\''.join( '\',\'', $LOCATION_CODE ).'\'';
		$where_condition = '';

		switch( $REFFERENCE_ROLE ) {
			case 'NATIONAL':
				$where_condition .= "";
			break;
			case 'COMP_CODE':
				$where_condition .= "AND SH.\"Company Code\" IN( $LOCATION_CODE )";
			break;
			case 'BA_CODE':
				$where_condition .= "AND SD.WERKS IN( $LOCATION_CODE )";
			break;
			case 'AFD_CODE':
				$where_condition .= "AND SD.WERKS || SD.AFD_CODE IN( $LOCATION_CODE )";
			break;
			case 'AFD_CODE':
				$where_condition .= "AND SD.WERKS || SD.AFD_CODE || SD.BLOCK_CODE IN( $LOCATION_CODE )";
			break;
		}

		$sql = ( "
			SELECT
				SD.BLOCK_NAME || ' / ' || SH.\"Maturity Status\" || ' / ' || SH.\"Estate Name\" AS LOCATION_CODE,
				SH.LAST_TGL_PANEN,
				SH.LAST_TGL_INSPEKSI,
				SD.INS_DATE1,
				SD.INS_ROLE1,
				SD.INS_BARIS1,
				SD.INS_DATE2,
				SD.INS_ROLE2,
				SD.INS_BARIS2,
				SD.INS_DATE3,
				SD.INS_ROLE3,
				SD.INS_BARIS3,
				SD.INS_DATE4,
				SD.INS_ROLE4,
				SD.INS_BARIS4,
				SD.PAN_DATE,
				SD.PAN_TOTAL_JJG,
				SD.PAN_BJR_LALU,
				SD.PAN_RES_TPH,
				SD.RAW_DATE,
				SD.RAW_CPT_DATE,
				SD.RAW_CPT_HK,
				SD.RAW_CPT_HA,
				SD.RAW_SPOT_DATE,
				SD.RAW_SPOT_HK,
				SD.RAW_SPOT_HA,
				SD.RAW_LALANG_DATE,
				SD.RAW_LALANG_HK,
				SD.RAW_LALANG_HA,
				SD.WERKS,
				SD.AFD_CODE,
				SD.BLOCK_CODE,
				SD.BLOCK_NAME
			FROM
				TR_SUGGESTION_H SH
				LEFT JOIN TR_SUGGESTION_D SD
					ON SH.\"Kode BA\" = SD.WERKS
					AND SH.AFD = SD.AFD_CODE
					AND SH.\"Block Code\" = SD.BLOCK_CODE
			WHERE 
				1 = 1
				$where_condition
				AND ROWNUM < 6
		" );
		$query = $this->mobile_inspection->select( $sql );
		
		$array_get_image_by_tr_code = array();
		foreach ( $query as $key => $q ) {
			$werks_afd_block_code = $q->werks.$q->afd_code.$q->block_code;
			$sql_statement = collect( $this->mobile_inspection->select( "
				SELECT
					LISTAGG( BLOCK_INSPECTION_CODE, ', ') WITHIN GROUP ( ORDER BY BLOCK_INSPECTION_CODE ) AS TR_CODE
				FROM
					TR_BLOCK_INSPECTION_H
				WHERE
					ROWNUM < 10
					AND WERKS || AFD_CODE || BLOCK_CODE = '{$werks_afd_block_code}'
			" ) )->first();
			$array_tr_code = ( $sql_statement->tr_code == '' ? array() : explode( ',', $sql_statement->tr_code ) );
			$array_get_image_by_tr_code[$werks_afd_block_code] = $array_tr_code;
		}

		$client = new \GuzzleHttp\Client();
		$result_images = $client->request('POST', 'http://149.129.250.199:4012/api/v1.1/inspection/suggestion', [
			'json' => $array_get_image_by_tr_code,
			'headers' => [
				'Authorization' => $req->header( 'Authorization' )
			]
		] );
		$result_images = json_decode( $result_images->getBody(), true );
		$result_images = $result_images['data'];

		$i = 0;
		foreach ( $query as $q ) {
			$werks_afd_block_code = $q->werks.$q->afd_code.$q->block_code;
			$image_url = $result_images[$werks_afd_block_code];
			$image_name = basename( parse_url( $image_url, PHP_URL_PATH ) );
			$response['data'][$i]['LOCATION_CODE'] = $q->location_code;
			$response['data'][$i]['WERKS'] = $q->werks;
			$response['data'][$i]['DESC'] = '';
			$response['data'][$i]['AFD_CODE'] = $q->afd_code;
			$response['data'][$i]['BLOCK_CODE'] = $q->block_code;
			$response['data'][$i]['BLOCK_NAME'] = $q->block_name;
			$response['data'][$i]['IMAGE'] = $image_url; // From API
			$response['data'][$i]['IMAGE_NAME'] = $image_name;
			$response['data'][$i]['DATA_ARRAY'] = array();

			$response['data'][$i]['DATA_ARRAY'][0]['TYPE'] = 'panen';
			$response['data'][$i]['DATA_ARRAY'][0]['DATE'] = date( 'Y-m-d H:i:s', strtotime( $q->pan_date ) );
			$response['data'][$i]['DATA_ARRAY'][0]['DESC'] = 'Panen';
			$response['data'][$i]['DATA_ARRAY'][0]['DATA'] = array();
			$response['data'][$i]['DATA_ARRAY'][0]['DATA']['TOTAL_JANJANG_PANEN'] = intval( $q->pan_total_jjg );
			$response['data'][$i]['DATA_ARRAY'][0]['DATA']['BJR_BULAN_LALU'] = intval( $q->pan_bjr_lalu );
			$response['data'][$i]['DATA_ARRAY'][0]['DATA']['TOTAL_RESTAN_TPH'] = intval( $q->pan_res_tph );

			$cpt_desc = ( $q->raw_cpt_date != '' ? date( 'd M Y', strtotime( $q->raw_cpt_date ) ) : '-' ).
				   ', '.( $q->raw_cpt_ha != '' ? $q->raw_cpt_ha : '-' ).' Ha, '.
				   ( $q->raw_cpt_hk != '' ? $q->raw_cpt_hk : '-' ).'HK';
			$spot_desc = ( $q->raw_spot_date != '' ? date( 'd M Y', strtotime( $q->raw_spot_date ) ) : '-' ).
				   ', '.( $q->raw_spot_ha != '' ? $q->raw_spot_ha : '-' ).' Ha, '.
				   ( $q->raw_spot_hk != '' ? $q->raw_spot_hk : '-' ).'HK';
			$lalang_desc = ( $q->raw_lalang_date != '' ? date( 'd M Y', strtotime( $q->raw_lalang_date ) ) : '-' ).
				   ', '.( $q->raw_lalang_ha != '' ? $q->raw_lalang_ha : '-' ).' Ha, '.
				   ( $q->raw_lalang_hk != '' ? $q->raw_lalang_hk : '-' ).'HK';

			$response['data'][$i]['DATA_ARRAY'][1]['TYPE'] = 'rawat';
			$response['data'][$i]['DATA_ARRAY'][1]['DATE'] = date( 'Y-m-d H:i:s', strtotime( $q->raw_date ) );
			$response['data'][$i]['DATA_ARRAY'][1]['DESC'] = 'Rawat';
			$response['data'][$i]['DATA_ARRAY'][1]['DATA'] = array();
			$response['data'][$i]['DATA_ARRAY'][1]['DATA']['CPT_SPRAYING'] = $cpt_desc;
			$response['data'][$i]['DATA_ARRAY'][1]['DATA']['SPOT_SPRAYING'] = $spot_desc;
			$response['data'][$i]['DATA_ARRAY'][1]['DATA']['LALANG_CTRL'] = $lalang_desc;

			$sorting_data[0]['INS_DATE'] =  $q->ins_date1;
			$sorting_data[0]['INS_ROLE'] = str_replace( '_', ' ', $q->ins_role1 );
			$sorting_data[0]['INS_BARIS'] = $q->ins_baris1;
			$sorting_data[1]['INS_DATE'] =  $q->ins_date2;
			$sorting_data[1]['INS_ROLE'] = str_replace( '_', ' ', $q->ins_role2 );
			$sorting_data[1]['INS_BARIS'] = $q->ins_baris2;
			$sorting_data[2]['INS_DATE'] =  $q->ins_date3;
			$sorting_data[2]['INS_ROLE'] = str_replace( '_', ' ', $q->ins_role3 );
			$sorting_data[2]['INS_BARIS'] = $q->ins_baris3;
			$sorting_data[3]['INS_DATE'] =  $q->ins_date4;
			$sorting_data[3]['INS_ROLE'] = str_replace( '_', ' ', $q->ins_role4 );
			$sorting_data[3]['INS_BARIS'] = $q->ins_baris4;

			$j = 0;
			$n = 31;
			foreach ( $sorting_data as $sd ) {
				if ( $sd['INS_DATE'] == null ) {
					$sorting_data[$j]['INS_DATE'] = date( 'Y-m-d H:i:s', strtotime( '9999-12-'.$n ) );
					$n--;
				}
				$j++;
			}

			foreach ( $sorting_data as $key => $part ) {
				$sort[$key] = strtotime( $part['INS_DATE'] );
			}
			array_multisort( $sort, SORT_ASC, $sorting_data );

			$response['data'][$i]['DATA_ARRAY'][2]['TYPE'] = 'inspeksi';
			$response['data'][$i]['DATA_ARRAY'][2]['DATE'] = ( $sorting_data[0]['INS_DATE'] != '' ? date( 'Y-m-d H:i:s', strtotime( $sorting_data[0]['INS_DATE'] ) ) : '' );
			$response['data'][$i]['DATA_ARRAY'][2]['DESC'] = $sorting_data[0]['INS_ROLE'];
			$response['data'][$i]['DATA_ARRAY'][2]['BARIS'] = $sorting_data[0]['INS_BARIS'];
			$response['data'][$i]['DATA_ARRAY'][2]['DATA'] = array();

			for( $z = 1; $z <= 3; $z++ ) {
				$z = intval( $z );
				$response['data'][$i]['DATA_ARRAY'][2]['DATA'][] = array(
					"ROLE" => $sorting_data[$z]['INS_ROLE'],
					"DATE_ROLE" => ( $sorting_data[$z]['INS_DATE'] != '' ? date( 'Y-m-d H:i:s', strtotime( $sorting_data[$z]['INS_DATE'] ) ) : '' ),
					"BARIS" => $sorting_data[$z]['INS_BARIS']
				);
			}

			$date_sort = array(
				date( 'Y-m-d H:i:s', strtotime( $q->pan_date ) ),
				date( 'Y-m-d H:i:s', strtotime( $q->raw_date ) ),
				( $sorting_data[0]['INS_DATE'] != '' ? date( 'Y-m-d H:i:s', strtotime( $sorting_data[0]['INS_DATE'] ) ) : date( 'Y-m-d H:i:s', strtotime( '9999-12-31' ) ) )
			);

			array_multisort( $date_sort, SORT_ASC, $response['data'][$i]['DATA_ARRAY'] );
			$i++;
		}

		return response()->json( $response );
	}

	public function data_titik_restan( Request $req ) {
		$query = $this->mobile_inspection->select( "SELECT * FROM TR_TITIK_RESTAN" );

		// print '<pre>';
		// print_r( $query );
		// print '</pre>';
		// dd();

		$jsondata = array();
		$i = 0;
		foreach ( $query as $q ) {
			$jsondata[$i]['OPH'] = $q->oph;
			$jsondata[$i]['BCC'] = $q->bcc;
			$jsondata[$i]['TPH_RESTANT_DAY'] = $q->tph_restant_day;
			$jsondata[$i]['LATITUDE'] = $q->latitude;
			$jsondata[$i]['LONGITUDE'] = $q->longitude;
			$jsondata[$i]['JML_JANJANG'] = intval( $q->jml_janjang );
			$jsondata[$i]['JML_BRONDOLAN'] = intval( $q->jml_brondolan );
			$jsondata[$i]['KG_TAKSASI'] = intval( $q->kg_taksasi );
			$jsondata[$i]['TGL_REPORT'] = intval( date( 'Ymd', strtotime( $q->tgl_report ) ) );
			$jsondata[$i]['WERKS'] = $q->werks;
			$jsondata[$i]['EST_NAME'] = $q->est_name;
			$jsondata[$i]['AFD_CODE'] = $q->afd_code;
			$jsondata[$i]['BLOCK_CODE'] = $q->block_code;
			$jsondata[$i]['BLOCK_NAME'] = $q->block_name;
			$i++;
		}
		print json_encode( $jsondata );
	}

}