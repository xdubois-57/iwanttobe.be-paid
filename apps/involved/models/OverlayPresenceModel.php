<?php
/**
 * Model for handling OVERLAY_PRESENCE table operations.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';

class OverlayPresenceModel {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    /**
     * Update last_seen for a user session on a specific overlay object URL
     * Creates a new record if one doesn't exist
     * 
     * @param string $url The URL of the overlay object
     * @param string $sessionId The PHP session ID
     * @return bool Success or failure
     */
    public function updatePresence($url, $sessionId) {
        if (empty($url) || empty($sessionId)) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            // First, get the overlay_object_id for this URL
            $objectId = $this->getOrCreateOverlayObjectId($url);
            if (!$objectId) {
                $this->db->rollback();
                return false;
            }
            
            // Try to update an existing record
            $updated = $this->db->query(
                'UPDATE OVERLAY_PRESENCE SET last_seen = CURRENT_TIMESTAMP ' . 
                'WHERE overlay_object_id = ? AND phpsessid = ?',
                [$objectId, $sessionId]
            );
            
            // If no records were updated, insert a new one
            if ($updated === 0) {
                $this->db->query(
                    'INSERT INTO OVERLAY_PRESENCE (overlay_object_id, phpsessid) VALUES (?, ?)',
                    [$objectId, $sessionId]
                );
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating presence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get or create an overlay object ID for a URL
     * 
     * @param string $url The URL
     * @return int|bool The ID or false on failure
     */
    private function getOrCreateOverlayObjectId($url) {
        // Try to get existing ID
        $id = $this->db->fetchValue(
            'SELECT id FROM OVERLAY_OBJECT WHERE url = ?',
            [$url]
        );
        
        if ($id) {
            return $id;
        }
        
        // Create a new record
        $this->db->query(
            'INSERT INTO OVERLAY_OBJECT (url, likes) VALUES (?, 0)',
            [$url]
        );
        
        return $this->db->fetchValue(
            'SELECT id FROM OVERLAY_OBJECT WHERE url = ?',
            [$url]
        );
    }
    
    /**
     * Get active presence count for a URL
     * 
     * @param string $url The URL
     * @param int $timeWindow Time window in seconds (default 120 - 2 minutes)
     * @return int Count of active users
     */
    public function getActivePresenceCount($url, $timeWindow = 120) {
        if (empty($url)) {
            return 0;
        }
        
        try {
            // Normalize URL for word cloud pages
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';
            $pathSegments = explode('/', trim($path, '/'));
            
            // Check if this is a wordcloud URL format: /lang/involved/eventkey/wordcloud/wcid[/add]
            if (count($pathSegments) >= 5 && $pathSegments[1] === 'involved' && $pathSegments[3] === 'wordcloud') {
                // Normalize URL to always match the main wordcloud URL
                if (isset($pathSegments[5]) && $pathSegments[5] === 'add') {
                    // This is an "add word" page - normalize to the main wordcloud URL
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
                    $host = $parsedUrl['host'] ?? '';
                    $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
                    
                    // Rebuild the URL up to the wordcloud ID (removing /add)
                    $url = "$scheme://$host$port/{$pathSegments[0]}/{$pathSegments[1]}/{$pathSegments[2]}/{$pathSegments[3]}/{$pathSegments[4]}";
                    error_log("Normalized URL for presence count: " . $url);
                }
            }
            
            $objectId = $this->db->fetchValue(
                'SELECT id FROM OVERLAY_OBJECT WHERE url = ?',
                [$url]
            );
            
            if (!$objectId) {
                error_log("No overlay object found for URL: " . $url);
                return 0;
            }
            
            $count = $this->db->fetchValue(
                'SELECT COUNT(*) FROM OVERLAY_PRESENCE ' . 
                'WHERE overlay_object_id = ? AND last_seen > DATE_SUB(NOW(), INTERVAL ? SECOND)',
                [$objectId, $timeWindow]
            ) ?: 0;
            
            error_log("Active presence count for URL ($url): $count");
            return $count;
        } catch (Exception $e) {
            error_log("Error getting active presence count: " . $e->getMessage());
            return 0;
        }
    }
}
