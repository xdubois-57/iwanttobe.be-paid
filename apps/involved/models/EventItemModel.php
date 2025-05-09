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
     * @param string $type  The type of the event item (e.g., 'wordcloud', 'multiple_choice', etc.)
     * @return int|false  Inserted ID on success, false on failure
     */
    public function create(int $eventId, string $question, int $position = 0, string $type = 'wordcloud'): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }

        return $this->db->insert('EVENT_ITEM', [
            'event_id' => $eventId,
            'question' => $question,
            'position' => $position,
            'type' => $type
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

    /**
     * Update positions of multiple event items in the provided order (0-based).
     * @param int[] $orderedIds ordered array of event_item IDs
     * @return bool success
     */
    public function updatePositions(array $orderedIds): bool
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        
        $success = true;
        foreach ($orderedIds as $pos => $id) {
            $result = $this->db->query('UPDATE `EVENT_ITEM` SET `position` = ? WHERE `id` = ?', [$pos, $id]);
            if ($result === false) {
                $success = false;
                Logger::getInstance()->error('Failed to update position for event_item ' . $id . ' to ' . $pos . ': ' . $this->db->getErrorMessage());
            }
        }
        
        if ($success) {
            Logger::getInstance()->info('Updated positions for ' . count($orderedIds) . ' event items');
        }
        
        return $success;
    }
    
    /**
     * Get the maximum position value for event items in a given event.
     * 
     * @param int $eventId The event ID
     * @return int The maximum position value (0 if no items exist)
     */
    public function getMaxPositionForEvent(int $eventId): int
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return 0;
        }
        
        $result = $this->db->fetchOne('SELECT MAX(position) as max_position FROM EVENT_ITEM WHERE event_id = ?', [$eventId]);
        
        if ($result === false || $result['max_position'] === null) {
            return 0;
        }
        
        return (int)$result['max_position'];
    }
}
