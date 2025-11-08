<?php

namespace LaravelArtifacts\Enums;

enum AiProvider: string
{
    case OPENAI = 'openai';
    case CLAUDE = 'claude';
    case GEMINI = 'gemini';
    case LOCAL = 'local';

    public function label(): string
    {
        return match ($this) {
            self::OPENAI => 'OpenAI',
            self::CLAUDE => 'Claude',
            self::GEMINI => 'Gemini',
            self::LOCAL => 'Local',
        };
    }

    public function model(): string
    {
        return match ($this) {
            self::OPENAI => config('artifacts.ai.providers.openai.model', 'gpt-4-turbo'),
            self::CLAUDE => config('artifacts.ai.providers.claude.model', 'claude-opus'),
            self::GEMINI => config('artifacts.ai.providers.gemini.model', 'gemini-pro'),
            self::LOCAL => 'local',
        };
    }
}
