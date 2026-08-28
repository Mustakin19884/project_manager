<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$from_date  = trim($_GET['from_date'] ?? '');
$to_date    = trim($_GET['to_date'] ?? '');
$status     = trim($_GET['status'] ?? '');
$priority   = trim($_GET['priority'] ?? '');
$project_id = (int)($_GET['project_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Build WHERE
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| Date Filter
|--------------------------------------------------------------------------
*/

if (
    $from_date !== '' &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)
) {

    $where[] = "DATE(t.created_at) >= ?";

    $params[] = $from_date;

    $types .= "s";
}


if (
    $to_date !== '' &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)
) {

    $where[] = "DATE(t.created_at) <= ?";

    $params[] = $to_date;

    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

$allowed_status = [
    'pending',
    'in_progress',
    'completed',
    'on_hold',
    'cancelled'
];

if (in_array($status, $allowed_status, true)) {

    $where[] = "t.status = ?";

    $params[] = $status;

    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Priority
|--------------------------------------------------------------------------
*/

$allowed_priority = [
    'low',
    'medium',
    'high',
    'urgent'
];

if (in_array($priority, $allowed_priority, true)) {

    $where[] = "t.priority = ?";

    $params[] = $priority;

    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Project
|--------------------------------------------------------------------------
*/

if ($project_id > 0) {

    $where[] = "t.project_id = ?";

    $params[] = $project_id;

    $types .= "i";
}


/*
|--------------------------------------------------------------------------
| WHERE SQL
|--------------------------------------------------------------------------
*/

$where_sql = "";

if (!empty($where)) {

    $where_sql =
        "WHERE " . implode(" AND ", $where);
}


/*
|--------------------------------------------------------------------------
| Query
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        t.id,
        t.title,
        t.status,
        t.priority,
        t.progress,
        t.deadline,
        t.created_at,

        p.name AS project_name,

        tm.name AS member_name

    FROM tasks t

    LEFT JOIN projects p
        ON p.id = t.project_id

    LEFT JOIN team_members tm
        ON tm.id = t.team_member_id

    $where_sql

    ORDER BY t.id DESC

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );
}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| CSV Download Headers
|--------------------------------------------------------------------------
*/

$filename =
    "project-manager-report-" .
    date("Y-m-d-H-i-s") .
    ".csv";


header(
    "Content-Type: text/csv; charset=UTF-8"
);

header(
    "Content-Disposition: attachment; filename=\"$filename\""
);

header("Pragma: no-cache");

header("Expires: 0");


/*
|--------------------------------------------------------------------------
| Open Output
|--------------------------------------------------------------------------
*/

$output = fopen(
    "php://output",
    "w"
);


/*
|--------------------------------------------------------------------------
| UTF-8 BOM
|--------------------------------------------------------------------------
*/

fprintf(
    $output,
    chr(0xEF) .
    chr(0xBB) .
    chr(0xBF)
);


/*
|--------------------------------------------------------------------------
| Report Information
|--------------------------------------------------------------------------
*/

fputcsv(
    $output,
    [
        "Project Manager Report"
    ]
);

fputcsv(
    $output,
    [
        "Generated",
        date("Y-m-d H:i:s")
    ]
);

fputcsv(
    $output,
    []
);


/*
|--------------------------------------------------------------------------
| Headers
|--------------------------------------------------------------------------
*/

fputcsv(
    $output,
    [
        "ID",
        "Task",
        "Project",
        "Team Member",
        "Status",
        "Priority",
        "Progress",
        "Deadline",
        "Created At"
    ]
);


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

while ($row = $result->fetch_assoc()) {

    fputcsv(
        $output,
        [

            $row['id'],

            $row['title'],

            $row['project_name']
                ?? 'Unassigned',

            $row['member_name']
                ?? 'Unassigned',

            ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $row['status']
                )
            ),

            ucfirst(
                $row['priority']
            ),

            $row['progress'] . "%",

            !empty($row['deadline'])
                ? date(
                    "Y-m-d",
                    strtotime(
                        $row['deadline']
                    )
                )
                : "",

            $row['created_at']

        ]
    );
}


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

fclose($output);

$stmt->close();

exit;

?>