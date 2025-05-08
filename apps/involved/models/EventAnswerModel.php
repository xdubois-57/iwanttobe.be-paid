<?php
/**
 * EventAnswerModel – wraps EVENT_ANSWERS table operations for Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

class EventAnswerModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Create a new event answer
     * @param int $eventItemId
     * @param string $answer
     * @param string $type
     * @return int|false inserted ID or false
     */
    public function create(int $eventItemId, string $answer, string $type = 'text'): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        return $this->db->insert('EVENT_ANSWERS', [
            'event_item_id' => $eventItemId,
            'answer' => $answer,
            'type' => $type,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all answers for an event item
     * @param int $eventItemId
     * @return array list of answers
     */
    public function getByEventItem(int $eventItemId): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM EVENT_ANSWERS WHERE event_item_id = ? ORDER BY created_at DESC', [$eventItemId]);
        return $rows ?? [];
    }

    /**
     * Delete answers for an event item
     * @param int $eventItemId
     * @return bool success
     */
    public function deleteByEventItem(int $eventItemId): bool
    {
        return $this->db->delete('EVENT_ANSWERS', 'event_item_id = ?', [$eventItemId]);
    }

    /**
     * Get answer count by type for an event item
     * @param int $eventItemId
     * @return array count by type
     */
    public function getAnswerCountByType(int $eventItemId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT type, COUNT(*) as count FROM EVENT_ANSWERS WHERE event_item_id = ? GROUP BY type',
            [$eventItemId]
        );
        return $rows ?? [];
    }
}
