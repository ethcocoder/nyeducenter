<?php
// Telegram-style Inbox: $conversations (array), $currentUser
?>
<style>
.msg-layout { display: flex; min-height: 70vh; background: #fff; border-radius: 1.5rem; box-shadow: 0 4px 24px 0 rgba(0,123,255,0.07); overflow: hidden; }
.msg-sidebar {
    width: 320px; min-width: 220px; max-width: 100%; background: #f7fafd; border-right: 1px solid #e3eaf1; padding: 1.2rem 0.5rem; display: flex; flex-direction: column;
}
.msg-sidebar .btn { margin-bottom: 1rem; }
.msg-chatlist { flex: 1 1 auto; overflow-y: auto; }
.msg-chatitem {
    display: flex; align-items: center; gap: 0.9rem; padding: 0.7rem 0.8rem; border-radius: 0.7rem; cursor: pointer; transition: background 0.15s;
}
.msg-chatitem.active, .msg-chatitem:hover { background: #eaf4ff; }
.msg-chatitem .avatar {
    width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: #e3eaf1; border: 2px solid #fff;
}
.msg-chatitem .group-badge { font-size: 1.1rem; margin-left: 0.2rem; color: #007bff; }
.msg-chatitem .chat-info { flex: 1 1 auto; min-width: 0; }
.msg-chatitem .chat-name { font-weight: 600; font-size: 1.08rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-chatitem .chat-last { color: #666; font-size: 0.97rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-chatitem .chat-meta { text-align: right; min-width: 60px; }
.msg-chatitem .unread-badge { background: #007bff; color: #fff; border-radius: 1rem; font-size: 0.85rem; padding: 0.1rem 0.6rem; margin-left: 0.2rem; }
@media (max-width: 900px) { .msg-layout { flex-direction: column; } .msg-sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e3eaf1; } }
</style>
<div class="container my-4">
    <div class="msg-layout">
        <div class="msg-sidebar">
            <div class="d-flex gap-2 mb-3">
                <a href="/messages/send" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-chat-dots"></i> New Chat</a>
                <a href="/messages/group/create" class="btn btn-outline-primary btn-sm flex-fill"><i class="bi bi-people"></i> New Group</a>
            </div>
            <div class="msg-chatlist">
                <?php if (empty($conversations)): ?>
                    <div class="text-center text-muted mt-5">No conversations yet.</div>
                <?php else: ?>
                    <?php foreach ($conversations as $chat): ?>
                        <a href="/messages?chat=<?= $chat['id'] ?>&type=<?= $chat['type'] ?>" class="msg-chatitem<?= !empty($chat['active']) ? ' active' : '' ?>">
                            <img src="<?= !empty($chat['avatar']) ? htmlspecialchars($chat['avatar']) : '/assets/default-profile.png' ?>" class="avatar">
                            <div class="chat-info">
                                <div class="chat-name">
                                    <?= htmlspecialchars($chat['name']) ?>
                                    <?php if ($chat['type'] === 'group'): ?><span class="group-badge" title="Group"><i class="bi bi-people-fill"></i></span><?php endif; ?>
                                </div>
                                <div class="chat-last"><?= htmlspecialchars($chat['last_message'] ?? '') ?></div>
                            </div>
                            <div class="chat-meta">
                                <div style="font-size:0.93rem; color:#888;">
                                    <?= !empty($chat['last_time']) ? htmlspecialchars($chat['last_time']) : '' ?>
                                </div>
                                <?php if (!empty($chat['unread_count'])): ?>
                                    <span class="unread-badge"><?= $chat['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex-fill d-flex align-items-center justify-content-center" style="min-height:400px;">
            <?php if (isset($contactOrGroup) && !empty($contactOrGroup)): ?>
                <div class="w-100" style="max-width:600px; margin:2rem auto;">
                    <div class="d-flex align-items-center mb-3 gap-3">
                        <img src="<?= !empty($contactOrGroup['profile_image']) ? htmlspecialchars($contactOrGroup['profile_image']) : '/assets/default-profile.png' ?>" alt="Contact" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                        <h4 class="mb-0" style="font-weight:700; font-size:1.3rem;">Conversation with <?= htmlspecialchars($contactOrGroup['name']) ?></h4>
                    </div>
                    <div class="mb-4" style="min-height:200px;">
                        <?php foreach ($conversation as $msg): ?>
                            <?php $isMe = $msg['sender_id'] == $currentUser['id']; ?>
                            <div class="d-flex mb-3 <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
                                <div class="p-2 rounded" style="background:<?= $isMe ? '#007bff' : '#f1f3f6' ?>; color:<?= $isMe ? '#fff' : '#222' ?>; max-width:70%;">
                                    <?= nl2br(htmlspecialchars($msg['body'])) ?>
                                    <div class="text-end" style="font-size:0.85rem; color:#eee;">
                                        <?= htmlspecialchars($msg['sender_name']) ?> · <?= date('M j, Y H:i', strtotime($msg['sent_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form method="post" action="/messages/send?receiver_id=<?= $contactOrGroup['id'] ?>&type=<?= $chatType ?>">
                        <div class="input-group">
                            <input type="text" name="body" class="form-control" placeholder="Type a message..." required>
                            <input type="hidden" name="receiver_id" value="<?= $contactOrGroup['id'] ?>">
                            <input type="hidden" name="receiver_type" value="<?= $chatType ?>">
                            <input type="hidden" name="subject" value="">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Send</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="bi bi-chat-dots" style="font-size:2.5rem;"></i>
                    <div class="mt-2">Select a conversation to start chatting</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 