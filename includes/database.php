<?php

if (!function_exists('db_execute')) {
    function db_execute($sql, $types = '', array $params = [])
    {
        global $conn;

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new RuntimeException('Database prepare failed: ' . mysqli_error($conn));
        }

        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Database execute failed: ' . $error);
        }

        return $stmt;
    }
}

if (!function_exists('db_select_all')) {
    function db_select_all($sql, $types = '', array $params = [])
    {
        $stmt = db_execute($sql, $types, $params);
        $result = mysqli_stmt_get_result($stmt);
        $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
        mysqli_stmt_close($stmt);

        return $rows;
    }
}

if (!function_exists('db_select_one')) {
    function db_select_one($sql, $types = '', array $params = [])
    {
        $rows = db_select_all($sql, $types, $params);
        return $rows[0] ?? null;
    }
}

if (!function_exists('db_insert')) {
    function db_insert($sql, $types = '', array $params = [])
    {
        global $conn;

        $stmt = db_execute($sql, $types, $params);
        mysqli_stmt_close($stmt);

        return mysqli_insert_id($conn);
    }
}

if (!function_exists('db_update')) {
    function db_update($sql, $types = '', array $params = [])
    {
        $stmt = db_execute($sql, $types, $params);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        return $affected;
    }
}

if (!function_exists('db_delete')) {
    function db_delete($sql, $types = '', array $params = [])
    {
        return db_update($sql, $types, $params);
    }
}
