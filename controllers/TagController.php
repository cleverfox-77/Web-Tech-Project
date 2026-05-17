<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Tag.php';

class TagController {
    private $tagModel;

    public function __construct() {
        AuthMiddleware::checkModerator();
        $this->tagModel = new Tag();
    }

    public function index() {
        $data = array();
        $data['tags'] = $this->tagModel->getAllTagsWithStats();
        return $data;
    }

    public function handleAction() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../../views/moderator/tag_management.php");
            exit();
        }

        $action  = isset($_POST['action']) ? trim($_POST['action']) : '';
        $session = AuthMiddleware::getSession();
        $mod_id  = $session['user_id'];
        $error   = '';

        if ($action == 'add') {
            $name        = isset($_POST['name'])        ? trim($_POST['name'])        : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            if (empty($name)) {
                $error = "Tag name is required.";
            } else {
                $this->tagModel->addTag($name, $description, $mod_id);
                $_SESSION['success'] = "Tag added successfully.";
            }

        } elseif ($action == 'rename') {
            $id          = isset($_POST['tag_id'])      ? (int)$_POST['tag_id']      : 0;
            $name        = isset($_POST['name'])        ? trim($_POST['name'])        : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            if (empty($name) || $id == 0) {
                $error = "Tag name and ID are required.";
            } else {
                $this->tagModel->renameTag($id, $name, $description);
                $_SESSION['success'] = "Tag renamed successfully.";
            }

        } elseif ($action == 'merge') {
            $source_id = isset($_POST['source_id']) ? (int)$_POST['source_id'] : 0;
            $dest_id   = isset($_POST['dest_id'])   ? (int)$_POST['dest_id']   : 0;
            if ($source_id == 0 || $dest_id == 0 || $source_id == $dest_id) {
                $error = "Select two different tags to merge.";
            } else {
                $this->tagModel->mergeTags($source_id, $dest_id);
                $_SESSION['success'] = "Tags merged successfully.";
            }

        } elseif ($action == 'delete') {
            $id = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0;
            if ($id == 0) {
                $error = "Tag ID is required.";
            } else {
                $deleted = $this->tagModel->deleteTag($id);
                if (!$deleted) {
                    $error = "Cannot delete a tag that is still used by questions.";
                } else {
                    $_SESSION['success'] = "Tag deleted successfully.";
                }
            }
        }

        if ($error) {
            $_SESSION['error'] = $error;
        }

        header("Location: ../../views/moderator/tag_management.php");
        exit();
    }
}
?>
