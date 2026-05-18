<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/Warning.php';

class ReportController {
    private $reportModel;
    private $contentModel;
    private $warningModel;

    public function __construct() {
        AuthMiddleware::checkModerator();
        $this->reportModel  = new Report();
        $this->contentModel = new Content();
        $this->warningModel = new Warning();
    }

    public function queue() {
        $data = array();
        $data['reports'] = $this->reportModel->getPendingReports();
        foreach ($data['reports'] as $key => $report) {
            $data['reports'][$key]['content'] = $this->contentModel->getContentByReport(
                $report['entity_type'], $report['entity_id']
            );
        }
        return $data;
    }

    public function handleAction() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/moderator/report_queue.php");
            exit();
        }

        $report_id   = isset($_POST['report_id'])   ? (int)$_POST['report_id']   : 0;
        $action      = isset($_POST['action'])       ? trim($_POST['action'])      : '';
        $entity_type = isset($_POST['entity_type'])  ? trim($_POST['entity_type']) : '';
        $entity_id   = isset($_POST['entity_id'])    ? (int)$_POST['entity_id']   : 0;
        $note        = isset($_POST['moderator_note']) ? trim($_POST['moderator_note']) : '';

        $session = AuthMiddleware::getSession();
        $mod_id  = $session['user_id'];

        $error = '';

        if ($action == 'dismiss') {
            $this->reportModel->updateReportStatus($report_id, 'dismissed', $note);

        } elseif ($action == 'edit') {
            if ($entity_type == 'question') {
                $title = isset($_POST['title']) ? trim($_POST['title']) : '';
                $body  = isset($_POST['body'])  ? trim($_POST['body'])  : '';
                if (empty($title) || empty($body)) {
                    $error = "Title and body are required.";
                } else {
                    $this->contentModel->editQuestion($entity_id, $title, $body, $mod_id);
                    $this->reportModel->updateReportStatus($report_id, 'resolved', $note);
                }
            } elseif ($entity_type == 'answer') {
                $body = isset($_POST['body']) ? trim($_POST['body']) : '';
                if (empty($body)) {
                    $error = "Body is required.";
                } else {
                    $this->contentModel->editAnswer($entity_id, $body, $mod_id);
                    $this->reportModel->updateReportStatus($report_id, 'resolved', $note);
                }
            } elseif ($entity_type == 'comment') {
                $body = isset($_POST['body']) ? trim($_POST['body']) : '';
                if (empty($body)) {
                    $error = "Body is required.";
                } else {
                    $this->contentModel->editComment($entity_id, $body, $mod_id);
                    $this->reportModel->updateReportStatus($report_id, 'resolved', $note);
                }
            }

        } elseif ($action == 'delete') {
            $deleted = false;
            if ($entity_type == 'question') {
                $deleted = $this->contentModel->deleteQuestion($entity_id);
                if (!$deleted) {
                    $error = "Cannot delete a question that has an accepted answer. Close it instead.";
                }
            } elseif ($entity_type == 'answer') {
                $deleted = $this->contentModel->deleteAnswer($entity_id);
            } elseif ($entity_type == 'comment') {
                $deleted = $this->contentModel->deleteComment($entity_id);
            }
            if ($deleted) {
                $delete_note = $note ? $note . ' [deleted]' : '[deleted]';
                $this->reportModel->updateReportStatus($report_id, 'resolved', $delete_note);
            }

        } elseif ($action == 'warn') {
            $author_id  = isset($_POST['author_id']) ? (int)$_POST['author_id'] : 0;
            $warn_reason = isset($_POST['warn_reason']) ? trim($_POST['warn_reason']) : '';
            if (empty($warn_reason)) {
                $error = "Warning reason is required.";
            } else {
                $this->warningModel->issueWarning($author_id, $mod_id, $warn_reason);
                $this->reportModel->updateReportStatus($report_id, 'resolved', $note);
            }

        } elseif ($action == 'close') {
            $close_reason = isset($_POST['close_reason']) ? trim($_POST['close_reason']) : '';
            if (empty($close_reason)) {
                $error = "Close reason is required.";
            } else {
                $this->contentModel->closeQuestion($entity_id, $close_reason);
                $this->reportModel->updateReportStatus($report_id, 'resolved', $note);
            }
        }

        if ($error) {
            $_SESSION['error'] = $error;
        } else {
            $_SESSION['success'] = "Action completed successfully.";
        }

        header("Location: ../../views/moderator/report_queue.php");
        exit();
    }
}
?>
