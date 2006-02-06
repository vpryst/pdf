<?php
/**
* Pdf system class for outputing pdf file images
*
* @author   
* @version  $Revision: 1.7 $
* @package  pdf
*/

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

/**
* required setup
*/
require_once( '../bit_setup_inc.php' );

include_once( STRUCTURES_PKG_PATH.'struct_lib.php');
include_once( WIKI_PKG_PATH.'BitPage.php');

// Create the HomePage if it doesn't exist
if (!$wikilib->pageExists($wiki_home_page)) {
	$wikilib->create_page($wiki_home_page, 0, '', $gBitSystem->getUTCTime(), 'bitweaver initialization');
}

if (!isset($_SESSION["thedate"])) {
	$thedate = $gBitSystem->getUTCTime();
} else {
	$thedate = $_SESSION["thedate"];
}

// Get the page from the request var or default it to HomePage
if (!isset($_REQUEST["page"])) {
	$page = $wiki_home_page;

	$gBitSmarty->assign('page', $wiki_home_page);
} else {
	$page = $_REQUEST["page"];

	$gBitSmarty->assign_by_ref('page', $_REQUEST["page"]);
}

require_once ( WIKI_PKG_PATH.'page_setup_inc.php' );

// Check if we have to perform an action for this page
// for example lock/unlock
if (isset($_REQUEST["action"])) {
	if ($_REQUEST["action"] == 'lock') {
		$wikilib->lock_page($page);
	} elseif ($_REQUEST["action"] == 'unlock') {
		$wikilib->unlock_page($page);
	}
}

// If the page doesn't exist then display an error
if (!$wikilib->pageExists($page)) {
	$gBitSmarty->assign('msg', tra("Page cannot be found"));

	$gBitSystem->display( 'error.tpl' );
	die;
}

// Now check permissions to access this page
if (!$gBitUser->hasPermission( 'bit_p_view' )) {
	$gBitSmarty->assign('msg', tra("Permission denied you cannot view this page"));

	$gBitSystem->display( 'error.tpl' );
	die;
}

// Now increment page hits since we are visiting this page
if ($gBitSystem->isFeatureActive( 'count_admin_pvs' ) || !$gBitUser->isAdmin()) {
	$wikilib->add_hit($page);
}

// Get page data
$info = $wikilib->get_page_info($page);

// Verify lock status
if ($info["flag"] == 'L') {
	$gBitSmarty->assign('lock', true);
} else {
	$gBitSmarty->assign('lock', false);
}

$pdata = $gBitSystem->parseData($info["data"]);

//$gBitSmarty->assign_by_ref('parsed',$pdata);
//$gBitSmarty->assign_by_ref('last_modified',date("l d of F, Y  [H:i:s]",$info["last_modified"]));
//$gBitSmarty->assign_by_ref('last_modified',$info["last_modified"]);
if (empty($info["user"])) {
	$info["user"] = 'anonymous';
}

//$gBitSmarty->assign_by_ref('lastUser',$info["user"]);

// Parse the Data into PDF format (:TODO:)
// 
include_once ("lib/class.ezpdf.php");
$pdf = &new Cezpdf();
$pdf->selectFont('lib/fonts/Helvetica');
$pdf->ezText("Hello world", 14);
$pdf->ezText($info["data"], 12);
$pdf->ezStream();

// Display the Index Template
/*
$gBitSystem->display('tiki-show_page.tpl');
$gBitSmarty->assign('show_page_bar','y');
*/

?>
