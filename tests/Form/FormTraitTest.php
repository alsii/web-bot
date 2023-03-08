<?php
namespace Alsi\WebBotTests\Form;

use Alsi\WebBot\Form\FormException;
use Alsi\WebBot\Form\FormInterface;
use Alsi\WebBot\Form\FormTrait;
use Alsi\WebBotTests\CodeProvider as CP;
use PHPUnit\Framework\TestCase;

class FormTraitTest extends TestCase implements FormInterface
{
    use FormTrait;

    public const F_FIELD_1 = 'field-1';
    public const F_FIELD_2 = 'field-2';
    public const F_FIELD_3 = 'field-3';
    public const F_FIELD_4 = 'field-4';
    public const F_FIELD_5 = 'field-5';
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

    public const F_DEFAULT = 'field-default';
    public const F_TEST_FIELD = 'field-test';
    public const WRONG_FIELD = 'field-wrong';

    public const F_OP_SAME_FIELD = 'field-op-same';
    public const F_OP_NOTSAME_FIELD = 'field-op-notsame';
    public const F_OP_EQ_FIELD = 'field-op-eq';
    public const F_OP_NEQ_FIELD = 'field-op-neq';
    public const F_OP_LT_FIELD = 'field-op-lt';
    public const F_OP_LE_FIELD = 'field-op-le';
    public const F_OP_GE_FIELD = 'field-op-ge';
    public const F_OP_GT_FIELD = 'field-op-gt';
    public const F_OP_INSTR_FIELD = 'field-op-instr';
    public const F_OP_NOTINSTR_FIELD = 'field-op-notinstr';

    public const FIELDS = [
        self::F_FIELD_1,
        self::F_FIELD_2,
        self::F_FIELD_3,
        self::F_FIELD_4,
        self::F_FIELD_5,
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
        self::F_TEST_FIELD,
        self::F_DEFAULT,
        self::F_OP_SAME_FIELD,
        self::F_OP_SAME_FIELD,
        self::F_OP_NOTSAME_FIELD,
        self::F_OP_EQ_FIELD,
        self::F_OP_NEQ_FIELD,
        self::F_OP_LT_FIELD,
        self::F_OP_LE_FIELD,
        self::F_OP_GE_FIELD,
        self::F_OP_GT_FIELD,
        self::F_OP_INSTR_FIELD,
        self::F_OP_NOTINSTR_FIELD,
    ];

    public const FIELD_CODES = [
        CP::F_FIELD_1 => self::F_FIELD_1,
        CP::F_FIELD_2 => self::F_FIELD_2,
        CP::F_FIELD_3 => self::F_FIELD_3,
        CP::F_FIELD_4 => self::F_FIELD_4,
        CP::F_FIELD_5 => self::F_FIELD_5,
        CP::F_HN1 => self::F_HN1,
        CP::F_HNZ1 => self::F_HNZ1,
        CP::F_HN2 => self::F_HN2,
        CP::F_HNZ2 => self::F_HNZ2,
        CP::F_MAP_ARRAY => self::F_MAP_ARRAY,
        CP::F_MAP_FIELD1 => self::F_MAP_FIELD1,
        CP::F_MAP_FIELD2 => self::F_MAP_FIELD2,
        CP::F_ARRAY1 => self::F_ARRAY1,
        CP::F_ARRAY2 => self::F_ARRAY2,
        CP::F_COUNT => self::F_COUNT,
        CP::F_COUNT_PLUS_ONE => self::F_COUNT_PLUS_ONE,
        CP::F_TEST_FIELD => self::F_TEST_FIELD,
        CP::F_DEFAULT => self::F_DEFAULT,
    ];

    private const V_FORTY_TWO = 42;
    private const V_FORTY_TWO_EXCL = '42!';
    private const V_EIGHTY_FOUR = 84;
    private const V_EIGHTY_FOUR_EXCL = '84!';

    private const VALUE_CODES = [
        CP::V_FORTY_TWO => self::V_FORTY_TWO,
        CP::V_FORTY_TWO_EXCL => self::V_FORTY_TWO_EXCL,
        CP::V_EIGHTY_FOUR => self::V_EIGHTY_FOUR,
        CP::V_EIGHTY_FOUR_EXCL => self::V_EIGHTY_FOUR_EXCL,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setFieldCodes(self::FIELD_CODES);
        $this->setValueCodes(self::VALUE_CODES);
    }

    public function testSetCodes(): void
    {
        $r1 = $this->setFieldCodes(self::FIELD_CODES);
        $this->assertEquals($this, $r1);

        $r2 = $this->setValueCodes(self::VALUE_CODES);
        $this->assertEquals($this, $r2);
    }

    public function testSetGetDataDirect(): void
    {
        $result = $this->setField(self::F_FIELD_1, self::V_FORTY_TWO);
        $this->assertEquals($this, $result);

        $data = $this->getField(self::F_FIELD_1);
        $this->assertEquals(self::V_FORTY_TWO, $data);

        $data = $this->getField(CP::F_FIELD_1, true, true);
        $this->assertEquals(CP::V_FORTY_TWO, $data);

        $this->expectException(FormException::class);
        $this->setField(self::WRONG_FIELD, 1);
    }

    public function testSetGetDataCode(): void
    {
        $this->setField(CP::F_FIELD_1, CP::V_FORTY_TWO, true, true);

        $data = $this->getField(self::F_FIELD_1);
        $this->assertEquals(self::V_FORTY_TWO, $data);

        $data = $this->getField(CP::F_FIELD_1, true, true);
        $this->assertEquals(CP::V_FORTY_TWO, $data);

        $this->setField(CP::F_FIELD_1, [CP::V_FORTY_TWO, CP::V_EIGHTY_FOUR], true, true);

        $data = $this->getField(self::F_FIELD_1);
        $this->assertEquals([self::V_FORTY_TWO, self::V_EIGHTY_FOUR], $data);

        $data = $this->getField(CP::F_FIELD_1, true, true);
        $this->assertEquals([CP::V_FORTY_TWO, CP::V_EIGHTY_FOUR], $data);
    }

    public function testExceptionBySetWrongFieldCode(): void
    {
        $this->expectException(FormException::class);
        $this->setField(self::WRONG_FIELD, 1, true, true);
    }

    public function testExceptionBySetWrongValueCode(): void
    {
        $this->expectException(FormException::class);
        $this->setField(CP::F_FIELD_1, 1, true, true);
    }

    public function testExceptionByGetWrongValueCode(): void
    {
        $this->setField(self::F_FIELD_1, 1);

        $this->expectException(FormException::class);
        $this->getField(CP::F_FIELD_1, true, true);
    }

    public function testExceptionByGetFieldNotSet(): void
    {
        $this->expectException(FormException::class);
        $this->getField(CP::F_FIELD_1, true, true);
    }

    public function testGetDataDirect(): void
    {
        $this->setField(self::F_FIELD_1, '42!');

        $result = $this->getData();
        $this->assertEquals(['field-1' => '42!'], $result);
    }

    public function testGetDataCode(): void
    {
        $this->setField(CP::F_FIELD_1, CP::V_FORTY_TWO_EXCL, true, true);

        $result = $this->getData();
        $this->assertEquals([self::F_FIELD_1 => self::V_FORTY_TWO_EXCL], $result);
    }

    public function testAddToFieldOption(): void
    {
        $this->setField(CP::F_FIELD_1, CP::V_FORTY_TWO, true, true);
        $this->setField(CP::F_FIELD_1, CP::V_EIGHTY_FOUR, true, true, true);

        $result = $this->getField(CP::F_FIELD_1, true, true);
        $this->assertEquals([CP::V_FORTY_TWO, CP::V_EIGHTY_FOUR], $result);
    }

    public function testGetHtmlDataDirect(): void
    {
        $this->setField(self::F_FIELD_1, '42!');

        $result = $this->getHtmlFormData();
        $this->assertEquals('field-1=42%21', $result);

        $this->setField(self::F_FIELD_1, ['42!', '84!']);

        $result = $this->getHtmlFormData();
        $this->assertEquals('field-1=42%21&field-1=84%21', $result);
    }

    public function testGetHtmlDataCode(): void
    {
        $this->setField(CP::F_FIELD_1, CP::V_FORTY_TWO_EXCL, true, true);

        $result = $this->getHtmlFormData();
        $this->assertEquals('field-1=42%21', $result);

        $this->setField(CP::F_FIELD_1, [CP::V_FORTY_TWO_EXCL, CP::V_EIGHTY_FOUR_EXCL], true, true);

        $result = $this->getHtmlFormData();
        $this->assertEquals('field-1=42%21&field-1=84%21', $result);
    }

    public function testValidate(): void
    {
        $this->assertTrue($this->validate());
    }

    public function testGetSelectors(): void
    {
        $selectors = $this->getSelectors();
        $this->assertEquals([], $selectors);
    }
}
