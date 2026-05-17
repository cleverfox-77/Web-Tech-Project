<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/ReportBadge.php';

AuthMiddleware::checkMember();

$session   = AuthMiddleware::getSession();
$user_id   = $session['user_id'];
$report    = new Report();

$entity_type = isset($_POST['entity_type']) ? trim($_POST['entity_type']) : '';
$entity_id   = isset($_POST['entity_id'])   ? (int)$_POST['entity_id']   : 0;
$reason      = isset($_POST['reason'])      ? trim($_POST['reason'])      : '';

if (empty($reason) || empty($entity_type) || $entity_id == 0) {
    $_SESSION['error'] = "All report fields are required.";
} else {
    $report->create($user_id, $entity_type, $entity_id, $reason);
    $_SESSION['success'] = "Report submitted. Moderators will review it.";
}

// Redirect back
$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/member/dashboard.php';
header("Location: " . $ref);
exit();
?>
