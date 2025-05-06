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
     * Append an emoji to the queue for a URL. Creates the record if it doesn't exist
     *
     * @param string $url
     * @param string $emoji Single Unicode emoji character
     * @return bool Success
     */
    public function appendEmoji(string $url, string $emoji): bool
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Fetch existing queue (if any)
            $row = $this->db->fetchOne('SELECT id, emoji_queue FROM OVERLAY_OBJECT WHERE url = ?', [$url]);
            if ($row) {
                $queue = $row['emoji_queue'] ? json_decode($row['emoji_queue'], true) : [];
                if (!is_array($queue)) {
                    $queue = [];
                }
                $queue[] = $emoji;
                $this->db->query('UPDATE OVERLAY_OBJECT SET emoji_queue = ? WHERE id = ?', [json_encode($queue), $row['id']]);
            } else {
                $this->db->insert('OVERLAY_OBJECT', [
                    'url'         => $url,
                    'emoji_queue' => json_encode([$emoji])
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
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
