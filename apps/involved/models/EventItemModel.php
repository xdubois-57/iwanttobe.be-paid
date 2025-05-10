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

    /**
     * Append an emoji to the emoji_queue for a specific event item
     * @param int $eventItemId
     * @param string $emoji
     * @return bool
     */
    public function appendEmojiToEventItem(int $eventItemId, string $emoji): bool
    {
        Logger::getInstance()->info('[appendEmojiToEventItem] Called with eventItemId: ' . $eventItemId . ', emoji: ' . $emoji);
        if (!$this->db->isConnected()) {
            $err = $this->db->getErrorMessage();
            Logger::getInstance()->error('[appendEmojiToEventItem] DB connection failed: ' . $err);
            return false;
        }
        $this->db->beginTransaction();
        try {
            $row = $this->db->fetchOne('SELECT emoji_queue FROM EVENT_ITEM WHERE id = ?', [$eventItemId]);
            $queue = ($row && $row['emoji_queue']) ? json_decode($row['emoji_queue'], true) : [];
            if (!is_array($queue)) {
                $queue = [];
            }
            $queue[] = $emoji;
            $this->db->query('UPDATE EVENT_ITEM SET emoji_queue = ? WHERE id = ?', [json_encode($queue), $eventItemId]);
            $this->db->commit();
            Logger::getInstance()->info('[appendEmojiToEventItem] Commit success');
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::getInstance()->error('[appendEmojiToEventItem] Exception: ' . $e->getMessage());
            Logger::getInstance()->error('Failed to append emoji to event item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active presence count
     */
    public function getActivePresenceCount(string $code, ?int $itemId = null): int {
        // If itemId provided, restrict to that item (future feature). For now event-level.
        $result = $this->db->fetchValue('SELECT COUNT(*) AS c FROM event_presence WHERE event_code = ? AND updated_at > (NOW() - INTERVAL 60 SECOND)', [$code]);
        return intval($result ?? 0);
    }

    /**
     * Get queued emojis for an item (consume if needed)
     */
    public function getEmojiQueue(int $itemId): array {
        // Fetch queued emojis in a transaction
        try {
            $this->db->query('START TRANSACTION');
            
            $rows = $this->db->fetchAll('SELECT id, emoji FROM event_item_emoji_queue WHERE event_item_id = ? ORDER BY id ASC LIMIT 20', [$itemId]);
            if (!$rows) {
                $this->db->query('COMMIT');
                return [];
            }
            
            $ids = array_column($rows, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // Delete fetched rows using placeholders for safety
            $this->db->query("DELETE FROM event_item_emoji_queue WHERE id IN ($placeholders)", $ids);
            $this->db->query('COMMIT');
            
            return array_column($rows, 'emoji');
        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            Logger::getInstance()->error('Failed to get emoji queue: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get word counts for item (supports wordcloud and poll answers)
     */
    public function getItemWordCounts(int $itemId): array {
        // For wordcloud, words are stored in event_answers. For polls they are in poll_values.
        $rows = $this->db->fetchAll('SELECT answer AS word, COUNT(*) AS c FROM event_answers WHERE event_item_id = ? GROUP BY answer', [$itemId]);
        if ($rows === false) {
            Logger::getInstance()->warning('Failed to fetch event answers for event item ' . $itemId);
            // Continue to try poll values instead of returning empty
        } else if (!empty($rows)) {
            return array_map(fn($r) => ['word' => $r['word'], 'count' => intval($r['c'])], $rows);
        }
        // fallback poll values
        $rows = $this->db->fetchAll('SELECT value AS word, COUNT(*) AS c FROM poll_values WHERE event_item_id = ? GROUP BY value', [$itemId]);
        if ($rows === false) {
            Logger::getInstance()->warning('Failed to fetch poll values for event item ' . $itemId);
            return [];
        }
        return array_map(fn($r) => ['word' => $r['word'], 'count' => intval($r['c'])], $rows);
    }

    /**
     * Get active URL for QR block (possibly stored in event_item table)
     */
    public function getActiveUrl(string $code, int $itemId): ?string {
        $row = $this->db->fetchOne('SELECT active_url FROM event_item WHERE id = ? AND event_code = ? LIMIT 1', [$itemId, $code]);
        return $row['active_url'] ?? null;
    }
}
