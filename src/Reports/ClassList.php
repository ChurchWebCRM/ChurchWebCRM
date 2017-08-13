<?php
/*******************************************************************************
*
*  filename    : Reports/ClassList.php
*  last change : 2003-08-30
*  description : Creates a PDF for a Sunday School Class List

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';
require '../Include/ReportFunctions.php';
require '../Include/GetGroupArray.php';

use ChurchCRM\Reports\ChurchInfoReport;
use ChurchCRM\dto\SystemConfig;
use ChurchCRM\Utils\InputUtils;

$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');
$iFYID = InputUtils::LegacyFilterInput($_GET['FYID'], 'int');
$dFirstSunday = InputUtils::LegacyFilterInput($_GET['FirstSunday']);
$dLastSunday = InputUtils::LegacyFilterInput($_GET['LastSunday']);

class PDF_ClassList extends ChurchInfoReport
{
    // Constructor
    public function PDF_ClassList()
    {
        parent::__construct('P', 'mm', $this->paperFormat);

        $this->SetMargins(0, 0);

        $this->SetFont('Times', '', 14);
        $this->SetAutoPageBreak(false);
        $this->AddPage();
    }
}

// Instantiate the directory class and build the report.
$pdf = new PDF_ClassList();

//Get the data on this group
$sSQL = 'SELECT * FROM group_grp WHERE grp_ID = '.$iGroupID;
$aGroupData = mysqli_fetch_array(RunQuery($sSQL));
extract($aGroupData);

$nameX = 20;
$birthdayX = 70;
$parentsX = 95;
$phoneX = 170;

$yTitle = 20;
$yTeachers = 26;
$yOffsetStartStudents = 6;
$yIncrement = 4;

$pdf->SetFont('Times', 'B', 16);

$pdf->WriteAt($nameX, $yTitle, ($grp_Name.' - '.$grp_Description));

$FYString = MakeFYString($iFYID);
$pdf->WriteAt($phoneX, $yTitle, $FYString);

$pdf->SetLineWidth(0.5);
$pdf->Line($nameX, $yTeachers - 0.75, 195, $yTeachers - 0.75);

$ga = GetGroupArray($iGroupID);
$numMembers = count($ga);

$teacherString1 = '';
$teacherString2 = '';
$teacherCount = 0;
$teachersThatFit = 4;

$bFirstTeacher1 = true;
$bFirstTeacher2 = true;
for ($row = 0; $row < $numMembers; $row++) {
    extract($ga[$row]);
    if ($lst_OptionName == gettext('Teacher')) {
        $phone = $pdf->StripPhone($fam_HomePhone);
        if ($teacherCount >= $teachersThatFit) {
            if (!$bFirstTeacher2) {
                $teacherString2 .= ', ';
            }
            $teacherString2 .= $per_FirstName.' '.$per_LastName.' '.$phone;
            $bFirstTeacher2 = false;
        } else {
            if (!$bFirstTeacher1) {
                $teacherString1 .= ', ';
            }
            $teacherString1 .= $per_FirstName.' '.$per_LastName.' '.$phone;
            $bFirstTeacher1 = false;
        }
        ++$teacherCount;
    }
}

$liaisonString = '';
for ($row = 0; $row < $numMembers; $row++) {
    extract($ga[$row]);
    if ($lst_OptionName == gettext('Liaison')) {
        $liaisonString .= gettext('Liaison').':'.$per_FirstName.' '.$per_LastName.' '.$fam_HomePhone.' ';
    }
}

$pdf->SetFont('Times', 'B', 10);

$y = $yTeachers;

$pdf->WriteAt($nameX, $y, $teacherString1);
$y += $yIncrement;

if ($teacherCount > $teachersThatFit) {
    $pdf->WriteAt($nameX, $y, $teacherString2);
    $y += $yIncrement;
}

$pdf->WriteAt($nameX, $y, $liaisonString);
$y += $yOffsetStartStudents;

$pdf->SetFont('Times', '', 12);
$prevStudentName = '';

for ($row = 0; $row < $numMembers; $row++) {
    extract($ga[$row]);

    if ($lst_OptionName == gettext('Student')) {
        $studentName = ($per_LastName.', '.$per_FirstName);

        if ($studentName != $prevStudentName) {
            $pdf->WriteAt($nameX, $y, $studentName);

            $birthdayStr = $per_BirthMonth.'-'.$per_BirthDay.'-'.$per_BirthYear;
            $pdf->WriteAt($birthdayX, $y, $birthdayStr);
        }

        $parentsStr = $pdf->MakeSalutation($fam_ID);
        $pdf->WriteAt($parentsX, $y, $parentsStr);

        $pdf->WriteAt($phoneX, $y, $pdf->StripPhone($fam_HomePhone));
        $y += $yIncrement;

        $addrStr = $fam_Address1;
        if ($fam_Address2 != '') {
            $addrStr .= ' '.$fam_Address2;
        }
        $addrStr .= ', '.$fam_City.', '.$fam_State.'  '.$fam_Zip;
        $pdf->WriteAt($parentsX, $y, $addrStr);

        $prevStudentName = $studentName;
        $y += 1.5 * $yIncrement;

        if ($y > 250) {
            $pdf->AddPage();
            $y = 20;
        }
    }
}

$pdf->SetFont('Times', 'B', 12);
$pdf->WriteAt($phoneX, $y, date('d-M-Y'));

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
if ($iPDFOutputType == 1) {
    $pdf->Output('ClassList'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
