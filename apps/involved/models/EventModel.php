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
            'pwdhash' => $pwdhash
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
        
        // If password was provided, verify it against pwdhash
        if ($password !== null && !empty($event['pwdhash'])) {
            $hash = hash('sha256', $password);
            if ($hash !== $event['pwdhash']) {
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
}
