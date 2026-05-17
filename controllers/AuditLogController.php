<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AuditLogController {
    private $auditLog;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->auditLog = new AuditLog();
    }

    public function index() {
        $data = array();
        $data['logs']  = $this->auditLog->getAll();
        $data['total'] = $this->auditLog->getCount();
        return $data;
    }
}
?>
