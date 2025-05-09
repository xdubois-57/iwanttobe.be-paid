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
     * Upsert an event answer: if it exists, increment votes; else insert new.
     * @param int $eventItemId
     * @param string $value
     * @param string $type
     * @return int|false ID of the affected/inserted row or false
     */
    public function upsertAnswer(int $eventItemId, string $value)
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        // Check if answer exists
        $row = $this->db->fetchOne(
            'SELECT id, votes FROM EVENT_ANSWERS WHERE event_item_id = ? AND value = ?',
            [$eventItemId, $value]
        );
        if ($row && isset($row['id'])) {
            // Increment votes
            $result = $this->db->update(
                'EVENT_ANSWERS',
                ['votes' => $row['votes'] + 1, 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$row['id']]
            );
            if ($result !== false) {
                Logger::getInstance()->info('Incremented votes for event_item_id=' . $eventItemId . ', value=' . json_encode($value));
                return $row['id'];
            } else {
                Logger::getInstance()->error('Failed to increment votes for event_item_id=' . $eventItemId . ', value=' . json_encode($value) . ' | SQL: UPDATE EVENT_ANSWERS SET votes = votes + 1 WHERE id = ' . $row['id'] . ' | Error: ' . $this->db->getErrorMessage());
                return false;
            }
        } else {
            // Insert new answer (no 'type' field)
            $insertData = [
                'event_item_id' => $eventItemId,
                'value' => $value,
                'votes' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $id = $this->db->insert('EVENT_ANSWERS', $insertData);
            if ($id !== false) {
                Logger::getInstance()->info('Inserted new answer for event_item_id=' . $eventItemId . ', value=' . json_encode($value));
                return $id;
            } else {
                Logger::getInstance()->error('Failed to insert answer for event_item_id=' . $eventItemId . ', value=' . json_encode($value) . ' | Insert data: ' . json_encode($insertData) . ' | Insert failed: ' . $this->db->getErrorMessage());
                return false;
            }
        }
    }

    /**
     * Get all answers for an event item
     * @param int $eventItemId
     * @return array list of answers
     */
    public function getByEventItem(int $eventItemId): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM EVENT_ANSWERS WHERE event_item_id = ? ORDER BY created_at DESC', [$eventItemId]);
        if ($rows === false) {
            Logger::getInstance()->error('Failed to fetch event answers: ' . $this->db->getErrorMessage() . ' | event_item_id=' . $eventItemId);
            return [];
        }
        return $rows;
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

    /**
     * Delete a single answer by its ID and event item ID
     * @param int $answerId
     * @param int $eventItemId
     * @return bool
     */
    public function deleteAnswer(int $answerId, int $eventItemId): bool
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB not connected when trying to delete answer');
            return false;
        }
        $result = $this->db->delete('EVENT_ANSWERS', 'id = ? AND event_item_id = ?', [$answerId, $eventItemId]);
        if ($result) {
            Logger::getInstance()->info('Deleted event answer', ['answer_id' => $answerId, 'event_item_id' => $eventItemId]);
        } else {
            Logger::getInstance()->error('Failed to delete event answer', ['answer_id' => $answerId, 'event_item_id' => $eventItemId, 'error' => $this->db->getErrorMessage()]);
        }
        return $result;
    }
}
