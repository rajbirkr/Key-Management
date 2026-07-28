
<?php
// Database configuration
// Use only environment variables for credentials.
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_name = getenv('DB_NAME');

if (!$db_host || !$db_port || !$db_user || !$db_pass || !$db_name) {
    die('Database configuration is incomplete. Please set DB_HOST, DB_PORT, DB_USER, DB_PASS, and DB_NAME.');
}

if (!function_exists('mysqli_init') && !class_exists('PDO')) {
    die('Database connector error: mysqli extension is unavailable and PDO is required for fallback.');
}

if (!defined('MYSQLI_OPT_CONNECT_TIMEOUT')) {
    define('MYSQLI_OPT_CONNECT_TIMEOUT', 1);
}
if (!defined('MYSQLI_CLIENT_SSL')) {
    define('MYSQLI_CLIENT_SSL', 0);
}

if (!function_exists('mysqli_init')) {
    $GLOBALS['mysqli_connect_error_msg'] = '';

    class MySQLiPDOCompat
    {
        public ?PDO $pdo = null;
        public int $timeout = 0;
        public string $error = '';
    }

    class MySQLiPDOStatementCompat
    {
        private array $rows;
        private int $index = 0;

        public function __construct(array $rows)
        {
            $this->rows = $rows;
        }

        public function fetch_assoc(): ?array
        {
            if ($this->index >= count($this->rows)) {
                return null;
            }
            return $this->rows[$this->index++];
        }

        public function num_rows(): int
        {
            return count($this->rows);
        }
    }

    function mysqli_init()
    {
        return new MySQLiPDOCompat();
    }

    function mysqli_options($conn, $option, $value)
    {
        if (!($conn instanceof MySQLiPDOCompat)) {
            return false;
        }
        if ($option === MYSQLI_OPT_CONNECT_TIMEOUT) {
            $conn->timeout = (int)$value;
            return true;
        }
        return false;
    }

    function mysqli_real_connect($conn, $host, $user, $pass, $db, $port, $socket, $flags)
    {
        if (!($conn instanceof MySQLiPDOCompat)) {
            return false;
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, (int)$port, $db);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($conn->timeout > 0) {
            $options[PDO::ATTR_TIMEOUT] = $conn->timeout;
        }

        try {
            $conn->pdo = new PDO($dsn, $user, $pass, $options);

            if ($flags & MYSQLI_CLIENT_SSL) {
                try {
                    $stmt = $conn->pdo->query("SELECT @@ssl_cipher AS cipher");
                    $cipher = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['cipher'] ?? '' : '';
                    if (empty($cipher)) {
                        $conn->error = 'Database connection did not negotiate SSL. SSL is required.';
                        $GLOBALS['mysqli_connect_error_msg'] = $conn->error;
                        return false;
                    }
                } catch (PDOException $e) {
                    $conn->error = $e->getMessage();
                    $GLOBALS['mysqli_connect_error_msg'] = $conn->error;
                    return false;
                }
            }

            return true;
        } catch (PDOException $e) {
            $conn->error = $e->getMessage();
            $GLOBALS['mysqli_connect_error_msg'] = $e->getMessage();
            return false;
        }
    }

    function mysqli_connect_error()
    {
        return $GLOBALS['mysqli_connect_error_msg'] ?: mysqli_error(null);
    }

    function mysqli_query($conn, $query)
    {
        if (!($conn instanceof MySQLiPDOCompat) || !$conn->pdo) {
            return false;
        }

        try {
            $stmt = $conn->pdo->query($query);
            if ($stmt === false) {
                return false;
            }
            return new MySQLiPDOStatementCompat($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $conn->error = $e->getMessage();
            return false;
        }
    }

    function mysqli_fetch_assoc($result)
    {
        if (!($result instanceof MySQLiPDOStatementCompat)) {
            return null;
        }
        return $result->fetch_assoc();
    }

    function mysqli_num_rows($result)
    {
        if (!($result instanceof MySQLiPDOStatementCompat)) {
            return 0;
        }
        return $result->num_rows();
    }

    function mysqli_real_escape_string($conn, $string)
    {
        if (!($conn instanceof MySQLiPDOCompat) || !$conn->pdo) {
            return addslashes($string);
        }
        return substr($conn->pdo->quote($string), 1, -1);
    }

    function mysqli_error($conn)
    {
        if ($conn instanceof MySQLiPDOCompat) {
            return $conn->error;
        }
        return '';
    }
}

$conn = mysqli_init();
if (!$conn) {
    die("Database initialization failed.");
}

// Optional timeout
mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// Connect using PDO-based MySQL.
if (!mysqli_real_connect(
    $conn,
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port,
    null,
    MYSQLI_CLIENT_SSL
)) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
