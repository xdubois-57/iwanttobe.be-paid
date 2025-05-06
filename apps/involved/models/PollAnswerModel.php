<?php
/**
 * PollAnswerModel – wraps POLL_ANSWERS table operations
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

class PollAnswerModel
{
    private DatabaseHelper $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    public function create(int $pollId, string $value): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB not connected');
            return false;
        }
        return $this->db->insert('POLL_ANSWERS', [
            'poll_id' => $pollId,
            'value' => $value,
            'votes' => 0
        ]);
    }

    public function getByPoll(int $pollId): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM POLL_ANSWERS WHERE poll_id = ? ORDER BY id ASC', [$pollId]);
        return $rows ?? [];
    }

    public function incrementVote(int $answerId): bool
    {
        if (!$this->db->isConnected()) {
            return false;
        }
        $stmt = $this->db->query('UPDATE POLL_ANSWERS SET votes = votes + 1 WHERE id = ?', [$answerId]);
        return $stmt !== false;
    }
}
