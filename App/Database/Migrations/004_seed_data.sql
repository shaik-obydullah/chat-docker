INSERT INTO users (id, email, password, name, avatar_color, status) VALUES
(1, 'you@chat.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'You', '#6366f1', 'online'),
(2, 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', '#8b5cf6', 'online'),
(3, 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Chen', '#06b6d4', 'online'),
(4, 'emma@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma Williams', '#f43f5e', 'away'),
(5, 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex Rodriguez', '#f97316', 'offline'),
(6, 'olivia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Olivia Brown', '#10b981', 'online');

INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES
(2, 1, 'Hey! How have you been?', NOW() - INTERVAL 30 MINUTE),
(1, 2, 'I\'m good, thanks! How about you?', NOW() - INTERVAL 25 MINUTE),
(2, 1, 'Doing great! Want to grab coffee this weekend?', NOW() - INTERVAL 20 MINUTE),
(3, 1, 'The project is almost done', NOW() - INTERVAL 2 HOUR),
(1, 3, 'Great work! Let me review it', NOW() - INTERVAL 1 HOUR),
(4, 1, 'Thanks for the help yesterday!', NOW() - INTERVAL 3 HOUR),
(1, 4, 'No problem at all!', NOW() - INTERVAL 2 HOUR),
(5, 1, 'Can we reschedule our meeting?', NOW() - INTERVAL 1 DAY),
(1, 5, 'Sure, what time works for you?', NOW() - INTERVAL 23 HOUR),
(6, 1, 'I\'m heading to the party now', NOW() - INTERVAL 12 HOUR);
