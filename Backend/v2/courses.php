<?php
include 'main.php'; // Include database functions

function fetchCourses($filters = []) {
    $query = "SELECT c.course_id, c.course_name, c.course_description, 
                     v.building_name, v.floor
              FROM ODCoursesDB c
              LEFT JOIN ODVenueDB v ON c.venue_id = v.venue_id
              WHERE 1";

    $params = [];
    $types = '';

    // Apply search filter for course name
    if (!empty($filters['search'])) {
        $query .= " AND c.course_name LIKE ?";
        $types .= 's';
        $params[] = "%" . $filters['search'] . "%"; // Search for partial matches
    }

    // Run the query with filters
    list($execute_success, $execute_result) = Generate_Query($query, array_merge([$types], array_map(function ($p) {
        return [$p];
    }, $params)));

    if ($execute_success) {
        $courses = [];
        while ($row = $execute_result->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode($courses);
    } else {
        echo json_encode([]);
    }
}

// Handle AJAX POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'search' => $_POST['search'] ?? null
    ];
    fetchCourses($filters);
}
?>