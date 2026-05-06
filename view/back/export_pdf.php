<?php
session_start();
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/User.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../front/login.php");
    exit();
}

require_once 'fpdf.php'; // Assumer que FPDF est dans le même répertoire

$database = new Database();
$conn = $database->connect();
$userModel = new User($conn);
$adminEmail = 'klai.aziz@admin.tn';
$users = $userModel->getAllUsersExceptAdmin($adminEmail);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

$logoPath = __DIR__ . '/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 10, 28);
}

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 118, 110);
$pdf->Cell(0, 10, 'Swaply - Liste des utilisateurs', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(102, 104, 139);
$pdf->Cell(0, 6, 'Export effectue le ' . date('d/m/Y') . ' A ' . date('H:i'), 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetDrawColor(46, 64, 83);
$pdf->SetLineWidth(0.4);
$pdf->Line(10, 32, 200, 32);
$pdf->Ln(4);

// En-tête de tableau
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(255, 255, 255);

$pdf->SetFillColor(20, 184, 166);
$pdf->Cell(45, 10, 'Nom', 1, 0, 'C', true);
$pdf->SetFillColor(12, 128, 102);
$pdf->Cell(45, 10, 'Prenom', 1, 0, 'C', true);
$pdf->SetFillColor(244, 121, 32);
$pdf->Cell(40, 10, 'Genre', 1, 0, 'C', true);
$pdf->SetFillColor(109, 40, 217);
$pdf->Cell(60, 10, 'Email', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

foreach ($users as $user) {
    $pdf->SetFillColor(225, 242, 249);
    $pdf->Cell(45, 10, utf8_decode($user['nom']), 1, 0, 'L', true);

    $pdf->SetFillColor(225, 249, 237);
    $pdf->Cell(45, 10, utf8_decode($user['prenom']), 1, 0, 'L', true);

    $pdf->SetFillColor(255, 242, 215);
    $pdf->Cell(40, 10, utf8_decode($user['genre']), 1, 0, 'C', true);

    $pdf->SetFillColor(237, 230, 255);
    $pdf->Cell(60, 10, utf8_decode($user['email']), 1, 1, 'L', true);
}

$pdf->Output('I', 'utilisateurs.pdf');
?>