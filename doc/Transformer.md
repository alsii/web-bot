# Transformer Trait

Transformer trait provides a transformer functionality to the class being applied to.

## Methods

### transformToForm($data, $form, $options)

Extracts values from the provided data and fills in the provided Form.

#### Parameters
 
- `array $data` - Array of data to process
- `FormInterface $form` - Form to fill in with extracted values
- `array $options` - Array of transformations. Each transformation is an array of transformation options.

The following transformation options are supported

#### Value source options

One and the only one of the following source options should be provided:

##### const

Specifies a constant value to be filled into a specified field

###### Suboptions
    
- `field` - a field name to fill in
- `value` - a constant value

###### Example

Simple transformation that fills the field `answer` with value of `42`

```php
[
    'const' => ['field' => 'answer', 'value' => 42],
]
```

##### path

An array, specifies a path in the data array, which contains a value to be extracted.

###### Example

To extract a value of `theAnswer` from the following JSON-data

```json
{
    "section": {
        "subsection": {
            "too-small": 41,
            "theAnswer": 42,
            "too_big": 43
        }
    }
}
```

use the following transformation

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
]
```

##### default

A default value to be used together with the path option only. If the specified path does not exist and 
the `default` option is specified, a value of the `default` option will be used as a result.
If the `default` option is not specified and the specified does not exist, the `TransformationException`
with the code `TransformationException::CODE_PATH_NOT_EXISTS` will be thrown.

###### Example

Value of 42 will be used if the specified path does not exist

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'default' => 42
]
```

#### Destination options

One and the only one of the following source options should be provided:

##### field

Specified a field name to be filled in

###### Example
    
This transformation takes the value from the specified path and places it into
the form field with the name of `answer`.

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'field' => 'answer'
]
```

##### map

An array of mapping options, to transform extracted values and fill them into the
specified fields. Keys of the array are transformed values itself. 

###### Suboptions

- `field` - a field name to fill in
- `value` - a constant value

###### Example

To extract a key value of `theAnswer` from the following JSON-data, replace it with a text
representation and place it into the `answer` field

```json
{
    "section": {
        "subsection": {
            "theAnswer": 42
        }
    }
}
```

use the following transformation

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'map' => [
        41 => ['value' => 'wrong answer', 'field' => 'answer'], 
        42 => ['value' => 'right answer', 'field' => 'answer'], 
        43 => ['value' => 'wrong answer', 'field' => 'answer'], 
    ]
]
```

You cau specify different field names for the different key values

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'map' => [
        41 => ['value' => 'forty one', 'field' => 'wrongAnswer'], 
        42 => ['value' => 'forty two', 'field' => 'rightAnswer'], 
        43 => ['value' => 'forty three', 'field' => 'wrongAnswer'], 
    ]
]
```

or even can specify `null` to fill in no field

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'map' => [
        41 => null, 
        42 => ['value' => 'forty two', 'field' => 'answer'], 
        43 => null, 
    ]
]
```

You should provide a full set of the possible key values. If the actual key value is not
matches with any of values you specified, then a `TransformationException` with the code 
`TransformationException::CODE_UNKNOWN_MAP_KEY` will be thrown.

#### Additional options

##### Processor

For a complex processing of the extracted value use an `processor` option as an
addition to the source and destination options. Processor should be a PHP-callable wich
accept the extracted value as the only parameter. Processor should return the processed value.
Processor applied just after the value extraction and before the `map` transformation.

###### Examples:

Processor callable

```php
private static function toHex($value)
{
    return dechex($value);
}
```

Processor with a `field` destination option 

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'processor' => [self, 'toHex'],
    'field' => 'hexAnswer',
]
```

Processor with a `map` destination option

```php
[
    'path' => ['section', 'subsection', 'theAnswer'],
    'processor' => [self, 'toHex'],
    'map' => [
        '29' => ['value' => 'forty one', 'field' => 'answer'], 
        '2a' => ['value' => 'forty two', 'field' => 'answer'], 
        '2b' => ['value' => 'forty three', 'field' => 'answer'], 
    ]
]
```

##### Test

Use `test` option to conditionally execute the transformation.

###### Suboptions

- `processor` - a PHP callable to provide a check
- `field` - a field name in the target form to check
- `path` - a path in the source data to check
- `value` - a value to compare with
- `strict` - throw a `TransformationException` if source does not exist

Either `processor` or `field` or `path` options should be specified. If more than one
are specified, only the first in the order mentioned above will be used.

`processor` callable should accept three parameters:

- `array $cond` - full content of the test option's data  
- `FormInterface $form` - form to be filled in
- `array $data` - full data to be transformed

`$cond` can contain any key-value pairs to be used by the `processor` callable.
`$form` and `$data` can be used for condition checking purpose only and can not be changed.

The return value of the `processor` callable will be converted to the boolean. The transformation will be
executed only if the value is true. 

###### Examples

Processor callable

```php
private static function checkAnswerData(array $cond, FormInterface $form, array $data)
{
    $answer = $data['section']['subsection']['theAnswer'] ?? null;     
    if ($answer === null) {
        throw new TransformationException(
            'No theAnswer data specified',
            TransformationException::CODE_PATH_NOT_EXISTS
        )
    }

    $min = $cond['min'] ?? PHP_INT_MIN;
    $max = $cond['max'] ?? PHP_INT_MAX;

    return $answer >= min && $answer <= $max; 
}
```

Test with the processor

```php
[
    'test' => ['processor' => [self, checkAnswerData], 'min' => 41, 'max' => 43]
    'path' => ['section', 'subsection', 'theAnswer'],
    'map' => [
        41 => ['value' => 'forty one', 'field' => 'wrongAnswer'], 
        42 => ['value' => 'forty two', 'field' => 'rightAnswer'], 
        43 => ['value' => 'forty three', 'field' => 'wrongAnswer'], 
    ]
]
```

If a `field` option or a `path` options are specified, the value contained in them will be checked.

If a `value` option is specified the value from a `field` or a `path` will be strictly compared with it.
The transformation will be executed only if the compared values are equal. If the `field` or `path` does not
exist an `strict` is `true`, the TransformationException will be thrown. If `strict` is `false`
the transformation will not be executed.

If a `value` option is not specified, the transformation will be executed only if `field` or `path` exists.

###### Examples

Test data

```json
{
    "section": {
        "subsection": {
            "theAnswer": 42
        }
    }
}
```

Only path is specified and exists - `true`

```php
[
    'test' => ['path' => ['section', 'subsection', 'theAnswer']]
]
```

Only path is specified and does not exist - `false`

```php
[
    'test' => ['path' => ['section', 'subsection', 'notTheAnswer']]
]
```

Only path is specified and does not exist. Value is not specified - `false`

`strict` option is specified and is `true` but not taken into account because no value is specified.

```php
[
    'test' => ['path' => ['section', 'subsection', 'notTheAnswer'], 'strict' => 'true']
]
```

Path is specified and exists. Value is specified and is equal to the value extracted from the path. - `true` 

```php
[
    'test' => ['path' => ['section', 'subsection', 'theAnswer'], 'value' => 42]
]
```

Path is specified and exists. Value is specified and is not equal to the value extracted from the path. - `false` 

```php
[
    'test' => ['path' => ['section', 'subsection', 'theAnswer'], 'value' => 43]
]

```
Path is specified but does not exist. Value is specified. Strict is not specified. - `false`

```php
[
    'test' => ['path' => ['section', 'subsection', 'notTheAnswer'], 'value' => 42]
]
```

Path is specified but does not exist. Value is specified. Strict is specified and is true - `TransformationException`.

```php
[
    'test' => ['path' => ['section', 'subsection', 'notTheAnswer'], 'value' => 42, 'strict' => true]
]
```

