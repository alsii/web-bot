<?php
namespace Alsi\WebBotTests\Form;

use Alsi\WebBot\Form\FormException;
use Alsi\WebBot\Form\FormInterface;
use Alsi\WebBot\Form\FormTrait;
use Alsi\WebBot\Form\SubformInterface;
use Alsi\WebBot\Form\SubFormTrait;
use PHPUnit\Framework\TestCase;

class SubFormTraitTest extends TestCase implements FormInterface, SubformInterface
{
    use FormTrait;
    use SubFormTrait;

    public const F_FIELD = 'field-1';
    public const F_SUBFORMS_MAX = 'subform-max';
    public const F_SUBFORMS_TOTAL = 'subform-total';

    public const FIELDS = [
        self::F_FIELD,
        self::F_SUBFORMS_MAX,
        self::F_SUBFORMS_TOTAL
    ];

    public const SUBFORMS = [
        self::class => [
            self::SF_TOTAL => self::F_SUBFORMS_TOTAL,
            self::SF_MAX => self::F_SUBFORMS_MAX,
        ],
    ];

    public function setUp(): void
    {
        $this->setField(self::F_FIELD, 42);
        $this->setField(self::F_SUBFORMS_MAX, 1);
//        $this->setField(self::F_SUBFORMS_TOTAL, 0);
    }

    public function tearDown(): void
    {
        $this->data = [];
    }

    public function testAddSubForm()
    {
        $result = $this->addSubForm($this);
        $this->assertEquals($this, $result);

        $this->expectException(FormException::class);
        $this->expectExceptionCode(FormException::CODE_TOO_MANY_SUBFORMS);

        $this->addSubForm($this);
    }

    public function testWrongSubform()
    {
        $this->expectException(FormException::class);
        $this->expectExceptionCode(FormException::CODE_WRONG_SUBFORM);

        $this->addSubForm(new FormTraitTest());
    }

    public function testGetData(): void
    {
        $this->addSubForm($this);
        $expected = [
            self::F_FIELD => 42,
            self::F_SUBFORMS_MAX => 1,
            self::F_SUBFORMS_TOTAL => 1,
            '0-' . self::F_FIELD => 42,
            '0-' . self::F_SUBFORMS_MAX => 1,
            '0-' . self::F_SUBFORMS_TOTAL => 1,
        ];
        $result = $this->getData();
        $this->assertEquals($expected, $result);
    }
}
