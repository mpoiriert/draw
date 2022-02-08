<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\Extractor\Constraint\ConstraintExtractionContext;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\NotBlankConstraintExtractor;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Draw\Component\OpenApi\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @covers \Draw\Component\OpenApi\Extraction\Extractor\Constraint\NotBlankConstraintExtractor
 */
class NotBlankConstraintExtractorTest extends TestCase
{
    /**
     * @var NotBlankConstraintExtractor
     */
    private $extractor;

    public function setUp(): void
    {
        $this->extractor = new NotBlankConstraintExtractor();
    }

    public function provideTestSupport(): iterable
    {
        yield 'other-constraint' => [
            new IsNull(),
            false,
        ];

        yield 'not-blank' => [
            new NotBlank(),
            true,
        ];
    }

    /**
     * @dataProvider provideTestSupport
     */
    public function testSupport(Constraint $constraint, bool $expected)
    {
        $this->assertSame($expected, $this->extractor->supportConstraint($constraint));
    }

    public function testExtractConstraintBaseParameter(): void
    {
        $constraint = new NotBlank();
        $context = new ConstraintExtractionContext();
        $context->validationConfiguration = new QueryParameter();

        $this->extractor->extractConstraint($constraint, $context);

        $this->assertSame('not empty', $context->validationConfiguration->format);
        $this->assertTrue($context->validationConfiguration->required);
    }

    public function testExtractConstraintSchema(): void
    {
        $constraint = new NotBlank();
        $context = new ConstraintExtractionContext();
        $context->propertyName = 'test';
        $context->classSchema = $context->validationConfiguration = new Schema();

        $this->extractor->extractConstraint($constraint, $context);

        $this->assertSame('not empty', $context->validationConfiguration->format);
        $this->assertContains('test', $context->classSchema->required);
    }

    public function testExtractConstraintBaseParameterAllowNull(): void
    {
        $constraint = new NotBlank();
        $constraint->allowNull = true;
        $context = new ConstraintExtractionContext();
        $context->validationConfiguration = new QueryParameter();

        $this->extractor->extractConstraint($constraint, $context);

        $this->assertSame('not empty', $context->validationConfiguration->format);
        $this->assertNull($context->validationConfiguration->required);
    }
}
