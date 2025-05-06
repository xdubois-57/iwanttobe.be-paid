<?php
/**
 * EventItemModel – wraps EVENT_ITEM table operations for the Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

class EventItemModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Create a new event item.
     *
     * @param int $eventId  The parent event ID
     * @param string $question  The question text
     * @param int $position  Position/order among other items (optional)
     * @return int|false  Inserted ID on success, false on failure
     */
    public function create(int $eventId, string $question, int $position = 0): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }

        return $this->db->insert('EVENT_ITEM', [
            'event_id' => $eventId,
            'question' => $question,
            'position' => $position,
        ]);
    }

    /**
     * Fetch an event item by its ID.
     */
    public function getById(int $id): ?array
    {
        $row = $this->db->fetchOne('SELECT * FROM EVENT_ITEM WHERE id = ?', [$id]);
        return $row === false ? null : $row;
    }

    /**
     * Fetch all event items for a given event, ordered by position.
     *
     * @param int $eventId
     * @return array
     */
    public function getByEvent(int $eventId): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM EVENT_ITEM WHERE event_id = ? ORDER BY position ASC, created_at ASC', [$eventId]);
        return $rows ?? [];
    }

    /**
     * Delete an event item by ID.
     * @param int $id
     * @return bool success
     */
    public function delete(int $id): bool
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        return $this->db->delete('EVENT_ITEM', 'id = ?', [$id]);
    }
}
