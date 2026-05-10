<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/demande.php';
require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/../Services/AIService.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Entity\Demande;


class demandeController {

public function listdem(): void {

    $pdo = Database::getInstance();
     // Pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 4; // Number of offers per page
        $demset = ($page - 1) * $limit;

        // Get total count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM demandes");
        $total_offres = $stmt->fetch()['total'];
        $total_pages = ceil($total_offres / $limit);

        // Get paginated offers
        $stmt = $pdo->prepare("SELECT * FROM demandes LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $demset, PDO::PARAM_INT);
        $stmt->execute();

    
    $rows = $stmt->fetchAll();

    $demandes = [];

    foreach ($rows as $row) {

        $demande = new Demande(
            $row['titre'],
            $row['description'],
            $row['categorie'],
            $row['niveau'],
            $row['statut'],
            $row['date_creation'] ? new DateTime($row['date_creation']) : null,
            $row['id_u']
        );

        $demande->setIdDemande($row['id_demande']);

        $demandes[] = $demande;
    }

    require __DIR__ . '/../view/front/demande_list.php';
}



public function showDetailsDemande(int $id): void {

    $pdo = Database::getInstance();

    // Récupérer la demande avec les infos de l'utilisateur (JOINTURE)
    $stmt = $pdo->prepare("
        SELECT d.*, u.nom, u.prenom, u.email, u.photo, u.telephone, u.date_naissance 
        FROM demandes d
        LEFT JOIN utilisateurs u ON d.id_u = u.id_u
        WHERE d.id_demande = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Demande introuvable");
    }

    $demande = new \Entity\Demande(
        $row['titre'],
        $row['description'],
        $row['categorie'],
        $row['niveau'],
        $row['statut'],
        !empty($row['date_creation']) ? new \DateTime($row['date_creation']) : null,
        $row['id_u']
    );

    $demande->setIdDemande((int)$row['id_demande']);
    
    // Créer un tableau avec les infos de l'utilisateur
    $createur = [
        'id_u' => $row['id_u'],
        'nom' => $row['nom'],
        'prenom' => $row['prenom'],
        'email' => $row['email'],
        'photo' => $row['photo'],
        'telephone' => $row['telephone'],
        'date_naissance' => $row['date_naissance']
    ];
    


    require __DIR__ . '/../view/front/demande_details.php';
}

public function ajoutd(): void {

    session_start();

    $pdo = Database::getInstance();

    // 🔥 utilisateur connecté
    $id_u = $_SESSION['user']['id_u'];

    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $categorie = $_POST['categorie'] ?? '';
    $niveau = $_POST['niveau'] ?? '';

    // 1. insert demande + id_u
    $stmt = $pdo->prepare("
        INSERT INTO demandes
        (titre, description, categorie, niveau, statut, date_creation, id_u)
        VALUES (?, ?, ?, ?, 'active', CURDATE(), ?)
    ");

    $stmt->execute([
        $titre,
        $description,
        $categorie,
        $niveau,
        $id_u
    ]);

    $id = $pdo->lastInsertId();

    // 2. build text
    $text = AIService::buildText([
        'titre' => $titre,
        'description' => $description,
        'categorie' => $categorie,
        'niveau' => $niveau
    ]);

    // 3. embedding
    $embedding = AIService::getEmbedding($text);

    // 4. save embedding
    $stmt = $pdo->prepare("UPDATE demandes SET embedding=? WHERE id_demande=?");
    $stmt->execute([
        json_encode($embedding),
        $id
    ]);

    // 5. email
    $this->sendMail(
        "khalouiranim@gmail.com",
        "Nouvelle demande ajoutée",
        "
        <h2>Nouvelle demande</h2>
        <p><b>Titre:</b> $titre</p>
        <p><b>Description:</b> $description</p>
        <p><b>Categorie:</b> $categorie</p>
        <p><b>Niveau:</b> $niveau</p>
        "
    );

    header("Location: index.php?action=choice");
    exit;
}

public function updatedem(): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("
        UPDATE demandes 
        SET titre = ?, description = ?, categorie = ?, niveau = ?
        WHERE id_demande = ?
    ");

    $stmt->execute([
        $_POST['titre'],
        $_POST['description'],
        $_POST['categorie'],
        $_POST['niveau'],
        $_POST['id_demande']
    ]);

    //  rebuild text
    $text = AIService::buildText($_POST);

    //  new embedding
    $embedding = AIService::getEmbedding($text);

    //  save embedding
    $stmt = $pdo->prepare("UPDATE demandes SET embedding=? WHERE id_demande=?");
    $stmt->execute([
        json_encode($embedding),
        $_POST['id_demande']
    ]);

    header("Location: index.php?action=listd");
    exit;
}

public function editDemande(int $id): void {
    $demande = $this->getDemandeById($id);
    require __DIR__ . '/../view/front/demande_edit.php';
}


public function getDemandeById(int $id): array {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("SELECT * FROM demandes WHERE id_demande = ?");
    $stmt->execute([$id]);

    $demande = $stmt->fetch();

    if (!$demande) {
        die("Demande introuvable");
    }

    return $demande;
}



public function deleteDemande(int $id): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("DELETE FROM demandes WHERE id_demande = ?");
    $stmt->execute([$id]);

    header("Location: index.php?action=listd");
    exit;
}



private function sendMail($to, $subject, $body) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'neodrive76@gmail.com';
        $mail->Password = '';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        $mail->setFrom('neodrive76@gmail.com', 'Swaply');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();

    } catch (Exception $e) {
        echo "Mail error: " . $mail->ErrorInfo;
    }
}
}


