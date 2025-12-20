<?php



function RandomString($length = 10) {

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $charactersLength = strlen($characters);

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {

        $randomString .= $characters[rand(0, $charactersLength - 1)];

    }

    return $randomString;

}







function getShipmentStatus($data)

{

    // Helper cek tanggal valid

    $isCompleted = function($v){

        return isset($v) && $v != '' && $v != null &&

               $v != '0000-00-00' && $v != '0000-00-00 00:00:00';

    };



    // Daftar step + field yang harus terpenuhi

    $steps = [

        'loading_port'          => ['actual_arrival_load_port'],

        'commence_loading'      => ['actual_arrival_load_port','commence_loading','complete_loading'],

        'departure'             => ['actual_departure'],

        'sample_received'       => ['dt_sample_received'],

        'unloading'             => ['complete_disch'],

        'documents'             => ['dt_coa_delivery'],

        'completed_shipment'    => ['dt_ba_bm'],

        'invoicing'             => ['dt_inv_received'],

        'completed_invoicing'   => ['dt_payment'],

    ];



    // Label yang mudah dibaca

    $labels = [

        'loading_port'          => 'Loading Port Completed',

        'commence_loading'      => 'Commence & Loading Completed',

        'departure'             => 'Departure Completed',

        'sample_received'       => 'Sample & Actual Received',

        'unloading'             => 'Unloading Completed',

        'documents'             => 'Documents Completed',

        'completed_shipment'    => 'Shipment Completed',

        'invoicing'             => 'Invoicing Completed',

        'completed_invoicing'   => 'All Invoice Completed',

    ];



    $results = [];

    $completedCount = 0;

    $lastCompletedKey = null;   // untuk menyimpan step terakhir



    foreach ($steps as $stepName => $fieldsNeeded) {



        $done = true;

        foreach ($fieldsNeeded as $field){

            if(!$isCompleted($data[$field] ?? null)){

                $done = false;

                break;

            }

        }



        $results[$stepName] = $done ? 'completed' : 'pending';



        if($done){

            $completedCount++;

            $lastCompletedKey = $stepName; // update setiap kali ada step completed

        }

    }



    // Hitung progress

    $totalSteps = count($steps);

    $percent = ($completedCount / $totalSteps) * 100;



    return [

        'steps' => $results,

        'completed_steps' => $completedCount,

        'total_steps' => $totalSteps,

        'progress_percent' => round($percent, 2),

        'status_label' => ($completedCount == $totalSteps) ? 'Fully Completed' : 'In Progress',



        // Tambahan baru:

        'last_completed_step' => $lastCompletedKey,

        'last_completed_label' => $lastCompletedKey ? $labels[$lastCompletedKey] : 'No Step Completed Yet'

    ];

}





	function set62($telp){

    

    if(@$telp[0]=='0'){

        $telp='+62'.substr($telp,1);

    }

    $telp=str_replace(' ','.',$telp);

    $telp=str_replace(' ',' ',$telp);

    $telp=str_replace(' ','-',$telp);

    

    return $telp;

}



	if ( ! function_exists('harusLogin'))

	{

		

		function harusLogin(){

			$CI =& get_instance();

			

			// This only returns the id does not set it.

			if ( ! $CI->session->userdata('is_login') )

			{

				//print_r($CI->session->userdata); exit;

				redirect('auth/logout');

			}

		}

		

	}

	

	if (!function_exists('tgl_mysql')) {

		function tgl_mysql($tgl)

		{

			$tmp = strtotime($tgl);

			if ($tmp > 0) {

				$date = date('Y-m-d', $tmp);

				} else {

				$date = $tmp;

			}

			

			//return date('d-M-Y', strtotime($tgl));

			return $date;

		}

	}

	

	if (!function_exists('tgl_show')) {

		function tgl_show($tgl, $today = false)

		{

			$tmp = strtotime($tgl);

			if ($tmp > 0) {

				$date = date('d-m-Y', $tmp);

				} else {

				$date = false;

				if ($today) {

					$date = date('d-m-Y');

				}

			}

			

			//return date('d-M-Y', strtotime($tgl));

			return $date;

		}

	}

	

	if (!function_exists('tgl_show_print')) {

		function tgl_show_print($tgl)

		{

			$tmp = strtotime($tgl);

			if ($tmp > 0) {

				setlocale(LC_TIME, 'id_ID');

				$date = date('d M Y', $tmp);

				$date = strftime('%d %B %Y', $tmp);

				} else {

				$date = '&nbsp;';

			}

			

			//return date('d-M-Y', strtotime($tgl));

			return $date;

		}

	}

	

		if (!function_exists('tgl_time')) {

		function tgl_time($tgl)

		{

			$tmp = strtotime($tgl);

			if ($tmp > 0) {

				setlocale(LC_TIME, 'id_ID');

				$date = date('d M Y H:i', $tmp);

				$date = strftime('%d %b %Y %H:%I', $tmp);

				} else {

				$date = '&nbsp;';

			}

			

			//return date('d-M-Y', strtotime($tgl));

			return $date;

		}

	}

	

	

	if (!function_exists('tgl_show_print_eng')) {

		function tgl_show_print_eng($tgl)

		{

			$tmp = strtotime($tgl);

			if ($tmp > 0) {

				setlocale(LC_TIME, 'en_US');

				$date = date('F dS Y', $tmp);

				} else {

				$date = '&nbsp;';

			}

			

			//return date('d-M-Y', strtotime($tgl));

			return $date;

		}

	}

	

	if (!function_exists('nilai_awal')) {

		function nilai_awal($awal='', $akhir='')

		{

			if ($awal == '') {

				return $akhir;

				} else {

				return $awal;

			}

		}

	}	

	

	 function showqr($de_data='https://jasacom.net'){

             $CI = &get_instance();



        $CI->load->library('ciqrcode');

	

        	header("Content-Type: image/png");

        	//$data = encode('https://sifaba.lbebanten.com/cek/disposal/?id_enc=MTU=&choe=UTF-8');

        	$params['data'] = decode($de_data);

        	

        	 $CI->ciqrcode->generate($params);

}

	function encode($value)

{

    /*

      $CI = &get_instance();

      $CI->load->library('encrypt');

      return $CI->encrypt->encode($value);

     */



    if (!$value) {

        return false;

    }

    $skey = 'jasacom.n3t';

    $text = $value;



     $data = base64_encode($text);

    $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);



    return trim($data);

  

}







function decode($value)

{

    /*

      $CI = &get_instance();

      $CI->load->library('encrypt');

      return $CI->encrypt->decode($value);

     */



    if (!$value) {

        return false;

    }

    $skey = 'jasacom.n3t';

    

     $data = str_replace(['-', '_'], ['+', '/'], $value);

    $mod4 = strlen($data) % 4;

    if ($mod4) {

        $data .= substr('====', $mod4);

    }



    $crypttext= base64_decode($data, true);

    

    

   

   

    return trim($crypttext);

}

	

	function tgl_indo($tanggal,$index='0'){

	$bulan = array (

		'01' =>   'Januari',

		'02' =>'Februari',

		'03' =>'Maret',

		'04' =>'April',

		'05' =>'Mei',

		'06' =>'Juni',

		'07' =>'Juli',

		'08' =>'Agustus',

		'09' =>'September',

		'10' =>'Oktober',

		'11' =>'November',

		'12' =>'Desember'

	);

	$pecahkan = explode(' ', $tanggal);

		$pecahkan = explode('-', $pecahkan[0]);

	// variabel pecahkan 0 = tahun

	// variabel pecahkan 1 = bulan

	// variabel pecahkan 2 = tanggal

    if($index==1){

        return $bulan[$pecahkan[$index]];

    }

	return $pecahkan[$index];

}



function hari_indo ($date) {

    $hariInggris=date("l", strtotime($date));



  switch ($hariInggris) {

    case 'Sunday':

      return 'Minggu';

    case 'Monday':

      return 'Senin';

    case 'Tuesday':

      return 'Selasa';

    case 'Wednesday':

      return 'Rabu';

    case 'Thursday':

      return 'Kamis';

    case 'Friday':

      return 'Jumat';

    case 'Saturday':

      return 'Sabtu';

    default:

      return 'hari tidak valid';

  }

}

	