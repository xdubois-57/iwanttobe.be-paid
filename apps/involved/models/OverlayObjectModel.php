<?php
/**
 * OverlayObjectModel – wraps OVERLAY_OBJECT table operations.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

class OverlayObjectModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }
    
    /**
     * Append an emoji to the queue for an event. Creates or uses the event record in OVERLAY_OBJECT
     *
     * @param string $eventCode Event code
     * @param string $emoji Single Unicode emoji character
     * @param int|null $eventItemId Optional event item ID for targeted emoji reactions
     * @return bool Success
     */
    public function appendEmojiForEvent(string $eventCode, string $emoji, ?int $eventItemId = null): bool
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }

        // Generate a consistent URL format for events and event items
        $url = '/involved/' . $eventCode;
        if ($eventItemId) {
            $url .= '/eventitem/' . $eventItemId;
        }
        
        // Use existing appendEmoji method with the constructed URL
        return $this->appendEmoji($url, $emoji);
    }

    /**
     * Append an emoji to the queue for a URL. Creates the record if it doesn't exist
     *
     * @param string $url
     * @param string $emoji Single Unicode emoji character
     * @return bool Success
     */
    public function appendEmoji(string $url, string $emoji): bool
    {
        $logFile = __DIR__ . '/../../../logs/presence_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] Called with url: $url, emoji: $emoji\n", FILE_APPEND);
        if (!$this->db->isConnected()) {
            $err = $this->db->getErrorMessage();
            file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] DB connection failed: $err\n", FILE_APPEND);
            Logger::getInstance()->error('DB connection failed: ' . $err);
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Fetch existing queue (if any)
            $sql = 'SELECT id, emoji_queue FROM OVERLAY_OBJECT WHERE url = ?';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] SQL: $sql [url: $url]\n", FILE_APPEND);
            $row = $this->db->fetchOne($sql, [$url]);
            if ($row) {
                $queue = $row['emoji_queue'] ? json_decode($row['emoji_queue'], true) : [];
                if (!is_array($queue)) {
                    $queue = [];
                }
                $queue[] = $emoji;
                $updateSql = 'UPDATE OVERLAY_OBJECT SET emoji_queue = ? WHERE id = ?';
                file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] SQL: $updateSql [queue: " . json_encode($queue) . ", id: {$row['id']}]\n", FILE_APPEND);
                $this->db->query($updateSql, [json_encode($queue), $row['id']]);
            } else {
                $insertData = [
                    'url'         => $url,
                    'emoji_queue' => json_encode([$emoji])
                ];
                file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] INSERT: " . json_encode($insertData) . "\n", FILE_APPEND);
                $this->db->insert('OVERLAY_OBJECT', $insertData);
            }
            $this->db->commit();
            file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] Commit success\n", FILE_APPEND);
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            file_put_contents($logFile, date('Y-m-d H:i:s') . " [appendEmoji] Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            Logger::getInstance()->error('Failed to append emoji: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pop up to $max emojis from the queue for the given URL (FIFO)
     *
     * @param string $url
     * @param int    $max
     * @return array List of emojis (may be empty)
     */
    public function popQueuedEmojis(string $url, int $max = 10): array
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return [];
        }

        $this->db->beginTransaction();
        try {
            $row = $this->db->fetchOne('SELECT id, emoji_queue FROM OVERLAY_OBJECT WHERE url = ? FOR UPDATE', [$url]);
            if (!$row || !$row['emoji_queue']) {
                $this->db->commit();
                return [];
            }
            $queue = json_decode($row['emoji_queue'], true);
            if (!is_array($queue) || empty($queue)) {
                $this->db->commit();
                return [];
            }
            $popped = array_splice($queue, 0, $max);
            $this->db->query('UPDATE OVERLAY_OBJECT SET emoji_queue = ? WHERE id = ?', [json_encode($queue), $row['id']]);
            $this->db->commit();
            return $popped;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::getInstance()->error('Failed to pop emojis: ' . $e->getMessage());
            return [];
        }
    }
}
