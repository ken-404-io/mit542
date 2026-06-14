<?php
/* =====================================================
   SHARED DATABASE LAYER  (PostgreSQL via PDO)
   -----------------------------------------------------
   The storefront was originally written for MySQL (mysqli).
   It now runs on PostgreSQL (e.g. Neon) through PDO. To avoid
   rewriting every query call-site, this file provides a thin
   mysqli_* compatibility shim backed by PDO, plus the shared
   $con connection and dbQuery() helper used across the app.

   Connection settings come from environment variables:
     - DATABASE_URL  (a single postgres:// URL, e.g. from Neon)
   or the discrete fallbacks:
     - DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME, DB_SSLMODE
   ===================================================== */

/* -----------------------------------------------------
   Build a PDO connection, or return false if the database
   is unreachable so callers can degrade gracefully (the
   storefront shows empty-state messages instead of crashing).
   ----------------------------------------------------- */
// Built-in default connection (a disposable school-activity database) so the
// app works out of the box on any host without configuring env vars. Set the
// DATABASE_URL environment variable to override this in a real deployment.
define('DEFAULT_DATABASE_URL',
    'postgresql://neondb_owner:npg_0CHB6LNQAmab@ep-gentle-unit-ai0uyu4w-pooler.c-4.us-east-1.aws.neon.tech/neondb?sslmode=require');

function dbConnect() {
    $url = getenv('DATABASE_URL') ?: getenv('DATABASE_URL_UNPOOLED') ?: DEFAULT_DATABASE_URL;

    if ($url !== '') {
        $p       = parse_url($url);
        $host    = $p['host'] ?? 'localhost';
        $port    = $p['port'] ?? 5432;
        $user    = isset($p['user']) ? urldecode($p['user']) : '';
        $pass    = isset($p['pass']) ? urldecode($p['pass']) : '';
        $name    = isset($p['path']) ? ltrim($p['path'], '/') : '';
        $sslmode = 'require';
        if (!empty($p['query'])) {
            parse_str($p['query'], $q);
            if (!empty($q['sslmode'])) {
                $sslmode = $q['sslmode'];
            }
        }
    } else {
        $host    = getenv('DB_HOST') ?: 'localhost';
        $port    = getenv('DB_PORT') ?: '5432';
        $user    = getenv('DB_USER') ?: 'postgres';
        $pass    = getenv('DB_PASS') ?: '';
        $name    = getenv('DB_NAME') ?: 'ecommerce_website';
        $sslmode = getenv('DB_SSLMODE') ?: 'prefer';
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$name;sslmode=$sslmode";

    try {
        return new PDO($dsn, $user, $pass, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 10,
        ));
    } catch (Exception $e) {
        return false;
    }
}

// Establish the shared connection once.
if (!isset($con)) {
    $con = dbConnect();
}

/* -----------------------------------------------------
   Run a query against the live connection. Returns a
   PDOStatement on success, or false when there is no
   connection or the query fails (mirrors mysqli_query()).
   ----------------------------------------------------- */
function dbQuery($sql) {
    global $con;
    if (!($con instanceof PDO)) {
        return false;
    }
    $stmt = $con->query($sql);
    return $stmt === false ? false : $stmt;
}

/* =====================================================
   mysqli_* COMPATIBILITY SHIM (backed by PDO)
   Defined only when the real mysqli extension is absent,
   so the legacy call-sites keep working unchanged.
   ===================================================== */

if (!function_exists('mysqli_query')) {

    function mysqli_query($con, $sql) {
        if (!($con instanceof PDO)) {
            return false;
        }
        $stmt = $con->query($sql);
        return $stmt === false ? false : $stmt;
    }

    function mysqli_fetch_assoc($res) {
        if (!($res instanceof PDOStatement)) {
            return null;
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    function mysqli_fetch_array($res, $mode = null) {
        // The app only ever reads associative keys from fetch_array().
        return mysqli_fetch_assoc($res);
    }

    function mysqli_fetch_row($res) {
        if (!($res instanceof PDOStatement)) {
            return null;
        }
        $row = $res->fetch(PDO::FETCH_NUM);
        return $row === false ? null : $row;
    }

    function mysqli_num_rows($res) {
        if (!($res instanceof PDOStatement)) {
            return 0;
        }
        // PDO_pgsql reliably reports the row count for SELECTs.
        return $res->rowCount();
    }

    function mysqli_real_escape_string($con, $str) {
        if (!($con instanceof PDO)) {
            return addslashes((string) $str);
        }
        $quoted = $con->quote((string) $str);
        // quote() wraps the value in single quotes; strip them because
        // callers add their own quotes around the placeholder.
        return (strlen($quoted) >= 2) ? substr($quoted, 1, -1) : (string) $str;
    }

    function mysqli_error($con) {
        if (!($con instanceof PDO)) {
            return '';
        }
        $info = $con->errorInfo();
        return isset($info[2]) ? (string) $info[2] : '';
    }

    function mysqli_insert_id($con) {
        if (!($con instanceof PDO)) {
            return 0;
        }
        try {
            return (int) $con->lastInsertId();
        } catch (Exception $e) {
            return 0;
        }
    }

    function mysqli_prepare($con, $sql) {
        if (!($con instanceof PDO)) {
            return false;
        }
        $stmt = $con->prepare($sql);
        return $stmt === false ? false : $stmt;
    }

    function mysqli_stmt_bind_param($stmt, $types, ...$vars) {
        if (!($stmt instanceof PDOStatement)) {
            return false;
        }
        for ($i = 0, $n = strlen($types); $i < $n; $i++) {
            $pdoType = ($types[$i] === 'i') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($i + 1, $vars[$i], $pdoType);
        }
        return true;
    }

    function mysqli_stmt_execute($stmt) {
        if (!($stmt instanceof PDOStatement)) {
            return false;
        }
        return $stmt->execute();
    }

    function mysqli_stmt_get_result($stmt) {
        return ($stmt instanceof PDOStatement) ? $stmt : false;
    }

    function mysqli_stmt_close($stmt) {
        if ($stmt instanceof PDOStatement) {
            $stmt->closeCursor();
        }
        return true;
    }
}
