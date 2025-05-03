<?php
/**
 * OverlayObjectModel – wraps OVERLAY_OBJECT table operations.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';

class OverlayObjectModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Increment likes for a URL, creating the entry if it doesn't exist
     * @param string $url
     * @return int|false New likes count or false
     */
    public function incrementLikes(string $url): int|false
    {
        if (!$this->db->isConnected()) {
            error_log('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }

        // Begin transaction for atomicity
        $this->db->beginTransaction();

        try {
            // Check if the URL exists
            $exists = $this->db->fetchOne('SELECT id, likes FROM OVERLAY_OBJECT WHERE url = ?', [$url]);
            
            if ($exists) {
                // Update existing record
                $this->db->query('UPDATE OVERLAY_OBJECT SET likes = likes + 1 WHERE url = ?', [$url]);
                $likes = $exists['likes'] + 1;
            } else {
                // Create new record
                $this->db->insert('OVERLAY_OBJECT', [
                    'url' => $url,
                    'likes' => 1
                ]);
                $likes = 1;
            }
            
            $this->db->commit();
            return $likes;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Failed to increment likes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get likes count for a URL
     * @param string $url
     * @return int Likes count (0 if not found)
     */
    public function getLikes(string $url): int
    {
        if (!$this->db->isConnected()) {
            error_log('DB connection failed: ' . $this->db->getErrorMessage());
            return 0;
        }
        
        $likes = $this->db->fetchValue('SELECT likes FROM OVERLAY_OBJECT WHERE url = ?', [$url]);
        return $likes !== false ? (int)$likes : 0;
    }
}
