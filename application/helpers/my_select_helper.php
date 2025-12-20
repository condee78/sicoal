<?php

	if (!defined('BASEPATH'))
    exit('No direct script access allowed');
	/**
		* CodeIgniter
		*
		* An open source application development framework for PHP 4.3.2 or newer
		*
		* @package		CodeIgniter
		* @author		ExpressionEngine Dev Team
		* @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
		* @license		http://codeigniter.com/user_guide/license.html
		* @link		http://codeigniter.com
		* @since		Version 1.0
		* @filesource
		*
		* <?php echo form_dropdown('AGAMA', select_agama(), @$row->AGAMA , 'class="form-control s2"');?>
		*
	*/


	if (!function_exists('select_menu')) {

		function select_menu()
		{
			$CI = &get_instance();
			$CI->db->select('*');
			$CI->db->order_by('ordering', 'ASC');
			$rows = $CI->db->get('menu');

			$tmp = array(
            '0' => 'Select Menu Position --'
			);


			foreach ($rows->result() as $row) {
				$key = $row->id_menu;
				$value = $row->name;
				$tmp[$key] = $value;
			}
			return $tmp;
		}

	}

	if (!function_exists('select_ci_groups')) {

		/**
			* Daftar pegawai dalam format array
			* @param type $rnj
			* @return array [reg] = nama jabatan
		*/
		function select_ci_groups()
		{

			$CI = &get_instance();

			$CI->db->select('*');
			$CI->db->order_by('groupid', 'ASC');
			$rows = $CI->db->get('ci_groups');

			$tmp = array(
            '0' => 'Select Groups --'
			);


			foreach ($rows->result() as $row) {
				$key = $row->groupid;
				$value = $row->groupname;
				$tmp[$key] = $value;
			}
			return $tmp;
		}

	}

	if (!function_exists('select_ci_menu')) {

		/**
			* Daftar pegawai dalam format array
			* @param type $rnj
			* @return array [reg] = nama jabatan
		*/
		function select_ci_menu()
		{

			$CI = &get_instance();

			$CI->db->select('*');
			$CI->db->where("link != '0'");
			$CI->db->order_by('menuid', 'ASC');
			$rows = $CI->db->get('ci_menu');

			$tmp = array(
            '0' => 'Select Menu --'
			);


			foreach ($rows->result() as $row) {
				$key = $row->menuid;
				$value = $row->menuname . ' : ' . $row->link;
				$tmp[$key] = $value;
			}
			return $tmp;
		}

	}


	if (!function_exists('select_bulan')) {

		function select_bulan()
		{
			$opt = array(
            '0' => 'Semua Bulan',
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
			);
			return $opt;
		}

	}


	if (!function_exists('select_tahun')) {

		function select_tahun()
		{
			$min = date('Y') - 5;
			$opt = array(
            '' => 'Semua Tahun'
			);

			for ($th = $min; $th <= date('Y'); $th++) {
				$opt[$th] = $th;
			}

			return $opt;
		}

	}


	if (!function_exists('select_yesno')) {

		function select_yesno($val = 'kosong')
		{

			$opt = array(
            '0' => 'Tidak',
            '1' => 'Ya'
			);

			if ($val != 'kosong') {
				$hasil = $opt[$val];
				return $hasil;
				} else {
				return $opt;
			}
		}

	}


	if (!function_exists('select_propinsi')) {

		function select_propinsi($key = 'dummy')
		{
			$opt = array(
            '0' => 'Silahkan Pilih Propinsi',
			'1' => 'Bali',
			'2' => 'Bangka Belitung',
			'3' => 'Banten',
			'4' => 'Bengkulu',
			'5' => 'Daerah Istimewa Yogyakarta',
			'6' => 'DKI Jakarta',
			'7' => 'Gorontalo',
			'8' => 'Jambi',
			'9' => 'Jawa Barat',
			'10' => 'Jawa Tengah',
			'11' => 'Jawa Timur',
			'12' => 'Kalimantan Barat',
			'13' => 'Kalimantan Selatan',
			'14' => 'Kalimantan Tengah',
			'15' => 'Kalimantan Timur',
			'16' => 'Kalimantan Utara',
			'17' => 'Kepulauan Riau',
			'18' => 'Lampung',
			'19' => 'Maluku',
			'20' => 'Maluku Utara',
			'21' => 'Nanggroe Aceh Darussalam',
			'22' => 'Nusa Tenggara Barat',
			'23' => 'Nusa Tenggara Timur',
			'24' => 'Papua',
			'25' => 'Papua Barat',
			'26' => 'Papua Pegunungan',
			'27' => 'Papua Selatan',
			'28' => 'Papua Tengah',
			'29' => 'Riau',
			'30' => 'Sulawesi Barat',
			'31' => 'Sulawesi Selatan',
			'32' => 'Sulawesi Tengah',
			'33' => 'Sulawesi Tenggara',
			'34' => 'Sulawesi Utara',
			'35' => 'Sumatra Barat',
			'36' => 'Sumatra Selatan',
			'37' => 'Sumatra Utara',
			);

			if ($key != 'dummy') {
				$key = ( $key == '' ? '0' : $key );
				return $opt[$key];
				} else {
				return $opt;
			}
		}

	}


	/**
		* warna bg
	*/
	if (!function_exists('select_bg_color')) {

		function select_bg_color()
		{
			$opt = array(
			'bg-yellow' => 'bg-yellow',
			'bg-red' => 'bg-red',
			'bg-aqua' => 'bg-aqua',
			'bg-green' => 'bg-green',
			'bg-gray' => 'bg-gray',
			'bg-navy' => 'bg-navy',
			'bg-teal' => 'bg-teal',
			'bg-purple' => 'bg-purple',
			'bg-orange' => 'bg-orange',
			'bg-maroon' => 'bg-maroon',
			'bg-blank' => 'bg-blank',
			);
			return $opt;
		}

	}

	/**
		* icon bg
	*/
	if (!function_exists('select_bg_icon')) {

		function select_bg_icon()
		{
			$opt = array(
			'fa-camera bg-purple' => 'fa-camera',
			'fa-user bg-aqua' => 'fa-user',
			'fa-envelope bg-teal' => 'fa-envelope',
			'fa-comments bg-orange' => 'fa-comments',
			'fa-video-camera bg-maroon' => 'fa-video-camera',
			);
			return $opt;
		}

	}


	/*
		* return XXX (kode mata uang)
	*/
	if(!function_exists('select_mata_uang_short'))
	{
		function select_mata_uang_short()
		{
			$arr_curr = array(
			'USD'   => 'USD',
			'IDR'   => 'IDR',
			'EUR'   => 'EUR',
			'AUD'   => 'AUD',
			'BND'   => 'BND',
			'CAD'   => 'CAD',
			'CHF'   => 'CHF',
			'CNY'   => 'CNY',
			'DKK'   => 'DKK',
			'GBP'   => 'GBP',
			'HKD'   => 'HKD',
			'JPY'   => 'JPY',
			'KRW'   => 'KRW',
			'MYR'   => 'MYR',
			'NOK'   => 'NOK',
			'NZD'   => 'NZD',
			'PGK'   => 'PGK',
			'PHP'   => 'PHP',
			'SEK'   => 'SEK',
			'SGD'   => 'SGD',
			'THB'   => 'THB'
			);
			return $arr_curr;
		}
	}


	/*
		* return XXX (kode mata uang)
	*/
	if(!function_exists('select_mata_uang'))
	{
		function select_mata_uang()
		{
			$arr_curr = array(
			'IDR'   => 'IDR - INDONESIAN RUPIAH',
			'USD'   => 'USD - US DOLLAR',
			'EUR'   => 'EUR - EURO',
			'AUD'   => 'AUD - AUSTRALIAN DOLLAR',
			'BND'   => 'BND - BRUNEI DOLLAR',
			'CAD'   => 'CAD - CANADIAN DOLLAR',
			'CHF'   => 'CHF - SWISS FRANC',
			'CNY'   => 'CNY - CHINA YUAN',
			'DKK'   => 'DKK - DANISH KRONE',
			'GBP'   => 'GBP - BRITISH POUND',
			'HKD'   => 'HKD - HONGKONG DOLLAR',
			'JPY'   => 'JPY - JAPANESE YEN',
			'KRW'   => 'KRW - KOREAN WON',
			'MYR'   => 'MYR - MALAYSIAN RINGGIT',
			'NOK'   => 'NOK - NORWEGIAN KRONE',
			'NZD'   => 'NZD - NEW ZEALAND DOLLAR',
			'PGK'   => 'PGK - PAPUA N.G. KINA',
			'PHP'   => 'PHP - PHILIPPINES PESO',
			'SEK'   => 'SEK - SWEDISH KRONA',
			'SGD'   => 'SGD - SINGAPORE DOLLAR',
			'THB'   => 'THB - THAI BATH'
			);
			return $arr_curr;
		}
	}





	/* End of file my_select_helper.php */
/* Location: ./app_simpro/helpers/my_select_helper.php */