<?php
namespace AtlasPress\Rest;

use AtlasPress\Core\Permissions;
use AtlasPress\Core\Cache;
use AtlasPress\Core\ProVersion;
use WP_REST_Request;
use WP_REST_Response;

class DashboardController {
    private const DEFAULT_RANGE = '30d';
    private const DEFAULT_COMPARE = 'previous_period';
    private const MAX_CUSTOM_RANGE_DAYS = 180;
    private const SUPPORTED_RANGES = ['7d', '30d', '90d', 'custom'];
    private const SUPPORTED_COMPARE_MODES = ['previous_period', 'none'];

    public static function register() {
        register_rest_route(
            'atlaspress/v1',
            '/dashboard',
            [
                'methods'  => 'GET',
                'callback' => [ self::class, 'stats' ],
                'permission_callback' => [Permissions::class, 'can_view_dashboard'],
                'args' => [
                    'range' => [
                        'required' => false,
                        'sanitize_callback' => [self::class, 'sanitize_range'],
                        'validate_callback' => [self::class, 'validate_range'],
                    ],
                    'compare' => [
                        'required' => false,
                        'sanitize_callback' => [self::class, 'sanitize_compare'],
                        'validate_callback' => [self::class, 'validate_compare'],
                    ],
                    'start_date' => [
                        'required' => false,
                        'sanitize_callback' => [self::class, 'sanitize_date'],
                        'validate_callback' => [self::class, 'validate_date'],
                    ],
                    'end_date' => [
                        'required' => false,
                        'sanitize_callback' => [self::class, 'sanitize_date'],
                        'validate_callback' => [self::class, 'validate_date'],
                    ],
                ]
            ]
        );
    }

    public static function stats(WP_REST_Request $request) {
        $is_pro_active = ProVersion::is_pro_active();
        $range = self::sanitize_range($request->get_param('range'));
        $compare_mode = self::sanitize_compare($request->get_param('compare'));
        $custom_start_date = self::sanitize_date($request->get_param('start_date'));
        $custom_end_date = self::sanitize_date($request->get_param('end_date'));

        if($range === 'custom' && (empty($custom_start_date) || empty($custom_end_date))) {
            return new \WP_Error(
                'invalid_custom_range',
                'Custom range requires both start_date and end_date.',
                ['status' => 400]
            );
        }

        if($range === 'custom' && strtotime($custom_start_date) > strtotime($custom_end_date)) {
            return new \WP_Error(
                'invalid_custom_range_order',
                'start_date must be on or before end_date.',
                ['status' => 400]
            );
        }

        $window = self::resolve_time_window($range, $custom_start_date, $custom_end_date);
        $compare_window = $compare_mode === 'previous_period'
            ? self::build_previous_window($window)
            : null;
        $range_context = self::build_range_context($window, $compare_mode, $compare_window);

        $cache_payload = [
            'pro' => $is_pro_active ? 1 : 0,
            'range' => $window['range'],
            'start_date' => $window['start_date'],
            'end_date' => $window['end_date'],
            'compare' => $compare_mode,
            'compare_start_date' => $compare_window['start_date'] ?? null,
            'compare_end_date' => $compare_window['end_date'] ?? null,
        ];
        $cache_scope = $is_pro_active ? 'pro' : 'free';
        $cache_key = 'dashboard_stats_' . $cache_scope . '_' . md5(wp_json_encode($cache_payload));

        return Cache::remember($cache_key, function() use ($is_pro_active, $window, $compare_mode, $compare_window, $range_context) {
            global $wpdb;

            $types_table = $wpdb->prefix . 'atlaspress_content_types';
            $entries_table = $wpdb->prefix . 'atlaspress_entries';
            
            // Check if tables exist
            if(
                $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $types_table)) !== $types_table
                || $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $entries_table)) !== $entries_table
            ) {
                return new WP_REST_Response([
                    'contentTypes' => 0,
                    'fields' => 0,
                    'entries' => 0,
                    'apiStatus' => 'Setup Required',
                    'recentEntries' => [],
                    'topContentTypes' => [],
                    'weeklyStats' => [],
                    'rangeContext' => $range_context,
                    'isProActive' => $is_pro_active,
                    'advancedAnalytics' => null
                ], 200);
            }
            
            $types = $wpdb->get_results( "SELECT * FROM $types_table ORDER BY created_at DESC", ARRAY_A );
            $entriesCount = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $entries_table" );
            
            // Recent entries
            $recentEntries = $wpdb->get_results(
                "SELECT e.*, ct.name as content_type_name 
                 FROM $entries_table e 
                 LEFT JOIN $types_table ct ON e.content_type_id = ct.id 
                 ORDER BY e.created_at DESC LIMIT 5", 
                ARRAY_A
            );
            
            // Top content types by entry count
            $topTypes = $wpdb->get_results(
                "SELECT ct.name, COUNT(e.id) as entry_count 
                 FROM $types_table ct 
                 LEFT JOIN $entries_table e ON ct.id = e.content_type_id 
                 GROUP BY ct.id 
                 ORDER BY entry_count DESC LIMIT 5", 
                ARRAY_A
            );
            
            // Date-range daily stats (kept under legacy key weeklyStats for compatibility)
            $daily_stats_raw = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT DATE(created_at) as date, COUNT(*) as count
                     FROM $entries_table
                     WHERE created_at BETWEEN %s AND %s
                     GROUP BY DATE(created_at)
                     ORDER BY date ASC",
                    $window['start_datetime'],
                    $window['end_datetime']
                ),
                ARRAY_A
            );
            $weeklyStats = self::build_daily_series($daily_stats_raw, $window['start_date'], $window['end_date']);

            $advanced_analytics = null;
            if($is_pro_active) {
                $entries_last_24h = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM $entries_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
                );

                $entries_last_7d = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM $entries_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
                );

                $entries_last_30d = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM $entries_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );

                $previous_7d = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM $entries_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
                );

                $entries_in_range = self::count_entries_between(
                    $entries_table,
                    $window['start_datetime'],
                    $window['end_datetime']
                );
                $previous_entries_in_range = null;
                if($compare_window) {
                    $previous_entries_in_range = self::count_entries_between(
                        $entries_table,
                        $compare_window['start_datetime'],
                        $compare_window['end_datetime']
                    );
                }

                $status_breakdown = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT status, COUNT(*) as count
                         FROM $entries_table
                         WHERE created_at BETWEEN %s AND %s
                         GROUP BY status
                         ORDER BY count DESC",
                        $window['start_datetime'],
                        $window['end_datetime']
                    ),
                    ARRAY_A
                );

                $hourly_activity_raw = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT HOUR(created_at) AS hour_slot, COUNT(*) AS count
                         FROM $entries_table
                         WHERE created_at BETWEEN %s AND %s
                         GROUP BY HOUR(created_at)
                         ORDER BY hour_slot ASC",
                        $window['start_datetime'],
                        $window['end_datetime']
                    ),
                    ARRAY_A
                );
                $hourly_activity = self::build_hourly_series($hourly_activity_raw);

                $type_momentum = [];
                if($compare_window) {
                    $type_momentum = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT ct.name,
                                    SUM(CASE WHEN e.created_at BETWEEN %s AND %s THEN 1 ELSE 0 END) AS current_entries,
                                    SUM(CASE WHEN e.created_at BETWEEN %s AND %s THEN 1 ELSE 0 END) AS previous_entries
                             FROM $types_table ct
                             LEFT JOIN $entries_table e ON ct.id = e.content_type_id
                             GROUP BY ct.id
                             ORDER BY current_entries DESC
                             LIMIT 5",
                            $window['start_datetime'],
                            $window['end_datetime'],
                            $compare_window['start_datetime'],
                            $compare_window['end_datetime']
                        ),
                        ARRAY_A
                    );
                } else {
                    $type_momentum = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT ct.name,
                                    SUM(CASE WHEN e.created_at BETWEEN %s AND %s THEN 1 ELSE 0 END) AS current_entries
                             FROM $types_table ct
                             LEFT JOIN $entries_table e ON ct.id = e.content_type_id
                             GROUP BY ct.id
                             ORDER BY current_entries DESC
                             LIMIT 5",
                            $window['start_datetime'],
                            $window['end_datetime']
                        ),
                        ARRAY_A
                    );
                }

                $status_breakdown = array_map(function($row) {
                    return [
                        'status' => sanitize_text_field((string) ($row['status'] ?? 'unknown')),
                        'count' => (int) ($row['count'] ?? 0),
                    ];
                }, is_array($status_breakdown) ? $status_breakdown : []);

                $type_momentum = array_map(function($row) {
                    $current = (int) ($row['current_entries'] ?? 0);
                    $previous = isset($row['previous_entries']) ? (int) $row['previous_entries'] : null;

                    return [
                        'name' => sanitize_text_field((string) ($row['name'] ?? '')),
                        'current_entries' => $current,
                        'previous_entries' => $previous,
                        'growth_percent' => $previous === null ? null : self::calculate_growth_percent($current, $previous),
                    ];
                }, is_array($type_momentum) ? $type_momentum : []);

                $advanced_analytics = [
                    'entries_last_24h' => $entries_last_24h,
                    'entries_last_7d' => $entries_last_7d,
                    'entries_last_30d' => $entries_last_30d,
                    'avg_daily_entries_30d' => round($entries_last_30d / 30, 2),
                    'growth_7d_percent' => self::calculate_growth_percent($entries_last_7d, $previous_7d),
                    'entries_in_range' => $entries_in_range,
                    'previous_entries_in_range' => $previous_entries_in_range,
                    'avg_daily_entries_range' => round($entries_in_range / max(1, (int) $window['days']), 2),
                    'growth_range_percent' => $previous_entries_in_range === null
                        ? null
                        : self::calculate_growth_percent($entries_in_range, $previous_entries_in_range),
                    'status_breakdown' => $status_breakdown,
                    'hourly_activity_range' => $hourly_activity,
                    'hourly_activity_7d' => $hourly_activity,
                    'type_momentum' => $type_momentum,
                    'compare_mode' => $compare_mode,
                ];
            }

            $contentTypesCount = count( $types );
            $fieldsCount = 0;

            foreach ( $types as $type ) {
                if ( empty( $type['settings'] ) ) continue;

                $settings = json_decode( $type['settings'], true );
                if ( isset( $settings['fields'] ) && is_array( $settings['fields'] ) ) {
                    $fieldsCount += count( $settings['fields'] );
                }
            }

            return new WP_REST_Response([
                'contentTypes' => $contentTypesCount,
                'fields' => $fieldsCount,
                'entries' => (int)$entriesCount,
                'apiStatus' => 'Healthy',
                'recentEntries' => $recentEntries,
                'topContentTypes' => $topTypes,
                'weeklyStats' => $weeklyStats,
                'rangeContext' => $range_context,
                'isProActive' => $is_pro_active,
                'advancedAnalytics' => $advanced_analytics,
            ], 200);
        }, 300); // Cache for 5 minutes
    }

    public static function sanitize_range($value) {
        if(!is_string($value)) {
            return self::DEFAULT_RANGE;
        }

        $range = strtolower(trim($value));
        return in_array($range, self::SUPPORTED_RANGES, true) ? $range : self::DEFAULT_RANGE;
    }

    public static function validate_range($value) {
        if($value === null || $value === '') {
            return true;
        }

        return in_array(strtolower((string) $value), self::SUPPORTED_RANGES, true);
    }

    public static function sanitize_compare($value) {
        if(!is_string($value)) {
            return self::DEFAULT_COMPARE;
        }

        $compare = strtolower(trim($value));
        return in_array($compare, self::SUPPORTED_COMPARE_MODES, true) ? $compare : self::DEFAULT_COMPARE;
    }

    public static function validate_compare($value) {
        if($value === null || $value === '') {
            return true;
        }

        return in_array(strtolower((string) $value), self::SUPPORTED_COMPARE_MODES, true);
    }

    public static function sanitize_date($value) {
        if(!is_string($value)) {
            return '';
        }

        $date = trim($value);
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }

        return $date;
    }

    public static function validate_date($value) {
        if($value === null || $value === '') {
            return true;
        }

        if(!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if(!$parsed instanceof \DateTimeImmutable) {
            return false;
        }

        return $parsed->format('Y-m-d') === $value;
    }

    private static function resolve_time_window($range, $start_date, $end_date) {
        $timezone = wp_timezone();
        $today = new \DateTimeImmutable('today', $timezone);
        $normalized_range = self::sanitize_range($range);

        if($normalized_range === 'custom') {
            $start = self::parse_local_date($start_date, $timezone);
            $end = self::parse_local_date($end_date, $timezone);

            if($start && $end) {
                $days = (int) $start->diff($end)->format('%a') + 1;
                if($days > self::MAX_CUSTOM_RANGE_DAYS) {
                    $start = $end->modify('-' . (self::MAX_CUSTOM_RANGE_DAYS - 1) . ' days');
                }

                return self::window_from_dates('custom', $start, $end);
            }

            $normalized_range = self::DEFAULT_RANGE;
        }

        $days_by_range = [
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
        ];
        $days = $days_by_range[$normalized_range] ?? $days_by_range[self::DEFAULT_RANGE];
        $start = $today->modify('-' . ($days - 1) . ' days');

        return self::window_from_dates($normalized_range, $start, $today);
    }

    private static function build_previous_window(array $window) {
        $timezone = wp_timezone();
        $current_start = self::parse_local_date($window['start_date'], $timezone);
        if(!$current_start) {
            return null;
        }

        $days = max(1, (int) ($window['days'] ?? 1));
        $previous_end = $current_start->modify('-1 day');
        $previous_start = $previous_end->modify('-' . ($days - 1) . ' days');

        return self::window_from_dates('previous_period', $previous_start, $previous_end);
    }

    private static function build_range_context(array $window, $compare_mode, $compare_window) {
        return [
            'range' => (string) ($window['range'] ?? self::DEFAULT_RANGE),
            'label' => (string) ($window['label'] ?? ''),
            'days' => (int) ($window['days'] ?? 0),
            'start_date' => (string) ($window['start_date'] ?? ''),
            'end_date' => (string) ($window['end_date'] ?? ''),
            'compare' => (string) $compare_mode,
            'compare_label' => $compare_mode === 'previous_period' ? 'Previous period' : 'No comparison',
            'compare_start_date' => $compare_window['start_date'] ?? null,
            'compare_end_date' => $compare_window['end_date'] ?? null,
        ];
    }

    private static function window_from_dates($range, \DateTimeImmutable $start, \DateTimeImmutable $end) {
        $start_at_midnight = $start->setTime(0, 0, 0);
        $end_at_end_of_day = $end->setTime(23, 59, 59);
        $days = (int) $start_at_midnight->diff($end_at_end_of_day)->format('%a') + 1;

        return [
            'range' => (string) $range,
            'label' => $range === 'custom'
                ? sprintf('%s to %s', $start_at_midnight->format('M j, Y'), $end_at_end_of_day->format('M j, Y'))
                : sprintf('Last %d days', $days),
            'days' => $days,
            'start_date' => $start_at_midnight->format('Y-m-d'),
            'end_date' => $end_at_end_of_day->format('Y-m-d'),
            'start_datetime' => $start_at_midnight->format('Y-m-d H:i:s'),
            'end_datetime' => $end_at_end_of_day->format('Y-m-d H:i:s'),
        ];
    }

    private static function parse_local_date($date, \DateTimeZone $timezone) {
        if(!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date, $timezone);
        if(!$parsed instanceof \DateTimeImmutable) {
            return null;
        }

        if($parsed->format('Y-m-d') !== $date) {
            return null;
        }

        return $parsed->setTime(0, 0, 0);
    }

    private static function build_daily_series($rows, $start_date, $end_date) {
        $timezone = wp_timezone();
        $start = self::parse_local_date($start_date, $timezone);
        $end = self::parse_local_date($end_date, $timezone);

        if(!$start || !$end || $end < $start) {
            return [];
        }

        $counts_by_day = [];
        foreach(is_array($rows) ? $rows : [] as $row) {
            $day_key = isset($row['date']) ? (string) $row['date'] : '';
            if($day_key === '') {
                continue;
            }
            $counts_by_day[$day_key] = (int) ($row['count'] ?? 0);
        }

        $series = [];
        $cursor = $start;
        while($cursor <= $end) {
            $day_key = $cursor->format('Y-m-d');
            $series[] = [
                'date' => $day_key,
                'count' => (int) ($counts_by_day[$day_key] ?? 0),
            ];
            $cursor = $cursor->modify('+1 day');
        }

        return $series;
    }

    private static function build_hourly_series($rows) {
        $hourly_activity = [];
        for($hour = 0; $hour < 24; $hour++) {
            $hourly_activity[] = [
                'hour' => $hour,
                'count' => 0,
            ];
        }

        foreach(is_array($rows) ? $rows : [] as $row) {
            $hour_index = isset($row['hour_slot']) ? (int) $row['hour_slot'] : -1;
            if($hour_index < 0 || $hour_index > 23) {
                continue;
            }

            $hourly_activity[$hour_index]['count'] = (int) ($row['count'] ?? 0);
        }

        return $hourly_activity;
    }

    private static function count_entries_between($entries_table, $start_datetime, $end_datetime) {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $entries_table WHERE created_at BETWEEN %s AND %s",
                $start_datetime,
                $end_datetime
            )
        );
    }

    private static function calculate_growth_percent($current, $previous) {
        $current = (int) $current;
        $previous = (int) $previous;

        if($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
