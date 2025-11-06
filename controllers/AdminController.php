<?php
require_once 'BaseController.php';

class AdminController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }

    // Main admin dashboard
    public function index() {
        $stats = [
            'users' => $this->user->countAll(),
            'innovations' => $this->innovation->countAll(),
            'messages' => $this->message->countAll(),
        ];
        $currentUser = $this->getCurrentUser();
        $this->render('dashboard/admin', [
            'stats' => $stats,
            'currentUser' => $currentUser
        ]);
    }

    // User management: list/search/filter users
    public function users() {
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $users = $this->user->getAllWithFilters($search, $role, $status, $page);
        $currentUser = $this->getCurrentUser();
        $this->render('dashboard/admin_users', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'currentUser' => $currentUser
        ]);
    }

    // User management: view/edit user
    public function userView($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/users');
        $user = $this->user->find($id);
        if (!$user) $this->redirect('/admin/users');
        $currentUser = $this->getCurrentUser();
        $this->render('dashboard/admin_user_view', [
            'user' => $user,
            'currentUser' => $currentUser
        ]);
    }

    // User management: activate/deactivate
    public function userToggleStatus($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/users');
        $user = $this->user->find($id);
        if (!$user) $this->redirect('/admin/users');
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $this->user->update($id, ['status' => $newStatus]);
        $this->setFlash('success', 'User status updated.');
        $this->redirect('/admin/users');
    }

    // User management: delete
    public function userDelete($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/users');
        $this->user->delete($id);
        $this->setFlash('success', 'User deleted.');
        $this->redirect('/admin/users');
    }

    // Innovation management: list/search/filter
    public function innovations() {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $innovations = $this->innovation->getAllWithAdminFilters($search, $status, $page);
        $currentUser = $this->getCurrentUser();
        $this->render('dashboard/admin_innovations', [
            'innovations' => $innovations,
            'search' => $search,
            'status' => $status,
            'currentUser' => $currentUser
        ]);
    }

    // Innovation management: approve/reject
    public function innovationToggleStatus($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/innovations');
        $innovation = $this->innovation->find($id);
        if (!$innovation) $this->redirect('/admin/innovations');
        $newStatus = $innovation['status'] === 'approved' ? 'rejected' : 'approved';
        $this->innovation->update($id, ['status' => $newStatus]);
        $this->setFlash('success', 'Innovation status updated.');
        $this->redirect('/admin/innovations');
    }

    // Innovation management: delete
    public function innovationDelete($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/innovations');
        $this->innovation->delete($id);
        $this->setFlash('success', 'Innovation deleted.');
        $this->redirect('/admin/innovations');
    }

    // Message management: list/search/filter
    public function messages() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $messages = $this->message->getAllWithAdminFilters($search, $page);
        $currentUser = $this->getCurrentUser();
        $this->render('dashboard/admin_messages', [
            'messages' => $messages,
            'search' => $search,
            'currentUser' => $currentUser
        ]);
    }

    // Message management: view conversation
    public function messageView($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/messages');
        $message = $this->message->find($id);
        if (!$message) $this->redirect('/admin/messages');
        $conversation = $this->message->getConversation($message['sender_id'], $message['receiver_id']);
        $currentUser = $this->getCurrentUser();
        $this->render('messages/conversation', [
            'conversation' => $conversation,
            'contact' => $this->user->find($message['sender_id']),
            'currentUser' => $currentUser
        ]);
    }

    // Message management: delete
    public function messageDelete($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/messages');
        $this->message->delete($id);
        $this->setFlash('success', 'Message deleted.');
        $this->redirect('/admin/messages');
    }
} 