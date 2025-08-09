<?php

namespace Mortezamasumi\FbReport\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Closure;

trait CanFormatState
{
    protected ?Closure $formatStateUsing = null;
    protected int|Closure|null $characterLimit = null;
    protected string|Closure|null $characterLimitEnd = null;
    protected int|Closure|null $wordLimit = null;
    protected string|Closure|null $wordLimitEnd = null;
    protected string|Closure|null $prefix = null;
    protected string|Closure|null $suffix = null;
    protected bool $isListedAsJson = false;
    protected bool $isBulleted = false;
    protected bool $isFlattenState = false;
    protected float $flattenDepth;
    protected bool $isListWithLineBreaks = false;

    public function limit(int|Closure|null $length = 100, string|Closure|null $end = '...'): static
    {
        $this->characterLimit = $length;
        $this->characterLimitEnd = $end;

        return $this;
    }

    public function words(int|Closure|null $words = 100, string|Closure|null $end = '...'): static
    {
        $this->wordLimit = $words;
        $this->wordLimitEnd = $end;

        return $this;
    }

    public function prefix(string|Closure|null $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function suffix(string|Closure|null $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function formatStateUsing(?Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    public function formatState(mixed $state): mixed
    {
        $state = $this->evaluate($this->formatStateUsing ?? $state, [
            'state' => $state,
        ]);

        if ($characterLimit = $this->getCharacterLimit()) {
            $state = Str::limit($state, $characterLimit, $this->getCharacterLimitEnd());
        }

        if ($wordLimit = $this->getWordLimit()) {
            $state = Str::words($state, $wordLimit, $this->getWordLimitEnd());
        }

        $prefix = $this->getPrefix();
        $suffix = $this->getSuffix();

        if (filled($prefix)) {
            $state = "$prefix$state";
        }

        if (filled($suffix)) {
            $state .= $suffix;
        }

        return $state;
    }

    public function getFormattedState(): ?string
    {
        $state = $this->getState();

        if (! is_array($state)) {
            return $this->formatState($state);
        }

        if ($this->isFlattenState()) {
            $state = Arr::flatten($state, $this->flattenDepth);
        }

        $state = array_map($this->formatState(...), $state);

        if ($this->isListedAsJson()) {
            return json_encode($state);
        }

        if ($this->isListWithLineBreaks()) {
            return implode('<br>', $state);
        }

        if ($this->isBulleted()) {
            return '<ul>'.implode('', array_map(fn ($state) => "<li>$state</li>", $state)).'</ul>';
        }

        return implode(', ', $state);
    }

    public function getCharacterLimit(): ?int
    {
        return $this->evaluate($this->characterLimit);
    }

    public function getCharacterLimitEnd(): ?string
    {
        return $this->evaluate($this->characterLimitEnd);
    }

    public function getWordLimit(): ?int
    {
        return $this->evaluate($this->wordLimit);
    }

    public function getWordLimitEnd(): ?string
    {
        return $this->evaluate($this->wordLimitEnd);
    }

    public function getPrefix(): ?string
    {
        return $this->evaluate($this->prefix);
    }

    public function getSuffix(): ?string
    {
        return $this->evaluate($this->suffix);
    }

    public function listAsJson(bool $condition = true): static
    {
        $this->isListedAsJson = $condition;

        return $this;
    }

    public function isListedAsJson(): bool
    {
        return $this->isListedAsJson;
    }

    public function bulleted(bool $condition = true): static
    {
        $this->isBulleted = $condition;

        return $this;
    }

    public function isBulleted(): bool
    {
        return $this->isBulleted;
    }

    public function listWithLineBreaks(bool $condition = true): static
    {
        $this->isListWithLineBreaks = $condition;

        return $this;
    }

    public function isListWithLineBreaks(): bool
    {
        return $this->isListWithLineBreaks;
    }

    public function flattenState(bool $condition = true, $flattenDepth = INF): static
    {
        $this->isFlattenState = $condition;

        $this->flattenDepth = $flattenDepth;

        return $this;
    }

    public function isFlattenState(): bool
    {
        return $this->isFlattenState;
    }
}
