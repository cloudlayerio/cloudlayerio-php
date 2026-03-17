<?php

declare(strict_types=1);

namespace CloudLayer\Utils;

/**
 * Serializes SDK option arrays/objects to API-ready camelCase arrays.
 */
final class OptionSerializer
{
    /** Fields that are sent via multipart, not JSON */
    private const SDK_ONLY_FIELDS = ['file'];

    /** Fields where explicit null is meaningful and must be preserved */
    private const PRESERVE_NULL_FIELDS = ['emulateMediaType'];

    /**
     * Serialize an options array to an API-ready array.
     *
     * - Converts enum values to their string backing
     * - Converts nested objects recursively
     * - Strips null values (except preserved-null fields)
     * - Strips empty arrays
     * - Strips SDK-only fields
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function serialize(array $options): array
    {
        $result = [];

        foreach ($options as $key => $value) {
            // Strip SDK-only fields
            if (in_array($key, self::SDK_ONLY_FIELDS, true)) {
                continue;
            }

            // Preserve explicit null for special fields
            if ($value === null && array_key_exists($key, $options) && in_array($key, self::PRESERVE_NULL_FIELDS, true)) {
                $result[$key] = null;
                continue;
            }

            // Strip null values
            if ($value === null) {
                continue;
            }

            // Strip empty arrays
            if (is_array($value) && $value === []) {
                continue;
            }

            $result[$key] = self::serializeValue($value);
        }

        return $result;
    }

    private static function serializeValue(mixed $value): mixed
    {
        // Enum -> string backing value
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        // Object -> serialize its public properties
        if (is_object($value)) {
            return self::serializeObject($value);
        }

        // Array of items -> serialize each element
        if (is_array($value)) {
            return self::serializeArray($value);
        }

        // Scalar values pass through
        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private static function serializeObject(object $obj): array
    {
        $result = [];
        $reflection = new \ReflectionClass($obj);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $val = $prop->getValue($obj);

            if ($val === null) {
                continue;
            }

            if (is_array($val) && $val === []) {
                continue;
            }

            $result[$name] = self::serializeValue($val);
        }

        return $result;
    }

    /**
     * @param array<int|string, mixed> $arr
     * @return array<int|string, mixed>
     */
    private static function serializeArray(array $arr): array
    {
        $result = [];

        foreach ($arr as $key => $value) {
            $result[$key] = self::serializeValue($value);
        }

        return $result;
    }

    private function __construct()
    {
    }
}
