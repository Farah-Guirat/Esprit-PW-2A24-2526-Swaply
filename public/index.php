<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Offre.php';

require_once __DIR__ . '/../Controller/OffreController.php';
require_once __DIR__ . '/../Controller/DashboardController.php';

// ================= CONTROLLERS =================
$offreController = new OffreController();
$dashboardController = new DashboardController();

// ================= ROUTER =================
$action = $_GET['action'] ?? 'home';

switch ($action) {

    // ================= HOME =================
    case 'home':
        require __DIR__ . '/../view/FrontOffice/home.php';
        break;


        case 'choicee':
include __DIR__ . '/../view/FrontOffice/offres_choice.php';    break;

    // ================= OFFRES =================
    case 'list':
        $offreController->listOffre();
        break;

    case 'add':
require __DIR__ . '/../view/FrontOffice/offre_add.php';       break;

    case 'store':
        $offreController->storeOffre();
        break;

    case 'show':
        if (isset($_GET['id'])) {
            $offreController->showDetailsOffre($_GET['id']);
        } else {
            header("Location: index.php?action=list");
        }
        break;

    case 'edit':
        if (isset($_GET['id'])) {
            $offreController->editOffre($_GET['id']);
        } else {
            header("Location: index.php?action=list");
        }
        break;

    case 'update':
        $offreController->updateOffre();
        break;

    case 'delete':
        if (isset($_GET['id'])) {
            $offreController->deleteOffre($_GET['id']);
        } else {
            header("Location: index.php?action=list");
        }
        break;


    // ================= DASHBOARD =================
    case 'dashboard':
        $dashboardController->showDashboard();
        break;

    // 👉 DELETE depuis dashboard
    case 'dashboard_delete':
        if (isset($_GET['id'])) {
            $dashboardController->deleteOffre($_GET['id']);
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

    // 👉 BLOCK depuis dashboard
    case 'dashboard_block':
        if (isset($_GET['id'])) {
            $dashboardController->blockOffre($_GET['id']);
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;


   case 'detailsoffre':
    if (isset($_GET['id'])) {
        $dashboardController->getOffreDetails($_GET['id']);
    }
    break;    

    // ================= DEFAULT =================
    default:
        require __DIR__ . '/../view/FrontOffice/home.php';
        break;
}