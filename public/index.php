<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Offre.php';
require_once __DIR__ . '/../Models/demande.php';


require_once __DIR__ . '/../Controller/OffreController.php';
require_once __DIR__ . '/../Controller/FrontControllerDemande.php';

require_once __DIR__ . '/../Controller/DashboardController.php';
require_once __DIR__ . '/../Controller/DashboardControllerDemande.php';


// ================= CONTROLLERS =================
$offreController = new OffreController();
$demandeController = new demandeController();

$dashboardController = new DashboardController();
$dashboardControllerd = new DashboardControllerd();

/// ================= ROUTER =================
$action = $_GET['action'] ?? 'home';

switch ($action) {

    // ================= HOME =================
    case 'home':
        require __DIR__ . '/../view/FrontOffice/home.php';
        break;


        case 'choicee':
include __DIR__ . '/../view/FrontOffice/offres_choice.php';    break;

 case 'choice':
include __DIR__ . '/../view/FrontOffice/demande_choice.php';    break;

    // ================= OFFRES  demandes =================

    //aff
    case 'list':
        $offreController->listOffre();
        break;


         case 'listd':
        $demandeController->listdem();
        break; 
        
        
        //add

    case 'add':
require __DIR__ . '/../view/FrontOffice/offre_add.php';       break;


  case 'add_demande':
require __DIR__ . '/../view/FrontOffice/demande_add.php';       break;





//ajoutt
    case 'ajout':
        $offreController->ajoutOffre();
        break;

        case 'ajoutd':
         $demandeController->ajoutd();
        break;



    case 'show':
        if (isset($_GET['id'])) {
            $offreController->showDetailsOffre($_GET['id']);
        } else {
            header("Location: index.php?action=list");
        }
        break;


          case 'showd':
        if (isset($_GET['id'])) {
             $demandeController->showDetailsDemande($_GET['id']);
        } else {
            header("Location: index.php?action=listd");
        }
        break;

    case 'edit':
        if (isset($_GET['id'])) {
            $offreController->editOffre($_GET['id']);
        } else {
            header("Location: index.php?action=list");
        }
        break;


        case 'editd':
        if (isset($_GET['id'])) {
            $demandeController->editDemande($_GET['id']);
        } else {
            header("Location: index.php?action=listd");
        }
        break;



    case 'update':
    $offreController->updateOffre();
    break;

case 'updatedem':
    $demandeController->updatedem();
    break;

    case 'delete':
        if (isset($_GET['id'])) {
            $offreController->deleteOffre($_GET['id']);
        } else {
            header("Location: index.php?action=list");
        }
        break;
case 'deleted':
        if (isset($_GET['id'])) {
            $demandeController->deleteDemande($_GET['id']);
        } else {
            header("Location: index.php?action=listd");
        }
        break;
        


   // ================= DASHBOARD =================
      case 'back':
 require __DIR__ . '/../view/BackOffice/back.php';
                break;
    case 'dashboard':
        $dashboardController->showDashboard();
        break;

         case 'dashboardd':
        $dashboardControllerd->showDashboardDemandes();
        break;

    // DELETE depuis dashboard
    case 'dashboard_delete':
        if (isset($_GET['id'])) {
            $dashboardController->deleteOffre($_GET['id']);
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

        case 'dashboard_delete_demande':
    if (isset($_GET['id'])) {
        $dashboardControllerd->deleteDemande($_GET['id']);
    } else {
        header("Location: index.php?action=dashboardd");
    }
    break;

    // BLOCK depuis dashboard
    case 'dashboard_block':
        if (isset($_GET['id'])) {
            $dashboardController->blockOffre($_GET['id']);
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

        case 'dashboard_block_demande':
    if (isset($_GET['id'])) {
        $dashboardControllerd->blockDemande((int)$_GET['id']);
    } else {
        header("Location: index.php?action=dashboardd");
    }
    break;


   case 'detailsoffre':
    if (isset($_GET['id'])) {
        $dashboardController->getOffreDetails($_GET['id']);
    }
    break;    


     case 'detailsdemande':
    if (isset($_GET['id'])) {
        $dashboardControllerd->getDemandeDetails($_GET['id']);
    }
    break;    

    // ================= DEFAULT =================
    default:
        require __DIR__ . '/../view/FrontOffice/home.php';
        break;
}