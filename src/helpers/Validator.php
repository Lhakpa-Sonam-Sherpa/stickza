<?php
/**
 * Validator – reusable input validation helper.
 *
 * Usage:
 *   $v = new Validator($_POST);
 *   $v->required('name')->maxLength('name', 100)
 *     ->email('email')
 *     ->minLength('password', 6);
 *   if ($v->fails()) { $errors = $v->errors(); }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ── Fluent rule methods ─────────────────────────────────

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field][] = "$label is required.";
        }
        return $this;
    }

    public function email(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "$label must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        if (strlen($value) > 0 && strlen($value) < $min) {
            $this->errors[$field][] = "$label must be at least $min characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        if (strlen($value) > $max) {
            $this->errors[$field][] = "$label must not exceed $max characters.";
        }
        return $this;
    }

    public function numeric(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field][] = "$label must be a number.";
        }
        return $this;
    }

    public function min(string $field, float $min, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        if (is_numeric($value) && (float)$value < $min) {
            $this->errors[$field][] = "$label must be at least $min.";
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (($this->data[$field] ?? '') !== ($this->data[$otherField] ?? '')) {
            $this->errors[$field][] = "$label does not match.";
        }
        return $this;
    }

    public function phone(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $value)) {
            $this->errors[$field][] = "$label must be a valid phone number.";
        }
        return $this;
    }

    /** Validate an uploaded image: checks MIME type (not just extension). */
    public function image(string $field, int $maxBytes = 2097152, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $file  = $_FILES[$field] ?? null;

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $this;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[$field][] = "$label upload failed (error code {$file['error']}).";
            return $this;
        }
        if ($file['size'] > $maxBytes) {
            $mb = round($maxBytes / 1048576, 1);
            $this->errors[$field][] = "$label must not exceed {$mb}MB.";
        }

        // MIME-type check using finfo (not just extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowed, true)) {
            $this->errors[$field][] = "$label must be a JPG, PNG, GIF, or WEBP image.";
        }

        return $this;
    }

    // ── Result methods ─────────────────────────────────────

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    /** Returns a flat array of all error strings. */
    public function errors(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $msg) {
                $flat[] = $msg;
            }
        }
        return $flat;
    }

    /** Returns per-field errors (field => [messages]). */
    public function errorsByField(): array
    {
        return $this->errors;
    }

    /** Update this in Validator.php to make it "Optional" */
    public function firstError(string $field = ''): string
    {
        if ($field === '') {
            $all = $this->errors(); 
            return $all[0] ?? '';
        }
        // Check if the key exists and has at least one entry
        return (!empty($this->errors[$field])) ? $this->errors[$field][0] : '';
    }

    public function addError(string $field, string $message): static
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    public function max(string $field, float $max, string $label = ''): static
{
    $label = $label ?: ucfirst(str_replace('_', ' ', $field));
    $value = $this->data[$field] ?? '';

    if (is_numeric($value) && (float)$value > $max) {
        $this->addError($field, "$label must not be greater than $max.");
    }
    return $this;
}
}
