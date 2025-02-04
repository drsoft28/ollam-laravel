<?php
namespace Drsoft28\OllamaLaravel;

class OllamaHelper {
    /**
 * Extracts the first complete JSON object from the buffer.
 *
 * @param string $buffer The buffer containing partial or complete JSON strings.
 * @return string|null The extracted JSON object as a string, or null if no complete object is found.
 */
static public function extractJsonObject(string $buffer): ?string
{
    // Regular expression to match a JSON object
    $pattern = '/\{(?:[^{}]|(?R))*\}/';

    if (preg_match($pattern, $buffer, $matches)) {
        return $matches[0]; // Return the first match
    }

    return null; // No complete JSON object found
}
}