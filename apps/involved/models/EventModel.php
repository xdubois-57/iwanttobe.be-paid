<?php
/**
 * EventModel – wraps EVENT table operations for Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';

class EventModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Create event with empty description and generated key
     * @param string $password Optional password for the event
     * @return string|false Generated key or false on failure
     */
    public function createEmpty(string $password = ''): string|false
    {
        // Check connection
        if (!$this->db->isConnected()) {
            error_log('Database connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        
        $code = $this->generateUniqueCode();
        if ($code === false) {
            return false;
        }
        
        $pwdhash = $password ? hash('sha256', $password) : null;
        
        $insertId = $this->db->insert('EVENT', [
            'key' => $code,
            'description' => '',
            'password' => $password
        ]);
        
        return $insertId !== false ? $code : false;
    }

    /**
     * Get event by key and verify password if provided
     * @param string $code Event key
     * @param string|null $password Password to verify (null to skip password check)
     * @return array|null Event data or null if not found or wrong password
     */
    public function getByKey(string $code, ?string $password = null): array|null
    {
        $event = $this->db->fetchOne('SELECT * FROM EVENT WHERE `key` = ?', [$code]);
        if (!$event) {
            return null;
        }
        
        // If password was provided, verify it against stored password
        if ($password !== null && !empty($event['password'])) {
            if ($password !== $event['password']) {
                return null;
            }
        }
        
        return $event;
    }

    /**
     * Generate unique 4-char code
     */
    private function generateUniqueCode(): string|false
    {
        $chars = array_diff(array_merge(range('A', 'Z'), range('1', '9')), ['O']);
        $attempts = 0;
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars[array_rand($chars)];
            }
            $exists = $this->db->fetchValue('SELECT COUNT(*) FROM EVENT WHERE `key` = ?', [$code]);
            $attempts++;
            if ($attempts > 100) {
                return false;
            }
        } while ($exists);
        return $code;
    }

    /**
     * Delete events that have not been updated for more than one month
     * This is important for GDPR compliance to ensure data isn't stored longer than necessary
     * 
     * @param int $months Number of months of inactivity before deletion (default: 1)
     * @return array Results containing count of deleted events and any errors
     */
    public function deleteOldEvents(int $months = 1): array
    {
        // Check connection
        if (!$this->db->isConnected()) {
            error_log('Database connection failed: ' . $this->db->getErrorMessage());
            return [
                'success' => false,
                'error' => 'Database connection failed: ' . $this->db->getErrorMessage(),
                'deleted_count' => 0
            ];
        }
        
        try {
            // Find events older than specified months
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$months} month"));
            
            // First, get count of events to be deleted (for logging)
            $countToDelete = $this->db->fetchValue(
                'SELECT COUNT(*) FROM EVENT WHERE updated_at < ?', 
                [$cutoffDate]
            );
            
            // Log the deletion operation for audit purposes
            error_log("GDPR Deletion: Preparing to delete {$countToDelete} events older than {$cutoffDate}");
            
            // Delete the events - cascading will handle related wordclouds and words
            $result = $this->db->delete('EVENT', 'updated_at < ?', [$cutoffDate]);
            
            if ($result === false) {
                error_log('GDPR Deletion Error: ' . $this->db->getErrorMessage());
                return [
                    'success' => false,
                    'error' => $this->db->getErrorMessage(),
                    'deleted_count' => 0
                ];
            }
            
            // Get number of affected rows (from result directly)
            $deletedCount = $result;
            
            // Log successful deletion
            error_log("GDPR Deletion: Successfully deleted {$deletedCount} events older than {$cutoffDate}");
            
            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate
            ];
        } catch (Exception $e) {
            error_log('GDPR Deletion Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'deleted_count' => 0
            ];
        }
    }
}
