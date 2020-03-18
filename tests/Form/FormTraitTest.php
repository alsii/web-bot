<?php
namespace Alsi\WebBotTests\Form;

use Alsi\WebBot\Form\FormException;
use Alsi\WebBot\Form\FormInterface;
use Alsi\WebBot\Form\FormTrait;
use PHPUnit\Framework\TestCase;

class FormTraitTest extends TestCase implements FormInterface
{
    use FormTrait;

    public const F_FIELD_1 = 'field-1';
    public const F_FIELD_2 = 'field-2';
    public const F_FIELD_3 = 'field-3';
    public const F_HN1 = 'field-hn1';
    public const F_HNZ1 = 'field-hnz1';
    public const F_HN2 = 'field-hn2';
    public const F_HNZ2 = 'field-hnz2';
    public const F_MAP_ARRAY = 'field-map-array1';
    public const F_MAP_FIELD1 = 'field-map-field-1';
    public const F_MAP_FIELD2 = 'field-map-field-2';
    public const F_ARRAY1 = 'field-array1';
    public const F_ARRAY2 = 'field-array2';
    public const F_COUNT = 'field-count';
    public const F_COUNT_PLUS_ONE = 'field-count-plus-one';

    public const WRONG_FIELD = 'field-wrong';

    public const FIELDS = [
        self::F_FIELD_1,
        self::F_FIELD_2,
        self::F_FIELD_3,
        self::F_HN1,
        self::F_HNZ1,
        self::F_HN2,
        self::F_HNZ2,
        self::F_MAP_ARRAY,
        self::F_MAP_FIELD1,
        self::F_MAP_FIELD2,
        self::F_ARRAY1,
        self::F_ARRAY2,
        self::F_COUNT,
        self::F_COUNT_PLUS_ONE,
    ];

    public function testSetData()
    {
        $result = $this->setField(self::F_FIELD_1, 42);
        $this->assertEquals($this, $result);

        $this->expectException(FormException::class);
        $this->setField(self::WRONG_FIELD, 1);
    }

    public function testGetData(): void
    {
        $this->setField(self::F_FIELD_1, '42!');

        $result = $this->getData();
        $this->assertEquals(['field-1' => '42!'], $result);
    }

    public function testGetHtmlData(): void
    {
        $this->setField(self::F_FIELD_1, '42!');

        $result = $this->getHtmlFormData();
        $this->assertEquals('field-1=42%21', $result);

        $this->setField(self::F_FIELD_1, ['42!', '84!']);

        $result = $this->getHtmlFormData();
        $this->assertEquals('field-1=42%21&field-1=84%21', $result);
    }

    public function testValidate()
    {
        $this->assertTrue($this->validate());
    }
}
