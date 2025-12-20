<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*function groupid(){ $CI=&get_instance(); return (int)($CI->session->userdata('groupid')); }
function is_superadmin(){ return groupid()===0; }
function is_admin_lbe(){ return groupid()===1; }
function is_staff_lbe(){ return groupid()===2; }
function is_vendor(){ return groupid()===4; }
*/

function role_code(){
  $CI=&get_instance();
  $gid = (string)$CI->session->userdata('groupid');
  switch ($gid) {
    case '0': return 'super';
    case '1': return 'admin';
    case '2': return 'staff';
    case '4': return 'vendor';
    default:  return null;
  }
}
function is_superadmin(){ return role_code()==='super'; }
function is_admin_lbe(){ return role_code()==='admin'; }
function is_staff_lbe(){ return role_code()==='staff'; }
function is_vendor(){ return role_code()==='vendor'; }

function can_upload_doc($doc_type, $role) {
  // Role code dari helper role_code() -> 'vendor'|'staff'|'admin'|'super'
  $vendor_docs = ['COA','AWB','OTHER_VENDOR'];
  $staff_admin_docs = [
    'BA_BM','LAB_RESULT','INVOICE_SC','INVOICE_HC','AH_PROOF','COA','AWB','OTHER_VENDOR'
  ];
  if ($role==='vendor') return in_array($doc_type, $vendor_docs, true);
  if ($role==='staff' || $role==='admin' || $role==='super') return in_array($doc_type, $staff_admin_docs, true);
  return false;
}

function is_logged_in(){ $CI=&get_instance(); return (bool)$CI->session->userdata('userid'); }
function require_login(){
  if (!is_logged_in()){
    redirect('login?next='.rawurlencode(current_url()));
    exit;
  }
}