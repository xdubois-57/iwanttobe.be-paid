<?php
/**
 * Model for handling OVERLAY_PRESENCE table operations.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

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
            Logger::getInstance()->error("Empty URL or session ID provided to updatePresence");
            return false;
        }
        
        try {
            // Normalize URL for word cloud pages
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';
            $pathSegments = explode('/', trim($path, '/'));
            
            // Check if this is a wordcloud URL format: /lang/involved/eventkey/wordcloud/wcid[/add]
            if (count($pathSegments) >= 5 && $pathSegments[1] === 'involved' && $pathSegments[3] === 'wordcloud') {
                // Normalize URL to always match the main wordcloud URL
                if (count($pathSegments) > 5) {
                    // This could be an "add word" page or any subpage - normalize to the main wordcloud URL
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
                    $host = $parsedUrl['host'] ?? '';
                    $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
                    
                    // Rebuild the URL up to the wordcloud ID (removing /add or any other subpath)
                    $url = "$scheme://$host$port/{$pathSegments[0]}/{$pathSegments[1]}/{$pathSegments[2]}/{$pathSegments[3]}/{$pathSegments[4]}";
                    Logger::getInstance()->error("Normalized URL for presence update: " . $url);
                }
            }
            
            $this->db->beginTransaction();
            
            // First, get the overlay_object_id for this URL
            $objectId = $this->getOrCreateOverlayObjectId($url);
            if (!$objectId) {
                $this->db->rollback();
                Logger::getInstance()->error("Failed to get or create overlay object ID for URL: " . $url);
                return false;
            }
            
            Logger::getInstance()->error("Got object ID $objectId for URL $url");
            
            // Try to update an existing record
            $stmt = $this->db->query(
                'UPDATE OVERLAY_PRESENCE SET last_seen = CURRENT_TIMESTAMP ' . 
                'WHERE overlay_object_id = ? AND phpsessid = ?',
                [$objectId, $sessionId]
            );
            
            if ($stmt === false) {
                $this->db->rollback();
                Logger::getInstance()->error("Update query failed: " . $this->db->getErrorMessage());
                return false;
            }
            
            // Get the number of affected rows
            $updatedRows = $stmt->rowCount();
            Logger::getInstance()->error("Update presence result: " . ($updatedRows === 0 ? "No rows updated" : "$updatedRows rows updated"));
            
            // If no records were updated, insert a new one
            if ($updatedRows === 0) {
                $stmt = $this->db->query(
                    'INSERT INTO OVERLAY_PRESENCE (overlay_object_id, phpsessid) VALUES (?, ?)',
                    [$objectId, $sessionId]
                );
                
                if ($stmt === false) {
                    $this->db->rollback();
                    Logger::getInstance()->error("Insert query failed: " . $this->db->getErrorMessage());
                    return false;
                }
                
                $insertedRows = $stmt->rowCount();
                Logger::getInstance()->error("Insert presence result: " . ($insertedRows > 0 ? "$insertedRows rows inserted" : "No rows inserted"));
                
                if ($insertedRows === 0) {
                    $this->db->rollback();
                    Logger::getInstance()->error("Failed to insert presence record");
                    return false;
                }
            }
            
            $this->db->commit();
            Logger::getInstance()->error("Presence tracking committed successfully for session $sessionId on URL $url");
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::getInstance()->error("Exception updating presence: " . $e->getMessage());
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
        
        // Create a new record - initialize emoji_queue as empty JSON array
        $this->db->query(
            'INSERT INTO OVERLAY_OBJECT (url, emoji_queue) VALUES (?, ?)',
            [$url, '[]']
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
     * @param int $timeWindow Time window in seconds (default 90)
     * @return int Count of active users
     */
    public function getActivePresenceCount($url, $timeWindow = 90) {
        Logger::getInstance()->debug("Starting presence count check for URL: $url");
        
        if (empty($url)) {
            Logger::getInstance()->error("Empty URL provided to getActivePresenceCount");
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
                if (count($pathSegments) > 5) {
                    // This could be an "add word" page or any subpage - normalize to the main wordcloud URL
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
                    $host = $parsedUrl['host'] ?? '';
                    $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
                    
                    // Rebuild the URL up to the wordcloud ID (removing /add or any other subpath)
                    $url = "$scheme://$host$port/{$pathSegments[0]}/{$pathSegments[1]}/{$pathSegments[2]}/{$pathSegments[3]}/{$pathSegments[4]}";
                    Logger::getInstance()->debug("Normalized URL for presence count: " . $url);
                }
            }
            
            $objectId = $this->db->fetchValue(
                'SELECT id FROM OVERLAY_OBJECT WHERE url = ?',
                [$url]
            );
            
            if (!$objectId) {
                Logger::getInstance()->info("No overlay object found for URL: " . $url);
                return 0;
            }
            
            Logger::getInstance()->debug("Looking up presence for object ID: $objectId, URL: $url");
            
            // Log all presence entries for debugging
            $allEntries = $this->db->fetchAll(
                'SELECT phpsessid, last_seen FROM OVERLAY_PRESENCE WHERE overlay_object_id = ?',
                [$objectId]
            );
            
            Logger::getInstance()->debug("Total presence entries for object $objectId: " . count($allEntries));
            foreach ($allEntries as $entry) {
                Logger::getInstance()->debug("Session: {$entry['phpsessid']}, Last seen: {$entry['last_seen']}");
            }
            
            // For debugging, directly query and print the count
            $rawCount = $this->db->fetchValue(
                'SELECT COUNT(*) FROM OVERLAY_PRESENCE WHERE overlay_object_id = ?',
                [$objectId]
            );
            Logger::getInstance()->debug("Raw SQL count result for object ID $objectId: $rawCount");
            
            // For now, let's just count all entries regardless of last_seen time
            $count = count($allEntries);
            
            Logger::getInstance()->debug("Total presence count (ignoring time window): $count");
            
            // Get active users with time window
            $activeCount = $this->db->fetchValue(
                'SELECT COUNT(*) FROM OVERLAY_PRESENCE ' . 
                'WHERE overlay_object_id = ? AND last_seen > DATE_SUB(NOW(), INTERVAL ? SECOND)',
                [$objectId, $timeWindow]
            ) ?: 0;
            
            Logger::getInstance()->debug("Active presence count with {$timeWindow}s window: $activeCount");
            
            // Return the active count with time window
            return $activeCount;
        } catch (Exception $e) {
            Logger::getInstance()->error("Error getting active presence count: " . $e->getMessage());
            return 0;
        }
    }
}
