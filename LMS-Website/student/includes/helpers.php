<?php
declare(strict_types=1);

/**
 * Helper for grade and performance labels.
 *
 * @return array{text:string,color:string,text_color:string}
 */
function get_status_details(float $percent): array
{
    if ($percent < 20) {
        return ['text' => 'Critical Warning', 'color' => 'bg-danger', 'text_color' => 'text-danger'];
    }

    if ($percent >= 20 && $percent < 50) {
        return ['text' => 'Needs Help', 'color' => 'bg-warning text-dark', 'text_color' => 'text-warning'];
    }

    if ($percent >= 50 && $percent <= 75) {
        return ['text' => 'On Track', 'color' => 'bg-info text-white', 'text_color' => 'text-info'];
    }

    return ['text' => 'Excellent', 'color' => 'bg-success', 'text_color' => 'text-success'];
}

/**
 * Keep placeholders centralized for future DB expansion.
 *
 * @return array<string, mixed>
 */
function get_database_extension_skeleton(): array
{
    return [
        'student_profile_table' => 'students',
        'enrollment_table' => 'courses',
        'grade_table' => 'grades',
        'assignment_table' => 'assignments',
        'future_operations' => [
            'save_profile_changes' => false,
            'settings_persistence' => false,
            'submission_tracking' => false,
        ],
    ];
}
