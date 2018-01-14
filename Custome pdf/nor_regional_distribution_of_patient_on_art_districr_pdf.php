<?php

include("../define.inc");
include('../languages/lang_en.php');
include('../languages/lang_fr.php');
include('../languages/lang_switcher_report.php');
//include_once ('function_lib.php');

$conn = mysql_connect(HOSTNAME, DBUSER, DBPWD) or die('Could not connect: ' . mysql_error());
mysql_select_db(DBNAME, $conn) or die('Could not connect: ' . mysql_error());

//$monthId = $_REQUEST['operation'];
//echo '==================================' . $monthId;

$task = '';
if (isset($_REQUEST['operation'])) {
    $task = $_REQUEST['operation'];
}

switch ($task) {
    case 'generateDistrictListReport':
        generateDistrictListReport($conn);
        break;
    default:
        echo "{failure:true}";
        break;
}

function generateDistrictListReport($conn) {
    //echo '========================================';

    $gTEXT = $TEXT;
    $monthId = $_REQUEST['MonthId'];
    $MonthName = $_REQUEST['MonthName'];
    $Year = $_REQUEST['Year'];

    require_once('tcpdf/tcpdf.php');
    require_once('fpdf/fpdi.php');
//    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new FPDI();
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    //$pdf->SetAutoPageBreak(true, 1);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->AddPage();
    //$pdf->startPage($orientation = P,$format = 'A4',$tocpage = false);
    //$pdf->startPage();
    $pdf->SetFillColor(255, 255, 255);

    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
        require_once(dirname(__FILE__) . '/lang/eng.php');
        $pdf->setLanguageArray($l);
    }
//    $pdf->setFontSubsetting(false);
//    $pdf->AddPage();
//    $pdf->SetFillColor(255, 255, 255);
//safe_query("SET @rank=0;");
//$serial = "@rank:=@rank+1 AS SL";

    $sQuery = "SELECT SQL_CALC_FOUND_ROWS c.`RegionId`,0 value, d.`DistrictName`,r.RegionName,
	SUM(a.`TotalPatient`) AS TotalPatient
 
	FROM `t_cfm_patientoverview` a 
	INNER JOIN `t_cfm_masterstockstatus` b ON a.`CFMStockId` = b.`CFMStockId` AND b.`StatusId` = 5
	INNER JOIN `t_facility` c ON a.`FacilityId` = c.`FacilityId`
	INNER JOIN `t_districts` d ON c.`DistrictId` = d.`DistrictId`
        INNER JOIN `t_region` r ON d.`RegionId` = r.`RegionId`
	WHERE a.`Year` =$Year
	AND a.`MonthId` = $monthId
	AND a.`ItemGroupId` =1
        GROUP BY  d.`DistrictName` order by r.RegionName,d.`DistrictName`";
//echo $sQuery;
    mysql_query("SET character_set_results=utf8");
    $dResult = mysql_query($sQuery);
    $aData = array();
    $total = mysql_num_rows($dResult);

//$r = safe_query($sQuery);
//$total = mysql_num_rows($r);
    $col = '';
    $serial = 1;
    if ($total > 0) {
        $tmpLevelId = '';
        $totalPatients = 0;
        $dataList = array();
        while ($r2 = mysql_fetch_array($dResult)) {
            $dataList[] = $r2;
            $totalPatients+= $r2['TotalPatient'];
        }
        $totalPatients = ($totalPatients == 0 ? 1 : $totalPatients);

        foreach ($dataList as $rec) {
            $col.= '<tr>
				<td width="25px" style="text-align:center" class="center-aln">' . $serial++ . '</td>
				<td width="110px" class="left-aln">' . $rec['RegionName'] . '</td>
				<td width="125px" class="left-aln">' . $rec['DistrictName'] . '</td>
				<td width="110px" style="text-align:right"> ' . number_format($rec['TotalPatient'], 2) . '</td>
				<td width="110px" style="text-align:right"> ' . number_format(($rec['TotalPatient'] * 100) / $totalPatients, 2) . '%</td>
			 </tr>';
        }

        //echo $col;        
        $lan = $_POST['lan'];
        if ($lan == 'en-GB') {
            $SITETITLE = SITETITLEENG;
        } else {
            $SITETITLE = SITETITLEFRN;
        }
        $html = '<style>
    </style>
    <head></head>
    <body>
        <h3 style="text-align:center;"><b>' . $SITETITLE . '</b></h3>
        <h4 style="text-align:center;"><b>Regional Distribution of Patient on ART District Data</b></h4>
         <h5 style="text-align:center;"><b>' . $MonthName . ',' . $Year . '</b></h5>
    </body>';

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->writeHTMLCell(0, 0, 10, 10, $html, '', 0, 0, false, 'C', true);

//        $html_head = "<h4 style='text-align:center;font-size:12px;'><b>test</b></h4>";
//
//
//        $pdf->writeHTMLCell(0, 0, 30, '', $html, '', 1, 1, false, 'C', true, $spacing = 0);
//        $pdf->setSourceFile("../report/pdfslice/regional_distribution_of_patient_on_art_district_chart.pdf");
//
//        $tplIdx = $pdf->importPage(1);
//        $pdf->useTemplate($tplIdx, 6, 0, 200);
//        $pdf->SetFont('dejavusans', '', 10);
//        $pdf->writeHTMLCell(0, 0, 10, 10, $html, '', 0, 0, false, 'C', true);



        $html = '
    <!-- EXAMPLE OF CSS STYLE -->
    <style>
     td{
         height: 6px;
         line-height:3px;
     }
     th{
     height: 20;
    }
    </style>
    <body>    
    <table width="500px" border="0.5" style="margin:0 auto;">
    <tr style="page-break-inside:avoid;">
			<th width="25px" align="center"><b>SL</b></th>	 
			<th width="110px" align="left"><b>Name of Regions</b></th>
			<th width="125px" align="left"><b>Name of Districts</b></th>
			<th width="110px" style="text-align:right"><b>Patients</b></th>
			<th width="110px" style="text-align:right"><b>Percentages</b></th>			
         </tr>' . $col . '</table></body>';
        //echo $html;
//        $pdf->SetFont('dejavusans', '', 7);
//        $pdf->writeHTMLCell(0, 0, 10, 45, $html, '', 1, 1, false, 'L', true);

        $pdf->SetFont('dejavusans', '', 7);
        $pdf->writeHTMLCell(0, 0, 10, 40, $html, '', 1, 1, false, 'L', true);

        $filePath = SITEDOCUMENT . '/report/pdfslice/regional_distribution_of_patient_on_art_district.pdf';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $pdf->Output('pdfslice/regional_distribution_of_patient_on_art_district.pdf', 'F');
        echo trim('regional_distribution_of_patient_on_art_district.pdf');
    } else {
        echo 'Processing Error';
    }
}

?> 