<?php
// controllers/PublicationController.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Model/publication.php';

class PublicationController {
    
    public function handleRequest() {
        $database = new Database();
        $db = $database->getConnection();
        $publication = new Publication($db);
        $error = "";

        // SUPPRESSION
        if (isset($_POST['delete_id'])) {
            $id = $_POST['delete_id'];
            $publication->id_pub = $id;
            
            $stmt = $db->prepare("SELECT image FROM publications WHERE id_pub = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['image'])) {
                $images = explode(',', $row['image']);
                foreach ($images as $img) {
                    $filePath = __DIR__ . '/../' . trim($img);
                    if (file_exists($filePath)) unlink($filePath);
                }
            }

            if ($publication->delete()) {
                header("Location: listepublication.php");
                exit();
            }
        }

        // CRÉATION / MODIFICATION
        if (isset($_POST['submit_pub'])) {
            $nom_saisi = trim($_POST['nom_utilisateur'] ?? '');
            if (empty($nom_saisi)) {
                $error = "Le nom d'utilisateur est requis.";
                return $error;
            }
            $id_client = $publication->getOrCreateClient($nom_saisi);
            
            $publication->titre = trim($_POST['titre'] ?? '');
            if (empty($publication->titre)) {
                $error = "Le titre est requis.";
                return $error;
            }
            $publication->contenu = trim($_POST['contenu'] ?? '');
            if (empty($publication->contenu)) {
                $error = "Le contenu est requis.";
                return $error;
            }
            $publication->id_client = $id_client;

            $uploadedImages = [];
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $newFileName = 'uploads/' . uniqid() . '_' . $_FILES['images']['name'][$key];
                        if (move_uploaded_file($tmpName, __DIR__ . '/../' . $newFileName)) {
                            $uploadedImages[] = $newFileName;
                        }
                    }
                }
            }

            if (isset($_GET['id'])) {
                $publication->id_pub = $_GET['id'];
                $publication->image = !empty($uploadedImages) ? implode(',', $uploadedImages) : ($_POST['existing_images'] ?? '');
                if ($publication->update()) { header("Location: listepublication.php"); exit(); }
            } else {
                $publication->image = implode(',', $uploadedImages);
                if ($publication->create()) { header("Location: listepublication.php"); exit(); }
            }
        }
        return $error;
    }
}