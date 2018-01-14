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
$dResult = mysql_query($sql);
$aData = array();

$total = mysql_num_rows($dResult);
$i = 1;
if ($total > 0) {

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
                        <link rel="stylesheet" type="text/css" href="' . $baseUrl . '/css/bootstrap.min.css"/>
                        <link rel="stylesheet" type="text/css" href="' . $baseUrl . '/css/global-custom.css"/>
                        <link rel="stylesheet" type="text/css" href="' . $baseUrl . '/css/font-awesome/css/font-awesome.min.css"/>
                        <link type="text/css" href="' . $baseUrl . '/css/jquery-ui-1.8.12.custom.css" rel="Stylesheet" />
                        <link type="text/css" href="' . $baseUrl . '/css/datatables.min.css" rel="Stylesheet" />

<style type="text/css">
body{
	color: #727272;
	font: 14px/23px "Open Sans",sans-serif;
}
h1, h2, h3, h4, h5, h6 {margin-top: 10px;}
.panel-heading {margin-top: 20px;}
.center-aln { text-align:center;}
.left-aln { text-align: left;}
.right-aln { text-align:right;}
.groupbg {background-color:#e8e8e8 !important;}
</style>			 
</head>
<body>
<div class="container">
	<div class="content_fullwidth lessmar">
	<div class="azp_col-md-12 one_full">
        <div class="row">
	<div class="col-md-12 col-sm-12 col-sx-12">
        <div id="cparams-panel" class="panel panel-default">';
    echo '<div class="panel-heading clearfix">
	<h2 class="center-aln">' . SITETITLEENG . '</h2>
	<h3 class="center-aln">Facility List</h3>
	<h4 class="center-aln">' . $ItemGroupName . ' - ' . $RegionName . ' - ' . $DistrictName . ' - ' . $ChiefdomName . ' - ' . $FacilityTypeName . ' - ' . $FacilityLevelName . ' - ' . $AssignGroupName . '<h4>
</div>';

    echo '<div class="panel-body">
        <div class="clearfix list-panel" >
        <table class="table table-striped table-bordered display" cellspacing="0">
        <thead>';
    echo '<tr>
	<th class="center-aln">SL#</th> 
	<th class="left-aln">Facility Code</th>
        <th class="left-aln">Facility Name</th> 
	<th class="left-aln">Facility Type</th>
        <th class="left-aln">Region Name</th> 
	<th class="left-aln">District Name</th>
        <th class="left-aln">Chiefdom Name</th> 
	<th class="left-aln">Assigned Group</th>
        ';

    echo ' </tr>';
    echo '</thead><tbody>';
    $tempGroupId = '';
    while ($row = mysql_fetch_array($dResult)) {
        if ($tempGroupId != $row['FLevelName']) {
            echo'<tr>
	    <td class="left-aln" colspan="8">' . $row['FLevelName'] . '</td>
            </tr>';
            $tempGroupId = $row['FLevelName'];
        }

        echo '<tr>
		<td style="text-align: center;">' . $i . '</td>
		<td>' . $row['FacilityCode'] . '</td>
                 <td>' . $row['FacilityName'] . '</td>
                 <td>' . $row['FTypeName'] . '</td>
                 <td>' . $row['RegionName'] . '</td>
                 <td>' . $row['DistrictName'] . '</td>
                 <td>' . $row['ChiefdomName'] . '</td>';


        if ($SubgroupId == 0) {
            $ARTLogistics = $row['ARTLogistics'];
            $ARTPatient = $row['ARTPatient'];
            $PMTCT = $row['PMTCT'];
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
            if ($ARTLogistics == 1 || $ARTPatient == 1 || $PMTCT == 1)
                echo '<td>' . $group_name = $row['GroupName'] . '[' . $subgrouptext . ']' . '</td>';
            else
                echo '<td>' . $group_name = $row['GroupName'] . '</td>';
        } else {
            if ($SubgroupId == 1) {
                $ARTLogistics = $row['ARTLogistics'];
            } else if ($SubgroupId == 2) {
                $ARTPatient = $row['ARTPatient'];
            } else if ($SubgroupId == 3) {
                $PMTCT = $row['PMTCT'];
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
                echo '<td>' . $group_name = $row['GroupName'] . '[' . $subgroupsingletext . ']' . '</td>';
            } else {
                echo '<td>' . $group_name = $row['GroupName'] . '</td>';
            }
        }
        echo '</tr>';
        $i++;
    }
//    $tempGroupId = '';
//    while ($rec = mysql_fetch_array($dResult)) {
//
//        if ($tempGroupId != $rec['FormulationName']) {
//            echo'<tr>
//	    <td class="left-aln" colspan="2">' . $rec['FormulationName'] . '</td>
//            </tr>';
//            $tempGroupId = $rec['FormulationName'];
//        }
//
//        echo '<tr>
//		<td style="text-align: center;">' . $i . '</td>
//		<td>' . $rec['RegimenName'] . '</td>
//		</tr>';
//        $i++;
//    }
    echo '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
</body>
</html>';
} else {
    echo 'No record found';
}
?>