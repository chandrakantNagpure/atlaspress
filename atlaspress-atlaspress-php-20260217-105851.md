# Plugin Check Report

**Plugin:** AtlasPress
**Generated at:** 2026-02-17 10:58:51


## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Admin\Pages\Integration.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 53 | 1 | ERROR | WordPress.WP.EnqueuedResources.NonEnqueuedScript | Scripts must be registered/enqueued via wp_enqueue_script() |  |
| 66 | 68 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'admin_url'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 107 | 1 | ERROR | WordPress.WP.EnqueuedResources.NonEnqueuedScript | Scripts must be registered/enqueued via wp_enqueue_script() |  |
| 119 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 119 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 119 | 20 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table_name used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$table_name&#039;&quot;)\n$table_name assigned unsafely at line 117:\n $table_name = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 119 | 28 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SHOW TABLES LIKE &#039;$table_name&#039;&quot; |  |
| 123 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 123 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 123 | 23 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table_name used in $wpdb-&gt;get_results(&quot;SELECT id, name, slug FROM $table_name ORDER BY name&quot;)\n$table_name assigned unsafely at line 117:\n $table_name = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 123 | 35 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SELECT id, name, slug FROM $table_name ORDER BY name&quot; |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Admin\Pages\Webhooks.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 50 | 43 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'admin_url'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 55 | 52 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'wp_create_nonce'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 69 | 43 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'admin_url'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 74 | 52 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'wp_create_nonce'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\ImportExportController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 36 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 36 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 36 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_results(&quot;SELECT * FROM $table&quot;)\n$table assigned unsafely at line 35:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$types assigned unsafely at line 36:\n $types = $wpdb-&gt;get_results(&quot;SELECT * FROM $table&quot;, ARRAY_A) |  |
| 36 | 37 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table&quot; |  |
| 55 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 55 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 55 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id))\n$types_table assigned unsafely at line 53:\n $types_table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 55:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id), ARRAY_A) |  |
| 55 | 47 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at &quot;SELECT * FROM $types_table WHERE id=%d&quot; |  |
| 58 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 58 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 58 | 27 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_results($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $entries_table WHERE content_type_id=%d&quot;, \r\n $type_id\r\n ))\n$entries_table assigned unsafely at line 52:\n $entries_table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039;\n$types_table assigned unsafely at line 53:\n $types_table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 55:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id), ARRAY_A) |  |
| 59 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at &quot;SELECT * FROM $entries_table WHERE content_type_id=%d&quot; |  |
| 89 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 89 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 89 | 30 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var($wpdb-&gt;prepare(&quot;SELECT COUNT(*) FROM $table WHERE slug=%s&quot;, $type[&#039;slug&#039;])) |  |
| 89 | 53 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(*) FROM $table WHERE slug=%s&quot; |  |
| 96 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 122 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 122 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 122 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id))\n$types_table assigned unsafely at line 120:\n $types_table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 122:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id), ARRAY_A) |  |
| 122 | 47 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at &quot;SELECT * FROM $types_table WHERE id=%d&quot; |  |
| 125 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 125 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 125 | 27 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_results($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $entries_table WHERE content_type_id=%d&quot;, \r\n $type_id\r\n ))\n$entries_table assigned unsafely at line 119:\n $entries_table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039;\n$types_table assigned unsafely at line 120:\n $types_table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 122:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id), ARRAY_A) |  |
| 126 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at &quot;SELECT * FROM $entries_table WHERE content_type_id=%d&quot; |  |
| 155 | 106 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 180 | 106 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 181 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$xml'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 191 | 106 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 223 | 9 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 232 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 232 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 232 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id))\n$types_table assigned unsafely at line 231:\n $types_table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 232:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $types_table WHERE id=%d&quot;, $type_id)) |  |
| 232 | 47 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at &quot;SELECT * FROM $types_table WHERE id=%d&quot; |  |
| 268 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 308 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\atlaspress.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 31 | 9 | ERROR | PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound | load_plugin_textdomain() has been discouraged since WordPress version 4.6. When your plugin is hosted on WordPress.org, you no longer need to manually include this function call for translations under your plugin slug. WordPress will automatically load the translations for you as needed. | [Docs](https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/) |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\FormProxy.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 41 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 41 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 50 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 63 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 65 | 47 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Integrations\HubSpot.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 37 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 47 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 49 | 44 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\EntriesController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 64 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 64 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 64 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var(&quot;SELECT COUNT(*) FROM $table WHERE $where&quot;) |  |
| 64 | 33 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(*) FROM $table WHERE $where&quot; |  |
| 64 | 33 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $where at &quot;SELECT COUNT(*) FROM $table WHERE $where&quot; |  |
| 67 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 67 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 67 | 27 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_results(&quot;SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset&quot;)\n$per_page assigned unsafely at line 42:\n $per_page = min(100, max(1, (int)$req-&gt;get_param(&#039;per_page&#039;) ?: 20))\n$offset assigned unsafely at line 43:\n $offset = ($page - 1) * $per_page\n$req used without escaping. |  |
| 68 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset&quot; |  |
| 68 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $where at &quot;SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset&quot; |  |
| 68 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $per_page at &quot;SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset&quot; |  |
| 68 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $offset at &quot;SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset&quot; |  |
| 92 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 92 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 92 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id))\n$table assigned unsafely at line 90:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039;\n$entry assigned unsafely at line 92:\n $entry = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id), ARRAY_A) |  |
| 92 | 48 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE id=%d&quot; |  |
| 115 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 138 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 138 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 138 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id))\n$table assigned unsafely at line 137:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039;\n$entry assigned unsafely at line 138:\n $entry = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id), ARRAY_A) |  |
| 138 | 48 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE id=%d&quot; |  |
| 151 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 151 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 167 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 167 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 167 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id))\n$table assigned unsafely at line 166:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039;\n$entry assigned unsafely at line 167:\n $entry = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id), ARRAY_A) |  |
| 167 | 48 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE id=%d&quot; |  |
| 168 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 168 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 194 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 194 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 195 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $placeholders at &quot;DELETE FROM {$wpdb-&gt;prefix}atlaspress_entries WHERE id IN ($placeholders)&quot; |  |
| 195 | 88 | WARNING | WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare | Replacement variables found, but no valid placeholders found in the query. |  |
| 206 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 206 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 226 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 226 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 226 | 33 | WARNING | WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber | Incorrect number of replacements passed to $wpdb-&gt;prepare(). Found 2 replacement parameters, expected 1. |  |
| 227 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $placeholders at &quot;UPDATE {$wpdb-&gt;prefix}atlaspress_entries SET status=%s WHERE id IN ($placeholders)&quot; |  |
| 240 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 240 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 240 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id))\n$table assigned unsafely at line 238:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039;\n$entry assigned unsafely at line 240:\n $entry = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;, $id), ARRAY_A) |  |
| 240 | 48 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE id=%d&quot; |  |
| 243 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 258 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 258 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 258 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT settings FROM $table WHERE id=%d&quot;, $type_id))\n$table assigned unsafely at line 257:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 258:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT settings FROM $table WHERE id=%d&quot;, $type_id), ARRAY_A)\n$type_id used without escaping. |  |
| 258 | 47 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT settings FROM $table WHERE id=%d&quot; |  |
| 278 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 278 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 278 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT settings FROM $table WHERE id=%d&quot;, $type_id))\n$table assigned unsafely at line 277:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$type assigned unsafely at line 278:\n $type = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT settings FROM $table WHERE id=%d&quot;, $type_id), ARRAY_A)\n$type_id used without escaping. |  |
| 278 | 47 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT settings FROM $table WHERE id=%d&quot; |  |
| 298 | 34 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 307 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 307 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\FormGeneratorController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 21 | 59 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 23 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 23 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\GraphQLController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 94 | 15 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 94 | 15 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 94 | 22 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $query used in $wpdb->get_results($wpdb->prepare($query, ...$params))\n$query assigned unsafely at line 89:\n $query .= " LIMIT %d"\n$params[] used without escaping.\n$args['limit'] used without escaping. |  |
| 94 | 49 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 95 | 22 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $query used in $wpdb->get_results($query)\n$query assigned unsafely at line 89:\n $query .= " LIMIT %d"\n$params[] used without escaping.\n$args['limit'] used without escaping. |  |
| 95 | 34 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 130 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 130 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 130 | 27 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $query used in $wpdb->get_results($wpdb->prepare($query, ...$params))\n$query assigned unsafely at line 123:\n $query .= " LIMIT %d"\n$args['limit'] used without escaping. |  |
| 130 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 147 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 147 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 147 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $table WHERE id=%d&quot;,\r\n $args[&#039;id&#039;]\r\n ))\n$table assigned unsafely at line 141:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_entries&#039; |  |
| 148 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE id=%d&quot; |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\RelationshipController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 37 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 37 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 37 | 27 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $query used in $wpdb->get_results($wpdb->prepare($query, ...$params))\n$query assigned unsafely at line 35:\n $query .= " ORDER BY title ASC LIMIT 50"\n$query assigned unsafely at line 31:\n $query .= " AND title LIKE %s"\n$entries assigned unsafely at line 37:\n $entries = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A)\n$params assigned unsafely at line 32:\n $params[] = '%' . $wpdb->esc_like($search) . '%'\n$params[] used without escaping.\n$search assigned unsafely at line 24:\n $search = sanitize_text_field($req->get_param('search'))\nNote: sanitize_text_field() is not a safe escaping function.\n$req used without escaping. |  |
| 37 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 61 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 61 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 61 | 27 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $query used in $wpdb->get_results($wpdb->prepare($query, ...$params))\n$query assigned unsafely at line 58:\n $query .= "MATCH(title, data) AGAINST(%s IN NATURAL LANGUAGE MODE) LIMIT 20"\n$search assigned unsafely at line 44:\n $search = sanitize_text_field($req->get_param('q'))\nNote: sanitize_text_field() is not a safe escaping function.\n$req used without escaping. |  |
| 61 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |

## `includes/Admin/Pages/Integration.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/ApiSecurity.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/Loader.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/Network.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/Permissions.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/RealTime.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/Security.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/Version.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Core/Webhooks.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/FormProxy.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Integrations/HubSpot.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `includes/Rest/ContentTypesController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Admin\Menu.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 16 | 30 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 16 | 55 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Admin\Pages\SecuritySettings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 9 | 12 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 9 | 12 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;action&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 9 | 12 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;action&#039;] |  |
| 40 | 65 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 40 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;allowed_origins&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 40 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;allowed_origins&#039;] |  |
| 49 | 19 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 50 | 45 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 50 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;api_key_name&#039;]. Check that the array index exists before using it. |  |
| 50 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;api_key_name&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Admin\Pages\SetupWizard.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 9 | 17 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 9 | 17 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;step&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 9 | 17 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;step&#039;] |  |
| 24 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 24 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 29 | 43 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;setup_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 30 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;project_name&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 63 | 13 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 75 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 75 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 87 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 87 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 87 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $content_types_table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$content_types_table&#039;&quot;)\n$content_types_table assigned unsafely at line 84:\n $content_types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039;\n$entries_table assigned unsafely at line 85:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039; |  |
| 87 | 32 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $content_types_table at &quot;SHOW TABLES LIKE &#039;$content_types_table&#039;&quot; |  |
| 88 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $content_types_table used in $wpdb-&gt;query(&quot;DELETE FROM $content_types_table&quot;)\n$content_types_table assigned unsafely at line 84:\n $content_types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039;\n$entries_table assigned unsafely at line 85:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039; |  |
| 88 | 30 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $content_types_table at &quot;DELETE FROM $content_types_table&quot; |  |
| 91 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 91 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 91 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$entries_table&#039;&quot;)\n$entries_table assigned unsafely at line 85:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$content_types_table assigned unsafely at line 84:\n $content_types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 91 | 32 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at &quot;SHOW TABLES LIKE &#039;$entries_table&#039;&quot; |  |
| 92 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;query(&quot;DELETE FROM $entries_table&quot;)\n$entries_table assigned unsafely at line 85:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$content_types_table assigned unsafely at line 84:\n $content_types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 92 | 30 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at &quot;DELETE FROM $entries_table&quot; |  |
| 108 | 13 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 118 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 118 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 118 | 20 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$table&#039;&quot;)\n$table assigned unsafely at line 116:\n $table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 118 | 28 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 130 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 130 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 130 | 20 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$table&#039;&quot;)\n$table assigned unsafely at line 128:\n $table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 130 | 28 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 142 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 142 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 142 | 20 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$table&#039;&quot;)\n$table assigned unsafely at line 140:\n $table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 142 | 28 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\ApiSecurity.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 92 | 19 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_AUTHORIZATION&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 92 | 19 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_AUTHORIZATION&#039;] |  |
| 123 | 19 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_ORIGIN&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 123 | 19 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_ORIGIN&#039;] |  |
| 137 | 16 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REQUEST_URI&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 137 | 16 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REQUEST_URI&#039;] |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\Installer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 56 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 56 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 56 | 26 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var($wpdb-&gt;prepare(\r\n &quot;SHOW INDEX FROM $table WHERE Key_name = %s&quot;,\r\n $index_name\r\n )) |  |
| 57 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW INDEX FROM $table WHERE Key_name = %s&quot; |  |
| 61 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 61 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 61 | 20 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;query(&quot;ALTER TABLE $table ADD INDEX $index_name ($columns)&quot;)\n$index_name used without escaping.\n$columns used without escaping. |  |
| 61 | 26 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;ALTER TABLE $table ADD INDEX $index_name ($columns)&quot; |  |
| 61 | 26 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $index_name at &quot;ALTER TABLE $table ADD INDEX $index_name ($columns)&quot; |  |
| 61 | 26 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $columns at &quot;ALTER TABLE $table ADD INDEX $index_name ($columns)&quot; |  |
| 61 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\Loader.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 90 | 19 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 91 | 45 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 91 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;api_key_name&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 97 | 19 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 98 | 93 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 98 | 93 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;allowed_origins&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 116 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;webhooks&#039;]. Check that the array index exists before using it. |  |
| 116 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;webhooks&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 116 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;webhooks&#039;] |  |
| 130 | 89 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;allowed_origins&#039;]. Check that the array index exists before using it. |  |
| 130 | 89 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;allowed_origins&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\Network.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 35 | 29 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 35 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;source_site&#039;]. Check that the array index exists before using it. |  |
| 36 | 45 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 36 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;target_sites&#039;]. Check that the array index exists before using it. |  |
| 37 | 46 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 37 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;content_types&#039;]. Check that the array index exists before using it. |  |
| 45 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 45 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 45 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_results($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $types_table WHERE id IN (&quot; . implode(&#039;,&#039;, array_fill(0, count($content_types), &#039;%d&#039;)) . &quot;)&quot;,\r\n ...$content_types\r\n ))\n$types_table assigned unsafely at line 42:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039;\n$entries_table assigned unsafely at line 43:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$types assigned unsafely at line 45:\n $types = $wpdb-&gt;get_results($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $types_table WHERE id IN (&quot; . implode(&#039;,&#039;, array_fill(0, count($content_types), &#039;%d&#039;)) . &quot;)&quot;,\r\n ...$content_types\r\n ), ARRAY_A) |  |
| 46 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at &quot;SELECT * FROM $types_table WHERE id IN (&quot; |  |
| 50 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 50 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 50 | 27 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_results($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $entries_table WHERE content_type_id IN (&quot; . implode(&#039;,&#039;, array_fill(0, count($content_types), &#039;%d&#039;)) . &quot;)&quot;,\r\n ...$content_types\r\n ))\n$entries_table assigned unsafely at line 43:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$types assigned unsafely at line 45:\n $types = $wpdb-&gt;get_results($wpdb-&gt;prepare(\r\n &quot;SELECT * FROM $types_table WHERE id IN (&quot; . implode(&#039;,&#039;, array_fill(0, count($content_types), &#039;%d&#039;)) . &quot;)&quot;,\r\n ...$content_types\r\n ), ARRAY_A)\n$types_table assigned unsafely at line 42:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 51 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at &quot;SELECT * FROM $entries_table WHERE content_type_id IN (&quot; |  |
| 61 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 61 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 71 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 71 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 98 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 98 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 99 | 30 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 99 | 30 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\Security.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 29 | 29 | WARNING | WordPress.DB.SlowDBQuery.slow_db_query_meta_key | Detected usage of meta_key, possible slow query. |  |
| 45 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[$header] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 45 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[$header] |  |
| 50 | 16 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 50 | 16 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\Version.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 44 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 44 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 44 | 16 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;query(&quot;ALTER TABLE $table ADD INDEX idx_search (title, status)&quot;)\n$table assigned unsafely at line 43:\n $table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039; |  |
| 44 | 22 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;ALTER TABLE $table ADD INDEX idx_search (title, status)&quot; |  |
| 44 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 60 | 12 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 60 | 12 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 60 | 19 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $old_table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$old_table&#039;&quot;)\n$old_table assigned unsafely at line 59:\n $old_table = $wpdb-&gt;prefix . &#039;old_atlaspress_data&#039;\n$upgrader_object used without escaping.\n$options used without escaping.\n$options[&#039;type&#039;] used without escaping.\n$options[&#039;plugins&#039;] used without escaping.\n$plugin used without escaping. |  |
| 60 | 27 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $old_table at &quot;SHOW TABLES LIKE &#039;$old_table&#039;&quot; |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\ContentTypesController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 50 | 12 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 50 | 12 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 50 | 19 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$table&#039;&quot;)\n$table assigned unsafely at line 47:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039; |  |
| 50 | 27 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 54 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 54 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 54 | 27 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_results(&quot;SELECT * FROM $table ORDER BY id DESC&quot;)\n$table assigned unsafely at line 47:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039; |  |
| 54 | 39 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table ORDER BY id DESC&quot; |  |
| 72 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 72 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 26 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;,$id))\n$table assigned unsafely at line 71:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$result assigned unsafely at line 72:\n $result = $wpdb-&gt;get_row($wpdb-&gt;prepare(&quot;SELECT * FROM $table WHERE id=%d&quot;,$id),ARRAY_A) |  |
| 72 | 49 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT * FROM $table WHERE id=%d&quot; |  |
| 86 | 12 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 86 | 12 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 86 | 19 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var($wpdb-&gt;prepare(&quot;SELECT COUNT(*) FROM $table WHERE slug=%s&quot;,$slug))\n$table assigned unsafely at line 85:\n $table = $wpdb-&gt;prefix.&#039;atlaspress_content_types&#039;\n$slug assigned unsafely at line 84:\n $slug = sanitize_title($name)\nNote: sanitize_title() is not a safe escaping function.\n$name assigned unsafely at line 82:\n $name = sanitize_text_field($req[&#039;name&#039;])\nNote: sanitize_text_field() is not a safe escaping function.\n$req[&#039;name&#039;] used without escaping. |  |
| 86 | 42 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(*) FROM $table WHERE slug=%s&quot; |  |
| 89 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 108 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 108 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 111 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 111 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 134 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 134 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 135 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $placeholders at &quot;DELETE FROM {$wpdb-&gt;prefix}atlaspress_entries WHERE content_type_id IN ($placeholders)&quot; |  |
| 135 | 101 | WARNING | WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare | Replacement variables found, but no valid placeholders found in the query. |  |
| 140 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 140 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 141 | 13 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $placeholders at &quot;DELETE FROM {$wpdb-&gt;prefix}atlaspress_content_types WHERE id IN ($placeholders)&quot; |  |
| 141 | 94 | WARNING | WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare | Replacement variables found, but no valid placeholders found in the query. |  |
| 166 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 166 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Rest\DashboardController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 30 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 30 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 30 | 23 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_var(&quot;SHOW TABLES LIKE &#039;$types_table&#039;&quot;)\n$types_table assigned unsafely at line 26:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039;\n$entries_table assigned unsafely at line 27:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039; |  |
| 30 | 31 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at &quot;SHOW TABLES LIKE &#039;$types_table&#039;&quot; |  |
| 42 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 42 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 42 | 29 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_results(&quot;SELECT * FROM $types_table ORDER BY created_at DESC&quot;)\n$types_table assigned unsafely at line 26:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039;\n$entries_table assigned unsafely at line 27:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039; |  |
| 42 | 42 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at &quot;SELECT * FROM $types_table ORDER BY created_at DESC&quot; |  |
| 43 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 43 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 43 | 36 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_var(&quot;SELECT COUNT(*) FROM $entries_table&quot;)\n$entries_table assigned unsafely at line 27:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$types_table assigned unsafely at line 26:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 43 | 45 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at &quot;SELECT COUNT(*) FROM $entries_table&quot; |  |
| 46 | 30 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 46 | 30 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 46 | 37 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_results(&quot;SELECT e.*, ct.name as content_type_name \r\n FROM $entries_table e \r\n LEFT JOIN $types_table ct ON e.content_type_id = ct.id \r\n ORDER BY e.created_at DESC LIMIT 5&quot;)\n$entries_table assigned unsafely at line 27:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$types_table assigned unsafely at line 26:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 48 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at FROM $entries_table e \r\n |  |
| 49 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at LEFT JOIN $types_table ct ON e.content_type_id = ct.id \r\n |  |
| 55 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 55 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 55 | 32 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $types_table used in $wpdb-&gt;get_results(&quot;SELECT ct.name, COUNT(e.id) as entry_count \r\n FROM $types_table ct \r\n LEFT JOIN $entries_table e ON ct.id = e.content_type_id \r\n GROUP BY ct.id \r\n ORDER BY entry_count DESC LIMIT 5&quot;)\n$types_table assigned unsafely at line 26:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039;\n$entries_table assigned unsafely at line 27:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039; |  |
| 57 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $types_table at FROM $types_table ct \r\n |  |
| 58 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at LEFT JOIN $entries_table e ON ct.id = e.content_type_id \r\n |  |
| 65 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 65 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 65 | 35 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $entries_table used in $wpdb-&gt;get_results(&quot;SELECT DATE(created_at) as date, COUNT(*) as count \r\n FROM $entries_table \r\n WHERE created_at &gt;= DATE_SUB(NOW(), INTERVAL 7 DAY) \r\n GROUP BY DATE(created_at) \r\n ORDER BY date DESC&quot;)\n$entries_table assigned unsafely at line 27:\n $entries_table = $wpdb-&gt;prefix . &#039;atlaspress_entries&#039;\n$types_table assigned unsafely at line 26:\n $types_table = $wpdb-&gt;prefix . &#039;atlaspress_content_types&#039; |  |
| 67 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $entries_table at FROM $entries_table \r\n |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\Cache.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 32 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 32 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 37 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 37 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `C:\Users\hp\Local Sites\my-workstation\app\public\wp-content\plugins\atlaspress\includes\Core\CLI.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 36 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 36 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 37 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 37 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 66 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 66 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 67 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 67 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 99 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 99 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 104 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 104 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
