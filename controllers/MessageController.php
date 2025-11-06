<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Innovation.php';

class MessageController extends BaseController {
    protected $message;
    protected $user;
    protected $innovation;

    public function __construct() {
        parent::__construct();
        $this->message = new Message();
        $this->user = new User();
        $this->innovation = new Innovation();
    }

    // Inbox
    public function inbox() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        // Gather all private chats
        $privateChats = $this->message->getPrivateConversations($currentUser['id']);
        // Gather all group chats
        $groupChats = $this->message->getGroupConversations($currentUser['id']);
        $conversations = [];
        foreach ($privateChats as $chat) {
            $conversations[] = [
                'type' => 'private',
                'id' => $chat['contact_id'],
                'name' => $chat['contact_name'],
                'avatar' => !empty($chat['contact_image']) ? $chat['contact_image'] : '/assets/default-profile.png',
                'last_message' => $chat['last_message'],
                'last_time' => $chat['last_time'],
                'unread_count' => $chat['unread_count'],
                'active' => false
            ];
        }
        foreach ($groupChats as $chat) {
            $conversations[] = [
                'type' => 'group',
                'id' => $chat['group_id'],
                'name' => $chat['group_name'],
                'avatar' => !empty($chat['group_image']) ? $chat['group_image'] : '/assets/default-profile.png',
                'last_message' => $chat['last_message'],
                'last_time' => $chat['last_time'],
                'unread_count' => $chat['unread_count'],
                'active' => false
            ];
        }
        // Sort by last_time desc
        usort($conversations, function($a, $b) {
            $aTime = $a['last_time'] ? strtotime($a['last_time']) : 0;
            $bTime = $b['last_time'] ? strtotime($b['last_time']) : 0;
            return $bTime <=> $aTime;
        });

        // Unified: check for selected chat
        $selectedChat = null;
        $conversation = [];
        $contactOrGroup = null;
        $chatId = $_GET['chat'] ?? null;
        $chatType = $_GET['type'] ?? null;
        if ($chatId && $chatType === 'private') {
            $contactOrGroup = $this->user->find($chatId);
            if ($contactOrGroup) {
                $conversation = $this->message->getConversation($currentUser['id'], $chatId);
                // Mark as active in chat list
                foreach ($conversations as &$c) {
                    if ($c['type'] === 'private' && $c['id'] == $chatId) $c['active'] = true;
                }
            }
        } else if ($chatId && $chatType === 'group') {
            $contactOrGroup = $this->message->getGroup($chatId);
            if ($contactOrGroup) {
                // Map group fields to match the view's expectations
                $contactOrGroup['profile_image'] = $contactOrGroup['image'];
                $contactOrGroup['name'] = $contactOrGroup['name'];
                $conversation = $this->message->getGroupConversation($chatId);
                foreach ($conversations as &$c) {
                    if ($c['type'] === 'group' && $c['id'] == $chatId) $c['active'] = true;
                }
            }
        }

        $this->render('messages/inbox', [
            'conversations' => $conversations,
            'currentUser' => $currentUser,
            'conversation' => $conversation,
            'contactOrGroup' => $contactOrGroup,
            'chatType' => $chatType
        ]);
    }

    // Sent
    public function sent() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $sent = $this->message->getSent($currentUser['id'], $page);
        $this->render('messages/sent', [
            'messages' => $sent,
            'currentUser' => $currentUser
        ]);
    }

    // View conversation with another user
    public function conversation($contactId = null) {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!$contactId) {
            $contactId = $_GET['contact_id'] ?? null;
        }
        if (!$contactId) {
            $this->redirect('/messages/inbox');
        }
        $contact = $this->user->find($contactId);
        if (!$contact) {
            $this->redirect('/messages/inbox');
        }
        $conversation = $this->message->getConversation($currentUser['id'], $contactId);
        $this->render('messages/conversation', [
            'conversation' => $conversation,
            'contact' => $contact,
            'currentUser' => $currentUser
        ]);
    }

    // Send message (GET: show form, POST: send)
    public function send($receiverId = null, $innovationId = null) {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();

        // Always check for receiver_id and innovation_id in GET if not provided
        if (!$receiverId) {
            $receiverId = $_GET['receiver_id'] ?? null;
        }
        if (!$innovationId) {
            $innovationId = $_GET['innovation_id'] ?? null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverType = $_POST['receiver_type'] ?? ($_GET['type'] ?? 'user');
            $data = [
                'sender_id' => $currentUser['id'],
                'receiver_id' => $_POST['receiver_id'] ?? null,
                'receiver_type' => $receiverType,
                'innovation_id' => $_POST['innovation_id'] ?? null,
                'subject' => trim($_POST['subject'] ?? ''),
                'body' => trim($_POST['body'] ?? '')
            ];
            $errors = $this->validate($data, [
                'receiver_id' => 'required',
                'subject' => 'max:255',
                'body' => 'required|min:1'
            ]);
            if (empty($data['subject'])) {
                $data['subject'] = 'Message';
            }
            if (empty($errors)) {
                $this->message->send($data);
                $this->setFlash('success', 'Message sent successfully!');
                $redirectType = $receiverType === 'group' ? 'group' : 'private';
                $this->redirect('/messages?chat=' . $data['receiver_id'] . '&type=' . $redirectType);
            } else {
                $receiver = $this->user->find($data['receiver_id']);
                $innovation = $data['innovation_id'] ? $this->innovation->find($data['innovation_id']) : null;
                $this->render('messages/send', [
                    'errors' => $errors,
                    'data' => $data,
                    'receiver' => $receiver,
                    'innovation' => $innovation,
                    'currentUser' => $currentUser
                ]);
            }
        } else {
            if (!$receiverId && isset($_GET['contact_admin'])) {
                // Show admin selection dropdown
                $admins = $this->user->getAdmins();
                $this->render('messages/send', [
                    'admins' => $admins,
                    'currentUser' => $currentUser
                ]);
                return;
            }
            if (!$receiverId) {
                // Show user selection view
                $users = $this->user->getAll();
                $this->render('messages/select_user', [
                    'users' => $users,
                    'currentUser' => $currentUser
                ]);
                return;
            }
            $receiver = $this->user->find($receiverId);
            if (!$receiver) {
                $this->redirect('/innovations');
            }
            $innovation = $innovationId ? $this->innovation->find($innovationId) : null;
            $this->render('messages/send', [
                'receiver' => $receiver,
                'innovation' => $innovation,
                'currentUser' => $currentUser
            ]);
        }
    }

    // Mark message as read (AJAX or redirect)
    public function markAsRead($messageId = null) {
        $this->requireLogin();
        if (!$messageId) {
            $messageId = $_GET['id'] ?? null;
        }
        if ($messageId) {
            $this->message->markAsRead($messageId);
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            $this->redirect('/messages/inbox');
        }
    }

    // Show group chat creation form
    public function createGroup() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        $users = $this->user->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupName = trim($_POST['group_name'] ?? '');
            $memberIds = $_POST['members'] ?? [];
            $errors = [];

            if (!$groupName) {
                $errors[] = 'Group name is required.';
            }
            if (empty($memberIds)) {
                $errors[] = 'Please select at least one member.';
            }

            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['group_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $target = __DIR__ . '/../uploads/profiles/' . $filename;
                if (move_uploaded_file($_FILES['group_image']['tmp_name'], $target)) {
                    $imagePath = '/uploads/profiles/' . $filename;
                }
            }

            if (empty($errors)) {
                $groupId = $this->message->createGroup($groupName, $currentUser['id'], $imagePath);
                $this->message->addGroupMembers($groupId, $memberIds);
                // Add creator as a member if not already
                if (!in_array($currentUser['id'], $memberIds)) {
                    $this->message->addGroupMembers($groupId, [$currentUser['id']]);
                }
                $this->setFlash('success', 'Group created successfully!');
                $this->redirect('/messages');
                return;
            } else {
                $this->render('messages/group_create', [
                    'currentUser' => $currentUser,
                    'users' => $users,
                    'errors' => $errors,
                    'old' => $_POST
                ]);
                return;
            }
        }
        // GET: show form
        $this->render('messages/group_create', [
            'currentUser' => $currentUser,
            'users' => $users
        ]);
    }
} 