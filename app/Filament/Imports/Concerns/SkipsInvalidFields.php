<?php

namespace App\Filament\Imports\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Lenient CSV import: a bad value in an optional field (e.g. a rating out of range,
 * text in a number column, an unknown parent id) is left empty instead of failing
 * the whole row. Only required fields (title, name, store...) can still fail a row.
 */
trait SkipsInvalidFields
{
    public function castData(): void
    {
        // text with no digits in a number column (e.g. "lots") would otherwise become 0
        foreach ($this->getCachedColumns() as $column) {
            $field = $column->getName();
            $value = $this->data[$field] ?? null;

            if ($column->isNumeric() && is_string($value) && filled($value) && ! preg_match('/\d/', $value)) {
                $this->data[$field] = null;
            }
        }

        parent::castData();
    }

    public function validateData(): void
    {
        $required = [];

        foreach ($this->getValidationRules() as $field => $rules) {
            $isRequired = collect($rules)->contains(fn ($rule) => is_string($rule) && str_starts_with($rule, 'required'));

            if ($isRequired) {
                $required[$field] = $rules;
                continue;
            }

            if (! array_key_exists($field, $this->data)) {
                continue;
            }

            if (Validator::make([$field => $this->data[$field]], [$field => $rules])->fails()) {
                Log::warning('CSV import: invalid value left empty', [
                    'importer' => class_basename(static::class),
                    'field' => $field,
                    'value' => is_scalar($this->data[$field]) ? $this->data[$field] : json_encode($this->data[$field]),
                ]);
                unset($this->data[$field]);
            }
        }

        Validator::validate($this->data, $required, $this->getValidationMessages(), $this->getValidationAttributes());
    }
}
