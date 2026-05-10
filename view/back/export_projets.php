<?php
require_once('../../lib/tcpdf/tcpdf.php');
require_once('../../model/projet.php');

$p = new Projet();
$data = $p->getAll();

// Création PDF
$pdf = new TCPDF();
$pdf->SetCreator('Swaply');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Liste des projets');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

//  LOGO
$logo = '../../assets/logo.png'; // mets ton image ici
if(file_exists($logo)){
    $pdf->Image($logo, 80, 10, 50);
}

// Titre
$pdf->Ln(30);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 150, 136);
$pdf->Cell(0, 10, 'Liste des Projets - Swaply', 0, 1, 'C');

$pdf->Ln(10);

// Header tableau
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(0, 150, 136);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(20, 10, 'ID', 1, 0, 'C', 1);
$pdf->Cell(50, 10, 'Nom', 1, 0, 'C', 1);
$pdf->Cell(70, 10, 'Description', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Statut', 1, 1, 'C', 1);

// Contenu
$pdf->SetFont('helvetica', '', 10);

foreach($data as $row){

    if($row['statut'] == "Terminé"){
        $pdf->SetFillColor(200,255,200);
    } else {
        $pdf->SetFillColor(200,220,255);
    }

    $pdf->SetTextColor(0,0,0);

    $pdf->Cell(20, 10, $row['id_projet'], 1, 0, 'C', 1);
    $pdf->Cell(50, 10, $row['nom_projet'], 1, 0, 'L', 1);
    $pdf->Cell(70, 10, substr($row['description'],0,40), 1, 0, 'L', 1);
    $pdf->Cell(40, 10, $row['statut'], 1, 1, 'C', 1);
}

// Output
$pdf->Output('projets_swaply.pdf', 'I');
?>