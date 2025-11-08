<?php

namespace LaravelArtifacts\Tests\Unit\Results;

use LaravelArtifacts\Results\CommandResult;
use LaravelArtifacts\Tests\TestCase;

class CommandResultTest extends TestCase
{
    /** @test */
    public function it_can_create_success_result(): void
    {
        $result = CommandResult::success(
            data: ['id' => 1],
            message: 'Operation successful'
        );

        $this->assertTrue($result->success);
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailed());
        $this->assertEquals(['id' => 1], $result->data);
        $this->assertEquals('Operation successful', $result->message);
        $this->assertEmpty($result->errors);
    }

    /** @test */
    public function it_can_create_failed_result(): void
    {
        $result = CommandResult::failed(
            errors: ['field' => ['error message']],
            message: 'Operation failed'
        );

        $this->assertFalse($result->success);
        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailed());
        $this->assertEquals(['field' => ['error message']], $result->errors);
        $this->assertEquals('Operation failed', $result->message);
        $this->assertNull($result->data);
    }

    /** @test */
    public function it_can_convert_to_array(): void
    {
        $result = CommandResult::success(
            data: ['test' => 'value'],
            message: 'Success',
            metadata: ['time' => 1.5]
        );

        $array = $result->toArray();

        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('errors', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('meta', $array);
        $this->assertTrue($array['success']);
        $this->assertEquals(['test' => 'value'], $array['data']);
        $this->assertEquals(['time' => 1.5], $array['meta']);
    }

    /** @test */
    public function it_can_convert_to_json(): void
    {
        $result = CommandResult::success(
            data: ['test' => 'value'],
            message: 'Success'
        );

        $json = $result->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertTrue($decoded['success']);
        $this->assertEquals(['test' => 'value'], $decoded['data']);
    }

    /** @test */
    public function it_can_convert_to_string(): void
    {
        $result = CommandResult::success(message: 'Test');

        $string = (string) $result;
        $decoded = json_decode($string, true);

        $this->assertIsString($string);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
    }

    /** @test */
    public function it_includes_metadata_in_result(): void
    {
        $metadata = ['execution_time' => 0.5, 'memory_used' => 1024];

        $result = CommandResult::success(
            message: 'Success',
            metadata: $metadata
        );

        $this->assertEquals($metadata, $result->metadata);
        $this->assertEquals($metadata, $result->toArray()['meta']);
    }
}
