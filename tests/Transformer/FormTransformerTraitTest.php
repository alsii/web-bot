<?php
namespace Alsi\WebBotTests\Transformer;

use Alsi\WebBot\Form\FormInterface;
use Alsi\WebBot\Transformer\FormTransformerInterface;
use Alsi\WebBot\Transformer\FormTransformerTrait;
use Alsi\WebBot\Transformer\TransformationException;
use Alsi\WebBotTests\Form\FormTraitTest;
use PHPUnit\Framework\TestCase;

class FormTransformerTraitTest extends TestCase implements FormTransformerInterface
{
    use FormTransformerTrait;

    protected const OPTIONS = [
        [
            'name' => 'Const 1',
            'const' => ['field' => FormTraitTest::F_FIELD_1, 'value' => 42],
            'test' => [],
        ],
        [
            'name' => 'Const 2',
            'test' => ['field' => FormTraitTest::F_FIELD_1, 'value' => 44],
            'const' => ['field' => FormTraitTest::F_FIELD_2, 'value' => 44],
        ],
        [
            'name' => 'Field 1',
            'test' => ['field' => FormTraitTest::F_FIELD_1, 'value' => 42],
            'path' => ['container', 'field-two'],
            'field' => FormTraitTest::F_FIELD_2,
        ],
        [
            'name' => 'Const 3',
            'test' => ['field' => FormTraitTest::F_FIELD_1],
            'const' => ['field' => FormTraitTest::F_FIELD_4, 'value' => 42]
        ],
        [
            'name' => 'Const 3',
            'test' => ['field' => 'nonexistent-field'],
            'const' => ['field' => FormTraitTest::F_FIELD_5, 'value' => 42]
        ],
        [
            'name' => 'Field 2',
            'test' => ['path' => ['test'], 'value' => 'forty-two'],
            'path' => ['container', 'field-three'],
            'field' => FormTraitTest::F_FIELD_3,
            'processor' => [self::class, 'dateProcessor'],
        ],
        [
            'name' => 'Field 2 strict',
            'test' => ['path' => ['test-strict'], 'value' => 'forty-two', 'strict' => true],
            'path' => ['container', 'field-three'],
            'field' => FormTraitTest::F_FIELD_3,
            'processor' => [self::class, 'dateProcessor'],
        ],
        [
            'name' => 'Field 3',
            'test' => ['processor' => [self::class, 'conditionProcessor'], 'val' => false],
            'path' => ['container', 'field-three'],
            'field' => FormTraitTest::F_FIELD_3,
        ],
        [
            'name' => 'Field Hausnummer 1',
            'path' => ['container', 'hausnummer'],
            'field' => FormTraitTest::F_HN1,
            'processor' => [self::class, 'extractHausnummer'],
        ],
        [
            'name' => 'Field Hausnummerzusatz 1',
            'path' => ['container', 'hausnummer'],
            'field' => FormTraitTest::F_HNZ1,
            'processor' => [self::class, 'extractHausnummerZusatz'],
        ],
        [
            'name' => 'Field Hausnummer 2',
            'path' => ['container', 'field-two'],
            'field' => FormTraitTest::F_HN2,
            'processor' => [self::class, 'extractHausnummer'],
        ],
        [
            'name' => 'Field Hausnummerzusatz 2',
            'path' => ['container', 'field-two'],
            'field' => FormTraitTest::F_HNZ2,
            'processor' => [self::class, 'extractHausnummerZusatz'],
        ],
        [
            'name' => 'Field Count',
            'path' => ['container', 'test-array'],
            'field' => FormTraitTest::F_COUNT,
            'processor' => [self::class, 'count_array_elements'],
        ],
        [
            'name' => 'Field Count',
            'path' => ['container', 'test-array'],
            'field' => FormTraitTest::F_COUNT_PLUS_ONE,
            'processor' => [self::class, 'count_array_elements_plus_one'],
        ],
        [
            'name' => 'Array 1 map',
            'path' => ['container', 'test-map-array'],
            'map' => [
                'map-key-1' => ['field' => FormTraitTest::F_MAP_ARRAY, 'value' =>'map-value-1'],
                'map-key-2' => ['field' => FormTraitTest::F_MAP_ARRAY, 'value' =>'map-value-2'],
            ]
        ],
        [
            'name' => 'Array 2 no map',
            'path' => ['container', 'test-array'],
        ],
        [
            'name' => 'Map scalar 1',
            'path' => ['container', 'map-scalar-1'],
            'map' => [
                true => ['field' => FormTraitTest::F_MAP_FIELD1, 'value' => 'map-value-1'],
                false => ['field' => FormTraitTest::F_MAP_FIELD1, 'value' => null],
            ]
        ],
        [
            'name' => 'Map scalar 2',
            'path' => ['container', 'map-scalar-2'],
            'map' => [
                true => ['field' => FormTraitTest::F_MAP_FIELD2, 'value' => 'map-value-2'],
                false => null,
            ]
        ],
        [
            'name' => 'Test path without value',
            'test' => ['path' => ['test-empty']],
            'path' => ['test-empty'],
            'field' => FormTraitTest::F_TEST_FIELD,
        ],
        [
            'name' => 'Default value',
            'path' => ['nonexistent-field'],
            'default' => 'default-value',
            'field' => FormTraitTest::F_DEFAULT,
        ],
        [
            'name' => 'test operator: same',
            'test' => ['path' => ['test-op', 'same'], 'value' => 42, 'op' =>'same'],
            'const' => ['field' => FormTraitTest::F_OP_SAME_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: not-same',
            'test' => ['path' => ['test-op', 'not-same'], 'value' => 42, 'op' =>'not-same'],
            'const' => ['field' => FormTraitTest::F_OP_NOTSAME_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: eq',
            'test' => ['path' => ['test-op', 'eq'], 'value' => 42, 'op' =>'eq'],
            'const' => ['field' => FormTraitTest::F_OP_EQ_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: neq',
            'test' => ['path' => ['test-op', 'neq'], 'value' => 42, 'op' =>'neq'],
            'const' => ['field' => FormTraitTest::F_OP_NEQ_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: lt',
            'test' => ['path' => ['test-op', 'lt'], 'value' => 42, 'op' =>'lt'],
            'const' => ['field' => FormTraitTest::F_OP_LT_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: le',
            'test' => ['path' => ['test-op', 'le'], 'value' => 42, 'op' =>'le'],
            'const' => ['field' => FormTraitTest::F_OP_LE_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: ge',
            'test' => ['path' => ['test-op', 'ge'], 'value' => 42, 'op' =>'ge'],
            'const' => ['field' => FormTraitTest::F_OP_GE_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: gt',
            'test' => ['path' => ['test-op', 'gt'], 'value' => 42, 'op' =>'gt'],
            'const' => ['field' => FormTraitTest::F_OP_GT_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: instr',
            'test' => ['path' => ['test-op', 'instr'], 'value' => 42, 'op' =>'instr'],
            'const' => ['field' => FormTraitTest::F_OP_INSTR_FIELD, 'value' => 42]
        ],
        [
            'name' => 'test operator: not-instr',
            'test' => ['path' => ['test-op', 'not-instr'], 'value' => 42, 'op' =>'not-instr'],
            'const' => ['field' => FormTraitTest::F_OP_NOTINSTR_FIELD, 'value' => 42]
        ],
    ];

    public function conditionProcessor($cond)
    {
        return $cond['val'];
    }

    public function transform($data): FormInterface
    {
        return $this->transformToForm($data, new FormTraitTest());
    }

    public function testGetSelectors(): void {
        $this->assertEquals([], $this->getSelectors());
    }

    public function testSetForm(): void
    {
        $result = $this->setForm(new FormTraitTest());
        $this->assertEquals($this, $result);
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider transformSuccessDataProvider
     */
    public function testTransformSuccess($data, $expected): void
    {
        $result = $this->transform($data);
        $this->assertEquals($expected, $result->getData());
    }

    /**
     * @return array
     */
    public function transformSuccessDataProvider(): array
    {
        return [
            'Success 1' => [
                [
                    'test' => 'forty-two',
                    'test-strict' => false,
                    'test-empty' => '42',
                    'container' => [
                        'field-two' => 'forty two',
                        'field-three' => '2020-03-11',
                        'hausnummer' => '42 a',
                        'test-map-array' => [
                            'map-key-1' => true,
                            'map-key-2' => false,
                        ],
                        'map-scalar-1' => true,
                        'map-scalar-2' => false,
                        'test-array' => [
                            FormTraitTest::F_ARRAY1 => 'array-1',
                            FormTraitTest::F_ARRAY2 => 'array-2',
                        ]
                    ]
                ],
                [
                    FormTraitTest::F_FIELD_1 => 42,
                    FormTraitTest::F_FIELD_2 => 'forty two',
                    FormTraitTest::F_FIELD_3 => '11.03.2020',
                    FormTraitTest::F_FIELD_4 => 42,
                    FormTraitTest::F_HN1 => '42',
                    FormTraitTest::F_HNZ1 => 'a',
                    FormTraitTest::F_HN2 => null,
                    FormTraitTest::F_HNZ2 => null,
                    FormTraitTest::F_COUNT => 2,
                    FormTraitTest::F_COUNT_PLUS_ONE => 3,
                    FormTraitTest::F_MAP_ARRAY => ['map-value-1', 'map-value-2'],
                    FormTraitTest::F_MAP_FIELD1 => 'map-value-1',
                    FormTraitTest::F_ARRAY1 => 'array-1',
                    FormTraitTest::F_ARRAY2 => 'array-2',
                    FormTraitTest::F_TEST_FIELD => '42',
                    FormTraitTest::F_DEFAULT => 'default-value',
                ],
            ],
            'Success 2' => [
                [
                    'test-strict' => false,
                    'container' => [
                        'field-two' => 'forty two',
                        'field-three' => '2020-03-11',
                        'hausnummer' => '42 a',
                        'test-map-array' => [
                            'map-key-1' => true,
                            'map-key-2' => false,
                        ],
                        'map-scalar-1' => true,
                        'map-scalar-2' => false,
                        'test-array' => [
                            FormTraitTest::F_ARRAY1 => 'array-1',
                            FormTraitTest::F_ARRAY2 => 'array-2',
                        ]
                    ]
                ],
                [
                    FormTraitTest::F_FIELD_1 => 42,
                    FormTraitTest::F_FIELD_2 => 'forty two',
                    FormTraitTest::F_FIELD_4 => 42,
                    FormTraitTest::F_HN1 => '42',
                    FormTraitTest::F_HNZ1 => 'a',
                    FormTraitTest::F_HN2 => null,
                    FormTraitTest::F_HNZ2 => null,
                    FormTraitTest::F_COUNT => 2,
                    FormTraitTest::F_COUNT_PLUS_ONE => 3,
                    FormTraitTest::F_MAP_ARRAY => ['map-value-1', 'map-value-2'],
                    FormTraitTest::F_MAP_FIELD1 => 'map-value-1',
                    FormTraitTest::F_ARRAY1 => 'array-1',
                    FormTraitTest::F_ARRAY2 => 'array-2',
                    FormTraitTest::F_DEFAULT => 'default-value',

                ],
            ],
            'Success 3: Test with OP' => [
                [
                    'test-strict' => false,
                    'container' => [
                        'field-two' => 'forty two',
                        'field-three' => '2020-03-11',
                        'hausnummer' => '42 a',
                        'test-map-array' => [
                            'map-key-1' => true,
                            'map-key-2' => false,
                        ],
                        'map-scalar-1' => true,
                        'map-scalar-2' => false,
                        'test-array' => [
                            FormTraitTest::F_ARRAY1 => 'array-1',
                            FormTraitTest::F_ARRAY2 => 'array-2',
                        ]
                    ],
                    'test-op' => [
                        'same' => 42,
                        'not-same' => '42',
                        'eq' => 42,
                        'neq' => 43,
                        'lt' => 41,
                        'le' => 42,
                        'ge' => 42,
                        'gt' => 43,
                        'instr' => 'the 42 number',
                        'not-instr' => 'the 41 number',
                    ],
                ],
                [
                    FormTraitTest::F_FIELD_1 => 42,
                    FormTraitTest::F_FIELD_2 => 'forty two',
                    FormTraitTest::F_FIELD_4 => 42,
                    FormTraitTest::F_HN1 => '42',
                    FormTraitTest::F_HNZ1 => 'a',
                    FormTraitTest::F_HN2 => null,
                    FormTraitTest::F_HNZ2 => null,
                    FormTraitTest::F_COUNT => 2,
                    FormTraitTest::F_COUNT_PLUS_ONE => 3,
                    FormTraitTest::F_MAP_ARRAY => ['map-value-1', 'map-value-2'],
                    FormTraitTest::F_MAP_FIELD1 => 'map-value-1',
                    FormTraitTest::F_ARRAY1 => 'array-1',
                    FormTraitTest::F_ARRAY2 => 'array-2',
                    FormTraitTest::F_DEFAULT => 'default-value',
                    FormTraitTest::F_OP_SAME_FIELD => 42,
                    FormTraitTest::F_OP_NOTSAME_FIELD => 42,
                    FormTraitTest::F_OP_EQ_FIELD => 42,
                    FormTraitTest::F_OP_NEQ_FIELD => 42,
                    FormTraitTest::F_OP_LT_FIELD => 42,
                    FormTraitTest::F_OP_LE_FIELD => 42,
                    FormTraitTest::F_OP_GE_FIELD => 42,
                    FormTraitTest::F_OP_GT_FIELD => 42,
                    FormTraitTest::F_OP_INSTR_FIELD => 42,
                    FormTraitTest::F_OP_NOTINSTR_FIELD => 42,
                ],
            ],
        ];
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider transformFailureDataProvider
     */
    public function testTransformFailure($data, $expected): void
    {
        $this->expectException($expected['exception']);
        $this->expectExceptionCode($expected['code']);
        $this->transform($data);
    }

    /**
     * @return array
     */
    public function transformFailureDataProvider(): array
    {
        return [
            'Failure 1' => [
                [
                    'test-strict' => false,
                    'test' => 'forty-two',
                    'container' => [
                        'field-three' => 'forty two',
                    ]
                ],
                [
                    'exception' => TransformationException::class,
                    'code' => TransformationException::CODE_PATH_NOT_EXISTS,
                ],
            ],
            'Failure 2' => [
                [
                    'test-strict' => false,
                    'test' => 'forty-two',
                    'container' => [
                        'field-two' => 'forty two',
                        'field-three' => '2020-03-11',
                        'hausnummer' => '42 a',
                        'test-map-array' => [
                            'map-key-1' => true,
                            'map-key-2' => false,
                        ],
                        'map-scalar-1' => true,
                        'map-scalar-2' => 5,
                        'test-array' => [
                            FormTraitTest::F_ARRAY1 => 'array-1',
                            FormTraitTest::F_ARRAY2 => 'array-2',
                        ],
                    ],
                ],
                [
                    'exception' => TransformationException::class,
                    'code' => TransformationException::CODE_UNKNOWN_MAP_KEY,
                ],
            ],
            'Failure 3' => [
                [
                    'container' => [
                        'field-two' => 'forty two',
                        'field-three' => '2020-03-11',
                        'hausnummer' => '42 a',
                        'test-map-array' => [
                            'map-key-1' => true,
                            'map-key-2' => false,
                        ],
                        'map-scalar-1' => true,
                        'map-scalar-2' => false,
                        'test-array' => [
                            FormTraitTest::F_ARRAY1 => 'array-1',
                            FormTraitTest::F_ARRAY2 => 'array-2',
                        ]
                    ]
                ],
                [
                    'exception' => TransformationException::class,
                    'code' => TransformationException::CODE_PATH_NOT_EXISTS,
                ],
            ],
        ];
    }
}