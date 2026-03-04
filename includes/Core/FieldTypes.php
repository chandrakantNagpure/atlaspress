<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FieldTypes {
    
    public static function getTypes() {
        return [
            'text' => [
                'label' => 'Text',
                'validation' => ['maxLength', 'minLength', 'pattern'],
                'settings' => ['placeholder', 'defaultValue']
            ],
            'textarea' => [
                'label' => 'Textarea',
                'validation' => ['maxLength', 'minLength'],
                'settings' => ['rows', 'placeholder', 'defaultValue']
            ],
            'richtext' => [
                'label' => 'Rich Text',
                'validation' => ['maxLength', 'minLength'],
                'settings' => ['height', 'toolbar']
            ],
            'number' => [
                'label' => 'Number',
                'validation' => ['min', 'max', 'step'],
                'settings' => ['defaultValue']
            ],
            'email' => [
                'label' => 'Email',
                'validation' => ['pattern'],
                'settings' => ['placeholder', 'defaultValue']
            ],
            'url' => [
                'label' => 'URL',
                'validation' => ['pattern'],
                'settings' => ['placeholder', 'defaultValue']
            ],
            'tel' => [
                'label' => 'Phone',
                'validation' => ['pattern'],
                'settings' => ['placeholder', 'defaultValue']
            ],
            'date' => [
                'label' => 'Date',
                'validation' => ['min', 'max'],
                'settings' => ['defaultValue']
            ],
            'time' => [
                'label' => 'Time',
                'validation' => ['min', 'max'],
                'settings' => ['defaultValue']
            ],
            'datetime' => [
                'label' => 'Date & Time',
                'validation' => ['min', 'max'],
                'settings' => ['defaultValue']
            ],
            'select' => [
                'label' => 'Select Dropdown',
                'validation' => ['required'],
                'settings' => ['options', 'multiple', 'defaultValue']
            ],
            'radio' => [
                'label' => 'Radio Buttons',
                'validation' => ['required'],
                'settings' => ['options', 'defaultValue']
            ],
            'checkbox' => [
                'label' => 'Single Checkbox',
                'validation' => [],
                'settings' => ['defaultValue', 'label']
            ],
            'checkboxes' => [
                'label' => 'Multiple Checkboxes',
                'validation' => ['minSelected', 'maxSelected'],
                'settings' => ['options', 'defaultValue']
            ],
            'range' => [
                'label' => 'Range Slider',
                'validation' => ['min', 'max', 'step'],
                'settings' => ['defaultValue']
            ],
            'color' => [
                'label' => 'Color Picker',
                'validation' => [],
                'settings' => ['defaultValue']
            ],
            'password' => [
                'label' => 'Password',
                'validation' => ['minLength', 'pattern'],
                'settings' => ['placeholder']
            ],
            'hidden' => [
                'label' => 'Hidden Field',
                'validation' => [],
                'settings' => ['defaultValue']
            ],
            'media' => [
                'label' => 'Media',
                'validation' => ['fileTypes', 'maxSize'],
                'settings' => ['multiple', 'mediaType']
            ],
            'file' => [
                'label' => 'File Upload',
                'validation' => ['fileTypes', 'maxSize', 'maxFiles'],
                'settings' => ['allowedTypes', 'multiple']
            ],
            'relationship' => [
                'label' => 'Relationship',
                'validation' => ['required'],
                'settings' => ['targetType', 'multiple', 'displayField']
            ],
            'json' => [
                'label' => 'JSON Data',
                'validation' => ['required'],
                'settings' => ['schema']
            ]
        ];
    }

    public static function validateField($field, $value) {
        $types = self::getTypes();
        if (!isset($types[$field['type']])) return false;

        $type = $types[$field['type']];
        $validation = $field['validation'] ?? [];
        $is_required = $field['required'] ?? ($validation['required'] ?? false);
        $is_empty = $value === null || $value === '' || (is_array($value) && count($value) === 0);

        if ($is_required && $is_empty) {
            return false;
        }

        // If optional and empty, skip further validation
        if (!$is_required && $is_empty) {
            return true;
        }

        // Basic type validation
        switch ($field['type']) {
            case 'number':
                if (!is_numeric($value)) return false;
                $min = $validation['min'] ?? $field['min'] ?? null;
                $max = $validation['max'] ?? $field['max'] ?? null;
                if ($min !== null && $value < $min) return false;
                if ($max !== null && $value > $max) return false;
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) return false;
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) return false;
                break;
            case 'tel':
                if (!preg_match('/^[\d\-\+\(\)\s\.]+$/', (string)$value)) return false;
                break;
            case 'date':
            case 'time':
            case 'datetime':
                if (strtotime((string)$value) === false) return false;
                break;
            case 'select':
            case 'radio':
                $options = $field['options'] ?? $validation['options'] ?? [];
                if (!empty($options) && !in_array($value, $options, true)) return false;
                break;
            case 'checkboxes':
                if (!is_array($value)) return false;
                $options = $field['options'] ?? $validation['options'] ?? [];
                if (!empty($options)) {
                    foreach ($value as $item) {
                        if (!in_array($item, $options, true)) return false;
                    }
                }
                $min_selected = $validation['minSelected'] ?? $field['minSelected'] ?? null;
                $max_selected = $validation['maxSelected'] ?? $field['maxSelected'] ?? null;
                if ($min_selected !== null && count($value) < $min_selected) return false;
                if ($max_selected !== null && count($value) > $max_selected) return false;
                break;
            case 'relationship':
                if (!is_numeric($value) && !is_array($value)) return false;
                break;
            case 'json':
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) return false;
                }
                break;
        }

        // Custom validation rules
        $min_length = $validation['minLength'] ?? $field['minLength'] ?? null;
        $max_length = $validation['maxLength'] ?? $field['maxLength'] ?? null;
        if (is_string($value)) {
            $len = strlen($value);
            if ($min_length !== null && $len < $min_length) return false;
            if ($max_length !== null && $len > $max_length) return false;
        }

        if (!empty($validation['pattern']) && is_string($value)) {
            if (@preg_match($validation['pattern'], '') === false) {
                // Invalid regex pattern in schema
                return false;
            }
            if (!preg_match($validation['pattern'], $value)) return false;
        }

        return true;
    }
}
