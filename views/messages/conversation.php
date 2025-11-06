<?php
// Conversation View: $conversation, $contact, $currentUser
?>
<style>
.chat-container {
    max-width: 700px;
    margin: 2rem auto;
    background: #fff;
    border-radius: 1.5rem;
    box-shadow: 0 4px 24px 0 rgba(0,123,255,0.07);
    padding: 2rem 1.5rem 1.5rem 1.5rem;
}
.chat-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.chat-header img {
    width: 48px; height: 48px; border-radius: 50%; object-fit: cover;
    border: 2px solid #eee;
}
.chat-bubble {
    display: flex;
    align-items: flex-end;
    margin-bottom: 1.2rem;
}
.chat-bubble.left { flex-direction: row; }
.chat-bubble.right { flex-direction: row-reverse; }
.bubble {
    max-width: 70%;
    padding: 0.9rem 1.2rem;
    border-radius: 1.2rem;
    font-size: 1.05rem;
    position: relative;
    word-break: break-word;
}
.bubble.left {
    background: #f1f3f6;
    color: #222;
    border-bottom-left-radius: 0.3rem;
}
.bubble.right {
    background: #007bff;
    color: #fff;
    border-bottom-right-radius: 0.3rem;
}
.bubble-meta {
    font-size: 0.85rem;
    color: #888;
    margin: 0.2rem 0.7rem;
    min-width: 90px;
    text-align: right;
}
@media (max-width: 600px) {
    .chat-container { padding: 1rem 0.2rem; }
    .bubble { max-width: 90%; }
}
</style>
<div class="chat-container">
    <div class="chat-header">
        <img src="<?= $contact['profile_image'] ? htmlspecialchars($contact['profile_image']) : '/assets/default-profile.png' ?>" alt="Contact">
        <div>
            <h4 class="mb-0" style="font-weight:700; font-size:1.3rem;">Conversation with <?= htmlspecialchars($contact['name']) ?></h4>
            <a href="/messages/inbox" class="btn btn-outline-primary btn-sm mt-1">Back to Inbox</a>
        </div>
    </div>
    <?php if (empty($conversation)): ?>
        <div class="alert alert-info">No messages in this conversation.</div>
    <?php else: ?>
        <div class="mb-4">
            <?php foreach ($conversation as $msg): ?>
                <?php $isMe = $msg['sender_id'] == $currentUser['id']; ?>
                <div class="chat-bubble <?= $isMe ? 'right' : 'left' ?>">
                    <img src="<?= !empty($msg['sender_image']) ? htmlspecialchars($msg['sender_image']) : '/assets/default-profile.png' ?>" alt="Sender" class="rounded-circle me-2" style="width:36px;height:36px;">
                    <div>
                        <div class="bubble <?= $isMe ? 'right' : 'left' ?>">
                            <?php if ($msg['innovation_title']): ?>
                                <div class="mb-1"><small class="text-muted">Regarding: <?= htmlspecialchars($msg['innovation_title']) ?></small></div>
                            <?php endif; ?>
                            <div><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
                        </div>
                        <div class="bubble-meta">
                            <span><?= htmlspecialchars($msg['sender_name']) ?></span> ·
                            <span><?= date('M j, Y H:i', strtotime($msg['sent_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <a href="/messages/send?receiver_id=<?= $contact['id'] ?>" class="btn btn-primary"><i class="bi bi-reply"></i> Reply</a>
</div> 