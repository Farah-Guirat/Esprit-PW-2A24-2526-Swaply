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
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Liste des Utilisateurs', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Nom', 1, 0, 'C');
$pdf->Cell(40, 10, 'Prenom', 1, 0, 'C');
$pdf->Cell(30, 10, 'Genre', 1, 0, 'C');
$pdf->Cell(60, 10, 'Email', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
foreach ($users as $user) {
    $pdf->Cell(40, 10, $user['nom'], 1, 0, 'L');
    $pdf->Cell(40, 10, $user['prenom'], 1, 0, 'L');
    $pdf->Cell(30, 10, $user['genre'], 1, 0, 'C');
    $pdf->Cell(60, 10, $user['email'], 1, 1, 'L');
}

$pdf->Output('I', 'utilisateurs.pdf');
?>