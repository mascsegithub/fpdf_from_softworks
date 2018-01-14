<?php

include("../define.inc");
include('../languages/lang_en.php');
include('../languages/lang_switcher_report.php');

$conn = mysql_connect(HOSTNAME, DBUSER, DBPWD) or die('Could not connect: ' . mysql_error());
mysql_select_db(DBNAME, $conn) or die('Could not connect: ' . mysql_error());
mysql_query('SET CHARACTER SET utf8');

$gTEXT = $TEXT;
$baseUrl = $_GET['baseUrl'];

$RegionId = $_GET['RegionId'];
$DistrictId = $_GET['DistrictId'];
$ItemGroupId = $_GET['ItemGroupId'];
$ChiefdomId = $_GET['ChiefdomId'];
$FacilityType = $_GET['FacilityType'];
$FacilityLevel = $_GET['FacilityLevel'];
$SubgroupId = $_GET['SubgroupId'];


$ItemGroupName = $_GET['ItemGroupName'];
$RegionName = $_GET['RegionName'];
$DistrictName = $_GET['DistrictName'];
$ChiefdomName = $_GET['ChiefdomName'];
$FacilityTypeName = $_GET['FacilityTypeName'];
$FacilityLevelName = $_GET['FacilityLevelName'];
$AssignGroupName = $_GET['AssignGroupName'];


//echo '============================' . $ChiefdomId;
//exit();
$baseUrl = $_GET['baseUrl'];
//echo '============================' . $baseUrl;
$lan = $_GET['lan'];
if ($lan == 'en-GB') {
    $FLevelName = 'FLevelName';
    $ServiceAreaName = 'ServiceAreaName';
    $OwnerTypeName = 'OwnerTypeName';
    $GroupName = 'GroupName';
} else {
    $FLevelName = 'FLevelNameFrench';
    $ServiceAreaName = 'ServiceAreaNameFrench';
    $OwnerTypeName = 'OwnerTypeNameFrench';
    $GroupName = 'GroupNameFrench';
}


if ($ChiefdomId == '') {
    $ChiefdomId = '';
} else {
    $ChiefdomId = $ChiefdomId;
}

if ($ARegionId == '') {
    $ARegionId = 0;
} else {
    $ARegionId = $ARegionId;
}
if ($DistrictId == '') {
    $DistrictId = 0;
} else {
    $DistrictId = $DistrictId;
}
if (!$ItemGroupId) {
    $ItemGroupId = 0;
}
if ($FacilityType) {
    $FacilityType = " AND a.FTypeId = '" . $FacilityType . "' ";
}
if ($FacilityLevel) {
    $FacilityLevel = " AND a.FLevelId = '" . $FacilityLevel . "' ";
}


if ($SubgroupId == 0) {
    $getValue1 = 'h.ARTLogistics,h.ARTPatient,h.PMTCT';
    $getValue2 = 'p.ARTLogistics,p.ARTPatient,p.PMTCT';
    $WhereCond = '';
} else if ($SubgroupId == 1) {
    $getValue1 = 'h.ARTLogistics';
    $getValue2 = 'p.ARTLogistics';
    $WhereCond = 'AND p.ARTLogistics =1';
} else if ($SubgroupId == 2) {
    $getValue1 = 'h.ARTPatient';
    $getValue2 = 'p.ARTPatient';
    $WhereCond = ' And p.ARTPatient =1';
} else if ($SubgroupId == 3) {
    $getValue1 = 'h.PMTCT';
    $getValue2 = 'p.PMTCT';
    $WhereCond = 'AND p.PMTCT =1';
}

$sWhere = "";
if ($_POST['sSearch'] != "") {
    $sWhere = " AND (FacilityCode like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FacilityName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FTypeName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR RegionName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR $FLevelName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FacilityAddress like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FacilityPhone like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FacilityFax like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FacilityEmail like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR FacilityManager like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR DistrictName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR $OwnerTypeName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%'
                    OR $ServiceAreaName like '%" . mysql_real_escape_string($_POST['sSearch']) . "%' ) ";
}

$sLimit = "";
if (isset($_POST['iDisplayStart'])) {
    $sLimit = "limit " . mysql_real_escape_string($_POST['iDisplayStart']) . ", " . mysql_real_escape_string($_POST['iDisplayLength']);
}

$sOrder = "";
if (isset($_POST['iSortCol_0'])) {
    $sOrder = " ORDER BY FLevelName, ";
    for ($i = 0; $i < mysql_real_escape_string($_POST['iSortingCols']); $i++) {
        $sOrder .= fnColumnToGetFacility(mysql_real_escape_string($_POST['iSortCol_' . $i])) . "" . mysql_real_escape_string($_POST['sSortDir_' . $i]) . ", ";
    }
    $sOrder = substr_replace($sOrder, "", -2);
}

function fnColumnToGetFacility($i) {

    if ($i == 2)
        return "FacilityCode ";
    else if ($i == 3)
        return "FacilityName ";
    else if ($i == 4)
        return "FTypeName ";
    else if ($i == 5)
        return "RegionName ";
    else if ($i == 6)
        return "DistrictName ";
    else if ($i == 7)
        return "OwnerTypeName ";
    else if ($i == 9)
        return "ServiceAreaName ";
    else if ($i == 9)
        return "PFacilityName ";
    else if ($i == 11)
        return "FacilityAddress ";
    else if ($i == 12)
        return "GroupName ";
//    else if ($i == 8)
//        return "AgentType ";
}

//echo '********'.$ItemGroupId;
if ($ItemGroupId > 0) {

    $sql = " SELECT SQL_CALC_FOUND_ROWS a.FacilityId, a.CountryId, a.RegionId, ParentFacilityId, 
             a.FTypeId, a.FLevelId, FacilityCode, FacilityName, FacilityAddress, FacilityPhone, FacilityFax, FacilityEmail, 
             FacilityManager, a.Latitude, a.Longitude, FacilityCount,$FLevelName FLevelName, FTypeName, RegionName,
             a.DistrictId, a.OwnerTypeId, a.ServiceAreaId, e.DistrictName, f.$OwnerTypeName OwnerTypeName, 
	     g.$ServiceAreaName ServiceAreaName,$getValue1,h.GroupName,cd.ChiefdomName,cd.ChiefdomId
             FROM t_facility a
             INNER JOIN t_facility_level b ON a.FLevelId = b.FLevelId
             INNER JOIN t_facility_type c ON a.FTypeId = c.FTypeId
             INNER JOIN t_region d ON a.RegionId = d.RegionId
             INNER JOIN t_districts e ON a.DistrictId = e.DistrictId
             INNER JOIN t_owner_type f ON a.OwnerTypeId = f.OwnerTypeId
             INNER JOIN t_service_area g ON a.ServiceAreaId = g.ServiceAreaId
             INNER JOIN t_chiefdom cd ON a.ChiefdomId  = cd.ChiefdomId
	     INNER JOIN (SELECT p.`FacilityId`,$getValue2,GROUP_CONCAT(q.$GroupName ORDER BY q.`ItemGroupId` ASC SEPARATOR ', ') GroupName
				 FROM t_facility_group_map p
				 INNER JOIN `t_itemgroup` q ON p.`ItemGroupId` = q.`ItemGroupId`
				 WHERE (p.ItemGroupId=$ItemGroupId OR $ItemGroupId=0) $WhereCond
				 GROUP BY p.`FacilityId`
			 ) h  ON a.`FacilityId` = h.`FacilityId`		           
             AND (a.RegionId = " . $ARegionId . " OR " . $ARegionId . " = 0) 
             AND (a.DistrictId = " . $DistrictId . " OR " . $DistrictId . " = 0) 
             AND (a.ChiefdomId = " . $ChiefdomId . " OR " . $ChiefdomId . " = 0) 
            " . $FacilityType . " " . $FacilityLevel . " 
             " . $sWhere . " ORDER BY FLevelName, FacilityCode ASC " . $sLimit . " ";
} else {
    $sql = "SELECT SQL_CALC_FOUND_ROWS a.FacilityId, a.CountryId, a.RegionId, ParentFacilityId,a.FTypeId, a.FLevelId, FacilityCode, FacilityName, FacilityAddress, FacilityPhone, FacilityFax, FacilityEmail, 
		FacilityManager, a.Latitude, a.Longitude, FacilityCount,$FLevelName FLevelName, FTypeName, RegionName,a.DistrictId, a.OwnerTypeId, a.ServiceAreaId, e.DistrictName, f.$OwnerTypeName OwnerTypeName, 
		g.$ServiceAreaName ServiceAreaName,$getValue1,h.GroupName,cd.ChiefdomName,cd.ChiefdomId
					 
     FROM t_facility a
     INNER JOIN t_facility_level b ON a.FLevelId = b.FLevelId
     INNER JOIN t_facility_type c ON a.FTypeId = c.FTypeId
     INNER JOIN t_region d ON a.RegionId = d.RegionId
     INNER JOIN t_districts e ON a.DistrictId = e.DistrictId
     INNER JOIN t_owner_type f ON a.OwnerTypeId = f.OwnerTypeId
     INNER JOIN t_service_area g ON a.ServiceAreaId = g.ServiceAreaId
     INNER JOIN t_chiefdom cd ON a.ChiefdomId  = cd.ChiefdomId
        INNER JOIN (SELECT p.`FacilityId`,$getValue2,GROUP_CONCAT(q.$GroupName ORDER BY q.`ItemGroupId` ASC SEPARATOR ', ') GroupName
				 FROM t_facility_group_map p
				 INNER JOIN `t_itemgroup` q ON p.`ItemGroupId` = q.`ItemGroupId`
				 WHERE (p.ItemGroupId=$ItemGroupId OR $ItemGroupId=0) $WhereCond
				 GROUP BY p.`FacilityId`
			 ) h  ON a.`FacilityId` = h.`FacilityId`
	 AND (a.RegionId = " . $ARegionId . " OR " . $ARegionId . " = 0) 
	 AND (a.DistrictId = " . $DistrictId . " OR " . $DistrictId . " = 0) 
         AND (a.ChiefdomId = " . $ChiefdomId . " OR " . $ChiefdomId . " = 0) 
	 " . $FacilityType . " " . $FacilityLevel . " 
	 " . $sWhere . " ORDER BY FLevelName, FacilityCode ASC " . $sLimit . " ";
}

//echo $sql;
mysql_query("SET character_set_results=utf8");
$r = mysql_query($sql);
$total = mysql_num_rows($r);
if ($total > 0) {
    require('../lib/PHPExcel.php');
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->SetCellValue('A2', SITETITLEENG);
    $styleThinBlackBorderOutline = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '16', 'bold' => true)), 'A2');

    $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');

    $objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Facility List');
    $styleThinBlackBorderOutline = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
    $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '14', 'bold' => true)), 'A3');

    $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');

    $objPHPExcel->getActiveSheet()->SetCellValue('A4', ($ItemGroupName . ' - ' . $RegionName . ' - ' . $DistrictName . ' - ' . $ChiefdomName . ' - ' . $FacilityTypeName . ' - ' . $FacilityLevelName . ' - ' . $AssignGroupName));
    $styleThinBlackBorderOutline = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
    $objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont();
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12')), 'A4');

    $objPHPExcel->getActiveSheet()->mergeCells('A4:H4');
    $objPHPExcel->getActiveSheet()
            ->SetCellValue('A6', 'SL#')
            ->SetCellValue('B6', 'Facility Code')
            ->SetCellValue('C6', 'Facility Name')
            ->SetCellValue('D6', 'Facility Type')
            ->SetCellValue('E6', 'Region Name')
            ->SetCellValue('F6', 'District Name')
            ->SetCellValue('G6', 'Chiefdom Name')
            ->SetCellValue('H6', 'Assigned Group');



    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'A6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'B6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'C6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'D6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'E6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'F6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'G6');
    $objPHPExcel->getActiveSheet()->duplicateStyleArray(array('font' => array('size' => '12', 'bold' => true)), 'H6');


    $objPHPExcel->getActiveSheet()->getStyle('A6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(52);



    $styleThinBlackBorderOutline = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
    $objPHPExcel->getActiveSheet()->getDefaultStyle('A7')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('A6' . ':A6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('B6' . ':B6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('B6')->getFont()->setBold(true);

    $i = 1;
    $j = 7;
    $tempGroupId = '';
    while ($rec = mysql_fetch_array($r)) {
        $styleThinBlackBorderOutline = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
        if ($tempGroupId != $rec['FLevelName']) {
            $styleThinBlackBorderOutline1 = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'),)),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'e8e8e8'),));

            $objPHPExcel->getActiveSheet()->mergeCells('A' . $j . ':H' . $j);
            $objPHPExcel->getActiveSheet()
                    ->SetCellValue('A' . $j, $rec['FLevelName']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $j . ':H' . $j)->applyFromArray($styleThinBlackBorderOutline1);
            $tempGroupId = $rec['FLevelName'];
            $j++;
        }

        if ($SubgroupId == 0) {
            $ARTLogistics = $rec['ARTLogistics'];
            $ARTPatient = $rec['ARTPatient'];
            $PMTCT = $rec['PMTCT'];
            $subgrouptext = '';
            if ($ARTLogistics == 1)
                $subgrouptext = 'ART Logistics';

            if ($ARTPatient == 1) {
                if ($subgrouptext != '')
                    $subgrouptext .= ', ';

                $subgrouptext .= 'ART Patient';
            }
            if ($PMTCT == 1) {
                if ($subgrouptext != '')
                    $subgrouptext .= ', ';

                $subgrouptext .= 'PMTCT';
            }
            if ($ARTLogistics == 1 || $ARTPatient == 1 || $PMTCT == 1) {
                $objPHPExcel->getActiveSheet()
                        ->SetCellValue('H' . $j, $rec['GroupName'] . '[' . $subgrouptext . ']');
            } else {
                $objPHPExcel->getActiveSheet()
                        ->SetCellValue('H' . $j, $rec['GroupName']);
            }
        } else {
            if ($SubgroupId == 1) {
                $ARTLogistics = $rec['ARTLogistics'];
            } else if ($SubgroupId == 2) {
                $ARTPatient = $rec['ARTPatient'];
            } else if ($SubgroupId == 3) {
                $PMTCT = $rec['PMTCT'];
            }
            if ($ARTLogistics == 1 || $ARTPatient == 1 || $PMTCT == 1) {
                $subgroupsingletext = '';
                if ($ARTLogistics == 1) {
                    $subgroupsingletext .= 'ART Logistics';
                } else {
                    $subgroupsingletext .= '';
                }
                if ($ARTPatient == 1) {
                    $subgroupsingletext .= 'ART Patient';
                } else {
                    $subgroupsingletext .= '';
                }
                if ($PMTCT == 1) {
                    $subgroupsingletext .= 'PMTCT';
                } else {
                    $subgroupsingletext .= '';
                }
                $objPHPExcel->getActiveSheet()
                        ->SetCellValue('H' . $j, $rec['GroupName'] . '[' . $subgroupsingletext . ']');
            } else {
                $objPHPExcel->getActiveSheet()
                        ->SetCellValue('H' . $j, $rec['GroupName']);
            }
        }
        $objPHPExcel->getActiveSheet()
                ->SetCellValue('A' . $j, $i)
                ->SetCellValue('B' . $j, $rec['FacilityCode'])
                ->SetCellValue('C' . $j, $rec['FacilityName'])
                ->SetCellValue('D' . $j, $rec['FTypeName'])
                ->SetCellValue('E' . $j, $rec['RegionName'])
                ->SetCellValue('F' . $j, $rec['DistrictName'])
                ->SetCellValue('G' . $j, $rec['ChiefdomName']);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $j . ':A' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $j . ':A' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $j . ':B' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('C' . $j . ':C' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('D' . $j . ':D' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('E' . $j . ':E' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('F' . $j . ':F' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('G' . $j . ':G' . $j)->applyFromArray($styleThinBlackBorderOutline);
        $i++;
        $j++;
    }
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('UTC');
    } else {
        putenv("TZ=UTC");
    }
    $exportTime = date("Y-m-d_His", time());
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $file = 'Regimen_' . $exportTime . '.xlsx';
    $objWriter->save(str_replace('.php', '.xlsx', 'media/' . $file));
    header('Location:media/' . $file);
} else {
    echo 'No record found';
}
?>